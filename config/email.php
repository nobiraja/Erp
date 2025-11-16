<?php
/**
 * Email Configuration
 * SMTP and email sending settings
 */

return [
    // Email Driver
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp', // smtp, sendmail, mail

    // SMTP Configuration
    'smtp' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // tls, ssl, null
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'auth_mode' => getenv('MAIL_AUTH_MODE') ?: 'login' // login, plain, cram-md5
    ],

    // Sendmail Configuration
    'sendmail' => [
        'path' => getenv('MAIL_SENDMAIL_PATH') ?: '/usr/sbin/sendmail -bs -i'
    ],

    // General Email Settings
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@school.com',
        'name' => getenv('MAIL_FROM_NAME') ?: 'School Management System'
    ],

    // Email Templates
    'templates' => [
        'welcome' => getenv('MAIL_TEMPLATE_WELCOME') ?: 'welcome.html',
        'password_reset' => getenv('MAIL_TEMPLATE_PASSWORD_RESET') ?: 'password_reset.html',
        'notification' => getenv('MAIL_TEMPLATE_NOTIFICATION') ?: 'notification.html',
        'report' => getenv('MAIL_TEMPLATE_REPORT') ?: 'report.html'
    ],

    // Queue Settings
    'queue' => [
        'enabled' => getenv('MAIL_QUEUE_ENABLED') ? filter_var(getenv('MAIL_QUEUE_ENABLED'), FILTER_VALIDATE_BOOLEAN) : false,
        'driver' => getenv('MAIL_QUEUE_DRIVER') ?: 'database', // database, redis, file
        'batch_size' => getenv('MAIL_QUEUE_BATCH_SIZE') ?: 50,
        'retry_attempts' => getenv('MAIL_QUEUE_RETRY_ATTEMPTS') ?: 3,
        'retry_delay' => getenv('MAIL_QUEUE_RETRY_DELAY') ?: 300 // 5 minutes
    ],

    // Email Validation
    'validation' => [
        'verify_dns' => getenv('MAIL_VERIFY_DNS') ? filter_var(getenv('MAIL_VERIFY_DNS'), FILTER_VALIDATE_BOOLEAN) : true,
        'verify_mx' => getenv('MAIL_VERIFY_MX') ? filter_var(getenv('MAIL_VERIFY_MX'), FILTER_VALIDATE_BOOLEAN) : false,
        'blacklist_domains' => explode(',', getenv('MAIL_BLACKLIST_DOMAINS') ?: ''),
        'whitelist_domains' => explode(',', getenv('MAIL_WHITELIST_DOMAINS') ?: '')
    ],

    // Rate Limiting
    'rate_limit' => [
        'enabled' => getenv('MAIL_RATE_LIMIT_ENABLED') ? filter_var(getenv('MAIL_RATE_LIMIT_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'max_per_hour' => getenv('MAIL_RATE_LIMIT_PER_HOUR') ?: 100,
        'max_per_day' => getenv('MAIL_RATE_LIMIT_PER_DAY') ?: 1000,
        'burst_limit' => getenv('MAIL_RATE_LIMIT_BURST') ?: 10
    ],

    // Logging
    'logging' => [
        'enabled' => getenv('MAIL_LOGGING_ENABLED') ? filter_var(getenv('MAIL_LOGGING_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'level' => getenv('MAIL_LOG_LEVEL') ?: 'info', // debug, info, warning, error
        'log_sent' => getenv('MAIL_LOG_SENT') ? filter_var(getenv('MAIL_LOG_SENT'), FILTER_VALIDATE_BOOLEAN) : true,
        'log_failed' => getenv('MAIL_LOG_FAILED') ? filter_var(getenv('MAIL_LOG_FAILED'), FILTER_VALIDATE_BOOLEAN) : true
    ],

    // Test Mode
    'test' => [
        'enabled' => getenv('MAIL_TEST_MODE') ? filter_var(getenv('MAIL_TEST_MODE'), FILTER_VALIDATE_BOOLEAN) : false,
        'intercept_email' => getenv('MAIL_TEST_INTERCEPT_EMAIL') ?: '',
        'log_only' => getenv('MAIL_TEST_LOG_ONLY') ? filter_var(getenv('MAIL_TEST_LOG_ONLY'), FILTER_VALIDATE_BOOLEAN) : false
    ],

    // DKIM Settings (for email authentication)
    'dkim' => [
        'enabled' => getenv('MAIL_DKIM_ENABLED') ? filter_var(getenv('MAIL_DKIM_ENABLED'), FILTER_VALIDATE_BOOLEAN) : false,
        'domain' => getenv('MAIL_DKIM_DOMAIN') ?: '',
        'selector' => getenv('MAIL_DKIM_SELECTOR') ?: 'default',
        'private_key' => getenv('MAIL_DKIM_PRIVATE_KEY') ?: '',
        'passphrase' => getenv('MAIL_DKIM_PASSPHRASE') ?: ''
    ]
];