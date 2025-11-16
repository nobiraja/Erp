<?php
/**
 * Application Configuration
 * General application settings and environment configuration
 */

return [
    // Application Environment
    'env' => getenv('APP_ENV') ?: 'development', // development, staging, production

    // Debug Mode
    'debug' => getenv('APP_DEBUG') ? filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true,

    // Application Name
    'name' => getenv('APP_NAME') ?: 'School Management System',

    // Application Version
    'version' => getenv('APP_VERSION') ?: '1.0.0',

    // Base URL
    'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost',

    // Timezone
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',

    // Locale
    'locale' => getenv('APP_LOCALE') ?: 'en_US',

    // Session Configuration
    'session' => [
        'lifetime' => getenv('SESSION_LIFETIME') ?: 7200, // 2 hours
        'path' => getenv('SESSION_PATH') ?: '/',
        'domain' => getenv('SESSION_DOMAIN') ?: '',
        'secure' => getenv('SESSION_SECURE') ? filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN) : false,
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // Cache Configuration
    'cache' => [
        'enabled' => getenv('CACHE_ENABLED') ? filter_var(getenv('CACHE_ENABLED'), FILTER_VALIDATE_BOOLEAN) : false,
        'driver' => getenv('CACHE_DRIVER') ?: 'file', // file, redis, memcached
        'ttl' => getenv('CACHE_TTL') ?: 3600, // 1 hour
        'path' => getenv('CACHE_PATH') ?: __DIR__ . '/../cache'
    ],

    // Logging Configuration
    'logging' => [
        'level' => getenv('LOG_LEVEL') ?: 'debug', // emergency, alert, critical, error, warning, notice, info, debug
        'path' => getenv('LOG_PATH') ?: __DIR__ . '/../logs',
        'max_files' => getenv('LOG_MAX_FILES') ?: 30,
        'format' => getenv('LOG_FORMAT') ?: '[{timestamp}] {level}: {message} {context}'
    ],

    // File Upload Paths
    'paths' => [
        'uploads' => getenv('UPLOAD_PATH') ?: __DIR__ . '/../uploads',
        'temp' => getenv('TEMP_PATH') ?: sys_get_temp_dir(),
        'logs' => getenv('LOG_PATH') ?: __DIR__ . '/../logs',
        'cache' => getenv('CACHE_PATH') ?: __DIR__ . '/../cache',
        'exports' => getenv('EXPORT_PATH') ?: __DIR__ . '/../exports'
    ],

    // API Configuration
    'api' => [
        'enabled' => getenv('API_ENABLED') ? filter_var(getenv('API_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'version' => getenv('API_VERSION') ?: 'v1',
        'rate_limit' => getenv('API_RATE_LIMIT') ?: 1000, // requests per hour
        'key_required' => getenv('API_KEY_REQUIRED') ? filter_var(getenv('API_KEY_REQUIRED'), FILTER_VALIDATE_BOOLEAN) : false
    ],

    // Maintenance Mode
    'maintenance' => [
        'enabled' => getenv('MAINTENANCE_MODE') ? filter_var(getenv('MAINTENANCE_MODE'), FILTER_VALIDATE_BOOLEAN) : false,
        'message' => getenv('MAINTENANCE_MESSAGE') ?: 'System is under maintenance. Please try again later.',
        'allowed_ips' => explode(',', getenv('MAINTENANCE_ALLOWED_IPS') ?: '')
    ]
];