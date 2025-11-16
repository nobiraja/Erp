
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Admissions'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Apply for admission and learn about our admission process'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'admission, apply, fee structure'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'Admissions'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Apply for admission'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/admission'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-admission {
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .process-step {
            position: relative;
            text-align: center;
            padding: 30px 20px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .process-step::before {
            content: '';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .fee-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: none;
        }
        .requirement-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        .stats-card {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #007bff;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 9px;
            top: 30px;
            width: 2px;
            height: calc(100% - 10px);
            background: #dee2e6;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        @media (max-width: 768px) {
            .hero-admission {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .process-step {
                margin-bottom: 20px;
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
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admission">Admission</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-admission">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Admissions Open</h1>
                    <p class="lead mb-4">Join our vibrant learning community. We're excited to welcome new students to our family for the <?php echo htmlspecialchars(date('Y') . '-' . (date('Y') + 1)); ?> academic session.</p>
                    <a href="#apply" class="btn btn-light btn-lg me-3">Apply Now</a>
                    <a href="#process" class="btn btn-outline-light btn-lg">Admission Process</a>
                </div>
                <div class="col-lg-4">
                    <img src="/images/admission-hero.jpg" alt="Admissions Open" class="img-fluid rounded shadow" onerror="this.src='/images/default-admission.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Admission Overview</h2>
                <p class="lead text-muted">Key statistics and information for prospective students</p>
            </div>
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['applications_this_year'] ?? '250'); ?>+</h3>
                        <p class="mb-0">Applications This Year</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars(count($stats['class_capacity'] ?? [])); ?>+</h3>
                        <p class="mb-0">Classes Available</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2">₹<?php echo htmlspecialchars(number_format($stats['fee_range']['min_fee'] ?? 1500)); ?>-<?php echo htmlspecialchars(number_format($stats['fee_range']['max_fee'] ?? 3000)); ?></h3>
                        <p class="mb-0">Fee Range (Monthly)</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($current_year); ?></h3>
                        <p class="mb-0">Academic Session</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Process Section -->
    <section id="process" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Admission Process</h2>
                <p class="lead text-muted">Simple steps to join our school community</p>
            </div>
            <div class="row">
                <?php if (!empty($process)): ?>
                    <?php foreach ($process as $step): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="process-step">
                                <div style="position: relative; z-index: 1;">
                                    <div class="text-primary mb-3">
                                        <i class="<?php echo htmlspecialchars($step['icon'] ?? 'bi-check-circle'); ?> display-4"></i>
                                    </div>
                                    <h5 class="card-title">Step <?php echo htmlspecialchars($step['step']); ?>: <?php echo htmlspecialchars($step['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($step['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default process steps -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="text-primary mb-3">
                                <i class="bi-file-earmark-text display-4"></i>
                            </div>
                            <h5 class="card-title">Step 1: Online Application</h5>
                            <p class="card-text">Fill out the online application form with student and parent details.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="text-primary mb-3">
                                <i class="bi-upload display-4"></i>
                            </div>
                            <h5 class="card-title">Step 2: Document Submission</h5>
                            <p class="card-text">Submit required documents including birth certificate and previous school records.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="process-step">
                            <div class="text-primary mb-3">
                                <i class="bi-clipboard-check display-4"></i>
                            </div>
                            <h5 class="card-title">Step 3: Entrance Assessment</h5>
                            <p class="card-text">Take the entrance examination and/or interview as per grade level requirements.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Requirements Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Admission Requirements</h2>
                <p class="lead text-muted">What you need to apply</p>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="requirement-card">
                        <h5 class="mb-3"><i class="bi bi-person-check text-primary me-2"></i>Eligibility Criteria</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><?php echo htmlspecialchars($requirements['age_criteria'] ?? 'Children must be 5+ years for Class 1, and appropriate age for subsequent grades.'); ?></li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><?php echo htmlspecialchars($requirements['eligibility_criteria'] ?? 'Minimum 60% in previous grade, Good conduct certificate, Medical fitness certificate'); ?></li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><?php echo htmlspecialchars($requirements['medical_requirements'] ?? 'Complete medical checkup certificate, vaccination records'); ?></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="requirement-card">
                        <h5 class="mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Required Documents</h5>
                        <ul class="list-unstyled">
                            <?php
                            $docs = explode(',', $requirements['documents_required'] ?? 'Birth Certificate, Previous School Records, Transfer Certificate, Medical Certificate, Passport Size Photos');
                            foreach ($docs as $doc): ?>
                                <li class="mb-2"><i class="bi bi-file-earmark me-2 text-info"></i><?php echo htmlspecialchars(trim($doc)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fee Structure Section -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Fee Structure</h2>
                <p class="lead text-muted">Transparent and affordable fee structure for quality education</p>
            </div>
            <div class="row">
                <?php if (!empty($fee_structure)): ?>
                    <?php
                    $groupedFees = [];
                    foreach ($fee_structure as $fee) {
                        $key = $fee['class_name'] . ' ' . $fee['section'];
                        if (!isset($groupedFees[$key])) {
                            $groupedFees[$key] = [];
                        }
                        $groupedFees[$key][] = $fee;
                    }
                    ?>
                    <?php foreach ($groupedFees as $class => $fees): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="fee-card">
                                <h5 class="mb-3">Class <?php echo htmlspecialchars($class); ?> - <?php echo htmlspecialchars($fees[0]['academic_year'] ?? '2024-2025'); ?></h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless text-white">
                                        <thead>
                                            <tr>
                                                <th>Fee Type</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fees as $fee): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($fee['fee_type']); ?></td>
                                                    <td>₹<?php echo htmlspecialchars(number_format($fee['amount'], 2)); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default fee structure -->
                    <div class="col-lg-6 mb-4">
                        <div class="fee-card">
                            <h5 class="mb-3">Primary Classes (1-5)</h5>
                            <table class="table table-borderless text-white">
                                <tbody>
                                    <tr><td>Admission Fee</td><td>₹2,000</td></tr>
                                    <tr><td>Tuition Fee (Monthly)</td><td>₹1,500</td></tr>
                                    <tr><td>Development Fee (Annual)</td><td>₹3,000</td></tr>
                                    <tr><td>Transportation (Optional)</td><td>₹800</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="fee-card">
                            <h5 class="mb-3">Secondary Classes (6-10)</h5>
                            <table class="table table-borderless text-white">
                                <tbody>
                                    <tr><td>Admission Fee</td><td>₹2,500</td></tr>
                                    <tr><td>Tuition Fee (Monthly)</td><td>₹2,000</td></tr>
                                    <tr><td>Development Fee (Annual)</td><td>₹4,000</td></tr>
                                    <tr><td>Computer Lab Fee (Monthly)</td><td>₹300</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Important Dates Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Important Dates</h2>
                <p class="lead text-muted">Mark your calendar for key admission dates</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if (!empty($important_dates)): ?>
                        <?php foreach ($important_dates as $date): ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($date['event']); ?></h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($date['description']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($date['date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default important dates -->
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Application Start Date</h6>
                                    <p class="text-muted mb-0">Online applications open for new academic session</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">Jan 01, <?php echo $current_year; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Application Deadline</h6>
                                    <p class="text-muted mb-0">Last date to submit admission applications</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">Mar 31, <?php echo $current_year; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Entrance Tests</h6>
                                    <p class="text-muted mb-0">Written tests and interviews for eligible candidates</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">Apr 15-20, <?php echo $current_year; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Apply Now Section -->
    <section id="apply" class="section-padding bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-3">Ready to Apply?</h2>
