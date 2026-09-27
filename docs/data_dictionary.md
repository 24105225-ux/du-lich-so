# DATA DICTIONARY — ĐT-17

## 1. users

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa nghiệp vụ và ràng buộc |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã kỹ thuật tự tăng |
| email | VARCHAR(180) | Có | UK | Tài khoản đăng nhập, duy nhất |
| password_hash | VARCHAR(255) | Có | | Mật khẩu đã băm, không lưu mật khẩu gốc |
| role | ENUM | Có | | admin/school/parent/organizer |
| status | ENUM | Có | | active/locked |
| last_login_at | DATETIME | Không | | Thời điểm đăng nhập gần nhất |
| created_at | TIMESTAMP | Có | | Thời điểm tạo |

## 2. schools

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa nghiệp vụ và ràng buộc |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã trường |
| user_id | BIGINT UNSIGNED | Có | FK, UK | Tài khoản quản trị trường |
| name | VARCHAR(180) | Có | | Tên trường |
| province | VARCHAR(80) | Có | | Tỉnh/thành |
| address | VARCHAR(255) | Có | | Địa chỉ mô phỏng |
| status | ENUM | Có | | pending/approved/suspended |
| created_at | TIMESTAMP | Có | | Thời điểm tạo |

## 3. organizers

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã đơn vị tổ chức |
| user_id | BIGINT UNSIGNED | Có | FK, UK | Tài khoản đơn vị |
| name | VARCHAR(180) | Có | | Tên đơn vị mô phỏng |
| license_code | VARCHAR(60) | Không | UK | Mã giấy phép mô phỏng |
| province | VARCHAR(80) | Có | | Khu vực hoạt động |
| status | ENUM | Có | | Trạng thái |

## 4. parents

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã phụ huynh |
| user_id | BIGINT UNSIGNED | Có | FK, UK | Tài khoản phụ huynh |
| full_name | VARCHAR(160) | Có | | Tên mô phỏng |
| phone | VARCHAR(20) | Có | | Điện thoại mô phỏng |
| relationship_note | VARCHAR(80) | Không | | Ghi chú quan hệ |

## 5. teachers

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã giáo viên |
| user_id | BIGINT UNSIGNED | Có | FK, UK | Tài khoản giáo viên |
| school_id | BIGINT UNSIGNED | Có | FK | Trường quản lý |
| full_name | VARCHAR(160) | Có | | Tên mô phỏng |
| department | VARCHAR(120) | Không | | Bộ môn |
| status | ENUM | Có | | active/inactive |

## 6. school_classes

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã lớp |
| school_id | BIGINT UNSIGNED | Có | FK | Trường |
| class_name | VARCHAR(80) | Có | UK cùng năm | Tên lớp |
| grade_level | VARCHAR(40) | Có | | Cấp học |
| academic_year | VARCHAR(20) | Có | | Năm học |
| status | ENUM | Có | | Trạng thái |

## 7. students

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa nghiệp vụ |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã học sinh |
| class_id | BIGINT UNSIGNED | Có | FK | Lớp |
| student_code | VARCHAR(40) | Có | UK | Mã học sinh mô phỏng |
| full_name | VARCHAR(160) | Có | | Tên mô phỏng |
| birth_year | SMALLINT UNSIGNED | Không | | Chỉ lưu năm để giảm dữ liệu |
| medical_info_encrypted | TEXT | Không | | Thông tin y tế mô phỏng đã mã hóa |
| created_at | TIMESTAMP | Có | | Thời điểm tạo |

## 8. parent_student

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| parent_id | BIGINT UNSIGNED | Có | PK/FK | Phụ huynh |
| student_id | BIGINT UNSIGNED | Có | PK/FK | Học sinh |
| relationship_type | ENUM | Có | | father/mother/guardian/other |
| is_primary | BOOLEAN | Có | | Người đại diện chính |

## 9. subjects

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã môn |
| code | VARCHAR(30) | Có | UK | Mã môn |
| name | VARCHAR(160) | Có | | Tên môn |
| education_level | VARCHAR(80) | Có | | Cấp học |

## 10. educational_requirements

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã yêu cầu |
| subject_id | BIGINT UNSIGNED | Có | FK | Môn học |
| code | VARCHAR(60) | Có | UK | Mã yêu cầu cần đạt |
| description | TEXT | Có | | Nội dung yêu cầu |
| education_level | VARCHAR(80) | Có | | Cấp học |

## 11. places

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã địa điểm |
| name | VARCHAR(180) | Có | | Tên điểm |
| province | VARCHAR(80) | Có | | Tỉnh/thành |
| lat | DECIMAL(10,7) | Không | | Vĩ độ |
| lng | DECIMAL(10,7) | Không | | Kinh độ |
| best_season | VARCHAR(80) | Không | | Mùa phù hợp |
| visit_minutes | SMALLINT | Không | | Thời gian tham quan |
| description | TEXT | Không | | Mô tả |
| source_note | VARCHAR(255) | Không | | Nguồn dữ liệu phải ghi rõ trong báo cáo |

## 12. programs

| Trường | Kiểu | Bắt buộc | Khóa | Ý nghĩa |
|---|---|---|---|---|
| id | BIGINT UNSIGNED | Có | PK | Mã chương trình |
| organizer_id | BIGINT UNSIGNED | Có | FK | Đơn vị tổ chức |
| code | VARCHAR(40) | Có | UK | Mã chương trình |
| name | VARCHAR(220) | Có | | Tên chương trình |
| education_level | VARCHAR(80) | Có | | Cấp học |
| base_cost_per_student | DECIMAL(12,2) | Có | | Chi phí cơ bản/học sinh |
| duration_days | TINYINT | Có | | Số ngày |
| capacity | SMALLINT | Có | | Sức chứa |
| status | ENUM | Có | | draft/published/archived |
| description | TEXT | Không | | Mô tả |

## 13. program_subjects

Bảng liên kết chương trình và môn học.

## 14. program_requirements

Bảng liên kết chương trình và yêu cầu cần đạt.

## 15. program_schedules

Bảng lịch tổ chức thực tế theo ngày/thời gian.

## 16. schedule_stops

Danh sách điểm dừng theo thứ tự của một lịch.

## 17. class_registrations

Đăng ký chương trình theo lớp.

## 18. parent_consents

Phiếu đồng ý của phụ huynh cho từng học sinh.

## 19. teacher_assignments

Phân công giáo viên cho từng lịch.

## 20. attendance_points

Các điểm checkpoint.

## 21. attendance_records

Bản ghi điểm danh theo học sinh và checkpoint.

## 22. parent_notifications

Thông báo hành trình dành cho phụ huynh.

## 23. learning_evaluations

Đánh giá kết quả học tập sau trải nghiệm.

## 24. safety_profiles

Hồ sơ an toàn của từng lịch trải nghiệm.

## 25. audit_logs

Ghi vết thao tác quản trị.
