<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Edit Media'); ?></title>
    <meta name="description" content="Edit gallery media item">

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
        .media-preview {
            max-width: 100%;
            height: 300px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
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
                        <h5 class="mb-0">Edit Media</h5>
                        <small class="text-muted">Modify media details and settings</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="/admin/gallery/media" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Back to Media
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

        <!-- Edit Content -->
        <main class="p-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Media Details</h6>
                        </div>
                        <div class="card-body">
                            <form id="editMediaForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">

                                <!-- Media Preview -->
                                <div class="mb-4 text-center">
                                    <?php if ($item['media_type'] === 'video'): ?>
                                        <video class="media-preview" controls>
                                            <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path']); ?>"
                                             alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title']); ?>"
                                             class="media-preview">
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mediaTitle" class="form-label">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="mediaTitle" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mediaCategory" class="form-label">Category</label>
                                            <select class="form-select" id="mediaCategory" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo ($item['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="mediaDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="mediaDescription" name="description" rows="3"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mediaTags" class="form-label">Tags</label>
                                            <input type="text" class="form-control" id="mediaTags" name="tags" value="<?php echo htmlspecialchars($item['tags'] ?? ''); ?>" placeholder="Separate with commas">
                                            <div class="form-text">e.g., school, event, sports</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mediaAltText" class="form-label">Alt Text</label>
                                            <input type="text" class="form-control" id="mediaAltText" name="alt_text" value="<?php echo htmlspecialchars($item['alt_text'] ?? ''); ?>" placeholder="Describe the image for accessibility">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="displayOrder" class="form-label">Display Order</label>
                                            <input type="number" class="form-control" id="displayOrder" name="display_order" value="<?php echo htmlspecialchars($item['display_order'] ?? 0); ?>" min="0">
                                            <div class="form-text">Lower numbers appear first</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="isFeatured" name="is_featured" <?php echo ($item['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="isFeatured">
                                                    Mark as featured
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Replace File Option -->
                                <div class="mb-3">
                                    <label for="replaceFile" class="form-label">Replace Media File (Optional)</label>
                                    <input type="file" class="form-control" id="replaceFile" name="media_file" accept="image/*,video/*">
                                    <div class="form-text">Leave empty to keep current file. Supported formats: JPG, PNG, GIF, WebP, MP4, AVI, MOV, WMV, FLV, WebM</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" id="saveBtn">
                                        <i class="bi bi-check-circle me-1"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteMedia()">
                                        <i class="bi bi-trash me-1"></i>Delete Media
                                    </button>
                                    <a href="/admin/gallery/media" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Media Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><?php echo strtoupper($item['media_type']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Size:</strong></td>
                                    <td><?php echo $item['file_size'] ? number_format($item['file_size'] / 1024, 1) . ' KB' : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>MIME Type:</strong></td>
                                    <td><?php echo htmlspecialchars($item['mime_type'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded:</strong></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($item['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded by:</strong></td>
                                    <td><?php echo htmlspecialchars($item['uploaded_by_name'] ?? 'Unknown'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Views:</strong></td>
                                    <td><?php echo number_format($item['view_count'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Downloads:</strong></td>
                                    <td><?php echo number_format($item['download_count'] ?? 0); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-info btn-sm" onclick="viewPublic()">
                                    <i class="bi bi-eye me-1"></i>View Public Page
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="duplicateMedia()">
                                    <i class="bi bi-copy me-1"></i>Duplicate Media
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="downloadOriginal()">
                                    <i class="bi bi-download me-1"></i>Download Original
                                </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            setupSidebar();
            setupForm();
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

        function setupForm() {
            const form = document.getElementById('editMediaForm');
            const saveBtn = document.getElementById('saveBtn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                saveChanges();
            });
        }

        function saveChanges() {
            const form = document.getElementById('editMediaForm');
            const formData = new FormData(form);
            const saveBtn = document.getElementById('saveBtn');

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="bi bi-hourglass me-1"></i>Saving...';

            fetch('/admin/gallery/update/<?php echo $item['id']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Media updated successfully!');
                    window.location.href = '/admin/gallery/media';
                } else {
                    alert('Update failed: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during update');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Changes';
            });
        }

        function deleteMedia() {
            if (confirm('Are you sure you want to delete this media item? This action cannot be undone.')) {
                fetch('/admin/gallery/delete/<?php echo $item['id']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $this->generateCsrfToken(); ?>'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Media deleted successfully!');
                        window.location.href = '/admin/gallery/media';
                    } else {
                        alert('Delete failed: ' + (result.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during deletion');
                });
            }
        }

        function viewPublic() {
            window.open('/gallery', '_blank');
        }

        function duplicateMedia() {
            if (confirm('Create a duplicate of this media item?')) {
                // This would need a duplicate endpoint in the controller
                alert('Duplicate functionality would be implemented here');
            }
        }

        function downloadOriginal() {
            const link = document.createElement('a');
            link.href = '<?php echo htmlspecialchars($item['image_path'] ?? $item['video_path']); ?>';
            link.download = '<?php echo htmlspecialchars($item['title']); ?>' + '<?php echo $item['media_type'] === 'video' ? '.mp4' : '.jpg'; ?>';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>