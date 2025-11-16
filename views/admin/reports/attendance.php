<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Attendance Reports'); ?></title>
    <meta name="description" content="Comprehensive attendance reports with detailed tracking and analysis">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Chart.js for interactive charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .report-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
        }
        .attendance-high { color: #28a745; font-weight: bold; }
        .attendance-medium { color: #ffc107; font-weight: bold; }
        .attendance-low { color: #dc3545; font-weight: bold; }
        .present-badge { background-color: #d4edda; color: #155724; }
        .absent-badge { background-color: #f8d7da; color: #721c24; }
        .late-badge { background-color: #fff3cd; color: #856404; }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
                    <a class="nav-link" href="/admin/gallery">
                        <i class="bi bi-images"></i>
                        <span>Gallery</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/reports">
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
                        <h5 class="mb-0"><?php echo htmlspecialchars($title); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/reports">Reports</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/reports" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Reports
                    </a>
                    <button type="button" class="btn btn-primary" onclick="generateReport()">
                        <i class="bi bi-graph-up me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </header>

        <!-- Attendance Reports Content -->
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

            <!-- Report Filters -->
            <div class="filter-section">
                <form id="reportForm" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class->id; ?>" <?php echo $class_id == $class->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="student_id" class="form-label">Student (Optional)</label>
                        <select class="form-select" id="student_id" name="student_id">
                            <option value="">All Students</option>
                            <!-- Students will be loaded dynamically if class is selected -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Generate
                        </button>
                        <?php if ($report_data): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-info" onclick="exportReport('excel')">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Report Results -->
            <?php if ($report_data): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-check me-2"></i>
                            Attendance Report
                            <?php if ($class_id): ?>
                                - <?php echo htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id', 'id'))]->class_name ?? ''); ?>
                            <?php endif; ?>
                            <?php if ($student_id && isset($student)): ?>
                                - <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>
                            <?php endif; ?>
                            <?php if ($start_date && $end_date): ?>
                                (<?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?>)
                            <?php endif; ?>
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="printReport()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($student_id && isset($student)): ?>
                            <!-- Individual Student Report -->
                            <div class="report-card">
                                <h6 class="mb-3">
                                    Student: <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>
                                    (Scholar No: <?php echo htmlspecialchars($student->scholar_number); ?>)
                                </h6>

                                <!-- Attendance Overview -->
                                <div class="row mb-4">
                                    <?php
                                    $presentCount = 0;
                                    $absentCount = 0;
                                    $lateCount = 0;
                                    $totalDays = count($report_data);

                                    foreach ($report_data as $record) {
                                        if ($record->status === 'present') $presentCount++;
                                        elseif ($record->status === 'absent') $absentCount++;
                                        elseif ($record->status === 'late') $lateCount++;
                                    }

                                    $attendancePercentage = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 0;
                                    ?>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 text-success"><?php echo $presentCount; ?></div>
                                            <small class="text-muted">Present Days</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 text-danger"><?php echo $absentCount; ?></div>
                                            <small class="text-muted">Absent Days</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 text-warning"><?php echo $lateCount; ?></div>
                                            <small class="text-muted">Late Days</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 <?php echo $attendancePercentage >= 85 ? 'text-success' : ($attendancePercentage >= 70 ? 'text-warning' : 'text-danger'); ?>">
                                                <?php echo $attendancePercentage; ?>%
                                            </div>
                                            <small class="text-muted">Attendance Rate</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Trend Chart -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Attendance Trend</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="chart-container">
                                                    <canvas id="attendanceTrendChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Attendance Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Subject</th>
                                                <th>Class</th>
                                                <th>Marked By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $record): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($record->attendance_date)); ?></td>
                                                    <td>
                                                        <span class="badge <?php
                                                            echo $record->status === 'present' ? 'present-badge' :
                                                                 ($record->status === 'absent' ? 'absent-badge' : 'late-badge');
                                                        ?>">
                                                            <?php echo ucfirst($record->status); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($record->subject_name ?? 'General'); ?></td>
                                                    <td><?php echo htmlspecialchars($record->class_name . ' ' . $record->section); ?></td>
                                                    <td><?php echo htmlspecialchars($record->teacher_name ?? 'System'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Class/School Summary Report -->
                            <div class="row mb-4">
                                <!-- Attendance Overview Chart -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Attendance Overview</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="attendanceOverviewChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Performance Chart -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Class Performance</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="classPerformanceChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Statistics -->
                            <div class="row mb-4">
                                <?php
                                $totalStudents = count($report_data);
                                $excellentCount = 0;
                                $goodCount = 0;
                                $poorCount = 0;
                                $avgAttendance = 0;

                                foreach ($report_data as $record) {
                                    $percentage = $record['attendance_percentage'];
                                    $avgAttendance += $percentage;

                                    if ($percentage >= 85) $excellentCount++;
                                    elseif ($percentage >= 70) $goodCount++;
                                    else $poorCount++;
                                }

                                $avgAttendance = $totalStudents > 0 ? round($avgAttendance / $totalStudents, 1) : 0;
                                ?>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary"><?php echo $totalStudents; ?></div>
                                        <small class="text-muted">Total Students</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="h4 text-success"><?php echo $excellentCount; ?></div>
                                        <small class="text-muted">Excellent (≥85%)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning"><?php echo $goodCount; ?></div>
                                        <small class="text-muted">Good (70-84%)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="h4 text-danger"><?php echo $poorCount; ?></div>
                                        <small class="text-muted">Poor (<70%)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mb-4">
                                <div class="h5">Average Class Attendance:
                                    <strong class="<?php echo $avgAttendance >= 85 ? 'attendance-high' : ($avgAttendance >= 70 ? 'attendance-medium' : 'attendance-low'); ?>">
                                        <?php echo $avgAttendance; ?>%
                                    </strong>
                                </div>
                            </div>

                            <!-- Detailed Results Table -->
                            <div class="table-responsive">
                                <table id="attendanceTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Scholar No.</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Total Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Late</th>
                                            <th>Attendance %</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['scholar_number'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                                <td><?php echo $record['total_days']; ?></td>
                                                <td><span class="text-success"><?php echo $record['present_days']; ?></span></td>
                                                <td><span class="text-danger"><?php echo $record['absent_days']; ?></span></td>
                                                <td><span class="text-warning"><?php echo $record['late_days']; ?></span></td>
                                                <td>
                                                    <strong class="<?php
                                                        $percentage = $record['attendance_percentage'];
                                                        echo $percentage >= 85 ? 'attendance-high' :
                                                             ($percentage >= 70 ? 'attendance-medium' : 'attendance-low');
                                                    ?>">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $percentage = $record['attendance_percentage'];
                                                    if ($percentage >= 85) {
                                                        echo '<span class="badge bg-success">Excellent</span>';
                                                    } elseif ($percentage >= 70) {
                                                        echo '<span class="badge bg-warning">Good</span>';
                                                    } else {
                                                        echo '<span class="badge bg-danger">Poor</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Data Message -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No Attendance Data</h5>
                        <p class="text-muted">Please select filters and generate an attendance report.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

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

            // Initialize DataTable if report data exists
            <?php if ($report_data && !$student_id): ?>
                $('#attendanceTable').DataTable({
                    "pageLength": 25,
                    "ordering": true,
                    "searching": true,
                    "paging": true,
                    "info": true,
                    "responsive": true,
                    "order": [[7, "desc"]] // Sort by attendance percentage
                });
            <?php endif; ?>

            // Load students when class changes
            document.getElementById('class_id').addEventListener('change', function() {
                loadStudentsForClass(this.value);
            });

            // Load students if class is already selected
            const selectedClassId = document.getElementById('class_id').value;
            if (selectedClassId) {
                loadStudentsForClass(selectedClassId);
            }

            // Initialize charts
            <?php if ($report_data): ?>
                initializeCharts();
            <?php endif; ?>
        });

        // Load students for selected class
        function loadStudentsForClass(classId) {
            const studentSelect = document.getElementById('student_id');

            if (!classId) {
                studentSelect.innerHTML = '<option value="">All Students</option>';
                return;
            }

            fetch(`/admin/attendance/get-students?class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let options = '<option value="">All Students</option>';
                        data.data.forEach(student => {
                            const selected = '<?php echo $student_id; ?>' == student.id ? 'selected' : '';
                            options += `<option value="${student.id}" ${selected}>${student.name} (${student.scholar_number})</option>`;
                        });
                        studentSelect.innerHTML = options;
                    }
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                });
        }

        // Initialize charts with report data
        function initializeCharts() {
            <?php if ($report_data): ?>
                <?php if ($student_id && isset($student)): ?>
                    // Individual student attendance trend
                    const attendanceData = <?php
                        $dates = [];
                        $statuses = [];
                        foreach ($report_data as $record) {
                            $dates[] = date('M d', strtotime($record->attendance_date));
                            $statuses[] = $record->status === 'present' ? 1 : ($record->status === 'late' ? 0.5 : 0);
                        }
                        echo json_encode(['dates' => $dates, 'statuses' => $statuses]);
                    ?>;

                    const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
                    new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: attendanceData.dates,
                            datasets: [{
                                label: 'Attendance Status',
                                data: attendanceData.statuses,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1,
                                pointBackgroundColor: attendanceData.statuses.map(status =>
                                    status === 1 ? 'rgba(40, 167, 69, 1)' :
                                    status === 0.5 ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)'
                                )
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 1,
                                    ticks: {
                                        callback: function(value) {
                                            if (value === 1) return 'Present';
                                            if (value === 0.5) return 'Late';
                                            return 'Absent';
                                        }
                                    }
                                }
                            }
                        }
                    });
                <?php else: ?>
                    // Class attendance overview
                    const overviewData = {
                        present: <?php echo array_sum(array_column($report_data, 'present_days')); ?>,
                        absent: <?php echo array_sum(array_column($report_data, 'absent_days')); ?>,
                        late: <?php echo array_sum(array_column($report_data, 'late_days')); ?>
                    };

                    const overviewCtx = document.getElementById('attendanceOverviewChart').getContext('2d');
                    new Chart(overviewCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Present', 'Absent', 'Late'],
                            datasets: [{
                                data: [overviewData.present, overviewData.absent, overviewData.late],
                                backgroundColor: [
                                    'rgba(40, 167, 69, 0.8)',
                                    'rgba(220, 53, 69, 0.8)',
                                    'rgba(255, 193, 7, 0.8)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });

                    // Class performance chart
                    const performanceData = <?php
                        $labels = [];
                        $percentages = [];
                        foreach ($report_data as $record) {
                            $labels[] = $record['student_name'];
                            $percentages[] = $record['attendance_percentage'];
                        }
                        echo json_encode(['labels' => $labels, 'percentages' => $percentages]);
                    ?>;

                    const performanceCtx = document.getElementById('classPerformanceChart').getContext('2d');
                    new Chart(performanceCtx, {
                        type: 'bar',
                        data: {
                            labels: performanceData.labels,
                            datasets: [{
                                label: 'Attendance Percentage',
                                data: performanceData.percentages,
                                backgroundColor: performanceData.percentages.map(p =>
                                    p >= 85 ? 'rgba(40, 167, 69, 0.8)' :
                                    p >= 70 ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)'
                                ),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                <?php endif; ?>
            <?php endif; ?>
        }

        // Generate report
        function generateReport() {
            document.getElementById('reportForm').submit();
        }

        // Export report
        function exportReport(format) {
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);

            // Create export URL
            const exportUrl = format === 'pdf' ? '/admin/reports/export-pdf' : '/admin/reports/export-excel';

            // Create a temporary form for export
            const exportForm = document.createElement('form');
            exportForm.method = 'POST';
            exportForm.action = exportUrl;
            exportForm.style.display = 'none';

            // Add type parameter
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'attendance';
            exportForm.appendChild(typeInput);

            // Copy form data
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                exportForm.appendChild(input);
            }

            document.body.appendChild(exportForm);
            exportForm.submit();
            document.body.removeChild(exportForm);
        }

        // Print report
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>