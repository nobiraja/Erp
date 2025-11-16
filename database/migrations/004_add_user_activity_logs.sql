-- Migration: 004_add_user_activity_logs
-- Description: Add user activity logs table for tracking user actions like profile updates
-- Created: 2025-11-16
-- MySQL 8.0+ Compatible

CREATE TABLE user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB;

-- Insert sample activity data for testing
INSERT INTO user_activity_logs (user_id, action, description, metadata) VALUES
(2, 'profile_updated', 'Profile information was updated', '{"fields": ["first_name", "email"]}'),
(2, 'password_changed', 'Password was changed successfully', NULL),
(2, 'login', 'Successful login from web browser', '{"device": "web", "browser": "Chrome"}'),
(2, 'fee_payment_viewed', 'Accessed fee payment details', '{"child_id": 1}'),
(2, 'exam_results_viewed', 'Viewed exam results', '{"child_id": 1, "exam_id": 1}');