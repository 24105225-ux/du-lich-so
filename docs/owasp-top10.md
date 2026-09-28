# RÀ SOÁT OWASP TOP 10 — ĐT-17

## 1. Broken Access Control

Biện pháp:
- EnsureRole middleware
- ClassRegistrationPolicy
- Kiểm soát quyền tại tầng server
- Không chỉ ẩn nút trên giao diện

Kiểm thử:
- Parent truy cập /nha-truong/dashboard
- School A truy cập dữ liệu của School B

Kỳ vọng:
- HTTP 403

## 2. Cryptographic Failures

Biện pháp:
- bcrypt
- 12 rounds
- Không lưu mật khẩu rõ
- HTTPS khi production
- Secure cookie khi production

Kiểm thử:
- Kiểm tra password_hash trong users
- Kiểm tra chứng thư HTTPS

## 3. Injection

Biện pháp:
- Eloquent ORM
- Validation
- Allowlist cho sort
- Không nối chuỗi SQL trực tiếp

Kiểm thử:
- "' OR 1=1 --"
- "<script>alert(1)</script>"

## 4. Insecure Design

Biện pháp:
- Quy tắc sức chứa nằm trong Service
- Transaction + lockForUpdate
- Phân quyền theo vai trò
- Policy cho dữ liệu thuộc sở hữu

Kiểm thử:
- Hai phiên đăng ký cùng chỗ
- Truy cập chéo trường

## 5. Security Misconfiguration

Biện pháp:
- APP_DEBUG=false khi production
- Không commit .env
- Nginx chặn /.env
- Security headers

Kiểm thử:
- Truy cập /.env
- Kiểm tra HTTP response headers

## 6. Vulnerable Components

Biện pháp:
- composer.lock
- package-lock.json
- composer audit
- npm audit
- pip list --outdated

## 7. Identification and Authentication Failures

Biện pháp:
- Hash password
- Session regeneration
- Login throttle
- Logout invalidate session
- Logout other devices khi đổi mật khẩu

Kiểm thử:
- Nhập sai mật khẩu nhiều lần
- Kiểm tra session ID trước/sau login

## 8. Software and Data Integrity Failures

Biện pháp:
- Khóa phiên bản dependency
- Không đưa thư viện không kiểm soát
- Không commit secret
- Theo dõi composer.lock/package-lock.json

## 9. Security Logging and Monitoring Failures

Biện pháp:
- Log truy cập trái phép
- Log thao tác nhạy cảm
- Ghi user_id, role, path, IP

Kiểm thử:
- Truy cập endpoint không có quyền
- Kiểm tra laravel.log

## 10. Server-Side Request Forgery

ĐT-17 hiện không có chức năng cho người dùng nhập
URL tùy ý để server tải tài nguyên.

Biện pháp:
- Không triển khai chức năng fetch URL tùy ý
- Không cho server gọi địa chỉ nội bộ từ URL người dùng
- Nếu bổ sung tính năng này, phải dùng allowlist domain

Kết luận:
Hạng mục được kiểm soát bằng thiết kế.
