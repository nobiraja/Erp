<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Subjects Management'); ?></title>
    <meta name="description" content="Manage subjects in the school management system">

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
                    <a class="nav-link active" href="/admin/subjects">
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
                        <h5 class="mb-0">Subjects Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Subjects</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/subjects/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Add Subject
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showBulkDeleteModal()">
                                <i class="bi bi-trash me-2"></i>Bulk Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Subjects Content -->
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
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search by subject name or code">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                        <a href="/admin/subjects" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Subjects Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-journal-text me-2"></i>
                        Subjects (<?php echo number_format($total); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="subjectsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Subject Name</th>
                                    <th>Subject Code</th>
                                    <th>Description</th>
                                    <th>Classes Assigned</th>
                                    <th>Teachers</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input subject-checkbox" value="<?php echo $subject->id; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($subject->subject_name); ?></td>
                                        <td><code><?php echo htmlspecialchars($subject->subject_code); ?></code></td>
                                        <td>
                                            <?php if ($subject->description): ?>
                                                <span title="<?php echo htmlspecialchars($subject->description); ?>">
                                                    <?php echo htmlspecialchars(substr($subject->description, 0, 50)); ?>
                                                    <?php if (strlen($subject->description) > 50): ?>...<?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No description</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $subject->class_count ?? 0; ?> classes</span>
                                        </td>
                                        <td><?php echo count($subject->teachers()); ?> teachers</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/subjects/<?php echo $subject->id; ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/admin/subjects/<?php echo $subject->id; ?>/edit" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="deleteSubject(<?php echo $subject->id; ?>, '<?php echo htmlspecialchars($subject->subject_name); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
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
                        <nav aria-label="Subjects pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>">
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

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Delete Subjects</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the selected subjects? This action cannot be undone.</p>
                    <div id="selectedSubjectsList" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()">Delete Selected</button>
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
            $('#subjectsTable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "searching": false,
                "paging": false,
                "info": false,
                "responsive": true
            });

            // Select all checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.subject-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        });

        // Show bulk delete modal
        function showBulkDeleteModal() {
            const selectedCheckboxes = document.querySelectorAll('.subject-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one subject to delete.');
                return;
            }

            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            const selectedSubjectsList = document.getElementById('selectedSubjectsList');
            selectedSubjectsList.innerHTML = '<strong>Selected subjects:</strong><ul>' +
                selectedIds.map(id => `<li>Subject ID: ${id}</li>`).join('') + '</ul>';

            const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
            modal.show();
        }

        // Confirm bulk delete
        function confirmBulkDelete() {
            const selectedCheckboxes = document.querySelectorAll('.subject-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            fetch('/admin/subjects/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`${data.data.deleted} subject(s) deleted successfully`);
                    location.reload();
                } else {
                    alert('Failed to delete subjects: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error deleting subjects');
                console.error('Delete error:', error);
            });

            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'));
            modal.hide();
        }

        // Delete single subject
        function deleteSubject(id, name) {
            if (confirm(`Are you sure you want to delete subject "${name}"? This action cannot be undone.`)) {
                fetch(`/admin/subjects/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Subject deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete subject: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting subject');
                    console.error('Delete error:', error);
                });
            }
        }
    </script>
</body>
</html>