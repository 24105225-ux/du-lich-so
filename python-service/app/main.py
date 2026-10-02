# D:\CODE\du-lich-so\python-service\app\main.py

from __future__ import annotations

import os

from fastapi import (
    FastAPI,
    Header,
    HTTPException,
    Query,
)

from . import analytics
from . import recommender


app = FastAPI(
    title="DT-17 Python Data Service",
    description=(
        "Dich vu xu ly du lieu cho "
        "nen tang du lich hoc duong"
    ),
    version="1.0.0",
)


TOKEN = os.getenv(
    "PY_SERVICE_TOKEN",
    "",
)


def kiem_tra_token(
    x_service_token: str | None = Header(
        default=None,
    ),
) -> None:
    """
    Kiểm tra token giữa Laravel và Python.

    Nếu PY_SERVICE_TOKEN trong môi trường trống,
    endpoint không bắt buộc token cho môi trường
    phát triển nội bộ.

    Khi triển khai thật, phải cấu hình token.
    """

    if TOKEN and x_service_token != TOKEN:
        raise HTTPException(
            status_code=401,
            detail="Token khong hop le",
        )


@app.get("/healthz")
def suc_khoe() -> dict:
    """
    Kiểm tra dịch vụ Python có hoạt động.
    """

    return {
        "status": "ok",
        "service": "dt17-python",
    }


@app.get("/recommend/{program_id}")
def api_goi_y(
    program_id: int,
    k: int = Query(
        default=6,
        ge=1,
        le=20,
    ),
    x_service_token: str | None = Header(
        default=None,
    ),
) -> dict:

    kiem_tra_token(
        x_service_token
    )

    return {
        "program_id": program_id,
        "items": recommender.goi_y(
            program_id,
            k,
        ),
    }


@app.get("/analytics/education-levels")
def api_cap_hoc(
    x_service_token: str | None = Header(
        default=None,
    ),
) -> dict:

    kiem_tra_token(
        x_service_token
    )

    return {
        "data": analytics.thong_ke_theo_cap_hoc()
    }


@app.get("/analytics/top-subjects")
def api_top_mon_hoc(
    limit: int = Query(
        default=10,
        ge=1,
        le=50,
    ),
    x_service_token: str | None = Header(
        default=None,
    ),
) -> dict:

    kiem_tra_token(
        x_service_token
    )

    return {
        "data": analytics.top_mon_hoc(
            limit
        )
    }


@app.get("/analytics/summary")
def api_summary(
    x_service_token: str | None = Header(
        default=None,
    ),
) -> dict:

    kiem_tra_token(
        x_service_token
    )

    return {
        "data": analytics.tong_hop()
    }


@app.post("/cache/refresh")
def api_lam_moi_cache(
    x_service_token: str | None = Header(
        default=None,
    ),
) -> dict:

    kiem_tra_token(
        x_service_token
    )

    recommender.lam_moi_cache()

    return {
        "status": "refreshed"
    }
