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
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .password-strength {
            height: 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .password-strength-weak { background-color: #dc3545; width: 33%; }
        .password-strength-medium { background-color: #ffc107; width: 66%; }
        .password-strength-strong { background-color: #28a745; width: 100%; }
        .tab-content {
            padding: 2rem 0;
        }
        .activity-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .status-active { color: #28a745; }
        .status-inactive { color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-house-heart-fill me-2"></i>
                Parent Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/children">
                            <i class="bi bi-people me-1"></i>My Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/events">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>Parent
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="/parent/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">My Profile</h2>
                <p class="text-muted mb-0">Manage your account information and preferences</p>
            </div>
        </div>

        <!-- Profile Overview -->
        <div class="row mb-4">
            <div class="col-lg-4 mb-4">
                <div class="card profile-card h-100">
                    <div class="card-body text-center">
                        <img src="<?php echo htmlspecialchars($parent['photo_path'] ?? '/assets/images/default-avatar.png'); ?>"
                             alt="Profile Photo" class="profile-avatar mb-3">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars(($parent['first_name'] ?? '') . ' ' . ($parent['last_name'] ?? '')); ?>
                        </h5>
                        <p class="text-muted mb-2">Parent</p>
                        <span class="badge bg-success">
                            <i class="bi bi-circle-fill me-1"></i>Active
                        </span>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="changePhoto()">
                                <i class="bi bi-camera me-1"></i>Change Photo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card profile-card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Full Name:</strong> <?php echo htmlspecialchars(($parent['first_name'] ?? '') . ' ' . ($parent['middle_name'] ?? '') . ' ' . ($parent['last_name'] ?? '')); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($parent['email'] ?? 'N/A'); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($parent['mobile'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Relationship:</strong> <?php echo htmlspecialchars($parent['relationship_to_student'] ?? 'Parent'); ?></p>
                                <p><strong>Occupation:</strong> <?php echo htmlspecialchars($parent['occupation'] ?? 'N/A'); ?></p>
                                <p><strong>Member Since:</strong> <?php echo isset($parent['created_at']) ? date('M d, Y', strtotime($parent['created_at'])) : 'N/A'; ?></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="editProfile()">
                                <i class="bi bi-pencil me-1"></i>Edit Profile
                            </button>
                            <button class="btn btn-outline-secondary ms-2" onclick="changePassword()">
                                <i class="bi bi-key me-1"></i>Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Management Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person me-1"></i>Personal Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                    <i class="bi bi-shield me-1"></i>Security
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                    <i class="bi bi-activity me-1"></i>Activity Log
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="profileTabsContent">
                            <!-- Personal Information Tab -->
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <form id="personalInfoForm">
                                    <div class="form-section">
                                        <h5 class="mb-3">Basic Information</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" value="<?php echo htmlspecialchars($parent['first_name'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="middleName" class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" id="middleName" value="<?php echo htmlspecialchars($parent['middle_name'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" value="<?php echo htmlspecialchars($parent['last_name'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($parent['email'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="mobile" class="form-label">Mobile Number</label>
                                                <input type="tel" class="form-control" id="mobile" value="<?php echo htmlspecialchars($parent['mobile'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h5 class="mb-3">Additional Information</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="occupation" class="form-label">Occupation</label>
                                                <input type="text" class="form-control" id="occupation" value="<?php echo htmlspecialchars($parent['occupation'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="relationship" class="form-label">Relationship to Student</label>
                                                <select class="form-select" id="relationship">
                                                    <option value="Parent" <?php echo ($parent['relationship_to_student'] ?? '') === 'Parent' ? 'selected' : ''; ?>>Parent</option>
                                                    <option value="Guardian" <?php echo ($parent['relationship_to_student'] ?? '') === 'Guardian' ? 'selected' : ''; ?>>Guardian</option>
                                                    <option value="Other" <?php echo ($parent['relationship_to_student'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="temporaryAddress" class="form-label">Temporary Address</label>
                                                <textarea class="form-control" id="temporaryAddress" rows="3"><?php echo htmlspecialchars($parent['temporary_address'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
                                        <button type="button" class="btn btn-primary ms-2" onclick="saveProfile()">
                                            <i class="bi bi-check me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <div class="form-section">
                                    <h5 class="mb-3">Change Password</h5>
                                    <form id="passwordChangeForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="currentPassword" class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="currentPassword" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="newPassword" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="newPassword" required onkeyup="checkPasswordStrength()">
                                                <div class="password-strength mt-1" id="passwordStrength"></div>
                                                <small class="text-muted">Password must be at least 8 characters long</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirmPassword" required>
                                                <div id="passwordMatch" class="mt-1"></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-primary" onclick="updatePassword()">
                                                <i class="bi bi-key me-1"></i>Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="form-section">
                                    <h5 class="mb-3">Account Security</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Two-Factor Authentication</h6>
                                                    <p class="text-muted small">Add an extra layer of security to your account</p>
                                                    <button class="btn btn-outline-primary btn-sm" onclick="setup2FA()">
                                                        <i class="bi bi-shield-check me-1"></i>Setup 2FA
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Login Sessions</h6>
                                                    <p class="text-muted small">Manage your active login sessions</p>
                                                    <button class="btn btn-outline-secondary btn-sm" onclick="viewSessions()">
                                                        <i class="bi bi-pc-display me-1"></i>View Sessions
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Log Tab -->
                            <div class="tab-pane fade" id="activity" role="tabpanel">
                                <div class="form-section">
                                    <h5 class="mb-3">Recent Activity</h5>
                                    <div id="activityLog">
                                        <?php if (!empty($activity_logs)): ?>
                                            <?php foreach ($activity_logs as $log): ?>
                                                <div class="activity-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($log['description']); ?></small>
                                                        </div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($log['time_ago']); ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-4">
                                                <i class="bi bi-activity fs-1 mb-2"></i>
                                                <p>No activity recorded yet</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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

    <!-- AJAX Functions -->
    <script>
        // Edit profile
        function editProfile() {
            document.getElementById('personal-tab').click();
        }

        // Cancel edit
        function cancelEdit() {
            // Reset form to original values
            location.reload();
        }

        // Save profile
        function saveProfile() {
            const formData = {
                first_name: document.getElementById('firstName').value,
                middle_name: document.getElementById('middleName').value,
                last_name: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                mobile: document.getElementById('mobile').value,
                occupation: document.getElementById('occupation').value,
                relationship_to_student: document.getElementById('relationship').value,
                temporary_address: document.getElementById('temporaryAddress').value
            };

            fetch('/parent/profile/updateProfile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating profile: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating profile');
            });
        }

        // Change password
        function changePassword() {
            document.getElementById('security-tab').click();
        }

        // Update password
        function updatePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }

            if (newPassword.length < 8) {
                alert('New password must be at least 8 characters long');
                return;
            }

            fetch('/parent/profile/changePassword', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password changed successfully!');
                    document.getElementById('passwordChangeForm').reset();
                } else {
                    alert('Error changing password: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error changing password');
            });
        }

        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthIndicator = document.getElementById('passwordStrength');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            strengthIndicator.className = 'password-strength mt-1';

            if (strength <= 2) {
                strengthIndicator.classList.add('password-strength-weak');
            } else if (strength <= 3) {
                strengthIndicator.classList.add('password-strength-medium');
            } else {
                strengthIndicator.classList.add('password-strength-strong');
            }
        }

        // Change photo
        function changePhoto() {
            // Create file input
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/gif';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size too large. Maximum size is 5MB');
                        return;
                    }

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid file type. Only JPEG, PNG, and GIF are allowed');
                        return;
                    }

                    // Upload the file
                    const formData = new FormData();
                    formData.append('photo', file);

                    fetch('/parent/profile/uploadPhoto', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Photo uploaded successfully!');
                            // Update the profile image
                            const img = document.querySelector('.profile-avatar');
                            if (img) {
                                img.src = data.photo_path;
                            }
                            location.reload(); // Reload to show updated photo
                        } else {
                            alert('Error uploading photo: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error uploading photo');
                    });
                }
            };
            input.click();
        }

        // Setup 2FA
        function setup2FA() {
            alert('Two-factor authentication setup would be implemented here');
        }

        // View sessions
        function viewSessions() {
            alert('Session management would be implemented here');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add password confirmation validation
            document.getElementById('confirmPassword').addEventListener('keyup', function() {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = this.value;
                const matchIndicator = document.getElementById('passwordMatch');

                if (confirmPassword === '') {
                    matchIndicator.innerHTML = '';
                } else if (newPassword === confirmPassword) {
                    matchIndicator.innerHTML = '<small class="text-success">Passwords match</small>';
                } else {
                    matchIndicator.innerHTML = '<small class="text-danger">Passwords do not match</small>';
                }
            });
        });
    </script>
</body>
</html>