<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Announcements Management'); ?></title>
    <meta name="description" content="Manage school announcements and notifications">

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
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .priority-urgent {
            border-left: 4px solid #dc3545;
        }
        .priority-high {
            border-left: 4px solid #fd7e14;
        }
        .priority-medium {
            border-left: 4px solid #ffc107;
        }
        .priority-low {
            border-left: 4px solid #28a745;
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
                    <a class="nav-link" href="/admin/events">
                        <i class="bi bi-calendar-event"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/announcements">
                        <i class="bi bi-megaphone"></i>
                        <span>Announcements</span>
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
                        <h5 class="mb-0">Announcements Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Announcements</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/announcements/create" class="btn btn-primary">
                        <i class="bi bi-megaphone me-1"></i>Add Announcement
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/events">
                                <i class="bi bi-calendar-event me-2"></i>Events
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Announcements Content -->
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
                    <div class="card stats-card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-megaphone text-primary fs-1"></i>
                            <h4 class="mt-2 mb-1"><?php echo number_format($stats['total_announcements']); ?></h4>
                            <p class="text-muted mb-0">Total Announcements</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <h4 class="mt-2 mb-1"><?php echo number_format($stats['active_announcements']); ?></h4>
                            <p class="text-muted mb-0">Active</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-clock text-warning fs-1"></i>
                            <h4 class="mt-2 mb-1"><?php echo number_format($stats['expired_announcements']); ?></h4>
                            <p class="text-muted mb-0">Expired</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle text-info fs-1"></i>
                            <h4 class="mt-2 mb-1"><?php echo number_format($stats['priority_breakdown']['urgent'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Urgent Priority</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="all" <?php echo ($filters['priority'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                            <option value="urgent" <?php echo ($filters['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            <option value="high" <?php echo ($filters['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo ($filters['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo ($filters['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="visibility" class="form-label">Visibility</label>
                        <select class="form-select" id="visibility" name="visibility">
                            <option value="all" <?php echo ($filters['visibility'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="students" <?php echo ($filters['visibility'] ?? '') === 'students' ? 'selected' : ''; ?>>Students</option>
                            <option value="teachers" <?php echo ($filters['visibility'] ?? '') === 'teachers' ? 'selected' : ''; ?>>Teachers</option>
                            <option value="parents" <?php echo ($filters['visibility'] ?? '') === 'parents' ? 'selected' : ''; ?>>Parents</option>
                            <option value="admin" <?php echo ($filters['visibility'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" <?php echo ($filters['status'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="expired" <?php echo ($filters['status'] ?? '') === 'expired' ? 'selected' : ''; ?>>Expired</option>
                            <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               placeholder="Search by title or content...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/announcements" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Announcements Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-megaphone me-2"></i>
                        Announcements (<?php echo number_format($pagination['total']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="announcementsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Visibility</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($announcements as $announcement): ?>
                                    <tr class="priority-<?php echo $announcement['priority']; ?>">
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($announcement['content'] ?? '', 0, 50)); ?><?php echo strlen($announcement['content'] ?? '') > 50 ? '...' : ''; ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClasses = [
                                                'urgent' => 'danger',
                                                'high' => 'warning',
                                                'medium' => 'info',
                                                'low' => 'success'
                                            ];
                                            $priorityClass = $priorityClasses[$announcement['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $priorityClass; ?>">
                                                <?php echo ucfirst($announcement['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $visibilityLabels = [
                                                'all' => 'All Users',
                                                'students' => 'Students',
                                                'teachers' => 'Teachers',
                                                'parents' => 'Parents',
                                                'admin' => 'Admin Only'
                                            ];
                                            $visibilityLabel = $visibilityLabels[$announcement['visibility']] ?? 'Unknown';
                                            $visibilityClasses = [
                                                'all' => 'primary',
                                                'students' => 'success',
                                                'teachers' => 'info',
                                                'parents' => 'warning',
                                                'admin' => 'danger'
                                            ];
                                            $visibilityClass = $visibilityClasses[$announcement['visibility']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $visibilityClass; ?>">
                                                <?php echo $visibilityLabel; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!$announcement['is_active']): ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php elseif ($announcement['expires_at'] && strtotime($announcement['expires_at']) <= time()): ?>
                                                <span class="badge bg-dark">Expired</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($announcement['created_by_name'] ?: 'System'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($announcement['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/admin/announcements/<?php echo $announcement['id']; ?>/edit" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>, '<?php echo htmlspecialchars($announcement['title']); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
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
                        <nav aria-label="Announcements pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&priority=<?php echo $filters['priority'] ?? 'all'; ?>&visibility=<?php echo $filters['visibility'] ?? 'all'; ?>&status=<?php echo $filters['status'] ?? 'all'; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&priority=<?php echo $filters['priority'] ?? 'all'; ?>&visibility=<?php echo $filters['visibility'] ?? 'all'; ?>&status=<?php echo $filters['status'] ?? 'all'; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&priority=<?php echo $filters['priority'] ?? 'all'; ?>&visibility=<?php echo $filters['visibility'] ?? 'all'; ?>&status=<?php echo $filters['status'] ?? 'all'; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>">
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
            $('#announcementsTable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "searching": false,
                "paging": false,
                "info": false,
                "responsive": true
            });
        });

        // Delete announcement
        function deleteAnnouncement(id, title) {
            if (confirm(`Are you sure you want to delete announcement "${title}"? This action cannot be undone.`)) {
                fetch(`/admin/announcements/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Announcement deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete announcement: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting announcement');
                    console.error('Delete error:', error);
                });
            }
        }
    </script>
</body>
</html>