<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Issue Transfer Certificate'); ?></title>
    <meta name="description" content="Issue a new transfer certificate for a student">

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
            cursor: pointer;
            transition: all 0.3s;
        }
        .student-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .student-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
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
                    <a class="nav-link active" href="/admin/certificates">
                        <i class="bi bi-file-earmark-ruled"></i>
                        <span>Certificates</span>
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
                        <h5 class="mb-0">Issue Transfer Certificate</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/certificates">Certificates</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Issue Certificate</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/certificates" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Certificates
                    </a>
                </div>
            </div>
        </header>

        <!-- Certificate Creation Content -->
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

            <form id="certificateForm" method="POST" action="/admin/certificates/store">
                <!-- Student Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>
                            Select Student
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="student_search" class="form-label">Search Student</label>
                                <input type="text" class="form-control" id="student_search"
                                       placeholder="Enter scholar number or student name">
                            </div>
                            <div class="col-md-6">
                                <label for="class_filter" class="form-label">Filter by Class</label>
                                <select class="form-select" id="class_filter">
                                    <option value="">All Classes</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="students_list" class="row">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card student-card" data-student-id="<?php echo $student['id']; ?>">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                           name="student_id" value="<?php echo $student['id']; ?>"
                                                           id="student_<?php echo $student['id']; ?>">
                                                    <label class="form-check-label w-100" for="student_<?php echo $student['id']; ?>">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-3">
                                                                <i class="bi bi-person-circle fs-1 text-primary"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></h6>
                                                                <small class="text-muted"><?php echo htmlspecialchars($student['scholar_number']); ?></small><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="bi bi-search fs-1 text-muted mb-3"></i>
                                        <p class="text-muted">No students found. Try adjusting your search criteria.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Certificate Details -->
                <div class="card mb-4" id="certificate_details" style="display: none;">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-ruled me-2"></i>
                            Certificate Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="certificate_number" class="form-label">Certificate Number *</label>
                                    <input type="text" class="form-control" id="certificate_number"
                                           name="certificate_number" readonly>
                                    <div class="form-text">Auto-generated certificate number</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label">Issue Date *</label>
                                    <input type="date" class="form-control" id="issue_date"
                                           name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="transfer_reason" class="form-label">Transfer Reason *</label>
                            <select class="form-select" id="transfer_reason" name="transfer_reason" required>
                                <option value="">Select reason</option>
                                <option value="Family relocation">Family relocation</option>
                                <option value="Better educational facilities">Better educational facilities</option>
                                <option value="Change of school">Change of school</option>
                                <option value="Medical reasons">Medical reasons</option>
                                <option value="Parent job transfer">Parent job transfer</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="transfer_to_school" class="form-label">Transferring To School</label>
                            <input type="text" class="form-control" id="transfer_to_school"
                                   name="transfer_to_school" placeholder="Name of the school student is transferring to">
                        </div>

                        <div class="mb-3">
                            <label for="conduct_grade" class="form-label">Conduct Grade *</label>
                            <select class="form-select" id="conduct_grade" name="conduct_grade" required>
                                <option value="excellent">Excellent</option>
                                <option value="very_good">Very Good</option>
                                <option value="good" selected>Good</option>
                                <option value="satisfactory">Satisfactory</option>
                                <option value="needs_improvement">Needs Improvement</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks"
                                      rows="3" placeholder="Additional remarks or comments"></textarea>
                        </div>

                        <!-- Academic Record Preview -->
                        <div class="mb-3">
                            <label class="form-label">Academic Record (Auto-generated)</label>
                            <div class="border rounded p-3 bg-light">
                                <div id="academic_record_preview">
                                    <em class="text-muted">Select a student to preview academic record</em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2" id="action_buttons" style="display: none;">
                    <button type="button" class="btn btn-outline-secondary" onclick="previewCertificate()">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Issue Certificate
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let selectedStudentId = null;

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

            // Student selection
            document.querySelectorAll('.student-card').forEach(card => {
                card.addEventListener('click', function() {
                    const studentId = this.dataset.studentId;
                    const radio = this.querySelector('input[type="radio"]');

                    // Remove selected class from all cards
                    document.querySelectorAll('.student-card').forEach(c => c.classList.remove('selected'));

                    // Add selected class to clicked card
                    this.classList.add('selected');

                    // Check the radio button
                    radio.checked = true;

                    // Load student details
                    loadStudentDetails(studentId);
                });
            });

            // Student search
            document.getElementById('student_search').addEventListener('input', function() {
                filterStudents();
            });

            // Class filter
            document.getElementById('class_filter').addEventListener('change', function() {
                filterStudents();
            });
        });

        // Filter students based on search and class
        function filterStudents() {
            const searchTerm = document.getElementById('student_search').value.toLowerCase();
            const classId = document.getElementById('class_filter').value;

            document.querySelectorAll('.student-card').forEach(card => {
                const studentName = card.querySelector('h6').textContent.toLowerCase();
                const scholarNumber = card.querySelector('small').textContent.toLowerCase();
                const studentClass = card.dataset.classId;

                const matchesSearch = studentName.includes(searchTerm) || scholarNumber.includes(searchTerm);
                const matchesClass = !classId || studentClass === classId;

                if (matchesSearch && matchesClass) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Load student details and generate certificate number
        function loadStudentDetails(studentId) {
            selectedStudentId = studentId;

            // Generate certificate number
            fetch(`/admin/certificates/generate-number`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('certificate_number').value = data.certificate_number;
            });

            // Load academic record
            fetch(`/admin/certificates/get-academic-record/${studentId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('academic_record_preview').innerHTML = data.record.replace(/\n/g, '<br>');
            });

            // Show certificate details
            document.getElementById('certificate_details').style.display = 'block';
            document.getElementById('action_buttons').style.display = 'flex';
        }

        // Preview certificate
        function previewCertificate() {
            if (!selectedStudentId) {
                alert('Please select a student first');
                return;
            }

            const formData = new FormData(document.getElementById('certificateForm'));
            formData.append('preview', '1');

            fetch('/admin/certificates/preview', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.open(data.preview_url, '_blank');
                } else {
                    alert('Error generating preview: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error generating preview');
            });
        }
    </script>
</body>
</html>