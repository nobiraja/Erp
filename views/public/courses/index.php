<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Academic Programs'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Explore our academic programs and curriculum'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'courses, curriculum, academic programs'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'Academic Programs'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Explore our academic programs'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/courses'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-courses {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .program-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .class-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .curriculum-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .subject-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
        }
        .admission-requirements {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            border-radius: 15px;
            padding: 40px;
        }
        @media (max-width: 768px) {
            .hero-courses {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .curriculum-section, .admission-requirements {
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
                        <a class="nav-link active" href="/courses">Courses</a>
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
    <section class="hero-courses">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Academic Programs</h1>
                    <p class="lead mb-4">Comprehensive curriculum designed to nurture intellectual growth, creativity, and character development in every student.</p>
                    <a href="#programs" class="btn btn-light btn-lg me-3">Explore Programs</a>
                    <a href="#admission" class="btn btn-outline-light btn-lg">Admission Info</a>
                </div>
                <div class="col-lg-4">
                    <img src="/images/courses-hero.jpg" alt="Academic Excellence" class="img-fluid rounded shadow" onerror="this.src='/images/default-courses.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Academic Overview</h2>
                <p class="lead text-muted">Numbers that reflect our commitment to quality education</p>
            </div>
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_classes'] ?? '25'); ?>+</h3>
                        <p class="mb-0">Classes</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_subjects'] ?? '15'); ?>+</h3>
                        <p class="mb-0">Subjects</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['primary_classes'] ?? '10'); ?>+</h3>
                        <p class="mb-0">Primary Classes</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['secondary_classes'] ?? '10'); ?>+</h3>
                        <p class="mb-0">Secondary Classes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academic Programs Section -->
    <section id="programs" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Academic Programs</h2>
                <p class="lead text-muted">Structured learning pathways from primary to higher secondary education</p>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card program-card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-house-door display-4 text-primary mb-3"></i>
                            <h4 class="card-title">Primary Education</h4>
                            <p class="card-text">Classes 1-5: Building strong foundations in literacy, numeracy, and social skills.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Core subjects focus</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Creative arts integration</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Physical development</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Character building</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card program-card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-calculator display-4 text-success mb-3"></i>
                            <h4 class="card-title">Secondary Education</h4>
                            <p class="card-text">Classes 6-10: Developing critical thinking and subject specialization.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Advanced mathematics</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Science & technology</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Language proficiency</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Social sciences</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card program-card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-mortarboard display-4 text-info mb-3"></i>
                            <h4 class="card-title">Higher Secondary</h4>
                            <p class="card-text">Classes 11-12: College preparation with career guidance and specialization.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Stream selection</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Entrance exam prep</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Career counseling</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Research skills</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Curriculum Details Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Curriculum Details</h2>
                <p class="lead text-muted">Comprehensive learning framework designed for holistic development</p>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="curriculum-section">
                        <h4 class="mb-3"><i class="bi bi-book me-2"></i>Primary Curriculum (Classes 1-5)</h4>
                        <p><?php echo htmlspecialchars($curriculum['primary'] ?? 'Our primary curriculum focuses on building strong foundations in core subjects while encouraging creativity and physical development through art, music, and sports.'); ?></p>
                        <div class="mt-3">
                            <strong>Key Focus Areas:</strong>
                            <ul class="mt-2">
                                <li>Language Development (English, Hindi, Regional)</li>
                                <li>Mathematical Concepts</li>
                                <li>Environmental Science</li>
                                <li>Social Studies & Moral Science</li>
                                <li>Art, Music & Physical Education</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="curriculum-section">
                        <h4 class="mb-3"><i class="bi bi-lightbulb me-2"></i>Secondary & Higher Secondary</h4>
                        <p><?php echo htmlspecialchars($curriculum['secondary'] ?? 'Secondary education emphasizes critical thinking, problem-solving, and subject specialization while maintaining a balanced approach to overall development.'); ?></p>
                        <div class="mt-3">
                            <strong>Key Focus Areas:</strong>
                            <ul class="mt-2">
                                <li>Advanced Science & Mathematics</li>
                                <li>Literature & Language Arts</li>
                                <li>Social Sciences & History</li>
                                <li>Computer Applications</li>
                                <li>Career Guidance & Counseling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Classes and Subjects Section -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Classes & Subjects</h2>
                <p class="lead text-muted">Detailed breakdown of our academic structure</p>
            </div>
            <div class="row" id="classes-content">
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="class-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h4 class="mb-1">Class <?php echo htmlspecialchars($class['class_name']); ?> - Section <?php echo htmlspecialchars($class['section']); ?></h4>
                                        <p class="mb-0 opacity-75">Academic Year: <?php echo htmlspecialchars($class['academic_year'] ?? '2024-2025'); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <small class="opacity-75">Class Teacher</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars(($class['teacher_first_name'] ?? '') . ' ' . ($class['teacher_last_name'] ?? '')); ?></p>
                                    </div>
                                </div>
                                <div class="subject-list">
                                    <h6><i class="bi bi-journal-text me-2"></i>Subjects (<?php echo htmlspecialchars($class['subject_count'] ?? 0); ?>)</h6>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($class['subjects'] ?? 'Subjects to be assigned'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default classes -->
                    <div class="col-lg-6 mb-4">
                        <div class="class-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">Class 1 - Section A</h4>
                                    <p class="mb-0 opacity-75">Academic Year: 2024-2025</p>
                                </div>
                                <div class="text-end">
                                    <small class="opacity-75">Class Teacher</small>
                                    <p class="mb-0 fw-bold">Ms. Johnson</p>
                                </div>
                            </div>
                            <div class="subject-list">
                                <h6><i class="bi bi-journal-text me-2"></i>Subjects (6)</h6>
                                <p class="mb-0 small">English, Mathematics, Science, Social Studies, Art, Physical Education</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="class-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">Class 10 - Section A</h4>
                                    <p class="mb-0 opacity-75">Academic Year: 2024-2025</p>
                                </div>
                                <div class="text-end">
                                    <small class="opacity-75">Class Teacher</small>
                                    <p class="mb-0 fw-bold">Mrs. Davis</p>
                                </div>
                            </div>
                            <div class="subject-list">
                                <h6><i class="bi bi-journal-text me-2"></i>Subjects (7)</h6>
                                <p class="mb-0 small">English, Mathematics, Physics, Chemistry, Biology, History, Geography</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Admission Requirements Section -->
    <section id="admission" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Admission Requirements</h2>
                <p class="lead text-muted">Everything you need to know about joining our academic community</p>
            </div>
            <div class="admission-requirements">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <h4 class="mb-3"><i class="bi bi-person-check me-2"></i>Eligibility Criteria</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($admission_requirements['age_criteria'] ?? 'Children must be 5+ years for Class 1, and appropriate age for subsequent grades.'); ?></li>
                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($admission_requirements['eligibility_criteria'] ?? 'Minimum 60% in previous grade, Good conduct certificate, Medical fitness certificate'); ?></li>
                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Application deadline: <?php echo htmlspecialchars($admission_requirements['application_deadline'] ?? 'March 31st for new academic session'); ?></li>
                        </ul>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <h4 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Required Documents</h4>
                        <ul class="list-unstyled">
                            <?php
                            $docs = explode(',', $admission_requirements['documents_required'] ?? 'Birth Certificate, Previous School Records, Transfer Certificate, Medical Certificate, Passport Size Photos');
                            foreach ($docs as $doc): ?>
                                <li class="mb-2"><i class="bi bi-file-earmark me-2"></i><?php echo htmlspecialchars(trim($doc)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <h5>Fee Structure</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($admission_requirements['fee_structure'] ?? 'Registration Fee: ₹500, Admission Fee: ₹2000, Monthly Tuition: ₹1500-3000 based on grade'); ?></p>
                    <a href="/admission" class="btn btn-light btn-lg mt-3">Apply for Admission</a>
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
                    <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>123 School Street, City, State 12345</p>
                    <p class="mb-1"><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                    <p class="mb-1"><i class="bi bi-envelope me-2"></i>info@school.com</p>
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