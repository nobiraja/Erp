<?php
/**
 * Session Management Class
 * Handles secure session operations with timeout and regeneration
 */

class Session {
    private static $instance = null;
    private $sessionLifetime = 3600; // 1 hour
    private $regenerateInterval = 300; // 5 minutes
    private $security;

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->security = Security::getInstance();
        $this->configureSession();
        $this->startSession();
        $this->validateSession();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Configure session settings
     */
    private function configureSession() {
        // Set secure session configuration
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', $this->security->isHTTPS());
        ini_set('session.gc_maxlifetime', $this->sessionLifetime);

        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->sessionLifetime,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $this->security->isHTTPS(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Start session
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Validate current session
     */
    private function validateSession() {
        // Check if session has expired
        if (isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity']) > $this->sessionLifetime) {
            $this->destroy();
            return;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        // Regenerate session ID periodically
        if (!isset($_SESSION['created']) ||
            (time() - $_SESSION['created']) > $this->regenerateInterval) {
            $this->regenerateId();
        }

        // Initialize session if not set
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        }
    }

    /**
     * Regenerate session ID
     */
    public function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    /**
     * Set session variable
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Get all session data
     */
    public function all() {
        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public function clear() {
        $_SESSION = [];
    }

    /**
     * Destroy session
     */
    public function destroy() {
        $this->clear();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->has('user_id') && $this->has('user_role');
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->get('user_id');
    }

    /**
     * Get current user role
     */
    public function getUserRole() {
        return $this->get('user_role');
    }

    /**
     * Set user session data
     */
    public function setUser($userId, $userRole, $userData = []) {
        $this->set('user_id', $userId);
        $this->set('user_role', $userRole);
        $this->set('user_data', $userData);
        $this->set('login_time', time());
        $this->regenerateId();
    }

    /**
     * Get user session data
     */
    public function getUserData() {
        return $this->get('user_data', []);
    }

    /**
     * Logout user
     */
    public function logout() {
        $this->remove('user_id');
        $this->remove('user_role');
        $this->remove('user_data');
        $this->remove('login_time');
        $this->regenerateId();
    }

    /**
     * Set flash message
     */
    public function setFlash($key, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get flash message
     */
    public function getFlash($key, $default = null) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return $default;
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Get all flash messages
     */
    public function getAllFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Set temporary data (cleared after next request)
     */
    public function setTemp($key, $value) {
        if (!isset($_SESSION['temp'])) {
            $_SESSION['temp'] = [];
        }
        $_SESSION['temp'][$key] = $value;
    }

    /**
     * Get temporary data
     */
    public function getTemp($key, $default = null) {
        return $_SESSION['temp'][$key] ?? $default;
    }

    /**
     * Clear temporary data
     */
    public function clearTemp() {
        unset($_SESSION['temp']);
    }

    /**
     * Get session ID
     */
    public function getId() {
        return session_id();
    }

    /**
     * Check session status
     */
    public function getStatus() {
        return session_status();
    }

    /**
     * Get session lifetime
     */
    public function getLifetime() {
        return $this->sessionLifetime;
    }

    /**
     * Extend session lifetime
     */
    public function extendLifetime($additionalSeconds = 3600) {
        $this->sessionLifetime += $additionalSeconds;
        ini_set('session.gc_maxlifetime', $this->sessionLifetime);
    }

    /**
     * Log session activity
     */
    private function logActivity($action) {
        $this->security->logSecurityEvent('session_' . $action, [
            'session_id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}