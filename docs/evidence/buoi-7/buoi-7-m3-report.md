# BUỔI 7 – M3

## Automated result

- M3 smoke total: 5
- M3 smoke PASS: 5
- M3 smoke FAIL: 0
- Full PHPUnit exit: 0
- B7 smoke exit: 0

## M3 test result

- TC-B7-01: PASS – Thanh toán thử nghiệm thành công
- TC-B7-02: PASS – Hủy giao dịch không làm sai inventory
- TC-B7-03: PASS – Từ chối refund trước thanh toán
- TC-B7-04: PASS – Python stop -> fallback, không 500
- TC-B7-05: PASS – Admin dashboard 4 KPI + 2 chart datasets

## Trial payment

Đã triển khai:

- tạo order
- trạng thái pending
- trial success
- trial failed
- trial cancelled
- paid
- refund
- từ chối refund sai trạng thái
- payment transaction record
- notification simulation
- order state machine

## Dashboard

4 indicators:

1. Tổng đơn hàng
2. Đơn đã thanh toán
3. Đơn chờ thanh toán
4. Doanh thu

2 datasets/charts:

1. Trạng thái đơn hàng
2. Doanh thu 7 ngày

Có CSV export.

## Python

Laravel gọi Python recommendation service bằng program ID.

Kiểm thử khi Python không khả dụng:

- endpoint test: http://127.0.0.1:8999
- expected: source=fallback
- result: PASS
- fallback items: 6

## Logging

Đã ghi nhận:

- DEBUG
- INFO
- WARNING

## Database backup

- File: dulichso-b7-20261007-204826.sql
- Size: 306344 bytes
- Option: --no-tablespaces
- orders table: present
- payments table: present

## Evidence

- m3-smoke-results.json
- http-smoke.json
- python-fallback-evidence.json
- 	hree-level-log-evidence.txt
- ackup.log
- ackup-header.txt
- m3-state-machine.md
- database backup .sql

## Manual evidence still required

Screenshots/video của giao diện thật cần được chụp trên máy phát triển; không sử dụng evidence giả lập.
