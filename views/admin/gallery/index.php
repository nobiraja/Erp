<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Gallery Management'); ?></title>
    <meta name="description" content="Gallery management dashboard for school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .gallery-item {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .chart-container {
            position: relative;
            height: 300px;
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
        .quick-action-btn {
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
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
                    <img src="<?php echo htmlspecialchars($school_logo ?? '/images/logo-small.png'); ?>" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle"><?php echo htmlspecialchars(substr($school_name ?? 'SMS', 0, 10)); ?></span>
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
                    <a class="nav-link active" href="/admin/gallery">
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
                        <div class="fw-bold small"><?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($current_user['role'] ?? 'Administrator'); ?></div>
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
                        <h5 class="mb-0">Gallery Management</h5>
                        <small class="text-muted">Manage photos, videos, and media content</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted"><?php echo date('M d, Y'); ?></small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/admin/change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Gallery Content -->
        <main class="p-4">
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-primary w-100 quick-action-btn" onclick="window.location.href='/admin/gallery/upload'">
                                        <i class="bi bi-cloud-upload d-block fs-4 mb-1"></i>
                                        <small>Upload Media</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-success w-100 quick-action-btn" onclick="window.location.href='/admin/gallery/categories'">
                                        <i class="bi bi-folder-plus d-block fs-4 mb-1"></i>
                                        <small>Manage Categories</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-info w-100 quick-action-btn" onclick="window.location.href='/admin/gallery/media'">
                                        <i class="bi bi-grid d-block fs-4 mb-1"></i>
                                        <small>View All Media</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-warning w-100 quick-action-btn" onclick="window.location.href='/admin/gallery/settings'">
                                        <i class="bi bi-gear d-block fs-4 mb-1"></i>
                                        <small>Settings</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-secondary w-100 quick-action-btn" onclick="window.open('/gallery', '_blank')">
                                        <i class="bi bi-eye d-block fs-4 mb-1"></i>
                                        <small>View Public Gallery</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-danger w-100 quick-action-btn" onclick="bulkDeleteSelected()">
                                        <i class="bi bi-trash d-block fs-4 mb-1"></i>
                                        <small>Bulk Delete</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsCards">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Media
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalMedia">
                                        <?php echo number_format($stats['total_media'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-images fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Categories
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCategories">
                                        <?php echo number_format($stats['total_categories'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-folder fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Recent Uploads
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="recentUploads">
                                        <?php echo number_format($stats['recent_uploads'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cloud-upload fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Featured Items
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="featuredCount">
                                        <?php echo number_format($stats['featured_count'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-star fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads and Popular Items -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Uploads</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="recentUploadsGrid">
                                <?php if (!empty($recent_uploads)): ?>
                                    <?php foreach ($recent_uploads as $item): ?>
                                        <div class="col-md-6">
                                            <div class="card gallery-item h-100" onclick="viewMedia(<?php echo $item['id']; ?>)">
                                                <div class="position-relative">
                                                    <?php if ($item['media_type'] === 'video'): ?>
                                                        <video class="card-img-top" style="height: 150px; object-fit: cover;">
                                                            <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                                        </video>
                                                        <div class="position-absolute top-50 start-50 translate-middle">
                                                            <i class="bi bi-play-circle-fill text-white fs-1"></i>
                                                        </div>
                                                    <?php else: ?>
                                                        <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path']); ?>"
                                                             class="card-img-top" alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title']); ?>"
                                                             style="height: 150px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['category_name'])): ?>
                                                        <span class="badge bg-primary position-absolute top-0 end-0 m-2">
                                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-1 small"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d', strtotime($item['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="text-center text-muted py-4">
                                            <i class="bi bi-cloud-upload fs-1 mb-3"></i>
                                            <p>No recent uploads</p>
                                            <a href="/admin/gallery/upload" class="btn btn-primary btn-sm">Upload Media</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Popular Items</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="popularItemsGrid">
                                <?php if (!empty($popular_items)): ?>
                                    <?php foreach ($popular_items as $item): ?>
                                        <div class="col-md-6">
                                            <div class="card gallery-item h-100" onclick="viewMedia(<?php echo $item['id']; ?>)">
                                                <div class="position-relative">
                                                    <?php if ($item['media_type'] === 'video'): ?>
                                                        <video class="card-img-top" style="height: 150px; object-fit: cover;">
                                                            <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                                        </video>
                                                        <div class="position-absolute top-50 start-50 translate-middle">
                                                            <i class="bi bi-play-circle-fill text-white fs-1"></i>
                                                        </div>
                                                    <?php else: ?>
                                                        <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path']); ?>"
                                                             class="card-img-top" alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title']); ?>"
                                                             style="height: 150px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-75 text-white p-1 small">
                                                        <i class="bi bi-eye me-1"></i><?php echo number_format($item['view_count'] ?? 0); ?>
                                                    </div>
                                                </div>
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-1 small"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-heart me-1"></i>Popular
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="text-center text-muted py-4">
                                            <i class="bi bi-star fs-1 mb-3"></i>
                                            <p>No popular items yet</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Overview -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
                            <a href="/admin/gallery/categories" class="btn btn-primary btn-sm">
                                <i class="bi bi-folder-plus me-1"></i>Manage Categories
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="categoriesGrid">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="card h-100 border-left-primary">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-folder fs-1 text-primary mb-3"></i>
                                                    <h6 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <p class="card-text text-muted small">
                                                        <?php echo number_format($category['media_count'] ?? 0); ?> items
                                                    </p>
                                                    <a href="/admin/gallery/media?category_id=<?php echo $category['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                        View Media
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="text-center text-muted py-4">
                                            <i class="bi bi-folder fs-1 mb-3"></i>
                                            <p>No categories found</p>
                                            <a href="/admin/gallery/categories" class="btn btn-primary btn-sm">Create Categories</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
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

        // View media item
        function viewMedia(id) {
            window.location.href = '/admin/gallery/edit/' + id;
        }

        // Bulk delete selected items (placeholder for future implementation)
        function bulkDeleteSelected() {
            alert('Bulk delete functionality will be implemented with media selection');
        }
    </script>
</body>
</html>