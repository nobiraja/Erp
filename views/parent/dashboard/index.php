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
        .child-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .child-card .card-body {
            padding: 1.5rem;
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
            font-size: 1.5rem;
            font-weight: bold;
        }
        .child-overview {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .metric-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 auto 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-house-heart-fill me-2"></i>
                Parent Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/parent/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/children">
                            <i class="bi bi-people me-1"></i>My Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/events">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($parent_data['first_name'] ?? 'Parent'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
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
                                    Welcome back, <?php echo htmlspecialchars(($parent_data['first_name'] ?? '') . ' ' . ($parent_data['last_name'] ?? '')); ?>!
                                </h2>
                                <p class="card-text mb-0">
                                    Parent Portal - Monitor your children's academic progress
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="bg-white text-primary rounded p-3 d-inline-block">
                                    <div class="h5 mb-0"><?php echo count($children); ?></div>
                                    <small>Children</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Overview -->
        <div class="row mb-4">
            <?php foreach ($children as $child): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card child-card dashboard-card h-100">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-2">
                                        <?php echo htmlspecialchars($child['full_name']); ?>
                                    </h5>
                                    <p class="card-text mb-2">
                                        <?php echo htmlspecialchars($child['class_name'] . ' - ' . $child['class_section']); ?> |
                                        Scholar No: <?php echo htmlspecialchars($child['scholar_number']); ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="metric-circle bg-white text-primary">
                                        <?php echo $child['attendance_percentage']; ?>%<br>
                                        <small>Attendance</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-4 text-center">
                                    <div class="child-overview">
                                        <div class="h6 mb-1">Fees</div>
                                        <div class="text-warning">₹<?php echo number_format($child['fee_status']['pending']); ?> Pending</div>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="child-overview">
                                        <div class="h6 mb-1">Results</div>
                                        <div class="text-success"><?php echo $child['exam_results']['average_percentage']; ?>% Avg</div>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="child-overview">
                                        <div class="h6 mb-1">Status</div>
                                        <div class="<?php echo $child['attendance_percentage'] >= 75 ? 'text-success' : 'text-warning'; ?>">
                                            <?php echo $child['attendance_percentage'] >= 75 ? 'Good' : 'Monitor'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="/parent/children" class="btn btn-outline-light btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Recent Notifications -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell me-2"></i>Recent Notifications
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

            <!-- Upcoming Events -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Upcoming School Events
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
                            <a href="/parent/events" class="btn btn-info btn-sm">View All Events</a>
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
                                <a href="/parent/children" class="quick-link">
                                    <div class="card h-100 text-center border-primary">
                                        <div class="card-body">
                                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Children Overview</h6>
                                            <small class="text-muted">Detailed academic snapshots</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/parent/attendance" class="quick-link">
                                    <div class="card h-100 text-center border-success">
                                        <div class="card-body">
                                            <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Attendance Tracking</h6>
                                            <small class="text-muted">Monitor attendance records</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/parent/results" class="quick-link">
                                    <div class="card h-100 text-center border-info">
                                        <div class="card-body">
                                            <i class="bi bi-clipboard-data text-info" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Academic Results</h6>
                                            <small class="text-muted">View exam performance</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/parent/fees" class="quick-link">
                                    <div class="card h-100 text-center border-warning">
                                        <div class="card-body">
                                            <i class="bi bi-cash-coin text-warning" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Fee Management</h6>
                                            <small class="text-muted">Track payments and dues</small>
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
        // Load notifications
        function loadNotifications() {
            fetch('/parent/dashboard/getNotifications', {
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

        // Update notifications
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

        // Auto-refresh notifications every 5 minutes
        setInterval(() => {
            loadNotifications();
        }, 300000);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Parent dashboard loaded');
        });
    </script>
</body>
</html>