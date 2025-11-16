<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Mark Attendance'); ?></title>
    <meta name="description" content="Mark daily attendance for students">

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
        .student-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .student-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .student-card.present {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .student-card.absent {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .student-card.late {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
        .status-btn {
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .status-btn.present {
            background-color: #28a745;
            color: white;
        }
        .status-btn.present:hover {
            background-color: #218838;
        }
        .status-btn.absent {
            background-color: #dc3545;
            color: white;
        }
        .status-btn.absent:hover {
            background-color: #c82333;
        }
        .status-btn.late {
            background-color: #ffc107;
            color: #212529;
        }
        .status-btn.late:hover {
            background-color: #e0a800;
        }
        .status-btn.active {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
                                <li class="breadcrumb-item active" aria-current="page">Mark Attendance</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/attendance" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Attendance
                    </a>
                    <button type="button" class="btn btn-success" onclick="saveAttendance()">
                        <i class="bi bi-check-circle me-1"></i>Save Attendance
                    </button>
                </div>
            </div>
        </header>

        <!-- Mark Attendance Content -->
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

            <!-- Class and Date Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-date me-2"></i>
                        Attendance Details
                    </h6>
                </div>
                <div class="card-body">
                    <form id="attendanceForm" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="class_id" class="form-label">Select Class</label>
                            <select class="form-select" id="class_id" name="class_id" onchange="loadStudents()">
                                <option value="">Choose a class...</option>
                                <?php
                                // Get all classes for selection
                                $allClasses = ClassModel::allWithTeachers();
                                foreach ($allClasses as $cls):
                                ?>
                                    <option value="<?php echo $cls->id; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $cls->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cls->class_name . ' ' . $cls->section . ' (' . $cls->academic_year . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date" class="form-label">Attendance Date</label>
                            <input type="date" class="form-control" id="date" name="date"
                                   value="<?php echo htmlspecialchars($date); ?>" onchange="loadExistingAttendance()">
                        </div>
                        <div class="col-md-3">
                            <label for="subject_id" class="form-label">Subject (Optional)</label>
                            <select class="form-select" id="subject_id" name="subject_id" onchange="loadExistingAttendance()">
                                <option value="">General Attendance</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject->id; ?>" <?php echo $subject_id == $subject->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subject->subject_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" onclick="loadStudents()">
                                <i class="bi bi-search me-1"></i>Load Students
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students List -->
            <div id="studentsSection" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Students - <?php echo htmlspecialchars($class ? $class->class_name . ' ' . $class->section : ''); ?>
                            <span class="badge bg-primary ms-2" id="studentCount"><?php echo count($students); ?></span>
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAllPresent()">
                                <i class="bi bi-check-circle me-1"></i>All Present
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAllAbsent()">
                                <i class="bi bi-x-circle me-1"></i>All Absent
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="clearAll()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="studentsList">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="student-card" id="student-<?php echo $student->id; ?>" data-student-id="<?php echo $student->id; ?>">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="flex-shrink-0 me-3">
                                                    <?php if ($student->photo_path): ?>
                                                        <img src="/uploads/<?php echo htmlspecialchars($student->photo_path); ?>"
                                                             alt="Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?></h6>
                                                    <small class="text-muted">Scholar No: <?php echo htmlspecialchars($student->scholar_number); ?></small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="status-btn present flex-fill"
                                                        onclick="setStatus(<?php echo $student->id; ?>, 'present')">
                                                    <i class="bi bi-check-circle me-1"></i>Present
                                                </button>
                                                <button type="button" class="status-btn absent flex-fill"
                                                        onclick="setStatus(<?php echo $student->id; ?>, 'absent')">
                                                    <i class="bi bi-x-circle me-1"></i>Absent
                                                </button>
                                                <button type="button" class="status-btn late flex-fill"
                                                        onclick="setStatus(<?php echo $student->id; ?>, 'late')">
                                                    <i class="bi bi-clock me-1"></i>Late
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="bi bi-people display-4 text-muted mb-3"></i>
                                        <h5 class="text-muted">No Students Found</h5>
                                        <p class="text-muted">Please select a class to load students.</p>
                                    </div>
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
        let attendanceData = {};
        let existingAttendance = <?php echo json_encode($existing_attendance); ?>;

        // Initialize existing attendance
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

            // Load existing attendance if available
            loadExistingAttendance();

            // Show students section if class is selected
            const classId = document.getElementById('class_id').value;
            if (classId) {
                document.getElementById('studentsSection').style.display = 'block';
            }
        });

        // Load students for selected class
        function loadStudents() {
            const classId = document.getElementById('class_id').value;
            const date = document.getElementById('date').value;
            const subjectId = document.getElementById('subject_id').value;

            if (!classId) {
                alert('Please select a class first.');
                return;
            }

            // Redirect with parameters
            const url = `/admin/attendance/mark?class_id=${classId}&date=${date}&subject_id=${subjectId}`;
            window.location.href = url;
        }

        // Load existing attendance data
        function loadExistingAttendance() {
            const classId = document.getElementById('class_id').value;
            const date = document.getElementById('date').value;
            const subjectId = document.getElementById('subject_id').value;

            if (!classId || !date) return;

            // Reset all statuses
            document.querySelectorAll('.student-card').forEach(card => {
                card.classList.remove('present', 'absent', 'late');
                card.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
            });

            // Apply existing attendance
            Object.keys(existingAttendance).forEach(studentId => {
                const attendance = existingAttendance[studentId];
                setStatus(studentId, attendance.status, false);
            });
        }

        // Set attendance status for a student
        function setStatus(studentId, status, updateData = true) {
            const studentCard = document.getElementById(`student-${studentId}`);
            if (!studentCard) return;

            // Remove previous status classes
            studentCard.classList.remove('present', 'absent', 'late');

            // Add new status class
            studentCard.classList.add(status);

            // Update button states
            const buttons = studentCard.querySelectorAll('.status-btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.classList.contains(status)) {
                    btn.classList.add('active');
                }
            });

            // Update attendance data
            if (updateData) {
                attendanceData[studentId] = status;
            }
        }

        // Bulk operations
        function markAllPresent() {
            document.querySelectorAll('.student-card').forEach(card => {
                const studentId = card.dataset.studentId;
                setStatus(studentId, 'present');
            });
        }

        function markAllAbsent() {
            document.querySelectorAll('.student-card').forEach(card => {
                const studentId = card.dataset.studentId;
                setStatus(studentId, 'absent');
            });
        }

        function clearAll() {
            document.querySelectorAll('.student-card').forEach(card => {
                const studentId = card.dataset.studentId;
                card.classList.remove('present', 'absent', 'late');
                card.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                delete attendanceData[studentId];
            });
        }

        // Save attendance
        function saveAttendance() {
            const classId = document.getElementById('class_id').value;
            const date = document.getElementById('date').value;
            const subjectId = document.getElementById('subject_id').value;

            if (!classId || !date) {
                alert('Please select a class and date.');
                return;
            }

            if (Object.keys(attendanceData).length === 0) {
                alert('Please mark attendance for at least one student.');
                return;
            }

            // Show loading
            const saveBtn = document.querySelector('button[onclick="saveAttendance()"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
            saveBtn.disabled = true;

            fetch('/admin/attendance/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    class_id: classId,
                    date: date,
                    subject_id: subjectId,
                    attendance: attendanceData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload to show updated data
                    window.location.reload();
                } else {
                    alert('Failed to save attendance: ' + (data.message || 'Unknown error'));
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                    }
                }
            })
            .catch(error => {
                alert('Error saving attendance');
                console.error('Save error:', error);
            })
            .finally(() => {
                // Restore button
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        }
    </script>
</body>
</html>