<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Gallery Media'); ?></title>
    <meta name="description" content="Manage gallery media items for school management system">

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
        .media-item {
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .media-item.selected {
            border: 3px solid #007bff;
        }
        .media-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }
        .bulk-actions {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            z-index: 1050;
        }
        .bulk-actions.show {
            display: block;
        }
        .view-toggle .btn {
            border-radius: 0;
        }
        .view-toggle .btn:first-child {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }
        .view-toggle .btn:last-child {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
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
                        <h5 class="mb-0">Gallery Media</h5>
                        <small class="text-muted">Manage photos and videos</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="/admin/gallery/upload" class="btn btn-primary me-2">
                        <i class="bi bi-cloud-upload me-1"></i>Upload Media
                    </a>
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

        <!-- Media Content -->
        <main class="p-4">
            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="categoryFilter" class="form-label">Category</label>
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="typeFilter" class="form-label">Media Type</label>
                            <select class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="image" <?php echo ($filters['media_type'] ?? '') === 'image' ? 'selected' : ''; ?>>Images</option>
                                <option value="video" <?php echo ($filters['media_type'] ?? '') === 'video' ? 'selected' : ''; ?>>Videos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="searchInput" class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by title, description, or tags..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">View</label>
                            <div class="view-toggle btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="gridView" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary" for="gridView">
                                    <i class="bi bi-grid"></i>
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="listView">
                                    <i class="bi bi-list"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-outline-primary me-2" onclick="applyFilters()">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bulk-actions" id="bulkActions">
                <div class="d-flex align-items-center p-3">
                    <span id="selectedCount" class="me-3">0 items selected</span>
                    <div class="btn-group me-2">
                        <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete()">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="bulkFeature()">
                            <i class="bi bi-star me-1"></i>Feature
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="bulkUnfeature()">
                            <i class="bi bi-star-fill me-1"></i>Unfeature
                        </button>
                    </div>
                    <div class="input-group" style="width: 200px;">
                        <label class="input-group-text">Move to:</label>
                        <select class="form-select form-select-sm" id="bulkCategorySelect">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary btn-sm" onclick="bulkMoveCategory()">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm ms-2" onclick="clearSelection()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Media Grid/List View -->
            <div id="mediaContainer">
                <?php if (!empty($items)): ?>
                    <!-- Grid View -->
                    <div id="gridViewContainer" class="row g-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card media-item h-100" data-media-id="<?php echo $item['id']; ?>" onclick="toggleSelection(<?php echo $item['id']; ?>, event)">
                                    <div class="position-relative">
                                        <input type="checkbox" class="form-check-input media-checkbox" value="<?php echo $item['id']; ?>" onchange="updateSelection()">
                                        <?php if ($item['media_type'] === 'video'): ?>
                                            <video class="card-img-top" style="height: 200px; object-fit: cover;">
                                                <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                            </video>
                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                <i class="bi bi-play-circle-fill text-white fs-1 bg-dark bg-opacity-50 rounded-circle p-2"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path']); ?>"
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title']); ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        <?php endif; ?>

                                        <!-- Status badges -->
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <?php if ($item['is_featured']): ?>
                                                <span class="badge bg-warning text-dark me-1">
                                                    <i class="bi bi-star-fill"></i>
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge bg-<?php echo $item['media_type'] === 'video' ? 'danger' : 'success'; ?>">
                                                <?php echo strtoupper($item['media_type']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title mb-2"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <p class="card-text small text-muted flex-grow-1">
                                            <?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 100)); ?>
                                            <?php if (strlen($item['description'] ?? '') > 100): ?>...<?php endif; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <small class="text-muted">
                                                <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                            </small>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editMedia(<?php echo $item['id']; ?>, event)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewMedia(<?php echo $item['id']; ?>, event)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteMedia(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>', event)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- List View (Hidden by default) -->
                    <div id="listViewContainer" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()"></th>
                                        <th>Preview</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr data-media-id="<?php echo $item['id']; ?>">
                                            <td><input type="checkbox" class="form-check-input media-checkbox" value="<?php echo $item['id']; ?>" onchange="updateSelection()"></td>
                                            <td>
                                                <?php if ($item['media_type'] === 'video'): ?>
                                                    <video style="width: 60px; height: 40px; object-fit: cover;">
                                                        <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                                    </video>
                                                <?php else: ?>
                                                    <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path']); ?>"
                                                         alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title']); ?>"
                                                         style="width: 60px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $item['media_type'] === 'video' ? 'danger' : 'success'; ?>">
                                                    <?php echo strtoupper($item['media_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $item['file_size'] ? number_format($item['file_size'] / 1024, 1) . ' KB' : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($item['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark">Featured</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editMedia(<?php echo $item['id']; ?>, event)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewMedia(<?php echo $item['id']; ?>, event)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteMedia(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>', event)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Media pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="changePage(<?php echo $pagination['page'] - 1; ?>)">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="#" onclick="changePage(<?php echo $i; ?>)"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="changePage(<?php echo $pagination['page'] + 1; ?>)">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-images fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Media Found</h5>
                        <p class="text-muted">Upload some photos or videos to get started.</p>
                        <a href="/admin/gallery/upload" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-1"></i>Upload Media
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let selectedItems = [];
        let currentFilters = <?php echo json_encode($filters ?? []); ?>;
        let currentPage = <?php echo $pagination['page'] ?? 1; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            setupSidebar();
            setupViewToggle();
            setupFilters();
        });

        function setupSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

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

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
        }

        function setupViewToggle() {
            document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const gridView = document.getElementById('gridViewContainer');
                    const listView = document.getElementById('listViewContainer');

                    if (this.id === 'gridView') {
                        gridView.classList.remove('d-none');
                        listView.classList.add('d-none');
                    } else {
                        gridView.classList.add('d-none');
                        listView.classList.remove('d-none');
                    }
                });
            });
        }

        function setupFilters() {
            // Search on Enter key
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        }

        function applyFilters() {
            const categoryId = document.getElementById('categoryFilter').value;
            const mediaType = document.getElementById('typeFilter').value;
            const search = document.getElementById('searchInput').value.trim();

            const params = new URLSearchParams();
            if (categoryId) params.set('category_id', categoryId);
            if (mediaType) params.set('media_type', mediaType);
            if (search) params.set('search', search);
            params.set('page', '1'); // Reset to first page

            window.location.href = '/admin/gallery/media?' + params.toString();
        }

        function clearFilters() {
            document.getElementById('categoryFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('searchInput').value = '';
            window.location.href = '/admin/gallery/media';
        }

        function changePage(page) {
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            window.location.href = '/admin/gallery/media?' + params.toString();
        }

        function toggleSelection(mediaId, event) {
            if (event && (event.target.tagName === 'BUTTON' || event.target.closest('button'))) {
                return; // Don't toggle if clicking on action buttons
            }

            const checkbox = document.querySelector(`.media-checkbox[value="${mediaId}"]`);
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateSelection();
            }
        }

        function updateSelection() {
            selectedItems = [];
            document.querySelectorAll('.media-checkbox:checked').forEach(checkbox => {
                selectedItems.push(parseInt(checkbox.value));
            });

            // Update visual selection
            document.querySelectorAll('.media-item').forEach(item => {
                const mediaId = parseInt(item.dataset.mediaId);
                if (selectedItems.includes(mediaId)) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            });

            // Show/hide bulk actions
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (selectedItems.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = `${selectedItems.length} item${selectedItems.length > 1 ? 's' : ''} selected`;
            } else {
                bulkActions.classList.remove('show');
            }

            // Update select all checkbox
            const selectAll = document.getElementById('selectAll');
            const allCheckboxes = document.querySelectorAll('.media-checkbox');
            const checkedBoxes = document.querySelectorAll('.media-checkbox:checked');

            if (selectAll) {
                selectAll.checked = allCheckboxes.length > 0 && checkedBoxes.length === allCheckboxes.length;
                selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allCheckboxes.length;
            }
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.media-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            updateSelection();
        }

        function clearSelection() {
            document.querySelectorAll('.media-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelection();
        }

        function editMedia(id, event) {
            if (event) event.stopPropagation();
            window.location.href = `/admin/gallery/edit/${id}`;
        }

        function viewMedia(id, event) {
            if (event) event.stopPropagation();
            // Open in new tab or modal - for now, redirect to edit
            window.open(`/admin/gallery/edit/${id}`, '_blank');
        }

        function deleteMedia(id, title, event) {
            if (event) event.stopPropagation();

            if (confirm(`Are you sure you want to delete "${title}"?`)) {
                fetch(`/admin/gallery/delete/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Failed to delete media'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the media');
                });
            }
        }

        function bulkDelete() {
            if (selectedItems.length === 0) return;

            if (confirm(`Are you sure you want to delete ${selectedItems.length} item${selectedItems.length > 1 ? 's' : ''}?`)) {
                fetch('/admin/gallery/bulkOperation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                    },
                    body: JSON.stringify({
                        operation: 'delete',
                        ids: selectedItems
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Bulk operation failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during bulk operation');
                });
            }
        }

        function bulkFeature() {
            if (selectedItems.length === 0) return;

            fetch('/admin/gallery/bulkOperation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                },
                body: JSON.stringify({
                    operation: 'feature',
                    ids: selectedItems
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Bulk operation failed'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during bulk operation');
            });
        }

        function bulkUnfeature() {
            if (selectedItems.length === 0) return;

            fetch('/admin/gallery/bulkOperation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                },
                body: JSON.stringify({
                    operation: 'unfeature',
                    ids: selectedItems
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Bulk operation failed'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during bulk operation');
            });
        }

        function bulkMoveCategory() {
            if (selectedItems.length === 0) return;

            const categoryId = document.getElementById('bulkCategorySelect').value;
            if (!categoryId) {
                alert('Please select a category');
                return;
            }

            fetch('/admin/gallery/bulkOperation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                },
                body: JSON.stringify({
                    operation: 'move_category',
                    ids: selectedItems,
                    category_id: categoryId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Bulk operation failed'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during bulk operation');
            });
        }
    </script>
</body>
</html>