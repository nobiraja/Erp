<?php
/**
 * Base API Controller
 * Provides common functionality for API endpoints
 */

class ApiController {
    protected $db;
    protected $user = null;
    protected $requestMethod;
    protected $requestData = [];
    protected $headers = [];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Parse request data
        $this->parseRequestData();

        // Set CORS headers
        $this->setCorsHeaders();

        // Check rate limiting
        if (!$this->checkRateLimit()) {
            $this->errorResponse('Rate limit exceeded', 429);
        }

        // Authenticate user if token provided
        $this->authenticate();
    }

    /**
     * Parse request data based on content type
     */
    protected function parseRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $this->requestData = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $this->requestData = $_POST;
        }

        // Add GET parameters
        $this->requestData = array_merge($this->requestData, $_GET);
    }

    /**
     * Set CORS headers
     */
    protected function setCorsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // Handle preflight requests
        if ($this->requestMethod === 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit;
        }
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit() {
        $config = require __DIR__ . '/../config/security.php';
        if (!$config['rate_limiting']['enabled']) {
            return true;
        }

        $clientIP = $_SERVER['REMOTE_ADDR'];
        $maxRequests = $config['rate_limiting']['max_requests'];
        $timeWindow = $config['rate_limiting']['time_window'];

        // Simple file-based rate limiting (in production, use Redis or database)
        $rateLimitFile = __DIR__ . '/../logs/rate_limit_' . md5($clientIP) . '.log';
        $currentTime = time();

        if (file_exists($rateLimitFile)) {
            $data = json_decode(file_get_contents($rateLimitFile), true);
            if ($data && ($currentTime - $data['window_start']) < $timeWindow) {
                if ($data['requests'] >= $maxRequests) {
                    return false;
                }
                $data['requests']++;
            } else {
                $data = [
                    'window_start' => $currentTime,
                    'requests' => 1
                ];
            }
        } else {
            $data = [
                'window_start' => $currentTime,
                'requests' => 1
            ];
        }

        file_put_contents($rateLimitFile, json_encode($data));
        return true;
    }

    /**
     * Authenticate user via token
     */
    protected function authenticate() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = null;

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } elseif (isset($_GET['token'])) {
            $token = $_GET['token'];
        }

        if ($token) {
            $this->user = $this->validateToken($token);
        }
    }

    /**
     * Validate API token
     */
    protected function validateToken($token) {
        $result = $this->db->fetch(
            "SELECT at.*, u.username, u.email, r.role_name
             FROM api_tokens at
             JOIN users u ON at.user_id = u.id
             JOIN user_roles r ON u.role_id = r.id
             WHERE at.token = ? AND at.expires_at > NOW() AND at.is_active = 1",
            [$token]
        );

        return $result ?: null;
    }

    /**
     * Generate API token for user
     */
    protected function generateToken($userId, $expiresIn = 86400) { // 24 hours default
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        $this->db->insert('api_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);

        return $token;
    }

    /**
     * Revoke token
     */
    protected function revokeToken($token) {
        return $this->db->update(
            'api_tokens',
            ['is_active' => 0],
            'token = ?',
            [$token]
        );
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!$this->user) {
            $this->errorResponse('Authentication required', 401);
        }
    }

    /**
     * Check if user has required role
     */
    protected function requireRole($roles) {
        $this->requireAuth();

        $roles = (array) $roles;
        if (!in_array($this->user['role_name'], $roles)) {
            $this->errorResponse('Insufficient permissions', 403);
        }
    }

    /**
     * Send JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Send success response
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Send error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data = null) {
        $data = $data ?? $this->requestData;
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->errorResponse('Missing required fields: ' . implode(', ', $missing), 400);
        }
    }

    /**
     * Get pagination parameters
     */
    protected function getPaginationParams() {
        return [
            'page' => (int) ($this->requestData['page'] ?? 1),
            'limit' => (int) ($this->requestData['limit'] ?? 20),
            'offset' => 0
        ];
    }

    /**
     * Apply pagination to query
     */
    protected function applyPagination($query, $params) {
        $params['offset'] = ($params['page'] - 1) * $params['limit'];
        return $query . " LIMIT {$params['limit']} OFFSET {$params['offset']}";
    }

    /**
     * Get filtered and paginated results
     */
    protected function getPaginatedResults($table, $where = '', $params = [], $orderBy = 'id DESC') {
        $pagination = $this->getPaginationParams();

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$table}";
        if ($where) {
            $countQuery .= " WHERE {$where}";
        }
        $countResult = $this->db->fetch($countQuery, $params);
        $total = $countResult['total'];

        // Get data
        $dataQuery = "SELECT * FROM {$table}";
        if ($where) {
            $dataQuery .= " WHERE {$where}";
        }
        if ($orderBy) {
            $dataQuery .= " ORDER BY {$orderBy}";
        }
        $dataQuery = $this->applyPagination($dataQuery, $pagination);

        $data = $this->db->fetchAll($dataQuery, $params);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'total' => $total,
                'pages' => ceil($total / $pagination['limit'])
            ]
        ];
    }

    /**
     * Handle not found
     */
    protected function notFound($resource = 'Resource') {
        $this->errorResponse("{$resource} not found", 404);
    }

    /**
     * Handle method not allowed
     */
    protected function methodNotAllowed() {
        $this->errorResponse('Method not allowed', 405);
    }
}