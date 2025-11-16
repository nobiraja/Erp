<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Edit Event'); ?></title>
    <meta name="description" content="Edit event in the school management system">

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
        .image-preview {
            width: 200px;
            height: 150px;
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            margin-bottom: 1rem;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.375rem;
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
                    <a class="nav-link active" href="/admin/events">
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
                        <h5 class="mb-0">Edit Event</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/events">Events</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="/admin/events" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Events
                    </a>
                </div>
            </div>
        </header>

        <!-- Event Form Content -->
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

            <form action="/admin/events/<?php echo $event['id']; ?>" method="POST" enctype="multipart/form-data" id="eventForm">
                <input type="hidden" name="_method" value="PUT">

                <!-- Basic Information -->
                <div class="form-section">
                    <h5><i class="bi bi-calendar-event me-2"></i>Event Information</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['title']) ? 'is-invalid' : ''; ?>"
                                   id="title" name="title"
                                   value="<?php echo htmlspecialchars($old_input['title'] ?? $event['title'] ?? ''); ?>" required
                                   placeholder="Enter event title">
                            <?php if (isset($validation_errors['title'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['title'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" <?php echo ($old_input['is_active'] ?? $event['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($old_input['is_active'] ?? $event['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?php echo isset($validation_errors['event_date']) ? 'is-invalid' : ''; ?>"
                                   id="event_date" name="event_date"
                                   value="<?php echo htmlspecialchars($old_input['event_date'] ?? $event['event_date'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['event_date'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['event_date'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time"
                                   value="<?php echo htmlspecialchars($old_input['event_time'] ?? $event['event_time'] ?? ''); ?>">
                            <div class="form-text">Leave empty if time is not specified</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location"
                                   value="<?php echo htmlspecialchars($old_input['location'] ?? $event['location'] ?? ''); ?>"
                                   placeholder="e.g., School Auditorium, Ground, etc.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="organizer" class="form-label">Organizer</label>
                            <input type="text" class="form-control" id="organizer" name="organizer"
                                   value="<?php echo htmlspecialchars($old_input['organizer'] ?? $event['organizer'] ?? ''); ?>"
                                   placeholder="e.g., Sports Department, Cultural Committee">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_info" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="contact_info" name="contact_info"
                                   value="<?php echo htmlspecialchars($old_input['contact_info'] ?? $event['contact_info'] ?? ''); ?>"
                                   placeholder="Phone number or email for inquiries">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Event Description</label>
                        <textarea class="form-control <?php echo isset($validation_errors['description']) ? 'is-invalid' : ''; ?>"
                                  id="description" name="description" rows="4"
                                  placeholder="Provide detailed description of the event"><?php echo htmlspecialchars($old_input['description'] ?? $event['description'] ?? ''); ?></textarea>
                        <?php if (isset($validation_errors['description'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['description'][0]); ?></div>
                        <?php endif; ?>
                        <div class="form-text">Describe the event, activities, and any special instructions</div>
                    </div>
                </div>

                <!-- Event Image -->
                <div class="form-section">
                    <h5><i class="bi bi-image me-2"></i>Event Image</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Upload New Event Image</label>
                            <input type="file" class="form-control" id="image" name="image"
                                   accept="image/*" onchange="previewImage(this)">
                            <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 5MB. Leave empty to keep current image.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current/Preview Image</label>
                            <div class="image-preview" id="imagePreview">
                                <?php if (($old_input['image_path'] ?? $event['image_path'] ?? null) && file_exists($old_input['image_path'] ?? $event['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($old_input['image_path'] ?? $event['image_path']); ?>" alt="Current Event Image">
                                <?php else: ?>
                                    <i class="bi bi-calendar-event text-muted fs-1"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="/admin/events" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <div>
                        <button type="reset" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Update Event
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
        });

        // Preview image before upload
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                // Reset to current image or placeholder
                preview.innerHTML = `
                    <?php if (($old_input['image_path'] ?? $event['image_path'] ?? null) && file_exists($old_input['image_path'] ?? $event['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($old_input['image_path'] ?? $event['image_path']); ?>" alt="Current Event Image">
                    <?php else: ?>
                        <i class="bi bi-calendar-event text-muted fs-1"></i>
                    <?php endif; ?>
                `;
            }
        }
    </script>
</body>
</html>