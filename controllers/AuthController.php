<?php
/**
 * Authentication Controller
 * Handles user login, logout, and authentication
 */

class AuthController extends BaseController {

    /**
     * Show login form
     */
    public function login() {
        // Check for remember me cookie
        $this->checkRememberMe();

        // If already logged in, redirect to dashboard
        if ($this->isAuthenticated()) {
            $this->redirectToDashboard();
        }

        // Get redirect URL
        $redirect = $this->input('redirect', '/dashboard');

        // Load login view
        echo $this->view('auth/login', [
            'redirect' => $redirect,
            'csrf_token' => $this->security->getCSRFToken()
        ]);
    }

    /**
     * Authenticate user (traditional form submission)
     */
    public function authenticate() {
        // Validate CSRF token
        if (!$this->security->validateCSRFToken($this->input('csrf_token'))) {
            $this->flash('error', 'Security validation failed');
            $this->back();
        }

        // Validate input
        $data = $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!$data) {
            $this->flash('error', 'Please fill in all fields');
            $this->back();
        }

        $usernameOrEmail = trim($data['username']);
        $password = $data['password'];
        $rememberMe = $this->input('remember_me') === 'on';

        // Authenticate user
        $user = User::authenticate($usernameOrEmail, $password);

        if ($user) {
            // Set user session
            $this->session->setUser($user['id'], $user['role_name'], [
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => $this->getUserDisplayName($user)
            ]);

            // Handle remember me
            if ($rememberMe) {
                $this->setRememberMeCookie($user['id']);
            }

            // Log successful login
            $this->security->logSecurityEvent('login_successful', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role_name'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Get redirect URL
            $redirect = $this->input('redirect', $this->getDashboardUrl($user['role_name']));

            // Redirect to dashboard
            $this->redirect($redirect);
        } else {
            // Log failed login attempt
            $this->security->logSecurityEvent('login_failed', [
                'username' => $usernameOrEmail,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->flash('error', 'Invalid username or password');
            $this->back();
        }
    }

    /**
     * API endpoint for AJAX login
     */
    public function apiLogin() {
        // Set JSON response header
        header('Content-Type: application/json');

        try {
            // Validate CSRF token
            if (!$this->security->validateCSRFToken($this->input('csrf_token'))) {
                throw new Exception('Security validation failed');
            }

            // Validate input
            $data = $this->validate([
                'username' => 'required',
                'password' => 'required'
            ]);

            if (!$data) {
                throw new Exception('Please fill in all fields');
            }

            $usernameOrEmail = trim($data['username']);
            $password = $data['password'];
            $rememberMe = $this->input('remember_me') === 'on';

            // Authenticate user
            $user = User::authenticate($usernameOrEmail, $password);

            if ($user) {
                // Set user session
                $this->session->setUser($user['id'], $user['role_name'], [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name' => $this->getUserDisplayName($user)
                ]);

                // Handle remember me
                if ($rememberMe) {
                    $this->setRememberMeCookie($user['id']);
                }

                // Log successful login
                $this->security->logSecurityEvent('login_successful', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role_name'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                // Get redirect URL
                $redirect = $this->input('redirect', $this->getDashboardUrl($user['role_name']));

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $redirect,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role_name']
                    ]
                ]);
            } else {
                // Log failed login attempt
                $this->security->logSecurityEvent('login_failed', [
                    'username' => $usernameOrEmail,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API endpoint for forgot password
     */
    public function apiForgotPassword() {
        // Set JSON response header
        header('Content-Type: application/json');

        try {
            // Validate CSRF token
            if (!$this->security->validateCSRFToken($this->input('csrf_token'))) {
                throw new Exception('Security validation failed');
            }

            // Validate input
            $data = $this->validate([
                'email' => 'required|email'
            ]);

            if (!$data) {
                throw new Exception('Please enter a valid email address');
            }

            $email = trim($data['email']);

            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Don't reveal if email exists or not for security
                echo json_encode([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent.'
                ]);
                return;
            }

            // Create password reset token
            $token = $user->createPasswordResetToken();

            // Send email (you would implement email sending here)
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;

            // For now, just log the reset link (in production, send email)
            error_log("Password reset link for {$email}: {$resetLink}");

            // TODO: Implement actual email sending
            // $this->sendPasswordResetEmail($email, $resetLink);

            echo json_encode([
                'success' => true,
                'message' => 'If an account with that email exists, a password reset link has been sent.'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show password reset form
     */
    public function resetPassword() {
        $token = $this->input('token');

        if (!$token) {
            $this->flash('error', 'Invalid password reset link');
            $this->redirect('/login');
        }

        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            $this->flash('error', 'Invalid or expired password reset link');
            $this->redirect('/login');
        }

        echo $this->view('auth/reset_password', [
            'token' => $token,
            'csrf_token' => $this->security->getCSRFToken()
        ]);
    }

    /**
     * Process password reset
     */
    public function processPasswordReset() {
        // Validate CSRF token
        if (!$this->security->validateCSRFToken($this->input('csrf_token'))) {
            $this->flash('error', 'Security validation failed');
            $this->back();
        }

        $data = $this->validate([
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password'
        ]);

        if (!$data) {
            $this->flash('error', 'Please fill in all fields correctly');
            $this->back();
        }

        $token = $data['token'];
        $newPassword = $data['password'];

        if (User::resetPasswordWithToken($token, $newPassword)) {
            $this->flash('success', 'Password reset successfully! You can now log in with your new password.');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'Invalid or expired password reset link');
            $this->back();
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        // Log logout
        $this->security->logSecurityEvent('logout', [
            'user_id' => $this->getUserId(),
            'username' => $this->getUserData()['username'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        // Clear remember me cookie
        $this->clearRememberMeCookie();

        $this->session->logout();
        $this->redirect('/login');
    }

    /**
     * Check for remember me cookie on page load
     */
    public function checkRememberMe() {
        if ($this->isAuthenticated()) {
            return; // Already logged in
        }

        $rememberToken = $_COOKIE['remember_me'] ?? null;
        if (!$rememberToken) {
            return;
        }

        // Validate remember me token
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT user_id FROM remember_me_tokens
            WHERE token = ? AND expires_at > NOW()
            LIMIT 1
        ");

        $stmt->execute([$rememberToken]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user = User::find($result['user_id']);
            if ($user) {
                // Set user session
                $this->session->setUser($user->id, $user->getRoleName(), [
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $this->getUserDisplayName($user->toArray())
                ]);

                // Log automatic login
                $this->security->logSecurityEvent('auto_login', [
                    'user_id' => $user->id,
                    'username' => $user->username
                ]);
            }
        } else {
            // Invalid token, clear cookie
            $this->clearRememberMeCookie();
        }
    }

    /**
     * Helper methods
     */

    /**
     * Get dashboard URL for role
     */
    private function getDashboardUrl($role) {
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
     * Get user display name
     */
    private function getUserDisplayName($user) {
        // Try to get from related tables based on role
        $role = $user['role_name'];
        $db = Database::getInstance()->getConnection();

        switch ($role) {
            case 'admin':
                return 'Administrator';
            case 'teacher':
                $stmt = $db->prepare("SELECT first_name, last_name FROM teachers WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
                return $teacher ? trim($teacher['first_name'] . ' ' . $teacher['last_name']) : 'Teacher';
            case 'student':
                $stmt = $db->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                return $student ? trim($student['first_name'] . ' ' . $student['last_name']) : 'Student';
            case 'cashier':
                return 'Cashier';
            case 'parent':
                return 'Parent';
            default:
                return ucfirst($role);
        }
    }

    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Store token in database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO remember_me_tokens (user_id, token, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)
        ");
        $stmt->execute([$userId, $token, $expires]);

        // Set cookie
        setcookie('remember_me', $token, [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'secure' => $this->security->isHTTPS(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            // Remove from database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM remember_me_tokens WHERE token = ?");
            $stmt->execute([$_COOKIE['remember_me']]);

            // Clear cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard() {
        $role = $this->getUserRole();
        $this->redirect($this->getDashboardUrl($role));
    }
}