<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Marksheets'); ?></title>
    <meta name="description" content="Generate marksheets for exams in the school management system">

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
        .card-preview {
            border: 2px dashed #dee2e6;
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .student-checkbox {
            margin-right: 10px;
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
                        <h5 class="mb-0">Marksheets - <?php echo htmlspecialchars($exam->exam_name); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Marksheets</li>
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

        <!-- Marksheets Content -->
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

            <!-- Exam Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1"><?php echo htmlspecialchars($exam->exam_name); ?></h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($exam->class()->class_name . ' ' . $exam->class()->section); ?> |
                                <i class="bi bi-calendar me-1"></i><?php echo date('d/m/Y', strtotime($exam->start_date)); ?> - <?php echo date('d/m/Y', strtotime($exam->end_date)); ?> |
                                <i class="bi bi-tag me-1"></i><?php echo ucfirst(str_replace('-', ' ', $exam->exam_type)); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-<?php echo $exam->is_active ? 'success' : 'danger'; ?> fs-6">
                                <?php echo $exam->is_active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Generation Options -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Generation Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="marksheetForm">
                                <input type="hidden" name="exam_id" value="<?php echo $exam->id; ?>">

                                <!-- Format Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Format</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="format" id="formatBulk" value="bulk" checked>
                                                <label class="form-check-label" for="formatBulk">
                                                    <strong>Bulk Generation</strong>
                                                    <br><small class="text-muted">Generate marksheets for all students (1-2 sheets per page)</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="format" id="formatIndividual" value="individual">
                                                <label class="form-check-label" for="formatIndividual">
                                                    <strong>Individual Generation</strong>
                                                    <br><small class="text-muted">Generate one marksheet per page for selected students</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Selection -->
                                <div class="mb-3" id="studentSelection" style="display: none;">
                                    <label class="form-label">Select Students</label>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div class="mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllStudents">
                                                <label class="form-check-label fw-bold" for="selectAllStudents">
                                                    Select All Students
                                                </label>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <?php foreach ($students as $student): ?>
                                            <div class="form-check">
                                                <input class="form-check-input student-checkbox" type="checkbox"
                                                       name="student_ids[]" value="<?php echo $student['id']; ?>"
                                                       id="student_<?php echo $student['id']; ?>">
                                                <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                    <?php echo htmlspecialchars($student['scholar_number'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" onclick="generateMarksheets()">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Marksheets
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="previewMarksheet()">
                                        <i class="bi bi-eye me-2"></i>Preview Sample
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Results Summary
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

                            <?php if (($stats['total_students'] ?? 0) > 0 && ($stats['total_results'] ?? 0) == 0): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No results have been entered yet. Please enter results before generating marksheets.
                                    <a href="/admin/exams/results?exam_id=<?php echo $exam->id; ?>" class="alert-link">Enter Results</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-eye me-2"></i>
                                Preview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="card-preview text-center">
                                <div>
                                    <i class="bi bi-file-earmark-pdf fs-1 text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Marksheet Preview</p>
                                    <small class="text-muted">Click "Preview Sample" to see how the marksheet will look</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6>Marksheet Includes:</h6>
                            <ul class="list-unstyled small mb-3">
                                <li><i class="bi bi-check-circle text-success me-2"></i>School name and logo</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Student details</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Subject-wise marks</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Grades and percentages</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Rank and totals</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Signature areas</li>
                            </ul>

                            <h6>Grade Scale:</h6>
                            <ul class="small text-muted">
                                <li>A+ (90-100%), A (80-89%)</li>
                                <li>B+ (70-79%), B (60-69%)</li>
                                <li>C+ (50-59%), C (40-49%)</li>
                                <li>D (33-39%), F (Below 33%)</li>
                            </ul>
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

            // Format selection handler
            document.querySelectorAll('input[name="format"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const studentSelection = document.getElementById('studentSelection');
                    if (this.value === 'individual') {
                        studentSelection.style.display = 'block';
                    } else {
                        studentSelection.style.display = 'none';
                    }
                });
            });

            // Select all students checkbox
            document.getElementById('selectAllStudents').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });

        // Generate marksheets
        function generateMarksheets() {
            const form = document.getElementById('marksheetForm');
            const formData = new FormData(form);

            const format = formData.get('format');
            const examId = formData.get('exam_id');

            let url = `/admin/exams/marksheets?exam_id=${examId}&format=${format}`;

            if (format === 'individual') {
                const selectedStudents = formData.getAll('student_ids[]');
                if (selectedStudents.length === 0) {
                    alert('Please select at least one student for individual generation.');
                    return;
                }
                url += '&student_ids=' + selectedStudents.join(',');
            }

            window.open(url, '_blank');
        }

        // Preview sample marksheet
        function previewMarksheet() {
            const examId = <?php echo $exam->id; ?>;
            window.open(`/admin/exams/preview-marksheet?exam_id=${examId}`, '_blank');
        }
    </script>
</body>
</html>