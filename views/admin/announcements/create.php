<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Add New Announcement'); ?></title>
    <meta name="description" content="Add new announcement to the school management system">

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
        .form-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section h5 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .priority-option {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .priority-option:hover {
            border-color: #dee2e6;
        }
        .priority-option.selected {
            border-color: #007bff;
            background-color: #e7f3ff;
        }
        .visibility-option {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .visibility-option:hover {
            border-color: #dee2e6;
        }
        .visibility-option.selected {
            border-color: #28a745;
            background-color: #e8f5e8;
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
                    <img src="/images/logo-small.png" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle">SMS</span>
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
                    <a class="nav-link active" href="/admin/announcements">
                        <i class="bi bi-megaphone"></i>
                        <span>Announcements</span>
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
                        <div class="fw-bold small">Admin</div>
                        <div class="text-muted small">Administrator</div>
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
                        <h5 class="mb-0">Add New Announcement</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/announcements">Announcements</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add New</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="/admin/announcements" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Announcements
                    </a>
                </div>
            </div>
        </header>

        <!-- Announcement Form Content -->
        <main class="p-4">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form action="/admin/announcements" method="POST" id="announcementForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h5><i class="bi bi-megaphone me-2"></i>Announcement Information</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['title']) ? 'is-invalid' : ''; ?>"
                                   id="title" name="title"
                                   value="<?php echo htmlspecialchars($old_input['title'] ?? ''); ?>" required
                                   placeholder="Enter announcement title">
                            <?php if (isset($validation_errors['title'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['title'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" <?php echo ($old_input['is_active'] ?? '1') === '1' ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($old_input['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Announcement Content <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php echo isset($validation_errors['content']) ? 'is-invalid' : ''; ?>"
                                  id="content" name="content" rows="6"
                                  placeholder="Enter the announcement content"><?php echo htmlspecialchars($old_input['content'] ?? ''); ?></textarea>
                        <?php if (isset($validation_errors['content'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['content'][0]); ?></div>
                        <?php endif; ?>
                        <div class="form-text">Provide detailed content for the announcement</div>
                    </div>
                </div>

                <!-- Priority Settings -->
                <div class="form-section">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Priority Level</h5>
                    <p class="text-muted">Select the priority level for this announcement</p>
                    <input type="hidden" id="priority" name="priority" value="<?php echo htmlspecialchars($old_input['priority'] ?? 'medium'); ?>">

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="priority-option card text-center p-3 <?php echo ($old_input['priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>" data-priority="low">
                                <div class="text-success mb-2">
                                    <i class="bi bi-info-circle fs-2"></i>
                                </div>
                                <h6 class="mb-1">Low Priority</h6>
                                <small class="text-muted">General information</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="priority-option card text-center p-3 <?php echo ($old_input['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>" data-priority="medium">
                                <div class="text-info mb-2">
                                    <i class="bi bi-dash-circle fs-2"></i>
                                </div>
                                <h6 class="mb-1">Medium Priority</h6>
                                <small class="text-muted">Important information</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="priority-option card text-center p-3 <?php echo ($old_input['priority'] ?? '') === 'high' ? 'selected' : ''; ?>" data-priority="high">
                                <div class="text-warning mb-2">
                                    <i class="bi bi-exclamation-triangle fs-2"></i>
                                </div>
                                <h6 class="mb-1">High Priority</h6>
                                <small class="text-muted">Urgent attention needed</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="priority-option card text-center p-3 <?php echo ($old_input['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>" data-priority="urgent">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-exclamation-circle fs-2"></i>
                                </div>
                                <h6 class="mb-1">Urgent Priority</h6>
                                <small class="text-muted">Critical information</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visibility Settings -->
                <div class="form-section">
                    <h5><i class="bi bi-eye me-2"></i>Visibility Settings</h5>
                    <p class="text-muted">Select who can see this announcement</p>
                    <input type="hidden" id="visibility" name="visibility" value="<?php echo htmlspecialchars($old_input['visibility'] ?? 'all'); ?>">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="visibility-option card text-center p-3 <?php echo ($old_input['visibility'] ?? 'all') === 'all' ? 'selected' : ''; ?>" data-visibility="all">
                                <div class="text-primary mb-2">
                                    <i class="bi bi-people fs-2"></i>
                                </div>
                                <h6 class="mb-1">All Users</h6>
                                <small class="text-muted">Visible to everyone</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="visibility-option card text-center p-3 <?php echo ($old_input['visibility'] ?? '') === 'students' ? 'selected' : ''; ?>" data-visibility="students">
                                <div class="text-success mb-2">
                                    <i class="bi bi-mortarboard fs-2"></i>
                                </div>
                                <h6 class="mb-1">Students Only</h6>
                                <small class="text-muted">Students and parents</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="visibility-option card text-center p-3 <?php echo ($old_input['visibility'] ?? '') === 'teachers' ? 'selected' : ''; ?>" data-visibility="teachers">
                                <div class="text-info mb-2">
                                    <i class="bi bi-person-badge fs-2"></i>
                                </div>
                                <h6 class="mb-1">Teachers Only</h6>
                                <small class="text-muted">Teaching staff</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="visibility-option card text-center p-3 <?php echo ($old_input['visibility'] ?? '') === 'parents' ? 'selected' : ''; ?>" data-visibility="parents">
                                <div class="text-warning mb-2">
                                    <i class="bi bi-house-door fs-2"></i>
                                </div>
                                <h6 class="mb-1">Parents Only</h6>
                                <small class="text-muted">Parents and guardians</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="visibility-option card text-center p-3 <?php echo ($old_input['visibility'] ?? '') === 'admin' ? 'selected' : ''; ?>" data-visibility="admin">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-shield-lock fs-2"></i>
                                </div>
                                <h6 class="mb-1">Admin Only</h6>
                                <small class="text-muted">Administrators only</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiration Settings -->
                <div class="form-section">
                    <h5><i class="bi bi-calendar-x me-2"></i>Expiration Settings</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                                   value="<?php echo htmlspecialchars($old_input['expires_at'] ?? ''); ?>">
                            <div class="form-text">Leave empty for no expiration</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="/admin/announcements" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <div>
                        <button type="reset" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Announcement
                        </button>
                    </div>
                </div>
            </form>
        </main>
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

            // Priority selection
            document.querySelectorAll('.priority-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.priority-option').forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('priority').value = this.dataset.priority;
                });
            });

            // Visibility selection
            document.querySelectorAll('.visibility-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.visibility-option').forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('visibility').value = this.dataset.visibility;
                });
            });
        });
    </script>
</body>
</html>