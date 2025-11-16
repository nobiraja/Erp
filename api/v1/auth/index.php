<?php
/**
 * Authentication API Endpoints
 * Handles login, logout, and token management
 */

require_once '../../../controllers/ApiController.php';

class AuthApiController extends ApiController {
    /**
     * Handle API requests
     */
    public function handleRequest() {
        switch ($this->requestMethod) {
            case 'POST':
                $this->handlePost();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->methodNotAllowed();
        }
    }

    /**
     * Handle POST requests (login)
     */
    private function handlePost() {
        $action = $this->requestData['action'] ?? 'login';

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'refresh':
                $this->refreshToken();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Handle DELETE requests (logout)
     */
    private function handleDelete() {
        $this->logout();
    }

    /**
     * User login
     */
    private function login() {
        $this->validateRequired(['username', 'password']);

        $username = trim($this->requestData['username']);
        $password = $this->requestData['password'];

        // Authenticate user
        $user = User::authenticate($username, $password);

        if (!$user) {
            $this->errorResponse('Invalid credentials', 401);
        }

        // Check if user is active
        if (!$user['is_active']) {
            $this->errorResponse('Account is disabled', 401);
        }

        // Generate API token
        $token = $this->generateToken($user['id']);

        // Get user role
        $userObj = new User($user);
        $roleName = $userObj->getRoleName();

        // Return user data and token
        $this->successResponse([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $roleName,
                'last_login' => $user['last_login']
            ],
            'token' => $token,
            'expires_in' => 86400 // 24 hours
        ], 'Login successful');
    }

    /**
     * Refresh API token
     */
    private function refreshToken() {
        $this->requireAuth();

        // Revoke current token
        $currentToken = $this->getCurrentToken();
        if ($currentToken) {
            $this->revokeToken($currentToken);
        }

        // Generate new token
        $token = $this->generateToken($this->user['user_id']);

        $this->successResponse([
            'token' => $token,
            'expires_in' => 86400
        ], 'Token refreshed successfully');
    }

    /**
     * User logout
     */
    private function logout() {
        $this->requireAuth();

        // Get current token from header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = null;

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if ($token) {
            $this->revokeToken($token);
        }

        $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get current token from request
     */
    private function getCurrentToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

// Initialize and handle request
$controller = new AuthApiController();
$controller->handleRequest();