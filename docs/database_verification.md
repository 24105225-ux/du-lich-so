# Database Verification — DT-17

## 1. So luong bang

Yeu cau:
- >= 10 thuc the nghiep vu

Ket qua:
- 25 bang

## 2. So luong du lieu

Nhom du lieu chinh:

- places
- programs
- program_schedules
- schedule_stops

Tong:
> 300 ban ghi

## 3. Quan he nhieu-nhieu

- parents — students
- programs — subjects
- programs — educational_requirements
- schedules — teachers
- schedules — places

## 4. Du lieu theo thoi gian

- program_schedules
- attendance_records
- parent_notifications
- learning_evaluations
- audit_logs

## 5. Chi muc

### Truy van

Loc lich trai nghiem theo khoang ngay va chi phi.

### Truoc index

- Type:
- Rows examined:
- Execution time:

### Sau index

- Type:
- Rows examined:
- Execution time:

## 6. Ket luan ky thuat

Index duoc tao tren cac cot co vai tro trong
tim kiem theo ngay va lien ket lich voi chuong trinh.
Ket qua EXPLAIN ANALYZE duoc ghi nhan tu cung mot cau
truy van truoc va sau khi tao index.
