-- ============================================================
-- UED LIBRARY MANAGEMENT - Module Quản lý tài khoản (User)
-- Chạy file này trong phpMyAdmin (tab SQL) hoặc MySQL Workbench
-- ============================================================

CREATE DATABASE IF NOT EXISTS ued_library
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ued_library;

-- Bảng users: tài khoản đăng nhập + phân quyền
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin', 'librarian', 'teacher', 'student') NOT NULL DEFAULT 'student',
    status      ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng students: thông tin riêng của sinh viên (Models/Student.php)
CREATE TABLE IF NOT EXISTS students (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL UNIQUE,
    student_code  VARCHAR(30) NOT NULL UNIQUE,
    class_name    VARCHAR(50) NULL,
    phone         VARCHAR(20) NULL,
    CONSTRAINT fk_students_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng teachers: thông tin riêng của giảng viên (Models/Teacher.php)
CREATE TABLE IF NOT EXISTS teachers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL UNIQUE,
    teacher_code  VARCHAR(30) NOT NULL UNIQUE,
    department    VARCHAR(100) NULL,
    phone         VARCHAR(20) NULL,
    CONSTRAINT fk_teachers_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dữ liệu mẫu (tài khoản quản trị và thủ thư)
--   admin@ued.edu.vn      / Admin@123
--   thuthu@ued.edu.vn     / Librarian@123
INSERT INTO users (full_name, email, password, role) VALUES
('Quản trị viên', 'admin@ued.edu.vn',
 '$2y$10$BtYGTvvkx.Gmj.qe0JUMneWXCNtDCBfPrbN0Qb7LGokiCNu3GTqx.', 'admin'),
('Thủ thư', 'thuthu@ued.edu.vn',
 '$2y$10$5exjlQaB3tc3bbRN7kGz0e.WNJcb06GLMcvIgAhcS45KkSUAL7khq', 'librarian')
ON DUPLICATE KEY UPDATE email = email;
