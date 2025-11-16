<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Performance Analytics'); ?></title>
    <meta name="description" content="View performance analytics and summaries for exams">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        .analytics-card {
            transition: transform 0.3s;
        }
        .analytics-card:hover {
            transform: translateY(-2px);
        }
        .grade-distribution-bar {
            height: 30px;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
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
                    <span class="fw-bold" id="sidebarTitle">Teacher</span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/dashboard">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/classes">
                        <i class="bi bi-book"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/attendance">
                        <i class="bi bi-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/teacher/exams">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Exams & Results</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher/profile">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
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
                        <div class="fw-bold small"><?php echo htmlspecialchars($teacher ? $teacher->getFullName() : 'Teacher'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($teacher ? $teacher->designation : 'Teacher'); ?></div>
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
                        <h5 class="mb-0">Performance Analytics</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/teacher/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($teacher ? $teacher->first_name : 'Teacher'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/teacher/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/teacher/profile#change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-4">
            <!-- Selection Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Select Class & Subject for Analytics</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="/teacher/exams/analytics" class="row g-3">
                        <div class="col-md-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Choose class...</option>
                                <?php foreach ($assignedSubjects ?? [] as $subject): ?>
                                    <?php
                                    $classData = ClassModel::find($subject['class_id']);
                                    if ($classData):
                                    ?>
                                        <option value="<?php echo $subject['class_id']; ?>" <?php echo ($class_id == $subject['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($classData->class_name . ' ' . $classData->section); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">Choose subject...</option>
                                <?php foreach ($assignedSubjects ?? [] as $subject): ?>
                                    <?php if (!$class_id || $subject['class_id'] == $class_id): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" <?php echo ($subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="exam_type" class="form-label">Exam Type (Optional)</label>
                            <select class="form-select" id="exam_type" name="exam_type">
                                <option value="">All Types</option>
                                <option value="mid-term" <?php echo ($exam_type == 'mid-term') ? 'selected' : ''; ?>>Mid-term</option>
                                <option value="final" <?php echo ($exam_type == 'final') ? 'selected' : ''; ?>>Final</option>
                                <option value="unit-test" <?php echo ($exam_type == 'unit-test') ? 'selected' : ''; ?>>Unit Test</option>
                                <option value="custom" <?php echo ($exam_type == 'custom') ? 'selected' : ''; ?>>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Generate Analytics
                                </button>
                                <a href="/teacher/exams" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($analytics_data && !empty($analytics_data['statistics'])): ?>
                <!-- Analytics Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 analytics-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Average Percentage
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($analytics_data['statistics']['avg_percentage'] ?? 0, 1); ?>%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-graph-up fs-2 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2 analytics-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Highest Score
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($analytics_data['statistics']['highest_percentage'] ?? 0, 1); ?>%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-trophy fs-2 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2 analytics-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Lowest Score
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($analytics_data['statistics']['lowest_percentage'] ?? 0, 1); ?>%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-arrow-down-circle fs-2 text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2 analytics-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Students
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $analytics_data['statistics']['total_students'] ?? 0; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fs-2 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Grade Distribution Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="bi bi-bar-chart me-2"></i>Grade Distribution
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="gradeDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Overview Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="bi bi-pie-chart me-2"></i>Performance Overview
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="performanceOverviewChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Grade Distribution -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Detailed Grade Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $gradeColors = [
                                'A+' => 'success',
                                'A' => 'success',
                                'B+' => 'primary',
                                'B' => 'primary',
                                'C+' => 'warning',
                                'C' => 'warning',
                                'D' => 'danger',
                                'F' => 'danger'
                            ];
                            $totalStudents = $analytics_data['statistics']['total_students'] ?? 1;

                            foreach ($analytics_data['grade_distribution'] as $grade => $count):
                                $percentage = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0;
                                $color = $gradeColors[$grade] ?? 'secondary';
                            ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <div class="mb-2">
                                                <span class="badge bg-<?php echo $color; ?> fs-5 px-3 py-2"><?php echo $grade; ?></span>
                                            </div>
                                            <h4 class="mb-1"><?php echo $count; ?></h4>
                                            <small class="text-muted"><?php echo $percentage; ?>% of students</small>
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Performance Insights -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Performance Insights</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Strengths</h6>
                                <ul class="list-unstyled">
                                    <?php
                                    $avgPercentage = $analytics_data['statistics']['avg_percentage'] ?? 0;
                                    if ($avgPercentage >= 75):
                                    ?>
                                        <li class="text-success mb-2">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Excellent overall performance (<?php echo number_format($avgPercentage, 1); ?>% average)
                                        </li>
                                    <?php endif; ?>

                                    <?php if (($analytics_data['grade_distribution']['A+'] ?? 0) + ($analytics_data['grade_distribution']['A'] ?? 0) > $totalStudents * 0.5): ?>
                                        <li class="text-success mb-2">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Strong performance with majority students in A grades
                                        </li>
                                    <?php endif; ?>

                                    <?php if (($analytics_data['grade_distribution']['F'] ?? 0) == 0): ?>
                                        <li class="text-success mb-2">
                                            <i class="bi bi-check-circle me-2"></i>
                                            No failing grades - great achievement!
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Areas for Improvement</h6>
                                <ul class="list-unstyled">
                                    <?php if ($avgPercentage < 60): ?>
                                        <li class="text-warning mb-2">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Overall performance needs improvement (<?php echo number_format($avgPercentage, 1); ?>% average)
                                        </li>
                                    <?php endif; ?>

                                    <?php if (($analytics_data['grade_distribution']['F'] ?? 0) + ($analytics_data['grade_distribution']['D'] ?? 0) > $totalStudents * 0.3): ?>
                                        <li class="text-warning mb-2">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Significant number of students need additional support
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $range = ($analytics_data['statistics']['highest_percentage'] ?? 0) - ($analytics_data['statistics']['lowest_percentage'] ?? 0);
                                    if ($range > 40):
                                    ?>
                                        <li class="text-info mb-2">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Large performance gap between top and bottom performers
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-outline-primary" onclick="exportAnalytics()">
                                <i class="bi bi-download me-1"></i>Export Analytics Report
                            </button>
                        </div>
                    </div>
                </div>
            <?php elseif ($class_id && $subject_id): ?>
                <!-- No data found -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bar-chart text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">No Analytics Data Available</h5>
                        <p class="text-muted mb-0">No exam results found for the selected class and subject.</p>
                        <a href="/teacher/exams/analytics" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left me-1"></i>Back to Selection
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- No selection made -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">Select Class and Subject</h5>
                        <p class="text-muted mb-0">Choose a class and subject to view performance analytics.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

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

            // Initialize analytics view
            initializeAnalyticsView();
        });

        // Initialize analytics view
        function initializeAnalyticsView() {
            <?php if ($analytics_data && !empty($analytics_data['statistics'])): ?>
                // Initialize charts
                initializeCharts();
            <?php endif; ?>
        }

        // Initialize charts
        function initializeCharts() {
            const gradeDistribution = <?php echo json_encode($analytics_data['grade_distribution'] ?? []); ?>;
            const statistics = <?php echo json_encode($analytics_data['statistics'] ?? []); ?>;

            // Grade Distribution Chart
            createGradeDistributionChart(gradeDistribution);

            // Performance Overview Chart
            createPerformanceOverviewChart(statistics);
        }

        // Create grade distribution chart
        function createGradeDistributionChart(gradeData) {
            const ctx = document.getElementById('gradeDistributionChart');
            if (!ctx) return;

            const labels = Object.keys(gradeData);
            const data = Object.values(gradeData);

            const backgroundColors = labels.map(grade => {
                const colors = {
                    'A+': '#28a745',
                    'A': '#28a745',
                    'B+': '#007bff',
                    'B': '#007bff',
                    'C+': '#ffc107',
                    'C': '#ffc107',
                    'D': '#dc3545',
                    'F': '#dc3545'
                };
                return colors[grade] || '#6c757d';
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Students',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color),
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
                        }
                    }
                }
            });
        }

        // Create performance overview chart
        function createPerformanceOverviewChart(statistics) {
            const ctx = document.getElementById('performanceOverviewChart');
            if (!ctx) return;

            const avgPercentage = statistics.avg_percentage || 0;
            const remaining = 100 - avgPercentage;

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Average Score', 'Remaining'],
                    datasets: [{
                        data: [avgPercentage, remaining],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(200, 200, 200, 0.3)'
                        ],
                        borderColor: [
                            'rgb(54, 162, 235)',
                            'rgba(200, 200, 200, 0.5)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Export analytics data
        function exportAnalytics() {
            const analyticsData = <?php echo json_encode($analytics_data ?? []); ?>;
            const classId = <?php echo json_encode($class_id); ?>;
            const subjectId = <?php echo json_encode($subject_id); ?>;
            const examType = <?php echo json_encode($exam_type); ?>;

            if (!analyticsData.statistics) {
                alert('No data to export');
                return;
            }

            // Create CSV content
            let csvContent = `Performance Analytics Report\n`;
            csvContent += `Class ID: ${classId}\n`;
            csvContent += `Subject ID: ${subjectId}\n`;
            csvContent += `Exam Type: ${examType || 'All'}\n`;
            csvContent += `Generated on: ${new Date().toLocaleDateString()}\n\n`;

            csvContent += `Statistics:\n`;
            csvContent += `Average Percentage,${analyticsData.statistics.avg_percentage?.toFixed(1) || 0}%\n`;
            csvContent += `Highest Percentage,${analyticsData.statistics.highest_percentage?.toFixed(1) || 0}%\n`;
            csvContent += `Lowest Percentage,${analyticsData.statistics.lowest_percentage?.toFixed(1) || 0}%\n`;
            csvContent += `Total Students,${analyticsData.statistics.total_students || 0}\n\n`;

            csvContent += `Grade Distribution:\n`;
            csvContent += `Grade,Count\n`;
            Object.entries(analyticsData.grade_distribution || {}).forEach(([grade, count]) => {
                csvContent += `${grade},${count}\n`;
            });

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `analytics_class_${classId}_subject_${subjectId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>