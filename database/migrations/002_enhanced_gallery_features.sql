-- Migration: 002_enhanced_gallery_features
-- Description: Enhanced gallery features including videos, hierarchical categories, and bulk operations
-- Created: 2025-11-16
-- MySQL 8.0+ Compatible

-- ===========================================
-- GALLERY ENHANCEMENT TABLES
-- ===========================================

-- Gallery Categories Table (Hierarchical)
CREATE TABLE gallery_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES gallery_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_gallery_categories_parent (parent_id),
    INDEX idx_gallery_categories_order (display_order),
    INDEX idx_gallery_categories_active (is_active),
    INDEX idx_gallery_categories_slug (slug)
) ENGINE=InnoDB;

-- Enhanced Gallery Table
ALTER TABLE gallery
ADD COLUMN media_type ENUM('image', 'video') NOT NULL DEFAULT 'image' AFTER image_path,
ADD COLUMN video_path VARCHAR(255) NULL AFTER media_type,
ADD COLUMN file_size INT NULL AFTER video_path,
ADD COLUMN mime_type VARCHAR(100) NULL AFTER file_size,
ADD COLUMN thumbnail_path VARCHAR(255) NULL AFTER mime_type,
ADD COLUMN category_id INT NULL AFTER category,
ADD COLUMN tags VARCHAR(500) NULL AFTER display_order,
ADD COLUMN alt_text VARCHAR(255) NULL AFTER tags,
ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER alt_text,
ADD COLUMN view_count INT DEFAULT 0 AFTER is_featured,
ADD COLUMN download_count INT DEFAULT 0 AFTER view_count,
ADD COLUMN FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
ADD INDEX idx_gallery_media_type (media_type),
ADD INDEX idx_gallery_category_id (category_id),
ADD INDEX idx_gallery_featured (is_featured),
ADD INDEX idx_gallery_tags (tags);

-- Gallery Settings Table
CREATE TABLE gallery_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gallery_settings_key (setting_key),
    INDEX idx_gallery_settings_group (setting_group)
) ENGINE=InnoDB;

-- Bulk Upload Sessions Table
CREATE TABLE gallery_upload_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(200) NOT NULL,
    total_files INT DEFAULT 0,
    uploaded_files INT DEFAULT 0,
    failed_files INT DEFAULT 0,
    status ENUM('uploading', 'completed', 'failed', 'cancelled') DEFAULT 'uploading',
    category_id INT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_upload_sessions_status (status),
    INDEX idx_upload_sessions_category (category_id),
    INDEX idx_upload_sessions_user (uploaded_by)
) ENGINE=InnoDB;

-- ===========================================
-- INITIAL DATA
-- ===========================================

-- Insert default gallery categories
INSERT INTO gallery_categories (name, slug, description, display_order, created_by) VALUES
('Events', 'events', 'School events and celebrations', 1, 1),
('Sports', 'sports', 'Sports activities and competitions', 2, 1),
('Cultural', 'cultural', 'Cultural programs and performances', 3, 1),
('Academics', 'academics', 'Academic achievements and activities', 4, 1),
('Infrastructure', 'infrastructure', 'School facilities and infrastructure', 5, 1),
('Student Life', 'student-life', 'Daily student activities and life', 6, 1);

-- Insert default gallery settings
INSERT INTO gallery_settings (setting_key, setting_value, setting_group, description) VALUES
('max_file_size', '10485760', 'upload', 'Maximum file size in bytes (10MB)'),
('allowed_image_types', 'jpg,jpeg,png,gif,webp', 'upload', 'Allowed image file extensions'),
('allowed_video_types', 'mp4,avi,mov,wmv,flv,webm', 'upload', 'Allowed video file extensions'),
('thumbnail_width', '300', 'thumbnails', 'Thumbnail width in pixels'),
('thumbnail_height', '200', 'thumbnails', 'Thumbnail height in pixels'),
('items_per_page', '12', 'display', 'Number of items to display per page'),
('enable_lazy_loading', '1', 'display', 'Enable lazy loading for images'),
('enable_video_player', '1', 'display', 'Enable video player for video files'),
('sync_with_public', '1', 'sync', 'Sync gallery changes with public website'),
('auto_generate_thumbnails', '1', 'processing', 'Automatically generate thumbnails for uploads');

-- ===========================================
-- TRIGGERS
-- ===========================================

-- Trigger for gallery categories audit
DELIMITER //
CREATE TRIGGER audit_gallery_categories_insert AFTER INSERT ON gallery_categories
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address)
    VALUES (NEW.created_by, 'INSERT', 'gallery_categories', NEW.id,
        JSON_OBJECT('name', NEW.name, 'slug', NEW.slug, 'parent_id', NEW.parent_id), '');
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER audit_gallery_categories_update AFTER UPDATE ON gallery_categories
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address)
    VALUES (NEW.created_by, 'UPDATE', 'gallery_categories', NEW.id,
        JSON_OBJECT('name', OLD.name, 'slug', OLD.slug, 'parent_id', OLD.parent_id, 'is_active', OLD.is_active),
        JSON_OBJECT('name', NEW.name, 'slug', NEW.slug, 'parent_id', NEW.parent_id, 'is_active', NEW.is_active), '');
END//
DELIMITER ;

-- Trigger for enhanced gallery audit
DELIMITER //
CREATE TRIGGER audit_gallery_enhanced_insert AFTER INSERT ON gallery
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address)
    VALUES (NEW.uploaded_by, 'INSERT', 'gallery', NEW.id,
        JSON_OBJECT('title', NEW.title, 'media_type', NEW.media_type, 'category_id', NEW.category_id), '');
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER audit_gallery_enhanced_update AFTER UPDATE ON gallery
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address)
    VALUES (NEW.uploaded_by, 'UPDATE', 'gallery', NEW.id,
        JSON_OBJECT('title', OLD.title, 'is_active', OLD.is_active, 'category_id', OLD.category_id),
        JSON_OBJECT('title', NEW.title, 'is_active', NEW.is_active, 'category_id', NEW.category_id), '');
END//
DELIMITER ;

-- ===========================================
-- STORED PROCEDURES
-- ===========================================

-- Procedure to get hierarchical categories
DELIMITER //
CREATE PROCEDURE get_gallery_categories_hierarchy()
BEGIN
    SELECT
        c1.id,
        c1.name,
        c1.slug,
        c1.description,
        c1.parent_id,
        c1.display_order,
        c1.is_active,
        c2.name as parent_name,
        COUNT(g.id) as media_count
    FROM gallery_categories c1
    LEFT JOIN gallery_categories c2 ON c1.parent_id = c2.id
    LEFT JOIN gallery g ON c1.id = g.category_id AND g.is_active = 1
    WHERE c1.is_active = 1
    GROUP BY c1.id, c1.name, c1.slug, c1.description, c1.parent_id, c1.display_order, c1.is_active, c2.name
    ORDER BY c1.display_order ASC, c1.name ASC;
END//
DELIMITER ;

-- Procedure to get gallery stats by category
DELIMITER //
CREATE PROCEDURE get_gallery_category_stats(IN category_id INT)
BEGIN
    SELECT
        COUNT(*) as total_items,
        SUM(CASE WHEN media_type = 'image' THEN 1 ELSE 0 END) as image_count,
        SUM(CASE WHEN media_type = 'video' THEN 1 ELSE 0 END) as video_count,
        SUM(file_size) as total_size,
        MAX(created_at) as last_upload,
        COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_count
    FROM gallery
    WHERE category_id = category_id AND is_active = 1;
END//
DELIMITER ;

-- Procedure for bulk category update
DELIMITER //
CREATE PROCEDURE bulk_update_gallery_category(IN old_category_id INT, IN new_category_id INT, IN updated_by INT)
BEGIN
    UPDATE gallery
    SET category_id = new_category_id, updated_at = NOW()
    WHERE category_id = old_category_id AND is_active = 1;

    INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address)
    VALUES (updated_by, 'BULK_UPDATE', 'gallery', NULL,
        JSON_OBJECT('action', 'category_change', 'old_category_id', old_category_id, 'new_category_id', new_category_id), '');
END//
DELIMITER ;