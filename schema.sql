-- ============================================================
--  StudentBase — schema.sql
--  Run once to set up the database.
--  Compatible with MySQL 5.7+ / MariaDB 10.3+
--
--  Default demo credentials (CHANGE BEFORE PRODUCTION USE):
--    Admin login    -> username: admin       password: Admin@123
--    Student login  -> student #: S2024001   password: Student@123
--                      (same password applies to all seeded students)
-- ============================================================

-- 1. Create & select the database
CREATE DATABASE IF NOT EXISTS studentbase
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE studentbase;

-- 2. Main students table
CREATE TABLE IF NOT EXISTS students (
    id                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    student_number     VARCHAR(20)    NOT NULL UNIQUE,
    password           VARCHAR(255)       NULL,   -- bcrypt hash, used for client portal login
    first_name         VARCHAR(50)    NOT NULL,
    last_name          VARCHAR(50)    NOT NULL,
    dob                DATE               NULL,
    gender             ENUM('Male','Female','Other','Prefer not to say') NULL,
    address            TEXT               NULL,

    -- Contact
    email              VARCHAR(120)   NOT NULL UNIQUE,
    phone              VARCHAR(25)        NULL,
    emergency_contact  VARCHAR(100)       NULL,
    emergency_phone    VARCHAR(25)        NULL,

    -- Academic
    course             VARCHAR(100)   NOT NULL,
    year_level         TINYINT UNSIGNED   NULL CHECK (year_level BETWEEN 1 AND 5),
    intake             VARCHAR(30)        NULL,
    gpa                DECIMAL(3,2)       NULL CHECK (gpa >= 0 AND gpa <= 4.00),
    status             ENUM(
                           'Active',
                           'Inactive',
                           'Graduated',
                           'Suspended',
                           'Deferred'
                       ) NOT NULL DEFAULT 'Active',

    -- Timestamps
    created_at         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_student_number (student_number),
    INDEX idx_email          (email),
    INDEX idx_status         (status),
    INDEX idx_course         (course),
    INDEX idx_name           (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. Admins table (for the admin/ management portal login)
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,   -- bcrypt hash
    name        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed admin account -> username: admin / password: Admin@123
INSERT INTO admins (username, password, name) VALUES
  ('admin', '$2y$10$.HZGhSEeN6Vjof73MPGW.u2zFeUqORdoiAHCgJyRENnuCIhfexQX.', 'System Administrator');


-- ============================================================
-- 4. Seed student data (sample records -- remove in production)
--    All seeded students share password: Student@123
-- ============================================================
INSERT INTO students
  (student_number, password, first_name, last_name, dob, gender, address,
   email, phone, emergency_contact, emergency_phone,
   course, year_level, intake, gpa, status)
VALUES
  ('S2024001', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Ahmad',  'Razali',  '2003-04-12', 'Male',   'No 5 Jln Maju, Kuala Lumpur',
   'ahmad@uni.edu.my',   '+60 12-345 6789', 'Razali Hamid',  '+60 12-100 0001',
   'Computer Science',        2, 'Sept 2023', 3.75, 'Active'),

  ('S2024002', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Siti',   'Aminah',  '2004-07-22', 'Female', 'Blk B, PJ Utama, Petaling Jaya',
   'siti@uni.edu.my',    '+60 11-234 5678', NULL, NULL,
   'Data Science',            1, 'Sept 2024', 3.90, 'Active'),

  ('S2023003', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Raj',    'Kumar',   '2002-11-05', 'Male',   'Apt 12, Nilai Utama',
   'raj@uni.edu.my',     '+60 16-789 0123', 'Priya Kumar',   '+60 16-000 9999',
   'Electrical Engineering',  3, 'Sept 2022', 2.88, 'Active'),

  ('S2022004', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Mei',    'Ling',    '2001-03-18', 'Female', 'Taman Seri, Seremban',
   'meiling@uni.edu.my', '+60 17-456 7890', 'Tan Ah Kow',    '+60 17-111 2222',
   'Business Administration', 4, 'Sept 2021', 3.50, 'Active'),

  ('S2021005', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Hafiz',  'Osman',   '2000-08-30', 'Male',   'Lorong 3, Kota Bharu',
   'hafiz@uni.edu.my',   '+60 13-654 3210', 'Osman Jaafar',  '+60 13-500 0600',
   'Medicine',                5, 'Sept 2020', 3.95, 'Active'),

  ('S2023006', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Nurul',  'Huda',    '2003-01-14', 'Female', 'No 88, Jln Damai, Penang',
   'nurul@uni.edu.my',   '+60 19-321 0987', NULL, NULL,
   'Law',                     2, 'Sept 2023', 3.60, 'Deferred'),

  ('S2020007', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Kevin',  'Lim',     '1999-06-25', 'Male',   'Subang Jaya, Selangor',
   'kevin@uni.edu.my',   '+60 12-000 1234', 'Lim Boon Seng', '+60 12-999 8888',
   'Software Engineering',    4, 'Sept 2019', 3.20, 'Graduated'),

  ('S2024008', '$2y$10$Pt.o4lqfjKURdTdjjOFOa.hTIIqAKM0DMLDkNPA1SeLyiVniH3La.', 'Priya',  'Nair',    '2004-09-09', 'Female', 'Jln Ipoh, Kuala Lumpur',
   'priya@uni.edu.my',   '+60 14-567 8901', 'Nair Gopal',    '+60 14-777 6666',
   'Pharmacy',                1, 'Sept 2024', 3.85, 'Active');


-- ============================================================
-- 5. Optional: activity log table
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id  INT UNSIGNED     NULL,
    action      VARCHAR(20)  NOT NULL,   -- 'create' | 'update' | 'delete'
    actor_ip    VARCHAR(45)      NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
