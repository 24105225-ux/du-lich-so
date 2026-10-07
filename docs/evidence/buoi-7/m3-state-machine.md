# BUỔI 7 – M3 ORDER STATE MACHINE

## Trạng thái chính

PENDING -> PAID
PENDING -> CANCELLED
PAID -> REFUNDED

## Payment thử nghiệm

### Thành công
- Payment: `success`
- Order: `paid`
- Registration: `approved`
- `hold_expires_at`: `NULL`

### Thất bại
- Payment: `failed`
- Order: `pending`
- Inventory không bị trừ sai

### Hủy
- Payment: `cancelled`
- Order: `pending`
- Registration vẫn giữ trạng thái pending

### Refund
- Chỉ cho phép refund từ `paid`
- Refund từ `pending` bị từ chối
- `paid -> refunded`

## Transaction

Luồng tạo order, payment và thay đổi registration sử dụng database transaction
và row locking ở các điểm cần thiết.

## Python fallback

Laravel gọi Python recommendation service bằng `programId`.
Khi Python không truy cập được, `PythonDataService` trả về dữ liệu fallback
và hệ thống không phát sinh HTTP 500.
