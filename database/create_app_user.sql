-- database/create_app_user.sql
-- THAY <ĐIỀN THÔNG TIN THẬT CỦA BẠN>
-- bằng mật khẩu mạnh dành riêng cho database app.

CREATE USER IF NOT EXISTS 'dulichso_app'@'127.0.0.1'
IDENTIFIED BY '';

GRANT SELECT, INSERT, UPDATE, DELETE
ON dulichso.*
TO 'dulichso_app'@'127.0.0.1';

FLUSH PRIVILEGES;
