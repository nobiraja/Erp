<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Students Management'); ?></title>
    <meta name="description" content="Manage students in the school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .student-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-badge {
            font-size: 0.75rem;
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
                        <h5 class="mb-0">Students Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Students</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/students/create" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Add Student
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showBulkImportModal()">
                                <i class="bi bi-upload me-2"></i>Bulk Import
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/students/export">
                                <i class="bi bi-download me-2"></i>Export CSV
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Students Content -->
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

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="section" class="form-label">Section</label>
                        <select class="form-select" id="section" name="section">
                            <option value="">All Sections</option>
                            <option value="A" <?php echo ($filters['section'] ?? '') === 'A' ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo ($filters['section'] ?? '') === 'B' ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo ($filters['section'] ?? '') === 'C' ? 'selected' : ''; ?>>C</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               placeholder="Search by name, scholar number, admission number...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/students" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Students Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Students (<?php echo number_format($pagination['total']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="studentsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Scholar No.</th>
                                    <th>Admission No.</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <?php if ($student['photo_path'] && file_exists($student['photo_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($student['photo_path']); ?>"
                                                     alt="Photo" class="student-photo">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center student-photo">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></div>
                                            <small class="text-muted">DOB: <?php echo date('d/m/Y', strtotime($student['dob'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $student['gender'] === 'male' ? 'primary' : ($student['gender'] === 'female' ? 'success' : 'secondary'); ?>">
                                                <?php echo ucfirst($student['gender']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($student['mobile']): ?>
                                                <i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($student['mobile']); ?><br>
                                            <?php endif; ?>
                                            <?php if ($student['email']): ?>
                                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($student['email']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge status-badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/students/<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-info" title="View Profile">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/admin/students/<?php echo $student['id']; ?>/edit" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="generateIdCard(<?php echo $student['id']; ?>)" class="btn btn-sm btn-outline-primary" title="ID Card">
                                                    <i class="bi bi-card-text"></i>
                                                </button>
                                                <button onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Students pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&class_id=<?php echo $filters['class_id'] ?? ''; ?>&section=<?php echo $filters['section'] ?? ''; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&class_id=<?php echo $filters['class_id'] ?? ''; ?>&section=<?php echo $filters['section'] ?? ''; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&class_id=<?php echo $filters['class_id'] ?? ''; ?>&section=<?php echo $filters['section'] ?? ''; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bulk Import Modal -->
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Import Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/admin/students/import" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="import_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".csv" required>
                            <div class="form-text">
                                CSV file should contain headers: scholar_number, admission_number, admission_date, first_name, middle_name, last_name, class_id, section, father_name, mother_name, guardian_name, guardian_contact, dob, gender, caste_category, nationality, religion, blood_group, village_address, permanent_address, temporary_address, mobile, email, aadhar, samagra, apaar_id, pan, previous_school, medical_conditions
                            </div>
                        </div>
                        <div class="mb-3">
                            <a href="/admin/students/sample-csv" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-download me-1"></i>Download Sample CSV
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import Students</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

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

            // Initialize DataTable
            $('#studentsTable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "searching": false,
                "paging": false,
                "info": false,
                "responsive": true
            });
        });

        // Show bulk import modal
        function showBulkImportModal() {
            const modal = new bootstrap.Modal(document.getElementById('bulkImportModal'));
            modal.show();
        }

        // Delete student
        function deleteStudent(id, name) {
            if (confirm(`Are you sure you want to delete student "${name}"? This action cannot be undone.`)) {
                fetch(`/admin/students/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Student deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete student: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting student');
                    console.error('Delete error:', error);
                });
            }
        }

        // Generate ID card
        function generateIdCard(id) {
            window.open(`/admin/students/${id}/id-card`, '_blank');
        }
    </script>
</body>
</html>