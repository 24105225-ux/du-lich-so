# D:\CODE\du-lich-so\python-service\app\analytics.py

"""
Phân tích dữ liệu chương trình trải nghiệm giáo dục DT-17.

Các chức năng:

- Thống kê chương trình theo cấp học.
- Thống kê giá và sức chứa trung bình.
- Xếp hạng môn học xuất hiện trong chương trình.
"""

from __future__ import annotations

import pandas as pd
from sqlalchemy import create_engine, text

from .etl_clean import DSN


_engine = create_engine(
    DSN,
    pool_pre_ping=True,
    pool_recycle=1800,
)


def thong_ke_theo_cap_hoc() -> list[dict]:
    """
    Thống kê số chương trình, giá trung bình
    và sức chứa trung bình theo cấp học.
    """

    query = text(
        """
        SELECT
            education_level,
            COUNT(*) AS so_chuong_trinh,
            ROUND(AVG(price_per_student), 2) AS gia_trung_binh,
            ROUND(AVG(capacity), 1) AS suc_chua_trung_binh
        FROM programs
        GROUP BY education_level
        ORDER BY education_level
        """
    )

    df = pd.read_sql(
        query,
        _engine,
    )

    return (
        df.fillna(0)
        .to_dict(orient="records")
    )


def top_mon_hoc(
    limit: int = 10,
) -> list[dict]:
    """
    Thống kê các môn học được gắn với nhiều chương trình nhất.
    """

    query = text(
        """
        SELECT
            s.name AS subject_name,
            COUNT(DISTINCT ps.program_id) AS so_chuong_trinh
        FROM subjects s
        INNER JOIN program_subjects ps
            ON ps.subject_id = s.id
        GROUP BY
            s.id,
            s.name
        ORDER BY
            so_chuong_trinh DESC,
            s.name ASC
        LIMIT :limit
        """
    )

    df = pd.read_sql(
        query,
        _engine,
        params={
            "limit": int(limit),
        },
    )

    return (
        df.fillna(0)
        .to_dict(orient="records")
    )


def thong_ke_gia() -> dict:
    """
    Trả về một số chỉ số tổng quan về giá chương trình.
    """

    query = text(
        """
        SELECT
            COUNT(*) AS total_programs,
            ROUND(MIN(price_per_student), 2) AS min_price,
            ROUND(MAX(price_per_student), 2) AS max_price,
            ROUND(AVG(price_per_student), 2) AS avg_price,
            ROUND(AVG(capacity), 1) AS avg_capacity
        FROM programs
        """
    )

    row = pd.read_sql(
        query,
        _engine,
    ).fillna(0).to_dict(
        orient="records"
    )

    return row[0] if row else {}


def tong_hop() -> dict:
    """
    Tổng hợp dữ liệu thống kê phục vụ kiểm thử
    và minh chứng báo cáo.
    """

    return {
        "theo_cap_hoc": thong_ke_theo_cap_hoc(),
        "top_mon_hoc": top_mon_hoc(10),
        "tong_quan": thong_ke_gia(),
    }
