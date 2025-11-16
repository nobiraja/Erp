<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Exam Management'); ?></title>
    <meta name="description" content="Teacher exam management dashboard">

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
        .feature-card {
            transition: all 0.3s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .quick-action-btn {
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
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
                        <h5 class="mb-0">Exam Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Exams & Results</li>
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
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card stats-card text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="card-title mb-2">Exam Management Center</h4>
                                    <p class="card-text mb-0">
                                        Manage examinations, enter marks, view student performance, and generate analytics for your assigned subjects.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <i class="bi bi-file-earmark-text display-4 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <?php
                $assignedSubjects = $teacher ? $teacher->getAssignedSubjects() : [];
                $totalSubjects = count($assignedSubjects);

                // Calculate pending results
                $pendingResults = 0;
                $upcomingExams = 0;
                $today = date('Y-m-d');

                if ($teacher) {
                    $pendingResultsQuery = $this->db->fetchAll(
                        "SELECT COUNT(*) as count FROM exams e
                         LEFT JOIN class_subjects cs ON e.class_id = cs.class_id
                         LEFT JOIN exam_results er ON e.id = er.exam_id AND cs.subject_id = er.subject_id
                         WHERE cs.teacher_id = ? AND e.end_date >= ?
                         GROUP BY e.id, cs.subject_id
                         HAVING COUNT(er.student_id) = 0",
                        [$teacher->id, $today]
                    );
                    $pendingResults = count($pendingResultsQuery);

                    $upcomingQuery = $this->db->fetch(
                        "SELECT COUNT(*) as count FROM exams e
                         LEFT JOIN class_subjects cs ON e.class_id = cs.class_id
                         WHERE cs.teacher_id = ? AND e.start_date > ?",
                        [$teacher->id, $today]
                    );
                    $upcomingExams = $upcomingQuery['count'] ?? 0;
                }
                ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Assigned Subjects
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalSubjects; ?></div>
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
                                        Upcoming Exams
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $upcomingExams; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-event-fill fs-2 text-success"></i>
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
                                        Pending Results
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingResults; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-text fs-2 text-info"></i>
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
                                        Classes Managed
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo count(array_unique(array_column($assignedSubjects, 'class_id'))); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-building fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Features -->
            <div class="row">
                <!-- Marks Entry -->
                <div class="col-lg-6 col-xl-3 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h5 class="card-title">Enter Marks</h5>
                            <p class="card-text text-muted small">
                                Enter and manage exam marks with real-time grade calculation
                            </p>
                            <a href="/teacher/exams/marks" class="btn btn-primary quick-action-btn">
                                <i class="bi bi-arrow-right me-1"></i>Start Entry
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Performance Records -->
                <div class="col-lg-6 col-xl-3 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-success text-white">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h5 class="card-title">Performance Records</h5>
                            <p class="card-text text-muted small">
                                View detailed student performance records and progress
                            </p>
                            <a href="/teacher/exams/performance" class="btn btn-success quick-action-btn">
                                <i class="bi bi-arrow-right me-1"></i>View Records
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Analytics & Reports -->
                <div class="col-lg-6 col-xl-3 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-info text-white">
                                <i class="bi bi-bar-chart"></i>
                            </div>
                            <h5 class="card-title">Analytics & Reports</h5>
                            <p class="card-text text-muted small">
                                Generate performance analytics and detailed reports
                            </p>
                            <a href="/teacher/exams/analytics" class="btn btn-info quick-action-btn">
                                <i class="bi bi-arrow-right me-1"></i>View Analytics
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Exam Schedule -->
                <div class="col-lg-6 col-xl-3 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-warning text-white">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h5 class="card-title">Exam Schedule</h5>
                            <p class="card-text text-muted small">
                                View exam schedules and manage examination timelines
                            </p>
                            <a href="/teacher/exams/schedule" class="btn btn-warning quick-action-btn">
                                <i class="bi bi-arrow-right me-1"></i>View Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Quick Actions -->
            <div class="row mt-4">
                <!-- Recent Activity -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($teacher): ?>
                                <?php
                                // Get recent exam results entered by this teacher
                                $recentActivity = $this->db->fetchAll(
                                    "SELECT er.*, e.exam_name, s.subject_name, st.first_name, st.last_name
                                     FROM exam_results er
                                     LEFT JOIN exams e ON er.exam_id = e.id
                                     LEFT JOIN subjects s ON er.subject_id = s.id
                                     LEFT JOIN students st ON er.student_id = st.id
                                     WHERE er.entered_by = ?
                                     ORDER BY er.created_at DESC
                                     LIMIT 5",
                                    [$teacher->user_id]
                                );
                                ?>

                                <?php if (!empty($recentActivity)): ?>
                                    <div class="timeline">
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <div class="timeline-item mb-3">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-check-circle"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">
                                                            Marks entered for <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                                        </h6>
                                                        <p class="text-muted small mb-1">
                                                            <?php echo htmlspecialchars($activity['exam_name']); ?> - <?php echo htmlspecialchars($activity['subject_name']); ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?> |
                                                            Score: <?php echo $activity['marks_obtained']; ?>/<?php echo $activity['max_marks']; ?> (<?php echo $activity['grade']; ?>)
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                                        <p class="text-muted mb-0">No recent activity found</p>
                                        <small class="text-muted">Start by entering marks for your exams</small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-person-x text-muted fs-1 mb-3"></i>
                                    <p class="text-muted mb-0">Teacher information not available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/teacher/exams/marks" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add Marks
                                </a>
                                <a href="/teacher/exams/performance" class="btn btn-outline-success">
                                    <i class="bi bi-eye me-2"></i>View Performance
                                </a>
                                <a href="/teacher/exams/analytics" class="btn btn-outline-info">
                                    <i class="bi bi-graph-up me-2"></i>Generate Report
                                </a>
                                <a href="/teacher/exams/schedule" class="btn btn-outline-warning">
                                    <i class="bi bi-calendar me-2"></i>Check Schedule
                                </a>
                                <hr>
                                <button class="btn btn-outline-secondary" onclick="exportAllData()">
                                    <i class="bi bi-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Need Help?</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-dot me-2"></i>
                                    <a href="#" class="text-decoration-none">How to enter marks</a>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-dot me-2"></i>
                                    <a href="#" class="text-decoration-none">Understanding analytics</a>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-dot me-2"></i>
                                    <a href="#" class="text-decoration-none">Grade calculation guide</a>
                                </li>
                                <li>
                                    <i class="bi bi-dot me-2"></i>
                                    <a href="#" class="text-decoration-none">Exporting reports</a>
                                </li>
                            </ul>
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
        });

        // Export all data function
        function exportAllData() {
            if (confirm('This will export all your exam-related data. Continue?')) {
                // Create a comprehensive export
                const teacherData = <?php echo json_encode($teacher ? [
                    'id' => $teacher->id,
                    'name' => $teacher->getFullName(),
                    'subjects' => $assignedSubjects
                ] : null); ?>;

                if (!teacherData) {
                    alert('Teacher data not available');
                    return;
                }

                let csvContent = `Exam Management Data Export\n`;
                csvContent += `Teacher: ${teacherData.name}\n`;
                csvContent += `Generated on: ${new Date().toLocaleDateString()}\n\n`;

                csvContent += `Assigned Subjects:\n`;
                csvContent += `Subject Name,Class,Section\n`;
                teacherData.subjects.forEach(subject => {
                    csvContent += `"${subject.subject_name}","${subject.class_name}","${subject.section}"\n`;
                });

                // Download CSV
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `exam_data_${teacherData.name.replace(/\s+/g, '_')}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    </script>
</body>
</html>