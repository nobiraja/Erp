<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Exam Results'); ?></title>
    <meta name="description" content="Enter and view exam results in the school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
        .result-input {
            width: 80px;
            text-align: center;
        }
        .student-row {
            transition: background-color 0.2s;
        }
        .student-row:hover {
            background-color: #f8f9fa;
        }
        .subject-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .marks-cell {
            min-width: 120px;
        }
        .grade-badge {
            font-size: 0.75rem;
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
                        <h5 class="mb-0">Exam Results - <?php echo htmlspecialchars($exam->exam_name); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Results</li>
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
                            <li><a class="dropdown-item" href="#" onclick="generateMarksheets()">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Generate Marksheets
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportResults()">
                                <i class="bi bi-download me-2"></i>Export Results
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/exams/schedule?exam_id=<?php echo $exam->id; ?>">
                                <i class="bi bi-calendar me-2"></i>Manage Schedule
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Results Content -->
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

            <!-- Results Form -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>
                        Enter Results
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveAllResults()">
                        <i class="bi bi-check-circle me-1"></i>Save All Results
                    </button>
                </div>
                <div class="card-body">
                    <form id="resultsForm">
                        <input type="hidden" name="exam_id" value="<?php echo $exam->id; ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered" id="resultsTable">
                                <thead>
                                    <tr class="subject-header">
                                        <th rowspan="2" class="align-middle">Student</th>
                                        <th rowspan="2" class="align-middle">Scholar No.</th>
                                        <?php
                                        // Get subjects for this class
                                        $subjects = ClassSubjectModel::getByClass($exam->class_id);
                                        foreach ($subjects as $subject): ?>
                                            <th colspan="2" class="text-center marks-cell">
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th rowspan="2" class="align-middle">Total</th>
                                        <th rowspan="2" class="align-middle">Percentage</th>
                                        <th rowspan="2" class="align-middle">Grade</th>
                                        <th rowspan="2" class="align-middle">Rank</th>
                                    </tr>
                                    <tr class="subject-header">
                                        <?php foreach ($subjects as $subject): ?>
                                            <th class="text-center">Marks</th>
                                            <th class="text-center">Max</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr class="student-row" data-student-id="<?php echo $student['id']; ?>">
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                                            <?php
                                            $totalMarks = 0;
                                            $totalMax = 0;
                                            foreach ($subjects as $subject):
                                                $result = $results_by_student[$student['id']]['subjects'][$subject['id']] ?? null;
                                                $marks = $result ? $result['marks_obtained'] : '';
                                                $maxMarks = $result ? $result['max_marks'] : '';
                                                $totalMarks += floatval($marks);
                                                $totalMax += floatval($maxMarks);
                                            ?>
                                                <td class="text-center">
                                                    <input type="number" class="form-control form-control-sm result-input marks-input"
                                                           name="results[<?php echo $student['id']; ?>][<?php echo $subject['id']; ?>][marks_obtained]"
                                                           value="<?php echo $marks; ?>" min="0" step="0.5"
                                                           data-subject-id="<?php echo $subject['id']; ?>">
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" class="form-control form-control-sm result-input max-marks-input"
                                                           name="results[<?php echo $student['id']; ?>][<?php echo $subject['id']; ?>][max_marks]"
                                                           value="<?php echo $maxMarks; ?>" min="0" step="0.5"
                                                           data-subject-id="<?php echo $subject['id']; ?>">
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center fw-bold total-marks"><?php echo $totalMarks > 0 ? number_format($totalMarks, 1) : '-'; ?></td>
                                            <td class="text-center fw-bold percentage"><?php echo $totalMax > 0 ? number_format(($totalMarks / $totalMax) * 100, 1) . '%' : '-'; ?></td>
                                            <td class="text-center">
                                                <span class="badge grade-badge bg-secondary grade">-</span>
                                            </td>
                                            <td class="text-center rank">-</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Enter marks for each subject. Total, percentage, grade, and rank will be calculated automatically.
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="clearAllResults()">
                                    <i class="bi bi-x-circle me-1"></i>Clear All
                                </button>
                                <button type="button" class="btn btn-primary" onclick="saveAllResults()">
                                    <i class="bi bi-check-circle me-1"></i>Save All Results
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Grade Distribution
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="gradeChart" width="100" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Performance Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary mb-1" id="avgPercentage">-</h4>
                                    <small class="text-muted">Average %</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1" id="passPercentage">-</h4>
                                    <small class="text-muted">Pass Rate</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-info mb-1" id="highestScore">-</h6>
                                    <small class="text-muted">Highest Score</small>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-warning mb-1" id="lowestScore">-</h6>
                                    <small class="text-muted">Lowest Score</small>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            // Initialize calculations
            calculateAllResults();
            updateSummaryStats();

            // Add event listeners for input changes
            document.querySelectorAll('.marks-input, .max-marks-input').forEach(input => {
                input.addEventListener('input', function() {
                    const studentRow = this.closest('tr');
                    calculateStudentResults(studentRow);
                    updateSummaryStats();
                });
            });
        });

        // Calculate results for a specific student
        function calculateStudentResults(studentRow) {
            const marksInputs = studentRow.querySelectorAll('.marks-input');
            const maxMarksInputs = studentRow.querySelectorAll('.max-marks-input');
            const totalMarksCell = studentRow.querySelector('.total-marks');
            const percentageCell = studentRow.querySelector('.percentage');
            const gradeCell = studentRow.querySelector('.grade');

            let totalMarks = 0;
            let totalMax = 0;

            marksInputs.forEach((input, index) => {
                const marks = parseFloat(input.value) || 0;
                const maxMarks = parseFloat(maxMarksInputs[index].value) || 0;

                totalMarks += marks;
                totalMax += maxMarks;
            });

            const percentage = totalMax > 0 ? (totalMarks / totalMax) * 100 : 0;
            const grade = calculateGrade(percentage);

            totalMarksCell.textContent = totalMarks > 0 ? totalMarks.toFixed(1) : '-';
            percentageCell.textContent = totalMax > 0 ? percentage.toFixed(1) + '%' : '-';
            gradeCell.textContent = grade;
            gradeCell.className = `badge grade-badge bg-${getGradeColor(grade)}`;
        }

        // Calculate all results and rankings
        function calculateAllResults() {
            const studentRows = document.querySelectorAll('#resultsTable tbody tr');

            // Calculate individual results first
            studentRows.forEach(row => {
                calculateStudentResults(row);
            });

            // Calculate rankings
            const results = Array.from(studentRows).map(row => {
                const percentage = parseFloat(row.querySelector('.percentage').textContent.replace('%', '')) || 0;
                return { row, percentage };
            });

            results.sort((a, b) => b.percentage - a.percentage);

            results.forEach((result, index) => {
                const rankCell = result.row.querySelector('.rank');
                rankCell.textContent = result.percentage > 0 ? (index + 1) : '-';
            });
        }

        // Calculate grade based on percentage
        function calculateGrade(percentage) {
            if (percentage >= 90) return 'A+';
            if (percentage >= 80) return 'A';
            if (percentage >= 70) return 'B+';
            if (percentage >= 60) return 'B';
            if (percentage >= 50) return 'C+';
            if (percentage >= 40) return 'C';
            if (percentage >= 33) return 'D';
            return 'F';
        }

        // Get grade color for badge
        function getGradeColor(grade) {
            switch (grade) {
                case 'A+': return 'success';
                case 'A': return 'success';
                case 'B+': return 'primary';
                case 'B': return 'primary';
                case 'C+': return 'warning';
                case 'C': return 'warning';
                case 'D': return 'secondary';
                case 'F': return 'danger';
                default: return 'secondary';
            }
        }

        // Update summary statistics
        function updateSummaryStats() {
            const studentRows = document.querySelectorAll('#resultsTable tbody tr');
            let totalStudents = 0;
            let totalPercentage = 0;
            let passCount = 0;
            let highestScore = 0;
            let lowestScore = 100;

            studentRows.forEach(row => {
                const percentageText = row.querySelector('.percentage').textContent;
                if (percentageText !== '-') {
                    const percentage = parseFloat(percentageText.replace('%', ''));
                    totalStudents++;
                    totalPercentage += percentage;

                    if (percentage >= 33) passCount++; // Assuming 33% is pass mark

                    if (percentage > highestScore) highestScore = percentage;
                    if (percentage < lowestScore) lowestScore = percentage;
                }
            });

            const avgPercentage = totalStudents > 0 ? (totalPercentage / totalStudents).toFixed(1) : '-';
            const passPercentage = totalStudents > 0 ? ((passCount / totalStudents) * 100).toFixed(1) : '-';

            document.getElementById('avgPercentage').textContent = avgPercentage + '%';
            document.getElementById('passPercentage').textContent = passPercentage + '%';
            document.getElementById('highestScore').textContent = highestScore.toFixed(1) + '%';
            document.getElementById('lowestScore').textContent = lowestScore.toFixed(1) + '%';

            // Update grade distribution chart
            updateGradeChart();
        }

        // Update grade distribution chart
        function updateGradeChart() {
            const gradeCounts = { 'A+': 0, 'A': 0, 'B+': 0, 'B': 0, 'C+': 0, 'C': 0, 'D': 0, 'F': 0 };

            document.querySelectorAll('.grade').forEach(gradeElement => {
                const grade = gradeElement.textContent;
                if (grade in gradeCounts) {
                    gradeCounts[grade]++;
                }
            });

            const ctx = document.getElementById('gradeChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(gradeCounts),
                    datasets: [{
                        label: 'Number of Students',
                        data: Object.values(gradeCounts),
                        backgroundColor: [
                            '#28a745', '#28a745', '#007bff', '#007bff',
                            '#ffc107', '#ffc107', '#6c757d', '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
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

        // Save all results
        function saveAllResults() {
            const formData = new FormData(document.getElementById('resultsForm'));

            fetch('/admin/exams/save-results', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Results saved successfully!');
                    calculateAllResults();
                    updateSummaryStats();
                } else {
                    alert('Failed to save results: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error saving results');
                console.error('Save error:', error);
            });
        }

        // Clear all results
        function clearAllResults() {
            if (confirm('Are you sure you want to clear all entered results? This action cannot be undone.')) {
                document.querySelectorAll('.marks-input, .max-marks-input').forEach(input => {
                    input.value = '';
                });
                calculateAllResults();
                updateSummaryStats();
            }
        }

        // Generate marksheets
        function generateMarksheets() {
            window.open(`/admin/exams/marksheets?exam_id=<?php echo $exam->id; ?>&format=bulk`, '_blank');
        }

        // Export results
        function exportResults() {
            window.open(`/admin/exams/export-results?exam_id=<?php echo $exam->id; ?>`, '_blank');
        }
    </script>
</body>
</html>