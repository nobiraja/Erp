<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Photo Gallery'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'Explore our photo gallery showcasing school events and activities'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords ?? 'photo gallery, school photos, events'); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'Photo Gallery'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description ?? 'Explore our photo gallery'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/gallery'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .hero-gallery {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: white;
            padding: 80px 0;
        }
        .section-padding {
            padding: 80px 0;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        .gallery-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover .gallery-img {
            transform: scale(1.1);
        }
        .category-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .category-filter:hover,
        .category-filter.active {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
        }
        .stats-card {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .search-bar {
            background: white;
            border-radius: 25px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-body {
            padding: 0;
        }
        @media (max-width: 768px) {
            .hero-gallery {
                padding: 40px 0;
            }
            .section-padding {
                padding: 40px 0;
            }
            .gallery-img {
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
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/gallery">Gallery</a>
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
    <section class="hero-gallery">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Photo Gallery</h1>
                    <p class="lead mb-4">Capturing the vibrant moments, achievements, and memories that make our school community special.</p>
                    <a href="#gallery" class="btn btn-light btn-lg me-3">Explore Gallery</a>
                    <a href="#categories" class="btn btn-outline-light btn-lg">Browse Categories</a>
                </div>
                <div class="col-lg-4">
                    <img src="/images/gallery-hero.jpg" alt="School Gallery" class="img-fluid rounded shadow" onerror="this.src='/images/default-gallery.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Gallery Highlights</h2>
                <p class="lead text-muted">A visual journey through our school life</p>
            </div>
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_media'] ?? '100'); ?>+</h3>
                        <p class="mb-0">Photos & Videos</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['total_categories'] ?? '6'); ?>+</h3>
                        <p class="mb-0">Categories</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['recent_uploads'] ?? '15'); ?>+</h3>
                        <p class="mb-0">Recent Uploads</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3 class="display-4 fw-bold mb-2"><?php echo htmlspecialchars($stats['popular_category'] ?? 'Sports'); ?></h3>
                        <p class="mb-0">Popular Category</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="section-padding">
        <div class="container">
            <div class="search-bar mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-0 bg-transparent" id="searchInput" placeholder="Search gallery...">
                        </div>
                    </div>
                    <div class="col-lg-6 text-lg-end">
                        <small class="text-muted me-3">Filter by category:</small>
                        <button class="category-filter active" data-category="all">All</button>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <button class="category-filter" data-category="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?> (<?php echo htmlspecialchars($category['media_count'] ?? 0); ?>)
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <button class="category-filter" data-category="Events">Events (0)</button>
                            <button class="category-filter" data-category="Sports">Sports (0)</button>
                            <button class="category-filter" data-category="Cultural">Cultural (0)</button>
                            <button class="category-filter" data-category="Academics">Academics (0)</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Grid Section -->
    <section id="gallery" class="section-padding bg-light">
        <div class="container">
            <div class="row" id="gallery-grid">
                <?php if (!empty($gallery_items)): ?>
                    <?php foreach ($gallery_items as $item): ?>
                        <div class="col-lg-4 col-md-6 mb-4 gallery-item-wrapper"
                             data-category="<?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>"
                             data-title="<?php echo htmlspecialchars($item['title'] ?? ''); ?>"
                             data-description="<?php echo htmlspecialchars($item['description'] ?? ''); ?>"
                             data-media-type="<?php echo htmlspecialchars($item['media_type'] ?? 'image'); ?>"
                             data-media-path="<?php echo htmlspecialchars($item['media_type'] === 'video' ? ($item['video_path'] ?? $item['image_path']) : $item['image_path']); ?>">
                            <div class="gallery-item card border-0 shadow-sm h-100">
                                <?php if ($item['media_type'] === 'video'): ?>
                                    <div class="position-relative">
                                        <video class="card-img-top gallery-img"
                                               style="height: 250px; object-fit: cover;">
                                            <source src="<?php echo htmlspecialchars($item['video_path'] ?? $item['image_path']); ?>" type="video/mp4">
                                        </video>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <i class="bi bi-play-circle-fill text-white fs-1 bg-dark bg-opacity-50 rounded-circle p-2"></i>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-danger">VIDEO</span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($item['thumbnail_path'] ?? $item['image_path'] ?? '/images/default-gallery.jpg'); ?>"
                                         class="card-img-top gallery-img"
                                         alt="<?php echo htmlspecialchars($item['alt_text'] ?? $item['title'] ?? 'Gallery Image'); ?>"
                                         onerror="this.src='/images/default-gallery.jpg'">
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-success">IMAGE</span>
                                    </div>
                                <?php endif; ?>

                                <div class="gallery-overlay">
                                    <div class="text-center text-white">
                                        <?php if ($item['media_type'] === 'video'): ?>
                                            <i class="bi bi-play-circle display-4 mb-2"></i>
                                            <h6><?php echo htmlspecialchars($item['title'] ?? 'Play Video'); ?></h6>
                                        <?php else: ?>
                                            <i class="bi bi-zoom-in display-4 mb-2"></i>
                                            <h6><?php echo htmlspecialchars($item['title'] ?? 'View Image'); ?></h6>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($item['title'] ?? 'Gallery Item'); ?></h6>
                                    <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 80)); ?><?php echo strlen($item['description'] ?? '') > 80 ? '...' : ''; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if (!empty($item['category_name'])): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['is_featured'])): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-star-fill me-1"></i>Featured
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['tags'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bi bi-tags me-1"></i><?php echo htmlspecialchars($item['tags']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default gallery items -->
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item-wrapper" data-category="Sports" data-title="Annual Sports Day" data-description="Students participating in various sports activities">
                        <div class="gallery-item card border-0 shadow-sm h-100">
                            <img src="/images/gallery/sports-day-1.jpg" class="card-img-top gallery-img" alt="Annual Sports Day" onerror="this.src='/images/default-gallery.jpg'">
                            <div class="gallery-overlay">
                                <div class="text-center text-white">
                                    <i class="bi bi-zoom-in display-4 mb-2"></i>
                                    <h6>Annual Sports Day</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Annual Sports Day</h6>
                                <p class="card-text small text-muted">Students participating in various sports activities...</p>
                                <span class="badge bg-primary">Sports</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item-wrapper" data-category="Achievements" data-title="Science Fair Winners" data-description="Proud winners of the inter-school science fair">
                        <div class="gallery-item card border-0 shadow-sm h-100">
                            <img src="/images/gallery/science-fair-1.jpg" class="card-img-top gallery-img" alt="Science Fair Winners" onerror="this.src='/images/default-gallery.jpg'">
                            <div class="gallery-overlay">
                                <div class="text-center text-white">
                                    <i class="bi bi-zoom-in display-4 mb-2"></i>
                                    <h6>Science Fair Winners</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Science Fair Winners</h6>
                                <p class="card-text small text-muted">Proud winners of the inter-school science fair...</p>
                                <span class="badge bg-primary">Achievements</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item-wrapper" data-category="Cultural" data-title="Cultural Dance Performance" data-description="Students showcasing traditional dance forms">
                        <div class="gallery-item card border-0 shadow-sm h-100">
                            <img src="/images/gallery/cultural-1.jpg" class="card-img-top gallery-img" alt="Cultural Dance Performance" onerror="this.src='/images/default-gallery.jpg'">
                            <div class="gallery-overlay">
                                <div class="text-center text-white">
                                    <i class="bi bi-zoom-in display-4 mb-2"></i>
                                    <h6>Cultural Dance Performance</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Cultural Dance Performance</h6>
                                <p class="card-text small text-muted">Students showcasing traditional dance forms...</p>
                                <span class="badge bg-primary">Cultural</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary btn-lg" id="loadMoreBtn" style="display: none;">Load More</button>
            </div>
        </div>
    </section>

    <!-- Media Modal -->
    <div class="modal fade" id="mediaModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div id="modalMediaContainer">
                        <!-- Image or Video will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <h5 class="mb-2" id="modalTitle"></h5>
                        <p class="mb-2 text-muted" id="modalDescription"></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="modalMeta">
                                <!-- Category, tags, etc. -->
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="previousMedia()">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="nextMedia()">
                                    Next <i class="bi bi-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Custom JavaScript for Gallery -->
    <script>
        let currentMediaIndex = 0;
        let visibleItems = [];

        document.addEventListener('DOMContentLoaded', function() {
            // Gallery filtering
            const categoryFilters = document.querySelectorAll('.category-filter');
            const searchInput = document.getElementById('searchInput');

            // Initialize visible items
            updateVisibleItems();

            // Category filtering
            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');

                    // Update active filter
                    categoryFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');

                    // Filter items
                    filterItems();
                });
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                filterItems();
            });

            // Modal functionality
            const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.addEventListener('click', function(e) {
                if (e.target.closest('.gallery-item')) {
                    const item = e.target.closest('.gallery-item-wrapper');
                    const index = Array.from(visibleItems).indexOf(item);

                    if (index !== -1) {
                        currentMediaIndex = index;
                        showMediaModal(item);
                        mediaModal.show();
                    }
                }
            });

            // Keyboard navigation for modal
            document.addEventListener('keydown', function(e) {
                if (document.getElementById('mediaModal').classList.contains('show')) {
                    if (e.key === 'ArrowLeft') {
                        previousMedia();
                    } else if (e.key === 'ArrowRight') {
                        nextMedia();
                    } else if (e.key === 'Escape') {
                        bootstrap.Modal.getInstance(document.getElementById('mediaModal')).hide();
                    }
                }
            });
        });

        function updateVisibleItems() {
            visibleItems = Array.from(document.querySelectorAll('.gallery-item-wrapper')).filter(item => {
                return item.style.display !== 'none';
            });
        }

        function filterItems() {
            const categoryFilters = document.querySelectorAll('.category-filter');
            const activeFilter = document.querySelector('.category-filter.active');
            const category = activeFilter ? activeFilter.getAttribute('data-category') : 'all';
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const galleryItems = document.querySelectorAll('.gallery-item-wrapper');

            galleryItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                const itemTitle = item.getAttribute('data-title').toLowerCase();
                const itemDescription = item.getAttribute('data-description').toLowerCase();

                const categoryMatch = category === 'all' || itemCategory === category;
                const searchMatch = !searchTerm ||
                    itemTitle.includes(searchTerm) ||
                    itemDescription.includes(searchTerm) ||
                    itemCategory.toLowerCase().includes(searchTerm);

                if (categoryMatch && searchMatch) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            updateVisibleItems();
        }

        function showMediaModal(item) {
            const mediaType = item.getAttribute('data-media-type');
            const mediaPath = item.getAttribute('data-media-path');
            const title = item.getAttribute('data-title');
            const description = item.getAttribute('data-description');
            const category = item.getAttribute('data-category');

            const modalMediaContainer = document.getElementById('modalMediaContainer');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            const modalMeta = document.getElementById('modalMeta');

            // Clear previous content
            modalMediaContainer.innerHTML = '';

            // Create media element
            if (mediaType === 'video') {
                const video = document.createElement('video');
                video.className = 'w-100';
                video.style.maxHeight = '70vh';
                video.controls = true;
                video.autoplay = false;

                const source = document.createElement('source');
                source.src = mediaPath;
                source.type = 'video/mp4';

                video.appendChild(source);
                modalMediaContainer.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = mediaPath;
                img.className = 'img-fluid w-100';
                img.style.maxHeight = '70vh';
                img.alt = title;
                modalMediaContainer.appendChild(img);
            }

            // Update modal content
            modalTitle.textContent = title;
            modalDescription.textContent = description || '';

            // Update meta information
            modalMeta.innerHTML = '';
            if (category) {
                modalMeta.innerHTML += `<span class="badge bg-primary me-2">${category}</span>`;
            }
            modalMeta.innerHTML += `<small class="text-muted">${currentMediaIndex + 1} of ${visibleItems.length}</small>`;
        }

        function previousMedia() {
            if (visibleItems.length === 0) return;

            currentMediaIndex = currentMediaIndex > 0 ? currentMediaIndex - 1 : visibleItems.length - 1;
            showMediaModal(visibleItems[currentMediaIndex]);
        }

        function nextMedia() {
            if (visibleItems.length === 0) return;

            currentMediaIndex = currentMediaIndex < visibleItems.length - 1 ? currentMediaIndex + 1 : 0;
            showMediaModal(visibleItems[currentMediaIndex]);
        }

        // Lazy loading for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.gallery-img');

            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                if (img.dataset.src) {
                    imageObserver.observe(img);
                }
            });
        });
    </script>
</body>
</html>