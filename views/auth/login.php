<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .school-logo i {
            font-size: 2rem;
            color: #4CAF50;
        }
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        .forgot-password a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .input-group-text {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        .form-check-input:checked {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        .loading-spinner {
            display: none;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <div class="school-logo">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h2 class="mb-0">Welcome Back</h2>
                        <p class="mb-0 opacity-75">School Management System</p>
                    </div>

                    <div class="login-body">
                        <!-- Error Messages -->
                        <div id="error-message" class="alert alert-danger" style="display: none;" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span id="error-text"></span>
                        </div>

                        <!-- Success Messages -->
                        <div id="success-message" class="alert alert-success" style="display: none;" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <span id="success-text"></span>
                        </div>

                        <form id="login-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $this->security->getCSRFToken(); ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person-fill me-1"></i>Username or Email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username"
                                           placeholder="Enter your username or email" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your username or email.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    <i class="bi bi-check-circle me-1"></i>Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-login w-100" id="login-btn">
                                <span id="login-text">Sign In</span>
                                <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </form>

                        <div class="forgot-password">
                            <a href="#" id="forgot-password-link">
                                <i class="bi bi-question-circle me-1"></i>Forgot your password?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="bi bi-key me-2"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgot-error-message" class="alert alert-danger" style="display: none;" role="alert">
                        <span id="forgot-error-text"></span>
                    </div>
                    <div id="forgot-success-message" class="alert alert-success" style="display: none;" role="alert">
                        <span id="forgot-success-text"></span>
                    </div>

                    <form id="forgot-password-form">
                        <div class="mb-3">
                            <label for="reset-email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="reset-email" name="email"
                                       placeholder="Enter your email address" required>
                            </div>
                            <div class="form-text">
                                We'll send a password reset link to your email.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="reset-btn">
                            <span id="reset-text">Send Reset Link</span>
                            <div class="spinner-border spinner-border-sm loading-spinner ms-2" role="status" style="display: none;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#toggle-password').click(function() {
                const passwordField = $('#password');
                const icon = $(this).find('i');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Forgot password modal
            $('#forgot-password-link').click(function(e) {
                e.preventDefault();
                $('#forgotPasswordModal').modal('show');
            });

            // Login form submission
            $('#login-form').submit(function(e) {
                e.preventDefault();

                // Hide previous messages
                $('#error-message').hide();
                $('#success-message').hide();

                // Show loading state
                $('#login-btn').prop('disabled', true);
                $('#login-text').text('Signing in...');
                $('#login-btn .loading-spinner').show();

                // Validate form
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    $('#login-btn').prop('disabled', false);
                    $('#login-text').text('Sign In');
                    $('#login-btn .loading-spinner').hide();
                    return;
                }

                const formData = new FormData(this);

                // AJAX login request
                $.ajax({
                    url: '/api/v1/auth/login',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#success-text').text(response.message || 'Login successful! Redirecting...');
                            $('#success-message').show();

                            // Redirect after success
                            setTimeout(function() {
                                window.location.href = response.redirect || '/dashboard';
                            }, 1500);
                        } else {
                            $('#error-text').text(response.message || 'Login failed. Please try again.');
                            $('#error-message').show();
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $('#error-text').text(message);
                        $('#error-message').show();
                    },
                    complete: function() {
                        // Reset loading state
                        $('#login-btn').prop('disabled', false);
                        $('#login-text').text('Sign In');
                        $('#login-btn .loading-spinner').hide();
                    }
                });
            });

            // Forgot password form submission
            $('#forgot-password-form').submit(function(e) {
                e.preventDefault();

                // Hide previous messages
                $('#forgot-error-message').hide();
                $('#forgot-success-message').hide();

                // Show loading state
                $('#reset-btn').prop('disabled', true);
                $('#reset-text').text('Sending...');
                $('#reset-btn .loading-spinner').show();

                const formData = new FormData(this);

                // AJAX forgot password request
                $.ajax({
                    url: '/api/v1/auth/forgot-password',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#forgot-success-text').text(response.message || 'Password reset link sent to your email.');
                            $('#forgot-success-message').show();
                            $('#forgot-password-form')[0].reset();
                        } else {
                            $('#forgot-error-text').text(response.message || 'Failed to send reset link. Please try again.');
                            $('#forgot-error-message').show();
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $('#forgot-error-text').text(message);
                        $('#forgot-error-message').show();
                    },
                    complete: function() {
                        // Reset loading state
                        $('#reset-btn').prop('disabled', false);
                        $('#reset-text').text('Send Reset Link');
                        $('#reset-btn .loading-spinner').hide();
                    }
                });
            });

            // Check for URL parameters (redirect after login)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('redirect')) {
                $('#login-form').append('<input type="hidden" name="redirect" value="' + urlParams.get('redirect') + '">');
            }
        });
    </script>
</body>
</html>