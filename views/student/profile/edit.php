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
        .form-card {
            transition: transform 0.2s ease-in-out;
        }
        .form-card:hover {
            transform: translateY(-2px);
        }
        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .editable-field {
            border-color: #0d6efd;
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
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item active" href="/student/profile/edit"><i class="bi bi-pencil me-2"></i>Edit Profile</a></li>
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
                        <h2 class="mb-1">Edit Profile</h2>
                        <p class="text-muted mb-0">Update your contact information and other editable details</p>
                    </div>
                    <div>
                        <a href="/student/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div id="message-container"></div>

        <div class="row">
            <!-- Edit Form -->
            <div class="col-lg-8 mb-4">
                <div class="card form-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="profile-edit-form">
                            <!-- Contact Information -->
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-2"></i>Contact Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control editable-field" id="mobile" name="mobile"
                                           value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>"
                                           placeholder="Enter mobile number">
                                    <div class="invalid-feedback">Please enter a valid mobile number.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control editable-field" id="email" name="email"
                                           value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>"
                                           placeholder="Enter email address">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <h6 class="text-primary mb-3 mt-4">
                                <i class="bi bi-geo-alt me-2"></i>Address Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="temporary_address" class="form-label">Temporary Address</label>
                                    <textarea class="form-control editable-field" id="temporary_address" name="temporary_address"
                                              rows="3" placeholder="Enter temporary address"><?php echo htmlspecialchars($student['temporary_address'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="medical_conditions" class="form-label">Medical Conditions</label>
                                    <textarea class="form-control editable-field" id="medical_conditions" name="medical_conditions"
                                              rows="3" placeholder="Enter any medical conditions or allergies"><?php echo htmlspecialchars($student['medical_conditions'] ?? ''); ?></textarea>
                                    <div class="form-text">Leave empty if none</div>
                                </div>
                            </div>

                            <!-- Read-only Information Notice -->
                            <div class="alert alert-info mt-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Personal details like name, date of birth, and academic information cannot be edited.
                                Contact your school administration for any changes to these fields.
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="save-btn">
                                    <i class="bi bi-check-circle me-1"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Current Information Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card form-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-circle me-2"></i>Current Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name:</label>
                            <p class="mb-1"><?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Scholar Number:</label>
                            <p class="mb-1"><?php echo htmlspecialchars($student['scholar_number'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Class & Section:</label>
                            <p class="mb-1"><?php echo htmlspecialchars(($student['class_name'] ?? '') . ' - ' . ($student['section'] ?? '')); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date of Birth:</label>
                            <p class="mb-1"><?php echo $student['dob'] ? date('d-m-Y', strtotime($student['dob'])) : ''; ?></p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Permanent Address:</label>
                            <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($student['permanent_address'] ?? '')); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Father's Name:</label>
                            <p class="mb-1"><?php echo htmlspecialchars($student['father_name'] ?? ''); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mother's Name:</label>
                            <p class="mb-1"><?php echo htmlspecialchars($student['mother_name'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card form-card mt-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/student/profile" class="btn btn-outline-primary">
                                <i class="bi bi-person me-2"></i>View Profile
                            </a>
                            <a href="/student/profile/change_password" class="btn btn-outline-warning">
                                <i class="bi bi-key me-2"></i>Change Password
                            </a>
                            <a href="/student/dashboard" class="btn btn-outline-secondary">
                                <i class="bi bi-house-door me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Form validation and submission
        document.getElementById('profile-edit-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Clear previous messages
            clearMessages();

            // Validate form
            if (!validateForm()) {
                return;
            }

            // Show loading state
            const saveBtn = document.getElementById('save-btn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
            saveBtn.disabled = true;

            // Prepare form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('/student/profile/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message || 'Profile updated successfully!');
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = '/student/profile';
                    }, 1500);
                } else {
                    showMessage('danger', data.message || 'Failed to update profile. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('danger', 'An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button state
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        });

        // Form validation
        function validateForm() {
            let isValid = true;

            // Validate mobile
            const mobile = document.getElementById('mobile').value.trim();
            const mobileRegex = /^[0-9+\-\s()]+$/;
            if (!mobile || !mobileRegex.test(mobile)) {
                document.getElementById('mobile').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('mobile').classList.remove('is-invalid');
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                document.getElementById('email').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('email').classList.remove('is-invalid');
            }

            return isValid;
        }

        // Reset form
        function resetForm() {
            document.getElementById('profile-edit-form').reset();
            clearMessages();
            // Remove validation classes
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }

        // Show message
        function showMessage(type, message) {
            const container = document.getElementById('message-container');
            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        // Clear messages
        function clearMessages() {
            document.getElementById('message-container').innerHTML = '';
        }

        // Real-time validation
        document.getElementById('mobile').addEventListener('input', function() {
            const mobileRegex = /^[0-9+\-\s()]+$/;
            if (this.value && !mobileRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('email').addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Student profile edit page loaded');
        });
    </script>
</body>
</html>