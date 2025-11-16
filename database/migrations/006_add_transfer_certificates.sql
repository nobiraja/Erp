-- Add Transfer Certificates Table
-- Migration: 006_add_transfer_certificates.sql

CREATE TABLE transfer_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    transfer_reason VARCHAR(255) NOT NULL,
    transfer_to_school VARCHAR(255),
    academic_record TEXT,
    conduct_grade ENUM('excellent', 'very_good', 'good', 'satisfactory', 'needs_improvement') DEFAULT 'good',
    remarks TEXT,
    issued_by INT NOT NULL,
    class_teacher_id INT NULL,
    principal_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (principal_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_tc_student (student_id),
    INDEX idx_tc_certificate_number (certificate_number),
    INDEX idx_tc_issue_date (issue_date),
    INDEX idx_tc_issued_by (issued_by)
) ENGINE=InnoDB;

-- Insert sample data for testing
INSERT INTO transfer_certificates (
    student_id,
    certificate_number,
    issue_date,
    transfer_reason,
    transfer_to_school,
    academic_record,
    conduct_grade,
    remarks,
    issued_by
) VALUES
(1, 'TC2024001', '2024-12-01', 'Family relocation', 'New School Name', 'Completed 10th grade with 85% marks', 'excellent', 'Good student with excellent behavior', 1);

-- Update todo status
-- This migration adds the transfer_certificates table to support the print system for certificates