# Nền tảng du lịch học đường — CSE703073 — ĐT-17

## 1. Thông tin dự án

- Học phần: CSE703073 – Lập trình ứng dụng web trong du lịch 2
- Đề tài: ĐT-17 – Nền tảng du lịch học đường và chương trình trải nghiệm giáo dục
- Giảng viên: TS. Nguyễn Văn Tánh
- Dữ liệu học sinh trong repo là dữ liệu mô phỏng phục vụ học tập.

## 2. Công nghệ thực tế trong repo

- Backend: PHP 8.3 + Laravel 12
- Database: MySQL 8.x
- Frontend: Vue 3 + Vite
- CSS: CSS variables + responsive CSS
- Python: Python 3.12 + pandas + scikit-learn + FastAPI
- Web server: Nginx 1.24 configuration có sẵn cho production
- Container: Docker + Docker Compose cho môi trường phát triển

## 3. Kiến trúc

```text
Vue 3 / Vite
     │ /api
     ▼
Laravel 12 ──────► MySQL
     │
     │ HTTP
     ▼
FastAPI / Python
     │
     └────────────► MySQL
```

## 4. Cấu trúc

```text
du-lich-so/
├── backend/
├── frontend/
├── python-service/
├── database/
├── docker/
├── docs/
├── tools/
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

## 5. Chạy local bằng PowerShell

### Backend

```powershell
Set-Location .\backend
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend Vue

```powershell
Set-Location .\frontend
npm install
npm run dev
```

Các trang Blade của Laravel dùng asset tĩnh trong `backend/public/assets`, vì vậy không cần chạy thêm Vite cho backend khi kiểm tra `/dang-nhap` hoặc `/dashboard`.

### Python

```powershell
Set-Location .\python-service
python -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001
```

### Database

Import `database/schema.sql`, sau đó `database/seed.sql`. Tạo tài khoản ứng dụng bằng `database/create_app_user.sql`.

## 6. Tài khoản mô phỏng

`database/seed.sql` dùng dữ liệu học tập mô phỏng. Mật khẩu kiểm thử của các tài khoản seed là `ChangeMe123!`; không dùng mật khẩu này cho môi trường thật.

## 6. Docker Compose

```powershell
docker compose up --build
```

Gateway Nginx dùng cổng 8080; service riêng vẫn có thể kiểm tra ở 8000, 8001, 5173 và 3306.

## 7. API kiểm tra nhanh

```text
GET http://127.0.0.1:8000/api/v1/programs
GET http://127.0.0.1:8000/api/v1/programs/1
GET http://127.0.0.1:8000/api/v1/programs/1/recommend
GET http://127.0.0.1:8001/healthz
GET http://127.0.0.1:8001/recommend/1?k=6
```

## 8. Git và thông tin nhóm

Thông tin thành viên, lớp và phân chia nhiệm vụ không được tự điền trong source để tránh tạo dữ liệu cá nhân giả. Điền theo hồ sơ nhóm trước khi nộp.

## 9. Tài liệu

- `00_INFO.md`: yêu cầu và stack
- `docs/PHAN_I_TONG_QUAN.md`: Phần I
- `docs/data_dictionary.md`: từ điển dữ liệu
- `docs/openapi.yaml`: API
- `docs/owasp-top10.md`: rà soát bảo mật
- `docs/part-v-architecture.png`: kiến trúc
- `docs/part-vi-security.png`: bảo mật
- `docs/part-vii-frontend.png`: Frontend
- `docs/part-viii-python.png`: Python Data Service
