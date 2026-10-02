# D:\CODE\du-lich-so\python-service\app\etl_clean.py

"""
Thu thập và làm sạch dữ liệu địa điểm trải nghiệm cho đề tài DT-17.

Luồng xử lý:

CSV thô
    -> chuẩn hóa tên cột
    -> loại bản ghi thiếu dữ liệu bắt buộc
    -> chuẩn hóa tên tỉnh/thành
    -> kiểm tra tọa độ Việt Nam
    -> loại bản ghi trùng
    -> chuẩn hóa thời lượng
    -> xuất CSV sạch

Có thể chạy:

python -m app.etl_clean --input data/raw/places.csv --dry-run

Hoặc:

python -m app.etl_clean --input data/raw/places.csv --load
"""

from __future__ import annotations

import argparse
import logging
import os
import re
import unicodedata
from pathlib import Path

import pandas as pd
from dotenv import load_dotenv
from sqlalchemy import create_engine, text


SERVICE_ROOT = Path(__file__).resolve().parents[1]
PROJECT_ROOT = SERVICE_ROOT.parent

load_dotenv(PROJECT_ROOT / "backend" / ".env")

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(message)s",
)

log = logging.getLogger("dt17-etl")


DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "3306")
DB_DATABASE = os.getenv("DB_DATABASE", "dulichso")
DB_USERNAME = os.getenv("DB_USERNAME", "")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")

DSN = (
    "mysql+pymysql://{user}:{password}@{host}:{port}/{database}"
    "?charset=utf8mb4"
).format(
    user=DB_USERNAME,
    password=DB_PASSWORD,
    host=DB_HOST,
    port=DB_PORT,
    database=DB_DATABASE,
)


TINH_CHUAN = {
    "ha noi": "Ha Noi",
    "hn": "Ha Noi",
    "da nang": "Da Nang",
    "tp ho chi minh": "TP Ho Chi Minh",
    "tphcm": "TP Ho Chi Minh",
    "hai phong": "Hai Phong",
    "quang ninh": "Quang Ninh",
    "ninh binh": "Ninh Binh",
    "thua thien hue": "Hue",
}


def bo_dau(value: str) -> str:
    """
    Bỏ dấu tiếng Việt để phục vụ chuẩn hóa và sinh khóa nghiệp vụ.
    """
    normalized = unicodedata.normalize("NFKD", str(value))

    return "".join(
        char
        for char in normalized
        if not unicodedata.combining(char)
    )


def slug_hoa(value: str) -> str:
    """
    Chuyển chuỗi về dạng chữ thường, không dấu và dùng dấu gạch ngang.
    """
    value = bo_dau(value).lower()
    value = re.sub(r"[^a-z0-9]+", "-", value).strip("-")
    return re.sub(r"-{2,}", "-", value)


def lam_sach(df: pd.DataFrame) -> pd.DataFrame:
    """
    Làm sạch dữ liệu địa điểm trải nghiệm theo 7 bước.
    """

    if df.empty:
        raise ValueError("Tệp dữ liệu không có bản ghi.")

    original_count = len(df)

    # 1. Chuẩn hóa tên cột
    df.columns = [
        slug_hoa(column).replace("-", "_")
        for column in df.columns
    ]

    required_columns = {
        "name",
        "province",
        "lat",
        "lng",
    }

    missing_columns = required_columns.difference(df.columns)

    if missing_columns:
        raise ValueError(
            "Thiếu cột bắt buộc: "
            + ", ".join(sorted(missing_columns))
        )

    # 2. Loại bản ghi thiếu trường bắt buộc
    df = df.dropna(
        subset=[
            "name",
            "province",
            "lat",
            "lng",
        ]
    ).copy()

    # 3. Chuẩn hóa khoảng trắng
    for column in ("name", "province"):
        df[column] = (
            df[column]
            .astype(str)
            .str.strip()
            .str.replace(r"\s{2,}", " ", regex=True)
        )

    # 4. Chuẩn hóa tỉnh/thành
    df["province"] = df["province"].apply(
        lambda value: TINH_CHUAN.get(
            bo_dau(value).lower(),
            value,
        )
    )

    # 5. Chuẩn hóa và kiểm tra tọa độ
    df["lat"] = pd.to_numeric(
        df["lat"],
        errors="coerce",
    )

    df["lng"] = pd.to_numeric(
        df["lng"],
        errors="coerce",
    )

    df = df[
        df["lat"].between(8.0, 24.0)
        & df["lng"].between(102.0, 110.0)
    ].copy()

    # 6. Loại dữ liệu trùng theo khóa nghiệp vụ
    df["business_key"] = (
        df["name"].apply(slug_hoa)
        + "|"
        + df["province"].apply(slug_hoa)
    )

    df = df.drop_duplicates(
        subset=["business_key"],
        keep="first",
    ).copy()

    # 7. Chuẩn hóa thời lượng tham quan nếu có
    if "visit_minutes" in df.columns:
        df["visit_minutes"] = pd.to_numeric(
            df["visit_minutes"],
            errors="coerce",
        )
    else:
        df["visit_minutes"] = 120

    df["visit_minutes"] = (
        df["visit_minutes"]
        .fillna(120)
        .clip(30, 720)
        .astype(int)
    )

    if "source_note" not in df.columns:
        df["source_note"] = (
            "Du lieu mo phong phuc vu de tai DT-17."
        )

    df = df.drop(
        columns=["business_key"],
        errors="ignore",
    )

    cleaned_count = len(df)

    log.info(
        "Lam sach: %d -> %d ban ghi | loai %d",
        original_count,
        cleaned_count,
        original_count - cleaned_count,
    )

    log.info(
        "Phan bo theo tinh/thanh:\n%s",
        df["province"].value_counts().head(10),
    )

    log.info(
        "Thong ke thoi luong:\n%s",
        df["visit_minutes"].describe(),
    )

    return df


def nap_vao_places(df: pd.DataFrame) -> int:
    """
    Nạp dữ liệu sạch vào bảng places.

    Không dùng ON DUPLICATE KEY để tránh phụ thuộc vào cấu hình
    unique index cụ thể của database DT-17.
    """

    engine = create_engine(
        DSN,
        pool_pre_ping=True,
        pool_recycle=1800,
    )

    select_sql = text(
        """
        SELECT id
        FROM places
        WHERE name = :name
          AND province = :province
        LIMIT 1
        FOR UPDATE
        """
    )

    update_sql = text(
        """
        UPDATE places
        SET lat = :lat,
            lng = :lng
        WHERE id = :id
        """
    )

    insert_sql = text(
        """
        INSERT INTO places (
            name,
            province,
            lat,
            lng
        )
        VALUES (
            :name,
            :province,
            :lat,
            :lng
        )
        """
    )

    affected = 0

    with engine.begin() as connection:
        for row in df.itertuples(index=False):
            params = {
                "name": str(row.name),
                "province": str(row.province),
                "lat": float(row.lat),
                "lng": float(row.lng),
            }

            existing = connection.execute(
                select_sql,
                params,
            ).mappings().first()

            if existing:
                connection.execute(
                    update_sql,
                    {
                        "id": existing["id"],
                        "lat": params["lat"],
                        "lng": params["lng"],
                    },
                )
            else:
                connection.execute(
                    insert_sql,
                    params,
                )

            affected += 1

    log.info(
        "Da nap/cap nhat %d ban ghi vao bang places.",
        affected,
    )

    return affected


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "ETL lam sach du lieu dia diem "
            "cho he thong du lich hoc duong DT-17."
        )
    )

    parser.add_argument(
        "--input",
        required=True,
        type=Path,
        help="Duong dan tep CSV dau vao.",
    )

    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Chi lam sach va xuat CSV, khong ghi CSDL.",
    )

    parser.add_argument(
        "--load",
        action="store_true",
        help="Lam sach va nap vao bang places.",
    )

    args = parser.parse_args()

    input_path = args.input

    if not input_path.is_absolute():
        input_path = SERVICE_ROOT / input_path

    if not input_path.exists():
        raise FileNotFoundError(
            f"Khong tim thay tep dau vao: {input_path}"
        )

    log.info(
        "Doc du lieu: %s",
        input_path,
    )

    df = pd.read_csv(
        input_path,
        encoding="utf-8-sig",
    )

    clean_df = lam_sach(df)

    output_path = (
        SERVICE_ROOT
        / "data"
        / "clean"
        / "places_clean.csv"
    )

    output_path.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    clean_df.to_csv(
        output_path,
        index=False,
        encoding="utf-8-sig",
    )

    log.info(
        "Da ghi du lieu sach: %s",
        output_path,
    )

    if args.load:
        nap_vao_places(clean_df)

    if not args.dry_run and not args.load:
        log.info(
            "Khong co --load: chi tao file du lieu sach."
        )


if __name__ == "__main__":
    main()

