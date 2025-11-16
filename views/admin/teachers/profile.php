<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Teacher Profile'); ?></title>
    <meta name="description" content="View teacher profile and details">

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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.375rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .info-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-section h5 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1rem;
            color: #212529;
            word-break: break-word;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .subject-assignment {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .performance-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
            text-align: center;
            height: 100%;
        }
        .performance-card h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
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
                    <a class="nav-link active" href="/admin/teachers">
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
                        <h5 class="mb-0">Teacher Profile</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/teachers">Teachers</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="assignSubjects(<?php echo $teacher['id']; ?>)" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Assign Subjects
                    </button>
                    <a href="/admin/teachers/<?php echo $teacher['id']; ?>/edit" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Teacher
                    </a>
                    <a href="/admin/teachers" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Teachers
                    </a>
                </div>
            </div>
        </header>

        <!-- Teacher Profile Content -->
        <main class="p-4">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <?php if ($teacher['photo_path'] && file_exists($teacher['photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($teacher['photo_path']); ?>" alt="Teacher Photo" class="profile-photo">
                        <?php else: ?>
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center profile-photo mx-auto">
                                <i class="bi bi-person text-secondary fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-1"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . ($teacher['middle_name'] ? $teacher['middle_name'] . ' ' : '') . $teacher['last_name']); ?></h2>
                        <p class="mb-2">
                            <i class="bi bi-person-badge me-1"></i>
                            Employee ID: <?php echo htmlspecialchars($teacher['employee_id']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-building me-1"></i>
                            <?php echo htmlspecialchars($teacher['designation'] ?: 'N/A'); ?> - <?php echo htmlspecialchars($teacher['department'] ?: 'N/A'); ?>
                        </p>
                        <div>
                            <span class="badge status-badge bg-<?php echo $teacher['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $teacher['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <span class="badge status-badge bg-<?php echo $teacher['gender'] === 'male' ? 'primary' : ($teacher['gender'] === 'female' ? 'success' : 'secondary'); ?> ms-2">
                                <?php echo ucfirst($teacher['gender']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="mb-2">
                            <small class="text-white-50">Experience</small>
                            <h4 class="mb-0"><?php echo htmlspecialchars($teacher['experience_years'] ?: 0); ?> years</h4>
                        </div>
                        <div>
                            <small class="text-white-50">Date of Joining</small>
                            <p class="mb-0"><?php echo $teacher['date_of_joining'] ? date('M d, Y', strtotime($teacher['date_of_joining'])) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="info-section">
                <h5><i class="bi bi-graph-up me-2"></i>Performance Metrics</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="performance-card">
                            <h6 class="text-muted">Total Sessions</h6>
                            <h3 class="text-primary"><?php echo $performanceMetrics['total_sessions'] ?? 0; ?></h3>
                            <small class="text-muted">Attendance Marked</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="performance-card">
                            <h6 class="text-muted">Results Entered</h6>
                            <h3 class="text-success"><?php echo $performanceMetrics['total_results_entered'] ?? 0; ?></h3>
                            <small class="text-muted">Exam Results</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="performance-card">
                            <h6 class="text-muted">Workload</h6>
                            <h3 class="text-warning"><?php echo $performanceMetrics['workload'] ?? 0; ?></h3>
                            <small class="text-muted">Assigned Classes</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="performance-card">
                            <h6 class="text-muted">Subjects</h6>
                            <h3 class="text-info"><?php echo $performanceMetrics['assigned_subjects'] ?? 0; ?></h3>
                            <small class="text-muted">Teaching</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Assignments -->
            <div class="info-section">
                <h5><i class="bi bi-book me-2"></i>Subject Assignments</h5>
                <?php if (!empty($assignedSubjects)): ?>
                    <div class="row">
                        <?php foreach ($assignedSubjects as $assignment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="subject-assignment">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($assignment['subject_name']); ?></h6>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-code me-1"></i><?php echo htmlspecialchars($assignment['subject_code']); ?>
                                            </p>
                                            <p class="mb-0 text-muted small">
                                                <i class="bi bi-house-door me-1"></i>Class: <?php echo htmlspecialchars($assignment['class_name'] . ' ' . $assignment['section']); ?>
                                            </p>
                                        </div>
                                        <button onclick="removeSubjectAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['subject_name']); ?>')" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-book text-muted fs-1"></i>
                        <p class="text-muted mt-2">No subjects assigned yet</p>
                        <button onclick="assignSubjects(<?php echo $teacher['id']; ?>)" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Assign Subjects
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Basic Information -->
            <div class="info-section">
                <h5><i class="bi bi-person me-2"></i>Basic Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . ($teacher['middle_name'] ? $teacher['middle_name'] . ' ' : '') . $teacher['last_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Employee ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['employee_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo $teacher['dob'] ? date('F d, Y', strtotime($teacher['dob'])) : 'Not provided'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo ucfirst($teacher['gender']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value"><?php echo ucfirst($teacher['marital_status'] ?: 'Not specified'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Blood Group</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['blood_group'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-<?php echo $teacher['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $teacher['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="info-section">
                <h5><i class="bi bi-briefcase me-2"></i>Professional Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Designation</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['designation'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['department'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Qualification</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['qualification'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Specialization</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['specialization'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Joining</div>
                        <div class="info-value"><?php echo $teacher['date_of_joining'] ? date('F d, Y', strtotime($teacher['date_of_joining'])) : 'Not provided'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Experience</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['experience_years'] ?: 0); ?> years</div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="info-section">
                <h5><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mobile Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['mobile'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['email'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Aadhar Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['aadhar'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">PAN Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['pan'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Samagra ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['samagra_id'] ?: 'Not provided'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="info-section">
                <h5><i class="bi bi-geo-alt me-2"></i>Address Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Permanent Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($teacher['permanent_address'] ?: 'Not provided')); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Temporary Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($teacher['temporary_address'] ?: 'Not provided')); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="info-section">
                <h5><i class="bi bi-heart me-2"></i>Medical Information</h5>
                <div class="info-item">
                    <div class="info-label">Medical Conditions</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($teacher['medical_conditions'] ?: 'None')); ?></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Subject Assignment Modal -->
    <div class="modal fade" id="assignSubjectsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Subjects to Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignSubjectsForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assign_class_id" class="form-label">Class</label>
                                <select class="form-select" id="assign_class_id" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <!-- Classes will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assign_subjects" class="form-label">Subjects</label>
                                <select class="form-select" id="assign_subjects" name="subject_ids[]" multiple required>
                                    <!-- Subjects will be loaded based on class selection -->
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple subjects</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Subjects</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let currentTeacherId = <?php echo $teacher['id']; ?>;

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

        // Assign subjects
        function assignSubjects(teacherId) {
            currentTeacherId = teacherId;
            loadClasses();
            const modal = new bootstrap.Modal(document.getElementById('assignSubjectsModal'));
            modal.show();
        }

        // Load classes
        function loadClasses() {
            fetch('/admin/teachers/get-classes')
                .then(response => response.json())
                .then(data => {
                    const classSelect = document.getElementById('assign_class_id');
                    classSelect.innerHTML = '<option value="">Select Class</option>';
                    data.forEach(cls => {
                        classSelect.innerHTML += `<option value="${cls.id}">${cls.class_name} ${cls.section} (${cls.academic_year})</option>`;
                    });
                })
                .catch(error => console.error('Error loading classes:', error));
        }

        // Load subjects when class is selected
        document.getElementById('assign_class_id').addEventListener('change', function() {
            const classId = this.value;
            if (classId) {
                fetch(`/admin/teachers/get-subjects?class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        const subjectSelect = document.getElementById('assign_subjects');
                        subjectSelect.innerHTML = '';
                        data.forEach(subject => {
                            subjectSelect.innerHTML += `<option value="${subject.id}">${subject.subject_name} (${subject.subject_code})</option>`;
                        });
                    })
                    .catch(error => console.error('Error loading subjects:', error));
            }
        });

        // Handle subject assignment form
        document.getElementById('assignSubjectsForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('teacher_id', currentTeacherId);

            fetch(`/admin/teachers/${currentTeacherId}/assign-subjects`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Subjects assigned successfully');
                    location.reload();
                } else {
                    alert('Failed to assign subjects: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error assigning subjects');
                console.error('Assign error:', error);
            });
        });

        // Remove subject assignment
        function removeSubjectAssignment(assignmentId, subjectName) {
            if (confirm(`Are you sure you want to remove "${subjectName}" assignment?`)) {
                fetch(`/admin/teachers/${currentTeacherId}/remove-subject/${assignmentId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Subject assignment removed successfully');
                        location.reload();
                    } else {
                        alert('Failed to remove subject assignment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error removing subject assignment');
                    console.error('Remove error:', error);
                });
            }
        }
    </script>
</body>
</html>