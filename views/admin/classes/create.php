<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Create Class'); ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #343a40; color: white; transition: all 0.3s; z-index: 1000; overflow-y: auto; }
        .sidebar.collapsed { width: 70px; }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: 0.75rem 1rem; transition: all 0.3s; }
        .sidebar .nav-link:hover { color: white; background: rgba(255,255,255,.1); }
        .sidebar .nav-link.active { color: white; background: #007bff; }
        .sidebar .nav-link i { width: 20px; margin-right: 10px; }
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .main-content { margin-left: 250px; transition: margin-left 0.3s; }
        .main-content.expanded { margin-left: 70px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .hamburger-menu { display: block; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; }
            .sidebar-overlay.show { display: block; }
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
                    <a class="nav-link active" href="/admin/classes">
                        <i class="bi bi-book"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/subjects">
                        <i class="bi bi-journal-text"></i>
                        <span>Subjects</span>
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
                        <h5 class="mb-0">Create Class</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes">Classes</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Create</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/classes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Classes
                    </a>
                </div>
            </div>
        </header>

        <!-- Create Class Content -->
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

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-plus-circle me-2"></i>
                                Create New Class
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="/admin/classes" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="class_name" class="form-label">Class Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="class_name" name="class_name"
                                               value="<?php echo htmlspecialchars($this->input('class_name', '')); ?>"
                                               placeholder="e.g., 10th, 11th, 12th" required>
                                        <div class="form-text">Enter the class name (e.g., 1st, 2nd, 10th)</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
                                        <select class="form-select" id="section" name="section" required>
                                            <option value="">Select Section</option>
                                            <option value="A" <?php echo $this->input('section') === 'A' ? 'selected' : ''; ?>>A</option>
                                            <option value="B" <?php echo $this->input('section') === 'B' ? 'selected' : ''; ?>>B</option>
                                            <option value="C" <?php echo $this->input('section') === 'C' ? 'selected' : ''; ?>>C</option>
                                            <option value="D" <?php echo $this->input('section') === 'D' ? 'selected' : ''; ?>>D</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-select" id="academic_year" name="academic_year" required>
                                            <option value="">Select Academic Year</option>
                                            <?php
                                            $currentYear = date('Y');
                                            for ($i = $currentYear - 1; $i <= $currentYear + 2; $i++):
                                                $year = $i . '-' . ($i + 1);
                                            ?>
                                                <option value="<?php echo $year; ?>" <?php echo $this->input('academic_year') === $year ? 'selected' : ''; ?>>
                                                    <?php echo $year; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="class_teacher_id" class="form-label">Class Teacher</label>
                                        <select class="form-select" id="class_teacher_id" name="class_teacher_id">
                                            <option value="">Select Class Teacher (Optional)</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?php echo $teacher->id; ?>" <?php echo $this->input('class_teacher_id') == $teacher->id ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($teacher->getFullName() . ' (' . $teacher->employee_id . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Assign a teacher as the class teacher</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Create Class
                                    </button>
                                    <a href="/admin/classes" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
    </script>
</body>
</html>