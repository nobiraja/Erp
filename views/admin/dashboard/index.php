<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Admin Dashboard'); ?></title>
    <meta name="description" content="Admin dashboard for school management system">

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
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
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
        .quick-action-btn {
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
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
                    <img src="<?php echo htmlspecialchars($school_logo ?? '/images/logo-small.png'); ?>" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle"><?php echo htmlspecialchars(substr($school_name ?? 'SMS', 0, 10)); ?></span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/dashboard">
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
                        <div class="fw-bold small"><?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($current_user['role'] ?? 'Administrator'); ?></div>
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
                        <h5 class="mb-0">Dashboard</h5>
                        <small class="text-muted">Welcome back, <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?></small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted"><?php echo htmlspecialchars($current_month); ?></small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/admin/change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-4">
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-primary w-100 quick-action-btn" onclick="quickAction('addStudent')">
                                        <i class="bi bi-person-plus d-block fs-4 mb-1"></i>
                                        <small>Add Student</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-success w-100 quick-action-btn" onclick="quickAction('addTeacher')">
                                        <i class="bi bi-person-badge d-block fs-4 mb-1"></i>
                                        <small>Add Teacher</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-info w-100 quick-action-btn" onclick="quickAction('addClass')">
                                        <i class="bi bi-book d-block fs-4 mb-1"></i>
                                        <small>Add Class</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-warning w-100 quick-action-btn" onclick="quickAction('markAttendance')">
                                        <i class="bi bi-calendar-check d-block fs-4 mb-1"></i>
                                        <small>Mark Attendance</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-danger w-100 quick-action-btn" onclick="quickAction('collectFee')">
                                        <i class="bi bi-cash d-block fs-4 mb-1"></i>
                                        <small>Collect Fee</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-secondary w-100 quick-action-btn" onclick="quickAction('addEvent')">
                                        <i class="bi bi-calendar-event d-block fs-4 mb-1"></i>
                                        <small>Add Event</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsCards">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Students
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalStudents">
                                        <?php echo number_format($dashboard_data['user_stats']['active_students'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people-fill fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Teachers
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalTeachers">
                                        <?php echo number_format($dashboard_data['user_stats']['active_teachers'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-badge-fill fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Attendance Rate
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="attendanceRate">
                                        <?php echo number_format($dashboard_data['attendance_stats']['attendance_percentage'] ?? 0, 1); ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check-fill fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Fee Collection
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="feeCollection">
                                        <?php echo number_format($dashboard_data['fee_stats']['collection_percentage'] ?? 0, 1); ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash-stack fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Monthly Fee Collection</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="feeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Class Attendance</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities and Events -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                        </div>
                        <div class="card-body">
                            <div id="recentActivities">
                                <?php if (!empty($dashboard_data['recent_activities'])): ?>
                                    <?php foreach ($dashboard_data['recent_activities'] as $activity): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <small class="text-muted"><?php echo htmlspecialchars($activity['username']); ?> <?php echo htmlspecialchars($activity['action']); ?> <?php echo htmlspecialchars($activity['table_name']); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No recent activities</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Upcoming Events</h6>
                        </div>
                        <div class="card-body">
                            <div id="upcomingEvents">
                                <?php if (!empty($dashboard_data['upcoming_events'])): ?>
                                    <?php foreach ($dashboard_data['upcoming_events'] as $event): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <i class="bi bi-calendar-event text-info fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                                    <?php if ($event['event_time']): ?>
                                                        <i class="bi bi-clock me-1 ms-2"></i><?php echo date('H:i', strtotime($event['event_time'])); ?>
                                                    <?php endif; ?>
                                                    <?php if ($event['location']): ?>
                                                        <br><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($event['location']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No upcoming events</p>
                                <?php endif; ?>
                            </div>
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

            // Load dashboard data
            loadDashboardData();

            // Initialize charts
            initializeCharts();
        });

        // Quick actions
        function quickAction(action) {
            const urls = {
                addStudent: '/admin/students/add',
                addTeacher: '/admin/teachers/add',
                addClass: '/admin/classes/add',
                markAttendance: '/admin/attendance/mark',
                collectFee: '/admin/fees/collect',
                addEvent: '/admin/events/add'
            };

            if (urls[action]) {
                window.location.href = urls[action];
            }
        }

        // Load dashboard data via AJAX
        function loadDashboardData() {
            // Load updated statistics
            fetch('/admin/dashboard/getStats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data.data);
                    }
                })
                .catch(error => console.log('Failed to load dashboard stats:', error));
        }

        // Update statistics display
        function updateStats(data) {
            if (data.user_stats) {
                document.getElementById('totalStudents').textContent = (data.user_stats.active_students || 0).toLocaleString();
                document.getElementById('totalTeachers').textContent = (data.user_stats.active_teachers || 0).toLocaleString();
            }

            if (data.attendance_stats) {
                document.getElementById('attendanceRate').textContent = (data.attendance_stats.attendance_percentage || 0).toFixed(1) + '%';
            }

            if (data.fee_stats) {
                document.getElementById('feeCollection').textContent = (data.fee_stats.collection_percentage || 0).toFixed(1) + '%';
            }
        }

        // Initialize charts
        function initializeCharts() {
            // Fee collection chart
            fetch('/admin/dashboard/getMonthlyFeeChart')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        createFeeChart(data.data);
                    }
                })
                .catch(error => console.log('Failed to load fee chart data:', error));

            // Attendance chart
            fetch('/admin/dashboard/getClassAttendanceChart')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        createAttendanceChart(data.data);
                    }
                })
                .catch(error => console.log('Failed to load attendance chart data:', error));
        }

        // Create fee collection chart
        function createFeeChart(data) {
            const ctx = document.getElementById('feeChart').getContext('2d');
            const labels = data.map(item => item.month_name);
            const values = data.map(item => item.collected_amount);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Fee Collection (₹)',
                        data: values,
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
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Create attendance chart
        function createAttendanceChart(data) {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const labels = data.map(item => item.class_name + ' ' + item.section);
            const values = data.map(item => item.attendance_percentage);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 159, 64)'
                        ]
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
        }

        // Auto refresh data every 5 minutes
        setInterval(loadDashboardData, 300000);
    </script>
</body>
</html>