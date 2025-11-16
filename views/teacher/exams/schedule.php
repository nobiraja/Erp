<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Exam Schedule'); ?></title>
    <meta name="description" content="View exam schedule and upcoming examinations">

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
        .exam-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid;
        }
        .exam-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .exam-card.upcoming {
            border-left-color: #007bff;
        }
        .exam-card.ongoing {
            border-left-color: #28a745;
        }
        .exam-card.completed {
            border-left-color: #6c757d;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #dee2e6;
        }
        .exam-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
                    <a class="nav-link" href="/teacher/dashboard">
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
                    <a class="nav-link active" href="/teacher/exams">
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
                        <h5 class="mb-0">Exam Schedule</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/teacher/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Schedule</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center">
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

        <!-- Main Content -->
        <main class="p-4">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-left-primary shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Exams
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalExams">
                                        <?php echo count($exam_schedule ?? []); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-event fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-left-success shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Upcoming
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="upcomingExams">
                                        <?php
                                        $upcoming = 0;
                                        $today = date('Y-m-d');
                                        foreach ($exam_schedule ?? [] as $exam) {
                                            if ($exam['start_date'] > $today) $upcoming++;
                                        }
                                        echo $upcoming;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-left-warning shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Ongoing
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="ongoingExams">
                                        <?php
                                        $ongoing = 0;
                                        $today = date('Y-m-d');
                                        foreach ($exam_schedule ?? [] as $exam) {
                                            if ($exam['start_date'] <= $today && $exam['end_date'] >= $today) $ongoing++;
                                        }
                                        echo $ongoing;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-play-circle fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-left-info shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Completed
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="completedExams">
                                        <?php
                                        $completed = 0;
                                        $today = date('Y-m-d');
                                        foreach ($exam_schedule ?? [] as $exam) {
                                            if ($exam['end_date'] < $today) $completed++;
                                        }
                                        echo $completed;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Schedule Views -->
            <div class="row">
                <!-- Timeline View -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-timeline me-2"></i>Exam Timeline</h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="timelineViewBtn">
                                    <i class="bi bi-list"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="calendarViewBtn">
                                    <i class="bi bi-calendar"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="timelineView">
                                <div class="timeline">
                                    <?php if (!empty($exam_schedule)): ?>
                                        <?php
                                        usort($exam_schedule, function($a, $b) {
                                            return strtotime($a['start_date']) - strtotime($b['start_date']);
                                        });
                                        ?>

                                        <?php foreach ($exam_schedule as $exam): ?>
                                            <?php
                                            $today = date('Y-m-d');
                                            $status = '';
                                            $statusClass = '';
                                            $statusText = '';

                                            if ($exam['end_date'] < $today) {
                                                $status = 'completed';
                                                $statusClass = 'bg-secondary';
                                                $statusText = 'Completed';
                                            } elseif ($exam['start_date'] <= $today && $exam['end_date'] >= $today) {
                                                $status = 'ongoing';
                                                $statusClass = 'bg-success';
                                                $statusText = 'Ongoing';
                                            } else {
                                                $status = 'upcoming';
                                                $statusClass = 'bg-primary';
                                                $statusText = 'Upcoming';
                                            }
                                            ?>
                                            <div class="timeline-item">
                                                <div class="card exam-card <?php echo $status; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="card-title mb-1">
                                                                    <?php echo htmlspecialchars($exam['exam_name']); ?>
                                                                    <span class="badge <?php echo $statusClass; ?> exam-status-badge ms-2">
                                                                        <?php echo $statusText; ?>
                                                                    </span>
                                                                </h6>
                                                                <p class="card-text small text-muted mb-2">
                                                                    <i class="bi bi-building me-1"></i>
                                                                    <?php echo htmlspecialchars($exam['class_name'] . ' ' . $exam['section']); ?> |
                                                                    <i class="bi bi-book me-1"></i>
                                                                    <?php echo htmlspecialchars($exam['subject_name']); ?>
                                                                </p>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <small class="text-muted">
                                                                            <i class="bi bi-calendar me-1"></i>
                                                                            Start: <?php echo date('M d, Y', strtotime($exam['start_date'])); ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <small class="text-muted">
                                                                            <i class="bi bi-calendar-check me-1"></i>
                                                                            End: <?php echo date('M d, Y', strtotime($exam['end_date'])); ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <a href="/teacher/exams/marks?exam_id=<?php echo $exam['id']; ?>&subject_id=<?php echo $exam['subject_id'] ?? ''; ?>"
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-pencil me-1"></i>Enter Marks
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-calendar-x text-muted fs-1 mb-3"></i>
                                            <h5 class="text-muted">No Exams Scheduled</h5>
                                            <p class="text-muted mb-0">There are no exams scheduled for your subjects.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Calendar View (Hidden by default) -->
                            <div id="calendarView" style="display: none;">
                                <div class="calendar-container">
                                    <div class="text-center py-5">
                                        <i class="bi bi-calendar text-muted fs-1 mb-3"></i>
                                        <p class="text-muted">Calendar view coming soon...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Summary -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/teacher/exams/marks" class="btn btn-primary">
                                    <i class="bi bi-pencil-square me-2"></i>Enter Marks
                                </a>
                                <a href="/teacher/exams/performance" class="btn btn-success">
                                    <i class="bi bi-graph-up me-2"></i>View Performance
                                </a>
                                <a href="/teacher/exams/analytics" class="btn btn-info">
                                    <i class="bi bi-bar-chart me-2"></i>Analytics
                                </a>
                                <button class="btn btn-outline-secondary" onclick="exportSchedule()">
                                    <i class="bi bi-download me-2"></i>Export Schedule
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Subject-wise Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-book me-2"></i>Subject Summary</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $subjectSummary = [];
                            foreach ($exam_schedule ?? [] as $exam) {
                                $subject = $exam['subject_name'];
                                if (!isset($subjectSummary[$subject])) {
                                    $subjectSummary[$subject] = 0;
                                }
                                $subjectSummary[$subject]++;
                            }
                            ?>

                            <?php if (!empty($subjectSummary)): ?>
                                <?php foreach ($subjectSummary as $subject => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small"><?php echo htmlspecialchars($subject); ?></span>
                                        <span class="badge bg-primary"><?php echo $count; ?> exam<?php echo $count > 1 ? 's' : ''; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small mb-0">No subjects assigned</p>
                            <?php endif; ?>
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

            // Initialize schedule view
            initializeScheduleView();
        });

        // Initialize schedule view
        function initializeScheduleView() {
            // View toggle functionality
            const timelineViewBtn = document.getElementById('timelineViewBtn');
            const calendarViewBtn = document.getElementById('calendarViewBtn');
            const timelineView = document.getElementById('timelineView');
            const calendarView = document.getElementById('calendarView');

            if (timelineViewBtn && calendarViewBtn) {
                timelineViewBtn.addEventListener('click', function() {
                    timelineView.style.display = 'block';
                    calendarView.style.display = 'none';
                    timelineViewBtn.classList.add('active');
                    calendarViewBtn.classList.remove('active');
                });

                calendarViewBtn.addEventListener('click', function() {
                    timelineView.style.display = 'none';
                    calendarView.style.display = 'block';
                    timelineViewBtn.classList.remove('active');
                    calendarViewBtn.classList.add('active');
                });
            }
        }

        // Export schedule
        function exportSchedule() {
            const examSchedule = <?php echo json_encode($exam_schedule ?? []); ?>;

            if (examSchedule.length === 0) {
                alert('No schedule data to export');
                return;
            }

            // Create CSV content
            let csvContent = `Exam Schedule\n`;
            csvContent += `Generated on: ${new Date().toLocaleDateString()}\n\n`;

            csvContent += `Exam Name,Class,Subject,Start Date,End Date,Status\n`;

            examSchedule.forEach(exam => {
                const today = new Date().toISOString().split('T')[0];
                let status = 'Upcoming';

                if (exam.end_date < today) {
                    status = 'Completed';
                } else if (exam.start_date <= today && exam.end_date >= today) {
                    status = 'Ongoing';
                }

                csvContent += `"${exam.exam_name}","${exam.class_name} ${exam.section}","${exam.subject_name}","${exam.start_date}","${exam.end_date}","${status}"\n`;
            });

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `exam_schedule_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>