<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Contact Us'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Get in touch with us'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'contact, school contact'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'Contact Us'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Get in touch with us'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/contact'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-contact {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .contact-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .department-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        @media (max-width: 768px) {
            .hero-contact {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .contact-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="/images/logo.png" alt="<?php echo htmlspecialchars($school_name ?? 'School Logo'); ?>" class="me-2" onerror="this.style.display='none'">
                <span class="fw-bold"><?php echo htmlspecialchars($school_name ?? 'School'); ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admission">Admission</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-contact">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                    <p class="lead mb-4">We'd love to hear from you. Get in touch with our team for any questions, concerns, or inquiries about our school.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-telephone display-6 me-3"></i>
                            <div>
                                <div class="fw-bold">Call Us</div>
                                <div><?php echo htmlspecialchars($contact_info['phone'] ?? '+1 (555) 123-4567'); ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope display-6 me-3"></i>
                            <div>
                                <div class="fw-bold">Email Us</div>
                                <div><?php echo htmlspecialchars($contact_info['email'] ?? 'info@school.com'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <img src="/images/contact-hero.jpg" alt="Contact Us" class="img-fluid rounded shadow" onerror="this.src='/images/default-contact.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Success/Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="container mt-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contact Form and Info Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="contact-form">
                        <h3 class="mb-4">Send us a Message</h3>
                        <form action="/contact/submit" method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select class="form-control" id="subject" name="subject" required>
                                        <option value="">Select Subject</option>
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Admissions">Admissions</option>
                                        <option value="Academic">Academic</option>
                                        <option value="Events">Events</option>
                                        <option value="Support">Support</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="contact-card card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-geo-alt text-primary me-2"></i>Contact Information
                            </h5>
                            <div class="mb-3">
                                <strong>Address:</strong><br>
                                <?php echo htmlspecialchars($contact_info['address'] ?? '123 School Street, City, State 12345'); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Phone:</strong><br>
                                <a href="tel:<?php echo htmlspecialchars($contact_info['phone'] ?? '+15551234567'); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_info['phone'] ?? '+1 (555) 123-4567'); ?>
                                </a>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong><br>
                                <a href="mailto:<?php echo htmlspecialchars($contact_info['email'] ?? 'info@school.com'); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($contact_info['email'] ?? 'info@school.com'); ?>
                                </a>
                            </div>
                            <div class="mb-3">
                                <strong>Working Hours:</strong><br>
                                <?php echo htmlspecialchars($contact_info['working_hours'] ?? 'Mon - Fri: 8:00 AM - 4:00 PM'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="contact-card card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3">Follow Us</h5>
                            <div class="d-flex justify-content-center gap-3">
                                <?php if (!empty($contact_info['social_facebook'])): ?>
                                    <a href="<?php echo htmlspecialchars($contact_info['social_facebook']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($contact_info['social_twitter'])): ?>
                                    <a href="<?php echo htmlspecialchars($contact_info['social_twitter']); ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                        <i class="bi bi-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($contact_info['social_instagram'])): ?>
                                    <a href="<?php echo htmlspecialchars($contact_info['social_instagram']); ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                        <i class="bi bi-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($contact_info['social_youtube'])): ?>
                                    <a href="<?php echo htmlspecialchars($contact_info['social_youtube']); ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                                        <i class="bi bi-youtube"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map and Departments Section -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <!-- Map -->
                <div class="col-lg-8 mb-4">
                    <div class="map-container">
                        <iframe
                            src="<?php echo htmlspecialchars($contact_info['map_location'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.1!2d-73.9!3d40.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1'); ?>"
                            width="100%"
                            height="400"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <!-- Department Contacts -->
                <div class="col-lg-4">
                    <h4 class="mb-4">Department Contacts</h4>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                            <div class="department-card">
                                <h6 class="mb-2"><?php echo htmlspecialchars($dept['department']); ?></h6>
                                <p class="mb-1"><strong><?php echo htmlspecialchars($dept['contact_person']); ?></strong></p>
                                <p class="mb-1">
                                    <i class="bi bi-telephone me-2"></i>
                                    <a href="tel:<?php echo htmlspecialchars($dept['phone']); ?>" class="text-white text-decoration-none">
                                        <?php echo htmlspecialchars($dept['phone']); ?>
                                    </a>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-envelope me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($dept['email']); ?>" class="text-white text-decoration-none">
                                        <?php echo htmlspecialchars($dept['email']); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default departments -->
                        <div class="department-card">
                            <h6 class="mb-2">Administration</h6>
                            <p class="mb-1"><strong>Principal</strong></p>
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                            <p class="mb-0"><i class="bi bi-envelope me-2"></i>principal@school.com</p>
                        </div>
                        <div class="department-card">
                            <h6 class="mb-2">Admissions</h6>
                            <p class="mb-1"><strong>Admissions Officer</strong></p>
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                            <p class="mb-0"><i class="bi bi-envelope me-2"></i>admissions@school.com</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?php echo htmlspecialchars($school_name ?? 'School'); ?></h5>
                    <p>Providing quality education and shaping tomorrow's leaders since <?php echo date('Y') - 25; ?>.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white-50">Home</a></li>
                        <li><a href="/about" class="text-white-50">About Us</a></li>
                        <li><a href="/courses" class="text-white-50">Courses</a></li>
                        <li><a href="/events" class="text-white-50">Events</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Admissions</h6>
                    <ul class="list-unstyled">
                        <li><a href="/admission" class="text-white-50">Apply Now</a></li>
                        <li><a href="/admission/requirements" class="text-white-50">Requirements</a></li>
                        <li><a href="/fees" class="text-white-50">Fee Structure</a></li>
                        <li><a href="/contact" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6>Contact Info</h6>
                    <p class="mb-1"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($contact_info['address'] ?? '123 School Street, City, State 12345'); ?></p>
                    <p class="mb-1"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($contact_info['phone'] ?? '+1 (555) 123-4567'); ?></p>
                    <p class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($contact_info['email'] ?? 'info@school.com'); ?></p>
                    <p class="mb-0"><i class="bi bi-clock me-2"></i><?php echo htmlspecialchars($contact_info['working_hours'] ?? 'Mon - Fri: 8:00 AM - 4:00 PM'); ?></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo $current_year; ?> <?php echo htmlspecialchars($school_name ?? 'School Management System'); ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="/terms" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript for Form Validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');

            contactForm.addEventListener('submit', function(e) {
                // Basic client-side validation
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value.trim();

                if (!name || !email || !subject || !message) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    return false;
                }

                // Show loading state
                const submitBtn = contactForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Sending...';
            });
        });
    </script>
</body>
</html>