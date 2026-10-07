# database/seed_data.py
# CSE703073 - DT-17
# Sinh du lieu mo phong cho he thong du lich hoc duong.
#
# Quy uoc:
# - Khong su dung du lieu hoc sinh that.
# - Du lieu nguoi dung la du lieu mo phong.
# - Danh sach dia diem du lich ke thua bo dia diem tham chieu
#   trong tai lieu ky thuat; nhom phai ghi ro nguon trong bao cao.
# - Ket qua duoc sinh co dinh de co the tai lap.
#
# Chay bang PowerShell:
# python .\database\seed_data.py
#
# File ket qua:
# database\seed.sql

from __future__ import annotations

from datetime import date, datetime, time, timedelta
from pathlib import Path
import random


SEED = 703073
random.seed(SEED)

BASE_DIR = Path(__file__).resolve().parent
OUTPUT_FILE = BASE_DIR / "seed.sql"


def sql_escape(value: object) -> str:
    text = str(value)
    return text.replace("'", "''")


def sql_text(value: str | None) -> str:
    if value is None:
        return "NULL"
    return f"'{sql_escape(value)}'"


def sql_number(value: int | float | None) -> str:
    if value is None:
        return "NULL"
    return str(value)


def sql_bool(value: bool) -> int:
    return 1 if value else 0


# ---------------------------------------------------------
# 1. Users
# ---------------------------------------------------------

# Mat khau kiem thu mo phong:
# ChangeMe123!
# Hash bcrypt duoc tao san de dung cho moi truong hoc tap.
PASSWORD_HASH = (
    "$2y$12$nSxNqZutf8OPJZ//Aj5aQuswSuOxAIaUOJnIY0JpvShTxBhZ5um.q"
)

users: list[str] = []
schools: list[str] = []
organizers: list[str] = []
parents: list[str] = []
teachers: list[str] = []
classes: list[str] = []
students: list[str] = []
parent_student: list[str] = []

subjects: list[str] = []
requirements: list[str] = []
places: list[str] = []
programs: list[str] = []
program_subjects: list[str] = []
program_requirements: list[str] = []

schedules: list[str] = []
schedule_stops: list[str] = []
registrations: list[str] = []
consents: list[str] = []
assignments: list[str] = []
attendance_points: list[str] = []
attendance_records: list[str] = []
notifications: list[str] = []
evaluations: list[str] = []
safety_profiles: list[str] = []
audit_logs: list[str] = []
program_reviews: list[str] = []


# ---------------------------------------------------------
# 2. Users
# ---------------------------------------------------------

users.append(
    "INSERT INTO users "
    "(id,email,password_hash,role,status) VALUES "
    f"(1,'admin@demo.test','{PASSWORD_HASH}','admin','active');"
)

user_id = 1

for i in range(1, 4):
    user_id += 1

    users.append(
        "INSERT INTO users "
        "(id,email,password_hash,role,status) VALUES "
        f"({user_id},'school{i}@demo.test','{PASSWORD_HASH}',"
        "'school','active');"
    )

for i in range(1, 31):
    user_id += 1

    users.append(
        "INSERT INTO users "
        "(id,email,password_hash,role,status) VALUES "
        f"({user_id},'phuhuynh{i:03d}@demo.test','{PASSWORD_HASH}',"
        "'parent','active');"
    )

for i in range(1, 5):
    user_id += 1

    users.append(
        "INSERT INTO users "
        "(id,email,password_hash,role,status) VALUES "
        f"({user_id},'donvitochuc{i}@demo.test','{PASSWORD_HASH}',"
        "'organizer','active');"
    )


# ---------------------------------------------------------
# 3. Schools
# ---------------------------------------------------------

school_locations = [
    ("Truong Tieu hoc Mo Phong 01", "Ha Noi", "Ha Noi"),
    ("Truong THCS Mo Phong 02", "Da Nang", "Da Nang"),
    ("Truong THPT Mo Phong 03", "Quang Ninh", "Quang Ninh"),
]

for i, (name, province, city) in enumerate(school_locations, start=1):
    user_id_for_school = i + 1

    schools.append(
        "INSERT INTO schools "
        "(id,user_id,name,province,address,status) VALUES "
        f"({i},{user_id_for_school},'{sql_escape(name)}',"
        f"'{province}','Dia chi mo phong {city}','approved');"
    )


# ---------------------------------------------------------
# 4. Organizers
# ---------------------------------------------------------

organizer_names = [
    "Don vi trai nghiem giao duc Mo Phong 01",
    "Don vi trai nghiem giao duc Mo Phong 02",
    "Don vi trai nghiem giao duc Mo Phong 03",
    "Don vi trai nghiem giao duc Mo Phong 04",
]

for i, name in enumerate(organizer_names, start=1):
    organizer_user_id = 34 + i

    organizers.append(
        "INSERT INTO organizers "
        "(id,user_id,name,license_code,province,status) VALUES "
        f"({i},{organizer_user_id},'{sql_escape(name)}',"
        f"'SIM-EDU-{i:03d}','Ha Noi','approved');"
    )


# ---------------------------------------------------------
# 5. Parents
# ---------------------------------------------------------

parent_start_user = 5

for i in range(1, 31):
    parent_id = i
    parent_user_id = parent_start_user + i - 1

    parents.append(
        "INSERT INTO parents "
        "(id,user_id,full_name,phone,relationship_note) VALUES "
        f"({parent_id},{parent_user_id},"
        f"'Phu huynh mo phong {i:03d}',"
        f"'090{i:07d}','Dai dien hop phap mo phong');"
    )


# ---------------------------------------------------------
# 6. Teachers
# ---------------------------------------------------------

teacher_id = 0

for school_id in range(1, 4):
    for local_index in range(1, 5):
        teacher_id += 1

        teacher_user_id = 34 + 4 + teacher_id

        teachers.append(
            "INSERT INTO teachers "
            "(id,user_id,school_id,full_name,department,status) "
            "VALUES "
            f"({teacher_id},{teacher_user_id},{school_id},"
            f"'Giao vien mo phong {teacher_id:03d}',"
            "'Bo mon tong hop','active');"
        )

        # Tao them user cho giao vien
        users.append(
            "INSERT INTO users "
            "(id,email,password_hash,role,status) VALUES "
            f"({teacher_user_id},"
            f"'giaovien{teacher_id:03d}@demo.test',"
            f"'{PASSWORD_HASH}','school','active');"
        )


# ---------------------------------------------------------
# 7. Classes
# ---------------------------------------------------------

class_id = 0
for school_id in range(1, 4):
    for local_index in range(1, 5):
        class_id += 1

        grade = (
            "Tieu hoc"
            if school_id == 1
            else "THCS"
            if school_id == 2
            else "THPT"
        )

        classes.append(
            "INSERT INTO school_classes "
            "(id,school_id,class_name,grade_level,academic_year,status) "
            "VALUES "
            f"({class_id},{school_id},"
            f"'Lop {local_index:02d}','{grade}','2026-2027','active');"
        )


# ---------------------------------------------------------
# 8. Students
# ---------------------------------------------------------

student_id = 0
student_parent_pairs: list[tuple[int, int]] = []

for current_class_id in range(1, 13):
    for local_index in range(1, 11):
        student_id += 1

        students.append(
            "INSERT INTO students "
            "(id,class_id,student_code,full_name,birth_year,"
            "medical_info_encrypted) VALUES "
            f"({student_id},{current_class_id},"
            f"'HS-{student_id:04d}',"
            f"'Hoc sinh mo phong {student_id:04d}',"
            f"{2010 + (student_id % 7)},"
            "'ENCRYPTED_SIMULATED_MEDICAL_DATA');"
        )

        parent_id = ((student_id - 1) % 30) + 1

        student_parent_pairs.append(
            (parent_id, student_id)
        )

        parent_student.append(
            "INSERT INTO parent_student "
            "(parent_id,student_id,relationship_type,is_primary) "
            "VALUES "
            f"({parent_id},{student_id},'guardian',{1});"
        )

        if student_id % 5 == 0:
            second_parent_id = (parent_id % 30) + 1

            parent_student.append(
                "INSERT INTO parent_student "
                "(parent_id,student_id,relationship_type,is_primary) "
                "VALUES "
                f"({second_parent_id},{student_id},'guardian',0);"
            )


# ---------------------------------------------------------
# 9. Subjects
# ---------------------------------------------------------

subject_data = [
    ("TOAN", "Toan hoc"),
    ("NGU_VAN", "Ngu van"),
    ("KHOA_HOC", "Khoa hoc tu nhien"),
    ("LICH_SU", "Lich su"),
    ("DIA_LY", "Dia ly"),
    ("SINH_HOC", "Sinh hoc"),
    ("GDKTPL", "Giao duc kinh te va phap luat"),
    ("TIN_HOC", "Tin hoc"),
]

for subject_id, (code, name) in enumerate(subject_data, start=1):
    subjects.append(
        "INSERT INTO subjects "
        "(id,code,name,education_level) VALUES "
        f"({subject_id},'{code}','{name}','Tieu hoc/THCS/THPT');"
    )


# ---------------------------------------------------------
# 10. Educational requirements
# ---------------------------------------------------------

requirement_id = 0

for subject_id in range(1, 9):
    for local_index in range(1, 3):
        requirement_id += 1

        requirements.append(
            "INSERT INTO educational_requirements "
            "(id,subject_id,code,description,education_level) VALUES "
            f"({requirement_id},{subject_id},"
            f"'YC{requirement_id:03d}',"
            f"'Yeu cau can dat mo phong {requirement_id:03d}',"
            "'THCS/THPT');"
        )


# ---------------------------------------------------------
# 11. Places
# ---------------------------------------------------------
# Danh sach giu theo bo du lieu tham chieu cua tai lieu ky thuat.
# Khi nộp bài, source_note trong báo cáo phải ghi rõ nguồn.

place_data = [
    ("Vinh Ha Long", "Quang Ninh", 20.9101, 107.1839),
    ("Quan the Trang An", "Ninh Binh", 20.2506, 105.8956),
    ("Pho co Hoi An", "Da Nang", 15.8801, 108.3380),
    ("Kinh thanh Hue", "Hue", 16.4698, 107.5769),
    ("Ba Na Hills", "Da Nang", 15.9950, 107.9967),
    ("Vuon quoc gia Phong Nha", "Quang Tri", 17.5333, 106.1167),
    ("Bai bien My Khe", "Da Nang", 16.0605, 108.2470),
    ("Cao nguyen Moc Chau", "Son La", 20.8333, 104.6333),
    ("Thi tran Sa Pa", "Lao Cai", 22.3364, 103.8438),
    ("Ho Ba Be", "Thai Nguyen", 22.4000, 105.6167),
    ("Dao Phu Quoc", "An Giang", 10.2270, 103.9670),
    ("Bien Nha Trang", "Khanh Hoa", 12.2388, 109.1967),
    ("Cao nguyen Da Lat", "Lam Dong", 11.9404, 108.4583),
    ("Cho noi Cai Rang", "Can Tho", 10.0089, 105.7469),
    ("Dinh Doc Lap", "TP Ho Chi Minh", 10.7772, 106.6958),
    ("Ho Hoan Kiem", "Ha Noi", 21.0287, 105.8524),
    ("Lang Co Loa", "Ha Noi", 21.1189, 105.8756),
    ("Bien Cua Lo", "Nghe An", 18.8000, 105.7167),
    ("Thanh Nha Ho", "Thanh Hoa", 20.0806, 105.6019),
    ("Cong vien dia chat Dak Nong", "Dak Lak", 12.2500, 107.6833),
]

for place_id, (name, province, lat, lng) in enumerate(place_data, start=1):
    places.append(
        "INSERT INTO places "
        "(id,name,province,lat,lng,best_season,visit_minutes,"
        "description,source_note) VALUES "
        f"({place_id},'{sql_escape(name)}','{province}',"
        f"{lat},{lng},'Quanh nam',180,"
        f"'Dia diem mo phong phuc vu chuong trinh trai nghiem.',"
        f"'Du lieu tham chieu mon hoc; bo sung nguon chinh thuc trong bao cao');"
    )


# ---------------------------------------------------------
# 12. Programs
# ---------------------------------------------------------

education_levels = [
    "Tieu hoc",
    "THCS",
    "THPT",
]

program_names = [
    "Trai nghiem khoa hoc ngoai lop hoc",
    "Kham pha di san va lich su",
    "Trai nghiem dia ly thuc dia",
    "Hoc tap sinh thai",
    "Ky nang song trong hanh trinh",
    "Cong nghe va du lich so",
]

program_id = 0

for organizer_id in range(1, 5):
    for local_index in range(1, 16):
        program_id += 1

        level = education_levels[(program_id - 1) % len(education_levels)]

        base_cost = (
            450000
            + ((program_id - 1) % 6) * 75000
        )

        duration_days = 1 + ((program_id - 1) % 3)

        programs.append(
            "INSERT INTO programs "
            "(id,organizer_id,code,name,education_level,"
            "base_cost_per_student,duration_days,capacity,status,"
            "description) VALUES "
            f"({program_id},{organizer_id},"
            f"'DT17-P{program_id:03d}',"
            f"'{sql_escape(program_names[(program_id - 1) % len(program_names)])} "
            f"{program_id:03d}',"
            f"'{level}',{base_cost},{duration_days},"
            f"{20 + ((program_id - 1) % 5) * 5},"
            "'published',"
            "'Chuong trinh trai nghiem giao duc mo phong cho DT-17.');"
        )

        # 2 mon hoc / program
        subject_a = ((program_id - 1) % 8) + 1
        subject_b = (program_id % 8) + 1

        program_subjects.append(
            "INSERT INTO program_subjects "
            "(program_id,subject_id,learning_role) VALUES "
            f"({program_id},{subject_a},"
            "'Muc tieu chinh');"
        )

        program_subjects.append(
            "INSERT INTO program_subjects "
            "(program_id,subject_id,learning_role) VALUES "
            f"({program_id},{subject_b},"
            "'Muc tieu lien mon');"
        )

        requirement_a = ((program_id - 1) * 2 % 16) + 1
        requirement_b = (requirement_a % 16) + 1

        program_requirements.append(
            "INSERT INTO program_requirements "
            "(program_id,requirement_id,mapping_note) VALUES "
            f"({program_id},{requirement_a},"
            "'Anh xa muc tieu trai nghiem voi yeu cau can dat mo phong.');"
        )

        program_requirements.append(
            "INSERT INTO program_requirements "
            "(program_id,requirement_id,mapping_note) VALUES "
            f"({program_id},{requirement_b},"
            "'Anh xa bo sung theo hoat dong trai nghiem.');"
        )


# ---------------------------------------------------------
# 13. Program schedules
# ---------------------------------------------------------

schedule_id = 0
base_date = date(2026, 11, 15)

for current_program_id in range(1, 61):
    for copy_index in range(2):
        schedule_id += 1

        trip_date = base_date + timedelta(
            days=(current_program_id * 3) + (copy_index * 14)
        )

        start = time(7, 30)
        end = time(17, 30)

        season_multiplier = (
            1.20
            if trip_date.month in {1, 2, 3, 4, 7, 8, 12}
            else 0.90
        )

        base_cost = 450000 + (
            ((current_program_id - 1) % 6) * 75000
        )

        cost_override = round(
            base_cost * season_multiplier
            / 10000
        ) * 10000

        capacity = 25 if season_multiplier > 1 else 20

        schedules.append(
            "INSERT INTO program_schedules "
            "(id,program_id,trip_date,start_time,end_time,"
            "capacity,cost_override,status) VALUES "
            f"({schedule_id},{current_program_id},"
            f"'{trip_date.isoformat()}','{start}','{end}',"
            f"{capacity},{cost_override},'open');"
        )


# ---------------------------------------------------------
# 14. Schedule stops
# ---------------------------------------------------------

for current_schedule_id in range(1, schedule_id + 1):
    for seq_no in range(1, 4):
        place_id = (
            ((current_schedule_id - 1) * 3 + seq_no - 1)
            % len(place_data)
        ) + 1

        activities = [
            "Khoi dong va dat muc tieu hoc tap",
            "Hoat dong trai nghiem thuc dia",
            "Tong ket va phan hoi",
        ]

        schedule_stops.append(
            "INSERT INTO schedule_stops "
            "(schedule_id,place_id,seq_no,activity,duration_minutes) "
            "VALUES "
            f"({current_schedule_id},{place_id},{seq_no},"
            f"'{activities[seq_no - 1]}',"
            f"{90 + seq_no * 30});"
        )


# ---------------------------------------------------------
# 15. Class registrations
# ---------------------------------------------------------

registration_id = 0

# 24 dang ky lop, phan bo theo cac lich dau.
for class_id in range(1, 13):
    for offset in range(2):
        registration_id += 1

        schedule_target = ((class_id - 1) * 2 + offset) + 1

        registrations.append(
            "INSERT INTO class_registrations "
            "(id,class_id,schedule_id,student_count,status) VALUES "
            f"({registration_id},{class_id},"
            f"{schedule_target},10,'approved');"
        )


# ---------------------------------------------------------
# 16. Consents
# ---------------------------------------------------------

consent_id = 0

for registration_id in range(1, 25):
    class_id = ((registration_id - 1) // 2) + 1

    first_student = ((class_id - 1) * 10) + 1
    last_student = first_student + 9

    parent_id = ((registration_id - 1) % 30) + 1

    for student_id in range(first_student, last_student + 1):
        consent_id += 1

        status = (
            "approved"
            if student_id % 7 != 0
            else "pending"
        )

        consented_at = (
            "2026-10-15 08:00:00"
            if status == "approved"
            else None
        )

        consent_value = "NULL"

        if consented_at is not None:
            consent_value = f"'{consented_at}'"

        consents.append(
            "INSERT INTO parent_consents "
            "(id,registration_id,parent_id,student_id,"
            "consent_status,consented_at,note) VALUES "
            f"({consent_id},{registration_id},"
            f"{parent_id},{student_id},'{status}',"
            f"{consent_value},"
            "'Du lieu dong y mo phong.');"
        )


# ---------------------------------------------------------
# 17. Teacher assignments
# ---------------------------------------------------------

for current_schedule_id in range(1, schedule_id + 1):
    teacher_a = ((current_schedule_id - 1) % 12) + 1
    teacher_b = (current_schedule_id % 12) + 1

    assignments.append(
        "INSERT INTO teacher_assignments "
        "(schedule_id,teacher_id,assignment_role) VALUES "
        f"({current_schedule_id},{teacher_a},'lead');"
    )

    assignments.append(
        "INSERT INTO teacher_assignments "
        "(schedule_id,teacher_id,assignment_role) VALUES "
        f"({current_schedule_id},{teacher_b},'support');"
    )


# ---------------------------------------------------------
# 18. Attendance points
# ---------------------------------------------------------

attendance_point_id = 0

for current_schedule_id in range(1, schedule_id + 1):
    for seq_no in range(1, 4):
        attendance_point_id += 1

        checkpoint = datetime(
            2026, 12, 1, 7, 30
        ) + timedelta(
            days=current_schedule_id,
            hours=(seq_no - 1) * 4
        )

        attendance_points.append(
            "INSERT INTO attendance_points "
            "(id,schedule_id,seq_no,point_name,checkpoint_at) "
            "VALUES "
            f"({attendance_point_id},{current_schedule_id},"
            f"{seq_no},"
            f"'Chot diem danh {seq_no}',"
            f"'{checkpoint:%Y-%m-%d %H:%M:%S}');"
        )


# ---------------------------------------------------------
# 19. Attendance records
# ---------------------------------------------------------

attendance_record_id = 0

# Chi tao 10 hoc sinh cho cac schedule dau tien de khong lam
# seed qua lon.
for point_id in range(1, min(attendance_point_id, 60) + 1):
    schedule_ref = ((point_id - 1) // 3) + 1
    class_ref = ((schedule_ref - 1) % 12) + 1

    first_student = ((class_ref - 1) * 10) + 1

    for offset in range(10):
        attendance_record_id += 1

        current_student = first_student + offset

        statuses = [
            "present",
            "present",
            "present",
            "late",
            "excused",
        ]

        attendance_status = statuses[
            (attendance_record_id - 1) % len(statuses)
        ]

        recorder_user = ((attendance_record_id - 1) % 12) + 35

        attendance_records.append(
            "INSERT INTO attendance_records "
            "(id,attendance_point_id,student_id,"
            "attendance_status,recorded_by,recorded_at) VALUES "
            f"({attendance_record_id},{point_id},"
            f"{current_student},'{attendance_status}',"
            f"{recorder_user},"
            f"'2026-12-01 08:00:00');"
        )


# ---------------------------------------------------------
# 20. Parent notifications
# ---------------------------------------------------------

notification_id = 0

for current_schedule_id in range(1, min(schedule_id, 120) + 1):
    for n in range(2):
        notification_id += 1

        student_id = ((current_schedule_id - 1) % 120) + 1
        parent_id = ((student_id - 1) % 30) + 1

        notification_types = [
            "departure",
            "checkpoint",
        ]

        notification_type = notification_types[n]

        notifications.append(
            "INSERT INTO parent_notifications "
            "(id,parent_id,student_id,schedule_id,"
            "notification_type,title,message,"
            "sent_at,read_at) VALUES "
            f"({notification_id},{parent_id},"
            f"{student_id},{current_schedule_id},"
            f"'{notification_type}',"
            f"'Thong bao hanh trinh',"
            f"'Du lieu thong bao mo phong cho DT-17.',"
            "'2026-12-01 08:30:00',NULL);"
        )


# ---------------------------------------------------------
# 21. Learning evaluations
# ---------------------------------------------------------

evaluation_id = 0

for student_id in range(1, 121):
    schedule_ref = ((student_id - 1) % 24) + 1
    teacher_ref = ((student_id - 1) % 12) + 1

    score = 6.5 + ((student_id * 7) % 35) / 10

    evaluation_id += 1

    evaluations.append(
        "INSERT INTO learning_evaluations "
        "(id,student_id,schedule_id,teacher_id,"
        "learning_score,learning_outcome,evaluated_at) VALUES "
        f"({evaluation_id},{student_id},"
        f"{schedule_ref},{teacher_ref},"
        f"{score:.2f},"
        f"'Danh gia hoc tap mo phong sau chuyen di.',"
        "'2026-12-15 16:00:00');"
    )


# ---------------------------------------------------------
# 22. Safety profiles
# ---------------------------------------------------------

for current_schedule_id in range(1, schedule_id + 1):
    risk_level = (
        "high"
        if current_schedule_id % 10 == 0
        else "medium"
        if current_schedule_id % 3 == 0
        else "low"
    )

    safety_profiles.append(
        "INSERT INTO safety_profiles "
        "(schedule_id,risk_level,first_aid_plan,"
        "emergency_plan,weather_threshold,"
        "emergency_contact,reviewed_at) VALUES "
        f"({current_schedule_id},'{risk_level}',"
        "'Bo tui so cuu va nguoi phu trach so cuu mo phong.',"
        "'Ke hoach lien lac va di chuyen thay the mo phong.',"
        "'Dung hoat dong khi dieu kien thoi tiet khong an toan.',"
        "'So lien he khan cap mo phong',"
        "'2026-10-30 09:00:00');"
    )


# ---------------------------------------------------------
# 23. Audit logs
# ---------------------------------------------------------

for audit_id in range(1, 201):
    actor_id = ((audit_id - 1) % 12) + 1

    audit_logs.append(
        "INSERT INTO audit_logs "
        "(id,actor_id,action,entity,entity_id,"
        "before_json,after_json,ip_address) VALUES "
        f"({audit_id},{actor_id},"
        "'CREATE','program',"
        f"{((audit_id - 1) % 60) + 1},"
        "NULL,"
        "'{\"status\":\"published\"}',"
        "'127.0.0.1');"
    )




# ---------------------------------------------------------
# 24. Program reviews
# ---------------------------------------------------------

for review_id in range(1, 31):
    program_ref = review_id
    parent_user_id = review_id + 4
    rating = 3 + (review_id % 3)

    program_reviews.append(
        "INSERT INTO program_reviews "
        "(id,program_id,user_id,rating,comment) VALUES "
        f"({review_id},{program_ref},{parent_user_id},"
        f"{rating},'Danh gia mo phong cho chuong trinh {program_ref:03d}.');"
    )

# ---------------------------------------------------------
# 25. Write SQL file
# ---------------------------------------------------------

sql_lines = [
    "USE dulichso;",
    "SET FOREIGN_KEY_CHECKS = 0;",
]

sections = [
    users,
    schools,
    organizers,
    parents,
    teachers,
    classes,
    students,
    parent_student,
    subjects,
    requirements,
    places,
    programs,
    program_subjects,
    program_requirements,
    schedules,
    schedule_stops,
    registrations,
    consents,
    assignments,
    attendance_points,
    attendance_records,
    notifications,
    evaluations,
    safety_profiles,
    audit_logs,
    program_reviews,
]

for section in sections:
    sql_lines.extend(section)

sql_lines.append("SET FOREIGN_KEY_CHECKS = 1;")

OUTPUT_FILE.write_text(
    "\n".join(sql_lines) + "\n",
    encoding="utf-8",
)

print("Da tao:", OUTPUT_FILE)
print("Users:", len(users))
print("Schools:", len(schools))
print("Organizers:", len(organizers))
print("Parents:", len(parents))
print("Teachers:", len(teachers))
print("Classes:", len(classes))
print("Students:", len(students))
print("Parent-Student:", len(parent_student))
print("Subjects:", len(subjects))
print("Requirements:", len(requirements))
print("Places:", len(places))
print("Programs:", len(programs))
print("Program-Subjects:", len(program_subjects))
print("Program-Requirements:", len(program_requirements))
print("Schedules:", len(schedules))
print("Schedule-Stops:", len(schedule_stops))
print("Registrations:", len(registrations))
print("Consents:", len(consents))
print("Teacher assignments:", len(assignments))
print("Attendance points:", len(attendance_points))
print("Attendance records:", len(attendance_records))
print("Notifications:", len(notifications))
print("Evaluations:", len(evaluations))
print("Safety profiles:", len(safety_profiles))
print("Audit logs:", len(audit_logs))
print("Program reviews:", len(program_reviews))
