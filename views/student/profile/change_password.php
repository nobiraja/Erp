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
        .password-card {
            transition: transform 0.2s ease-in-out;
        }
        .password-card:hover {
            transform: translateY(-2px);
        }
        .password-strength {
            height: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .password-strength.weak {
            background-color: #dc3545;
            width: 33%;
        }
        .password-strength.medium {
            background-color: #ffc107;
            width: 66%;
        }
        .password-strength.strong {
            background-color: #198754;
            width: 100%;
        }
        .password-requirements {
            font-size: 0.875rem;
        }
        .password-requirements .valid {
            color: #198754;
        }
        .password-requirements .invalid {
            color: #dc3545;
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
                            Student
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile/edit"><i class="bi bi-pencil me-2"></i>Edit Profile</a></li>
                            <li><a class="dropdown-item active" href="/student/profile/change_password"><i class="bi bi-key me-2"></i>Change Password</a></li>
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
                        <h2 class="mb-1">Change Password</h2>
                        <p class="text-muted mb-0">Update your account password for better security</p>
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

        <div class="row justify-content-center">
            <!-- Change Password Form -->
            <div class="col-lg-6 mb-4">
                <div class="card password-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-key me-2"></i>Change Password
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="change-password-form">
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password"
                                           placeholder="Enter your current password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="bi bi-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                           placeholder="Enter your new password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="bi bi-eye" id="new_password_icon"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-1" id="password-strength"></div>
                                <div class="invalid-feedback">Please enter a valid new password.</div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           placeholder="Confirm your new password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_password_icon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>

                            <!-- Password Requirements -->
                            <div class="mb-3">
                                <label class="form-label">Password Requirements:</label>
                                <div class="password-requirements">
                                    <div id="req-length" class="invalid">
                                        <i class="bi bi-x-circle me-1"></i> At least 8 characters long
                                    </div>
                                    <div id="req-match" class="invalid">
                                        <i class="bi bi-x-circle me-1"></i> New password and confirmation must match
                                    </div>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div class="alert alert-info">
                                <i class="bi bi-shield-check me-2"></i>
                                <strong>Security Tip:</strong> Choose a strong password with a mix of uppercase, lowercase, numbers, and special characters.
                                Avoid using personal information or common words.
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-warning" id="change-btn">
                                    <i class="bi bi-key me-1"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Information -->
            <div class="col-lg-4 mb-4">
                <div class="card password-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i>Password Security
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Why change your password regularly?</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Protects your account from unauthorized access
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Prevents data breaches and identity theft
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Maintains privacy of your academic records
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Ensures secure access to school systems
                            </li>
                        </ul>

                        <hr>

                        <h6>Last Password Change:</h6>
                        <p class="text-muted small">Not available</p>

                        <div class="alert alert-light">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Your password will expire in 90 days. Regular changes help maintain account security.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card password-card mt-3">
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
                            <a href="/student/profile/edit" class="btn btn-outline-secondary">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </a>
                            <a href="/student/dashboard" class="btn btn-outline-info">
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
        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '_icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength');

            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                return;
            }

            let score = 0;

            // Length check
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;

            // Character variety
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            // Update strength bar
            if (score < 3) {
                strengthBar.className = 'password-strength weak';
            } else if (score < 5) {
                strengthBar.className = 'password-strength medium';
            } else {
                strengthBar.className = 'password-strength strong';
            }
        }

        // Update password requirements
        function updatePasswordRequirements() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Length requirement
            const lengthReq = document.getElementById('req-length');
            if (newPassword.length >= 8) {
                lengthReq.className = 'valid';
                lengthReq.innerHTML = '<i class="bi bi-check-circle me-1"></i> At least 8 characters long';
            } else {
                lengthReq.className = 'invalid';
                lengthReq.innerHTML = '<i class="bi bi-x-circle me-1"></i> At least 8 characters long';
            }

            // Match requirement
            const matchReq = document.getElementById('req-match');
            if (newPassword && confirmPassword && newPassword === confirmPassword) {
                matchReq.className = 'valid';
                matchReq.innerHTML = '<i class="bi bi-check-circle me-1"></i> New password and confirmation must match';
            } else {
                matchReq.className = 'invalid';
                matchReq.innerHTML = '<i class="bi bi-x-circle me-1"></i> New password and confirmation must match';
            }
        }

        // Form validation and submission
        document.getElementById('change-password-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Clear previous messages
            clearMessages();

            // Validate form
            if (!validateForm()) {
                return;
            }

            // Show loading state
            const changeBtn = document.getElementById('change-btn');
            const originalText = changeBtn.innerHTML;
            changeBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Changing...';
            changeBtn.disabled = true;

            // Prepare form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('/student/profile/update_password', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message || 'Password changed successfully!');
                    // Reset form after success
                    setTimeout(() => {
                        resetForm();
                    }, 1500);
                } else {
                    showMessage('danger', data.message || 'Failed to change password. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('danger', 'An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button state
                changeBtn.innerHTML = originalText;
                changeBtn.disabled = false;
            });
        });

        // Form validation
        function validateForm() {
            let isValid = true;

            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Check required fields
            if (!currentPassword) {
                document.getElementById('current_password').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('current_password').classList.remove('is-invalid');
            }

            if (!newPassword || newPassword.length < 8) {
                document.getElementById('new_password').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('new_password').classList.remove('is-invalid');
            }

            if (!confirmPassword || newPassword !== confirmPassword) {
                document.getElementById('confirm_password').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('confirm_password').classList.remove('is-invalid');
            }

            return isValid;
        }

        // Reset form
        function resetForm() {
            document.getElementById('change-password-form').reset();
            clearMessages();
            // Remove validation classes
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            // Reset password strength
            document.getElementById('password-strength').className = 'password-strength';
            // Reset requirements
            updatePasswordRequirements();
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

        // Event listeners for real-time validation
        document.getElementById('new_password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            updatePasswordRequirements();
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            updatePasswordRequirements();
        });

        document.getElementById('current_password').addEventListener('input', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePasswordRequirements();
            console.log('Change password page loaded');
        });
    </script>
</body>
</html>