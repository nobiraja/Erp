<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Events Calendar'); ?></title>
    <meta name="description" content="View events in calendar format">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #343a40;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: #007bff;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        .main-content.expanded {
            margin-left: 70px;
        }
        .hamburger-menu {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .hamburger-menu {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        .fc-event {
            cursor: pointer;
        }
        .fc-event:hover {
            opacity: 0.8;
        }
        .event-details-modal .modal-dialog {
            max-width: 500px;
        }
        .calendar-controls {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Menu -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <img src="/images/logo-small.png" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle">SMS</span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/students">
                        <i class="bi bi-people"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/teachers">
                        <i class="bi bi-person-badge"></i>
                        <span>Teachers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/classes">
                        <i class="bi bi-book"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/attendance">
                        <i class="bi bi-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/exams">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/fees">
                        <i class="bi bi-cash"></i>
                        <span>Fees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/events">
                        <i class="bi bi-calendar-event"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/gallery">
                        <i class="bi bi-images"></i>
                        <span>Gallery</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/settings">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>

            <!-- User Profile Section -->
            <div class="mt-auto pt-4 border-top">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-circle fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-2" id="userInfo">
                        <div class="fw-bold small">Admin</div>
                        <div class="text-muted small">Administrator</div>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="/logout" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="bg-white shadow-sm border-bottom">
            <div class="d-flex align-items-center justify-content-between px-4 py-3">
                <div class="d-flex align-items-center">
                    <button class="hamburger-menu d-md-none me-3" id="mobileMenuToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <h5 class="mb-0">Events Calendar</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/events">Events</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Calendar</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/events" class="btn btn-outline-primary">
                        <i class="bi bi-list me-1"></i>List View
                    </a>
                    <a href="/admin/events/create" class="btn btn-primary">
                        <i class="bi bi-calendar-plus me-1"></i>Add Event
                    </a>
                </div>
            </div>
        </header>

        <!-- Calendar Content -->
        <main class="p-4">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Calendar Controls -->
            <div class="calendar-controls">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar me-2"></i>
                            <?php echo date('F Y', strtotime($current_year . '-' . $current_month . '-01')); ?>
                        </h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="today">Today</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade event-details-modal" id="eventDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="editEventBtn" class="btn btn-primary">Edit Event</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Desktop sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');

                    const icon = this.querySelector('i');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.className = 'bi bi-chevron-right';
                    } else {
                        icon.className = 'bi bi-chevron-left';
                    }
                });
            }

            // Mobile menu toggle
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Initialize FullCalendar
            initializeCalendar();
        });

        let calendar;
        let currentYear = <?php echo $current_year; ?>;
        let currentMonth = <?php echo $current_month; ?>;

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`,
                headerToolbar: false, // We use custom controls
                height: 'auto',
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Fetch events for the current month
                    fetch(`/admin/events/ajax-calendar?year=${currentYear}&month=${currentMonth}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const events = data.data.map(event => ({
                                    id: event.id,
                                    title: event.title,
                                    start: event.date,
                                    allDay: true,
                                    extendedProps: {
                                        time: event.time,
                                        location: event.location,
                                        description: event.description,
                                        image: event.image,
                                        is_active: event.is_active
                                    }
                                }));
                                successCallback(events);
                            } else {
                                successCallback([]);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                eventDidMount: function(info) {
                    // Add custom styling based on event status
                    if (!info.event.extendedProps.is_active) {
                        info.el.style.opacity = '0.6';
                        info.el.style.textDecoration = 'line-through';
                    }
                }
            });

            calendar.render();
        }

        function showEventDetails(event) {
            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            const modalTitle = document.getElementById('eventModalTitle');
            const modalBody = document.getElementById('eventModalBody');
            const editBtn = document.getElementById('editEventBtn');

            modalTitle.textContent = event.title;

            const eventDate = new Date(event.start);
            const formattedDate = eventDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            let eventHtml = `
                <div class="event-details">
                    <div class="mb-3">
                        <strong>Date:</strong> ${formattedDate}
                    </div>
            `;

            if (event.extendedProps.time) {
                eventHtml += `
                    <div class="mb-3">
                        <strong>Time:</strong> ${event.extendedProps.time}
                    </div>
                `;
            }

            if (event.extendedProps.location) {
                eventHtml += `
                    <div class="mb-3">
                        <strong>Location:</strong> ${event.extendedProps.location}
                    </div>
                `;
            }

            if (event.extendedProps.description) {
                eventHtml += `
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="mt-2">${event.extendedProps.description}</p>
                    </div>
                `;
            }

            if (event.extendedProps.image) {
                eventHtml += `
                    <div class="mb-3">
                        <strong>Image:</strong>
                        <div class="mt-2">
                            <img src="${event.extendedProps.image}" alt="${event.title}" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                `;
            }

            eventHtml += `
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge ${event.extendedProps.is_active ? 'bg-success' : 'bg-secondary'} ms-2">
                            ${event.extendedProps.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
            `;

            modalBody.innerHTML = eventHtml;
            editBtn.href = `/admin/events/${event.id}/edit`;

            modal.show();
        }

        // Calendar navigation
        document.getElementById('prevMonth').addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            updateCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            updateCalendar();
        });

        document.getElementById('today').addEventListener('click', function() {
            const today = new Date();
            currentYear = today.getFullYear();
            currentMonth = today.getMonth() + 1;
            updateCalendar();
        });

        function updateCalendar() {
            calendar.gotoDate(`${currentYear}-${String(currentMonth).padStart(2, '0')}-01`);
            calendar.refetchEvents();

            // Update header
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.querySelector('.calendar-controls h6').innerHTML =
                `<i class="bi bi-calendar me-2"></i>${monthNames[currentMonth - 1]} ${currentYear}`;
        }
    </script>
</body>
</html>