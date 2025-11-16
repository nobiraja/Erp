<?php
/**
 * School Management System Installation Wizard
 * Web-based installation with system checks, database setup, and admin account creation
 */

// Check if already installed
if (file_exists(__DIR__ . '/config/database.php')) {
    // Try to connect to database
    $config = require __DIR__ . '/config/database.php';
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}",
            $config['username'],
            $config['password'],
            $config['options']
        );
        $pdo->exec("USE {$config['database']}");
        // If we get here, system is installed
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Database connection failed, allow reinstall
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');

    $step = $_POST['step'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid request'];

    switch ($step) {
        case 'check_requirements':
            $response = checkSystemRequirements();
            break;
        case 'test_database':
            $response = testDatabaseConnection($_POST);
            break;
        case 'install_system':
            $response = installSystem($_POST);
            break;
    }

    echo json_encode($response);
    exit;
}

function checkSystemRequirements() {
    $requirements = [
        'php_version' => [
            'name' => 'PHP Version',
            'required' => '8.1.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '8.1.0', '>=')
        ],
        'pdo' => [
            'name' => 'PDO Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('pdo')
        ],
        'pdo_mysql' => [
            'name' => 'PDO MySQL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('pdo_mysql')
        ],
        'mbstring' => [
            'name' => 'Multibyte String Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('mbstring')
        ],
        'curl' => [
            'name' => 'cURL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('curl')
        ],
        'json' => [
            'name' => 'JSON Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('json')
        ],
        'session' => [
            'name' => 'Session Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('session') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('session')
        ],
        'openssl' => [
            'name' => 'OpenSSL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('openssl')
        ],
        'gd' => [
            'name' => 'GD Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('gd') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('gd')
        ],
        'zip' => [
            'name' => 'ZIP Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('zip')
        ]
    ];

    $all_passed = true;
    foreach ($requirements as $req) {
        if (!$req['status']) {
            $all_passed = false;
            break;
        }
    }

    return [
        'success' => true,
        'requirements' => $requirements,
        'all_passed' => $all_passed
    ];
}

function testDatabaseConnection($data) {
    $host = $data['db_host'] ?? 'localhost';
    $database = $data['db_name'] ?? '';
    $username = $data['db_user'] ?? '';
    $password = $data['db_pass'] ?? '';
    $port = $data['db_port'] ?? 3306;

    if (empty($database) || empty($username)) {
        return ['success' => false, 'message' => 'Database name and username are required'];
    }

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Test if database exists, create if not
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        return ['success' => true, 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
}

function installSystem($data) {
    $db_config = [
        'host' => $data['db_host'] ?? 'localhost',
        'database' => $data['db_name'] ?? '',
        'username' => $data['db_user'] ?? '',
        'password' => $data['db_pass'] ?? '',
        'charset' => 'utf8mb4',
        'port' => $data['db_port'] ?? 3306,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ];

    $admin_username = $data['admin_username'] ?? '';
    $admin_email = $data['admin_email'] ?? '';
    $admin_password = $data['admin_password'] ?? '';

    if (empty($admin_username) || empty($admin_email) || empty($admin_password)) {
        return ['success' => false, 'message' => 'Admin credentials are required'];
    }

    try {
        // Execute schema using mysqli for multi-query support
        $mysqli = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], '', $db_config['port']);
        if ($mysqli->connect_error) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $mysqli->connect_error];
        }

        // Create database if not exists
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$db_config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            return ['success' => false, 'message' => 'Failed to create database: ' . $mysqli->error];
        }

        // Select database
        if (!$mysqli->select_db($db_config['database'])) {
            return ['success' => false, 'message' => 'Failed to select database: ' . $mysqli->error];
        }

        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        if (!$schema) {
            return ['success' => false, 'message' => 'Could not read database schema file'];
        }

        // Remove CREATE DATABASE and USE statements
        $schema = preg_replace('/CREATE DATABASE.*;/i', '', $schema);
        $schema = preg_replace('/USE.*;/i', '', $schema);

        // Execute the schema
        if ($mysqli->multi_query($schema)) {
            // Consume all results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }

        if ($mysqli->error) {
            return ['success' => false, 'message' => 'Schema execution failed: ' . $mysqli->error];
        }

        // Update admin user with provided credentials
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET username = '$admin_username', email = '$admin_email', password_hash = '$hashed_password' WHERE id = 1";
        if (!$mysqli->query($updateQuery)) {
            return ['success' => false, 'message' => 'Failed to update admin user: ' . $mysqli->error];
        }

        $mysqli->close();

        // Save database configuration
        $config_content = "<?php\n" .
            "/**\n" .
            " * Database Configuration\n" .
            " * Generated by installation wizard\n" .
            " */\n\n" .
            "return " . var_export($db_config, true) . ";\n";

        if (!file_put_contents(__DIR__ . '/config/database.php', $config_content)) {
            return ['success' => false, 'message' => 'Could not save database configuration'];
        }

        // Create necessary directories
        $dirs = ['logs', 'uploads', 'cache', 'exports', 'reports'];
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        return ['success' => true, 'message' => 'System installed successfully'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Installation failed: ' . $e->getMessage()];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Installation</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .install-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 800px;
            margin: 2rem auto;
        }
        .install-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            padding: 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            position: relative;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        .step.active .step-circle {
            background: #007bff;
            color: white;
        }
        .step.completed .step-circle {
            background: #28a745;
            color: white;
        }
        .step-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .step.active .step-text {
            color: #007bff;
            font-weight: bold;
        }
        .step.completed .step-text {
            color: #28a745;
        }
        .install-content {
            padding: 2rem;
            min-height: 400px;
        }
        .install-footer {
            padding: 1rem 2rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            background: white;
        }
        .requirement-status {
            font-weight: bold;
        }
        .status-pass {
            color: #28a745;
        }
        .status-fail {
            color: #dc3545;
        }
        .progress-tracker {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 1rem 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="install-container">
            <div class="install-header">
                <h1><i class="bi bi-gear-fill me-2"></i>School Management System</h1>
                <p class="mb-0">Installation Wizard</p>
            </div>

            <div class="step-indicator">
                <div class="step active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-text">Requirements</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-text">Database</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-text">Admin Account</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-text">Install</div>
                </div>
            </div>

            <div class="install-content">
                <!-- Step 1: Requirements Check -->
                <div class="step-content" id="step-1">
                    <h4><i class="bi bi-check-circle-fill text-success me-2"></i>System Requirements Check</h4>
                    <p class="text-muted">Checking your server configuration to ensure compatibility with the School Management System.</p>

                    <div id="requirements-list">
                        <div class="text-center py-4">
                            <div class="loading-spinner"></div>
                            <p class="mt-2">Checking requirements...</p>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> All requirements must pass before you can proceed with the installation.
                    </div>
                </div>

                <!-- Step 2: Database Configuration -->
                <div class="step-content d-none" id="step-2">
                    <h4><i class="bi bi-database-fill text-primary me-2"></i>Database Configuration</h4>
                    <p class="text-muted">Configure your MySQL database connection settings.</p>

                    <form id="database-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="db_port" class="form-label">Port</label>
                                    <input type="number" class="form-control" id="db_port" name="db_port" value="3306" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="db_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="school_management" required>
                            <div class="form-text">Database will be created if it doesn't exist</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="test-db-btn">
                            <i class="bi bi-play-circle me-2"></i>Test Connection
                        </button>
                    </form>
                </div>

                <!-- Step 3: Admin Account Setup -->
                <div class="step-content d-none" id="step-3">
                    <h4><i class="bi bi-person-fill-add text-warning me-2"></i>Administrator Account</h4>
                    <p class="text-muted">Create the main administrator account for your school management system.</p>

                    <form id="admin-form">
                        <div class="mb-3">
                            <label for="admin_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="admin_username" name="admin_username" value="admin" required>
                            <div class="form-text">Choose a unique username for the administrator</div>
                        </div>
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@school.com" required>
                            <div class="form-text">Administrator's email address</div>
                        </div>
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                            <div class="form-text">Minimum 8 characters with letters, numbers, and symbols</div>
                        </div>
                        <div class="mb-3">
                            <label for="admin_confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="admin_confirm_password" name="admin_confirm_password" required>
                        </div>
                    </form>
                </div>

                <!-- Step 4: Installation Process -->
                <div class="step-content d-none" id="step-4">
                    <h4><i class="bi bi-rocket-fill text-success me-2"></i>Installation Progress</h4>
                    <p class="text-muted">Installing the School Management System. Please wait...</p>

                    <div class="progress-tracker">
                        <div class="progress-bar" id="install-progress" style="width: 0%"></div>
                    </div>

                    <div id="install-log" class="mt-3">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Preparing installation...
                        </div>
                    </div>
                </div>

                <!-- Step 5: Success -->
                <div class="step-content d-none" id="step-5">
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-success">Installation Complete!</h4>
                        <p class="text-muted">The School Management System has been successfully installed.</p>

                        <div class="alert alert-success mt-4">
                            <h6><i class="bi bi-key me-2"></i>Administrator Credentials</h6>
                            <p class="mb-1"><strong>Username:</strong> <span id="final-username"></span></p>
                            <p class="mb-1"><strong>Email:</strong> <span id="final-email"></span></p>
                            <p class="mb-0"><strong>Password:</strong> As configured</p>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Please save these credentials and change the password after first login.
                        </div>

                        <a href="index.php" class="btn btn-success btn-lg mt-3">
                            <i class="bi bi-house-door me-2"></i>Go to Homepage
                        </a>
                        <a href="admin/dashboard" class="btn btn-primary btn-lg mt-3 ms-2">
                            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="install-footer">
                <button class="btn btn-outline-secondary" id="prev-btn" disabled>
                    <i class="bi bi-arrow-left me-2"></i>Previous
                </button>
                <div class="step-info">Step 1 of 4</div>
                <button class="btn btn-primary" id="next-btn">
                    Next<i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Alert container for floating messages -->
    <div id="alert-container" class="alert-floating"></div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let requirementsPassed = false;
        let databaseConfigured = false;

        document.addEventListener('DOMContentLoaded', function() {
            initializeWizard();
            loadRequirements();
        });

        function initializeWizard() {
            document.getElementById('prev-btn').addEventListener('click', previousStep);
            document.getElementById('next-btn').addEventListener('click', nextStep);
            document.getElementById('test-db-btn').addEventListener('click', testDatabase);
        }

        function loadRequirements() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'step=check_requirements'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRequirements(data.requirements);
                    requirementsPassed = data.all_passed;
                    updateNextButton();
                } else {
                    showAlert('Error checking requirements: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error checking requirements: ' + error.message, 'danger');
            });
        }

        function displayRequirements(requirements) {
            const container = document.getElementById('requirements-list');
            container.innerHTML = '';

            for (const [key, req] of Object.entries(requirements)) {
                const item = document.createElement('div');
                item.className = 'requirement-item';
                item.innerHTML = `
                    <div>
                        <strong>${req.name}</strong>
                        <br><small class="text-muted">Required: ${req.required}</small>
                    </div>
                    <div class="requirement-status ${req.status ? 'status-pass' : 'status-fail'}">
                        ${req.current} ${req.status ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>'}
                    </div>
                `;
                container.appendChild(item);
            }
        }

        function testDatabase() {
            const form = document.getElementById('database-form');
            const formData = new FormData(form);
            formData.append('step', 'test_database');

            document.getElementById('test-db-btn').innerHTML = '<div class="loading-spinner"></div> Testing...';
            document.getElementById('test-db-btn').disabled = true;

            fetch('', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('test-db-btn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Test Connection';
                document.getElementById('test-db-btn').disabled = false;

                if (data.success) {
                    databaseConfigured = true;
                    showAlert('Database connection successful!', 'success');
                    updateNextButton();
                } else {
                    databaseConfigured = false;
                    showAlert('Database connection failed: ' + data.message, 'danger');
                    updateNextButton();
                }
            })
            .catch(error => {
                document.getElementById('test-db-btn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Test Connection';
                document.getElementById('test-db-btn').disabled = false;
                showAlert('Error testing database: ' + error.message, 'danger');
            });
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                if (validateCurrentStep()) {
                    currentStep++;
                    showStep(currentStep);
                }
            } else if (currentStep === totalSteps) {
                startInstallation();
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.add('d-none');
            });

            // Show current step
            document.getElementById('step-' + step).classList.remove('d-none');

            // Update step indicators
            document.querySelectorAll('.step').forEach((stepEl, index) => {
                const stepNum = index + 1;
                stepEl.classList.remove('active', 'completed');
                if (stepNum === currentStep) {
                    stepEl.classList.add('active');
                } else if (stepNum < currentStep) {
                    stepEl.classList.add('completed');
                }
            });

            // Update navigation
            document.getElementById('prev-btn').disabled = step === 1;
            document.querySelector('.step-info').textContent = `Step ${step} of ${totalSteps}`;

            // Update next button
            updateNextButton();

            // Special actions for specific steps
            if (step === totalSteps) {
                document.getElementById('next-btn').innerHTML = 'Install<i class="bi bi-rocket ms-2"></i>';
            } else {
                document.getElementById('next-btn').innerHTML = 'Next<i class="bi bi-arrow-right ms-2"></i>';
            }
        }

        function validateCurrentStep() {
            switch (currentStep) {
                case 1:
                    return requirementsPassed;
                case 2:
                    return databaseConfigured;
                case 3:
                    return validateAdminForm();
                default:
                    return true;
            }
        }

        function validateAdminForm() {
            const username = document.getElementById('admin_username').value.trim();
            const email = document.getElementById('admin_email').value.trim();
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('admin_confirm_password').value;

            if (!username || !email || !password) {
                showAlert('All fields are required', 'danger');
                return false;
            }

            if (password.length < 8) {
                showAlert('Password must be at least 8 characters long', 'danger');
                return false;
            }

            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'danger');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'danger');
                return false;
            }

            return true;
        }

        function updateNextButton() {
            const nextBtn = document.getElementById('next-btn');
            const isValid = validateCurrentStep();

            nextBtn.disabled = !isValid;

            if (currentStep === 1 && !requirementsPassed) {
                nextBtn.innerHTML = 'Requirements not met<i class="bi bi-x-circle ms-2"></i>';
            } else if (currentStep === 2 && !databaseConfigured) {
                nextBtn.innerHTML = 'Test database first<i class="bi bi-database-x ms-2"></i>';
            } else if (currentStep === totalSteps) {
                nextBtn.innerHTML = 'Install<i class="bi bi-rocket ms-2"></i>';
            } else {
                nextBtn.innerHTML = 'Next<i class="bi bi-arrow-right ms-2"></i>';
            }
        }

        function startInstallation() {
            const formData = new FormData();
            formData.append('step', 'install_system');

            // Collect database data
            const dbForm = document.getElementById('database-form');
            const dbData = new FormData(dbForm);
            for (let [key, value] of dbData.entries()) {
                formData.append(key, value);
            }

            // Collect admin data
            const adminForm = document.getElementById('admin-form');
            const adminData = new FormData(adminForm);
            for (let [key, value] of adminData.entries()) {
                formData.append(key, value);
            }

            // Show installation step
            showStep(4);

            const progressBar = document.getElementById('install-progress');
            const logContainer = document.getElementById('install-log');

            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
            }, 500);

            fetch('', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';

                if (data.success) {
                    logContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Installation completed successfully!
                        </div>
                    `;

                    // Show final step after a delay
                    setTimeout(() => {
                        document.getElementById('final-username').textContent = document.getElementById('admin_username').value;
                        document.getElementById('final-email').textContent = document.getElementById('admin_email').value;
                        showStep(5);
                    }, 1000);
                } else {
                    logContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            Installation failed: ${data.message}
                        </div>
                    `;
                    document.getElementById('next-btn').disabled = false;
                    document.getElementById('next-btn').innerHTML = 'Retry Installation<i class="bi bi-arrow-repeat ms-2"></i>';
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                logContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        Installation error: ${error.message}
                    </div>
                `;
                document.getElementById('next-btn').disabled = false;
                document.getElementById('next-btn').innerHTML = 'Retry Installation<i class="bi bi-arrow-repeat ms-2"></i>';
            });
        }

        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>