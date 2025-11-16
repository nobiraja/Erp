<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Subject Schedule'); ?></title>
    <meta name="description" content="Manage exam subject schedules in the school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

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
        .schedule-row {
            transition: background-color 0.2s;
        }
        .schedule-row:hover {
            background-color: #f8f9fa;
        }
        .time-input {
            width: 100px;
        }
        .date-input {
            width: 120px;
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
                    <a class="nav-link active" href="/admin/exams">
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
                    <a class="nav-link" href="/admin/events">
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
                        <h5 class="mb-0">Subject Schedule - <?php echo htmlspecialchars($exam->exam_name); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Schedule</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/exams" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Exams
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="generateAdmitCards()">
                                <i class="bi bi-card-text me-2"></i>Generate Admit Cards
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/exams/results?exam_id=<?php echo $exam->id; ?>">
                                <i class="bi bi-pencil-square me-2"></i>Enter Results
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="exportSchedule()">
                                <i class="bi bi-download me-2"></i>Export Schedule
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Schedule Content -->
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

            <!-- Exam Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1"><?php echo htmlspecialchars($exam->exam_name); ?></h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($exam->class()->class_name . ' ' . $exam->class()->section); ?> |
                                <i class="bi bi-calendar me-1"></i><?php echo date('d/m/Y', strtotime($exam->start_date)); ?> - <?php echo date('d/m/Y', strtotime($exam->end_date)); ?> |
                                <i class="bi bi-tag me-1"></i><?php echo ucfirst(str_replace('-', ' ', $exam->exam_type)); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-<?php echo $exam->is_active ? 'success' : 'danger'; ?> fs-6">
                                <?php echo $exam->is_active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Form -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar me-2"></i>
                        Subject Schedule
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="addSubjectRow()">
                            <i class="bi bi-plus-circle me-1"></i>Add Subject
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="saveSchedule()">
                            <i class="bi bi-check-circle me-1"></i>Save Schedule
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="scheduleForm">
                        <input type="hidden" name="exam_id" value="<?php echo $exam->id; ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered" id="scheduleTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Subject</th>
                                        <th width="15%">Exam Date</th>
                                        <th width="10%">Day</th>
                                        <th width="15%">Start Time</th>
                                        <th width="15%">End Time</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleBody">
                                    <?php $index = 0; ?>
                                    <?php foreach ($schedule as $item): ?>
                                        <tr class="schedule-row" data-index="<?php echo $index; ?>">
                                            <td class="text-center"><?php echo $index + 1; ?></td>
                                            <td>
                                                <select class="form-select form-select-sm" name="schedule[<?php echo $index; ?>][subject_id]" required>
                                                    <option value="">Select Subject</option>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <option value="<?php echo $subject['id']; ?>" <?php echo $item['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm date-input"
                                                       name="schedule[<?php echo $index; ?>][exam_date]"
                                                       value="<?php echo $item['exam_date']; ?>"
                                                       min="<?php echo $exam->start_date; ?>" max="<?php echo $exam->end_date; ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="schedule[<?php echo $index; ?>][day]"
                                                       value="<?php echo htmlspecialchars($item['day']); ?>"
                                                       placeholder="e.g., Monday">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm time-input"
                                                       name="schedule[<?php echo $index; ?>][start_time]"
                                                       value="<?php echo $item['start_time']; ?>" required>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm time-input"
                                                       name="schedule[<?php echo $index; ?>][end_time]"
                                                       value="<?php echo $item['end_time']; ?>" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSubjectRow(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php $index++; ?>
                                    <?php endforeach; ?>

                                    <?php if (empty($schedule)): ?>
                                        <tr class="schedule-row" data-index="0">
                                            <td class="text-center">1</td>
                                            <td>
                                                <select class="form-select form-select-sm" name="schedule[0][subject_id]" required>
                                                    <option value="">Select Subject</option>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <option value="<?php echo $subject['id']; ?>">
                                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm date-input"
                                                       name="schedule[0][exam_date]"
                                                       min="<?php echo $exam->start_date; ?>" max="<?php echo $exam->end_date; ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="schedule[0][day]" placeholder="e.g., Monday">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm time-input"
                                                       name="schedule[0][start_time]" required>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm time-input"
                                                       name="schedule[0][end_time]" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSubjectRow(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Set exam dates, times, and days for each subject. Dates must be within the exam period.
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="clearSchedule()">
                                    <i class="bi bi-x-circle me-1"></i>Clear All
                                </button>
                                <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                                    <i class="bi bi-check-circle me-1"></i>Save Schedule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Summary -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-week me-2"></i>
                                Schedule Overview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary mb-1" id="totalSubjects"><?php echo count($schedule); ?></h4>
                                    <small class="text-muted">Total Subjects</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1" id="scheduledSubjects"><?php echo count(array_filter($schedule, function($item) { return !empty($item['exam_date']); })); ?></h4>
                                    <small class="text-muted">Scheduled</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-clock me-2"></i>
                                Time Distribution
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-info mb-1" id="earliestTime">-</h6>
                                    <small class="text-muted">Earliest Start</small>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-warning mb-1" id="latestTime">-</h6>
                                    <small class="text-muted">Latest End</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let rowIndex = <?php echo count($schedule); ?>;

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

            // Update summary on load
            updateScheduleSummary();

            // Add event listeners for real-time updates
            document.getElementById('scheduleBody').addEventListener('input', updateScheduleSummary);
            document.getElementById('scheduleBody').addEventListener('change', updateScheduleSummary);
        });

        // Add new subject row
        function addSubjectRow() {
            const tbody = document.getElementById('scheduleBody');
            const newRow = document.createElement('tr');
            newRow.className = 'schedule-row';
            newRow.setAttribute('data-index', rowIndex);

            newRow.innerHTML = `
                <td class="text-center">${rowIndex + 1}</td>
                <td>
                    <select class="form-select form-select-sm" name="schedule[${rowIndex}][subject_id]" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>">
                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm date-input"
                           name="schedule[${rowIndex}][exam_date]"
                           min="<?php echo $exam->start_date; ?>" max="<?php echo $exam->end_date; ?>" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm"
                           name="schedule[${rowIndex}][day]" placeholder="e.g., Monday">
                </td>
                <td>
                    <input type="time" class="form-control form-control-sm time-input"
                           name="schedule[${rowIndex}][start_time]" required>
                </td>
                <td>
                    <input type="time" class="form-control form-control-sm time-input"
                           name="schedule[${rowIndex}][end_time]" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSubjectRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(newRow);
            rowIndex++;
            updateRowNumbers();
        }

        // Remove subject row
        function removeSubjectRow(button) {
            const row = button.closest('tr');
            row.remove();
            updateRowNumbers();
            updateScheduleSummary();
        }

        // Update row numbers
        function updateRowNumbers() {
            const rows = document.querySelectorAll('#scheduleBody tr');
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
                row.setAttribute('data-index', index);

                // Update input names
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.name;
                    if (name) {
                        input.name = name.replace(/\[\d+\]/, `[${index}]`);
                    }
                });
            });
            rowIndex = rows.length;
        }

        // Save schedule
        function saveSchedule() {
            const formData = new FormData(document.getElementById('scheduleForm'));

            fetch('/admin/exams/save-schedule', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Schedule saved successfully!');
                    updateScheduleSummary();
                } else {
                    alert('Failed to save schedule: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error saving schedule');
                console.error('Save error:', error);
            });
        }

        // Clear schedule
        function clearSchedule() {
            if (confirm('Are you sure you want to clear the entire schedule?')) {
                document.querySelectorAll('#scheduleBody input, #scheduleBody select').forEach(input => {
                    input.value = '';
                });
                updateScheduleSummary();
            }
        }

        // Update schedule summary
        function updateScheduleSummary() {
            const rows = document.querySelectorAll('#scheduleBody tr');
            let totalSubjects = rows.length;
            let scheduledSubjects = 0;
            let earliestTime = null;
            let latestTime = null;

            rows.forEach(row => {
                const dateInput = row.querySelector('input[type="date"]');
                const startTimeInput = row.querySelector('input[name*="[start_time]"]');
                const endTimeInput = row.querySelector('input[name*="[end_time]"]');

                if (dateInput && dateInput.value) {
                    scheduledSubjects++;
                }

                if (startTimeInput && startTimeInput.value) {
                    if (!earliestTime || startTimeInput.value < earliestTime) {
                        earliestTime = startTimeInput.value;
                    }
                }

                if (endTimeInput && endTimeInput.value) {
                    if (!latestTime || endTimeInput.value > latestTime) {
                        latestTime = endTimeInput.value;
                    }
                }
            });

            document.getElementById('totalSubjects').textContent = totalSubjects;
            document.getElementById('scheduledSubjects').textContent = scheduledSubjects;
            document.getElementById('earliestTime').textContent = earliestTime || '-';
            document.getElementById('latestTime').textContent = latestTime || '-';
        }

        // Generate admit cards
        function generateAdmitCards() {
            window.open(`/admin/exams/admit-cards?exam_id=<?php echo $exam->id; ?>&format=bulk`, '_blank');
        }

        // Export schedule
        function exportSchedule() {
            window.open(`/admin/exams/export-schedule?exam_id=<?php echo $exam->id; ?>`, '_blank');
        }
    </script>
</body>
</html>