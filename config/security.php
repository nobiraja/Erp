<?php
/**
 * Security Configuration
 * Security-related settings including encryption, CSRF, and authentication
 */

return [
    // Encryption Settings
    'encryption' => [
        'key' => getenv('ENCRYPTION_KEY') ?: 'change-this-key-in-production-32-chars',
        'cipher' => getenv('ENCRYPTION_CIPHER') ?: 'AES-256-CBC',
        'hash_algorithm' => getenv('HASH_ALGORITHM') ?: 'sha256'
    ],

    // CSRF Protection
    'csrf' => [
        'enabled' => getenv('CSRF_ENABLED') ? filter_var(getenv('CSRF_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'token_name' => getenv('CSRF_TOKEN_NAME') ?: 'csrf_token',
        'token_length' => getenv('CSRF_TOKEN_LENGTH') ?: 32,
        'regenerate' => getenv('CSRF_REGENERATE') ? filter_var(getenv('CSRF_REGENERATE'), FILTER_VALIDATE_BOOLEAN) : true,
        'lifetime' => getenv('CSRF_LIFETIME') ?: 3600 // 1 hour
    ],

    // Password Security
    'password' => [
        'min_length' => getenv('PASSWORD_MIN_LENGTH') ?: 8,
        'require_uppercase' => getenv('PASSWORD_REQUIRE_UPPERCASE') ? filter_var(getenv('PASSWORD_REQUIRE_UPPERCASE'), FILTER_VALIDATE_BOOLEAN) : true,
        'require_lowercase' => getenv('PASSWORD_REQUIRE_LOWERCASE') ? filter_var(getenv('PASSWORD_REQUIRE_LOWERCASE'), FILTER_VALIDATE_BOOLEAN) : true,
        'require_numbers' => getenv('PASSWORD_REQUIRE_NUMBERS') ? filter_var(getenv('PASSWORD_REQUIRE_NUMBERS'), FILTER_VALIDATE_BOOLEAN) : true,
        'require_symbols' => getenv('PASSWORD_REQUIRE_SYMBOLS') ? filter_var(getenv('PASSWORD_REQUIRE_SYMBOLS'), FILTER_VALIDATE_BOOLEAN) : false,
        'hash_algorithm' => getenv('PASSWORD_HASH_ALGORITHM') ?: PASSWORD_ARGON2ID,
        'hash_options' => [
            'memory_cost' => getenv('PASSWORD_MEMORY_COST') ?: 65536,
            'time_cost' => getenv('PASSWORD_TIME_COST') ?: 4,
            'threads' => getenv('PASSWORD_THREADS') ?: 3
        ]
    ],

    // Session Security
    'session' => [
        'secure' => getenv('SESSION_SECURE') ? filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN) : false,
        'httponly' => getenv('SESSION_HTTPONLY') ? filter_var(getenv('SESSION_HTTPONLY'), FILTER_VALIDATE_BOOLEAN) : true,
        'samesite' => getenv('SESSION_SAMESITE') ?: 'Lax',
        'regenerate_frequency' => getenv('SESSION_REGENERATE_FREQUENCY') ?: 300, // 5 minutes
        'max_lifetime' => getenv('SESSION_MAX_LIFETIME') ?: 7200 // 2 hours
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => getenv('RATE_LIMITING_ENABLED') ? filter_var(getenv('RATE_LIMITING_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'max_requests' => getenv('RATE_LIMIT_MAX_REQUESTS') ?: 100,
        'time_window' => getenv('RATE_LIMIT_TIME_WINDOW') ?: 3600, // 1 hour
        'block_duration' => getenv('RATE_LIMIT_BLOCK_DURATION') ?: 900 // 15 minutes
    ],

    // Security Headers
    'headers' => [
        'hsts' => [
            'enabled' => getenv('HSTS_ENABLED') ? filter_var(getenv('HSTS_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
            'max_age' => getenv('HSTS_MAX_AGE') ?: 31536000, // 1 year
            'include_subdomains' => getenv('HSTS_INCLUDE_SUBDOMAINS') ? filter_var(getenv('HSTS_INCLUDE_SUBDOMAINS'), FILTER_VALIDATE_BOOLEAN) : true,
            'preload' => getenv('HSTS_PRELOAD') ? filter_var(getenv('HSTS_PRELOAD'), FILTER_VALIDATE_BOOLEAN) : false
        ],
        'csp' => [
            'enabled' => getenv('CSP_ENABLED') ? filter_var(getenv('CSP_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
            'default_src' => getenv('CSP_DEFAULT_SRC') ?: "'self'",
            'script_src' => getenv('CSP_SCRIPT_SRC') ?: "'self' 'unsafe-inline'",
            'style_src' => getenv('CSP_STYLE_SRC') ?: "'self' 'unsafe-inline'",
            'img_src' => getenv('CSP_IMG_SRC') ?: "'self' data: https:",
            'font_src' => getenv('CSP_FONT_SRC') ?: "'self' https:"
        ],
        'x_frame_options' => getenv('X_FRAME_OPTIONS') ?: 'SAMEORIGIN',
        'x_content_type_options' => getenv('X_CONTENT_TYPE_OPTIONS') ?: 'nosniff',
        'referrer_policy' => getenv('REFERRER_POLICY') ?: 'strict-origin-when-cross-origin'
    ],

    // Authentication
    'auth' => [
        'max_login_attempts' => getenv('AUTH_MAX_LOGIN_ATTEMPTS') ?: 5,
        'lockout_duration' => getenv('AUTH_LOCKOUT_DURATION') ?: 900, // 15 minutes
        'remember_me_duration' => getenv('AUTH_REMEMBER_ME_DURATION') ?: 604800, // 7 days
        'password_reset_expiry' => getenv('AUTH_PASSWORD_RESET_EXPIRY') ?: 3600 // 1 hour
    ],

    // Input Validation
    'validation' => [
        'sanitize_input' => getenv('VALIDATION_SANITIZE_INPUT') ? filter_var(getenv('VALIDATION_SANITIZE_INPUT'), FILTER_VALIDATE_BOOLEAN) : true,
        'allow_html_tags' => explode(',', getenv('VALIDATION_ALLOW_HTML_TAGS') ?: ''),
        'max_input_length' => getenv('VALIDATION_MAX_INPUT_LENGTH') ?: 10000,
        'max_file_uploads' => getenv('VALIDATION_MAX_FILE_UPLOADS') ?: 20
    ],

    // Security Monitoring
    'monitoring' => [
        'log_suspicious_activity' => getenv('LOG_SUSPICIOUS_ACTIVITY') ? filter_var(getenv('LOG_SUSPICIOUS_ACTIVITY'), FILTER_VALIDATE_BOOLEAN) : true,
        'log_failed_logins' => getenv('LOG_FAILED_LOGINS') ? filter_var(getenv('LOG_FAILED_LOGINS'), FILTER_VALIDATE_BOOLEAN) : true,
        'alert_on_suspicious' => getenv('ALERT_ON_SUSPICIOUS') ? filter_var(getenv('ALERT_ON_SUSPICIOUS'), FILTER_VALIDATE_BOOLEAN) : false,
        'alert_email' => getenv('SECURITY_ALERT_EMAIL') ?: ''
    ]
];