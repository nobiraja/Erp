-- Migration: 003_add_event_enhancements
-- Description: Add enhanced fields to events table and create event_registrations table
-- Created: 2025-11-16

-- Add new fields to events table
ALTER TABLE events
ADD COLUMN event_type ENUM('academic', 'cultural', 'sports', 'other') DEFAULT 'other' AFTER description,
ADD COLUMN registration_required BOOLEAN DEFAULT FALSE AFTER event_time,
ADD COLUMN venue VARCHAR(200) AFTER location,
ADD COLUMN additional_info TEXT AFTER contact_info,
ADD COLUMN max_participants INT DEFAULT NULL AFTER additional_info,
ADD COLUMN registration_deadline DATE AFTER max_participants;

-- Create event_registrations table
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    parent_id INT NOT NULL,
    student_id INT NULL, -- Optional: which child is registering
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled', 'attended') DEFAULT 'registered',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_parent_student (event_id, parent_id, student_id),
    INDEX idx_event_registrations_event (event_id),
    INDEX idx_event_registrations_parent (parent_id),
    INDEX idx_event_registrations_student (student_id),
    INDEX idx_event_registrations_status (status)
) ENGINE=InnoDB;

-- Update existing events to have default values
UPDATE events SET event_type = 'other' WHERE event_type IS NULL;
UPDATE events SET registration_required = FALSE WHERE registration_required IS NULL;