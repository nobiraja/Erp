<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Upload Media'); ?></title>
    <meta name="description" content="Upload photos and videos to the gallery">

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
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #f8f9fa;
        }
        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: #007bff;
            background: #e7f3ff;
        }
        .upload-zone.dragover {
            border-color: #28a745;
            background: #e8f5e8;
        }
        .file-preview {
            position: relative;
            display: inline-block;
            margin: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            overflow: hidden;
            background: white;
        }
        .file-preview img,
        .file-preview video {
            width: 120px;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        .file-preview .file-info {
            padding: 0.5rem;
            font-size: 0.75rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .file-preview .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress {
            height: 6px;
        }
        .upload-progress {
            display: none;
            margin-top: 1rem;
        }
        .upload-results {
            display: none;
            margin-top: 1rem;
        }
        .result-item {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }
        .result-item.success {
            background: #d1edff;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .result-item.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
                        <h5 class="mb-0">Upload Media</h5>
                        <small class="text-muted">Add photos and videos to your gallery</small>
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

        <!-- Upload Content -->
        <main class="p-4">
            <!-- Upload Type Tabs -->
            <ul class="nav nav-tabs" id="uploadTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">Single Upload</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">Bulk Upload</button>
                </li>
            </ul>

            <div class="tab-content mt-4" id="uploadTabsContent">
                <!-- Single Upload Tab -->
                <div class="tab-pane fade show active" id="single" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Single File</h6>
                                </div>
                                <div class="card-body">
                                    <form id="singleUploadForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">

                                        <div class="mb-3">
                                            <label for="singleFile" class="form-label">Select File</label>
                                            <input type="file" class="form-control" id="singleFile" name="media_file" accept="image/*,video/*" required>
                                            <div class="form-text">Supported formats: JPG, PNG, GIF, WebP, MP4, AVI, MOV, WMV, FLV, WebM</div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="singleTitle" class="form-label">Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="singleTitle" name="title" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="singleCategory" class="form-label">Category</label>
                                                    <select class="form-select" id="singleCategory" name="category_id">
                                                        <option value="">Select Category</option>
                                                        <?php foreach ($categories as $category): ?>
                                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="singleDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="singleDescription" name="description" rows="3"></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="singleTags" class="form-label">Tags</label>
                                                    <input type="text" class="form-control" id="singleTags" name="tags" placeholder="Separate with commas">
                                                    <div class="form-text">e.g., school, event, sports</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="singleAltText" class="form-label">Alt Text</label>
                                                    <input type="text" class="form-control" id="singleAltText" name="alt_text" placeholder="Describe the image for accessibility">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="singleFeatured" name="is_featured">
                                                <label class="form-check-label" for="singleFeatured">
                                                    Mark as featured
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary" id="singleUploadBtn">
                                            <i class="bi bi-cloud-upload me-1"></i>Upload Media
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Upload Guidelines</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Supported Formats:</h6>
                                    <ul class="mb-3">
                                        <li><strong>Images:</strong> JPG, PNG, GIF, WebP</li>
                                        <li><strong>Videos:</strong> MP4, AVI, MOV, WMV, FLV, WebM</li>
                                    </ul>

                                    <h6>File Size Limits:</h6>
                                    <ul class="mb-3">
                                        <li><strong>Images:</strong> Max 10MB per file</li>
                                        <li><strong>Videos:</strong> Max 100MB per file</li>
                                    </ul>

                                    <h6>Best Practices:</h6>
                                    <ul>
                                        <li>Use descriptive titles and alt text</li>
                                        <li>Choose appropriate categories</li>
                                        <li>Add relevant tags for better search</li>
                                        <li>Compress large files before upload</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Upload Tab -->
                <div class="tab-pane fade" id="bulk" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Bulk Upload</h6>
                                </div>
                                <div class="card-body">
                                    <form id="bulkUploadForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">

                                        <div class="mb-3">
                                            <label for="bulkCategory" class="form-label">Category for All Files</label>
                                            <select class="form-select" id="bulkCategory" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">All uploaded files will be assigned to this category</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="bulkFeatured" name="is_featured">
                                                <label class="form-check-label" for="bulkFeatured">
                                                    Mark all files as featured
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Drag and Drop Zone -->
                                        <div class="upload-zone mb-3" id="uploadZone">
                                            <div id="uploadZoneContent">
                                                <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                                                <h5>Drag & Drop Files Here</h5>
                                                <p class="text-muted">or <span class="text-primary" style="cursor: pointer;" onclick="document.getElementById('bulkFiles').click()">browse files</span></p>
                                                <small class="text-muted">Supported: Images (JPG, PNG, GIF, WebP) and Videos (MP4, AVI, MOV, WMV, FLV, WebM)</small>
                                            </div>
                                            <input type="file" id="bulkFiles" name="media_files[]" multiple accept="image/*,video/*" style="display: none;">
                                        </div>

                                        <!-- File Preview Area -->
                                        <div id="filePreview" class="mb-3" style="display: none;">
                                            <h6>Selected Files:</h6>
                                            <div id="previewContainer"></div>
                                        </div>

                                        <button type="submit" class="btn btn-primary" id="bulkUploadBtn" disabled>
                                            <i class="bi bi-cloud-upload me-1"></i>Upload All Files
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="clearFiles()">
                                            <i class="bi bi-x-circle me-1"></i>Clear All
                                        </button>
                                    </form>

                                    <!-- Upload Progress -->
                                    <div class="upload-progress" id="uploadProgress">
                                        <h6>Uploading Files...</h6>
                                        <div class="progress mb-3">
                                            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                                        </div>
                                        <div id="progressText">Preparing files...</div>
                                    </div>

                                    <!-- Upload Results -->
                                    <div class="upload-results" id="uploadResults">
                                        <h6>Upload Results:</h6>
                                        <div id="resultsContainer"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Bulk Upload Tips</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Quick Upload:</h6>
                                    <ul class="mb-3">
                                        <li>Select multiple files at once</li>
                                        <li>Drag and drop files into the upload zone</li>
                                        <li>All files get the same category and settings</li>
                                    </ul>

                                    <h6>File Naming:</h6>
                                    <ul class="mb-3">
                                        <li>Use descriptive filenames</li>
                                        <li>Files are automatically titled from filenames</li>
                                        <li>You can edit titles later in media management</li>
                                    </ul>

                                    <h6>Performance:</h6>
                                    <ul>
                                        <li>Upload in batches of 10-20 files</li>
                                        <li>Larger files may take longer to process</li>
                                        <li>Check upload progress in real-time</li>
                                    </ul>
                                </div>
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
        let selectedFiles = [];
        let uploadZone, fileInput, previewContainer;

        document.addEventListener('DOMContentLoaded', function() {
            setupSidebar();
            setupSingleUpload();
            setupBulkUpload();
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

        function setupSingleUpload() {
            const form = document.getElementById('singleUploadForm');
            const submitBtn = document.getElementById('singleUploadBtn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitSingleUpload();
            });
        }

        function setupBulkUpload() {
            uploadZone = document.getElementById('uploadZone');
            fileInput = document.getElementById('bulkFiles');
            previewContainer = document.getElementById('previewContainer');

            // Drag and drop events
            uploadZone.addEventListener('dragover', handleDragOver);
            uploadZone.addEventListener('dragleave', handleDragLeave);
            uploadZone.addEventListener('drop', handleDrop);
            uploadZone.addEventListener('click', () => fileInput.click());

            // File input change
            fileInput.addEventListener('change', handleFileSelect);

            // Form submission
            document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitBulkUpload();
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (validateFile(file)) {
                    selectedFiles.push(file);
                }
            }
            updateFilePreview();
        }

        function validateFile(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/webm'];
            const maxSize = file.type.startsWith('video/') ? 100 * 1024 * 1024 : 10 * 1024 * 1024; // 100MB for videos, 10MB for images

            if (!allowedTypes.includes(file.type)) {
                alert(`File "${file.name}" has unsupported format.`);
                return false;
            }

            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large. Maximum size is ${maxSize / (1024 * 1024)}MB.`);
                return false;
            }

            return true;
        }

        function updateFilePreview() {
            previewContainer.innerHTML = '';
            const filePreview = document.getElementById('filePreview');

            if (selectedFiles.length > 0) {
                filePreview.style.display = 'block';

                selectedFiles.forEach((file, index) => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'file-preview';

                    let mediaElement = '';
                    if (file.type.startsWith('image/')) {
                        mediaElement = `<img src="${URL.createObjectURL(file)}" alt="${file.name}">`;
                    } else if (file.type.startsWith('video/')) {
                        mediaElement = `<video><source src="${URL.createObjectURL(file)}" type="${file.type}"></video>`;
                    }

                    previewDiv.innerHTML = `
                        ${mediaElement}
                        <div class="file-info">
                            <div class="fw-bold">${file.name}</div>
                            <small>${(file.size / 1024).toFixed(1)} KB</small>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    `;

                    previewContainer.appendChild(previewDiv);
                });

                document.getElementById('bulkUploadBtn').disabled = false;
            } else {
                filePreview.style.display = 'none';
                document.getElementById('bulkUploadBtn').disabled = true;
            }
        }

        function removeFile(index) {
            URL.revokeObjectURL(selectedFiles[index]);
            selectedFiles.splice(index, 1);
            updateFilePreview();
        }

        function clearFiles() {
            selectedFiles.forEach(file => URL.revokeObjectURL(file));
            selectedFiles = [];
            fileInput.value = '';
            updateFilePreview();
        }

        function submitSingleUpload() {
            const form = document.getElementById('singleUploadForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('singleUploadBtn');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass me-1"></i>Uploading...';

            fetch('/admin/gallery/uploadSingle', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Media uploaded successfully!');
                    form.reset();
                } else {
                    alert('Upload failed: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during upload');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Upload Media';
            });
        }

        function submitBulkUpload() {
            if (selectedFiles.length === 0) return;

            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $this->generateCsrfToken(); ?>');
            formData.append('category_id', document.getElementById('bulkCategory').value);
            formData.append('is_featured', document.getElementById('bulkFeatured').checked ? '1' : '0');

            selectedFiles.forEach(file => {
                formData.append('media_files[]', file);
            });

            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const resultsDiv = document.getElementById('uploadResults');
            const resultsContainer = document.getElementById('resultsContainer');
            const submitBtn = document.getElementById('bulkUploadBtn');

            progressDiv.style.display = 'block';
            resultsDiv.style.display = 'none';
            submitBtn.disabled = true;

            fetch('/admin/gallery/uploadBulk', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                progressDiv.style.display = 'none';
                resultsDiv.style.display = 'block';

                if (result.success) {
                    resultsContainer.innerHTML = '<div class="alert alert-success">Bulk upload completed successfully!</div>';

                    result.results.forEach(item => {
                        const resultDiv = document.createElement('div');
                        resultDiv.className = `result-item ${item.success ? 'success' : 'error'}`;
                        resultDiv.innerHTML = `
                            <strong>${item.file}</strong>: ${item.success ? 'Uploaded successfully' : (item.message || 'Upload failed')}
                        `;
                        resultsContainer.appendChild(resultDiv);
                    });

                    // Clear files on success
                    clearFiles();
                } else {
                    resultsContainer.innerHTML = `<div class="alert alert-danger">${result.message || 'Bulk upload failed'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                progressDiv.style.display = 'none';
                resultsDiv.style.display = 'block';
                resultsContainer.innerHTML = '<div class="alert alert-danger">An error occurred during bulk upload</div>';
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        }
    </script>
</body>
</html>