<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js for trend analysis -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <style>
        .calendar-day {
            height: 80px;
            border: 1px solid #dee2e6;
            padding: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .calendar-day:hover {
            background-color: #f8f9fa;
        }
        .calendar-day.today {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .attendance-present {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .attendance-absent {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .attendance-late {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
        .attendance-no-record {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .stats-card {
            transition: transform 0.2s ease-in-out;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .calendar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .trend-chart {
            max-height: 300px;
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .attendance-history-table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 4px 8px;
        }
        @media (max-width: 768px) {
            .calendar-day {
                height: 60px;
                font-size: 0.9em;
            }
            .stats-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Student Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/student/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/student/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($student_data['first_name'] ?? 'Student'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="bi bi-calendar-check text-primary me-2"></i>My Attendance
                        </h2>
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars(($student_data['first_name'] ?? '') . ' ' . ($student_data['last_name'] ?? '')); ?> |
                            <?php echo htmlspecialchars($student_data['class_name'] ?? ''); ?> - <?php echo htmlspecialchars($student_data['section'] ?? ''); ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="h3 text-primary mb-0"><?php echo $attendance_stats['attendance_percentage']; ?>%</div>
                        <small class="text-muted">Overall Attendance</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100 border-success">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1 text-success"><?php echo $attendance_stats['present_days']; ?></div>
                        <div class="text-muted">Present Days</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100 border-danger">
                    <div class="card-body text-center">
                        <div class="text-danger mb-2">
                            <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1 text-danger"><?php echo $attendance_stats['absent_days']; ?></div>
                        <div class="text-muted">Absent Days</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100 border-warning">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1 text-warning"><?php echo $attendance_stats['late_days']; ?></div>
                        <div class="text-muted">Late Days</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100 border-info">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="bi bi-calendar-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1 text-info"><?php echo $attendance_stats['total_days']; ?></div>
                        <div class="text-muted">Total Days</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" value="<?php echo date('Y-m-t'); ?>">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" onclick="filterAttendance()">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success w-100" onclick="exportAttendance()">
                        <i class="bi bi-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Monthly Calendar -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header calendar-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar me-2"></i>
                                Monthly Calendar - <?php echo date('F Y', strtotime($current_year . '-' . $current_month . '-01')); ?>
                            </h5>
                            <div class="btn-group">
                                <button class="btn btn-outline-light btn-sm" onclick="changeMonth(-1)">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button class="btn btn-outline-light btn-sm" onclick="changeMonth(1)">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar-container">
                            <!-- Calendar will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Trend Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Attendance Trends
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" class="trend-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>Attendance History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover attendance-history-table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Subject</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    <!-- Attendance records will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary" onclick="loadMoreHistory()">
                                <i class="bi bi-arrow-down-circle me-1"></i>Load More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let currentMonth = <?php echo $current_month; ?>;
        let currentYear = <?php echo $current_year; ?>;
        let attendanceData = <?php echo json_encode($attendance_data); ?>;
        let monthlyTrends = <?php echo json_encode($monthly_trends); ?>;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCalendar();
            loadAttendanceHistory();
            renderTrendChart();
        });

        // Load monthly calendar
        function loadCalendar() {
            fetch('/student/attendance/getMonthlyCalendar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `month=${currentMonth}&year=${currentYear}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCalendar(data.data.calendar, data.data.month, data.data.year);
                }
            })
            .catch(error => console.error('Error loading calendar:', error));
        }

        // Render calendar
        function renderCalendar(calendarData, month, year) {
            const container = document.getElementById('calendar-container');
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            let html = '<div class="calendar-grid">';
            html += '<div class="row mb-2">';
            dayNames.forEach(day => {
                html += `<div class="col text-center fw-bold">${day}</div>`;
            });
            html += '</div>';

            calendarData.forEach(week => {
                html += '<div class="row">';
                week.forEach(day => {
                    if (day === null) {
                        html += '<div class="col calendar-day"></div>';
                    } else {
                        const today = new Date().toDateString() === new Date(year, month-1, day.day).toDateString();
                        const statusClass = getStatusClass(day.status);
                        html += `<div class="col calendar-day ${statusClass} ${today ? 'today' : ''}" title="${getStatusText(day.status)}">
                                    <div class="fw-bold">${day.day}</div>
                                    <small class="status-indicator">${getStatusIcon(day.status)}</small>
                                </div>`;
                    }
                });
                html += '</div>';
            });
            html += '</div>';

            container.innerHTML = html;
        }

        // Load attendance history
        function loadAttendanceHistory(startDate = null, endDate = null) {
            const formData = new FormData();
            if (startDate) formData.append('start_date', startDate);
            if (endDate) formData.append('end_date', endDate);

            fetch('/student/attendance/getAttendanceHistory', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAttendanceTable(data.data.attendance);
                    updateStats(data.data.stats);
                }
            })
            .catch(error => console.error('Error loading attendance history:', error));
        }

        // Render attendance table
        function renderAttendanceTable(attendance) {
            const tbody = document.getElementById('attendanceTableBody');
            let html = '';

            if (attendance.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted py-4">No attendance records found for the selected period.</td></tr>';
            } else {
                attendance.forEach(record => {
                    const date = new Date(record.attendance_date);
                    const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                    const statusBadge = getStatusBadge(record.status);

                    html += `<tr>
                        <td>${date.toLocaleDateString()}</td>
                        <td>${dayName}</td>
                        <td>${statusBadge}</td>
                        <td>${record.subject_name || 'General'}</td>
                        <td>${record.remarks || '-'}</td>
                    </tr>`;
                });
            }

            tbody.innerHTML = html;
        }

        // Update statistics display
        function updateStats(stats) {
            // Update the stats cards with new data
            const presentEl = document.querySelector('.text-success.h4');
            const absentEl = document.querySelector('.text-danger.h4');
            const lateEl = document.querySelector('.text-warning.h4');
            const totalEl = document.querySelector('.text-info.h4');
            const percentageEl = document.querySelector('.h3.text-primary');

            if (presentEl) presentEl.textContent = stats.present_days;
            if (absentEl) absentEl.textContent = stats.absent_days;
            if (lateEl) lateEl.textContent = stats.late_days;
            if (totalEl) totalEl.textContent = stats.total_days;
            if (percentageEl) percentageEl.textContent = stats.attendance_percentage + '%';
        }

        // Render trend chart
        function renderTrendChart() {
            const ctx = document.getElementById('trendChart').getContext('2d');

            const labels = monthlyTrends.map(trend => {
                const [year, month] = trend.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });

            const data = monthlyTrends.map(trend => trend.avg_percentage);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance Percentage',
                        data: data,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Filter attendance
        function filterAttendance() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }

            loadAttendanceHistory(startDate, endDate);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('startDate').value = '<?php echo date('Y-m-01'); ?>';
            document.getElementById('endDate').value = '<?php echo date('Y-m-t'); ?>';
            loadAttendanceHistory();
        }

        // Change month
        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            loadCalendar();
        }

        // Export attendance
        function exportAttendance() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            window.open(`/student/attendance/export?start_date=${startDate}&end_date=${endDate}`, '_blank');
        }

        // Load more history (placeholder)
        function loadMoreHistory() {
            // Implement pagination if needed
            console.log('Load more history - not implemented yet');
        }

        // Helper functions
        function getStatusClass(status) {
            switch (status) {
                case 'present': return 'attendance-present';
                case 'absent': return 'attendance-absent';
                case 'late': return 'attendance-late';
                default: return 'attendance-no-record';
            }
        }

        function getStatusText(status) {
            switch (status) {
                case 'present': return 'Present';
                case 'absent': return 'Absent';
                case 'late': return 'Late';
                default: return 'No Record';
            }
        }

        function getStatusIcon(status) {
            switch (status) {
                case 'present': return '✓';
                case 'absent': return '✗';
                case 'late': return '⏰';
                default: return '';
            }
        }

        function getStatusBadge(status) {
            const statusMap = {
                'present': '<span class="badge bg-success status-badge">Present</span>',
                'absent': '<span class="badge bg-danger status-badge">Absent</span>',
                'late': '<span class="badge bg-warning status-badge">Late</span>',
                'no_record': '<span class="badge bg-secondary status-badge">No Record</span>'
            };
            return statusMap[status] || statusMap['no_record'];
        }
    </script>
</body>
</html>