<?php
/**
 * Database Configuration Example
 * Copy this file to database.php and update the settings for your environment
 */

return [
    'host' => 'localhost', // Database server hostname or IP address
    'database' => 'school_management', // Database name
    'username' => 'root', // Database username
    'password' => '', // Database password
    'charset' => 'utf8mb4', // Database charset
    'port' => 3306, // Database port (default: 3306 for MySQL)
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];