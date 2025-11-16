<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .dashboard-card {
            transition: transform 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .attendance-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto;
        }
        .notification-item {
            border-left: 4px solid;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .notification-warning { border-left-color: #ffc107; }
        .notification-danger { border-left-color: #dc3545; }
        .notification-info { border-left-color: #0dcaf0; }
        .notification-success { border-left-color: #198754; }
        .quick-link {
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }
        .quick-link:hover {
            text-decoration: none;
            color: inherit;
            transform: scale(1.02);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .exam-result-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Student Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/student/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/dashboard">
                            <i class="bi bi-megaphone me-1"></i>Announcements
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($student_data['first_name'] ?? 'Student'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="card-title mb-2">
                                    Welcome back, <?php echo htmlspecialchars(($student_data['first_name'] ?? '') . ' ' . ($student_data['last_name'] ?? '')); ?>!
                                </h2>
                                <p class="card-text mb-0">
                                    <?php echo htmlspecialchars($student_data['class_name'] ?? ''); ?> - <?php echo htmlspecialchars($student_data['section'] ?? ''); ?> |
                                    Scholar No: <?php echo htmlspecialchars($student_data['scholar_number'] ?? ''); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="attendance-circle bg-white text-primary">
                                    <?php echo $attendance_percentage; ?>%<br>
                                    <small>Attendance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="bi bi-calendar-check-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="stats-number text-primary"><?php echo $attendance_percentage; ?>%</div>
                        <div class="text-muted">Attendance</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                        </div>
                        <div class="stats-number text-success">₹<?php echo number_format($fee_status['pending'] ?? 0); ?></div>
                        <div class="text-muted">Pending Fees</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="bi bi-clipboard-data" style="font-size: 2rem;"></i>
                        </div>
                        <div class="stats-number text-info"><?php echo count($exam_results); ?></div>
                        <div class="text-muted">Recent Exams</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="bi bi-calendar-event" style="font-size: 2rem;"></i>
                        </div>
                        <div class="stats-number text-warning"><?php echo count($upcoming_events); ?></div>
                        <div class="text-muted">Upcoming Events</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Exam Results -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-check me-2"></i>Recent Exam Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="exam-results-container">
                            <?php if (empty($exam_results)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-clipboard-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No exam results available yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($exam_results as $result): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($result['subject_name'] ?? ''); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($result['exam_name'] ?? ''); ?> -
                                                        <?php echo date('M d, Y', strtotime($result['exam_date'] ?? '')); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($result['marks_obtained'] ?? 0); ?>/<?php echo htmlspecialchars($result['total_marks'] ?? 0); ?></span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($result['grade'] ?? ''); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadExamResults()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                            <a href="/student/results" class="btn btn-primary btn-sm">View All Results</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Status -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cash-coin me-2"></i>Fee Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="fee-status-container">
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="text-success">
                                        <div class="h4 mb-0">₹<?php echo number_format($fee_status['total'] ?? 0); ?></div>
                                        <small>Total</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-primary">
                                        <div class="h4 mb-0">₹<?php echo number_format($fee_status['paid'] ?? 0); ?></div>
                                        <small>Paid</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="<?php echo ($fee_status['pending'] ?? 0) > 0 ? 'text-warning' : 'text-success'; ?>">
                                        <div class="h4 mb-0">₹<?php echo number_format($fee_status['pending'] ?? 0); ?></div>
                                        <small>Pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: <?php echo $fee_status['percentage'] ?? 0; ?>%"
                                     aria-valuenow="<?php echo $fee_status['percentage'] ?? 0; ?>"
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $fee_status['percentage'] ?? 0; ?>%
                                </div>
                            </div>
                            <?php if (($fee_status['overdue'] ?? 0) > 0): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    You have ₹<?php echo number_format($fee_status['overdue'] ?? 0); ?> in overdue fees.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-success btn-sm" onclick="loadFeeStatus()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                            <a href="/student/fees" class="btn btn-success btn-sm">View Fee Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upcoming Events -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Upcoming Events
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="upcoming-events-container">
                            <?php if (empty($upcoming_events)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No upcoming events.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($upcoming_events as $event): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title'] ?? ''); ?></h6>
                                                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
                                                    <small class="text-primary">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <?php echo date('M d, Y', strtotime($event['event_date'] ?? '')); ?>
                                                        <?php if (!empty($event['event_time'])): ?>
                                                            at <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <?php if (!empty($event['venue'])): ?>
                                                    <small class="text-muted">
                                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($event['venue']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-info btn-sm" onclick="loadUpcomingEvents()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell me-2"></i>Notifications
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="notifications-container">
                            <?php if (empty($notifications)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No new notifications.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item notification-<?php echo $notification['type']; ?>">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-<?php echo $notification['icon']; ?> me-2"></i>
                                                    <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                                </div>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <?php if (!empty($notification['action_url'])): ?>
                                                    <a href="<?php echo $notification['action_url']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-warning btn-sm" onclick="loadNotifications()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Links -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Access</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="/student/attendance" class="quick-link">
                                    <div class="card h-100 text-center border-primary">
                                        <div class="card-body">
                                            <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Attendance Records</h6>
                                            <small class="text-muted">View your attendance history</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/student/results" class="quick-link">
                                    <div class="card h-100 text-center border-success">
                                        <div class="card-body">
                                            <i class="bi bi-clipboard-data text-success" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Exam Results</h6>
                                            <small class="text-muted">Check your academic performance</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/student/fees" class="quick-link">
                                    <div class="card h-100 text-center border-info">
                                        <div class="card-body">
                                            <i class="bi bi-cash-coin text-info" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Fee Payments</h6>
                                            <small class="text-muted">Manage your fee payments</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/student/dashboard" class="quick-link">
                                    <div class="card h-100 text-center border-warning">
                                        <div class="card-body">
                                            <i class="bi bi-megaphone text-warning" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Announcements</h6>
                                            <small class="text-muted">Stay updated with school news</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX Functions -->
    <script>
        // Load attendance data
        function loadAttendance() {
            fetch('/student/dashboard/getAttendance', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update attendance display
                    console.log('Attendance data loaded:', data.data);
                }
            })
            .catch(error => console.error('Error loading attendance:', error));
        }

        // Load exam results
        function loadExamResults() {
            fetch('/student/dashboard/getExamResults', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateExamResults(data.data);
                }
            })
            .catch(error => console.error('Error loading exam results:', error));
        }

        // Load fee status
        function loadFeeStatus() {
            fetch('/student/dashboard/getFeeStatus', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateFeeStatus(data.data);
                }
            })
            .catch(error => console.error('Error loading fee status:', error));
        }

        // Load notifications
        function loadNotifications() {
            fetch('/student/dashboard/getNotifications', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotifications(data.data);
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
        }

        // Load upcoming events
        function loadUpcomingEvents() {
            fetch('/student/dashboard/getUpcomingEvents', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateUpcomingEvents(data.data);
                }
            })
            .catch(error => console.error('Error loading upcoming events:', error));
        }

        // Update functions
        function updateExamResults(results) {
            const container = document.getElementById('exam-results-container');
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clipboard-x" style="font-size: 3rem;"></i>
                        <p class="mt-2">No exam results available yet.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            results.forEach(result => {
                html += `
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${result.subject_name || ''}</h6>
                                <small class="text-muted">
                                    ${result.exam_name || ''} - ${new Date(result.exam_date).toLocaleDateString()}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">${result.marks_obtained || 0}/${result.total_marks || 0}</span>
                                <br>
                                <small class="text-muted">${result.grade || ''}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        function updateFeeStatus(feeData) {
            const container = document.getElementById('fee-status-container');
            container.innerHTML = `
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-success">
                            <div class="h4 mb-0">₹${feeData.total ? feeData.total.toLocaleString() : 0}</div>
                            <small>Total</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-primary">
                            <div class="h4 mb-0">₹${feeData.paid ? feeData.paid.toLocaleString() : 0}</div>
                            <small>Paid</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="${feeData.pending > 0 ? 'text-warning' : 'text-success'}">
                            <div class="h4 mb-0">₹${feeData.pending ? feeData.pending.toLocaleString() : 0}</div>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: ${feeData.percentage || 0}%"
                         aria-valuenow="${feeData.percentage || 0}"
                         aria-valuemin="0" aria-valuemax="100">
                        ${feeData.percentage || 0}%
                    </div>
                </div>
                ${feeData.overdue > 0 ? `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        You have ₹${feeData.overdue.toLocaleString()} in overdue fees.
                    </div>
                ` : ''}
            `;
        }

        function updateUpcomingEvents(events) {
            const container = document.getElementById('upcoming-events-container');
            if (events.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <p class="mt-2">No upcoming events.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            events.forEach(event => {
                html += `
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${event.title || ''}</h6>
                                <p class="mb-1 text-muted small">${event.description || ''}</p>
                                <small class="text-primary">
                                    <i class="bi bi-calendar me-1"></i>
                                    ${new Date(event.event_date).toLocaleDateString()}
                                    ${event.event_time ? ` at ${new Date('1970-01-01T' + event.event_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}` : ''}
                                </small>
                            </div>
                            ${event.venue ? `<small class="text-muted"><i class="bi bi-geo-alt me-1"></i>${event.venue}</small>` : ''}
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        function updateNotifications(notifications) {
            const container = document.getElementById('notifications-container');
            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                        <p class="mt-2">No new notifications.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            notifications.forEach(notification => {
                html += `
                    <div class="notification-item notification-${notification.type}">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-${notification.icon} me-2"></i>
                                    <strong>${notification.title}</strong>
                                </div>
                                <p class="mb-1 small">${notification.message}</p>
                                ${notification.action_url ? `<a href="${notification.action_url}" class="btn btn-sm btn-outline-primary">View Details</a>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            loadExamResults();
            loadFeeStatus();
            loadNotifications();
            loadUpcomingEvents();
        }, 300000);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initial data is already loaded server-side
            console.log('Student dashboard loaded');
        });
    </script>
</body>
</html>