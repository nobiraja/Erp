<?php
/**
 * Teacher Study Materials and Assignments View
 * Allows teachers to upload and manage study materials and assignments
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Study Materials'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .material-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .material-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .file-icon {
            font-size: 2rem;
            color: #6c757d;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        .upload-area.dragover {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        .material-type-study { border-left: 4px solid #28a745; }
        .material-type-assignment { border-left: 4px solid #ffc107; }
        .material-type-exam { border-left: 4px solid #dc3545; }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/teacher/dashboard">
                <i class="fas fa-school"></i> School Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/attendance">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/teacher/classes">
                            <i class="fas fa-chalkboard"></i> Classes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/exams">
                            <i class="fas fa-file-alt"></i> Exams
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Teacher'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/teacher/profile"><i class="fas fa-user-edit"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chalkboard"></i> Class Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/teacher/classes" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> View Classes
                            </a>
                            <a href="/teacher/classes/subjects" class="btn btn-outline-success">
                                <i class="fas fa-book"></i> Manage Subjects
                            </a>
                            <a href="/teacher/classes/timetable?class_id=<?php echo $class->id; ?>" class="btn btn-outline-info">
                                <i class="fas fa-calendar-alt"></i> View Timetable
                            </a>
                            <a href="/teacher/classes/materials?class_id=<?php echo $class->id; ?>" class="btn btn-outline-warning active">
                                <i class="fas fa-upload"></i> Study Materials
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Class Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Class Information</h6>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></h5>
                        <p class="text-muted mb-2">Academic Year: <?php echo htmlspecialchars($class->academic_year); ?></p>
                        <div class="mb-3">
                            <strong>My Subjects:</strong>
                            <div class="mt-2">
                                <?php foreach ($assignedSubjects as $subject): ?>
                                    <span class="badge bg-primary me-1 mb-1"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Upload Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-success">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <div><strong><?php echo count($materials); ?></strong></div>
                                    <div class="small">Materials</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-warning">
                                    <i class="fas fa-tasks fa-2x mb-2"></i>
                                    <div><strong>0</strong></div>
                                    <div class="small">Assignments</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-info">
                                    <i class="fas fa-download fa-2x mb-2"></i>
                                    <div><strong>0</strong></div>
                                    <div class="small">Downloads</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-upload"></i> Study Materials & Assignments</h2>
                        <p class="text-muted"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?> - Upload and manage resources</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="showUploadModal()">
                            <i class="fas fa-plus"></i> Upload Material
                        </button>
                    </div>
                </div>

                <!-- Materials List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Uploaded Materials</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($materials)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                <h4>No materials uploaded yet</h4>
                                <p class="text-muted">Start by uploading study materials or assignments for your class.</p>
                                <button class="btn btn-primary" onclick="showUploadModal()">
                                    <i class="fas fa-plus"></i> Upload First Material
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="materialsTable" class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Type</th>
                                            <th>Title</th>
                                            <th>Subject</th>
                                            <th>File</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Materials would be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Upload Cards -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card material-card border-success" onclick="showUploadModal('study')">
                            <div class="card-body text-center">
                                <i class="fas fa-book text-success fa-3x mb-3"></i>
                                <h5>Study Material</h5>
                                <p class="text-muted">Upload notes, presentations, or reference materials</p>
                                <button class="btn btn-outline-success">
                                    <i class="fas fa-plus"></i> Upload
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card material-card border-warning" onclick="showUploadModal('assignment')">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks text-warning fa-3x mb-3"></i>
                                <h5>Assignment</h5>
                                <p class="text-muted">Create and upload homework assignments</p>
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-plus"></i> Create
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card material-card border-info" onclick="showUploadModal('exam')">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt text-info fa-3x mb-3"></i>
                                <h5>Exam Paper</h5>
                                <p class="text-muted">Upload practice tests or question papers</p>
                                <button class="btn btn-outline-info">
                                    <i class="fas fa-plus"></i> Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload"></i> Upload Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="materialType" class="form-label">Material Type *</label>
                                <select class="form-select" id="materialType" name="material_type" required>
                                    <option value="">Select type...</option>
                                    <option value="study">Study Material</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="exam">Exam Paper</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-select" id="subject" name="subject_id" required>
                                    <option value="">Select subject...</option>
                                    <?php foreach ($assignedSubjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>">
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="file" class="form-label">File *</label>
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Drag & drop files here</h5>
                                    <p class="text-muted">or <span class="text-primary" style="cursor: pointer;" onclick="document.getElementById('file').click()">browse files</span></p>
                                    <input type="file" class="d-none" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif" required>
                                    <div class="mt-2">
                                        <small class="text-muted">Supported formats: PDF, DOC, PPT, XLS, TXT, Images (Max: 10MB)</small>
                                    </div>
                                </div>
                                <div id="fileInfo" class="mt-2 d-none">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> <span id="fileName"></span>
                                        (<span id="fileSize"></span>)
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" id="assignmentFields" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="dueDate" class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="dueDate" name="due_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="maxMarks" class="form-label">Maximum Marks</label>
                                        <input type="number" class="form-control" id="maxMarks" name="max_marks" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="uploadMaterial()">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable if materials exist
            if ($('#materialsTable').length) {
                $('#materialsTable').DataTable({
                    "pageLength": 10,
                    "order": [[4, "desc"]],
                    "responsive": true
                });
            }

            // Material type change handler
            $('#materialType').change(function() {
                const type = $(this).val();
                if (type === 'assignment') {
                    $('#assignmentFields').show();
                    $('#dueDate').attr('required', true);
                } else {
                    $('#assignmentFields').hide();
                    $('#dueDate').removeAttr('required');
                }
            });

            // File upload handling
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('file');

            uploadArea.addEventListener('click', () => fileInput.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    displayFileInfo(files[0]);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    displayFileInfo(e.target.files[0]);
                }
            });
        });

        function showUploadModal(type = '') {
            if (type) {
                $('#materialType').val(type).trigger('change');
            }
            $('#uploadModal').modal('show');
        }

        function displayFileInfo(file) {
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('d-none');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function uploadMaterial() {
            const form = document.getElementById('uploadForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            formData.append('class_id', '<?php echo $class->id; ?>');

            // Show loading state
            const uploadBtn = document.querySelector('#uploadModal .btn-primary');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadBtn.disabled = true;

            $.ajax({
                url: '/teacher/classes/upload-material',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#uploadModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Upload failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Upload failed. Please try again.');
                },
                complete: function() {
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>