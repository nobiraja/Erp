<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Manage Subjects'); ?></title>

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
        .subject-card { transition: all 0.3s ease; }
        .subject-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .assigned { border-left: 4px solid #28a745; }
        .unassigned { border-left: 4px solid #6c757d; }
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
                        <h5 class="mb-0">Manage Subjects</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes">Classes</a></li>
                                <li class="breadcrumb-item"><a href="/admin/classes/<?php echo $class->id; ?>"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Subjects</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/classes/<?php echo $class->id; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Class
                    </a>
                </div>
            </div>
        </header>

        <!-- Manage Subjects Content -->
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

            <!-- Class Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></h4>
                            <p class="text-muted mb-0">Academic Year: <?php echo htmlspecialchars($class->academic_year); ?></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h5 mb-0"><?php echo count($assignedSubjects); ?></div>
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

            <div class="row">
                <!-- Available Subjects -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-plus-circle me-2"></i>
                                Available Subjects (<?php echo count($allSubjects); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="availableSubjects">
                                <?php
                                $assignedSubjectIds = array_column($assignedSubjects, 'subject_id');
                                foreach ($allSubjects as $subject):
                                    $isAssigned = in_array($subject->id, $assignedSubjectIds);
                                ?>
                                    <div class="col-12">
                                        <div class="card subject-card border <?php echo $isAssigned ? 'assigned' : 'unassigned'; ?>">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($subject->subject_name); ?></h6>
                                                        <small class="text-muted d-block">Code: <?php echo htmlspecialchars($subject->subject_code); ?></small>
                                                        <?php if ($subject->description): ?>
                                                            <small class="text-muted d-block"><?php echo htmlspecialchars(substr($subject->description, 0, 60)); ?><?php echo strlen($subject->description) > 60 ? '...' : ''; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ms-2">
                                                        <?php if ($isAssigned): ?>
                                                            <span class="badge bg-success">Assigned</span>
                                                        <?php else: ?>
                                                            <button onclick="assignSubject(<?php echo $subject->id; ?>, '<?php echo htmlspecialchars($subject->subject_name); ?>')" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-plus-circle"></i> Assign
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Subjects -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Assigned Subjects (<?php echo count($assignedSubjects); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="assignedSubjects">
                                <?php foreach ($assignedSubjects as $assignment): ?>
                                    <div class="col-12">
                                        <div class="card subject-card border assigned">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($assignment->subject_name); ?></h6>
                                                        <small class="text-muted d-block">Code: <?php echo htmlspecialchars($assignment->subject_code); ?></small>

                                                        <!-- Teacher Assignment -->
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block mb-1">Teacher:</small>
                                                            <select class="form-select form-select-sm teacher-select"
                                                                    data-class-id="<?php echo $class->id; ?>"
                                                                    data-subject-id="<?php echo $assignment->subject_id; ?>"
                                                                    onchange="assignTeacher(this)">
                                                                <option value="">Select Teacher</option>
                                                                <?php foreach ($teachers as $teacher): ?>
                                                                    <option value="<?php echo $teacher->id; ?>" <?php echo $assignment->teacher_id == $teacher->id ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($teacher->getFullName() . ' (' . $teacher->employee_id . ')'); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="ms-2">
                                                        <button onclick="unassignSubject(<?php echo $assignment->subject_id; ?>, '<?php echo htmlspecialchars($assignment->subject_name); ?>')" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-x-circle"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (empty($assignedSubjects)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-journal-x display-4 text-muted mb-3"></i>
                                    <h6 class="text-muted">No subjects assigned</h6>
                                    <p class="text-muted small">Select subjects from the left panel to assign them to this class</p>
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

        // Assign subject to class
        function assignSubject(subjectId, subjectName) {
            if (confirm(`Are you sure you want to assign "${subjectName}" to this class?`)) {
                fetch(`/admin/classes/<?php echo $class->id; ?>/subjects/${subjectId}/assign`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Subject assigned successfully');
                        location.reload();
                    } else {
                        alert('Failed to assign subject: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error assigning subject');
                    console.error('Assign error:', error);
                });
            }
        }

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

        // Assign teacher to subject
        function assignTeacher(selectElement) {
            const classId = selectElement.getAttribute('data-class-id');
            const subjectId = selectElement.getAttribute('data-subject-id');
            const teacherId = selectElement.value;

            fetch(`/admin/classes/${classId}/subjects/${subjectId}/teacher`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ teacher_id: teacherId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message briefly
                    const originalText = selectElement.nextElementSibling ? selectElement.nextElementSibling.textContent : '';
                    const successMsg = document.createElement('small');
                    successMsg.className = 'text-success ms-2';
                    successMsg.textContent = '✓ Assigned';
                    selectElement.parentNode.appendChild(successMsg);

                    setTimeout(() => {
                        if (successMsg.parentNode) {
                            successMsg.remove();
                        }
                    }, 2000);
                } else {
                    alert('Failed to assign teacher: ' + (data.message || 'Unknown error'));
                    // Reset select
                    selectElement.value = '';
                }
            })
            .catch(error => {
                alert('Error assigning teacher');
                console.error('Assign teacher error:', error);
                selectElement.value = '';
            });
        }
    </script>
</body>
</html>