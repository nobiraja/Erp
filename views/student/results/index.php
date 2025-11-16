<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <style>
        .results-card {
            transition: transform 0.2s ease-in-out;
        }
        .results-card:hover {
            transform: translateY(-2px);
        }
        .grade-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }
        .exam-schedule-item {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .performance-chart {
            max-height: 300px;
        }
        .rank-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
        }
        .subject-result {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .analytics-tabs .nav-link {
            border: none;
            color: #6c757d;
        }
        .analytics-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .download-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
            color: white;
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
                        <a class="nav-link" href="/student/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/student/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/dashboard">
                            <i class="bi bi-megaphone me-1"></i>Announcements
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
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-1">Exam Results & Performance</h1>
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars($student_data['class_name'] ?? ''); ?> - <?php echo htmlspecialchars($student_data['section'] ?? ''); ?> |
                            Scholar No: <?php echo htmlspecialchars($student_data['scholar_number'] ?? ''); ?>
                        </p>
                    </div>
                    <div>
                        <button class="download-btn me-2" onclick="downloadReportCard()">
                            <i class="bi bi-download me-2"></i>Download Report Card
                        </button>
                        <button class="btn btn-outline-primary" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card results-card h-100">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="bi bi-trophy-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1"><?php echo htmlspecialchars($results_summary['overall_grade'] ?? 'N/A'); ?></div>
                        <div class="text-muted">Overall Grade</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card results-card h-100">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="bi bi-percent" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1"><?php echo htmlspecialchars($results_summary['average_percentage'] ?? 0); ?>%</div>
                        <div class="text-muted">Average Score</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card results-card h-100">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1"><?php echo htmlspecialchars($results_summary['total_exams'] ?? 0); ?></div>
                        <div class="text-muted">Total Exams</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card results-card h-100">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="bi bi-hash" style="font-size: 2rem;"></i>
                        </div>
                        <div class="h4 mb-1"><?php echo htmlspecialchars($student_rank['rank'] ?? 'N/A'); ?>/<?php echo htmlspecialchars($student_rank['total_students'] ?? 0); ?></div>
                        <div class="text-muted">Class Rank</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Exam Schedules -->
            <div class="col-lg-4 mb-4">
                <div class="card results-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Upcoming Exams
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="exam-schedules-container">
                            <?php if (empty($upcoming_exams)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No upcoming exams scheduled.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($upcoming_exams as $exam): ?>
                                    <div class="exam-schedule-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($exam['subject_name'] ?? ''); ?></h6>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($exam['exam_date'] ?? '')); ?>
                                                </small>
                                                <?php if (!empty($exam['start_time'])): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($exam['start_time'])); ?>
                                                        <?php if (!empty($exam['end_time'])): ?>
                                                            - <?php echo date('h:i A', strtotime($exam['end_time'])); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($exam['exam_name'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadExamSchedules()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject-wise Results -->
            <div class="col-lg-8 mb-4">
                <div class="card results-card h-100">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-data me-2"></i>Subject-wise Results
                        </h5>
                        <select class="form-select form-select-sm" id="exam-filter" style="width: auto; background-color: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.3);">
                            <option value="">All Exams</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="card-body">
                        <div id="subject-results-container">
                            <?php if (empty($exam_results)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-clipboard-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No exam results available yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($exam_results as $result): ?>
                                    <div class="subject-result">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($result['subject_name'] ?? ''); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($result['exam_name'] ?? ''); ?> -
                                                    <?php echo date('M d, Y', strtotime($result['exam_date'] ?? '')); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <div class="h5 mb-0"><?php echo htmlspecialchars($result['marks_obtained'] ?? 0); ?>/<?php echo htmlspecialchars($result['max_marks'] ?? 0); ?></div>
                                                <small class="text-muted">Marks</small>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <div class="h6 mb-0"><?php echo htmlspecialchars($result['percentage'] ?? 0); ?>%</div>
                                                <small class="text-muted">Percentage</small>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <span class="badge grade-badge bg-<?php
                                                    $grade = $result['calculated_grade'] ?? 'F';
                                                    echo match($grade) {
                                                        'A+', 'A' => 'success',
                                                        'B+', 'B' => 'primary',
                                                        'C+', 'C' => 'warning',
                                                        'D' => 'info',
                                                        default => 'danger'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($grade); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analytics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card results-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart-line me-2"></i>Performance Analytics
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs analytics-tabs mb-4" id="analyticsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="trends-tab" data-bs-toggle="tab" data-bs-target="#trends" type="button" role="tab">
                                    <i class="bi bi-graph-up me-1"></i>Trends
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="subjects-tab" data-bs-toggle="tab" data-bs-target="#subjects" type="button" role="tab">
                                    <i class="bi bi-book me-1"></i>Subject Performance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="strengths-tab" data-bs-toggle="tab" data-bs-target="#strengths" type="button" role="tab">
                                    <i class="bi bi-trophy me-1"></i>Strengths & Weaknesses
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="analyticsTabsContent">
                            <!-- Trends Tab -->
                            <div class="tab-pane fade show active" id="trends" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <canvas id="performanceChart" class="performance-chart"></canvas>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rank-display">
                                            <h3>Class Rank</h3>
                                            <div class="display-4 mb-2"><?php echo htmlspecialchars($student_rank['rank'] ?? 'N/A'); ?></div>
                                            <p class="mb-0">Out of <?php echo htmlspecialchars($student_rank['total_students'] ?? 0); ?> students</p>
                                            <?php if ($student_rank['percentage'] ?? false): ?>
                                                <small>Top <?php echo htmlspecialchars($student_rank['percentage']); ?>%</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Performance Tab -->
                            <div class="tab-pane fade" id="subjects" role="tabpanel">
                                <canvas id="subjectChart" class="performance-chart"></canvas>
                            </div>

                            <!-- Strengths & Weaknesses Tab -->
                            <div class="tab-pane fade" id="strengths" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="bi bi-trophy-fill me-2"></i>Strengths
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="strengths-list">
                                                    <?php if (!empty($performance_analytics['strengths'])): ?>
                                                        <?php foreach ($performance_analytics['strengths'] as $strength): ?>
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span><?php echo htmlspecialchars($strength['subject_name'] ?? ''); ?></span>
                                                                <span class="badge bg-success"><?php echo htmlspecialchars($strength['avg_percentage'] ?? 0); ?>%</span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-0">No data available</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="card-title mb-0">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Areas for Improvement
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="weaknesses-list">
                                                    <?php if (!empty($performance_analytics['weaknesses'])): ?>
                                                        <?php foreach ($performance_analytics['weaknesses'] as $weakness): ?>
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span><?php echo htmlspecialchars($weakness['subject_name'] ?? ''); ?></span>
                                                                <span class="badge bg-warning"><?php echo htmlspecialchars($weakness['avg_percentage'] ?? 0); ?>%</span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-0">No data available</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX Functions and Charts -->
    <script>
        let performanceChart = null;
        let subjectChart = null;

        // Initialize charts
        function initializeCharts() {
            // Performance Trends Chart
            const trendsData = <?php echo json_encode($performance_analytics['exam_trends'] ?? []); ?>;
            const ctx = document.getElementById('performanceChart').getContext('2d');

            if (performanceChart) {
                performanceChart.destroy();
            }

            performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.map(item => item.exam_name || ''),
                    datasets: [{
                        label: 'Percentage',
                        data: trendsData.map(item => item.avg_percentage || 0),
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
                    }
                }
            });

            // Subject Performance Chart
            const subjectData = <?php echo json_encode($performance_analytics['subject_performance'] ?? []); ?>;
            const subjectCtx = document.getElementById('subjectChart').getContext('2d');

            if (subjectChart) {
                subjectChart.destroy();
            }

            subjectChart = new Chart(subjectCtx, {
                type: 'bar',
                data: {
                    labels: subjectData.map(item => item.subject_name || ''),
                    datasets: [{
                        label: 'Average Percentage',
                        data: subjectData.map(item => item.avg_percentage || 0),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
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
                    }
                }
            });
        }

        // Load exam schedules
        function loadExamSchedules() {
            fetch('/student/results/getExamSchedules', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateExamSchedules(data.data);
                }
            })
            .catch(error => console.error('Error loading exam schedules:', error));
        }

        // Load performance data
        function loadPerformanceData() {
            fetch('/student/results/getPerformanceData', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updatePerformanceData(data.data);
                }
            })
            .catch(error => console.error('Error loading performance data:', error));
        }

        // Download report card
        function downloadReportCard() {
            window.location.href = '/student/results/downloadReportCard';
        }

        // Refresh all data
        function refreshData() {
            loadExamSchedules();
            loadPerformanceData();
        }

        // Update functions
        function updateExamSchedules(schedules) {
            const container = document.getElementById('exam-schedules-container');
            if (schedules.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <p class="mt-2">No upcoming exams scheduled.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            schedules.forEach(schedule => {
                html += `
                    <div class="exam-schedule-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${schedule.subject_name || ''}</h6>
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar me-1"></i>
                                    ${new Date(schedule.exam_date).toLocaleDateString()}
                                </small>
                                ${schedule.start_time ? `
                                    <small class="text-muted d-block">
                                        <i class="bi bi-clock me-1"></i>
                                        ${new Date('1970-01-01T' + schedule.start_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        ${schedule.end_time ? ` - ${new Date('1970-01-01T' + schedule.end_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}` : ''}
                                    </small>
                                ` : ''}
                            </div>
                            <span class="badge bg-primary">${schedule.exam_name || ''}</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function updatePerformanceData(data) {
            // Update charts with new data
            if (performanceChart && data.exam_trends) {
                performanceChart.data.labels = data.exam_trends.map(item => item.exam_name || '');
                performanceChart.data.datasets[0].data = data.exam_trends.map(item => item.avg_percentage || 0);
                performanceChart.update();
            }

            if (subjectChart && data.subject_performance) {
                subjectChart.data.labels = data.subject_performance.map(item => item.subject_name || '');
                subjectChart.data.datasets[0].data = data.subject_performance.map(item => item.avg_percentage || 0);
                subjectChart.update();
            }

            // Update strengths and weaknesses
            updateStrengthsWeaknesses(data.strengths || [], data.weaknesses || []);
        }

        function updateStrengthsWeaknesses(strengths, weaknesses) {
            const strengthsContainer = document.getElementById('strengths-list');
            const weaknessesContainer = document.getElementById('weaknesses-list');

            strengthsContainer.innerHTML = strengths.length > 0 ?
                strengths.map(strength => `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>${strength.subject_name || ''}</span>
                        <span class="badge bg-success">${strength.avg_percentage || 0}%</span>
                    </div>
                `).join('') :
                '<p class="text-muted mb-0">No data available</p>';

            weaknessesContainer.innerHTML = weaknesses.length > 0 ?
                weaknesses.map(weakness => `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>${weakness.subject_name || ''}</span>
                        <span class="badge bg-warning">${weakness.avg_percentage || 0}%</span>
                    </div>
                `).join('') :
                '<p class="text-muted mb-0">No data available</p>';
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            console.log('Student results page loaded');
        });
    </script>
</body>
</html>