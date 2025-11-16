<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Student Profile'); ?></title>
    <meta name="description" content="View student profile and details">

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
                    <a class="nav-link active" href="/admin/students">
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
                        <h5 class="mb-0">Student Profile</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/students">Students</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/students/<?php echo $student['id']; ?>/edit" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Student
                    </a>
                    <button onclick="generateIdCard(<?php echo $student['id']; ?>)" class="btn btn-success">
                        <i class="bi bi-card-text me-1"></i>ID Card
                    </button>
                    <a href="/admin/students" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Students
                    </a>
                </div>
            </div>
        </header>

        <!-- Student Profile Content -->
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

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <?php if ($student['photo_path'] && file_exists($student['photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Student Photo" class="profile-photo">
                        <?php else: ?>
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center profile-photo mx-auto">
                                <i class="bi bi-person text-secondary fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></h2>
                        <p class="mb-2">
                            <i class="bi bi-mortarboard me-1"></i>
                            Scholar No: <?php echo htmlspecialchars($student['scholar_number']); ?> |
                            Admission No: <?php echo htmlspecialchars($student['admission_number']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-book me-1"></i>
                            Class: <?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?>
                        </p>
                        <div>
                            <span class="badge status-badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <span class="badge status-badge bg-<?php echo $student['gender'] === 'male' ? 'primary' : ($student['gender'] === 'female' ? 'success' : 'secondary'); ?> ms-2">
                                <?php echo ucfirst($student['gender']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="mb-2">
                            <small class="text-white-50">Age</small>
                            <h4 class="mb-0"><?php echo $student['dob'] ? date_diff(date_create($student['dob']), date_create('today'))->y : 'N/A'; ?> years</h4>
                        </div>
                        <div>
                            <small class="text-white-50">Admission Date</small>
                            <p class="mb-0"><?php echo $student['admission_date'] ? date('M d, Y', strtotime($student['admission_date'])) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="info-section">
                <h5><i class="bi bi-person me-2"></i>Basic Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo $student['dob'] ? date('F d, Y', strtotime($student['dob'])) : 'Not provided'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo ucfirst($student['gender']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Class & Section</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Scholar Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['scholar_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Admission Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['admission_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Admission Date</div>
                        <div class="info-value"><?php echo $student['admission_date'] ? date('F d, Y', strtotime($student['admission_date'])) : 'Not provided'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Family Information -->
            <div class="info-section">
                <h5><i class="bi bi-house me-2"></i>Family Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Father's Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['father_name'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Mother's Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['mother_name'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Guardian's Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['guardian_name'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Guardian's Contact</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['guardian_contact'] ?: 'Not provided'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="info-section">
                <h5><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mobile Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['mobile'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['email'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Aadhar Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['aadhar'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Samagra Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['samagra'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Apaar ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['apaar_id'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">PAN Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['pan'] ?: 'Not provided'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="info-section">
                <h5><i class="bi bi-geo-alt me-2"></i>Address Information</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Village Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($student['village_address'] ?: 'Not provided')); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Permanent Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($student['permanent_address'] ?: 'Not provided')); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Temporary Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($student['temporary_address'] ?: 'Not provided')); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="info-section">
                <h5><i class="bi bi-info-circle me-2"></i>Additional Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Caste/Category</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['caste_category'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nationality</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['nationality'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Religion</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['religion'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Blood Group</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['blood_group'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Previous School</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['previous_school'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Medical Conditions</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($student['medical_conditions'] ?: 'None')); ?></div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="info-section">
                <h5><i class="bi bi-book me-2"></i>Academic Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Attendance</h6>
                                <h3 class="text-primary mb-0"><?php echo $student['attendance_percentage'] ?? 0; ?>%</h3>
                                <small class="text-muted">Current Month</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Fee Status</h6>
                                <h3 class="text-<?php echo ($student['fee_status']['percentage'] ?? 0) >= 80 ? 'success' : 'warning'; ?> mb-0">
                                    <?php echo $student['fee_status']['percentage'] ?? 0; ?>%
                                </h3>
                                <small class="text-muted">Paid</small>
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
        });

        // Generate ID card
        function generateIdCard(id) {
            window.open(`/admin/students/${id}/id-card`, '_blank');
        }
    </script>
</body>
</html>