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
        .child-card {
            transition: transform 0.2s ease-in-out;
            margin-bottom: 2rem;
        }
        .child-card:hover {
            transform: translateY(-2px);
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .progress-custom {
            height: 8px;
            border-radius: 4px;
        }
        .subject-result {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .status-good { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger { color: #dc3545; }
        .chart-container {
            position: relative;
            height: 200px;
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
                        <a class="nav-link active" href="/parent/children">
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
                        <h2 class="mb-1">My Children</h2>
                        <p class="text-muted mb-0">Detailed academic overview and performance tracking</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6"><?php echo count($children); ?> Children</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Cards -->
        <?php foreach ($children as $child): ?>
            <div class="card child-card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['middle_name'] . ' ' . $child['last_name']); ?>
                            </h5>
                            <small>
                                <?php echo htmlspecialchars($child['class_name'] . ' - ' . $child['class_section']); ?> |
                                Scholar No: <?php echo htmlspecialchars($child['scholar_number']); ?> |
                                Admission No: <?php echo htmlspecialchars($child['admission_number']); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-outline-light btn-sm me-2" onclick="loadChildDetails(<?php echo $child['id']; ?>)">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                            <button class="btn btn-light btn-sm" onclick="exportChildReport(<?php echo $child['id']; ?>)">
                                <i class="bi bi-download me-1"></i>Export Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Attendance Metrics -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value"><?php echo $child['attendance']['percentage']; ?>%</div>
                                <div class="metric-label">Attendance Rate</div>
                                <div class="mt-2">
                                    <small><?php echo $child['attendance']['present_days']; ?>/<?php echo $child['attendance']['total_days']; ?> days</small>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Performance -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value"><?php echo $child['exam_results']['average_percentage']; ?>%</div>
                                <div class="metric-label">Average Score</div>
                                <div class="mt-2">
                                    <small><?php echo $child['exam_results']['total_exams']; ?> exams</small>
                                </div>
                            </div>
                        </div>

                        <!-- Fee Status -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value">₹<?php echo number_format($child['fee_status']['pending']); ?></div>
                                <div class="metric-label">Pending Fees</div>
                                <div class="mt-2">
                                    <small>Paid: ₹<?php echo number_format($child['fee_status']['paid']); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Overall Status -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-value">
                                    <?php
                                    $status = 'Good';
                                    $statusClass = 'status-good';
                                    if ($child['attendance']['percentage'] < 75 || $child['fee_status']['overdue'] > 0) {
                                        $status = 'Monitor';
                                        $statusClass = 'status-warning';
                                    }
                                    if ($child['exam_results']['average_percentage'] < 40) {
                                        $status = 'Concern';
                                        $statusClass = 'status-danger';
                                    }
                                    echo '<span class="' . $statusClass . '">' . $status . '</span>';
                                    ?>
                                </div>
                                <div class="metric-label">Overall Status</div>
                                <div class="mt-2">
                                    <small>Academic Health</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Recent Exam Results -->
                        <div class="col-lg-6 mb-3">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-clipboard-check me-2"></i>Recent Exam Results
                            </h6>
                            <div id="exam-results-<?php echo $child['id']; ?>">
                                <?php if (empty($child['recent_results'])): ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-clipboard-x" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No recent results</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($child['recent_results'] as $result): ?>
                                        <div class="subject-result">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($result['subject_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($result['exam_name']); ?> -
                                                        <?php echo date('M d, Y', strtotime($result['exam_date'])); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($result['marks_obtained']); ?>/<?php echo htmlspecialchars($result['max_marks']); ?></span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($result['percentage']); ?>%</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Performance Trend -->
                        <div class="col-lg-6 mb-3">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-graph-up me-2"></i>Performance Trend
                            </h6>
                            <div class="chart-container">
                                <canvas id="performance-chart-<?php echo $child['id']; ?>"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Overview -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-cash-coin me-2"></i>Fee Overview
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 text-success">₹<?php echo number_format($child['fee_status']['total']); ?></div>
                                        <small class="text-muted">Total Fees</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 text-primary">₹<?php echo number_format($child['fee_status']['paid']); ?></div>
                                        <small class="text-muted">Paid</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 <?php echo $child['fee_status']['pending'] > 0 ? 'text-warning' : 'text-success'; ?>">
                                            ₹<?php echo number_format($child['fee_status']['pending']); ?>
                                        </div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: <?php echo $child['fee_status']['percentage']; ?>%"
                                     aria-valuenow="<?php echo $child['fee_status']['percentage']; ?>"
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Child Details Modal -->
        <div class="modal fade" id="childDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Child Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="childDetailsContent">
                        <!-- Content will be loaded here -->
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
        // Load child details
        function loadChildDetails(childId) {
            fetch(`/parent/dashboard/getChildDetails?child_id=${childId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayChildDetails(data.data);
                }
            })
            .catch(error => console.error('Error loading child details:', error));
        }

        // Display child details in modal
        function displayChildDetails(childData) {
            const modal = new bootstrap.Modal(document.getElementById('childDetailsModal'));
            const content = document.getElementById('childDetailsContent');

            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <p><strong>Name:</strong> ${childData.first_name} ${childData.middle_name} ${childData.last_name}</p>
                        <p><strong>Class:</strong> ${childData.class_name} - ${childData.class_section}</p>
                        <p><strong>Scholar Number:</strong> ${childData.scholar_number}</p>
                        <p><strong>Admission Number:</strong> ${childData.admission_number}</p>
                        <p><strong>Date of Birth:</strong> ${new Date(childData.date_of_birth).toLocaleDateString()}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Contact Information</h6>
                        <p><strong>Mobile:</strong> ${childData.mobile || 'N/A'}</p>
                        <p><strong>Email:</strong> ${childData.email || 'N/A'}</p>
                        <p><strong>Address:</strong> ${childData.permanent_address || 'N/A'}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Subjects</h6>
                        <div class="row">
                            ${childData.subjects.map(subject => `
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2">
                                        <strong>${subject.subject_name}</strong><br>
                                        <small class="text-muted">Teacher: ${subject.teacher_name || 'TBD'}</small>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;

            modal.show();
        }

        // Export child report
        function exportChildReport(childId) {
            window.open(`/parent/children/export?child_id=${childId}`, '_blank');
        }

        // Initialize performance charts
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($children as $child): ?>
                const ctx<?php echo $child['id']; ?> = document.getElementById('performance-chart-<?php echo $child['id']; ?>');
                if (ctx<?php echo $child['id']; ?>) {
                    new Chart(ctx<?php echo $child['id']; ?>, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_column($child['performance_trend'], 'month')); ?>,
                            datasets: [{
                                label: 'Performance %',
                                data: <?php echo json_encode(array_column($child['performance_trend'], 'average_percentage')); ?>,
                                borderColor: '#667eea',
                                backgroundColor: 'rgba(102, 126, 234, 0.1)',
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
            <?php endforeach; ?>
        });
    </script>
</body>
</html>