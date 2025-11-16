<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Teacher Dashboard'); ?></title>
    <meta name="description" content="Teacher dashboard for school management system">

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
        .task-item {
            transition: all 0.3s;
        }
        .task-item:hover {
            background-color: #f8f9fa;
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
                    <span class="fw-bold" id="sidebarTitle">Teacher</span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/teacher/dashboard">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/classes">
                        <i class="bi bi-book"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/attendance">
                        <i class="bi bi-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/exams">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Exams & Results</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/profile">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
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
                        <div class="fw-bold small"><?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Teacher'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($teacher ? $teacher->designation : 'Teacher'); ?></div>
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
                        <h5 class="mb-0">Teacher Dashboard</h5>
                        <small class="text-muted">Welcome back, <?php echo htmlspecialchars($teacher ? $teacher->first_name : 'Teacher'); ?></small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted"><?php echo date('l, F j, Y', strtotime($currentDate)); ?></small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($teacher ? $teacher->first_name : 'Teacher'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/teacher/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/teacher/profile#change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
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
                                    <a href="/teacher/attendance" class="btn btn-primary w-100 quick-action-btn text-decoration-none">
                                        <i class="bi bi-calendar-check d-block fs-4 mb-1"></i>
                                        <small>Mark Attendance</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="/teacher/classes" class="btn btn-success w-100 quick-action-btn text-decoration-none">
                                        <i class="bi bi-book d-block fs-4 mb-1"></i>
                                        <small>View Classes</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="/teacher/exams" class="btn btn-info w-100 quick-action-btn text-decoration-none">
                                        <i class="bi bi-file-earmark-text d-block fs-4 mb-1"></i>
                                        <small>Enter Results</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-warning w-100 quick-action-btn" onclick="viewAnnouncements()">
                                        <i class="bi bi-megaphone d-block fs-4 mb-1"></i>
                                        <small>Announcements</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="/teacher/classes" class="btn btn-danger w-100 quick-action-btn text-decoration-none">
                                        <i class="bi bi-people d-block fs-4 mb-1"></i>
                                        <small>Student List</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="/teacher/profile" class="btn btn-secondary w-100 quick-action-btn text-decoration-none">
                                        <i class="bi bi-person d-block fs-4 mb-1"></i>
                                        <small>My Profile</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Statistics -->
            <div class="row mb-4" id="statsCards">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Assigned Classes
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="assignedClasses">
                                        <?php echo count($assignedSubjects ?? []); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-book-fill fs-2 text-primary"></i>
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
                                        Today's Tasks
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="todayTasks">
                                        <?php echo count($todayAttendanceTasks ?? []); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check-fill fs-2 text-success"></i>
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
                                        <?php echo number_format($attendanceSummary['attendance_rate'] ?? 0, 1); ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-graph-up fs-2 text-info"></i>
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
                                        Pending Results
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingResults">
                                        <?php echo count($pendingResults ?? []); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-text fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row mb-4">
                <!-- Today's Attendance Tasks -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-calendar-check me-2"></i>Today's Attendance Tasks
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="todayTasksList">
                                <?php if (!empty($todayAttendanceTasks)): ?>
                                    <?php foreach ($todayAttendanceTasks as $task): ?>
                                        <div class="task-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($task['class_name'] . ' ' . $task['section']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($task['subject_name']); ?></small>
                                                <br>
                                                <small class="text-danger"><?php echo $task['unmarked_students']; ?> students unmarked</small>
                                            </div>
                                            <a href="/teacher/attendance?class_id=<?php echo $task['class_id']; ?>&subject_id=<?php echo $task['subject_id']; ?>" class="btn btn-sm btn-primary">
                                                Mark
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                                        <p class="text-muted mb-0">All attendance tasks completed for today!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Exams -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-calendar-event me-2"></i>Upcoming Exams
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="upcomingExamsList">
                                <?php if (!empty($upcomingExams)): ?>
                                    <?php foreach ($upcomingExams as $exam): ?>
                                        <div class="task-item d-flex align-items-center p-3 mb-2 border rounded">
                                            <div class="me-3">
                                                <i class="bi bi-file-earmark-text text-info fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($exam['exam_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($exam['class_name'] . ' ' . $exam['section']); ?> |
                                                    <?php echo date('M d', strtotime($exam['start_date'])); ?> - <?php echo date('M d', strtotime($exam['end_date'])); ?>
                                                </small>
                                                <br>
                                                <small class="text-success"><?php echo $exam['results_entered']; ?>/<?php echo $exam['total_students']; ?> results entered</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-calendar-x text-muted fs-1 mb-3"></i>
                                        <p class="text-muted mb-0">No upcoming exams</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class Performance and Announcements -->
            <div class="row">
                <!-- Class Performance Statistics -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-graph-up me-2"></i>Class Performance Overview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-megaphone me-2"></i>Announcements
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="announcementsList">
                                <?php if (!empty($recentAnnouncements)): ?>
                                    <?php foreach ($recentAnnouncements as $announcement): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($announcement['event_date'])); ?>
                                                <?php if ($announcement['event_time']): ?>
                                                    <i class="bi bi-clock me-1 ms-2"></i><?php echo date('H:i', strtotime($announcement['event_time'])); ?>
                                                <?php endif; ?>
                                            </small>
                                            <?php if ($announcement['location']): ?>
                                                <br><small class="text-muted">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($announcement['location']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                                        <p class="text-muted mb-0">No recent announcements</p>
                                    </div>
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

        // View announcements
        function viewAnnouncements() {
            // Scroll to announcements section or open modal
            const announcementsCard = document.querySelector('[data-bs-target="#announcementsModal"]');
            if (announcementsCard) {
                announcementsCard.click();
            } else {
                document.getElementById('announcementsList').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Load dashboard data via AJAX
        function loadDashboardData() {
            fetch('/teacher/dashboard/getDashboardData')
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        updateDashboard(data);
                    }
                })
                .catch(error => console.log('Failed to load dashboard data:', error));
        }

        // Update dashboard with new data
        function updateDashboard(data) {
            // Update statistics
            if (data.todayAttendanceTasks) {
                document.getElementById('todayTasks').textContent = data.todayAttendanceTasks.length;
            }

            if (data.attendanceSummary) {
                document.getElementById('attendanceRate').textContent = (data.attendanceSummary.attendance_rate || 0).toFixed(1) + '%';
            }

            if (data.pendingResults) {
                document.getElementById('pendingResults').textContent = data.pendingResults.length;
            }

            // Update today's tasks list
            if (data.todayAttendanceTasks) {
                updateTodayTasks(data.todayAttendanceTasks);
            }

            // Update upcoming exams
            if (data.upcomingExams) {
                updateUpcomingExams(data.upcomingExams);
            }

            // Update announcements
            if (data.recentAnnouncements) {
                updateAnnouncements(data.recentAnnouncements);
            }
        }

        // Update today's tasks
        function updateTodayTasks(tasks) {
            const container = document.getElementById('todayTasksList');
            if (!container) return;

            if (tasks.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                        <p class="text-muted mb-0">All attendance tasks completed for today!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = tasks.map(task => `
                <div class="task-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded">
                    <div>
                        <h6 class="mb-1">${task.class_name} ${task.section}</h6>
                        <small class="text-muted">${task.subject_name}</small>
                        <br>
                        <small class="text-danger">${task.unmarked_students} students unmarked</small>
                    </div>
                    <a href="/teacher/attendance?class_id=${task.class_id}&subject_id=${task.subject_id}" class="btn btn-sm btn-primary">
                        Mark
                    </a>
                </div>
            `).join('');
        }

        // Update upcoming exams
        function updateUpcomingExams(exams) {
            const container = document.getElementById('upcomingExamsList');
            if (!container) return;

            if (exams.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">No upcoming exams</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = exams.map(exam => `
                <div class="task-item d-flex align-items-center p-3 mb-2 border rounded">
                    <div class="me-3">
                        <i class="bi bi-file-earmark-text text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${exam.exam_name}</h6>
                        <small class="text-muted">
                            ${exam.class_name} ${exam.section} |
                            ${new Date(exam.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} -
                            ${new Date(exam.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                        </small>
                        <br>
                        <small class="text-success">${exam.results_entered}/${exam.total_students} results entered</small>
                    </div>
                </div>
            `).join('');
        }

        // Update announcements
        function updateAnnouncements(announcements) {
            const container = document.getElementById('announcementsList');
            if (!container) return;

            if (announcements.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">No recent announcements</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = announcements.map(announcement => `
                <div class="mb-3 pb-3 border-bottom">
                    <h6 class="mb-1">${announcement.title}</h6>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>${new Date(announcement.event_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        })}
                        ${announcement.event_time ? `<i class="bi bi-clock me-1 ms-2"></i>${new Date('1970-01-01T' + announcement.event_time).toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        })}` : ''}
                    </small>
                    ${announcement.location ? `<br><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>${announcement.location}</small>` : ''}
                </div>
            `).join('');
        }

        // Initialize charts
        function initializeCharts() {
            // Performance chart
            const performanceData = <?php echo json_encode($performanceStats ?? []); ?>;
            if (performanceData.length > 0) {
                createPerformanceChart(performanceData);
            }
        }

        // Create performance chart
        function createPerformanceChart(data) {
            const ctx = document.getElementById('performanceChart');
            if (!ctx) return;

            const labels = data.map(item => item.class_name + ' ' + item.section);
            const attendanceData = data.map(item => item.attendance_percentage);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance Rate (%)',
                        data: attendanceData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Auto refresh data every 2 minutes
        setInterval(loadDashboardData, 120000);
    </script>
</body>
</html>