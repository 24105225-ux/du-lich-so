# Database Verification — ĐT-17

## 1. Thực thể

- 26 bảng nghiệp vụ trong schema, bao gồm `program_reviews`.
- 4 bảng framework của Laravel: `sessions`, `cache`, `cache_locks`, `personal_access_tokens`.

## 2. Dữ liệu mô phỏng

Bộ sinh `database/seed_data.py` tạo dữ liệu xác định và có thể tái tạo. Các bảng chương trình/lịch trình/điểm dừng/điểm danh và nghiệp vụ liên quan tổng cộng vượt ngưỡng 300 bản ghi yêu cầu của đề tài.

## 3. Quan hệ

- parents — students
- programs — subjects
- programs — educational_requirements
- schedules — teachers
- schedules — places
- programs — reviews

## 4. Chỉ mục

Schema đã có chỉ mục cho các luồng tìm kiếm chương trình, lịch, parent notifications, audit logs và các khóa ngoại quan trọng.

## 5. Tạo database sạch

1. Chạy `database/schema.sql`.
2. Chạy `database/seed.sql`.
3. Chạy `database/create_app_user.sql`.

## 6. Backup

`tools/backup-db.ps1` tạo file backup bằng `mysqldump` từ các biến DB trong `backend/.env`; không chứa secret trong repo.
