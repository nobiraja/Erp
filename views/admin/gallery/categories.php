<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Gallery Categories'); ?></title>
    <meta name="description" content="Manage gallery categories for school management system">

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
        .category-item {
            transition: all 0.3s;
        }
        .category-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .category-tree {
            position: relative;
        }
        .category-tree::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .category-level-1 { padding-left: 30px; }
        .category-level-2 { padding-left: 60px; }
        .category-level-3 { padding-left: 90px; }
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
                        <h5 class="mb-0">Gallery Categories</h5>
                        <small class="text-muted">Manage photo and video categories</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary me-2" onclick="showCreateCategoryModal()">
                        <i class="bi bi-plus-circle me-1"></i>Add Category
                    </button>
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

        <!-- Categories Content -->
        <main class="p-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/gallery">Gallery</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>

            <!-- Categories List -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-folder me-2"></i>All Categories</h6>
                </div>
                <div class="card-body">
                    <div id="categoriesContainer">
                        <?php if (!empty($categories)): ?>
                            <div class="category-tree">
                                <?php
                                function renderCategoryTree($categories, $parentId = null, $level = 0) {
                                    $html = '';
                                    foreach ($categories as $category) {
                                        if ($category['parent_id'] == $parentId) {
                                            $children = array_filter($categories, function($c) use ($category) {
                                                return $c['parent_id'] == $category['id'];
                                            });

                                            $hasChildren = !empty($children);
                                            $levelClass = 'category-level-' . min($level + 1, 3);

                                            $html .= '<div class="card category-item mb-3 ' . $levelClass . '" data-category-id="' . $category['id'] . '">';
                                            $html .= '<div class="card-body">';
                                            $html .= '<div class="d-flex justify-content-between align-items-center">';
                                            $html .= '<div class="d-flex align-items-center">';
                                            $html .= '<i class="bi bi-folder fs-4 text-primary me-3"></i>';
                                            $html .= '<div>';
                                            $html .= '<h6 class="mb-1">' . htmlspecialchars($category['name']) . '</h6>';
                                            $html .= '<small class="text-muted">Slug: ' . htmlspecialchars($category['slug']) . '</small>';
                                            if (!empty($category['description'])) {
                                                $html .= '<br><small class="text-muted">' . htmlspecialchars($category['description']) . '</small>';
                                            }
                                            $html .= '</div>';
                                            $html .= '</div>';
                                            $html .= '<div class="d-flex align-items-center">';
                                            $html .= '<span class="badge bg-info me-3">' . number_format($category['media_count'] ?? 0) . ' items</span>';
                                            $html .= '<div class="btn-group" role="group">';
                                            $html .= '<button class="btn btn-sm btn-outline-primary" onclick="editCategory(' . $category['id'] . ')" title="Edit">';
                                            $html .= '<i class="bi bi-pencil"></i>';
                                            $html .= '</button>';
                                            $html .= '<button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(' . $category['id'] . ', \'' . htmlspecialchars($category['name']) . '\')" title="Delete" ' . ($hasChildren || ($category['media_count'] ?? 0) > 0 ? 'disabled' : '') . '>';
                                            $html .= '<i class="bi bi-trash"></i>';
                                            $html .= '</button>';
                                            $html .= '</div>';
                                            $html .= '</div>';
                                            $html .= '</div>';
                                            $html .= '</div>';
                                            $html .= '</div>';

                                            if ($hasChildren) {
                                                $html .= renderCategoryTree($categories, $category['id'], $level + 1);
                                            }
                                        }
                                    }
                                    return $html;
                                }

                                echo renderCategoryTree($categories);
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Categories Found</h5>
                                <p class="text-muted">Create your first category to organize your gallery media.</p>
                                <button class="btn btn-primary" onclick="showCreateCategoryModal()">
                                    <i class="bi bi-plus-circle me-1"></i>Create First Category
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Create Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" id="categoryId" name="category_id">

                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                            <div class="invalid-feedback">
                                Please provide a category name.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="categorySlug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="categorySlug" name="slug" readonly>
                            <div class="form-text">Auto-generated from category name</div>
                        </div>

                        <div class="mb-3">
                            <label for="parentCategory" class="form-label">Parent Category</label>
                            <select class="form-select" id="parentCategory" name="parent_id">
                                <option value="">No Parent (Top Level)</option>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveCategoryBtn">
                            <i class="bi bi-check-circle me-1"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<strong id="deleteCategoryName"></strong>"?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action cannot be undone. All media in this category will become uncategorized.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let categories = <?php echo json_encode($categories ?? []); ?>;
        let categoryModal, deleteModal, categoryForm;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modals
            categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            categoryForm = document.getElementById('categoryForm');

            // Sidebar functionality
            setupSidebar();

            // Form handling
            setupFormHandling();

            // Populate parent category options
            populateParentCategories();
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

        function setupFormHandling() {
            // Auto-generate slug from name
            document.getElementById('categoryName').addEventListener('input', function() {
                const name = this.value;
                const slug = name.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim('-');
                document.getElementById('categorySlug').value = slug;
            });

            // Form submission
            categoryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveCategory();
            });
        }

        function populateParentCategories() {
            const select = document.getElementById('parentCategory');
            select.innerHTML = '<option value="">No Parent (Top Level)</option>';

            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }

        function showCreateCategoryModal() {
            document.getElementById('categoryModalLabel').textContent = 'Create Category';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categorySlug').value = '';
            document.getElementById('parentCategory').value = '';
            document.getElementById('categoryDescription').value = '';

            categoryModal.show();
        }

        function editCategory(id) {
            const category = categories.find(c => c.id == id);
            if (!category) return;

            document.getElementById('categoryModalLabel').textContent = 'Edit Category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categorySlug').value = category.slug;
            document.getElementById('parentCategory').value = category.parent_id || '';
            document.getElementById('categoryDescription').value = category.description || '';

            categoryModal.show();
        }

        function saveCategory() {
            const formData = new FormData(categoryForm);
            const categoryId = formData.get('category_id');
            const isEdit = categoryId !== '';

            const url = isEdit ? `/admin/gallery/updateCategory/${categoryId}` : '/admin/gallery/createCategory';
            const method = isEdit ? 'PUT' : 'POST';

            // Convert FormData to JSON
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    categoryModal.hide();
                    location.reload(); // Refresh to show changes
                } else {
                    alert('Error: ' + (result.message || 'Failed to save category'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the category');
            });
        }

        function deleteCategory(id, name) {
            document.getElementById('deleteCategoryName').textContent = name;
            document.getElementById('confirmDeleteBtn').onclick = function() {
                performDelete(id);
            };
            deleteModal.show();
        }

        function performDelete(id) {
            fetch(`/admin/gallery/deleteCategory/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    deleteModal.hide();
                    location.reload(); // Refresh to show changes
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete category'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the category');
            });
        }
    </script>
</body>
</html>