<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated
 */

class AuthMiddleware {
    private $session;

    public function __construct() {
        $this->session = Session::getInstance();
    }

    /**
     * Handle middleware
     */
    public function handle() {
        if (!$this->session->isLoggedIn()) {
            // Check if it's an AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Authentication required'
                ]);
                exit;
            } else {
                // Redirect to login page
                $currentUrl = urlencode($_SERVER['REQUEST_URI']);
                Router::getInstance()->redirect("/login?redirect={$currentUrl}");
            }
        }
    }
}