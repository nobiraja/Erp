<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Exams & Results Management'); ?></title>
    <meta name="description" content="Manage exams and results in the school management system">

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
                    <a class="nav-link active" href="/admin/exams">
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
                        <h5 class="mb-0">Exams & Results Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Exams</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/exams/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create Exam
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showBulkActionsModal()">
                                <i class="bi bi-check-circle me-2"></i>Bulk Actions
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/exams/export">
                                <i class="bi bi-download me-2"></i>Export Data
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Exams Content -->
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $stats['total_exams'] ?? 0; ?></div>
                        <div class="stat-label">Total Exams</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="stat-number"><?php echo $stats['active_exams'] ?? 0; ?></div>
                        <div class="stat-label">Active Exams</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="stat-number"><?php echo $stats['classes_with_exams'] ?? 0; ?></div>
                        <div class="stat-label">Classes with Exams</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <div class="stat-number"><?php echo $stats['upcoming_exams'] ?? 0; ?></div>
                        <div class="stat-label">Upcoming Exams</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="exam_type" class="form-label">Exam Type</label>
                        <select class="form-select" id="exam_type" name="exam_type">
                            <option value="">All Types</option>
                            <option value="mid-term" <?php echo $exam_type === 'mid-term' ? 'selected' : ''; ?>>Mid-term</option>
                            <option value="final" <?php echo $exam_type === 'final' ? 'selected' : ''; ?>>Final</option>
                            <option value="unit-test" <?php echo $exam_type === 'unit-test' ? 'selected' : ''; ?>>Unit Test</option>
                            <option value="custom" <?php echo $exam_type === 'custom' ? 'selected' : ''; ?>>Custom</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <select class="form-select" id="academic_year" name="academic_year">
                            <option value="">All Years</option>
                            <?php for ($year = date('Y') + 1; $year >= date('Y') - 2; $year--): ?>
                                <option value="<?php echo $year . '-' . ($year + 1); ?>" <?php echo $academic_year === $year . '-' . ($year + 1) ? 'selected' : ''; ?>>
                                    <?php echo $year . '-' . ($year + 1); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search by exam name...">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/exams" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Exams Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Exams (<?php echo number_format($total); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="examsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Type</th>
                                    <th>Class</th>
                                    <th>Academic Year</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($exam['exam_name']); ?></div>
                                            <small class="text-muted">Created by: <?php echo htmlspecialchars($exam['created_by_name']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $exam['exam_type'] === 'mid-term' ? 'info' :
                                                     ($exam['exam_type'] === 'final' ? 'success' :
                                                      ($exam['exam_type'] === 'unit-test' ? 'warning' : 'secondary'));
                                            ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $exam['exam_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($exam['class_name'] . ' ' . $exam['section']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['academic_year']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($exam['start_date'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($exam['end_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $exam['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $exam['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/exams/results?exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-info" title="View Results">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/admin/exams/schedule?exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-primary" title="Schedule">
                                                    <i class="bi bi-calendar"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="/admin/exams/<?php echo $exam['id']; ?>/edit">
                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="generateAdmitCards(<?php echo $exam['id']; ?>)">
                                                            <i class="bi bi-card-text me-2"></i>Admit Cards
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="generateMarksheets(<?php echo $exam['id']; ?>)">
                                                            <i class="bi bi-file-earmark-pdf me-2"></i>Marksheets
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteExam(<?php echo $exam['id']; ?>, '<?php echo htmlspecialchars($exam['exam_name']); ?>')">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Exams pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&class_id=<?php echo $class_id; ?>&exam_type=<?php echo $exam_type; ?>&academic_year=<?php echo $academic_year; ?>&search=<?php echo urlencode($search); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&class_id=<?php echo $class_id; ?>&exam_type=<?php echo $exam_type; ?>&academic_year=<?php echo $academic_year; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&class_id=<?php echo $class_id; ?>&exam_type=<?php echo $exam_type; ?>&academic_year=<?php echo $academic_year; ?>&search=<?php echo urlencode($search); ?>">
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

    <!-- Bulk Actions Modal -->
    <div class="modal fade" id="bulkActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select exams to perform bulk actions:</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label" for="selectAll">
                            Select All Exams
                        </label>
                    </div>
                    <hr>
                    <button class="btn btn-outline-primary me-2" onclick="bulkActivate()">
                        <i class="bi bi-check-circle me-1"></i>Activate Selected
                    </button>
                    <button class="btn btn-outline-secondary me-2" onclick="bulkDeactivate()">
                        <i class="bi bi-pause-circle me-1"></i>Deactivate Selected
                    </button>
                    <button class="btn btn-outline-danger" onclick="bulkDelete()">
                        <i class="bi bi-trash me-1"></i>Delete Selected
                    </button>
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
            $('#examsTable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "searching": false,
                "paging": false,
                "info": false,
                "responsive": true
            });
        });

        // Show bulk actions modal
        function showBulkActionsModal() {
            const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
            modal.show();
        }

        // Delete exam
        function deleteExam(id, name) {
            if (confirm(`Are you sure you want to delete exam "${name}"? This action cannot be undone.`)) {
                fetch(`/admin/exams/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Exam deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete exam: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting exam');
                    console.error('Delete error:', error);
                });
            }
        }

        // Generate admit cards
        function generateAdmitCards(examId) {
            window.open(`/admin/exams/admit-cards?exam_id=${examId}&format=bulk`, '_blank');
        }

        // Generate marksheets
        function generateMarksheets(examId) {
            window.open(`/admin/exams/marksheets?exam_id=${examId}&format=bulk`, '_blank');
        }

        // Bulk actions
        function bulkActivate() {
            // Implementation for bulk activate
            alert('Bulk activate functionality to be implemented');
        }

        function bulkDeactivate() {
            // Implementation for bulk deactivate
            alert('Bulk deactivate functionality to be implemented');
        }

        function bulkDelete() {
            // Implementation for bulk delete
            alert('Bulk delete functionality to be implemented');
        }
    </script>
</body>
</html>