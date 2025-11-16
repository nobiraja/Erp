<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Events & Activities'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Stay updated with school events and activities'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'school events, activities, calendar'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'Events & Activities'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Stay updated with school events'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/events'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-events {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .event-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .event-date {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
        }
        .calendar-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .calendar-day {
            min-height: 80px;
            border: 1px solid #e9ecef;
            padding: 5px;
            position: relative;
        }
        .calendar-day.has-event {
            background-color: #e3f2fd;
        }
        .calendar-day.today {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
        }
        .event-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 6px;
            height: 6px;
            background-color: #007bff;
            border-radius: 50%;
        }
        .stats-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .past-event-card {
            opacity: 0.8;
            border: 1px solid #dee2e6;
        }
        @media (max-width: 768px) {
            .hero-events {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .calendar-day {
                min-height: 60px;
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
                        <a class="nav-link active" href="/events">Events</a>
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
    <section class="hero-events">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Events & Activities</h1>
                    <p class="lead mb-4">Join us for exciting events, celebrations, and memorable experiences that enrich our school community.</p>
                    <a href="#upcoming" class="btn btn-light btn-lg me-3">Upcoming Events</a>
                    <a href="#calendar" class="btn btn-outline-light btn-lg">Event Calendar</a>
                </div>
                <div class="col-lg-4">
                    <img src="/images/events-hero.jpg" alt="School Events" class="img-fluid rounded shadow" onerror="this.src='/images/default-events.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Event Highlights</h2>
                <p class="lead text-muted">Celebrating achievements and creating memories</p>
            </div>
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_events'] ?? '25'); ?>+</h3>
                        <p class="mb-0">Total Events</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['upcoming_events'] ?? '8'); ?>+</h3>
                        <p class="mb-0">Upcoming Events</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['past_events'] ?? '17'); ?>+</h3>
                        <p class="mb-0">Past Events</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['this_month_events'] ?? '3'); ?>+</h3>
                        <p class="mb-0">This Month</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section id="upcoming" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Upcoming Events</h2>
                <p class="lead text-muted">Don't miss these exciting upcoming events</p>
            </div>
            <div class="row" id="upcoming-events">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="col-lg-4 mb-4">
                            <div class="card event-card h-100 shadow-sm">
                                <?php if (!empty($event['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="event-date mb-3">
                                        <div class="fw-bold"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                        <div class="display-6"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                        <small><?php echo date('Y', strtotime($event['event_date'])); ?></small>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 100)); ?>...</p>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?php echo date('h:i A', strtotime($event['event_time'] ?? '00:00:00')); ?>
                                            <?php if (!empty($event['location'])): ?>
                                                <br><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($event['location']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($event['organizer'])): ?>
                                        <small class="text-muted">Organized by: <?php echo htmlspecialchars($event['organizer']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default upcoming events -->
                    <div class="col-lg-4 mb-4">
                        <div class="card event-card h-100 shadow-sm">
                            <img src="/images/sports-day.jpg" class="card-img-top" alt="Annual Sports Day" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="event-date mb-3">
                                    <div class="fw-bold"><?php echo date('M', strtotime('+7 days')); ?></div>
                                    <div class="display-6"><?php echo date('d', strtotime('+7 days')); ?></div>
                                    <small><?php echo date('Y', strtotime('+7 days')); ?></small>
                                </div>
                                <h5 class="card-title">Annual Sports Day</h5>
                                <p class="card-text">Join us for an exciting day of sports competitions, games, and celebrations.</p>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>9:00 AM
                                        <br><i class="bi bi-geo-alt me-1"></i>School Ground
                                    </small>
                                </div>
                                <small class="text-muted">Organized by: Sports Department</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card event-card h-100 shadow-sm">
                            <img src="/images/science-fair.jpg" class="card-img-top" alt="Science Fair 2024" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="event-date mb-3">
                                    <div class="fw-bold"><?php echo date('M', strtotime('+14 days')); ?></div>
                                    <div class="display-6"><?php echo date('d', strtotime('+14 days')); ?></div>
                                    <small><?php echo date('Y', strtotime('+14 days')); ?></small>
                                </div>
                                <h5 class="card-title">Science Fair 2024</h5>
                                <p class="card-text">Showcase your innovative science projects and compete for prizes.</p>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>10:00 AM
                                        <br><i class="bi bi-geo-alt me-1"></i>Science Lab
                                    </small>
                                </div>
                                <small class="text-muted">Organized by: Science Department</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card event-card h-100 shadow-sm">
                            <img src="/images/cultural-fest.jpg" class="card-img-top" alt="Cultural Fest" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="event-date mb-3">
                                    <div class="fw-bold"><?php echo date('M', strtotime('+21 days')); ?></div>
                                    <div class="display-6"><?php echo date('d', strtotime('+21 days')); ?></div>
                                    <small><?php echo date('Y', strtotime('+21 days')); ?></small>
                                </div>
                                <h5 class="card-title">Cultural Fest</h5>
                                <p class="card-text">Celebrate diversity through music, dance, and cultural performances.</p>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>2:00 PM
                                        <br><i class="bi bi-geo-alt me-1"></i>Auditorium
                                    </small>
                                </div>
                                <small class="text-muted">Organized by: Cultural Committee</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Event Calendar Section -->
    <section id="calendar" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Event Calendar</h2>
                <p class="lead text-muted">Plan ahead with our monthly event calendar</p>
            </div>

            <div class="calendar-header text-center mb-4">
                <h3><?php echo htmlspecialchars($current_month_name . ' ' . $current_year); ?></h3>
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-light" onclick="changeMonth(-1)">Previous</button>
                    <button class="btn btn-outline-light" onclick="changeMonth(1)">Next</button>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="calendar-container bg-white rounded shadow p-4">
                        <div class="calendar-grid" id="calendar-grid">
                            <!-- Calendar will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Past Events Section -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Recent Events</h2>
                <p class="lead text-muted">Memorable moments from our past events</p>
            </div>
            <div class="row" id="past-events">
                <?php if (!empty($past_events)): ?>
                    <?php foreach ($past_events as $event): ?>
                        <div class="col-lg-4 mb-4">
                            <div class="card past-event-card h-100">
                                <?php if (!empty($event['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <p class="card-text small"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 80)); ?>...</p>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default past events -->
                    <div class="col-lg-4 mb-4">
                        <div class="card past-event-card h-100">
                            <img src="/images/independence-day.jpg" class="card-img-top" alt="Independence Day Celebration" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">Independence Day Celebration</h6>
                                <p class="card-text small">Patriotic celebrations with flag hoisting and cultural programs.</p>
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>Aug 15, 2024</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card past-event-card h-100">
                            <img src="/images/ptm.jpg" class="card-img-top" alt="Parent-Teacher Meeting" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">Parent-Teacher Meeting</h6>
                                <p class="card-text small">Interactive session between parents and teachers to discuss student progress.</p>
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>Jul 20, 2024</small>
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

    <!-- Custom JavaScript for Calendar -->
    <script>
        let currentYear = <?php echo $current_year; ?>;
        let currentMonth = <?php echo $current_month; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            loadCalendar(currentYear, currentMonth);
        });

        function changeMonth(direction) {
            currentMonth += direction;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            } else if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            loadCalendar(currentYear, currentMonth);
        }

        function loadCalendar(year, month) {
            // Update header
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.querySelector('.calendar-header h3').textContent = monthNames[month - 1] + ' ' + year;

            // Generate calendar grid
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();

            let calendarHTML = '<div class="row mb-3"><div class="col-12"><div class="row text-center fw-bold">';
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayNames.forEach(day => {
                calendarHTML += `<div class="col calendar-day-header">${day}</div>`;
            });
            calendarHTML += '</div></div></div><div class="row">';

            let dayCount = 1;
            for (let week = 0; week < 6; week++) {
                calendarHTML += '<div class="col-12"><div class="row">';
                for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
                    if ((week === 0 && dayOfWeek < startingDayOfWeek) || dayCount > daysInMonth) {
                        calendarHTML += '<div class="col calendar-day"></div>';
                    } else {
                        const isToday = (year === new Date().getFullYear() &&
                                       month === new Date().getMonth() + 1 &&
                                       dayCount === new Date().getDate());
                        const hasEvent = Math.random() > 0.7; // Placeholder for actual event check
                        calendarHTML += `<div class="col calendar-day ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}">`;
                        calendarHTML += `<div class="day-number">${dayCount}</div>`;
                        if (hasEvent) {
                            calendarHTML += '<div class="event-indicator"></div>';
                        }
                        calendarHTML += '</div>';
                        dayCount++;
                    }
                }
                calendarHTML += '</div></div>';
                if (dayCount > daysInMonth) break;
            }
            calendarHTML += '</div>';

            document.getElementById('calendar-grid').innerHTML = calendarHTML;
        }
    </script>
</body>
</html>