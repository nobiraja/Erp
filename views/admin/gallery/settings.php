<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Gallery Settings'); ?></title>
    <meta name="description" content="Configure gallery settings and preferences">

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
        .setting-group {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        .setting-group-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .setting-group-body {
            padding: 1rem;
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
                    <a class="nav-link active" href="/admin/gallery">
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
                    <a class="nav-link" href="/admin/settings">
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
                        <h5 class="mb-0">Gallery Settings</h5>
                        <small class="text-muted">Configure gallery behavior and preferences</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary me-2" onclick="saveSettings()">
                        <i class="bi bi-check-circle me-1"></i>Save Settings
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/admin/change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Settings Content -->
        <main class="p-4">
            <form id="settingsForm">
                <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">

                <!-- Upload Settings -->
                <div class="setting-group">
                    <div class="setting-group-header">
                        <h6 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Settings</h6>
                    </div>
                    <div class="setting-group-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maxFileSize" class="form-label">Maximum File Size (bytes)</label>
                                    <input type="number" class="form-control" id="maxFileSize" name="settings[max_file_size]"
                                           value="<?php echo htmlspecialchars($settings['max_file_size']['setting_value'] ?? '10485760'); ?>">
                                    <div class="form-text">Default: 10MB (10485760 bytes)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemsPerPage" class="form-label">Items Per Page</label>
                                    <input type="number" class="form-control" id="itemsPerPage" name="settings[items_per_page]"
                                           value="<?php echo htmlspecialchars($settings['items_per_page']['setting_value'] ?? '12'); ?>" min="1" max="50">
                                    <div class="form-text">Number of items to display per page</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="allowedImageTypes" class="form-label">Allowed Image Types</label>
                                    <input type="text" class="form-control" id="allowedImageTypes" name="settings[allowed_image_types]"
                                           value="<?php echo htmlspecialchars($settings['allowed_image_types']['setting_value'] ?? 'jpg,jpeg,png,gif,webp'); ?>">
                                    <div class="form-text">Comma-separated list of extensions</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="allowedVideoTypes" class="form-label">Allowed Video Types</label>
                                    <input type="text" class="form-control" id="allowedVideoTypes" name="settings[allowed_video_types]"
                                           value="<?php echo htmlspecialchars($settings['allowed_video_types']['setting_value'] ?? 'mp4,avi,mov,wmv,flv,webm'); ?>">
                                    <div class="form-text">Comma-separated list of extensions</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thumbnail Settings -->
                <div class="setting-group">
                    <div class="setting-group-header">
                        <h6 class="mb-0"><i class="bi bi-image me-2"></i>Thumbnail Settings</h6>
                    </div>
                    <div class="setting-group-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="thumbnailWidth" class="form-label">Thumbnail Width (px)</label>
                                    <input type="number" class="form-control" id="thumbnailWidth" name="settings[thumbnail_width]"
                                           value="<?php echo htmlspecialchars($settings['thumbnail_width']['setting_value'] ?? '300'); ?>" min="100" max="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="thumbnailHeight" class="form-label">Thumbnail Height (px)</label>
                                    <input type="number" class="form-control" id="thumbnailHeight" name="settings[thumbnail_height]"
                                           value="<?php echo htmlspecialchars($settings['thumbnail_height']['setting_value'] ?? '200'); ?>" min="100" max="1000">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autoGenerateThumbnails" name="settings[auto_generate_thumbnails]"
                                       value="1" <?php echo ($settings['auto_generate_thumbnails']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="autoGenerateThumbnails">
                                    Automatically generate thumbnails for uploaded images
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="setting-group">
                    <div class="setting-group-header">
                        <h6 class="mb-0"><i class="bi bi-display me-2"></i>Display Settings</h6>
                    </div>
                    <div class="setting-group-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enableLazyLoading" name="settings[enable_lazy_loading]"
                                               value="1" <?php echo ($settings['enable_lazy_loading']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enableLazyLoading">
                                            Enable lazy loading for images
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enableVideoPlayer" name="settings[enable_video_player]"
                                               value="1" <?php echo ($settings['enable_video_player']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enableVideoPlayer">
                                            Enable video player for video files
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="syncWithPublic" name="settings[sync_with_public]"
                                       value="1" <?php echo ($settings['sync_with_public']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="syncWithPublic">
                                    Sync gallery changes with public website automatically
                                </label>
                            </div>
                            <div class="form-text">When enabled, gallery updates will be reflected on the public website immediately</div>
                        </div>
                    </div>
                </div>

                <!-- Default Categories -->
                <div class="setting-group">
                    <div class="setting-group-header">
                        <h6 class="mb-0"><i class="bi bi-folder-plus me-2"></i>Default Categories</h6>
                    </div>
                    <div class="setting-group-body">
                        <p class="text-muted">Default gallery categories have been created. You can manage them from the <a href="/admin/gallery/categories">Categories</a> page.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Default Categories:</strong> Events, Sports, Cultural, Academics, Infrastructure, Student Life
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="setting-group">
                    <div class="setting-group-header">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h6>
                    </div>
                    <div class="setting-group-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Upload Directory</label>
                                    <input type="text" class="form-control" value="uploads/gallery" readonly>
                                    <div class="form-text">Directory where media files are stored</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">PHP Upload Limits</label>
                                    <div class="small">
                                        <div>Max File Size: <?php echo ini_get('upload_max_filesize'); ?></div>
                                        <div>Max Post Size: <?php echo ini_get('post_max_size'); ?></div>
                                        <div>Memory Limit: <?php echo ini_get('memory_limit'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupSidebar();
        });

        function setupSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

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

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
        }

        function saveSettings() {
            const form = document.getElementById('settingsForm');
            const formData = new FormData(form);

            // Convert checkbox values
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    formData.set(checkbox.name, '0');
                }
            });

            fetch('/admin/gallery/updateSettings', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Failed to save settings: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving settings');
            });
        }
    </script>
</body>
</html>