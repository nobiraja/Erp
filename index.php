<?php
/**
 * School Management System - Main Entry Point
 * MVC Framework Bootstrap
 */

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define application constants
define('ROOT_PATH', __DIR__);
define('CORE_PATH', ROOT_PATH . '/core');
define('APP_PATH', ROOT_PATH);
define('CONFIG_PATH', ROOT_PATH . '/config');

// Autoloader for core classes
spl_autoload_register(function ($className) {
    // Core classes
    $coreFile = CORE_PATH . '/' . $className . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }

    // Models
    $modelFile = APP_PATH . '/models/' . $className . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    // Controllers
    $controllerFile = APP_PATH . '/controllers/' . $className . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }

    // Middleware
    $middlewareFile = APP_PATH . '/middleware/' . $className . '.php';
    if (file_exists($middlewareFile)) {
        require_once $middlewareFile;
        return;
    }

    // Helpers
    $helperFile = APP_PATH . '/helpers/' . $className . '.php';
    if (file_exists($helperFile)) {
        require_once $helperFile;
        return;
    }
});

// Load configuration
$config = [];
if (file_exists(CONFIG_PATH . '/app.php')) {
    $config = array_merge($config, require CONFIG_PATH . '/app.php');
}

// Initialize security
$security = Security::getInstance();
$security->setSecurityHeaders();

// Check for suspicious activity
// if ($security->detectSuspiciousActivity()) {
//     http_response_code(403);
//     echo "Access denied";
//     exit;
// }

// Initialize session
$session = Session::getInstance();

// Rate limiting check
// if (!$security->checkRateLimit('global', 1000, 3600)) { // 1000 requests per hour
//     http_response_code(429);
//     echo "Too many requests";
//     exit;
// }

// Initialize router
$router = Router::getInstance();

// Load routes
$routesFile = APP_PATH . '/routes.php';
if (file_exists($routesFile)) {
    require_once $routesFile;
} else {
    // Default routes if no routes file exists
    $router->get('/', 'Homepage@index');

    $router->get('/login', 'Auth@login');
    $router->post('/login', 'Auth@authenticate');
    $router->get('/logout', 'Auth@logout');
}

// Handle the request
try {
    echo $router->dispatch();
} catch (Exception $e) {
    // Log the error
    error_log("Application Error: " . $e->getMessage());

    // Handle error response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX request
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $config['debug'] ?? false ? $e->getMessage() : null
        ]);
    } else {
        // Regular request
        http_response_code(500);
        if ($config['debug'] ?? false) {
            echo "<h1>Application Error</h1>";
            echo "<pre>{$e->getMessage()}</pre>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        } else {
            echo "<h1>Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
}