<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Add New Student'); ?></title>
    <meta name="description" content="Add new student to the school management system">

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
        .form-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section h5 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .photo-preview {
            width: 120px;
            height: 120px;
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            margin-bottom: 1rem;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.375rem;
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
                    <a class="nav-link active" href="/admin/students">
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
                        <h5 class="mb-0">Add New Student</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/students">Students</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add New</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="/admin/students" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Students
                    </a>
                </div>
            </div>
        </header>

        <!-- Student Form Content -->
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

            <form action="/admin/students" method="POST" enctype="multipart/form-data" id="studentForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h5><i class="bi bi-person me-2"></i>Basic Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="scholar_number" class="form-label">Scholar Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['scholar_number']) ? 'is-invalid' : ''; ?>"
                                   id="scholar_number" name="scholar_number"
                                   value="<?php echo htmlspecialchars($old_input['scholar_number'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['scholar_number'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['scholar_number'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="admission_number" class="form-label">Admission Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['admission_number']) ? 'is-invalid' : ''; ?>"
                                   id="admission_number" name="admission_number"
                                   value="<?php echo htmlspecialchars($old_input['admission_number'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['admission_number'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['admission_number'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="admission_date" class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?php echo isset($validation_errors['admission_date']) ? 'is-invalid' : ''; ?>"
                                   id="admission_date" name="admission_date"
                                   value="<?php echo htmlspecialchars($old_input['admission_date'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['admission_date'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['admission_date'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['first_name']) ? 'is-invalid' : ''; ?>"
                                   id="first_name" name="first_name"
                                   value="<?php echo htmlspecialchars($old_input['first_name'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['first_name'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['first_name'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name"
                                   value="<?php echo htmlspecialchars($old_input['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($validation_errors['last_name']) ? 'is-invalid' : ''; ?>"
                                   id="last_name" name="last_name"
                                   value="<?php echo htmlspecialchars($old_input['last_name'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['last_name'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['last_name'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo isset($validation_errors['class_id']) ? 'is-invalid' : ''; ?>"
                                    id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo ($old_input['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section'] . ' (' . $class['academic_year'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($validation_errors['class_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['class_id'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo isset($validation_errors['section']) ? 'is-invalid' : ''; ?>"
                                    id="section" name="section" required>
                                <option value="">Select Section</option>
                                <option value="A" <?php echo ($old_input['section'] ?? '') === 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo ($old_input['section'] ?? '') === 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo ($old_input['section'] ?? '') === 'C' ? 'selected' : ''; ?>>C</option>
                            </select>
                            <?php if (isset($validation_errors['section'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['section'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?php echo isset($validation_errors['dob']) ? 'is-invalid' : ''; ?>"
                                   id="dob" name="dob"
                                   value="<?php echo htmlspecialchars($old_input['dob'] ?? ''); ?>" required>
                            <?php if (isset($validation_errors['dob'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['dob'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo isset($validation_errors['gender']) ? 'is-invalid' : ''; ?>"
                                    id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($old_input['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($old_input['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($old_input['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (isset($validation_errors['gender'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['gender'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Family Information -->
                <div class="form-section">
                    <h5><i class="bi bi-house me-2"></i>Family Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="father_name" class="form-label">Father's Name</label>
                            <input type="text" class="form-control" id="father_name" name="father_name"
                                   value="<?php echo htmlspecialchars($old_input['father_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mother_name" class="form-label">Mother's Name</label>
                            <input type="text" class="form-control" id="mother_name" name="mother_name"
                                   value="<?php echo htmlspecialchars($old_input['mother_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guardian_name" class="form-label">Guardian's Name</label>
                            <input type="text" class="form-control" id="guardian_name" name="guardian_name"
                                   value="<?php echo htmlspecialchars($old_input['guardian_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="guardian_contact" class="form-label">Guardian's Contact Number</label>
                            <input type="text" class="form-control" id="guardian_contact" name="guardian_contact"
                                   value="<?php echo htmlspecialchars($old_input['guardian_contact'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="form-section">
                    <h5><i class="bi bi-geo-alt me-2"></i>Address Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="village_address" class="form-label">Village Address</label>
                            <textarea class="form-control" id="village_address" name="village_address" rows="2"><?php echo htmlspecialchars($old_input['village_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="permanent_address" class="form-label">Permanent Address</label>
                            <textarea class="form-control" id="permanent_address" name="permanent_address" rows="2"><?php echo htmlspecialchars($old_input['permanent_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="temporary_address" class="form-label">Temporary Address</label>
                            <textarea class="form-control" id="temporary_address" name="temporary_address" rows="2"><?php echo htmlspecialchars($old_input['temporary_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact & Identification -->
                <div class="form-section">
                    <h5><i class="bi bi-telephone me-2"></i>Contact & Identification</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile" name="mobile"
                                   value="<?php echo htmlspecialchars($old_input['mobile'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control <?php echo isset($validation_errors['email']) ? 'is-invalid' : ''; ?>"
                                   id="email" name="email"
                                   value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>">
                            <?php if (isset($validation_errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($validation_errors['email'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="aadhar" class="form-label">Aadhar Number</label>
                            <input type="text" class="form-control" id="aadhar" name="aadhar"
                                   value="<?php echo htmlspecialchars($old_input['aadhar'] ?? ''); ?>" maxlength="12">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="samagra" class="form-label">Samagra Number</label>
                            <input type="text" class="form-control" id="samagra" name="samagra"
                                   value="<?php echo htmlspecialchars($old_input['samagra'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="apaar_id" class="form-label">Apaar ID</label>
                            <input type="text" class="form-control" id="apaar_id" name="apaar_id"
                                   value="<?php echo htmlspecialchars($old_input['apaar_id'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pan" class="form-label">PAN Number</label>
                            <input type="text" class="form-control" id="pan" name="pan"
                                   value="<?php echo htmlspecialchars($old_input['pan'] ?? ''); ?>" maxlength="10">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h5><i class="bi bi-info-circle me-2"></i>Additional Information</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="caste_category" class="form-label">Caste/Category</label>
                            <input type="text" class="form-control" id="caste_category" name="caste_category"
                                   value="<?php echo htmlspecialchars($old_input['caste_category'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="nationality" name="nationality"
                                   value="<?php echo htmlspecialchars($old_input['nationality'] ?? 'Indian'); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="religion" name="religion"
                                   value="<?php echo htmlspecialchars($old_input['religion'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="blood_group" class="form-label">Blood Group</label>
                            <select class="form-select" id="blood_group" name="blood_group">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?php echo ($old_input['blood_group'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($old_input['blood_group'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($old_input['blood_group'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($old_input['blood_group'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo ($old_input['blood_group'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($old_input['blood_group'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo ($old_input['blood_group'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($old_input['blood_group'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="previous_school" class="form-label">Previous School</label>
                            <input type="text" class="form-control" id="previous_school" name="previous_school"
                                   value="<?php echo htmlspecialchars($old_input['previous_school'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="medical_conditions" class="form-label">Medical Conditions</label>
                            <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="2"><?php echo htmlspecialchars($old_input['medical_conditions'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Student Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
                            <div class="form-text">Accepted formats: JPG, PNG. Max size: 2MB</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo Preview</label>
                            <div class="photo-preview" id="photoPreview">
                                <i class="bi bi-person text-muted fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="/admin/students" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <div>
                        <button type="reset" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Student
                        </button>
                    </div>
                </div>
            </form>
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

            // Auto-generate scholar number
            generateScholarNumber();
        });

        // Preview photo before upload
        function previewPhoto(input) {
            const preview = document.getElementById('photoPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Photo Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<i class="bi bi-person text-muted fs-1"></i>';
            }
        }

        // Auto-generate scholar number
        function generateScholarNumber() {
            const scholarInput = document.getElementById('scholar_number');
            if (!scholarInput.value) {
                const timestamp = Date.now();
                const random = Math.floor(Math.random() * 1000);
                scholarInput.value = `SCH${timestamp}${random}`;
            }
        }

        // Auto-generate admission number
        document.getElementById('admission_date').addEventListener('change', function() {
            const admissionInput = document.getElementById('admission_number');
            if (!admissionInput.value) {
                const date = new Date(this.value);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const random = Math.floor(Math.random() * 1000);
                admissionInput.value = `ADM${year}${month}${random}`;
            }
        });
    </script>
</body>
</html>