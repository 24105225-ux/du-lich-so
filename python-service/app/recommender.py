# D:\CODE\du-lich-so\python-service\app\recommender.py

"""
Gợi ý chương trình trải nghiệm giáo dục cho DT-17.

Phương pháp:

TF-IDF + cosine similarity

Hồ sơ mỗi chương trình gồm:

- tên chương trình
- cấp học
- môn học
- tỉnh/thành
- địa điểm trải nghiệm

Ngoài độ tương đồng nội dung, hệ thống lọc thêm
theo khoảng giá để kết quả có tính khả thi.
"""

from __future__ import annotations

import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

from .etl_clean import DSN

from sqlalchemy import create_engine, text


_engine = create_engine(
    DSN,
    pool_pre_ping=True,
    pool_recycle=1800,
)


_cache: dict = {}


def _nap_ho_so() -> pd.DataFrame:
    """
    Nạp hồ sơ nội dung của toàn bộ chương trình.
    """

    query = text(
        """
        SELECT
            p.id,
            p.name AS title,
            p.education_level,
            p.base_cost_per_student AS price_per_student,

            GROUP_CONCAT(
                DISTINCT s.name
                SEPARATOR ' '
            ) AS subjects,

            GROUP_CONCAT(
                DISTINCT pl.province
                SEPARATOR ' '
            ) AS provinces,

            GROUP_CONCAT(
                DISTINCT pl.name
                SEPARATOR ' '
            ) AS places

        FROM programs p

        LEFT JOIN program_subjects ps
            ON ps.program_id = p.id

        LEFT JOIN subjects s
            ON s.id = ps.subject_id

        LEFT JOIN program_schedules sch
            ON sch.program_id = p.id

        LEFT JOIN schedule_stops ss
            ON ss.schedule_id = sch.id

        LEFT JOIN places pl
            ON pl.id = ss.place_id

        GROUP BY
            p.id,
            p.name,
            p.education_level,
            p.base_cost_per_student
        """
    )

    df = pd.read_sql(
        query,
        _engine,
    )

    if df.empty:
        return df

    for column in (
        "title",
        "education_level",
        "subjects",
        "provinces",
        "places",
    ):
        if column not in df.columns:
            df[column] = ""

        df[column] = df[column].fillna("").astype(str)

    # Lặp lại cấp học để tăng trọng số.
    df["mo_ta"] = (
        df["education_level"]
        + " "
        + df["education_level"]
        + " "
        + df["title"]
        + " "
        + df["subjects"]
        + " "
        + df["provinces"]
        + " "
        + df["places"]
    )

    return df


def _ma_tran() -> tuple[pd.DataFrame, object]:
    """
    Sinh ma trận TF-IDF và cosine similarity.
    """

    if "df" not in _cache or "matrix" not in _cache:

        df = _nap_ho_so()

        if df.empty:
            return df, None

        vectorizer = TfidfVectorizer(
            analyzer="word",
            ngram_range=(1, 2),
            min_df=1,
        )

        matrix = vectorizer.fit_transform(
            df["mo_ta"]
        )

        similarity = cosine_similarity(matrix)

        _cache["df"] = df
        _cache["matrix"] = similarity

    return (
        _cache["df"],
        _cache["matrix"],
    )


def goi_y(
    program_id: int,
    k: int = 6,
    khoang_gia: float = 0.6,
) -> list[dict]:
    """
    Gợi ý tối đa k chương trình tương tự.

    Chỉ giữ những chương trình có giá nằm trong
    khoảng ±60% so với chương trình gốc.
    """

    df, matrix = _ma_tran()

    if df.empty or matrix is None:
        return []

    ids = set(
        df["id"].astype(int).tolist()
    )

    if int(program_id) not in ids:
        return []

    target_index = df.index[
        df["id"].astype(int) == int(program_id)
    ][0]

    source_price = float(
        df.at[
            target_index,
            "price_per_student",
        ]
    )

    scores = pd.Series(
        matrix[target_index],
        index=df.index,
    )

    scores = scores.drop(
        target_index,
        errors="ignore",
    )

    min_price = source_price * (
        1 - khoang_gia
    )

    max_price = source_price * (
        1 + khoang_gia
    )

    price_mask = df.loc[
        scores.index,
        "price_per_student",
    ].between(
        min_price,
        max_price,
    )

    scores = scores[
        price_mask
    ].sort_values(
        ascending=False
    ).head(k)

    result = []

    for index, score in scores.items():

        result.append(
            {
                "program_id": int(
                    df.at[index, "id"]
                ),
                "title": str(
                    df.at[index, "title"]
                ),
                "education_level": str(
                    df.at[
                        index,
                        "education_level",
                    ]
                ),
                "price_per_student": float(
                    df.at[
                        index,
                        "price_per_student",
                    ]
                ),
                "similarity": round(
                    float(score),
                    4,
                ),
            }
        )

    return result


def lam_moi_cache() -> None:
    """
    Xóa cache khi dữ liệu chương trình thay đổi.
    """

    _cache.clear()




