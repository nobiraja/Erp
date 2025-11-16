<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'School Homepage'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Welcome to our school website'); ?>">
    <meta name="keywords" content="school, education, students, teachers, courses, events">
    <meta name="author" content="<?php echo htmlspecialchars($school_name ?? 'School Management System'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'School Homepage'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Welcome to our school website'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-carousel {
            height: 70vh;
            min-height: 500px;
        }
        .hero-carousel .carousel-item {
            height: 100%;
        }
        .hero-carousel img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-content {
            text-align: center;
            color: white;
            z-index: 2;
        }
        .section-padding {
            padding: 80px 0;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .testimonial-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .achievement-counter {
            font-size: 3rem;
            font-weight: bold;
            color: #007bff;
        }
        .navbar-brand img {
            height: 50px;
            width: auto;
        }
        @media (max-width: 768px) {
            .hero-carousel {
                height: 50vh;
                min-height: 300px;
            }
            .section-padding {
                padding: 40px 0;
            }
            .achievement-counter {
                font-size: 2rem;
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
                        <a class="nav-link active" href="/">Home</a>
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
                        <a class="nav-link" href="/admission">Admission</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Image Carousel -->
    <section id="hero-carousel" class="hero-carousel">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators" id="carousel-indicators">
                <!-- Indicators will be loaded dynamically -->
            </div>
            <div class="carousel-inner" id="carousel-inner">
                <!-- Carousel items will be loaded dynamically -->
                <div class="carousel-item active">
                    <img src="/images/default-hero.jpg" class="d-block w-100" alt="School Hero Image">
                    <div class="hero-overlay">
                        <div class="hero-content">
                            <h1 class="display-4 fw-bold mb-3">Welcome to <?php echo htmlspecialchars($school_name ?? 'Our School'); ?></h1>
                            <p class="lead mb-4">Excellence in Education, Building Tomorrow's Leaders</p>
                            <a href="/admission" class="btn btn-primary btn-lg me-3">Apply Now</a>
                            <a href="/about" class="btn btn-outline-light btn-lg">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- About Section -->
    <section id="about-section" class="section-padding bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" id="about-content">
                    <h2 class="mb-4">About Our School</h2>
                    <p class="lead mb-4">We are committed to providing quality education and nurturing the potential of every student.</p>
                    <p>Our school offers a comprehensive curriculum, experienced faculty, and modern facilities to ensure the best learning environment for our students.</p>
                    <a href="/about" class="btn btn-primary">Read More</a>
                </div>
                <div class="col-lg-6">
                    <img src="/images/about-school.jpg" class="img-fluid rounded shadow" alt="About Our School" onerror="this.src='/images/default-about.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section id="courses-section" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Courses</h2>
                <p class="lead text-muted">Comprehensive academic programs designed for excellence</p>
            </div>
            <div class="row" id="courses-content">
                <!-- Courses will be loaded dynamically -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-book display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Primary Education</h5>
                            <p class="card-text">Foundation building with comprehensive primary curriculum</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-calculator display-4 text-success mb-3"></i>
                            <h5 class="card-title">Secondary Education</h5>
                            <p class="card-text">Advanced learning with specialized subject streams</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-mortarboard display-4 text-info mb-3"></i>
                            <h5 class="card-title">Higher Secondary</h5>
                            <p class="card-text">College preparation with career guidance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="/courses" class="btn btn-outline-primary btn-lg">View All Courses</a>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events-section" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Upcoming Events</h2>
                <p class="lead text-muted">Stay connected with our school activities and events</p>
            </div>
            <div class="row" id="events-content">
                <!-- Events will be loaded dynamically -->
            </div>
            <div class="text-center mt-4">
                <a href="/events" class="btn btn-outline-primary btn-lg">View All Events</a>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section id="achievements-section" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Achievements</h2>
                <p class="lead text-muted">Celebrating excellence and success</p>
            </div>
            <div class="row text-center" id="achievements-content">
                <!-- Achievements will be loaded dynamically -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="achievement-counter" data-target="1500">0</div>
                    <h5 class="text-muted">Students</h5>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="achievement-counter" data-target="95">0</div>
                    <span class="h3 text-primary">%</span>
                    <h5 class="text-muted">Pass Rate</h5>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="achievement-counter" data-target="50">0</div>
                    <span class="h3 text-primary">+</span>
                    <h5 class="text-muted">Teachers</h5>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="achievement-counter" data-target="25">0</div>
                    <span class="h3 text-primary">+</span>
                    <h5 class="text-muted">Years</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Preview Section -->
    <section id="gallery-section" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Gallery</h2>
                <p class="lead text-muted">Capturing memorable moments</p>
            </div>
            <div class="row" id="gallery-content">
                <!-- Gallery images will be loaded dynamically -->
            </div>
            <div class="text-center mt-4">
                <a href="/gallery" class="btn btn-outline-primary btn-lg">View Full Gallery</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials-section" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">What Parents Say</h2>
                <p class="lead text-muted">Hear from our community</p>
            </div>
            <div class="row" id="testimonials-content">
                <!-- Testimonials will be loaded dynamically -->
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section id="cta-section" class="section-padding bg-primary text-white">
        <div class="container text-center">
            <div id="cta-content">
                <h2 class="display-5 fw-bold mb-3">Ready to Join Our Community?</h2>
                <p class="lead mb-4">Take the first step towards a brighter future for your child</p>
                <a href="/admission" class="btn btn-light btn-lg me-3">Apply for Admission</a>
                <a href="/contact" class="btn btn-outline-light btn-lg">Contact Us</a>
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
                        <li><a href="/about" class="text-white-50">About Us</a></li>
                        <li><a href="/courses" class="text-white-50">Courses</a></li>
                        <li><a href="/events" class="text-white-50">Events</a></li>
                        <li><a href="/gallery" class="text-white-50">Gallery</a></li>
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

    <!-- Custom JavaScript for AJAX loading -->
    <script>
        // Load homepage content via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            loadHomepageContent();
            initializeCounters();
        });

        function loadHomepageContent() {
            // Load carousel images
            fetch('/api/homepage/carousel')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        updateCarousel(data.data);
                    }
                })
                .catch(error => console.log('Carousel loading failed:', error));

            // Load events
            fetch('/api/homepage/events')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        updateEvents(data.data);
                    }
                })
                .catch(error => console.log('Events loading failed:', error));

            // Load gallery
            fetch('/api/homepage/gallery')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        updateGallery(data.data);
                    }
                })
                .catch(error => console.log('Gallery loading failed:', error));
        }

        function updateCarousel(images) {
            const indicators = document.getElementById('carousel-indicators');
            const inner = document.getElementById('carousel-inner');

            indicators.innerHTML = '';
            inner.innerHTML = '';

            images.forEach((image, index) => {
                // Add indicator
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.setAttribute('data-bs-target', '#carouselExampleIndicators');
                indicator.setAttribute('data-bs-slide-to', index);
                if (index === 0) indicator.className = 'active';
                indicators.appendChild(indicator);

                // Add carousel item
                const item = document.createElement('div');
                item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                item.innerHTML = `
                    <img src="${image.image_path || '/images/default-hero.jpg'}" class="d-block w-100" alt="${image.title || 'School Image'}">
                    <div class="hero-overlay">
                        <div class="hero-content">
                            <h1 class="display-4 fw-bold mb-3">${image.title || 'Welcome to Our School'}</h1>
                            <p class="lead mb-4">${image.content || 'Excellence in Education'}</p>
                            ${image.link_url ? `<a href="${image.link_url}" class="btn btn-primary btn-lg me-3">Learn More</a>` : ''}
                        </div>
                    </div>
                `;
                inner.appendChild(item);
            });
        }

        function updateEvents(events) {
            const container = document.getElementById('events-content');
            container.innerHTML = '';

            events.slice(0, 3).forEach(event => {
                const eventDate = new Date(event.event_date).toLocaleDateString();
                const col = document.createElement('div');
                col.className = 'col-lg-4 mb-4';
                col.innerHTML = `
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        ${event.image_path ? `<img src="${event.image_path}" class="card-img-top" alt="${event.title}" style="height: 200px; object-fit: cover;">` : ''}
                        <div class="card-body">
                            <h5 class="card-title">${event.title}</h5>
                            <p class="card-text text-muted mb-2"><i class="bi bi-calendar me-2"></i>${eventDate}</p>
                            <p class="card-text">${event.description ? event.description.substring(0, 100) + '...' : ''}</p>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        }

        function updateGallery(images) {
            const container = document.getElementById('gallery-content');
            container.innerHTML = '';

            images.forEach(image => {
                const col = document.createElement('div');
                col.className = 'col-lg-4 col-md-6 mb-4';
                col.innerHTML = `
                    <div class="card card-hover border-0 shadow-sm">
                        <img src="${image.image_path}" class="card-img-top" alt="${image.title}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">${image.title}</h6>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        }

        function initializeCounters() {
            const counters = document.querySelectorAll('.achievement-counter');

            const observerOptions = {
                threshold: 0.5
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.getAttribute('data-target'));
                        animateCounter(counter, target);
                        observer.unobserve(counter);
                    }
                });
            }, observerOptions);

            counters.forEach(counter => observer.observe(counter));
        }

        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 30);
        }
    </script>
</body>
</html>