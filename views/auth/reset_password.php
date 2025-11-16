<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - School Management System</title>
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
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .reset-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #ee5a24 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .reset-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #FF6B6B;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }
        .btn-reset {
            background: linear-gradient(135deg, #FF6B6B 0%, #ee5a24 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
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
            color: #FF6B6B;
        }
        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }
        .back-to-login a {
            color: #FF6B6B;
            text-decoration: none;
            font-weight: 500;
        }
        .back-to-login a:hover {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="reset-container">
                    <div class="reset-header">
                        <div class="school-logo">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <h2 class="mb-0">Reset Password</h2>
                        <p class="mb-0 opacity-75">Enter your new password</p>
                    </div>

                    <div class="reset-body">
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

                        <form id="reset-form" method="POST" action="/reset-password">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>New Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Enter new password" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Password must be at least 8 characters long.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                           placeholder="Confirm new password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-reset w-100" id="reset-btn">
                                <span id="reset-text">Reset Password</span>
                                <div class="spinner-border spinner-border-sm loading-spinner ms-2" role="status" style="display: none;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </form>

                        <div class="back-to-login">
                            <a href="/login">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
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
                const confirmField = $('#password_confirm');
                const icon = $(this).find('i');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    confirmField.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    confirmField.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Form validation
            $('#reset-form').submit(function(e) {
                e.preventDefault();

                // Hide previous messages
                $('#error-message').hide();
                $('#success-message').hide();

                // Show loading state
                $('#reset-btn').prop('disabled', true);
                $('#reset-text').text('Resetting...');
                $('#reset-btn .loading-spinner').show();

                // Validate form
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    $('#reset-btn').prop('disabled', false);
                    $('#reset-text').text('Reset Password');
                    $('#reset-btn .loading-spinner').hide();
                    return;
                }

                // Check if passwords match
                const password = $('#password').val();
                const confirmPassword = $('#password_confirm').val();

                if (password !== confirmPassword) {
                    $('#error-text').text('Passwords do not match.');
                    $('#error-message').show();
                    $('#reset-btn').prop('disabled', false);
                    $('#reset-text').text('Reset Password');
                    $('#reset-btn .loading-spinner').hide();
                    return;
                }

                // Submit form
                this.submit();
            });

            // Show flash messages
            <?php if (isset($_SESSION['flash']['error'])): ?>
                $('#error-text').text('<?php echo addslashes($_SESSION['flash']['error']); ?>');
                $('#error-message').show();
                <?php unset($_SESSION['flash']['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash']['success'])): ?>
                $('#success-text').text('<?php echo addslashes($_SESSION['flash']['success']); ?>');
                $('#success-message').show();
                <?php unset($_SESSION['flash']['success']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>