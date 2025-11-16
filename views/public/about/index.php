<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'About Us'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Learn about our school, history, mission, and faculty'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'school, about, history, mission, faculty'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'About Us'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Learn about our school'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/about'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-about {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .faculty-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .faculty-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .faculty-img {
            height: 250px;
            object-fit: cover;
        }
        .stats-card {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .mission-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
        }
        .history-timeline {
            position: relative;
            padding-left: 30px;
        }
        .history-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #007bff;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
        }
        @media (max-width: 768px) {
            .hero-about {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .faculty-img {
                height: 200px;
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
                        <a class="nav-link active" href="/about">About</a>
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
                        <a class="nav-link" href="/contact">Contact</a>
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
    <section class="hero-about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">About <?php echo htmlspecialchars($school_name ?? 'Our School'); ?></h1>
                    <p class="lead mb-4">Discover our rich history, values, and the dedicated team that makes learning extraordinary.</p>
                    <a href="#history" class="btn btn-light btn-lg me-3">Our History</a>
                    <a href="#faculty" class="btn btn-outline-light btn-lg">Meet Our Faculty</a>
                </div>
                <div class="col-lg-4">
                    <img src="/images/about-hero.jpg" alt="School Building" class="img-fluid rounded shadow" onerror="this.src='/images/default-about.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- School Overview -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">Welcome to <?php echo htmlspecialchars($school_name ?? 'Our School'); ?></h2>
                    <p class="lead mb-4"><?php echo htmlspecialchars($school_info['school_description'] ?? 'A comprehensive school management system for modern educational institutions.'); ?></p>
                    <p>Established in <?php echo htmlspecialchars($school_info['established_year'] ?? date('Y') - 25); ?>, our institution has been at the forefront of educational excellence, continuously adapting to meet the evolving needs of our students and the community we serve.</p>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_students'] ?? '1500'); ?>+</h3>
                        <p class="mb-0">Students Enrolled</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="mission-card">
                        <div class="text-center">
                            <i class="bi bi-bullseye display-4 text-primary mb-3"></i>
                            <h3 class="mb-3">Our Mission</h3>
                            <p class="lead"><?php echo htmlspecialchars($school_info['school_mission'] ?? 'To provide holistic education that nurtures intellectual, emotional, and physical development of every student.'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="mission-card">
                        <div class="text-center">
                            <i class="bi bi-eye display-4 text-success mb-3"></i>
                            <h3 class="mb-3">Our Vision</h3>
                            <p class="lead"><?php echo htmlspecialchars($school_info['school_vision'] ?? 'To be a leading educational institution that inspires excellence, innovation, and character development.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section id="history" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our History</h2>
                <p class="lead text-muted">A journey of excellence spanning decades</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="history-timeline">
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($school_info['established_year'] ?? date('Y') - 25); ?> - Foundation</h4>
                            <p>School was established with a vision to provide quality education to the community.</p>
                        </div>
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($school_info['established_year'] ?? date('Y') - 25) + 5; ?> - First Milestone</h4>
                            <p>Achieved recognition for academic excellence and student achievements.</p>
                        </div>
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($school_info['established_year'] ?? date('Y') - 25) + 10; ?> - Expansion</h4>
                            <p>Expanded facilities and introduced new academic programs.</p>
                        </div>
                        <div class="timeline-item">
                            <h4><?php echo date('Y'); ?> - Today</h4>
                            <p>Continuing our legacy of educational excellence with modern facilities and innovative teaching methods.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold"><?php echo htmlspecialchars($school_name ?? 'Our School'); ?> by Numbers</h2>
                <p class="lead text-muted">Key statistics that define our institution</p>
            </div>
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_students'] ?? '1500'); ?>+</h3>
                        <p class="mb-0">Students</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_teachers'] ?? '50'); ?>+</h3>
                        <p class="mb-0">Teachers</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_classes'] ?? '25'); ?>+</h3>
                        <p class="mb-0">Classes</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['avg_teacher_experience'] ?? '8.5'); ?>yrs</h3>
                        <p class="mb-0">Avg Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Faculty Section -->
    <section id="faculty" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Meet Our Faculty</h2>
                <p class="lead text-muted">Dedicated educators committed to student success</p>
            </div>
            <div class="row" id="faculty-content">
                <?php if (!empty($faculty)): ?>
                    <?php foreach ($faculty as $teacher): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card faculty-card h-100 shadow-sm">
                                <img src="<?php echo htmlspecialchars($teacher['photo_path'] ?? '/images/default-teacher.jpg'); ?>"
                                     class="card-img-top faculty-img"
                                     alt="<?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>"
                                     onerror="this.src='/images/default-teacher.jpg'">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . ($teacher['middle_name'] ? $teacher['middle_name'] . ' ' : '') . $teacher['last_name']); ?></h5>
                                    <p class="text-primary fw-bold mb-2"><?php echo htmlspecialchars($teacher['designation'] ?? 'Teacher'); ?></p>
                                    <?php if (!empty($teacher['qualification'])): ?>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($teacher['qualification']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($teacher['subjects'])): ?>
                                        <p class="text-muted small mb-0"><strong>Subjects:</strong> <?php echo htmlspecialchars($teacher['subjects']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($teacher['experience_years'] > 0): ?>
                                        <p class="text-muted small"><?php echo htmlspecialchars($teacher['experience_years']); ?> years experience</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default faculty members -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card faculty-card h-100 shadow-sm">
                            <img src="/images/default-teacher.jpg" class="card-img-top faculty-img" alt="Faculty Member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Dr. Sarah Johnson</h5>
                                <p class="text-primary fw-bold mb-2">Principal</p>
                                <p class="text-muted small mb-2">Ph.D. in Education</p>
                                <p class="text-muted small mb-0"><strong>Subjects:</strong> Administration</p>
                                <p class="text-muted small">15 years experience</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card faculty-card h-100 shadow-sm">
                            <img src="/images/default-teacher.jpg" class="card-img-top faculty-img" alt="Faculty Member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Mr. Robert Davis</h5>
                                <p class="text-primary fw-bold mb-2">Mathematics Teacher</p>
                                <p class="text-muted small mb-2">M.Sc. Mathematics</p>
                                <p class="text-muted small mb-0"><strong>Subjects:</strong> Mathematics, Statistics</p>
                                <p class="text-muted small">10 years experience</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card faculty-card h-100 shadow-sm">
                            <img src="/images/default-teacher.jpg" class="card-img-top faculty-img" alt="Faculty Member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ms. Emily Chen</h5>
                                <p class="text-primary fw-bold mb-2">Science Teacher</p>
                                <p class="text-muted small mb-2">M.Sc. Biology</p>
                                <p class="text-muted small mb-0"><strong>Subjects:</strong> Biology, Chemistry</p>
                                <p class="text-muted small">8 years experience</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?php echo htmlspecialchars($school_name ?? 'School'); ?></h5>
                    <p>Providing quality education and shaping tomorrow's leaders since <?php echo htmlspecialchars($school_info['established_year'] ?? date('Y') - 25); ?>.</p>
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
                    <p class="mb-1"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($school_info['school_address'] ?? '123 School Street, City, State 12345'); ?></p>
                    <p class="mb-1"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($school_info['school_phone'] ?? '+1 (555) 123-4567'); ?></p>
                    <p class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($school_info['school_email'] ?? 'info@school.com'); ?></p>
                    <p class="mb-0"><i class="bi bi-clock me-2"></i>Mon - Fri: 8:00 AM - 4:00 PM</p>
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
</body>
</html>