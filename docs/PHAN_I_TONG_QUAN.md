# PHẦN I — TỔNG QUAN VÀ NGĂN XẾP CÔNG NGHỆ

## Đề tài

ĐT-17 — Nền tảng du lịch học đường và chương trình trải nghiệm giáo dục

## Công nghệ tham chiếu

PHP 8.3
Laravel 11
MySQL 8.0
Vue 3
Vite
Bootstrap 5.3
Python 3.12
pandas
scikit-learn
FastAPI
Nginx 1.24
Docker
Docker Compose

## Kiến trúc logic

Frontend
    ↓
Laravel Backend
    ↓
MySQL

Laravel Backend
    ↓
FastAPI
    ↓
Python Data Processing

## Yêu cầu đặc thù ĐT-17

Nhà trường
Phụ huynh
Đơn vị tổ chức

Các nhóm chức năng:

- Quản lý chương trình trải nghiệm.
- Gắn chương trình với cấp học.
- Gắn chương trình với môn học.
- Đăng ký theo lớp.
- Phiếu đồng ý của phụ huynh.
- Danh sách học sinh.
- Thông tin y tế cơ bản.
- Phân công giáo viên.
- Điểm danh theo chặng.
- Thông báo trong hành trình.
- Đánh giá kết quả học tập.
- Hồ sơ an toàn.

## Nguyên tắc dữ liệu

Toàn bộ dữ liệu học sinh sử dụng trong hệ thống là dữ liệu mô phỏng.

Không sử dụng dữ liệu cá nhân thật.

< BỔ SUNG ẢNH >
Hình kiến trúc tổng thể hệ thống sẽ bổ sung ở giai đoạn thiết kế kiến trúc.

< BỔ SUNG AUDIO >
Không áp dụng riêng cho Phần I. Audio phục vụ video minh chứng cuối dự án.