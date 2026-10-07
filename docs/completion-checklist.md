# Checklist đóng gói source I–VIII

## Đã xử lý trong bản ZIP này

- Đồng bộ tên cột `programs`, `schedule_stops`, `places`, `schools`, `organizers`, `school_classes`.
- `schema.sql` có `student_count`, `program_reviews` và các bảng framework Laravel.
- Python đọc `backend/.env`, recommender dùng TF-IDF + cosine similarity.
- Laravel gọi FastAPI qua `PythonDataService`, có fallback.
- Frontend gọi recommendation endpoint mới và có CSRF token cho POST review.
- Review eligibility được gọi thực tế từ `ProgramDetailView.vue`.
- Audit log service được bổ sung cho các thao tác quan trọng.
- Loại bỏ placeholder ảnh trong màn hình chi tiết bằng ảnh minh họa có sẵn.
- Tài liệu cập nhật Laravel 12 / MySQL 8.x và kiến trúc thực tế.
- Có Docker Compose/dev gateway, công cụ backup database và smoke tests; source đã được rà soát syntax PHP/Vue/Python.
- ZIP source không bao gồm secret, `vendor`, `node_modules`, `.venv`, log và build artifact.

## Không thể tự điền trung thực

- Tên/MSSV/email thành viên nhóm: cần nhóm cung cấp.
- Báo cáo 50–100 trang, video 08–12 phút và audio thuyết minh: là sản phẩm ngoài source code.
- Lịch sử Git 40 commit/08 tuần và tỷ lệ commit từng thành viên: không được tạo giả. Repo gốc hiện chỉ có lịch sử ngắn, nên việc này phải đáp ứng bằng quy trình Git thực tế.
- Triển khai public HTTPS: cần thông tin máy chủ/domain thật.
