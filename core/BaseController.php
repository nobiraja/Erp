<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

class BaseController {
    protected $request;
    protected $response;
    protected $session;
    protected $validator;
    protected $security;
    protected $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->initializeCoreComponents();
        $this->initializeRequest();
    }

    /**
     * Initialize core framework components
     */
    private function initializeCoreComponents() {
        $this->session = Session::getInstance();
        $this->security = Security::getInstance();

        // Try to initialize database, but don't fail if it's not available
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            $this->db = null;
            // Log the error but don't stop execution
            error_log("Database connection failed: " . $e->getMessage());
        }

        $this->validator = new Validator();
    }

    /**
     * Initialize request data
     */
    private function initializeRequest() {
        $this->request = [
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'server' => $_SERVER,
            'cookies' => $_COOKIE,
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'headers' => $this->getHeaders()
        ];
    }

    /**
     * Get request headers
     */
    private function getHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', $header);
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get request input
     */
    protected function input($key = null, $default = null) {
        if ($key === null) {
            return array_merge($this->request['get'], $this->request['post']);
        }

        return $this->request['post'][$key] ??
               $this->request['get'][$key] ??
               $default;
    }

    /**
     * Get all input data
     */
    protected function all() {
        return array_merge($this->request['get'], $this->request['post']);
    }

    /**
     * Check if request has input
     */
    protected function has($key) {
        return isset($this->request['post'][$key]) ||
               isset($this->request['get'][$key]);
    }

    /**
     * Get file from request
     */
    protected function file($key) {
        return $this->request['files'][$key] ?? null;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return isset($this->request['headers']['X-Requested-With']) &&
               $this->request['headers']['X-Requested-With'] === 'XMLHttpRequest';
    }

    /**
     * Check request method
     */
    protected function isMethod($method) {
        return strtoupper($this->request['method']) === strtoupper($method);
    }

    /**
     * Validate request data
     */
    protected function validate($rules, $messages = []) {
        $data = $this->all();
        $this->validator->setData($data);
        $this->validator->setRules($rules);

        if (!empty($messages)) {
            $this->validator->setMessages($messages);
        }

        if (!$this->validator->validate()) {
            return false;
        }

        return $this->validator->getValidatedData();
    }

    /**
     * Get validation errors
     */
    protected function getValidationErrors() {
        return $this->validator->getErrors();
    }

    /**
     * Check if validation failed
     */
    protected function validationFails() {
        return $this->validator->hasErrors();
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url, $statusCode = 302) {
        Router::getInstance()->redirect($url, $statusCode);
    }

    /**
     * Redirect back
     */
    protected function back() {
        $referer = $this->request['headers']['Referer'] ??
                   $this->request['server']['HTTP_REFERER'] ??
                   '/';
        $this->redirect($referer);
    }

    /**
     * Set flash message
     */
    protected function flash($key, $message) {
        $this->session->setFlash($key, $message);
    }

    /**
     * Get flash message
     */
    protected function getFlash($key, $default = null) {
        return $this->session->getFlash($key, $default);
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return $this->session->isLoggedIn();
    }

    /**
     * Get current user ID
     */
    protected function getUserId() {
        return $this->session->getUserId();
    }

    /**
     * Get current user role
     */
    protected function getUserRole() {
        return $this->session->getUserRole();
    }

    /**
     * Check if user has role
     */
    protected function hasRole($role) {
        return $this->getUserRole() === $role;
    }

    /**
     * Check if user has any of the roles
     */
    protected function hasAnyRole($roles) {
        return in_array($this->getUserRole(), (array)$roles);
    }

    /**
     * Get user data
     */
    protected function getUserData() {
        return $this->session->getUserData();
    }

    /**
     * Logout user
     */
    protected function logout() {
        $this->session->logout();
    }

    /**
     * Render view
     */
    protected function view($view, $data = []) {
        // Basic view rendering - can be extended
        extract($data);

        $viewFile = $this->getViewFile($view);

        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            return ob_get_clean();
        }

        throw new Exception("View {$view} not found");
    }

    /**
     * Get view file path
     */
    private function getViewFile($view) {
        // Convert dot notation to path
        $view = str_replace('.', '/', $view);
        return __DIR__ . '/../views/' . $view . '.php';
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Return success JSON response
     */
    protected function success($data = [], $message = 'Success') {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return error JSON response
     */
    protected function error($message = 'Error', $errors = [], $statusCode = 400) {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Handle file upload
     */
    protected function handleUpload($fileKey, $destination, $allowedTypes = [], $maxSize = null) {
        $file = $this->file($fileKey);

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed'];
        }

        // Check file size
        if ($maxSize && $file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large'];
        }

        // Check file type
        if (!empty($allowedTypes)) {
            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'File type not allowed'];
            }
        }

        // Generate unique filename
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $destination . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $targetPath,
                'original_name' => $file['name']
            ];
        }

        return ['success' => false, 'error' => 'Failed to save file'];
    }

    /**
     * Middleware execution
     */
    public function middleware($middleware) {
        // This will be called by the router
        if (is_callable($middleware)) {
            $middleware();
        } elseif (is_string($middleware)) {
            $this->executeMiddleware($middleware);
        }
    }

    /**
     * Execute middleware class
     */
    private function executeMiddleware($middlewareClass) {
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            if (method_exists($middleware, 'handle')) {
                $middleware->handle();
            }
        }
    }

    /**
     * Before action hook
     */
    protected function beforeAction($action) {
        // Override in child classes
    }

    /**
     * After action hook
     */
    protected function afterAction($action) {
        // Override in child classes
    }

    /**
     * Handle exceptions
     */
    protected function handleException($e) {
        if ($this->isAjax()) {
            $this->error('An error occurred', [], 500);
        } else {
            // Log error
            error_log($e->getMessage());

            // Show error page
            http_response_code(500);
            echo "Internal Server Error";
        }
    }
}