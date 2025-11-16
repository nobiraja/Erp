<?php
/**
 * Settings Model
 * Handles all settings-related database operations
 */

class SettingsModel extends BaseModel {

    /**
     * Get all settings grouped by category
     */
    public static function getAllSettings() {
        $db = Database::getInstance();

        $query = "SELECT * FROM settings ORDER BY setting_group, setting_key";
        $settings = $db->fetchAll($query);

        $grouped = [];
        foreach ($settings as $setting) {
            $group = $setting['setting_group'] ?: 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $setting;
        }

        return $grouped;
    }

    /**
     * Update multiple settings
     */
    public static function updateSettings($settings) {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $query = "INSERT INTO settings (setting_key, setting_value, updated_at)
                         VALUES (?, ?, NOW())
                         ON DUPLICATE KEY UPDATE
                         setting_value = VALUES(setting_value),
                         updated_at = NOW()";
                $db->execute($query, [$key, $value]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Get all users with role information
     */
    public static function getAllUsers() {
        $db = Database::getInstance();

        $query = "SELECT u.*, ur.role_name
                 FROM users u
                 LEFT JOIN user_roles ur ON u.role_id = ur.id
                 ORDER BY u.created_at DESC";

        return $db->fetchAll($query);
    }

    /**
     * Create new user
     */
    public static function createUser($userData) {
        $db = Database::getInstance();

        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, email, password_hash, role_id, created_at)
                 VALUES (?, ?, ?, ?, NOW())";

        $db->execute($query, [
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['role_id']
        ]);

        return $db->lastInsertId();
    }

    /**
     * Update user
     */
    public static function updateUser($userId, $userData) {
        $db = Database::getInstance();

        $query = "UPDATE users SET
                 username = ?,
                 email = ?,
                 role_id = ?,
                 is_active = ?,
                 updated_at = NOW()
                 WHERE id = ?";

        $db->execute($query, [
            $userData['username'],
            $userData['email'],
            $userData['role_id'],
            isset($userData['is_active']) ? $userData['is_active'] : 1,
            $userId
        ]);
    }

    /**
     * Delete user
     */
    public static function deleteUser($userId) {
        $db = Database::getInstance();

        // Don't allow deleting the current admin user or if it's the last admin
        $user = $db->fetch("SELECT role_id FROM users WHERE id = ?", [$userId]);
        if ($user && $user['role_id'] == 1) { // Admin role
            $adminCount = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role_id = 1 AND is_active = 1")['count'];
            if ($adminCount <= 1) {
                throw new Exception("Cannot delete the last active admin user");
            }
        }

        $query = "DELETE FROM users WHERE id = ?";
        $db->execute($query, [$userId]);
    }

    /**
     * Get all user roles
     */
    public static function getAllRoles() {
        $db = Database::getInstance();

        $query = "SELECT * FROM user_roles ORDER BY role_name";
        return $db->fetchAll($query);
    }

    /**
     * Get permissions for a specific role
     */
    public static function getPermissionsByRole($roleId) {
        $db = Database::getInstance();

        $query = "SELECT p.*, m.module_name, m.display_name
                 FROM permissions p
                 LEFT JOIN modules m ON p.module_name = m.name
                 WHERE p.role_id = ?
                 ORDER BY p.module_name, p.permission_name";

        return $db->fetchAll($query, [$roleId]);
    }

    /**
     * Update permissions for a role
     */
    public static function updatePermissions($roleId, $permissions) {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            // Delete existing permissions for this role
            $db->execute("DELETE FROM permissions WHERE role_id = ?", [$roleId]);

            // Insert new permissions
            foreach ($permissions as $permission) {
                $query = "INSERT INTO permissions
                         (role_id, module_name, permission_name, can_view, can_create, can_edit, can_delete)
                         VALUES (?, ?, ?, ?, ?, ?, ?)";

                $db->execute($query, [
                    $roleId,
                    $permission['module_name'],
                    $permission['permission_name'],
                    $permission['can_view'] ?? 0,
                    $permission['can_create'] ?? 0,
                    $permission['can_edit'] ?? 0,
                    $permission['can_delete'] ?? 0
                ]);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Get homepage content
     */
    public static function getHomepageContent() {
        $db = Database::getInstance();

        $query = "SELECT * FROM homepage_content
                 WHERE is_active = 1
                 ORDER BY display_order, section_name";

        return $db->fetchAll($query);
    }

    /**
     * Update homepage content
     */
    public static function updateHomepageContent($content) {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            foreach ($content as $item) {
                if (isset($item['id'])) {
                    // Update existing
                    $query = "UPDATE homepage_content SET
                             title = ?,
                             content = ?,
                             image_path = ?,
                             link_url = ?,
                             display_order = ?,
                             is_active = ?,
                             updated_at = NOW()
                             WHERE id = ?";

                    $db->execute($query, [
                        $item['title'] ?? '',
                        $item['content'] ?? '',
                        $item['image_path'] ?? '',
                        $item['link_url'] ?? '',
                        $item['display_order'] ?? 0,
                        $item['is_active'] ?? 1,
                        $item['id']
                    ]);
                } else {
                    // Insert new
                    $query = "INSERT INTO homepage_content
                             (section_name, content_type, title, content, image_path, link_url, display_order, is_active, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                    $db->execute($query, [
                        $item['section_name'] ?? 'general',
                        $item['content_type'] ?? 'text',
                        $item['title'] ?? '',
                        $item['content'] ?? '',
                        $item['image_path'] ?? '',
                        $item['link_url'] ?? '',
                        $item['display_order'] ?? 0,
                        $item['is_active'] ?? 1
                    ]);
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Create database backup
     */
    public static function createBackup() {
        $db = Database::getInstance();

        // Create backup directory if it doesn't exist
        $backupDir = __DIR__ . '/../backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Generate backup filename
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . "/backup_{$timestamp}.sql";

        // Get all tables
        $tables = $db->fetchAll("SHOW TABLES");
        $sql = "-- Database Backup\n-- Created: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $tableName = current($table);

            // Get table structure
            $createTable = $db->fetch("SHOW CREATE TABLE `$tableName`");
            $sql .= $createTable['Create Table'] . ";\n\n";

            // Get table data
            $rows = $db->fetchAll("SELECT * FROM `$tableName`");
            if (!empty($rows)) {
                $sql .= "INSERT INTO `$tableName` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        $rowValues[] = $db->quote($value);
                    }
                    $values[] = "(" . implode(", ", $rowValues) . ")";
                }
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        // Write to file
        file_put_contents($backupFile, $sql);

        return $backupFile;
    }

    /**
     * Get list of backup files
     */
    public static function getBackupList() {
        $backupDir = __DIR__ . '/../backup';

        if (!is_dir($backupDir)) {
            return [];
        }

        $files = glob($backupDir . "/backup_*.sql");
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'path' => $file,
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Restore database from backup
     */
    public static function restoreBackup($backupFile) {
        $db = Database::getInstance();
        $backupPath = __DIR__ . '/../backup/' . $backupFile;

        if (!file_exists($backupPath)) {
            throw new Exception("Backup file not found");
        }

        $sql = file_get_contents($backupPath);

        $db->beginTransaction();
        try {
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $db->execute($statement);
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Get API security settings
     */
    public static function getApiSettings() {
        $db = Database::getInstance();

        $settings = [];

        // Get API-related settings
        $apiKeys = $db->fetchAll("SELECT * FROM settings WHERE setting_key LIKE 'api_%'");
        foreach ($apiKeys as $key) {
            $settings[$key['setting_key']] = $key['setting_value'];
        }

        // Default API settings if not set
        $defaults = [
            'api_enabled' => '1',
            'api_rate_limit' => '100',
            'api_key_required' => '1',
            'api_cors_enabled' => '1',
            'api_cors_origins' => '*'
        ];

        return array_merge($defaults, $settings);
    }

    /**
     * Update API security settings
     */
    public static function updateApiSettings($settings) {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $query = "INSERT INTO settings (setting_key, setting_value, setting_group, updated_at)
                         VALUES (?, ?, 'api', NOW())
                         ON DUPLICATE KEY UPDATE
                         setting_value = VALUES(setting_value),
                         updated_at = NOW()";

                $db->execute($query, [$key, $value]);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}