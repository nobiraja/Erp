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
        .child-attendance-card {
            transition: transform 0.2s ease-in-out;
            margin-bottom: 2rem;
        }
        .child-attendance-card:hover {
            transform: translateY(-2px);
        }
        .attendance-calendar {
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
        }
        .calendar-day.present { background-color: #d4edda; color: #155724; }
        .calendar-day.absent { background-color: #f8d7da; color: #721c24; }
        .calendar-day.late { background-color: #fff3cd; color: #856404; }
        .calendar-day.no-record { background-color: #f8f9fa; color: #6c757d; }
        .calendar-day.weekend { background-color: #e9ecef; color: #495057; }
        .calendar-day.empty { background-color: transparent; }
        .calendar-header {
            text-align: center;
            font-weight: bold;
            padding: 0.5rem;
            background-color: #f8f9fa;
        }
        .metric-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 auto;
        }
        .trend-chart {
            height: 200px;
        }
        .status-present { color: #28a745; }
        .status-absent { color: #dc3545; }
        .status-late { color: #ffc107; }
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
                        <a class="nav-link active" href="/parent/attendance">
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
                        <a class="nav-link" href="/parent/events">
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
                        <h2 class="mb-1">Attendance Tracking</h2>
                        <p class="text-muted mb-0">Monitor your children's attendance records and patterns</p>
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
                            for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                $selected = ($i == $currentYear) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary" onclick="loadAttendanceData()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Attendance Cards -->
        <?php foreach ($children as $child): ?>
            <div class="card child-attendance-card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($child['full_name']); ?>
                            </h5>
                            <small>
                                <?php echo htmlspecialchars($child['class_name'] . ' - ' . $child['class_section']); ?> |
                                Scholar No: <?php echo htmlspecialchars($child['scholar_number']); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="metric-circle bg-white text-success">
                                <?php echo $child['attendance_summary']['percentage']; ?>%<br>
                                <small>Present</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Attendance Summary -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-bar-chart me-2"></i>Attendance Summary
                            </h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 text-success mb-1"><?php echo $child['attendance_summary']['present_days']; ?></div>
                                    <small class="text-muted">Present</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-danger mb-1"><?php echo $child['attendance_summary']['absent_days']; ?></div>
                                    <small class="text-muted">Absent</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-warning mb-1"><?php echo $child['attendance_summary']['late_days']; ?></div>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?php echo $child['attendance_summary']['percentage']; ?>%"
                                         aria-valuenow="<?php echo $child['attendance_summary']['percentage']; ?>"
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <?php echo $child['attendance_summary']['percentage']; ?>% attendance rate
                                </small>
                            </div>
                        </div>

                        <!-- Monthly Calendar -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-calendar me-2"></i><?php echo date('F Y'); ?> Calendar
                            </h6>
                            <div class="attendance-calendar" id="calendar-<?php echo $child['id']; ?>">
                                <!-- Calendar will be populated by JavaScript -->
                            </div>
                        </div>

                        <!-- Monthly Trends -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-graph-up me-2"></i>12-Month Trend
                            </h6>
                            <div class="trend-chart">
                                <canvas id="trend-chart-<?php echo $child['id']; ?>"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Attendance Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-table me-2"></i>Recent Attendance Records
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendance-table-<?php echo $child['id']; ?>">
                                        <!-- Attendance records will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-success btn-sm" onclick="loadDetailedAttendance(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>View All Records
                                </button>
                                <button class="btn btn-success btn-sm" onclick="exportAttendanceReport(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-download me-1"></i>Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Attendance Details Modal -->
        <div class="modal fade" id="attendanceDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detailed Attendance Records</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="detailedAttendanceTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="detailedAttendanceBody">
                                    <!-- Detailed records will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportDetailedReport()">Export</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- AJAX Functions -->
    <script>
        let currentChildId = null;

        // Load attendance data for selected month/year
        function loadAttendanceData() {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;

            <?php foreach ($children as $child): ?>
                loadChildAttendance(<?php echo $child['id']; ?>, month, year);
                loadChildCalendar(<?php echo $child['id']; ?>, month, year);
                updateChildAttendanceSummary(<?php echo $child['id']; ?>, month, year);
                updateChildTrendChart(<?php echo $child['id']; ?>, month, year);
            <?php endforeach; ?>
        }

        // Update child trend chart
        function updateChildTrendChart(childId, month, year) {
            // For trend chart, we need to load the last 12 months data
            // This would require a separate endpoint or modify the existing one
            // For now, we'll keep the static data but could be enhanced later
            console.log(`Updating trend chart for child ${childId}, month ${month}, year ${year}`);
        }

        // Update child attendance summary
        function updateChildAttendanceSummary(childId, month, year) {
            const startDate = `${year}-${month.toString().padStart(2, '0')}-01`;
            const endDate = new Date(year, month, 0).toISOString().split('T')[0];

            fetch(`/parent/attendance/getChildAttendance?child_id=${childId}&start_date=${startDate}&end_date=${endDate}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const summary = calculateAttendanceSummary(data.data);
                    updateAttendanceSummaryUI(childId, summary);
                }
            })
            .catch(error => console.error('Error loading attendance summary:', error));
        }

        // Calculate attendance summary from records
        function calculateAttendanceSummary(records) {
            const totalDays = records.length;
            const presentDays = records.filter(r => r.status === 'present').length;
            const absentDays = records.filter(r => r.status === 'absent').length;
            const lateDays = records.filter(r => r.status === 'late').length;

            const percentage = totalDays > 0 ? Math.round((presentDays / totalDays) * 100) : 0;

            return {
                total_days: totalDays,
                present_days: presentDays,
                absent_days: absentDays,
                late_days: lateDays,
                percentage: percentage
            };
        }

        // Update attendance summary UI
        function updateAttendanceSummaryUI(childId, summary) {
            // Update percentage circle
            const percentageElement = document.querySelector(`#calendar-${childId}`).closest('.card').querySelector('.metric-circle');
            if (percentageElement) {
                percentageElement.innerHTML = `${summary.percentage}%<br><small>Present</small>`;
            }

            // Update summary numbers
            const cardBody = document.querySelector(`#calendar-${childId}`).closest('.card').querySelector('.card-body');
            const presentElement = cardBody.querySelector('.h4.text-success');
            const absentElement = cardBody.querySelector('.h4.text-danger');
            const lateElement = cardBody.querySelector('.h4.text-warning');

            if (presentElement) presentElement.textContent = summary.present_days;
            if (absentElement) absentElement.textContent = summary.absent_days;
            if (lateElement) lateElement.textContent = summary.late_days;

            // Update progress bar
            const progressBar = cardBody.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = `${summary.percentage}%`;
                progressBar.setAttribute('aria-valuenow', summary.percentage);
            }

            // Update progress text
            const progressText = cardBody.querySelector('.mt-1.d-block');
            if (progressText) {
                progressText.textContent = `${summary.percentage}% attendance rate`;
            }
        }

        // Load child attendance summary
        function loadChildAttendance(childId, month, year) {
            const startDate = `${year}-${month.toString().padStart(2, '0')}-01`;
            const endDate = new Date(year, month, 0).toISOString().split('T')[0];

            fetch(`/parent/attendance/getChildAttendance?child_id=${childId}&start_date=${startDate}&end_date=${endDate}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateAttendanceTable(childId, data.data);
                    updateCalendarWithData(childId, data.data, month, year);
                }
            })
            .catch(error => console.error('Error loading attendance:', error));
        }

        // Load child calendar
        function loadChildCalendar(childId, month, year) {
            const calendarElement = document.getElementById(`calendar-${childId}`);
            // Generate calendar for the month
            generateCalendar(calendarElement, month, year, childId);
        }

        // Generate calendar
        function generateCalendar(container, month, year, childId) {
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

            // Days of the month - initially show no-record
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                const dayOfWeek = new Date(year, month - 1, day).getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

                let status = 'no-record';
                if (isWeekend) {
                    status = 'weekend';
                }

                html += `<div class="calendar-day ${status}" data-date="${dateStr}">${day}</div>`;
            }

            container.innerHTML = html;
        }

        // Update calendar with real attendance data
        function updateCalendarWithData(childId, attendanceData, month, year) {
            const calendarElement = document.getElementById(`calendar-${childId}`);
            const calendarDays = calendarElement.querySelectorAll('.calendar-day[data-date]');

            // Create a map of attendance data by date
            const attendanceMap = {};
            attendanceData.forEach(record => {
                attendanceMap[record.attendance_date] = record.status;
            });

            // Update each calendar day
            calendarDays.forEach(dayElement => {
                const date = dayElement.getAttribute('data-date');
                const status = attendanceMap[date] || 'no-record';

                // Remove existing status classes
                dayElement.classList.remove('present', 'absent', 'late', 'no-record', 'weekend');

                // Add new status class
                dayElement.classList.add(status);
            });
        }

        // Update attendance table
        function updateAttendanceTable(childId, records) {
            const tbody = document.getElementById(`attendance-table-${childId}`);
            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No attendance records found</td></tr>';
                return;
            }

            let html = '';
            records.slice(0, 10).forEach(record => { // Show last 10 records
                const date = new Date(record.attendance_date);
                const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                const statusClass = `status-${record.status}`;

                html += `
                    <tr>
                        <td>${date.toLocaleDateString()}</td>
                        <td>${dayName}</td>
                        <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                        <td>${record.remarks || '-'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Load detailed attendance
        function loadDetailedAttendance(childId) {
            currentChildId = childId;
            const modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
            const startDate = document.getElementById('startDate').value || '2024-01-01';
            const endDate = document.getElementById('endDate').value || new Date().toISOString().split('T')[0];

            fetch(`/parent/attendance/getChildAttendance?child_id=${childId}&start_date=${startDate}&end_date=${endDate}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDetailedAttendance(data.data);
                }
            })
            .catch(error => console.error('Error loading detailed attendance:', error));

            modal.show();
        }

        // Display detailed attendance
        function displayDetailedAttendance(records) {
            const tbody = document.getElementById('detailedAttendanceBody');
            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No records found for the selected period</td></tr>';
                return;
            }

            let html = '';
            records.forEach(record => {
                const date = new Date(record.attendance_date);
                const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                const statusClass = `status-${record.status}`;

                html += `
                    <tr>
                        <td>${date.toLocaleDateString()}</td>
                        <td>${dayName}</td>
                        <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                        <td>${record.remarks || '-'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Export attendance report
        function exportAttendanceReport(childId) {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;
            const startDate = `${year}-${month.toString().padStart(2, '0')}-01`;
            const endDate = new Date(year, month, 0).toISOString().split('T')[0];

            window.open(`/parent/attendance/exportChildReport?child_id=${childId}&start_date=${startDate}&end_date=${endDate}`, '_blank');
        }

        // Export detailed report
        function exportDetailedReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            window.open(`/parent/attendance/exportChildReport?child_id=${currentChildId}&start_date=${startDate}&end_date=${endDate}`, '_blank');
        }

        // Initialize charts and calendars on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize trend charts
            <?php foreach ($children as $child): ?>
                const ctx<?php echo $child['id']; ?> = document.getElementById('trend-chart-<?php echo $child['id']; ?>');
                if (ctx<?php echo $child['id']; ?>) {
                    new Chart(ctx<?php echo $child['id']; ?>, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_column($child['monthly_trends'], 'month')); ?>,
                            datasets: [{
                                label: 'Attendance %',
                                data: <?php echo json_encode(array_column($child['monthly_trends'], 'percentage')); ?>,
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                tension: 0.4,
                                fill: true
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

                // Initialize calendars
                generateCalendar(
                    document.getElementById('calendar-<?php echo $child['id']; ?>'),
                    <?php echo date('n'); ?>,
                    <?php echo date('Y'); ?>,
                    <?php echo $child['id']; ?>
                );
            <?php endforeach; ?>

            // Set default date range for modal
            const today = new Date();
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(today.getMonth() - 1);

            document.getElementById('startDate').value = oneMonthAgo.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        });
    </script>
</body>
</html>