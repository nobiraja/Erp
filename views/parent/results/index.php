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
        .child-results-card {
            transition: transform 0.2s ease-in-out;
            margin-bottom: 2rem;
        }
        .child-results-card:hover {
            transform: translateY(-2px);
        }
        .exam-result-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .grade-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .grade-A { background-color: #28a745; color: white; }
        .grade-B { background-color: #17a2b8; color: white; }
        .grade-C { background-color: #ffc107; color: black; }
        .grade-D { background-color: #fd7e14; color: white; }
        .grade-F { background-color: #dc3545; color: white; }
        .performance-chart {
            height: 250px;
        }
        .subject-breakdown {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .metric-large {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .progress-custom {
            height: 10px;
            border-radius: 5px;
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
                        <a class="nav-link active" href="/parent/results">
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
                        <h2 class="mb-1">Academic Results</h2>
                        <p class="text-muted mb-0">Track your children's academic performance and exam results</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshAllResults()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Results Cards -->
        <?php foreach ($children as $child): ?>
            <div class="card child-results-card shadow-sm">
                <div class="card-header bg-info text-white">
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
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-outline-light btn-sm" onclick="viewDetailedResults(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </button>
                                <button class="btn btn-light btn-sm" onclick="downloadReportCard(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-download me-1"></i>Report Card
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Overall Performance Summary -->
                        <div class="col-lg-4 mb-3">
                            <div class="exam-result-card">
                                <h6 class="mb-3">
                                    <i class="bi bi-trophy me-2"></i>Overall Performance
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="metric-large"><?php echo $child['exam_summary']['average_percentage']; ?>%</div>
                                        <small>Average Score</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-large"><?php echo $child['exam_summary']['total_exams']; ?></div>
                                        <small>Total Exams</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-large">
                                            <?php echo $child['exam_summary']['class_rank'] > 0 ? $child['exam_summary']['class_rank'] : 'N/A'; ?>
                                        </div>
                                        <small>Class Rank</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="h5 mb-1"><?php echo $child['exam_summary']['grade_distribution']['A']; ?></div>
                                            <small>Grade A</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h5 mb-1"><?php echo $child['exam_summary']['grade_distribution']['B']; ?></div>
                                            <small>Grade B</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h5 mb-1"><?php echo $child['exam_summary']['grade_distribution']['C']; ?></div>
                                            <small>Grade C</small>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <small class="text-muted">
                                            Out of <?php echo $child['exam_summary']['total_students']; ?> students
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Chart -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-info mb-3">
                                <i class="bi bi-graph-up me-2"></i>Performance Trend
                            </h6>
                            <div class="performance-chart">
                                <canvas id="performance-chart-<?php echo $child['id']; ?>"></canvas>
                            </div>
                        </div>

                        <!-- Recent Exam Results -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-info mb-3">
                                <i class="bi bi-clipboard-check me-2"></i>Recent Exams
                            </h6>
                            <div id="recent-exams-<?php echo $child['id']; ?>">
                                <?php if (empty($child['recent_exams'])): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-clipboard-x" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No recent exams</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($child['recent_exams'] as $exam): ?>
                                        <div class="subject-breakdown">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($exam['subject_name']); ?> - <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <?php if ($exam['result']): ?>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($exam['result']['marks_obtained']); ?>/<?php echo htmlspecialchars($exam['result']['max_marks']); ?></span>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($exam['result']['percentage']); ?>%</small>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Pending</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Subject-wise Results -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-info mb-3">
                                <i class="bi bi-table me-2"></i>Subject-wise Performance
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Exam</th>
                                            <th>Date</th>
                                            <th>Marks</th>
                                            <th>Percentage</th>
                                            <th>Grade</th>
                                            <th>Teacher</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-table-<?php echo $child['id']; ?>">
                                        <!-- Results will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-info btn-sm" onclick="loadDetailedResults(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>View All Results
                                </button>
                                <button class="btn btn-info btn-sm" onclick="exportResultsReport(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-download me-1"></i>Export Results
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Detailed Results Modal -->
        <div class="modal fade" id="resultsDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detailed Exam Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="examSelect" class="form-label">Select Exam</label>
                                <select class="form-select" id="examSelect">
                                    <option value="">All Exams</option>
                                    <!-- Exam options will be loaded here -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="subjectFilter" class="form-label">Filter by Subject</label>
                                <select class="form-select" id="subjectFilter">
                                    <option value="">All Subjects</option>
                                    <!-- Subject options will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="detailedResultsTable">
                                <thead>
                                    <tr>
                                        <th>Exam</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Marks Obtained</th>
                                        <th>Max Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        <th>Teacher</th>
                                    </tr>
                                </thead>
                                <tbody id="detailedResultsBody">
                                    <!-- Detailed results will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportDetailedResults()">Export</button>
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

        // Refresh all results
        function refreshAllResults() {
            <?php foreach ($children as $child): ?>
                loadChildResults(<?php echo $child['id']; ?>);
            <?php endforeach; ?>
        }

        // Load child results
        function loadChildResults(childId) {
            fetch(`/parent/getChildResults?child_id=${childId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChildResults(childId, data.data);
                }
            })
            .catch(error => console.error('Error loading results:', error));
        }

        // Update child results display
        function updateChildResults(childId, results) {
            const tbody = document.getElementById(`results-table-${childId}`);
            if (results.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No exam results available</td></tr>';
                return;
            }

            let html = '';
            results.slice(0, 5).forEach(result => { // Show last 5 results
                const gradeClass = `grade-${result.calculated_grade || 'C'}`;
                html += `
                    <tr>
                        <td>${result.subject_name || ''}</td>
                        <td>${result.exam_name || ''}</td>
                        <td>${result.exam_date ? new Date(result.exam_date).toLocaleDateString() : ''}</td>
                        <td>${result.marks_obtained || 0}/${result.max_marks || 0}</td>
                        <td>${result.percentage || 0}%</td>
                        <td><span class="grade-badge ${gradeClass}">${result.calculated_grade || 'N/A'}</span></td>
                        <td>${result.teacher_name || 'N/A'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // View detailed results
        function viewDetailedResults(childId) {
            currentChildId = childId;
            const modal = new bootstrap.Modal(document.getElementById('resultsDetailsModal'));

            fetch(`/parent/getChildResults?child_id=${childId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDetailedResults(data.data);
                }
            })
            .catch(error => console.error('Error loading detailed results:', error));

            modal.show();
        }

        // Display detailed results
        function displayDetailedResults(results) {
            const tbody = document.getElementById('detailedResultsBody');
            if (results.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No results found</td></tr>';
                return;
            }

            let html = '';
            results.forEach(result => {
                const gradeClass = `grade-${result.calculated_grade || 'C'}`;
                html += `
                    <tr>
                        <td>${result.exam_name || ''}</td>
                        <td>${result.subject_name || ''}</td>
                        <td>${result.exam_date ? new Date(result.exam_date).toLocaleDateString() : ''}</td>
                        <td>${result.marks_obtained || 0}</td>
                        <td>${result.max_marks || 0}</td>
                        <td>${result.percentage || 0}%</td>
                        <td><span class="grade-badge ${gradeClass}">${result.calculated_grade || 'N/A'}</span></td>
                        <td>${result.teacher_name || 'N/A'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Download report card
        function downloadReportCard(childId) {
            window.open(`/parent/results/download?child_id=${childId}`, '_blank');
        }

        // Export results report
        function exportResultsReport(childId) {
            window.open(`/parent/results/export?child_id=${childId}`, '_blank');
        }

        // Export detailed results
        function exportDetailedResults() {
            const examId = document.getElementById('examSelect').value;
            window.open(`/parent/results/export?child_id=${currentChildId}&exam_id=${examId}`, '_blank');
        }

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize performance trend charts
            <?php foreach ($children as $child): ?>
                const ctx<?php echo $child['id']; ?> = document.getElementById('performance-chart-<?php echo $child['id']; ?>');
                if (ctx<?php echo $child['id']; ?>) {
                    const performanceTrend = <?php echo json_encode($child['performance_trend']); ?>;
                    const labels = performanceTrend.map(item => item.date);
                    const data = performanceTrend.map(item => item.percentage);

                    new Chart(ctx<?php echo $child['id']; ?>, {
                        type: 'line',
                        data: {
                            labels: labels.length > 0 ? labels : ['No data'],
                            datasets: [{
                                label: 'Performance %',
                                data: data.length > 0 ? data : [0],
                                borderColor: '#17a2b8',
                                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#17a2b8',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4
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
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Score: ' + context.parsed.y + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            <?php endforeach; ?>

            // Load initial results data
            refreshAllResults();
        });
    </script>
</body>
</html>