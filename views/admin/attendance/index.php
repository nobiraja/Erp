<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Attendance Management'); ?></title>
    <meta name="description" content="Manage attendance in the school management system">

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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stats-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stats-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .status-present { color: #28a745; }
        .status-absent { color: #dc3545; }
        .status-late { color: #ffc107; }
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
                        <h5 class="mb-0">Attendance Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/attendance/mark" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Mark Attendance
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/attendance/reports">
                                <i class="bi bi-file-earmark-text me-2"></i>Reports
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/attendance/analytics">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showBulkUploadModal()">
                                <i class="bi bi-upload me-2"></i>Bulk Upload
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Attendance Content -->
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

            <!-- Today's Stats -->
            <?php if ($today_stats): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $today_stats['total_marked'] ?? 0; ?></div>
                        <div class="stat-label">Total Marked Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <div class="stat-number"><?php echo $today_stats['present_count'] ?? 0; ?></div>
                        <div class="stat-label">Present Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                        <div class="stat-number"><?php echo $today_stats['absent_count'] ?? 0; ?></div>
                        <div class="stat-label">Absent Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #e83e8c 100%);">
                        <div class="stat-number"><?php echo $today_stats['late_count'] ?? 0; ?></div>
                        <div class="stat-label">Late Today</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class->id; ?>" <?php echo $class_id == $class->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date"
                               value="<?php echo htmlspecialchars($date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="late" <?php echo $status === 'late' ? 'selected' : ''; ?>>Late</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search by student name, scholar number...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/attendance" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-check me-2"></i>
                        Attendance Records (<?php echo number_format($total); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="attendanceTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Scholar No.</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Subject</th>
                                    <th>Marked By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['scholar_number']); ?></td>
                                        <td><?php echo htmlspecialchars($record['class_name'] . ' ' . $record['section']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($record['attendance_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $record['status'] === 'present' ? 'success' :
                                                     ($record['status'] === 'absent' ? 'danger' : 'warning');
                                            ?>">
                                                <i class="bi bi-<?php
                                                    echo $record['status'] === 'present' ? 'check-circle' :
                                                         ($record['status'] === 'absent' ? 'x-circle' : 'clock');
                                                ?> me-1"></i>
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['subject_name'] ?? 'General'); ?></td>
                                        <td><?php echo htmlspecialchars($record['marked_by_name']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button onclick="editAttendance(<?php echo $record['id']; ?>)" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button onclick="deleteAttendance(<?php echo $record['id']; ?>, '<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
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
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Attendance pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&class_id=<?php echo $class_id; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&class_id=<?php echo $class_id; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&class_id=<?php echo $class_id; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
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

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Upload Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bulkUploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="attendance_file" class="form-label">Select CSV/Excel File</label>
                            <input type="file" class="form-control" id="attendance_file" name="attendance_file"
                                   accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">
                                File should contain columns: student_id, class_id, attendance_date, status, subject_id (optional)
                            </div>
                        </div>
                        <div class="mb-3">
                            <a href="/admin/attendance/download-template" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-download me-1"></i>Download Template
                            </a>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="uploadAttendanceFile()">Upload</button>
                </div>
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
            $('#attendanceTable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "searching": false,
                "paging": false,
                "info": false,
                "responsive": true
            });
        });

        // Show bulk upload modal
        function showBulkUploadModal() {
            const modal = new bootstrap.Modal(document.getElementById('bulkUploadModal'));
            modal.show();
        }

        // Upload attendance file
        function uploadAttendanceFile() {
            const form = document.getElementById('bulkUploadForm');
            const formData = new FormData(form);

            fetch('/admin/attendance/bulk-upload', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error uploading file');
                console.error('Upload error:', error);
            });

            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkUploadModal'));
            modal.hide();
        }

        // Edit attendance (placeholder)
        function editAttendance(id) {
            // Redirect to edit page or open modal
            window.location.href = `/admin/attendance/edit/${id}`;
        }

        // Delete attendance
        function deleteAttendance(id, studentName) {
            if (confirm(`Are you sure you want to delete attendance record for "${studentName}"?`)) {
                fetch(`/admin/attendance/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Attendance record deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete attendance record: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting attendance record');
                    console.error('Delete error:', error);
                });
            }
        }
    </script>
</body>
</html>