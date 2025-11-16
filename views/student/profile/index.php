<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .profile-card {
            transition: transform 0.2s ease-in-out;
        }
        .profile-card:hover {
            transform: translateY(-2px);
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .subject-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .academic-stats {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .guardian-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Student Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/student/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/student/profile">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($student['first_name'] ?? 'Student'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile/edit"><i class="bi bi-pencil me-2"></i>Edit Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile/change_password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">My Profile</h2>
                        <p class="text-muted mb-0">View and manage your personal and academic information</p>
                    </div>
                    <div>
                        <a href="/student/profile/edit" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>Edit Profile
                        </a>
                        <a href="/student/profile/change_password" class="btn btn-outline-secondary">
                            <i class="bi bi-key me-1"></i>Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Personal Information -->
            <div class="col-lg-8 mb-4">
                <div class="card profile-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-circle me-2"></i>Personal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">Full Name:</label>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Scholar Number:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['scholar_number'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Admission Number:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['admission_number'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Date of Birth:</label>
                                    <div class="info-value">
                                        <?php echo $student['dob'] ? date('d-m-Y', strtotime($student['dob'])) : ''; ?>
                                        (<?php echo $student['dob'] ? $student->getAge() . ' years old' : ''; ?>)
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Gender:</label>
                                    <div class="info-value"><?php echo htmlspecialchars(ucfirst($student['gender'] ?? '')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">Class & Section:</label>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars(($student['class_name'] ?? '') . ' - ' . ($student['section'] ?? '')); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Admission Date:</label>
                                    <div class="info-value">
                                        <?php echo $student['admission_date'] ? date('d-m-Y', strtotime($student['admission_date'])) : ''; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Blood Group:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['blood_group'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Caste/Category:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['caste_category'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Nationality:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['nationality'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Photo & Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card profile-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-image me-2"></i>Profile Photo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($student['photo_path'])): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Profile Photo" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        <p class="text-muted small">Profile photo helps in identification</p>
                    </div>
                </div>

                <!-- Academic Stats -->
                <div class="card profile-card mt-3">
                    <div class="card-header academic-stats">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Academic Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 mb-1"><?php echo $academic_info['attendance']['percentage']; ?>%</div>
                                <small class="text-muted">Attendance</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-1">₹<?php echo number_format($academic_info['fee_status']['total_fees']); ?></div>
                                <small class="text-muted">Total Fees</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 mb-1"><?php echo $academic_info['attendance']['present_days']; ?>/<?php echo $academic_info['attendance']['total_days']; ?></div>
                                <small class="text-muted">Present Days</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-1">₹<?php echo number_format($academic_info['fee_status']['paid_fees']); ?></div>
                                <small class="text-muted">Paid</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-6 mb-4">
                <div class="card profile-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-telephone me-2"></i>Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="info-label">Mobile Number:</label>
                            <div class="info-value"><?php echo htmlspecialchars($student['mobile'] ?? ''); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="info-label">Email Address:</label>
                            <div class="info-value"><?php echo htmlspecialchars($student['email'] ?? ''); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="info-label">Permanent Address:</label>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($student['permanent_address'] ?? '')); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="info-label">Temporary Address:</label>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($student['temporary_address'] ?? '')); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="info-label">Village:</label>
                            <div class="info-value"><?php echo htmlspecialchars($student['village_address'] ?? ''); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardian Information -->
            <div class="col-lg-6 mb-4">
                <div class="card profile-card">
                    <div class="card-header guardian-info">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2"></i>Guardian Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">Father's Name:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['father_name'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Mother's Name:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['mother_name'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">Guardian Name:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['guardian_name'] ?? ''); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label">Guardian Contact:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['guardian_contact'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Class Subjects -->
            <div class="col-lg-6 mb-4">
                <div class="card profile-card">
                    <div class="card-header subject-card">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-book me-2"></i>Class Subjects
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($class_subjects)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-book-x" style="font-size: 3rem;"></i>
                                <p class="mt-2">No subjects assigned yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($class_subjects as $subject): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 border-primary">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($subject['subject_name'] ?? ''); ?></h6>
                                                <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars($subject['subject_code'] ?? ''); ?></p>
                                                <?php if (!empty($subject['first_name'])): ?>
                                                    <small class="text-primary">
                                                        <i class="bi bi-person me-1"></i>
                                                        <?php echo htmlspecialchars(($subject['first_name'] ?? '') . ' ' . ($subject['last_name'] ?? '')); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Academic Performance -->
            <div class="col-lg-6 mb-4">
                <div class="card profile-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-data me-2"></i>Recent Academic Performance
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($academic_info['recent_results'])): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clipboard-x" style="font-size: 3rem;"></i>
                                <p class="mt-2">No recent results available.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($academic_info['recent_results'], 0, 5) as $result): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($result['subject_name'] ?? ''); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($result['exam_name'] ?? ''); ?> -
                                                    <?php echo date('M d, Y', strtotime($result['created_at'] ?? '')); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($result['marks_obtained'] ?? 0); ?>/<?php echo htmlspecialchars($result['max_marks'] ?? 0); ?>
                                                </span>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($result['grade'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="/student/results" class="btn btn-outline-primary btn-sm">View All Results</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="row">
            <div class="col-12">
                <div class="card profile-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Additional Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="info-label">Religion:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['religion'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="info-label">Aadhaar Number:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['aadhar'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="info-label">Samagra ID:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['samagra'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="info-label">Previous School:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['previous_school'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">Medical Conditions:</label>
                                    <div class="info-value"><?php echo nl2br(htmlspecialchars($student['medical_conditions'] ?? 'None')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="info-label">PAN Number:</label>
                                    <div class="info-value"><?php echo htmlspecialchars($student['pan'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Student profile page loaded');
        });
    </script>
</body>
</html>