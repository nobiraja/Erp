<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Academic Reports'); ?></title>
    <meta name="description" content="Academic reports with student performance and class analytics">

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
        .grade-excellent { color: #28a745; font-weight: bold; }
        .grade-good { color: #17a2b8; font-weight: bold; }
        .grade-average { color: #ffc107; font-weight: bold; }
        .grade-poor { color: #dc3545; font-weight: bold; }
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
                                <li class="breadcrumb-item active" aria-current="page">Academic</li>
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

        <!-- Academic Reports Content -->
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
                        <label for="exam_id" class="form-label">Exam</label>
                        <select class="form-select" id="exam_id" name="exam_id">
                            <option value="">All Exams</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?php echo $exam->id; ?>" <?php echo $exam_id == $exam->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($exam->exam_name); ?>
                                </option>
                            <?php endforeach; ?>
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
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Academic Performance Report
                            <?php if ($class_id): ?>
                                - <?php echo htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id', 'id'))]->class_name ?? ''); ?>
                            <?php endif; ?>
                            <?php if ($exam_id): ?>
                                - <?php echo htmlspecialchars($exams[array_search($exam_id, array_column($exams, 'id', 'id'))]->exam_name ?? ''); ?>
                            <?php endif; ?>
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="printReport()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Performance Charts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Grade Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="gradeChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Subject-wise Performance</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="subjectChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="table-responsive">
                            <table id="academicTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Scholar No.</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>Exam</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $gradeCounts = ['90-100%' => 0, '80-89%' => 0, '70-79%' => 0, '60-69%' => 0, 'Below 60%' => 0];
                                    $subjectPerformance = [];

                                    foreach ($report_data as $record):
                                        // Count grades
                                        $percentage = $record['percentage'];
                                        if ($percentage >= 90) $gradeCounts['90-100%']++;
                                        elseif ($percentage >= 80) $gradeCounts['80-89%']++;
                                        elseif ($percentage >= 70) $gradeCounts['70-79%']++;
                                        elseif ($percentage >= 60) $gradeCounts['60-69%']++;
                                        else $gradeCounts['Below 60%']++;

                                        // Track subject performance
                                        $subject = $record['subject_name'];
                                        if (!isset($subjectPerformance[$subject])) {
                                            $subjectPerformance[$subject] = ['total' => 0, 'count' => 0];
                                        }
                                        $subjectPerformance[$subject]['total'] += $percentage;
                                        $subjectPerformance[$subject]['count']++;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['scholar_number']); ?></td>
                                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['exam_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['marks']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $record['grade'] === 'A+' || $record['grade'] === 'A' ? 'success' :
                                                         ($record['grade'] === 'B+' || $record['grade'] === 'B' ? 'primary' :
                                                          ($record['grade'] === 'C+' || $record['grade'] === 'C' ? 'warning' : 'danger'));
                                                ?>">
                                                    <?php echo htmlspecialchars($record['grade']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="<?php
                                                    echo $percentage >= 90 ? 'grade-excellent' :
                                                         ($percentage >= 80 ? 'grade-good' :
                                                          ($percentage >= 70 ? 'grade-average' : 'grade-poor'));
                                                ?>">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </strong>
                                            </td>
                                            <td>
                                                <?php
                                                if ($percentage >= 90) {
                                                    echo '<span class="badge bg-success">Excellent</span>';
                                                } elseif ($percentage >= 80) {
                                                    echo '<span class="badge bg-primary">Good</span>';
                                                } elseif ($percentage >= 70) {
                                                    echo '<span class="badge bg-warning">Average</span>';
                                                } else {
                                                    echo '<span class="badge bg-danger">Needs Improvement</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row mt-4">
                            <?php
                            $totalStudents = count($report_data);
                            $excellentCount = $gradeCounts['90-100%'] + $gradeCounts['80-89%'];
                            $averageCount = $gradeCounts['70-79%'] + $gradeCounts['60-69%'];
                            $poorCount = $gradeCounts['Below 60%'];
                            $avgPerformance = $totalStudents > 0 ? array_sum(array_column($report_data, 'percentage')) / $totalStudents : 0;
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
                                    <small class="text-muted">Excellent (≥80%)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h4 text-warning"><?php echo $averageCount; ?></div>
                                    <small class="text-muted">Average (60-79%)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h4 text-danger"><?php echo $poorCount; ?></div>
                                    <small class="text-muted">Needs Improvement (<60%)</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="h5">Average Class Performance:
                                <strong><?php echo number_format($avgPerformance, 1); ?>%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Data Message -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-mortarboard display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No Academic Data</h5>
                        <p class="text-muted">Please select filters and generate an academic performance report.</p>
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
            <?php if ($report_data): ?>
                $('#academicTable').DataTable({
                    "pageLength": 25,
                    "ordering": true,
                    "searching": true,
                    "paging": true,
                    "info": true,
                    "responsive": true,
                    "order": [[7, "desc"]] // Sort by percentage
                });

                // Initialize charts
                initializeCharts();
            <?php endif; ?>
        });

        // Initialize charts with report data
        function initializeCharts() {
            <?php if ($report_data): ?>
                // Grade distribution chart
                const gradeCtx = document.getElementById('gradeChart').getContext('2d');
                new Chart(gradeCtx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_keys($gradeCounts)); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_values($gradeCounts)); ?>,
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(23, 162, 184, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(108, 117, 125, 0.8)'
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

                // Subject performance chart
                const subjectData = <?php
                    $subjectLabels = [];
                    $subjectAverages = [];
                    foreach ($subjectPerformance as $subject => $data) {
                        $subjectLabels[] = $subject;
                        $subjectAverages[] = round($data['total'] / $data['count'], 1);
                    }
                    echo json_encode([
                        'labels' => $subjectLabels,
                        'averages' => $subjectAverages
                    ]);
                ?>;

                const subjectCtx = document.getElementById('subjectChart').getContext('2d');
                new Chart(subjectCtx, {
                    type: 'bar',
                    data: {
                        labels: subjectData.labels,
                        datasets: [{
                            label: 'Average Percentage',
                            data: subjectData.averages,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
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
            typeInput.value = 'academic';
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