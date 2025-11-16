<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Timetable - <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #343a40; color: white; transition: all 0.3s; z-index: 1000; overflow-y: auto; }
        .sidebar.collapsed { width: 70px; }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: 0.75rem 1rem; transition: all 0.3s; }
        .sidebar .nav-link:hover { color: white; background: rgba(255,255,255,.1); }
        .sidebar .nav-link.active { color: white; background: #007bff; }
        .sidebar .nav-link i { width: 20px; margin-right: 10px; }
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .main-content { margin-left: 250px; transition: margin-left 0.3s; }
        .main-content.expanded { margin-left: 70px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .hamburger-menu { display: block; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; }
            .sidebar-overlay.show { display: block; }
        }
        .timetable-cell { min-height: 80px; border: 1px solid #dee2e6; padding: 0.5rem; }
        .timetable-header { background: #f8f9fa; font-weight: bold; text-align: center; }
        .subject-slot { background: #e3f2fd; border-radius: 0.25rem; padding: 0.25rem; margin-bottom: 0.25rem; font-size: 0.875rem; }
        .subject-slot .subject-name { font-weight: 500; }
        .subject-slot .teacher-name { font-size: 0.75rem; color: #6c757d; }
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
                    <a class="nav-link active" href="/admin/classes">
                        <i class="bi bi-book"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/subjects">
                        <i class="bi bi-journal-text"></i>
                        <span>Subjects</span>
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
                        <h5 class="mb-0">Class Timetable</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes">Classes</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes/<?php echo $class->id; ?>"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Timetable</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="printTimetable()" class="btn btn-outline-primary">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                    <a href="/admin/classes/<?php echo $class->id; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Class
                    </a>
                </div>
            </div>
        </header>

        <!-- Timetable Content -->
        <main class="p-4">
            <!-- Class Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?> - Weekly Timetable</h4>
                            <p class="text-muted mb-0">Academic Year: <?php echo htmlspecialchars($class->academic_year); ?></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h5 mb-0"><?php echo count($subjects); ?></div>
                                    <small class="text-muted">Assigned Subjects</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 mb-0"><?php echo $class->students() ? count($class->students()) : 0; ?></div>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable Note -->
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> This is a sample timetable based on assigned subjects. For a complete timetable management system, additional database tables and scheduling logic would be required.
            </div>

            <!-- Weekly Timetable -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        Weekly Schedule
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th class="timetable-header" style="width: 100px;">Time</th>
                                    <th class="timetable-header">Monday</th>
                                    <th class="timetable-header">Tuesday</th>
                                    <th class="timetable-header">Wednesday</th>
                                    <th class="timetable-header">Thursday</th>
                                    <th class="timetable-header">Friday</th>
                                    <th class="timetable-header">Saturday</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $periods = [
                                    '08:00 - 09:00',
                                    '09:00 - 10:00',
                                    '10:00 - 11:00',
                                    '11:00 - 12:00',
                                    '12:00 - 13:00', // Lunch break
                                    '13:00 - 14:00',
                                    '14:00 - 15:00',
                                    '15:00 - 16:00'
                                ];

                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                                // Sample schedule - distribute subjects across days and periods
                                $schedule = [];
                                $subjectIndex = 0;
                                $lunchBreak = '12:00 - 13:00';

                                foreach ($periods as $period) {
                                    if ($period === $lunchBreak) {
                                        // Lunch break
                                        $schedule[$period] = array_fill(0, 6, ['type' => 'break', 'name' => 'Lunch Break']);
                                        continue;
                                    }

                                    foreach ($days as $dayIndex => $day) {
                                        if ($subjectIndex < count($subjects)) {
                                            $subject = $subjects[$subjectIndex % count($subjects)];
                                            $schedule[$period][$dayIndex] = [
                                                'type' => 'subject',
                                                'name' => $subject->subject_name,
                                                'code' => $subject->subject_code,
                                                'teacher' => $subject->getTeacher() ? $subject->getTeacher()->getFullName() : 'Not assigned'
                                            ];
                                            $subjectIndex++;
                                        } else {
                                            $schedule[$period][$dayIndex] = ['type' => 'free', 'name' => 'Free Period'];
                                        }
                                    }
                                }

                                foreach ($periods as $period):
                                ?>
                                    <tr>
                                        <td class="timetable-header fw-bold"><?php echo $period; ?></td>
                                        <?php for ($i = 0; $i < 6; $i++): ?>
                                            <td class="timetable-cell">
                                                <?php
                                                $slot = $schedule[$period][$i] ?? ['type' => 'free', 'name' => 'Free Period'];
                                                if ($slot['type'] === 'break'):
                                                ?>
                                                    <div class="text-center text-muted">
                                                        <i class="bi bi-cup-hot"></i><br>
                                                        <small><?php echo $slot['name']; ?></small>
                                                    </div>
                                                <?php elseif ($slot['type'] === 'subject'): ?>
                                                    <div class="subject-slot">
                                                        <div class="subject-name"><?php echo htmlspecialchars($slot['name']); ?></div>
                                                        <div class="teacher-name"><?php echo htmlspecialchars($slot['teacher']); ?></div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center text-muted">
                                                        <small><?php echo $slot['name']; ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subject Legend -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Subject Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold"><?php echo htmlspecialchars(substr($subject->subject_code, 0, 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($subject->subject_name); ?></div>
                                        <small class="text-muted">Code: <?php echo htmlspecialchars($subject->subject_code); ?></small><br>
                                        <small class="text-muted">
                                            Teacher: <?php echo $subject->getTeacher() ? htmlspecialchars($subject->getTeacher()->getFullName()) : 'Not assigned'; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
        });

        // Print timetable
        function printTimetable() {
            window.print();
        }
    </script>

    <style media="print">
        .sidebar, .card-header .btn, .breadcrumb, header .d-flex {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .card {
            border: 1px solid #000 !important;
        }
        .timetable-cell {
            border: 1px solid #000 !important;
        }
    </style>
</body>
</html>