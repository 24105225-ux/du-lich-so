# Kế hoạch kiểm thử 60 Test Cases – CSE703073 / ĐT-17

> Phạm vi: các chức năng thực tế đang có trong source dự án.
> Đây là kế hoạch kiểm thử phục vụ Buổi 4, không phải tuyên bố rằng toàn bộ 60 ca đã được tự động hóa.

| ID | Nhóm | Test case | Kết quả mong đợi |
|---|---|---|---|
| TC-01 | Authentication | Mở trang đăng ký | HTTP 200, hiển thị form |
| TC-02 | Authentication | Trang đăng ký có CSRF | Có trường `_token` |
| TC-03 | Authentication | Đăng ký email/mật khẩu hợp lệ | Tạo tài khoản, chuyển đăng nhập |
| TC-04 | Authentication | Đăng ký email đã tồn tại | Từ chối, báo email đã sử dụng |
| TC-05 | Authentication | Đăng ký email sai định dạng | Validation thất bại |
| TC-06 | Authentication | Mật khẩu dưới 8 ký tự | Validation thất bại |
| TC-07 | Authentication | Xác nhận mật khẩu không khớp | Validation thất bại |
| TC-08 | Authentication | Kiểm tra role sau đăng ký | Tài khoản mới có role `parent` |
| TC-09 | Authentication | Mở trang đăng nhập | HTTP 200 |
| TC-10 | Authentication | Đăng nhập bằng tài khoản hợp lệ | Tạo session và chuyển dashboard |
| TC-11 | Session/RBAC | Truy cập dashboard khi chưa đăng nhập | Không được truy cập |
| TC-12 | Session/RBAC | Session được regenerate sau login | Session ID được thay đổi |
| TC-13 | Session/RBAC | Đăng xuất | Session bị invalidate |
| TC-14 | Session/RBAC | Session được regenerate CSRF khi logout | CSRF token được cấp lại |
| TC-15 | Session/RBAC | Phụ huynh truy cập khu vực nhà trường | HTTP 403 |
| TC-16 | Session/RBAC | Organizer truy cập khu vực nhà trường | HTTP 403 |
| TC-17 | Program | Mở danh mục chương trình | HTTP 200 |
| TC-18 | Program | Lấy danh sách chương trình qua API | HTTP 200, có dữ liệu |
| TC-19 | Program | Xem chi tiết chương trình hợp lệ | HTTP 200 |
| TC-20 | Program | Xem chương trình không tồn tại | HTTP 404 |
| TC-21 | Program | Tìm chương trình theo keyword | Trả về dữ liệu phù hợp |
| TC-22 | Program | Lọc theo education level | Chỉ trả về đúng cấp học |
| TC-23 | Program | Lọc min price hợp lệ | Không có giá thấp hơn min |
| TC-24 | Program | Lọc max price hợp lệ | Không có giá cao hơn max |
| TC-25 | Program | max price nhỏ hơn min price | Validation thất bại |
| TC-26 | Program | per_page hợp lệ | Số bản ghi/trang đúng giới hạn |
| TC-27 | Recommendation | Gọi recommendation cho program hợp lệ | HTTP 200 |
| TC-28 | Recommendation | Recommendation trả về tối đa 6 mục | Không vượt quá `k=6` |
| TC-29 | Recommendation | Python service health check | HTTP 200, status `ok` |
| TC-30 | Recommendation | Python recommendation trực tiếp | HTTP 200, có items |
| TC-31 | Registration | Người dùng hợp lệ mở form đăng ký lớp | Hiển thị form |
| TC-32 | Registration | Người chưa đăng nhập mở form đăng ký lớp | Không được truy cập |
| TC-33 | Registration | School mở remaining seats | Trả về số chỗ còn lại |
| TC-34 | Registration | Kiểm tra remaining seats với class không hợp lệ | Validation/xử lý lỗi phù hợp |
| TC-35 | Registration | Đăng ký lớp với dữ liệu hợp lệ | Tạo `class_registration` |
| TC-36 | Registration | Đăng ký vượt sức chứa | Từ chối đăng ký |
| TC-37 | Registration | student_count nhỏ hơn 1 | Validation thất bại |
| TC-38 | Registration | Thiếu class_id | Validation thất bại |
| TC-39 | Registration | Thiếu program_schedule_id | Validation thất bại |
| TC-40 | Registration | Mở trang kết quả bằng token hợp lệ | Hiển thị kết quả |
| TC-41 | Registration | Token kết quả bị sửa/không hợp lệ | HTTP 404 |
| TC-42 | Registration | Người không có quyền xem registration | HTTP 403 |
| TC-43 | Review | Người đã đăng nhập kiểm tra review eligibility | API trả kết quả eligibility |
| TC-44 | Review | Người chưa đăng nhập kiểm tra eligibility | Không được truy cập |
| TC-45 | Review | Gửi review với dữ liệu hợp lệ | Review được tạo |
| TC-46 | Review | Gửi review thiếu dữ liệu bắt buộc | Validation thất bại |
| TC-47 | Review | Gửi review cho program không tồn tại | HTTP 404 |
| TC-48 | Place CRUD | Admin lấy danh sách places | HTTP 200 |
| TC-49 | Place CRUD | Organizer lấy danh sách places | HTTP 200 |
| TC-50 | Place CRUD | Parent gọi API quản lý places | HTTP 403 |
| TC-51 | Place CRUD | Admin tạo Place hợp lệ | HTTP 201, bản ghi được tạo |
| TC-52 | Place CRUD | Tạo Place thiếu name/province | Validation thất bại |
| TC-53 | Place CRUD | Xem một Place hợp lệ | HTTP 200 |
| TC-54 | Place CRUD | Cập nhật Place hợp lệ | Dữ liệu được cập nhật |
| TC-55 | Place CRUD | Cập nhật Place với lat/lng ngoài phạm vi | Validation thất bại |
| TC-56 | Place CRUD | Xóa Place chưa được sử dụng | Place bị xóa |
| TC-57 | Place CRUD | Xóa Place đang được schedule sử dụng | HTTP 409 |
| TC-58 | Security | Login sai nhiều lần | Rate limiting được áp dụng |
| TC-59 | Security | Kiểm tra audit log khi Register/Login/Logout | Có bản ghi audit tương ứng |
| TC-60 | Data/Python | Chạy ETL clean ở chế độ dry-run | ETL xử lý dữ liệu và báo số bản ghi |