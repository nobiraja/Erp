-- School Management System Database Schema
-- MySQL 8.0+ Compatible
-- Generated based on school.md specifications

CREATE DATABASE IF NOT EXISTS school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_management;

-- ===========================================
-- CORE TABLES
-- ===========================================

-- User Roles Table
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    INDEX idx_users_email (email),
    INDEX idx_users_role_id (role_id),
    INDEX idx_users_username (username)
) ENGINE=InnoDB;

-- Classes Table
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(20) NOT NULL,
    section VARCHAR(10) NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    class_teacher_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_class_section_year (class_name, section, academic_year),
    FOREIGN KEY (class_teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_classes_academic_year (academic_year),
    INDEX idx_classes_class_teacher (class_teacher_id)
) ENGINE=InnoDB;

-- Subjects Table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subjects_code (subject_code)
) ENGINE=InnoDB;

-- Students Table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL UNIQUE,
    scholar_number VARCHAR(20) NOT NULL UNIQUE,
    admission_number VARCHAR(20) NOT NULL UNIQUE,
    admission_date DATE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    class_id INT NOT NULL,
    section VARCHAR(10) NOT NULL,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    guardian_name VARCHAR(100),
    guardian_contact VARCHAR(15),
    dob DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    caste_category VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indian',
    religion VARCHAR(50),
    blood_group VARCHAR(10),
    village_address TEXT,
    permanent_address TEXT,
    temporary_address TEXT,
    mobile VARCHAR(15),
    email VARCHAR(100),
    aadhar VARCHAR(12),
    samagra VARCHAR(20),
    apaar_id VARCHAR(20),
    pan VARCHAR(10),
    previous_school VARCHAR(100),
    medical_conditions TEXT,
    photo_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_students_scholar (scholar_number),
    INDEX idx_students_admission (admission_number),
    INDEX idx_students_class (class_id),
    INDEX idx_students_email (email),
    INDEX idx_students_aadhar (aadhar)
) ENGINE=InnoDB;

-- Teachers Table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL UNIQUE,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    blood_group VARCHAR(10),
    qualification VARCHAR(100),
    specialization VARCHAR(100),
    designation VARCHAR(50),
    department VARCHAR(50),
    date_of_joining DATE NOT NULL,
    experience_years INT DEFAULT 0,
    permanent_address TEXT,
    temporary_address TEXT,
    mobile VARCHAR(15),
    email VARCHAR(100),
    aadhar VARCHAR(12),
    pan VARCHAR(10),
    samagra_id VARCHAR(20),
    medical_conditions TEXT,
    photo_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_teachers_employee (employee_id),
    INDEX idx_teachers_email (email),
    INDEX idx_teachers_aadhar (aadhar)
) ENGINE=InnoDB;

-- Class Subjects Table
CREATE TABLE class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_class_subject (class_id, subject_id),
    INDEX idx_class_subjects_class (class_id),
    INDEX idx_class_subjects_subject (subject_id),
    INDEX idx_class_subjects_teacher (teacher_id)
) ENGINE=InnoDB;

-- ===========================================
-- TRANSACTION TABLES
-- ===========================================

-- Attendance Table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL DEFAULT 'present',
    marked_by INT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date_subject (student_id, attendance_date, subject_id),
    INDEX idx_attendance_student (student_id),
    INDEX idx_attendance_class (class_id),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_attendance_status (status)
) ENGINE=InnoDB;

-- Exams Table
CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    exam_type ENUM('mid-term', 'final', 'unit-test', 'custom') NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_exams_class (class_id),
    INDEX idx_exams_type (exam_type),
    INDEX idx_exams_year (academic_year),
    INDEX idx_exams_dates (start_date, end_date)
) ENGINE=InnoDB;

-- Exam Subjects Table (for scheduling)
CREATE TABLE exam_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date DATE NOT NULL,
    day VARCHAR(10),
    start_time TIME,
    end_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_exam_subject (exam_id, subject_id),
    INDEX idx_exam_subjects_exam (exam_id),
    INDEX idx_exam_subjects_subject (subject_id),
    INDEX idx_exam_subjects_date (exam_date)
) ENGINE=InnoDB;

-- Exam Results Table
CREATE TABLE exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5),
    percentage DECIMAL(5,2),
    remarks TEXT,
    entered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_exam_student_subject (exam_id, student_id, subject_id),
    INDEX idx_exam_results_exam (exam_id),
    INDEX idx_exam_results_student (student_id),
    INDEX idx_exam_results_subject (subject_id),
    INDEX idx_exam_results_grade (grade)
) ENGINE=InnoDB;

-- Fees Table
CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    description TEXT,
    is_paid BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fees_student (student_id),
    INDEX idx_fees_type (fee_type),
    INDEX idx_fees_year (academic_year),
    INDEX idx_fees_due_date (due_date),
    INDEX idx_fees_paid (is_paid)
) ENGINE=InnoDB;

-- Fee Payments Table
CREATE TABLE fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_mode ENUM('cash', 'online', 'cheque', 'upi') NOT NULL,
    transaction_id VARCHAR(100),
    cheque_number VARCHAR(50),
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    collected_by INT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fee_payments_fee (fee_id),
    INDEX idx_fee_payments_date (payment_date),
    INDEX idx_fee_payments_mode (payment_mode),
    INDEX idx_fee_payments_receipt (receipt_number)
) ENGINE=InnoDB;

-- Expenses Table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_category VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    reason TEXT NOT NULL,
    payment_mode ENUM('cash', 'online', 'cheque', 'upi') NOT NULL,
    transaction_id VARCHAR(100),
    cheque_number VARCHAR(50),
    created_by INT NOT NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_expenses_category (expense_category),
    INDEX idx_expenses_date (payment_date),
    INDEX idx_expenses_created_by (created_by)
) ENGINE=InnoDB;

-- ===========================================
-- CONTENT TABLES
-- ===========================================

-- Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    organizer VARCHAR(100),
    contact_info VARCHAR(100),
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_events_date (event_date),
    INDEX idx_events_active (is_active),
    INDEX idx_events_created_by (created_by)
) ENGINE=InnoDB;

-- Announcements Table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    visibility ENUM('all', 'students', 'teachers', 'parents', 'admin') DEFAULT 'all',
    target_audience TEXT, -- JSON array of specific classes, users, etc.
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_announcements_priority (priority),
    INDEX idx_announcements_visibility (visibility),
    INDEX idx_announcements_active (is_active),
    INDEX idx_announcements_expires (expires_at),
    INDEX idx_announcements_created_by (created_by)
) ENGINE=InnoDB;

-- Gallery Table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_gallery_category (category),
    INDEX idx_gallery_active (is_active),
    INDEX idx_gallery_order (display_order)
) ENGINE=InnoDB;

-- News Table
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    summary TEXT,
    image_path VARCHAR(255),
    published_date DATE NOT NULL,
    author_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    tags VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_news_published_date (published_date),
    INDEX idx_news_status (status),
    INDEX idx_news_featured (is_featured),
    INDEX idx_news_author (author_id)
) ENGINE=InnoDB;

-- Homepage Content Table
CREATE TABLE homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(50) NOT NULL,
    content_type ENUM('text', 'image', 'carousel', 'video') NOT NULL,
    title VARCHAR(200),
    content TEXT,
    image_path VARCHAR(255),
    video_url VARCHAR(255),
    link_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_homepage_section (section_name),
    INDEX idx_homepage_order (display_order),
    INDEX idx_homepage_active (is_active)
) ENGINE=InnoDB;

-- ===========================================
-- SYSTEM TABLES
-- ===========================================

-- Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_user (user_id),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_table (table_name),
    INDEX idx_audit_logs_created (created_at)
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key),
    INDEX idx_settings_group (setting_group)
) ENGINE=InnoDB;

-- Permissions Table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    module_name VARCHAR(50) NOT NULL,
    permission_name VARCHAR(50) NOT NULL,
    can_view BOOLEAN DEFAULT FALSE,
    can_create BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_module_permission (role_id, module_name, permission_name),
    INDEX idx_permissions_role (role_id),
    INDEX idx_permissions_module (module_name)
) ENGINE=InnoDB;

-- Password Reset Tokens Table
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_reset_user (user_id),
    INDEX idx_password_reset_token (token),
    INDEX idx_password_reset_expires (expires_at)
) ENGINE=InnoDB;

-- Remember Me Tokens Table
CREATE TABLE remember_me_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_remember_me_user (user_id),
    INDEX idx_remember_me_token (token),
    INDEX idx_remember_me_expires (expires_at)
) ENGINE=InnoDB;

-- ===========================================
-- TRIGGERS FOR AUDIT LOGGING
-- ===========================================

-- Trigger for users table
DELIMITER //
CREATE TRIGGER audit_users_insert AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address)
    VALUES (NEW.id, 'INSERT', 'users', NEW.id, JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role_id', NEW.role_id), '');
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER audit_users_update AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address)
    VALUES (NEW.id, 'UPDATE', 'users', NEW.id,
        JSON_OBJECT('username', OLD.username, 'email', OLD.email, 'role_id', OLD.role_id, 'is_active', OLD.is_active),
        JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role_id', NEW.role_id, 'is_active', NEW.is_active), '');
END//
DELIMITER ;

-- Similar triggers can be added for other critical tables as needed

-- ===========================================
-- INITIAL DATA SEEDING
-- ===========================================

-- Insert default user roles
INSERT INTO user_roles (role_name, description) VALUES
('admin', 'System Administrator with full access'),
('teacher', 'Teaching staff with limited access'),
('student', 'Student user with read-only access'),
('cashier', 'Finance staff for fee management'),
('parent', 'Parent user for child monitoring');

-- Insert default admin user (password: admin123 - should be changed)
INSERT INTO users (username, email, password_hash, role_id) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample settings
INSERT INTO settings (setting_key, setting_value, setting_group, description) VALUES
('school_name', 'School Management System', 'general', 'Name of the school'),
('academic_year', '2024-2025', 'academic', 'Current academic year'),
('currency', 'INR', 'finance', 'Currency used for fees'),
('timezone', 'Asia/Kolkata', 'system', 'System timezone');

-- Insert default permissions for admin role
INSERT INTO permissions (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete) VALUES
(1, 'users', 'manage_users', 1, 1, 1, 1),
(1, 'students', 'manage_students', 1, 1, 1, 1),
(1, 'teachers', 'manage_teachers', 1, 1, 1, 1),
(1, 'classes', 'manage_classes', 1, 1, 1, 1),
(1, 'attendance', 'manage_attendance', 1, 1, 1, 1),
(1, 'exams', 'manage_exams', 1, 1, 1, 1),
(1, 'fees', 'manage_fees', 1, 1, 1, 1),
(1, 'events', 'manage_events', 1, 1, 1, 1),
(1, 'gallery', 'manage_gallery', 1, 1, 1, 1),
(1, 'reports', 'view_reports', 1, 1, 1, 1),
(1, 'settings', 'manage_settings', 1, 1, 1, 1);

-- Insert default permissions for teacher role
INSERT INTO permissions (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete) VALUES
(2, 'attendance', 'mark_attendance', 1, 1, 1, 0),
(2, 'exams', 'view_exams', 1, 0, 0, 0),
(2, 'students', 'view_students', 1, 0, 0, 0),
(2, 'classes', 'view_classes', 1, 0, 0, 0);

-- Insert default permissions for student role
INSERT INTO permissions (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete) VALUES
(3, 'attendance', 'view_own_attendance', 1, 0, 0, 0),
(3, 'exams', 'view_own_results', 1, 0, 0, 0),
(3, 'fees', 'view_own_fees', 1, 0, 0, 0),
(3, 'profile', 'manage_own_profile', 1, 0, 1, 0);

-- Insert default permissions for cashier role
INSERT INTO permissions (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete) VALUES
(4, 'fees', 'manage_fee_payments', 1, 1, 1, 1),
(4, 'expenses', 'manage_expenses', 1, 1, 1, 1),
(4, 'reports', 'view_financial_reports', 1, 0, 0, 0);

-- Insert default permissions for parent role
INSERT INTO permissions (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete) VALUES
(5, 'children', 'view_children_info', 1, 0, 0, 0),
(5, 'attendance', 'view_children_attendance', 1, 0, 0, 0),
(5, 'exams', 'view_children_results', 1, 0, 0, 0),
(5, 'fees', 'view_children_fees', 1, 0, 0, 0),
(5, 'events', 'view_events', 1, 0, 0, 0);