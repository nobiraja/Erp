<?php
/**
 * User Model
 * Handles user authentication and user data operations
 */

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'is_active',
        'last_login'
    ];

    protected $hidden = [
        'password_hash'
    ];

    /**
     * Get user by username or email
     */
    public static function findByUsernameOrEmail($usernameOrEmail) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT u.*, r.role_name
            FROM users u
            JOIN user_roles r ON u.role_id = r.id
            WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Update last login
     */
    public function updateLastLogin() {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$this->id]);
    }

    /**
     * Get user role name
     */
    public function getRoleName() {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT role_name
            FROM user_roles
            WHERE id = ?
        ");

        $stmt->execute([$this->role_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['role_name'] : null;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($module, $permission) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT p.can_view, p.can_create, p.can_edit, p.can_delete
            FROM permissions p
            WHERE p.role_id = ? AND p.module_name = ? AND p.permission_name = ?
            LIMIT 1
        ");

        $stmt->execute([$this->role_id, $module, $permission]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * Get user dashboard URL based on role
     */
    public function getDashboardUrl() {
        $role = $this->getRoleName();

        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'teacher':
                return '/teacher/dashboard';
            case 'student':
                return '/student/dashboard';
            case 'cashier':
                return '/cashier/dashboard';
            case 'parent':
                return '/parent/dashboard';
            default:
                return '/dashboard';
        }
    }

    /**
     * Authenticate user
     */
    public static function authenticate($usernameOrEmail, $password) {
        $user = self::findByUsernameOrEmail($usernameOrEmail);

        if (!$user) {
            return false;
        }

        if (!self::verifyPassword($password, $user['password_hash'])) {
            return false;
        }

        // Update last login
        $userObj = new self($user);
        $userObj->updateLastLogin();

        return $user;
    }

    /**
     * Create password reset token
     */
    public function createPasswordResetToken() {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db = Database::getInstance()->getConnection();

        // First, clean up any existing tokens for this user
        $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$this->id]);

        // Insert new token
        $stmt = $db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([$this->id, $token, $expires]);

        return $token;
    }

    /**
     * Find user by password reset token
     */
    public static function findByPasswordResetToken($token) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT u.*, prt.token, prt.expires_at
            FROM users u
            JOIN password_reset_tokens prt ON u.id = prt.user_id
            WHERE prt.token = ? AND prt.expires_at > NOW() AND u.is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Reset password using token
     */
    public static function resetPasswordWithToken($token, $newPassword) {
        $user = self::findByPasswordResetToken($token);

        if (!$user) {
            return false;
        }

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            // Update password
            $hashedPassword = self::hashPassword($newPassword);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);

            // Delete used token
            $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$token]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}