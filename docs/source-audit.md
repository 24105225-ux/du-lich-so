# Source audit — bản đóng gói I–VIII

## Những điểm đã sửa

- Đồng bộ model Laravel với `database/schema.sql`, gồm `programs`, `schedule_stops`, `places`, `schools`, `organizers`, `school_classes` và `class_registrations`.
- Đồng bộ `ProgramResource` với `seq_no`, `activity`, `duration_minutes`; loại bỏ field trùng.
- Đồng bộ recommender Python với `name`, `base_cost_per_student` và `schedule_id`.
- Đồng bộ analytics Python với `base_cost_per_student`.
- Python local đọc `backend/.env`; Docker dùng biến môi trường của container.
- Recommendation có đường đi chuẩn Laravel → `PythonDataService` → FastAPI; endpoint tương thích cũ vẫn dùng chung service.
- Bổ sung audit log cho login/logout, đổi mật khẩu, đăng ký lớp và review.
- Bổ sung mã hóa/giải mã dữ liệu y tế qua Laravel `Crypt` trong model `Student`.
- Làm cho các migration framework và migration `student_count` an toàn khi schema đã chứa bảng/cột.
- Đồng bộ `program_reviews` và `student_count` vào `schema.sql` và tái sinh `seed.sql`.
- Frontend gọi review eligibility và recommendation đúng lúc; thêm lớp CSRF token cho các request thay đổi dữ liệu.
- Blade UI dùng asset tĩnh ổn định trong `backend/public/assets`, không phụ thuộc `public/build` khi chạy backend local.
- Thêm Docker Compose, Dockerfiles và dev gateway.
- Loại bỏ secret/runtime/build artifact khỏi gói source phát hành.

## Kiểm tra đã thực hiện trong môi trường build

- PHP lint: không phát hiện lỗi cú pháp trong `app`, `routes`, `config`, `migrations`, `tests`.
- Schema/model: không còn field `$fillable` nào không tồn tại trong `schema.sql`.
- Vue SFC: 12 file source được parse/compile kiểm tra với Vue compiler, không có lỗi.
- JavaScript source: `node --check` PASS.
- Python: `py_compile` PASS.
- Docker Compose YAML: parse PASS.
- Seed generator: tạo 50 users, 60 programs, 120 schedules, 360 stops, 600 attendance records, 200 audit logs và 30 reviews.

## Các tiêu chí không được giả tạo

Thông tin thành viên nhóm, 40 commit/08 tuần, tỷ lệ đóng góp của từng thành viên, triển khai public HTTPS và video thuyết minh cần được cung cấp/thực hiện thật; source ZIP không tự tạo bằng chứng giả cho các mục này.
