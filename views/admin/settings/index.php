<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Settings'); ?></title>
    <meta name="description" content="Admin settings for school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #343a40;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: #007bff;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        .main-content.expanded {
            margin-left: 70px;
        }
        .hamburger-menu {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .hamburger-menu {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
        .settings-section {
            display: none;
        }
        .settings-section.active {
            display: block;
        }
        .permission-checkbox {
            margin-right: 10px;
        }
        .user-status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Menu -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($school_logo ?? '/images/logo-small.png'); ?>" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle"><?php echo htmlspecialchars(substr($school_name ?? 'SMS', 0, 10)); ?></span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/students">
                        <i class="bi bi-people"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/teachers">
                        <i class="bi bi-person-badge"></i>
                        <span>Teachers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/classes">
                        <i class="bi bi-book"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/attendance">
                        <i class="bi bi-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/exams">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/fees">
                        <i class="bi bi-cash"></i>
                        <span>Fees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/events">
                        <i class="bi bi-calendar-event"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/gallery">
                        <i class="bi bi-images"></i>
                        <span>Gallery</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/settings">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>

            <!-- User Profile Section -->
            <div class="mt-auto pt-4 border-top">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-circle fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-2" id="userInfo">
                        <div class="fw-bold small"><?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($current_user['role'] ?? 'Administrator'); ?></div>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="/logout" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="bg-white shadow-sm border-bottom">
            <div class="d-flex align-items-center justify-content-between px-4 py-3">
                <div class="d-flex align-items-center">
                    <button class="hamburger-menu d-md-none me-3" id="mobileMenuToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <h5 class="mb-0">Settings</h5>
                        <small class="text-muted">Manage system settings and configurations</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted"><?php echo htmlspecialchars($current_year); ?></small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Settings Content -->
        <main class="p-4">
            <!-- Settings Navigation Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                        <i class="bi bi-house me-1"></i>General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                                        <i class="bi bi-people me-1"></i>Users
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button" role="tab">
                                        <i class="bi bi-shield me-1"></i>Permissions
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab">
                                        <i class="bi bi-house-door me-1"></i>Homepage
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">
                                        <i class="bi bi-api me-1"></i>API
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                                        <i class="bi bi-database me-1"></i>Backup
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="settingsTabContent">
                                <!-- General Settings Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <h6 class="mb-3">School Information</h6>
                                    <form id="generalSettingsForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="school_name" class="form-label">School Name</label>
                                                <input type="text" class="form-control" id="school_name" name="school_name"
                                                       value="<?php echo htmlspecialchars($settings_data['general']['school_name']['setting_value'] ?? 'School Management System'); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="academic_year" class="form-label">Current Academic Year</label>
                                                <input type="text" class="form-control" id="academic_year" name="academic_year"
                                                       value="<?php echo htmlspecialchars($settings_data['academic']['academic_year']['setting_value'] ?? date('Y') . '-' . (date('Y') + 1)); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="currency" class="form-label">Currency</label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="INR" <?php echo ($settings_data['finance']['currency']['setting_value'] ?? 'INR') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                                    <option value="USD" <?php echo ($settings_data['finance']['currency']['setting_value'] ?? 'INR') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                                    <option value="EUR" <?php echo ($settings_data['finance']['currency']['setting_value'] ?? 'INR') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="timezone" class="form-label">Timezone</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="Asia/Kolkata" <?php echo ($settings_data['system']['timezone']['setting_value'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
                                                    <option value="UTC" <?php echo ($settings_data['system']['timezone']['setting_value'] ?? 'Asia/Kolkata') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i>Save General Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Users Management Tab -->
                                <div class="tab-pane fade" id="users" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">User Management</h6>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                            <i class="bi bi-person-plus me-1"></i>Add User
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="usersTable">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Last Login</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="usersTableBody">
                                                <!-- Users will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Permissions Tab -->
                                <div class="tab-pane fade" id="permissions" role="tabpanel">
                                    <h6 class="mb-3">Role Permissions</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="list-group" id="rolesList">
                                                <!-- Roles will be loaded via AJAX -->
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div id="permissionsPanel">
                                                <div class="alert alert-info">
                                                    Select a role to view and edit permissions
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Homepage Tab -->
                                <div class="tab-pane fade" id="homepage" role="tabpanel">
                                    <h6 class="mb-3">Homepage Customization</h6>
                                    <div id="homepageContent">
                                        <!-- Homepage content will be loaded via AJAX -->
                                    </div>
                                </div>

                                <!-- API Settings Tab -->
                                <div class="tab-pane fade" id="api" role="tabpanel">
                                    <h6 class="mb-3">API Security Settings</h6>
                                    <form id="apiSettingsForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="api_enabled" name="api_enabled">
                                                    <label class="form-check-label" for="api_enabled">
                                                        Enable API Access
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="api_key_required" name="api_key_required">
                                                    <label class="form-check-label" for="api_key_required">
                                                        Require API Key
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="api_rate_limit" class="form-label">Rate Limit (requests per hour)</label>
                                                <input type="number" class="form-control" id="api_rate_limit" name="api_rate_limit" min="1" max="10000">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="api_cors_origins" class="form-label">CORS Origins</label>
                                                <input type="text" class="form-control" id="api_cors_origins" name="api_cors_origins" placeholder="e.g., * or https://example.com">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i>Save API Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Backup Tab -->
                                <div class="tab-pane fade" id="backup" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Create Backup</h6>
                                            <p class="text-muted">Create a full database backup of the system</p>
                                            <button class="btn btn-success" id="createBackupBtn">
                                                <i class="bi bi-database-add me-1"></i>Create Backup
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Available Backups</h6>
                                            <div id="backupsList">
                                                <!-- Backups will be loaded via AJAX -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="new_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="new_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_role_id" class="form-label">Role</label>
                            <select class="form-select" id="new_role_id" name="role_id" required>
                                <!-- Roles will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role_id" class="form-label">Role</label>
                            <select class="form-select" id="edit_role_id" name="role_id" required>
                                <!-- Roles will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                                <label class="form-check-label" for="edit_is_active">
                                    Active User
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Desktop sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');

                    const icon = this.querySelector('i');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.className = 'bi bi-chevron-right';
                    } else {
                        icon.className = 'bi bi-chevron-left';
                    }
                });
            }

            // Mobile menu toggle
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Initialize settings
            initializeSettings();
        });

        // Initialize all settings functionality
        function initializeSettings() {
            loadUsers();
            loadRoles();
            loadApiSettings();
            loadHomepageContent();
            loadBackups();

            // Form submissions
            setupFormHandlers();
        }

        // Setup form event handlers
        function setupFormHandlers() {
            // General settings form
            document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveGeneralSettings();
            });

            // Add user form
            document.getElementById('addUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                addUser();
            });

            // Edit user form
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateUser();
            });

            // API settings form
            document.getElementById('apiSettingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveApiSettings();
            });

            // Create backup button
            document.getElementById('createBackupBtn').addEventListener('click', function() {
                createBackup();
            });
        }

        // General settings functions
        function saveGeneralSettings() {
            const formData = new FormData(document.getElementById('generalSettingsForm'));
            const settings = {};
            for (let [key, value] of formData.entries()) {
                settings[key] = value;
            }

            fetch('/admin/settings/updateSchoolSettings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ settings: settings })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('General settings saved successfully!', 'success');
                } else {
                    showAlert('Failed to save settings', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        // User management functions
        function loadUsers() {
            fetch('/admin/settings/getUsers')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsers(data.data);
                }
            })
            .catch(error => console.error('Failed to load users:', error));
        }

        function displayUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';

            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td><span class="badge bg-secondary">${user.role_name}</span></td>
                    <td>
                        <span class="badge ${user.is_active ? 'bg-success' : 'bg-danger'} user-status-badge">
                            ${user.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(${user.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function addUser() {
            const formData = new FormData(document.getElementById('addUserForm'));
            const userData = {};
            for (let [key, value] of formData.entries()) {
                userData[key] = value;
            }

            fetch('/admin/settings/createUser', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                    document.getElementById('addUserForm').reset();
                    loadUsers();
                    showAlert('User added successfully!', 'success');
                } else {
                    showAlert(data.message || 'Failed to add user', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        function editUser(userId) {
            // Load user data and populate edit modal
            fetch('/admin/settings/getUsers')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data.find(u => u.id == userId);
                    if (user) {
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_username').value = user.username;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_role_id').value = user.role_id;
                        document.getElementById('edit_is_active').checked = user.is_active;

                        // Load roles for dropdown
                        loadRolesForSelect('edit_role_id');

                        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        modal.show();
                    }
                }
            });
        }

        function updateUser() {
            const formData = new FormData(document.getElementById('editUserForm'));
            const userData = {};
            for (let [key, value] of formData.entries()) {
                userData[key] = value;
            }

            fetch('/admin/settings/updateUser', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                    loadUsers();
                    showAlert('User updated successfully!', 'success');
                } else {
                    showAlert(data.message || 'Failed to update user', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('/admin/settings/deleteUser', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadUsers();
                        showAlert('User deleted successfully!', 'success');
                    } else {
                        showAlert(data.message || 'Failed to delete user', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'danger');
                });
            }
        }

        // Role management functions
        function loadRoles() {
            fetch('/admin/settings/getRoles')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRoles(data.data);
                    loadRolesForSelect('new_role_id');
                }
            })
            .catch(error => console.error('Failed to load roles:', error));
        }

        function displayRoles(roles) {
            const rolesList = document.getElementById('rolesList');
            rolesList.innerHTML = '';

            roles.forEach(role => {
                const roleItem = document.createElement('a');
                roleItem.className = 'list-group-item list-group-item-action';
                roleItem.href = '#';
                roleItem.textContent = role.role_name;
                roleItem.onclick = () => loadPermissions(role.id, role.role_name);
                rolesList.appendChild(roleItem);
            });
        }

        function loadRolesForSelect(selectId) {
            fetch('/admin/settings/getRoles')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById(selectId);
                    select.innerHTML = '';
                    data.data.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.id;
                        option.textContent = role.role_name;
                        select.appendChild(option);
                    });
                }
            });
        }

        function loadPermissions(roleId, roleName) {
            fetch(`/admin/settings/getPermissions?role_id=${roleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPermissions(roleId, roleName, data.data);
                }
            })
            .catch(error => console.error('Failed to load permissions:', error));
        }

        function displayPermissions(roleId, roleName, permissions) {
            const panel = document.getElementById('permissionsPanel');
            panel.innerHTML = `
                <h6>Permissions for ${roleName}</h6>
                <form id="permissionsForm">
                    <div class="row">
                        ${permissions.map(perm => `
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <strong>${perm.module_name}</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   id="view_${perm.module_name}" name="permissions[${perm.module_name}][can_view]"
                                                   ${perm.can_view ? 'checked' : ''}>
                                            <label class="form-check-label" for="view_${perm.module_name}">
                                                View
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   id="create_${perm.module_name}" name="permissions[${perm.module_name}][can_create]"
                                                   ${perm.can_create ? 'checked' : ''}>
                                            <label class="form-check-label" for="create_${perm.module_name}">
                                                Create
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   id="edit_${perm.module_name}" name="permissions[${perm.module_name}][can_edit]"
                                                   ${perm.can_edit ? 'checked' : ''}>
                                            <label class="form-check-label" for="edit_${perm.module_name}">
                                                Edit
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   id="delete_${perm.module_name}" name="permissions[${perm.module_name}][can_delete]"
                                                   ${perm.can_delete ? 'checked' : ''}>
                                            <label class="form-check-label" for="delete_${perm.module_name}">
                                                Delete
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Permissions
                    </button>
                </form>
            `;

            // Add form submit handler
            document.getElementById('permissionsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                savePermissions(roleId);
            });
        }

        function savePermissions(roleId) {
            const formData = new FormData(document.getElementById('permissionsForm'));
            const permissions = {};

            for (let [key, value] of formData.entries()) {
                if (key.startsWith('permissions[')) {
                    const matches = key.match(/permissions\[(.+?)\]\[(.+?)\]/);
                    if (matches) {
                        const module = matches[1];
                        const permission = matches[2];
                        if (!permissions[module]) {
                            permissions[module] = {};
                        }
                        permissions[module][permission] = value === 'on';
                    }
                }
            }

            fetch('/admin/settings/updatePermissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ role_id: roleId, permissions: permissions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Permissions updated successfully!', 'success');
                } else {
                    showAlert('Failed to update permissions', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        // Homepage functions
        function loadHomepageContent() {
            fetch('/admin/settings/getHomepageContent')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayHomepageContent(data.data);
                }
            })
            .catch(error => console.error('Failed to load homepage content:', error));
        }

        function displayHomepageContent(content) {
            const container = document.getElementById('homepageContent');
            container.innerHTML = `
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="addHomepageSection()">
                        <i class="bi bi-plus-circle me-1"></i>Add Section
                    </button>
                </div>
                <div class="row">
                    ${content.map(item => `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">${item.title || 'Untitled'}</h6>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editHomepageSection(${item.id})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteHomepageSection(${item.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">${item.content ? item.content.substring(0, 100) + '...' : 'No content'}</p>
                                    <small class="text-muted">Order: ${item.display_order}</small>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // API settings functions
        function loadApiSettings() {
            fetch('/admin/settings/getApiSettings')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateApiSettings(data.data);
                }
            })
            .catch(error => console.error('Failed to load API settings:', error));
        }

        function populateApiSettings(settings) {
            document.getElementById('api_enabled').checked = settings.api_enabled == '1';
            document.getElementById('api_key_required').checked = settings.api_key_required == '1';
            document.getElementById('api_rate_limit').value = settings.api_rate_limit || '100';
            document.getElementById('api_cors_origins').value = settings.api_cors_origins || '*';
        }

        function saveApiSettings() {
            const formData = new FormData(document.getElementById('apiSettingsForm'));
            const settings = {};
            for (let [key, value] of formData.entries()) {
                if (key === 'api_enabled' || key === 'api_key_required') {
                    settings[key] = document.getElementById(key).checked ? '1' : '0';
                } else {
                    settings[key] = value;
                }
            }

            fetch('/admin/settings/updateApiSettings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ settings: settings })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('API settings saved successfully!', 'success');
                } else {
                    showAlert('Failed to save API settings', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        // Backup functions
        function loadBackups() {
            fetch('/admin/settings/getBackups')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBackups(data.data);
                }
            })
            .catch(error => console.error('Failed to load backups:', error));
        }

        function displayBackups(backups) {
            const container = document.getElementById('backupsList');
            if (backups.length === 0) {
                container.innerHTML = '<p class="text-muted">No backups available</p>';
                return;
            }

            container.innerHTML = `
                <div class="list-group">
                    ${backups.map(backup => `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${backup.filename}</strong>
                                <br>
                                <small class="text-muted">Created: ${backup.created_at} | Size: ${formatFileSize(backup.size)}</small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-success me-1" onclick="restoreBackup('${backup.filename}')">
                                    <i class="bi bi-database-arrow-up"></i> Restore
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup('${backup.filename}')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function createBackup() {
            document.getElementById('createBackupBtn').disabled = true;
            document.getElementById('createBackupBtn').innerHTML = '<i class="bi bi-hourglass me-1"></i>Creating...';

            fetch('/admin/settings/createBackup', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('createBackupBtn').disabled = false;
                document.getElementById('createBackupBtn').innerHTML = '<i class="bi bi-database-add me-1"></i>Create Backup';

                if (data.success) {
                    showAlert('Backup created successfully!', 'success');
                    loadBackups();
                } else {
                    showAlert('Failed to create backup', 'danger');
                }
            })
            .catch(error => {
                document.getElementById('createBackupBtn').disabled = false;
                document.getElementById('createBackupBtn').innerHTML = '<i class="bi bi-database-add me-1"></i>Create Backup';
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            });
        }

        function restoreBackup(filename) {
            if (confirm('Are you sure you want to restore from this backup? This will overwrite current data.')) {
                fetch('/admin/settings/restoreBackup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ backup_file: filename })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Database restored successfully! Please refresh the page.', 'success');
                    } else {
                        showAlert(data.message || 'Failed to restore backup', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'danger');
                });
            }
        }

        // Utility functions
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Placeholder functions for homepage management
        function addHomepageSection() {
            showAlert('Homepage section management coming soon!', 'info');
        }

        function editHomepageSection(id) {
            showAlert('Edit functionality coming soon!', 'info');
        }

        function deleteHomepageSection(id) {
            showAlert('Delete functionality coming soon!', 'info');
        }

        function downloadBackup(filename) {
            showAlert('Download functionality coming soon!', 'info');
        }
    </script>
</body>
</html>