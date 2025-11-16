<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Reports Dashboard'); ?></title>
    <meta name="description" content="Comprehensive reports dashboard for school management">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

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
        .report-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #fcb045 0%, #fd1d1d 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                                <li class="breadcrumb-item active" aria-current="page">Reports</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </header>

        <!-- Reports Content -->
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

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card success">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up fs-1 mb-2"></i>
                            <h4 id="totalReports">0</h4>
                            <p class="mb-0">Total Reports Generated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card info">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-pdf fs-1 mb-2"></i>
                            <h4 id="pdfExports">0</h4>
                            <p class="mb-0">PDF Exports</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-spreadsheet fs-1 mb-2"></i>
                            <h4 id="excelExports">0</h4>
                            <p class="mb-0">Excel Exports</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-clock fs-1 mb-2"></i>
                            <h4 id="lastGenerated">-</h4>
                            <p class="mb-0">Last Generated</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Categories -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Report Categories
                    </h6>
                </div>
            </div>

            <div class="row">
                <!-- Academic Reports -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card report-card h-100" onclick="navigateToReport('academic')">
                        <div class="card-body text-center">
                            <i class="bi bi-mortarboard text-primary fs-1 mb-3"></i>
                            <h6 class="card-title">Academic Reports</h6>
                            <p class="card-text text-muted small">
                                Student performance, class analytics, exam results, and grade distributions.
                            </p>
                            <div class="btn btn-primary btn-sm">View Reports</div>
                        </div>
                    </div>
                </div>

                <!-- Financial Reports -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card report-card h-100" onclick="navigateToReport('financial')">
                        <div class="card-body text-center">
                            <i class="bi bi-cash-coin text-success fs-1 mb-3"></i>
                            <h6 class="card-title">Financial Reports</h6>
                            <p class="card-text text-muted small">
                                Revenue analysis, expense tracking, fee collection summaries, and financial planning.
                            </p>
                            <div class="btn btn-success btn-sm">View Reports</div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Reports -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card report-card h-100" onclick="navigateToReport('attendance')">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check text-info fs-1 mb-3"></i>
                            <h6 class="card-title">Attendance Reports</h6>
                            <p class="card-text text-muted small">
                                Detailed attendance tracking, class-wise analytics, and attendance patterns.
                            </p>
                            <div class="btn btn-info btn-sm">View Reports</div>
                        </div>
                    </div>
                </div>

                <!-- Custom Reports -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card report-card h-100" onclick="navigateToReport('custom')">
                        <div class="card-body text-center">
                            <i class="bi bi-gear text-warning fs-1 mb-3"></i>
                            <h6 class="card-title">Custom Reports</h6>
                            <p class="card-text text-muted small">
                                Filterable data reports with customizable parameters and advanced analytics.
                            </p>
                            <div class="btn btn-warning btn-sm">View Reports</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Charts Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-bar-chart me-2"></i>Quick Analytics
                    </h6>
                </div>
            </div>

            <div class="row">
                <!-- Attendance Trend Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>Attendance Trend (Current Month)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Collection Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-cash me-2"></i>Fee Collection (Current Year)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="feeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Recent Reports
                            </h6>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadRecentReports()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="recentReportsList">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-file-earmark-text fs-1 mb-2"></i>
                                    <p>Loading recent reports...</p>
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

            // Initialize dashboard
            initializeDashboard();
        });

        // Navigate to specific report
        function navigateToReport(reportType) {
            window.location.href = `/admin/reports/${reportType}`;
        }

        // Initialize dashboard with data
        function initializeDashboard() {
            loadDashboardStats();
            loadCharts();
            loadRecentReports();
        }

        // Load dashboard statistics
        function loadDashboardStats() {
            // For now, show placeholder data
            // In a real implementation, this would fetch from an API
            document.getElementById('totalReports').textContent = '24';
            document.getElementById('pdfExports').textContent = '18';
            document.getElementById('excelExports').textContent = '6';
            document.getElementById('lastGenerated').textContent = '2 hours ago';
        }

        // Load charts
        function loadCharts() {
            loadAttendanceChart();
            loadFeeChart();
        }

        // Load attendance trend chart
        function loadAttendanceChart() {
            fetch('/admin/reports/get-chart-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    chart_type: 'attendance_trend',
                    filters: {
                        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                        end_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0]
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const ctx = document.getElementById('attendanceChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.data.map(item => item.date),
                            datasets: [{
                                label: 'Attendance %',
                                data: data.data.map(item => item.percentage),
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1
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
                }
            })
            .catch(error => {
                console.error('Error loading attendance chart:', error);
            });
        }

        // Load fee collection chart
        function loadFeeChart() {
            fetch('/admin/reports/get-chart-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    chart_type: 'fee_collection',
                    filters: {
                        year: new Date().getFullYear()
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const ctx = document.getElementById('feeChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.data.map(item => item.month_name),
                            datasets: [{
                                label: 'Collected Amount',
                                data: data.data.map(item => item.collected_amount),
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
                }
            })
            .catch(error => {
                console.error('Error loading fee chart:', error);
            });
        }

        // Load recent reports
        function loadRecentReports() {
            // Placeholder for recent reports
            const recentReportsHtml = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Academic Performance Report</h6>
                            <small>2 hours ago</small>
                        </div>
                        <p class="mb-1">Class 10A - Term 1 Results</p>
                        <small class="text-muted">Generated by Admin</small>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Fee Collection Summary</h6>
                            <small>1 day ago</small>
                        </div>
                        <p class="mb-1">Monthly collection report for January 2024</p>
                        <small class="text-muted">Generated by Admin</small>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Attendance Analytics</h6>
                            <small>3 days ago</small>
                        </div>
                        <p class="mb-1">Class-wise attendance analysis</p>
                        <small class="text-muted">Generated by Admin</small>
                    </div>
                </div>
            `;

            document.getElementById('recentReportsList').innerHTML = recentReportsHtml;
        }

        // Refresh all data
        function refreshData() {
            loadDashboardStats();
            loadCharts();
            loadRecentReports();
        }
    </script>
</body>
</html>