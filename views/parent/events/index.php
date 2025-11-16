<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .event-card {
            transition: transform 0.2s ease-in-out;
            margin-bottom: 1.5rem;
        }
        .event-card:hover {
            transform: translateY(-2px);
        }
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 1.5rem;
        }
        .event-content {
            background: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            margin-top: 1rem;
        }
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
        }
        .calendar-day.has-event {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .calendar-day.today {
            background-color: #1976d2;
            color: white;
        }
        .calendar-day.empty {
            background-color: transparent;
        }
        .calendar-header {
            text-align: center;
            font-weight: bold;
            padding: 0.5rem;
            background-color: #f8f9fa;
        }
        .event-type-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .event-academic { background-color: #28a745; }
        .event-cultural { background-color: #dc3545; }
        .event-sports { background-color: #ffc107; }
        .event-other { background-color: #17a2b8; }
        .filter-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-house-heart-fill me-2"></i>
                Parent Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/children">
                            <i class="bi bi-people me-1"></i>My Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/parent/events">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>Parent
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">School Events</h2>
                        <p class="text-muted mb-0">Stay updated with upcoming school events and activities</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="monthSelect" style="width: auto;">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == date('n')) ? 'selected' : '';
                                echo "<option value='$i' $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                            }
                            ?>
                        </select>
                        <select class="form-select" id="yearSelect" style="width: auto;">
                            <?php
                            $currentYear = date('Y');
                            for ($i = $currentYear - 1; $i <= $currentYear + 1; $i++) {
                                $selected = ($i == $currentYear) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary" onclick="loadEvents()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-buttons">
                    <button class="btn btn-outline-primary active" onclick="filterEvents('all')">All Events</button>
                    <button class="btn btn-outline-success" onclick="filterEvents('academic')">Academic</button>
                    <button class="btn btn-outline-danger" onclick="filterEvents('cultural')">Cultural</button>
                    <button class="btn btn-outline-warning" onclick="filterEvents('sports')">Sports</button>
                    <button class="btn btn-outline-info" onclick="filterEvents('other')">Other</button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Events List -->
            <div class="col-lg-8 mb-4">
                <div id="events-container">
                    <?php if (empty($events)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x" style="font-size: 4rem; color: #6c757d;"></i>
                            <h4 class="mt-3 text-muted">No Upcoming Events</h4>
                            <p class="text-muted">There are no events scheduled at the moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="event-card" data-event-type="<?php echo htmlspecialchars($event['event_type'] ?? 'other'); ?>">
                                <div class="event-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                <?php echo htmlspecialchars($event['title'] ?? ''); ?>
                                            </h5>
                                            <p class="mb-0 opacity-75">
                                                <?php echo date('l, F d, Y', strtotime($event['event_date'] ?? '')); ?>
                                                <?php if (!empty($event['event_time'])): ?>
                                                    at <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-primary">
                                                <?php echo htmlspecialchars($event['event_type'] ?? 'Other'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="event-content">
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php if (!empty($event['venue'])): ?>
                                                    <p class="mb-1">
                                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                                        <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($event['organizer'])): ?>
                                                    <p class="mb-1">
                                                        <i class="bi bi-person text-primary me-2"></i>
                                                        <strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizer']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button class="btn btn-primary btn-sm" onclick="viewEventDetails(<?php echo $event['id']; ?>)">
                                                    <i class="bi bi-eye me-1"></i>View Details
                                                </button>
                                                <?php if (!empty($event['registration_required']) && $event['registration_required']): ?>
                                                    <?php if (!empty($event['is_registered']) && $event['is_registered']): ?>
                                                        <button class="btn btn-success btn-sm ms-2" disabled>
                                                            <i class="bi bi-check-circle me-1"></i>Registered
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-success btn-sm ms-2" onclick="registerForEvent(<?php echo $event['id']; ?>)">
                                                            <i class="bi bi-person-plus me-1"></i>Register
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Calendar Sidebar -->
            <div class="col-lg-4 mb-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar me-2"></i>Event Calendar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="event-calendar">
                            <!-- Calendar will be generated here -->
                        </div>
                        <div class="mt-3">
                            <h6>Legend</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <small class="badge bg-light text-primary">
                                    <span class="event-type-badge event-academic"></span> Academic
                                </small>
                                <small class="badge bg-light text-danger">
                                    <span class="event-type-badge event-cultural"></span> Cultural
                                </small>
                                <small class="badge bg-light text-warning">
                                    <span class="event-type-badge event-sports"></span> Sports
                                </small>
                                <small class="badge bg-light text-info">
                                    <span class="event-type-badge event-other"></span> Other
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details Modal -->
        <div class="modal fade" id="eventDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Event Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="eventDetailsContent">
                        <!-- Event details will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="registerBtn" style="display: none;">Register for Event</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX Functions -->
    <script>
        let currentFilter = 'all';
        let eventsData = <?php echo json_encode($events); ?>;

        // Show alert messages
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container') || createAlertContainer();
            const alertId = 'alert-' + Date.now();

            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            alertContainer.insertAdjacentHTML('beforeend', alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }

        // Create alert container if it doesn't exist
        function createAlertContainer() {
            const container = document.createElement('div');
            container.id = 'alert-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
            return container;
        }

        // Load events
        function loadEvents() {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;

            // Fetch events from server
            fetch('/parent/getEventsByMonth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `year=${year}&month=${month}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    eventsData = data.data;
                    renderEvents(eventsData);
                    generateCalendar(month, year);
                } else {
                    showAlert('danger', data.message || 'Failed to load events');
                }
            })
            .catch(error => {
                console.error('Load events error:', error);
                showAlert('danger', 'An error occurred while loading events');
            });
        }

        // Render events in the container
        function renderEvents(events) {
            const container = document.getElementById('events-container');

            if (!events || events.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="mt-3 text-muted">No Upcoming Events</h4>
                        <p class="text-muted">There are no events scheduled at the moment.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            events.forEach(event => {
                const eventType = event.event_type || 'other';
                const isRegistered = event.is_registered || false;

                html += `
                    <div class="event-card" data-event-type="${eventType}">
                        <div class="event-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        ${escapeHtml(event.title || '')}
                                    </h5>
                                    <p class="mb-0 opacity-75">
                                        ${new Date(event.event_date).toLocaleDateString('en-US', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        })}
                                        ${event.event_time ? `at ${new Date('1970-01-01T' + event.event_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}` : ''}
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-primary">
                                        ${escapeHtml(eventType.charAt(0).toUpperCase() + eventType.slice(1))}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="event-content">
                            <div class="card-body">
                                <p class="card-text">${escapeHtml(event.description || '')}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        ${event.venue ? `<p class="mb-1"><i class="bi bi-geo-alt text-primary me-2"></i><strong>Venue:</strong> ${escapeHtml(event.venue)}</p>` : ''}
                                        ${event.organizer ? `<p class="mb-1"><i class="bi bi-person text-primary me-2"></i><strong>Organizer:</strong> ${escapeHtml(event.organizer)}</p>` : ''}
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button class="btn btn-primary btn-sm" onclick="viewEventDetails(${event.id})">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </button>
                                        ${event.registration_required ? `
                                            ${isRegistered ? `
                                                <button class="btn btn-success btn-sm ms-2" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>Registered
                                                </button>
                                            ` : `
                                                <button class="btn btn-success btn-sm ms-2" onclick="registerForEvent(${event.id})">
                                                    <i class="bi bi-person-plus me-1"></i>Register
                                                </button>
                                            `}
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Filter events
        function filterEvents(type) {
            currentFilter = type;

            // Update button states
            document.querySelectorAll('.filter-buttons .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter events
            const eventsContainer = document.getElementById('events-container');
            const eventCards = eventsContainer.querySelectorAll('.event-card');

            eventCards.forEach(card => {
                if (type === 'all' || card.dataset.eventType === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // View event details
        function viewEventDetails(eventId) {
            // Fetch event details from server
            fetch('/parent/getEventDetails', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `event_id=${eventId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const event = data.data;
                    showEventDetailsModal(event);
                } else {
                    showAlert('danger', data.message || 'Failed to load event details');
                }
            })
            .catch(error => {
                console.error('Event details error:', error);
                showAlert('danger', 'An error occurred while loading event details');
            });
        }

        // Show event details modal
        function showEventDetailsModal(event) {
            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            const content = document.getElementById('eventDetailsContent');
            const registerBtn = document.getElementById('registerBtn');

            content.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h4>${escapeHtml(event.title || '')}</h4>
                        <p class="text-muted">${escapeHtml(event.description || '')}</p>

                        <div class="row mt-3">
                            <div class="col-6">
                                <p><strong>Date:</strong> ${new Date(event.event_date).toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })}</p>
                                ${event.event_time ? `<p><strong>Time:</strong> ${new Date('1970-01-01T' + event.event_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>` : ''}
                                ${event.registration_deadline ? `<p><strong>Registration Deadline:</strong> ${new Date(event.registration_deadline).toLocaleDateString('en-US')}</p>` : ''}
                            </div>
                            <div class="col-6">
                                ${event.location ? `<p><strong>Location:</strong> ${escapeHtml(event.location)}</p>` : ''}
                                ${event.venue ? `<p><strong>Venue:</strong> ${escapeHtml(event.venue)}</p>` : ''}
                                ${event.organizer ? `<p><strong>Organizer:</strong> ${escapeHtml(event.organizer)}</p>` : ''}
                                ${event.contact_info ? `<p><strong>Contact:</strong> ${escapeHtml(event.contact_info)}</p>` : ''}
                            </div>
                        </div>

                        ${event.additional_info ? `<div class="mt-3"><strong>Additional Information:</strong><p>${escapeHtml(event.additional_info)}</p></div>` : ''}

                        ${event.max_participants ? `<div class="mt-3"><strong>Capacity:</strong> ${event.registration_count || 0} / ${event.max_participants} registered</div>` : ''}
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <span class="badge bg-primary fs-6 mb-2">${escapeHtml((event.event_type || 'other').charAt(0).toUpperCase() + (event.event_type || 'other').slice(1))}</span>
                                ${event.registration_required ?
                                    '<div class="alert alert-info small">Registration Required</div>' :
                                    '<div class="alert alert-success small">Open to All</div>'
                                }
                                ${event.is_registered ?
                                    '<div class="alert alert-success small mt-2">You are registered for this event</div>' :
                                    ''
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;

            if (event.registration_required && !event.is_registered) {
                registerBtn.style.display = 'inline-block';
                registerBtn.innerHTML = 'Register for Event';
                registerBtn.onclick = () => registerForEvent(event.id);
            } else if (event.is_registered) {
                registerBtn.style.display = 'inline-block';
                registerBtn.innerHTML = 'Cancel Registration';
                registerBtn.className = 'btn btn-danger';
                registerBtn.onclick = () => cancelEventRegistration(event.id);
            } else {
                registerBtn.style.display = 'none';
            }

            modal.show();
        }

        // Cancel event registration
        function cancelEventRegistration(eventId) {
            if (!confirm('Are you sure you want to cancel your registration for this event?')) {
                return;
            }

            fetch('/parent/cancelEventRegistration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `event_id=${eventId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message || 'Registration cancelled successfully');
                    // Close modal and refresh
                    bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
                    loadEvents();
                } else {
                    showAlert('danger', data.message || 'Failed to cancel registration');
                }
            })
            .catch(error => {
                console.error('Cancel registration error:', error);
                showAlert('danger', 'An error occurred while cancelling registration');
            });
        }

        // Register for event
        function registerForEvent(eventId) {
            // Show loading state
            const registerBtn = event.target;
            const originalText = registerBtn.innerHTML;
            registerBtn.innerHTML = '<i class="bi bi-hourglass me-1"></i>Registering...';
            registerBtn.disabled = true;

            // Send registration request
            fetch('/parent/registerForEvent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `event_id=${eventId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message || 'Successfully registered for the event!');

                    // Update button state
                    registerBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Registered';
                    registerBtn.className = 'btn btn-success btn-sm';
                    registerBtn.disabled = true;

                    // Refresh events to show updated status
                    loadEvents();
                } else {
                    showAlert('danger', data.message || 'Failed to register for the event');
                    registerBtn.innerHTML = originalText;
                    registerBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                showAlert('danger', 'An error occurred while registering. Please try again.');
                registerBtn.innerHTML = originalText;
                registerBtn.disabled = false;
            });
        }

        // Generate calendar
        function generateCalendar(month, year) {
            const calendarElement = document.getElementById('event-calendar');
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();

            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];

            let html = '';

            // Month header
            html += `<div class="calendar-header">${monthNames[month - 1]} ${year}</div>`;

            // Day headers
            dayNames.forEach(day => {
                html += `<div class="calendar-day calendar-header">${day}</div>`;
            });

            // Empty cells for days before the first day of the month
            for (let i = 0; i < startingDayOfWeek; i++) {
                html += `<div class="calendar-day empty"></div>`;
            }

            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                const isToday = new Date().toDateString() === new Date(dateStr).toDateString();

                // Check if there's an event on this day
                const eventsOnDay = eventsData.filter(event =>
                    event.event_date === dateStr
                );

                let dayClass = 'calendar-day';
                if (isToday) dayClass += ' today';
                if (eventsOnDay.length > 0) dayClass += ' has-event';

                html += `<div class="${dayClass}" data-date="${dateStr}" onclick="showDayEvents('${dateStr}')">
                    ${day}
                    ${eventsOnDay.length > 0 ? `<span class="event-type-badge event-${eventsOnDay[0].event_type || 'other'}"></span>` : ''}
                </div>`;
            }

            calendarElement.innerHTML = html;
        }

        // Show events for a specific day
        function showDayEvents(dateStr) {
            const eventsOnDay = eventsData.filter(event => event.event_date === dateStr);

            if (eventsOnDay.length === 0) {
                alert('No events on this day');
                return;
            }

            // Show the first event's details
            viewEventDetails(eventsOnDay[0].id);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial events
            loadEvents();
        });
    </script>
</body>
</html>