<?php
/**
 * Role Check Middleware
 * Checks if user has required role(s)
 */

class RoleCheckMiddleware {
    private $session;
    private $allowedRoles = [];

    public function __construct($allowedRoles = []) {
        $this->session = Session::getInstance();
        $this->allowedRoles = (array) $allowedRoles;
    }

    /**
     * Handle middleware
     */
    public function handle() {
        // Check if user is authenticated
        if (!$this->session->isLoggedIn()) {
            $this->denyAccess('Authentication required');
        }

        $userRole = $this->session->getUserRole();

        // If no specific roles required, allow authenticated users
        if (empty($this->allowedRoles)) {
            return;
        }

        // Check if user has required role
        if (!in_array($userRole, $this->allowedRoles)) {
            $this->denyAccess('Insufficient permissions');
        }
    }

    /**
     * Deny access with appropriate response
     */
    private function denyAccess($message) {
        // Log access attempt
        $security = Security::getInstance();
        $security->logSecurityEvent('unauthorized_access', [
            'user_id' => $this->session->getUserId(),
            'user_role' => $this->session->getUserRole(),
            'required_roles' => $this->allowedRoles,
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        // Check if AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        } else {
            // Show access denied page or redirect
            http_response_code(403);
            echo "<h1>Access Denied</h1>";
            echo "<p>{$message}</p>";
            echo "<p><a href='/'>Go back to homepage</a></p>";
            exit;
        }
    }

    /**
     * Create middleware instance with specific roles
     */
    public static function roles($roles) {
        return new self($roles);
    }

    /**
     * Admin only middleware
     */
    public static function admin() {
        return new self(['admin']);
    }

    /**
     * Teacher only middleware
     */
    public static function teacher() {
        return new self(['teacher']);
    }

    /**
     * Student only middleware
     */
    public static function student() {
        return new self(['student']);
    }

    /**
     * Cashier only middleware
     */
    public static function cashier() {
        return new self(['cashier']);
    }

    /**
     * Parent only middleware
     */
    public static function parent() {
        return new self(['parent']);
    }

    /**
     * Admin or Teacher middleware
     */
    public static function adminOrTeacher() {
        return new self(['admin', 'teacher']);
    }

    /**
     * Admin or Cashier middleware
     */
    public static function adminOrCashier() {
        return new self(['admin', 'cashier']);
    }
}