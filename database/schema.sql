-- database/schema.sql
-- CSE703073 - DT-17
-- Nen tang du lich hoc duong va chuong trinh trai nghiem giao duc
-- MySQL 8.0

CREATE DATABASE IF NOT EXISTS dulichso
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dulichso;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS program_reviews;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS safety_profiles;
DROP TABLE IF EXISTS learning_evaluations;
DROP TABLE IF EXISTS parent_notifications;
DROP TABLE IF EXISTS attendance_records;
DROP TABLE IF EXISTS attendance_points;
DROP TABLE IF EXISTS teacher_assignments;
DROP TABLE IF EXISTS parent_consents;
DROP TABLE IF EXISTS class_registrations;
DROP TABLE IF EXISTS schedule_stops;
DROP TABLE IF EXISTS program_schedules;
DROP TABLE IF EXISTS program_requirements;
DROP TABLE IF EXISTS program_subjects;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS educational_requirements;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS parent_student;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS school_classes;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS parents;
DROP TABLE IF EXISTS organizers;
DROP TABLE IF EXISTS schools;
DROP TABLE IF EXISTS users;

-- =========================================================
-- 1. USERS
-- =========================================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'school', 'parent', 'organizer')
        NOT NULL,
    status ENUM('active', 'locked')
        NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_users_role_status (role, status)
) ENGINE=InnoDB;

-- =========================================================
-- 2. SCHOOLS
-- =========================================================

CREATE TABLE schools (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    province VARCHAR(80) NOT NULL,
    address VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'suspended')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_schools_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_schools_province_status (province, status)
) ENGINE=InnoDB;

-- =========================================================
-- 3. ORGANIZERS
-- =========================================================

CREATE TABLE organizers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    license_code VARCHAR(60) NULL UNIQUE,
    province VARCHAR(80) NOT NULL,
    status ENUM('pending', 'approved', 'suspended')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_organizers_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_organizers_province_status (province, status)
) ENGINE=InnoDB;

-- =========================================================
-- 4. PARENTS
-- =========================================================

CREATE TABLE parents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    relationship_note VARCHAR(80) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_parents_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_parents_phone (phone)
) ENGINE=InnoDB;

-- =========================================================
-- 5. TEACHERS
-- =========================================================

CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    school_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    department VARCHAR(120) NULL,
    status ENUM('active', 'inactive')
        NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_teachers_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_teachers_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE RESTRICT,

    INDEX idx_teachers_school_status (school_id, status)
) ENGINE=InnoDB;

-- =========================================================
-- 6. SCHOOL CLASSES
-- =========================================================

CREATE TABLE school_classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id BIGINT UNSIGNED NOT NULL,
    class_name VARCHAR(80) NOT NULL,
    grade_level VARCHAR(40) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    status ENUM('active', 'archived')
        NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_classes_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_class (school_id, class_name, academic_year),

    INDEX idx_classes_grade_year (grade_level, academic_year)
) ENGINE=InnoDB;

-- =========================================================
-- 7. STUDENTS
-- =========================================================

CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    student_code VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,

    -- Chỉ lưu năm sinh nhằm giảm thiểu dữ liệu cá nhân
    birth_year SMALLINT UNSIGNED NULL,

    -- Dữ liệu y tế được mã hóa ở tầng ứng dụng Laravel
    medical_info_encrypted TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_students_class
        FOREIGN KEY (class_id)
        REFERENCES school_classes(id)
        ON DELETE CASCADE,

    INDEX idx_students_class (class_id)
) ENGINE=InnoDB;

-- =========================================================
-- 8. PARENT - STUDENT
-- =========================================================

CREATE TABLE parent_student (
    parent_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    relationship_type ENUM(
        'father',
        'mother',
        'guardian',
        'other'
    ) NOT NULL DEFAULT 'guardian',
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,

    PRIMARY KEY (parent_id, student_id),

    CONSTRAINT fk_parent_student_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_parent_student_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 9. SUBJECTS
-- =========================================================

CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    education_level VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

-- =========================================================
-- 10. EDUCATIONAL REQUIREMENTS
-- =========================================================

CREATE TABLE educational_requirements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(60) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    education_level VARCHAR(80) NOT NULL,

    CONSTRAINT fk_requirements_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE CASCADE,

    INDEX idx_requirements_subject (subject_id)
) ENGINE=InnoDB;

-- =========================================================
-- 11. PLACES
-- =========================================================

CREATE TABLE places (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    province VARCHAR(80) NOT NULL,
    lat DECIMAL(10, 7) NULL,
    lng DECIMAL(10, 7) NULL,
    best_season VARCHAR(80) NULL,
    visit_minutes SMALLINT UNSIGNED NULL,
    description TEXT NULL,

    -- Nguon du lieu phai duoc ghi ro trong bao cao
    source_note VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_places_province (province),
    INDEX idx_places_geo (lat, lng),
    FULLTEXT KEY ft_places (name, description)
) ENGINE=InnoDB;

-- =========================================================
-- 12. PROGRAMS
-- =========================================================

CREATE TABLE programs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(220) NOT NULL,
    education_level VARCHAR(80) NOT NULL,
    base_cost_per_student DECIMAL(12, 2) NOT NULL,
    duration_days TINYINT UNSIGNED NOT NULL DEFAULT 1,
    capacity SMALLINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'archived')
        NOT NULL DEFAULT 'draft',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_programs_organizer
        FOREIGN KEY (organizer_id)
        REFERENCES organizers(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_program_cost
        CHECK (base_cost_per_student >= 0),

    CONSTRAINT chk_program_capacity
        CHECK (capacity > 0),

    INDEX idx_program_filter (
        status,
        education_level,
        base_cost_per_student
    ),

    FULLTEXT KEY ft_program_name (name)
) ENGINE=InnoDB;

-- =========================================================
-- 13. PROGRAM - SUBJECT
-- =========================================================

CREATE TABLE program_subjects (
    program_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    learning_role VARCHAR(120) NULL,

    PRIMARY KEY (program_id, subject_id),

    CONSTRAINT fk_program_subject_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_program_subject_subject
        FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 14. PROGRAM - EDUCATIONAL REQUIREMENT
-- =========================================================

CREATE TABLE program_requirements (
    program_id BIGINT UNSIGNED NOT NULL,
    requirement_id BIGINT UNSIGNED NOT NULL,
    mapping_note TEXT NULL,

    PRIMARY KEY (program_id, requirement_id),

    CONSTRAINT fk_program_requirement_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_program_requirement_requirement
        FOREIGN KEY (requirement_id)
        REFERENCES educational_requirements(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 15. PROGRAM SCHEDULES
-- =========================================================

CREATE TABLE program_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id BIGINT UNSIGNED NOT NULL,
    trip_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,

    capacity SMALLINT UNSIGNED NOT NULL,
    cost_override DECIMAL(12, 2) NULL,

    status ENUM('planned', 'open', 'closed', 'completed', 'cancelled')
        NOT NULL DEFAULT 'planned',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_schedules_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_schedule_capacity
        CHECK (capacity > 0),

    CONSTRAINT chk_schedule_time
        CHECK (end_time > start_time),

    UNIQUE KEY uq_schedule (
        program_id,
        trip_date,
        start_time
    ),

    INDEX idx_schedules_date (trip_date),
    INDEX idx_schedules_program_date (program_id, trip_date)
) ENGINE=InnoDB;

-- =========================================================
-- 16. SCHEDULE STOPS
-- =========================================================

CREATE TABLE schedule_stops (
    schedule_id BIGINT UNSIGNED NOT NULL,
    place_id BIGINT UNSIGNED NOT NULL,
    seq_no TINYINT UNSIGNED NOT NULL,
    activity VARCHAR(220) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,

    PRIMARY KEY (schedule_id, seq_no),

    CONSTRAINT fk_schedule_stops_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_schedule_stops_place
        FOREIGN KEY (place_id)
        REFERENCES places(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uq_schedule_place (
        schedule_id,
        place_id,
        seq_no
    )
) ENGINE=InnoDB;

-- =========================================================
-- 17. CLASS REGISTRATIONS
-- =========================================================

CREATE TABLE class_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_class_schedule (class_id, schedule_id),
    student_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('pending', 'approved', 'cancelled', 'completed')
        NOT NULL DEFAULT 'pending',
    hold_expires_at TIMESTAMP NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reg_class
        FOREIGN KEY (class_id)
        REFERENCES school_classes(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_reg_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE RESTRICT,

    INDEX idx_reg_class_schedule_status (
        class_id,
        schedule_id,
    UNIQUE KEY uq_class_schedule (class_id, schedule_id),
        status
    ),

    INDEX idx_reg_hold_expires (hold_expires_at),

    INDEX idx_reg_status (status, registered_at)
) ENGINE=InnoDB;

-- =========================================================
-- 18. PARENT CONSENTS
-- =========================================================

CREATE TABLE parent_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    consent_status ENUM('pending', 'approved', 'rejected')
        NOT NULL DEFAULT 'pending',

    consented_at DATETIME NULL,
    note VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_consents_registration
        FOREIGN KEY (registration_id)
        REFERENCES class_registrations(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_consents_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_consents_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uq_consent (
        registration_id,
        parent_id,
        student_id
    ),

    INDEX idx_consents_status (consent_status)
) ENGINE=InnoDB;

-- =========================================================
-- 19. TEACHER ASSIGNMENTS
-- =========================================================

CREATE TABLE teacher_assignments (
    schedule_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,

    assignment_role ENUM('lead', 'support')
        NOT NULL DEFAULT 'support',

    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (schedule_id, teacher_id),

    CONSTRAINT fk_assign_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_assign_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES teachers(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 20. ATTENDANCE POINTS
-- =========================================================

CREATE TABLE attendance_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id BIGINT UNSIGNED NOT NULL,
    seq_no TINYINT UNSIGNED NOT NULL,
    point_name VARCHAR(160) NOT NULL,
    checkpoint_at DATETIME NOT NULL,

    CONSTRAINT fk_attendance_points_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_attendance_point (
        schedule_id,
        seq_no
    )
) ENGINE=InnoDB;

-- =========================================================
-- 21. ATTENDANCE RECORDS
-- =========================================================

CREATE TABLE attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_point_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    attendance_status ENUM(
        'present',
        'absent',
        'late',
        'excused'
    ) NOT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    recorded_at DATETIME NOT NULL,

    CONSTRAINT fk_attendance_record_point
        FOREIGN KEY (attendance_point_id)
        REFERENCES attendance_points(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attendance_record_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_attendance_record_user
        FOREIGN KEY (recorded_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uq_attendance_record (
        attendance_point_id,
        student_id
    ),

    INDEX idx_attendance_student (
        student_id,
        recorded_at
    )
) ENGINE=InnoDB;

-- =========================================================
-- 22. PARENT NOTIFICATIONS
-- =========================================================

CREATE TABLE parent_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,

    notification_type ENUM(
        'departure',
        'checkpoint',
        'delay',
        'safety',
        'return',
        'general'
    ) NOT NULL,

    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,

    sent_at DATETIME NOT NULL,
    read_at DATETIME NULL,

    CONSTRAINT fk_notification_parent
        FOREIGN KEY (parent_id)
        REFERENCES parents(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE,

    INDEX idx_notification_parent_time (
        parent_id,
        sent_at
    )
) ENGINE=InnoDB;

-- =========================================================
-- 23. LEARNING EVALUATIONS
-- =========================================================

CREATE TABLE learning_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    schedule_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,

    learning_score DECIMAL(4, 2) NOT NULL,
    learning_outcome TEXT NOT NULL,

    evaluated_at DATETIME NOT NULL,

    CONSTRAINT fk_evaluation_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_evaluation_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_evaluation_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES teachers(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_learning_score
        CHECK (learning_score BETWEEN 0 AND 10),

    INDEX idx_evaluation_schedule (
        schedule_id,
        evaluated_at
    )
) ENGINE=InnoDB;

-- =========================================================
-- 24. SAFETY PROFILES
-- =========================================================

CREATE TABLE safety_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id BIGINT UNSIGNED NOT NULL UNIQUE,

    risk_level ENUM('low', 'medium', 'high')
        NOT NULL DEFAULT 'medium',

    first_aid_plan TEXT NOT NULL,
    emergency_plan TEXT NOT NULL,
    weather_threshold VARCHAR(160) NULL,
    emergency_contact TEXT NOT NULL,

    reviewed_at DATETIME NOT NULL,

    CONSTRAINT fk_safety_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES program_schedules(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 25. AUDIT LOGS
-- =========================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    entity VARCHAR(60) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,

    before_json JSON NULL,
    after_json JSON NULL,

    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_actor
        FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    INDEX idx_audit_entity (
        entity,
        entity_id,
        created_at
    )
) ENGINE=InnoDB;

-- =========================================================
-- 26. PROGRAM REVIEWS
-- =========================================================

CREATE TABLE program_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_program_reviews_program
        FOREIGN KEY (program_id)
        REFERENCES programs(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_program_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_program_reviews_rating
        CHECK (rating BETWEEN 1 AND 5),

    UNIQUE KEY uq_program_user (program_id, user_id),
    INDEX idx_program_reviews_program (program_id),
    INDEX idx_program_reviews_user (user_id)
) ENGINE=InnoDB;

-- =========================================================
-- FRAMEWORK TABLES USED BY LARAVEL
-- =========================================================

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_personal_access_tokens_expires_at (expires_at),
    INDEX idx_personal_access_tokens_tokenable (tokenable_type, tokenable_id),
    INDEX idx_personal_access_tokens_expires_at (expires_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
