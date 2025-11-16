-- Migration: 005_add_api_tokens
-- Description: Add API tokens table for REST API authentication
-- Created: 2025-11-16
-- MySQL 8.0+ Compatible

-- API Tokens Table
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) DEFAULT 'API Token',
    abilities JSON NULL, -- For future scope-based permissions
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_tokens_user (user_id),
    INDEX idx_api_tokens_token (token),
    INDEX idx_api_tokens_expires (expires_at),
    INDEX idx_api_tokens_active (is_active)
) ENGINE=InnoDB;

-- Insert default data if needed
-- This migration only creates the table structure