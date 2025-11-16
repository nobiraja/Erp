<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Class Details'); ?></title>

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
                        <h5 class="mb-0">Class Details</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes">Classes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/classes/<?php echo $class->id; ?>/edit" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>Edit Class
                    </a>
                    <a href="/admin/classes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Classes
                    </a>
                </div>
            </div>
        </header>

        <!-- Class Details Content -->
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

            <!-- Class Information -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Class Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-book display-4 text-primary mb-2"></i>
                                        <h5><?php echo htmlspecialchars($class->class_name); ?></h5>
                                        <small class="text-muted">Class Name</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-diagram-3 display-4 text-success mb-2"></i>
                                        <h5><?php echo htmlspecialchars($class->section); ?></h5>
                                        <small class="text-muted">Section</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-calendar display-4 text-info mb-2"></i>
                                        <h5><?php echo htmlspecialchars($class->academic_year); ?></h5>
                                        <small class="text-muted">Academic Year</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Class Teacher</h6>
                                    <?php if ($class->teacher()): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle fs-4 me-3"></i>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($class->teacher()->getFullName()); ?></div>
                                                <small class="text-muted">Employee ID: <?php echo htmlspecialchars($class->teacher()->employee_id); ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-person-x me-2"></i>No class teacher assigned
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6>Statistics</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2 text-center">
                                                <div class="h5 mb-0"><?php echo $student_count; ?></div>
                                                <small class="text-muted">Students</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2 text-center">
                                                <div class="h5 mb-0"><?php echo count($subjects); ?></div>
                                                <small class="text-muted">Subjects</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Assigned -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-journal-text me-2"></i>
                                Subjects Assigned (<?php echo count($subjects); ?>)
                            </h6>
                            <a href="/admin/classes/<?php echo $class->id; ?>/subjects" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-circle me-1"></i>Manage Subjects
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($subjects)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Code</th>
                                                <th>Teacher</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subjects as $subject): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($subject->subject_name); ?></td>
                                                    <td><code><?php echo htmlspecialchars($subject->subject_code); ?></code></td>
                                                    <td>
                                                        <?php if ($subject->getTeacher()): ?>
                                                            <?php echo htmlspecialchars($subject->getTeacher()->getFullName()); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not assigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button onclick="unassignSubject(<?php echo $subject->id; ?>, '<?php echo htmlspecialchars($subject->subject_name); ?>')" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-journal-x display-4 text-muted mb-3"></i>
                                    <h6 class="text-muted">No subjects assigned</h6>
                                    <a href="/admin/classes/<?php echo $class->id; ?>/subjects" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Assign Subjects
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/admin/classes/<?php echo $class->id; ?>/students" class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>View Students
                                </a>
                                <a href="/admin/classes/<?php echo $class->id; ?>/attendance" class="btn btn-outline-success">
                                    <i class="bi bi-calendar-check me-2"></i>Mark Attendance
                                </a>
                                <a href="/admin/classes/<?php echo $class->id; ?>/timetable" class="btn btn-outline-info">
                                    <i class="bi bi-calendar3 me-2"></i>View Timetable
                                </a>
                                <a href="/admin/classes/<?php echo $class->id; ?>/reports" class="btn btn-outline-secondary">
                                    <i class="bi bi-graph-up me-2"></i>Class Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Students -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Recent Students (<?php echo min(5, $student_count); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($students)): ?>
                                <?php foreach (array_slice($students, 0, 5) as $student): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-shrink-0 me-2">
                                            <?php if ($student->photo_path && file_exists($student->photo_path)): ?>
                                                <img src="<?php echo htmlspecialchars($student->photo_path); ?>" alt="Photo" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-white" style="font-size: 14px;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small"><?php echo htmlspecialchars($student->getFullName()); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($student->scholar_number); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($student_count > 5): ?>
                                    <div class="text-center mt-2">
                                        <a href="/admin/classes/<?php echo $class->id; ?>/students" class="btn btn-sm btn-outline-primary">
                                            View All Students
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <small class="text-muted">No students enrolled</small>
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

        // Unassign subject from class
        function unassignSubject(subjectId, subjectName) {
            if (confirm(`Are you sure you want to unassign "${subjectName}" from this class?`)) {
                fetch(`/admin/classes/<?php echo $class->id; ?>/subjects/${subjectId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Subject unassigned successfully');
                        location.reload();
                    } else {
                        alert('Failed to unassign subject: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error unassigning subject');
                    console.error('Unassign error:', error);
                });
            }
        }
    </script>
</body>
</html>