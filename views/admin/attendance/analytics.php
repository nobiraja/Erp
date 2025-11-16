<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Attendance Analytics'); ?></title>
    <meta name="description" content="View attendance analytics and trends">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
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
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stats-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stats-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
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
                    <a class="nav-link active" href="/admin/attendance">
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
                        <h5 class="mb-0"><?php echo htmlspecialchars($title); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/attendance">Attendance</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/attendance" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Attendance
                    </a>
                    <button type="button" class="btn btn-primary" onclick="refreshAnalytics()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </header>

        <!-- Analytics Content -->
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

            <!-- Filters -->
            <div class="filter-section">
                <form id="analyticsForm" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">Class (Optional)</label>
                        <select class="form-select" id="class_id" name="class_id" onchange="updateAnalytics()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class->id; ?>" <?php echo $class_id == $class->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label">Academic Year</label>
                        <select class="form-select" id="year" name="year" onchange="updateAnalytics()">
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++):
                            ?>
                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" onclick="updateAnalytics()">
                            <i class="bi bi-graph-up me-1"></i>Update Analytics
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Month Summary -->
            <?php if (!empty($current_month_data)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="mb-3">
                        <i class="bi bi-calendar-month me-2"></i>
                        Current Month Summary (<?php echo date('F Y'); ?>)
                    </h6>
                </div>
                <?php
                $totalStudents = count($current_month_data);
                $excellentCount = 0;
                $goodCount = 0;
                $poorCount = 0;

                foreach ($current_month_data as $student) {
                    $percentage = $student['attendance_percentage'];
                    if ($percentage >= 85) $excellentCount++;
                    elseif ($percentage >= 70) $goodCount++;
                    else $poorCount++;
                }
                ?>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $totalStudents; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <div class="stat-number"><?php echo $excellentCount; ?></div>
                        <div class="stat-label">Excellent (≥85%)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                        <div class="stat-number"><?php echo $goodCount; ?></div>
                        <div class="stat-label">Good (70-84%)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                        <div class="stat-number"><?php echo $poorCount; ?></div>
                        <div class="stat-label">Poor (<70%)</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Charts Row -->
            <div class="row">
                <!-- Monthly Trends Chart -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Monthly Attendance Trends
                                <?php if ($class_id): ?>
                                    - <?php echo htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id', 'id'))]->class_name ?? ''); ?>
                                <?php endif; ?>
                                (<?php echo $year; ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Distribution -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Current Month Distribution
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="distributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="row mt-4">
                <!-- Top Performers -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-trophy me-2"></i>
                                Top 5 Performers (Current Month)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($current_month_data)): ?>
                                <?php
                                // Sort by attendance percentage descending
                                usort($current_month_data, function($a, $b) {
                                    return $b['attendance_percentage'] <=> $a['attendance_percentage'];
                                });
                                $topPerformers = array_slice($current_month_data, 0, 5);
                                ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($topPerformers as $index => $student): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">Scholar No: <?php echo htmlspecialchars($student['scholar_number']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success"><?php echo number_format($student['attendance_percentage'], 1); ?>%</span>
                                                <br>
                                                <small class="text-muted"><?php echo $student['present_days']; ?>/<?php echo $student['total_days']; ?> days</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-info-circle text-muted display-4 mb-3"></i>
                                    <p class="text-muted">No data available for current month</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Students Needing Attention -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Students Needing Attention (< 70%)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($current_month_data)): ?>
                                <?php
                                $lowPerformers = array_filter($current_month_data, function($student) {
                                    return $student['attendance_percentage'] < 70;
                                });
                                usort($lowPerformers, function($a, $b) {
                                    return $a['attendance_percentage'] <=> $b['attendance_percentage'];
                                });
                                $attentionList = array_slice($lowPerformers, 0, 5);
                                ?>
                                <?php if (!empty($attentionList)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($attentionList as $student): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">Scholar No: <?php echo htmlspecialchars($student['scholar_number']); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-danger"><?php echo number_format($student['attendance_percentage'], 1); ?>%</span>
                                                    <br>
                                                    <small class="text-muted"><?php echo $student['present_days']; ?>/<?php echo $student['total_days']; ?> days</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-check-circle text-success display-4 mb-3"></i>
                                        <p class="text-success">All students have good attendance!</p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-info-circle text-muted display-4 mb-3"></i>
                                    <p class="text-muted">No data available for current month</p>
                                </div>
                            <?php endif; ?>
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
        // Chart instances
        let monthlyTrendsChart = null;
        let distributionChart = null;

        // Chart data
        const monthlyTrendsData = <?php echo json_encode($monthly_trends); ?>;
        const currentMonthData = <?php echo json_encode($current_month_data); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
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

            // Initialize charts
            initializeCharts();
        });

        // Initialize charts
        function initializeCharts() {
            // Monthly Trends Chart
            const ctx1 = document.getElementById('monthlyTrendsChart').getContext('2d');
            const labels = monthlyTrendsData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });

            monthlyTrendsChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Present',
                        data: monthlyTrendsData.map(item => item.present_count),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Absent',
                        data: monthlyTrendsData.map(item => item.absent_count),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Late',
                        data: monthlyTrendsData.map(item => item.late_count),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Attendance Trends'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Records'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });

            // Distribution Chart
            const ctx2 = document.getElementById('distributionChart').getContext('2d');

            // Calculate distribution
            let excellent = 0, good = 0, poor = 0;
            currentMonthData.forEach(student => {
                const percentage = student.attendance_percentage;
                if (percentage >= 85) excellent++;
                else if (percentage >= 70) good++;
                else poor++;
            });

            distributionChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent (≥85%)', 'Good (70-84%)', 'Poor (<70%)'],
                    datasets: [{
                        data: [excellent, good, poor],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#dc3545'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Attendance Distribution'
                        },
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Update analytics
        function updateAnalytics() {
            const classId = document.getElementById('class_id').value;
            const year = document.getElementById('year').value;

            // Redirect with new parameters
            const url = `/admin/attendance/analytics?class_id=${classId}&year=${year}`;
            window.location.href = url;
        }

        // Refresh analytics
        function refreshAnalytics() {
            location.reload();
        }
    </script>
</body>
</html>