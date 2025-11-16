<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Custom Reports'); ?></title>
    <meta name="description" content="Custom reports with filterable data and advanced analytics">

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
        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .filter-badge {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin: 0.125rem;
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
                                <li class="breadcrumb-item active" aria-current="page">Custom</li>
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

        <!-- Custom Reports Content -->
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

            <!-- Advanced Filters -->
            <div class="filter-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>Advanced Filters
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </button>
                </div>

                <form id="reportForm" method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class->id; ?>" <?php echo $filters['class_id'] == $class->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select class="form-select" id="subject_id" name="subject_id">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject->id; ?>" <?php echo $filters['subject_id'] == $subject->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject->subject_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select class="form-select" id="teacher_id" name="teacher_id">
                            <option value="">All Teachers</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher->id; ?>" <?php echo $filters['teacher_id'] == $teacher->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($teacher->first_name . ' ' . $teacher->last_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="present" <?php echo $filters['status'] === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $filters['status'] === 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="late" <?php echo $filters['status'] === 'late' ? 'selected' : ''; ?>>Late</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Apply Filters
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

                <!-- Active Filters Display -->
                <?php
                $activeFilters = array_filter($filters, function($value) {
                    return !empty($value);
                });
                if (!empty($activeFilters)):
                ?>
                    <div class="mt-3">
                        <small class="text-muted">Active Filters:</small>
                        <?php foreach ($activeFilters as $key => $value): ?>
                            <span class="filter-badge">
                                <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong>
                                <?php
                                if ($key === 'class_id' && isset($classes)) {
                                    $class = array_filter($classes, function($c) use ($value) { return $c->id == $value; });
                                    echo htmlspecialchars(reset($class)->class_name ?? $value);
                                } elseif ($key === 'subject_id' && isset($subjects)) {
                                    $subject = array_filter($subjects, function($s) use ($value) { return $s->id == $value; });
                                    echo htmlspecialchars(reset($subject)->subject_name ?? $value);
                                } elseif ($key === 'teacher_id' && isset($teachers)) {
                                    $teacher = array_filter($teachers, function($t) use ($value) { return $t->id == $value; });
                                    $teacher = reset($teacher);
                                    echo htmlspecialchars(($teacher ? $teacher->first_name . ' ' . $teacher->last_name : $value));
                                } elseif (in_array($key, ['start_date', 'end_date'])) {
                                    echo date('d/m/Y', strtotime($value));
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Report Results -->
            <?php if ($report_data): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-table me-2"></i>
                            Custom Report Results
                            <small class="text-muted">(<?php echo count($report_data); ?> records)</small>
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="printReport()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <?php
                            $totalRecords = count($report_data);
                            $statusCounts = ['present' => 0, 'absent' => 0, 'late' => 0];
                            $uniqueStudents = [];
                            $uniqueClasses = [];
                            $uniqueSubjects = [];

                            foreach ($report_data as $record) {
                                if (isset($record['status'])) {
                                    $statusCounts[$record['status']] = ($statusCounts[$record['status']] ?? 0) + 1;
                                }
                                $uniqueStudents[$record['scholar_number']] = true;
                                $uniqueClasses[$record['class_name']] = true;
                                $uniqueSubjects[$record['subject_name']] = true;
                            }
                            ?>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 text-primary"><?php echo $totalRecords; ?></div>
                                    <small class="text-muted">Total Records</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 text-success"><?php echo count($uniqueStudents); ?></div>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 text-info"><?php echo count($uniqueClasses); ?></div>
                                    <small class="text-muted">Classes</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 text-warning"><?php echo count($uniqueSubjects); ?></div>
                                    <small class="text-muted">Subjects</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 status-present"><?php echo $statusCounts['present']; ?></div>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h4 status-absent"><?php echo $statusCounts['absent']; ?></div>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Row -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Status Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="statusChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Class Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="classChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table id="customTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Scholar No.</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Marked At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['scholar_number']); ?></td>
                                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['teacher_name']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($record['attendance_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $record['status'] === 'present' ? 'success' :
                                                         ($record['status'] === 'absent' ? 'danger' : 'warning');
                                                ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $record['marked_at'] ? date('d/m/Y H:i', strtotime($record['marked_at'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Data Message -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No Custom Report Data</h5>
                        <p class="text-muted">Please apply filters and generate a custom report.</p>
                        <div class="mt-3">
                            <small class="text-muted">
                                Tip: Try selecting different combinations of filters to get specific insights.
                            </small>
                        </div>
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
                $('#customTable').DataTable({
                    "pageLength": 25,
                    "ordering": true,
                    "searching": true,
                    "paging": true,
                    "info": true,
                    "responsive": true,
                    "order": [[5, "desc"]] // Sort by date
                });

                // Initialize charts
                initializeCharts();
            <?php endif; ?>
        });

        // Initialize charts with report data
        function initializeCharts() {
            <?php if ($report_data): ?>
                // Status distribution chart
                const statusData = <?php echo json_encode($statusCounts); ?>;
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Present', 'Absent', 'Late'],
                        datasets: [{
                            data: [statusData.present, statusData.absent, statusData.late],
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

                // Class distribution chart
                const classCounts = <?php
                    $classCountArray = array_count_values(array_column($report_data, 'class_name'));
                    echo json_encode($classCountArray);
                ?>;
                const classCtx = document.getElementById('classChart').getContext('2d');
                new Chart(classCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(classCounts),
                        datasets: [{
                            label: 'Number of Records',
                            data: Object.values(classCounts),
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
                                beginAtZero: true
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

        // Reset filters
        function resetFilters() {
            document.getElementById('class_id').value = '';
            document.getElementById('subject_id').value = '';
            document.getElementById('teacher_id').value = '';
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('status').value = '';
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
            typeInput.value = 'custom';
            exportForm.appendChild(typeInput);

            // Copy form data
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input';
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