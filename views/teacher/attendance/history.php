<?php
/**
 * Teacher Attendance History View
 * Shows attendance history for selected month and class/subject
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Attendance History'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .history-card {
            transition: transform 0.2s;
        }
        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .calendar-day {
            min-height: 60px;
            border: 1px solid #dee2e6;
            padding: 5px;
        }
        .calendar-day.today {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .attendance-present { background-color: #d4edda; }
        .attendance-absent { background-color: #f8d7da; }
        .attendance-late { background-color: #fff3cd; }
        .chart-container {
            position: relative;
            height: 250px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/teacher/dashboard">
                <i class="fas fa-school"></i> School Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/attendance">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/attendance/reports">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/teacher/attendance/history">
                            <i class="fas fa-history"></i> History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/classes">
                            <i class="fas fa-chalkboard"></i> Classes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/teacher/exams">
                            <i class="fas fa-file-alt"></i> Exams
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Teacher'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/teacher/profile"><i class="fas fa-user-edit"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> History Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/teacher/attendance/history" class="btn btn-outline-info active">
                                <i class="fas fa-calendar-alt"></i> Monthly View
                            </a>
                            <a href="/teacher/attendance/reports" class="btn btn-outline-success">
                                <i class="fas fa-chart-bar"></i> Summary Reports
                            </a>
                            <a href="/teacher/attendance" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle"></i> Mark Attendance
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <?php if (isset($history_data) && !empty($history_data)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Monthly Stats</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalDays = count($history_data);
                        $totalPresent = array_sum(array_column($history_data, 'present_count'));
                        $totalAbsent = array_sum(array_column($history_data, 'absent_count'));
                        $totalLate = array_sum(array_column($history_data, 'late_count'));
                        $avgAttendance = $totalDays > 0 ? array_sum(array_column($history_data, 'attendance_rate')) / $totalDays : 0;
                        ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h5 mb-0"><?php echo number_format($avgAttendance, 1); ?>%</div>
                                <small class="text-muted">Avg Rate</small>
                            </div>
                            <div class="col-6">
                                <div class="h5 mb-0"><?php echo $totalDays; ?></div>
                                <small class="text-muted">Days</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-success"><?php echo $totalPresent; ?></div>
                                <small>P</small>
                            </div>
                            <div class="col-4">
                                <div class="text-danger"><?php echo $totalAbsent; ?></div>
                                <small>A</small>
                            </div>
                            <div class="col-4">
                                <div class="text-warning"><?php echo $totalLate; ?></div>
                                <small>L</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-history"></i> Attendance History</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="/teacher/attendance/history" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select class="form-select" id="class_id" name="class_id">
                                        <option value="">Select Class</option>
                                        <?php
                                        $displayedClasses = [];
                                        foreach ($assignedSubjects as $subject):
                                            $classKey = $subject['class_id'] . '-' . $subject['section'];
                                            if (!isset($displayedClasses[$classKey])):
                                                $displayedClasses[$classKey] = true;
                                        ?>
                                        <option value="<?php echo $subject['class_id']; ?>" <?php echo ($class_id == $subject['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?>
                                        </option>
                                        <?php endif; endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="subject_id" class="form-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id">
                                        <option value="">Select Subject</option>
                                        <?php foreach ($assignedSubjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" <?php echo ($subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($month == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php $currentYear = date('Y'); ?>
                                        <?php for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> View History
                                </button>
                                <?php if (isset($history_data) && !empty($history_data)): ?>
                                <button type="button" class="btn btn-info" onclick="exportHistory()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>

                        <?php if (isset($history_data) && !empty($history_data)): ?>
                        <!-- History Results -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card history-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Daily Attendance Trend</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="trendChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card history-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Best Attendance Day</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $bestDay = null;
                                        $bestRate = 0;
                                        foreach ($history_data as $day) {
                                            if ($day['attendance_rate'] > $bestRate) {
                                                $bestRate = $day['attendance_rate'];
                                                $bestDay = $day;
                                            }
                                        }
                                        if ($bestDay):
                                        ?>
                                        <div class="text-center">
                                            <div class="h3 text-success mb-1"><?php echo date('j', strtotime($bestDay['attendance_date'])); ?></div>
                                            <div class="text-muted"><?php echo date('M', strtotime($bestDay['attendance_date'])); ?></div>
                                            <div class="badge bg-success mt-2"><?php echo number_format($bestRate, 1); ?>% Present</div>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center text-muted">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                            <div>No data available</div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar View -->
                        <div class="card history-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar"></i> Monthly Calendar View</h5>
                            </div>
                            <div class="card-body">
                                <?php echo generateCalendar($history_data, $month, $year); ?>
                            </div>
                        </div>

                        <!-- Detailed Table -->
                        <div class="card history-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-table"></i> Daily Attendance Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="historyTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Total Students</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Attendance Rate</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($history_data as $day): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($day['attendance_date'])); ?></td>
                                                <td><?php echo date('l', strtotime($day['attendance_date'])); ?></td>
                                                <td><?php echo $day['total_students']; ?></td>
                                                <td><span class="badge bg-success"><?php echo $day['present_count']; ?></span></td>
                                                <td><span class="badge bg-danger"><?php echo $day['absent_count']; ?></span></td>
                                                <td><span class="badge bg-warning"><?php echo $day['late_count']; ?></span></td>
                                                <td>
                                                    <strong class="<?php
                                                        echo $day['attendance_rate'] >= 85 ? 'text-success' :
                                                             ($day['attendance_rate'] >= 75 ? 'text-warning' : 'text-danger');
                                                    ?>">
                                                        <?php echo number_format($day['attendance_rate'], 1); ?>%
                                                    </strong>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDayDetails('<?php echo $day['attendance_date']; ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($class_id) || !empty($subject_id) || !empty($month) || !empty($year))): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No attendance history found for the selected criteria.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-primary">
                            <i class="fas fa-info-circle"></i> Select class, subject, month, and year to view attendance history.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Details Modal -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-day"></i> Day Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dayDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize class-subject relationship
            const assignedSubjects = <?php echo json_encode($assignedSubjects); ?>;

            $('#class_id').change(function() {
                const classId = $(this).val();
                const subjectSelect = $('#subject_id');

                subjectSelect.find('option:not(:first)').remove();

                if (classId) {
                    const classSubjects = assignedSubjects.filter(subject => subject.class_id == classId);
                    classSubjects.forEach(subject => {
                        subjectSelect.append(
                            `<option value="${subject.subject_id}">${subject.subject_name}</option>`
                        );
                    });
                }
            });

            // Initialize chart if data exists
            <?php if (isset($history_data) && !empty($history_data)): ?>
            initializeChart();
            <?php endif; ?>
        });

        function initializeChart() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            const historyData = <?php echo json_encode($history_data ?? []); ?>;

            const labels = historyData.map(day => new Date(day.attendance_date).getDate());
            const rates = historyData.map(day => day.attendance_rate);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance Rate (%)',
                        data: rates,
                        borderColor: 'rgba(23, 162, 184, 1)',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Day of Month'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Daily Attendance Rate Trend'
                        }
                    }
                }
            });
        }

        function viewDayDetails(date) {
            // This would typically load detailed attendance for that day
            $('#dayDetailsContent').html(`
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p>Loading details for ${new Date(date).toLocaleDateString()}...</p>
                </div>
            `);

            $('#dayDetailsModal').modal('show');

            // Simulate loading (replace with actual AJAX call)
            setTimeout(() => {
                $('#dayDetailsContent').html(`
                    <p class="text-muted">Detailed attendance view for ${new Date(date).toLocaleDateString()} would be loaded here.</p>
                    <p>This feature would show individual student attendance records for the selected day.</p>
                `);
            }, 1000);
        }

        function exportHistory() {
            const classId = $('#class_id').val();
            const subjectId = $('#subject_id').val();
            const month = $('#month').val();
            const year = $('#year').val();

            const params = new URLSearchParams({
                class_id: classId,
                subject_id: subjectId,
                month: month,
                year: year,
                export: 'csv'
            });

            window.location.href = `/teacher/attendance/export-history?${params}`;
        }
    </script>

    <?php
    function generateCalendar($historyData, $month, $year) {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));
        $firstDayOfMonth = date('w', strtotime("$year-$month-01"));

        // Create attendance lookup
        $attendanceLookup = [];
        foreach ($historyData as $day) {
            $dayNum = date('j', strtotime($day['attendance_date']));
            $attendanceLookup[$dayNum] = $day;
        }

        $calendar = '<div class="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;">';

        // Day headers
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        foreach ($dayNames as $dayName) {
            $calendar .= "<div class='text-center fw-bold p-2 bg-light'>$dayName</div>";
        }

        // Empty cells for days before the first day of the month
        for ($i = 0; $i < $firstDayOfMonth; $i++) {
            $calendar .= '<div class="calendar-day"></div>';
        }

        // Days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $isToday = ($day == date('j') && $month == date('m') && $year == date('Y'));
            $classes = 'calendar-day text-center ' . ($isToday ? 'today' : '');

            if (isset($attendanceLookup[$day])) {
                $attendance = $attendanceLookup[$day];
                $rate = $attendance['attendance_rate'];
                if ($rate >= 85) {
                    $classes .= ' attendance-present';
                } elseif ($rate >= 70) {
                    $classes .= ' attendance-late';
                } else {
                    $classes .= ' attendance-absent';
                }
                $content = "<strong>$day</strong><br><small>" . number_format($rate, 0) . "%</small>";
            } else {
                $content = $day;
            }

            $calendar .= "<div class='$classes'>$content</div>";
        }

        $calendar .= '</div>';
        return $calendar;
    }
    ?>
</body>
</html>