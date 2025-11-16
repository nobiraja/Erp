<?php
/**
 * Security Middleware
 * Performs additional security checks
 */

class SecurityMiddleware {
    private $security;

    public function __construct() {
        $this->security = Security::getInstance();
    }

    /**
     * Handle middleware
     */
    public function handle() {
        // Force HTTPS in production
        $this->security->forceHTTPS();

        // Check for suspicious activity
        // if ($this->security->detectSuspiciousActivity()) {
        //     $this->security->logSecurityEvent('blocked_request', [
        //         'ip' => $_SERVER['REMOTE_ADDR'],
        //         'uri' => $_SERVER['REQUEST_URI'],
        //         'user_agent' => $_SERVER['HTTP_USER_AGENT']
        //     ]);

        //     http_response_code(403);
        //     echo "Access denied due to suspicious activity";
        //     exit;
        // }

        // Rate limiting
        // if (!$this->security->checkRateLimit('api', 100, 60)) { // 100 requests per minute
        //     http_response_code(429);
        //     echo "Too many requests. Please try again later.";
        //     exit;
        // }

        // Validate CSRF token for POST/PUT/DELETE requests
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            if (!$this->security->validateCSRFToken()) {
                http_response_code(403);
                echo "CSRF token validation failed";
                exit;
            }
        }
    }
}