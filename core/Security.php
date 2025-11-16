<?php
/**
 * Security Utilities Class
 * Handles CSRF protection, XSS prevention, input sanitization, and other security measures
 */

class Security {
    private static $instance = null;
    private $csrfTokenName = 'csrf_token';

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION[$this->csrfTokenName])) {
            $_SESSION[$this->csrfTokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$this->csrfTokenName];
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token = null) {
        if ($token === null) {
            $token = $_POST[$this->csrfTokenName] ?? $_GET[$this->csrfTokenName] ?? '';
        }

        if (!isset($_SESSION[$this->csrfTokenName])) {
            return false;
        }

        return hash_equals($_SESSION[$this->csrfTokenName], $token);
    }

    /**
     * Get CSRF token for forms
     */
    public function getCSRFToken() {
        return $this->generateCSRFToken();
    }

    /**
     * Sanitize input data
     */
    public function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        // Remove null bytes
        $data = str_replace("\0", '', $data);

        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $data;
    }

    /**
     * Sanitize for database input (prepared statements should be used instead)
     */
    public function sanitizeForDB($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeForDB'], $data);
        }

        // Basic sanitization - real security comes from prepared statements
        return trim($data);
    }

    /**
     * Validate email
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate IP address
     */
    public function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate random string
     */
    public function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate secure random token
     */
    public function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Encrypt data
     */
    public function encrypt($data, $key = null) {
        if ($key === null) {
            $key = $this->getEncryptionKey();
        }

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data
     */
    public function decrypt($data, $key = null) {
        if ($key === null) {
            $key = $this->getEncryptionKey();
        }

        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Get encryption key
     */
    private function getEncryptionKey() {
        // In production, this should come from environment variables
        $key = getenv('ENCRYPTION_KEY') ?: 'default-key-change-in-production';
        return substr(hash('sha256', $key), 0, 32);
    }

    /**
     * Check if request is HTTPS
     */
    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Force HTTPS redirect
     */
    public function forceHTTPS() {
        if (!$this->isHTTPS()) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: {$url}", true, 301);
            exit;
        }
    }

    /**
     * Set security headers
     */
    public function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');

        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");

        // HSTS (HTTP Strict Transport Security)
        if ($this->isHTTPS()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Rate limiting check
     */
    public function checkRateLimit($key, $maxRequests = 100, $timeWindow = 3600) {
        $sessionKey = "rate_limit_{$key}";
        $now = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 1, 'reset_time' => $now + $timeWindow];
            return true;
        }

        $data = $_SESSION[$sessionKey];

        if ($now > $data['reset_time']) {
            $_SESSION[$sessionKey] = ['count' => 1, 'reset_time' => $now + $timeWindow];
            return true;
        }

        if ($data['count'] >= $maxRequests) {
            return false;
        }

        $_SESSION[$sessionKey]['count']++;
        return true;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($event, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => json_encode($details)
        ];

        // Log to file
        $logFile = __DIR__ . '/../logs/security.log';
        $logMessage = implode(' | ', $logData) . PHP_EOL;

        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check for suspicious activity
     */
    public function detectSuspiciousActivity() {
        $suspicious = false;
        $reasons = [];

        // Check for SQL injection patterns
        $sqlPatterns = ['/\bunion\b/i', '/\bselect\b/i', '/\binsert\b/i', '/\bupdate\b/i', '/\bdelete\b/i', '/\bdrop\b/i'];
        foreach ($_GET + $_POST as $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $suspicious = true;
                        $reasons[] = 'Potential SQL injection detected';
                        break 2;
                    }
                }
            }
        }

        // Check for XSS patterns
        $xssPatterns = ['/<script/i', '/javascript:/i', '/on\w+\s*=/i'];
        foreach ($_GET + $_POST as $value) {
            if (is_string($value)) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $suspicious = true;
                        $reasons[] = 'Potential XSS detected';
                        break 2;
                    }
                }
            }
        }

        if ($suspicious) {
            $this->logSecurityEvent('suspicious_activity_detected', ['reasons' => $reasons]);
        }

        return $suspicious;
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