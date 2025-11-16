<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Edit Exam'); ?></title>
    <meta name="description" content="Edit exam details in the school management system">

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
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
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
                    <a class="nav-link active" href="/admin/exams">
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
                        <h5 class="mb-0">Edit Exam</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/exams" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Exams
                    </a>
                </div>
            </div>
        </header>

        <!-- Edit Exam Content -->
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

            <!-- Exam Edit Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pencil me-2"></i>
                                Exam Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="/admin/exams/<?php echo $exam->id; ?>/update" method="POST" id="examForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="exam_name" class="form-label required-field">Exam Name</label>
                                        <input type="text" class="form-control" id="exam_name" name="exam_name"
                                               value="<?php echo htmlspecialchars($exam->exam_name); ?>"
                                               placeholder="e.g., Mid-term Examination 2024" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid exam name.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="exam_type" class="form-label required-field">Exam Type</label>
                                        <select class="form-select" id="exam_type" name="exam_type" required>
                                            <option value="">Select Exam Type</option>
                                            <?php foreach ($exam_types as $type): ?>
                                                <option value="<?php echo $type; ?>" <?php echo $exam->exam_type === $type ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst(str_replace('-', ' ', $type)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select an exam type.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="class_id" class="form-label required-field">Class</label>
                                        <select class="form-select" id="class_id" name="class_id" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>" <?php echo $exam->class_id == $class['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a class.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="academic_year" class="form-label required-field">Academic Year</label>
                                        <select class="form-select" id="academic_year" name="academic_year" required>
                                            <option value="">Select Academic Year</option>
                                            <?php for ($year = date('Y') + 1; $year >= date('Y') - 2; $year--): ?>
                                                <option value="<?php echo $year . '-' . ($year + 1); ?>" <?php echo $exam->academic_year === $year . '-' . ($year + 1) ? 'selected' : ''; ?>>
                                                    <?php echo $year . '-' . ($year + 1); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select an academic year.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label required-field">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                               value="<?php echo $exam->start_date; ?>" required>
                                        <div class="invalid-feedback">
                                            Please select a valid start date.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label required-field">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                               value="<?php echo $exam->end_date; ?>" required>
                                        <div class="invalid-feedback">
                                            Please select a valid end date.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   <?php echo $exam->is_active ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                Active Exam
                                            </label>
                                            <div class="form-text">
                                                Uncheck this to deactivate the exam.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="/admin/exams" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Update Exam
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Exam Statistics Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Exam Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php $stats = $exam->getStatistics(); ?>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-1"><?php echo $stats['total_students'] ?? 0; ?></h4>
                                        <small class="text-muted">Total Students</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-success mb-1"><?php echo $stats['total_results'] ?? 0; ?></h4>
                                        <small class="text-muted">Results Entered</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end">
                                        <h4 class="text-info mb-1"><?php echo number_format($stats['avg_marks'] ?? 0, 1); ?></h4>
                                        <small class="text-muted">Average Marks</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning mb-1"><?php echo number_format($stats['avg_percentage'] ?? 0, 1); ?>%</h4>
                                    <small class="text-muted">Average %</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <a href="/admin/exams/results?exam_id=<?php echo $exam->id; ?>" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-pencil-square me-2"></i>Enter Results
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="/admin/exams/schedule?exam_id=<?php echo $exam->id; ?>" class="btn btn-outline-info w-100">
                                        <i class="bi bi-calendar me-2"></i>Manage Schedule
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button onclick="generateAdmitCards(<?php echo $exam->id; ?>)" class="btn btn-outline-success w-100">
                                        <i class="bi bi-card-text me-2"></i>Admit Cards
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button onclick="generateMarksheets(<?php echo $exam->id; ?>)" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Marksheets
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

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

            // Form validation
            const examForm = document.getElementById('examForm');
            if (examForm) {
                examForm.addEventListener('submit', function(e) {
                    if (!examForm.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    examForm.classList.add('was-validated');
                });
            }

            // Date validation
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function validateDates() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (startDate && endDate && endDate < startDate) {
                    endDateInput.setCustomValidity('End date cannot be before start date');
                } else {
                    endDateInput.setCustomValidity('');
                }
            }

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', validateDates);
                endDateInput.addEventListener('change', validateDates);
            }
        });

        // Generate admit cards
        function generateAdmitCards(examId) {
            window.open(`/admin/exams/admit-cards?exam_id=${examId}&format=bulk`, '_blank');
        }

        // Generate marksheets
        function generateMarksheets(examId) {
            window.open(`/admin/exams/marksheets?exam_id=${examId}&format=bulk`, '_blank');
        }
    </script>
</body>
</html>