<?php
/**
 * Teacher Attendance Reports View
 * Shows attendance summary reports for selected class and subject
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Attendance Reports'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card {
            transition: transform 0.2s;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .attendance-high { color: #28a745; }
        .attendance-medium { color: #ffc107; }
        .attendance-low { color: #dc3545; }
        .chart-container {
            position: relative;
            height: 300px;
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
                        <a class="nav-link active" href="/teacher/attendance/reports">
                            <i class="fas fa-chart-bar"></i> Reports
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
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Report Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/teacher/attendance/reports" class="btn btn-outline-success active">
                                <i class="fas fa-chart-line"></i> Summary Reports
                            </a>
                            <a href="/teacher/attendance/history" class="btn btn-outline-info">
                                <i class="fas fa-history"></i> Attendance History
                            </a>
                            <a href="/teacher/attendance" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle"></i> Mark Attendance
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <?php if (isset($report_data) && !empty($report_data)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Summary Stats</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalStudents = count($report_data);
                        $avgAttendance = array_sum(array_column($report_data, 'attendance_percentage')) / $totalStudents;
                        $highAttendance = count(array_filter($report_data, fn($s) => $s['attendance_percentage'] >= 85));
                        $lowAttendance = count(array_filter($report_data, fn($s) => $s['attendance_percentage'] < 75));
                        ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 mb-0"><?php echo number_format($avgAttendance, 1); ?>%</div>
                                <small class="text-muted">Avg Attendance</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0"><?php echo $totalStudents; ?></div>
                                <small class="text-muted">Total Students</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-success h6 mb-0"><?php echo $highAttendance; ?></div>
                                <small class="text-muted">High (≥85%)</small>
                            </div>
                            <div class="col-6">
                                <div class="text-danger h6 mb-0"><?php echo $lowAttendance; ?></div>
                                <small class="text-muted">Low (<75%)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Attendance Summary Reports</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="/teacher/attendance/reports" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select class="form-select" id="class_id" name="class_id">
                                        <option value="">All Classes</option>
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
                                        <option value="">All Subjects</option>
                                        <?php foreach ($assignedSubjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" <?php echo ($subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="<?php echo $start_date ?? date('Y-m-01'); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="<?php echo $end_date ?? date('Y-m-t'); ?>">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                                <?php if (isset($report_data) && !empty($report_data)): ?>
                                <button type="button" class="btn btn-success" onclick="exportReport()">
                                    <i class="fas fa-download"></i> Export CSV
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>

                        <?php if (isset($report_data) && !empty($report_data)): ?>
                        <!-- Report Results -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card report-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Attendance Distribution</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="attendanceChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card report-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Performers</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $topPerformers = array_slice(array_filter($report_data, fn($s) => $s['attendance_percentage'] >= 90), 0, 3);
                                        if (empty($topPerformers)) {
                                            $topPerformers = array_slice($report_data, 0, 3);
                                        }
                                        foreach ($topPerformers as $student):
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-truncate"><?php echo htmlspecialchars($student['student_name']); ?></span>
                                            <span class="badge bg-success"><?php echo number_format($student['attendance_percentage'], 1); ?>%</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Table -->
                        <div class="card report-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-table"></i> Detailed Attendance Report</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="reportTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Scholar No.</th>
                                                <th>Student Name</th>
                                                <th>Total Days</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Attendance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                                                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                                <td><?php echo $student['total_days']; ?></td>
                                                <td><span class="badge bg-success"><?php echo $student['present_days']; ?></span></td>
                                                <td><span class="badge bg-danger"><?php echo $student['absent_days']; ?></span></td>
                                                <td><span class="badge bg-warning"><?php echo $student['late_days']; ?></span></td>
                                                <td>
                                                    <strong class="<?php
                                                        echo $student['attendance_percentage'] >= 85 ? 'attendance-high' :
                                                             ($student['attendance_percentage'] >= 75 ? 'attendance-medium' : 'attendance-low');
                                                    ?>">
                                                        <?php echo number_format($student['attendance_percentage'], 1); ?>%
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php if ($student['attendance_percentage'] >= 85): ?>
                                                        <span class="badge bg-success">Excellent</span>
                                                    <?php elseif ($student['attendance_percentage'] >= 75): ?>
                                                        <span class="badge bg-warning">Good</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Needs Attention</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($class_id) || !empty($subject_id) || !empty($start_date) || !empty($end_date))): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No attendance data found for the selected criteria.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-primary">
                            <i class="fas fa-info-circle"></i> Select class, subject, and date range to generate attendance reports.
                        </div>
                        <?php endif; ?>
                    </div>
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
            <?php if (isset($report_data) && !empty($report_data)): ?>
            initializeChart();
            <?php endif; ?>
        });

        function initializeChart() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const reportData = <?php echo json_encode($report_data ?? []); ?>;

            const attendanceRanges = {
                '90-100%': reportData.filter(s => s.attendance_percentage >= 90).length,
                '80-89%': reportData.filter(s => s.attendance_percentage >= 80 && s.attendance_percentage < 90).length,
                '70-79%': reportData.filter(s => s.attendance_percentage >= 70 && s.attendance_percentage < 80).length,
                '60-69%': reportData.filter(s => s.attendance_percentage >= 60 && s.attendance_percentage < 70).length,
                'Below 60%': reportData.filter(s => s.attendance_percentage < 60).length
            };

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(attendanceRanges),
                    datasets: [{
                        label: 'Number of Students',
                        data: Object.values(attendanceRanges),
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(23, 162, 184, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(255, 152, 0, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(23, 162, 184, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(255, 152, 0, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Attendance Distribution by Percentage Range'
                        }
                    }
                }
            });
        }

        function exportReport() {
            const classId = $('#class_id').val();
            const subjectId = $('#subject_id').val();
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            const params = new URLSearchParams({
                class_id: classId,
                subject_id: subjectId,
                start_date: startDate,
                end_date: endDate,
                export: 'csv'
            });

            window.location.href = `/teacher/attendance/export?${params}`;
        }
    </script>
</body>
</html>