-- Migration: 002_add_parents_relationship
-- Description: Add parents table and parent_id to students for parent-child relationships
-- Created: 2025-11-16
-- MySQL 8.0+ Compatible

-- ===========================================
-- PARENTS TABLE
-- ===========================================

CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    mobile VARCHAR(15),
    email VARCHAR(100),
    permanent_address TEXT,
    temporary_address TEXT,
    occupation VARCHAR(100),
    relationship_to_student ENUM('father', 'mother', 'guardian', 'other') DEFAULT 'guardian',
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    photo_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_parents_user (user_id),
    INDEX idx_parents_email (email),
    INDEX idx_parents_mobile (mobile)
) ENGINE=InnoDB;

-- ===========================================
-- ADD PARENT_ID TO STUDENTS TABLE
-- ===========================================

ALTER TABLE students
ADD COLUMN parent_id INT NULL AFTER user_id,
ADD FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL,
ADD INDEX idx_students_parent (parent_id);

-- ===========================================
-- UPDATE EXISTING DATA (if any)
-- ===========================================

-- Note: This migration assumes no existing data that needs migration
-- If there are existing students with parent information in father_name/mother_name,
-- additional migration logic would be needed here

-- ===========================================
-- INSERT SAMPLE PARENT DATA (for testing)
-- ===========================================

-- Insert sample parent user
INSERT INTO users (username, email, password_hash, role_id) VALUES
('parent1', 'parent1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5);

-- Insert corresponding parent record
INSERT INTO parents (user_id, first_name, last_name, mobile, email, relationship_to_student) VALUES
(2, 'John', 'Doe', '9876543210', 'parent1@school.com', 'father');

-- Update sample student to link to parent (assuming student with id 1 exists)
-- UPDATE students SET parent_id = 1 WHERE id = 1;