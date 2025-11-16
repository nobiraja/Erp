<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Student Performance Records'); ?></title>
    <meta name="description" content="View student performance records and academic history">

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
        .performance-card {
            transition: transform 0.3s;
        }
        .performance-card:hover {
            transform: translateY(-2px);
        }
        .grade-badge {
            font-size: 0.9rem;
            padding: 0.375rem 0.75rem;
        }
        .progress-custom {
            height: 8px;
        }
        .student-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
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
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
                        <h5 class="mb-0">Student Performance Records</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/teacher/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Performance</li>
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
                    <h6 class="mb-0"><i class="bi bi-search me-2"></i>Select Class & Student</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="/teacher/exams/performance" class="row g-3">
                        <div class="col-md-4">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-select" id="class_id" name="class_id" required onchange="loadStudents()">
                                <option value="">Choose class...</option>
                                <?php foreach ($classes ?? [] as $classId): ?>
                                    <?php
                                    $classData = ClassModel::find($classId);
                                    if ($classData):
                                    ?>
                                        <option value="<?php echo $classId; ?>" <?php echo ($class_id == $classId) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($classData->class_name . ' ' . $classData->section); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Choose student...</option>
                                <?php if ($class_data && !empty($students)): ?>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" <?php echo ($student_id == $student['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['scholar_number'] . ' - ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>View Performance
                                </button>
                                <a href="/teacher/exams" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($student_data && !empty($performance_data)): ?>
                <!-- Student Information Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="<?php echo $student_data->photo_path ? '/uploads/' . $student_data->photo_path : '/images/default-avatar.png'; ?>"
                                     alt="Student Photo" class="student-photo mb-2" onerror="this.src='/images/default-avatar.png'">
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-1"><?php echo htmlspecialchars($student_data->first_name . ' ' . ($student_data->middle_name ?? '') . ' ' . $student_data->last_name); ?></h4>
                                <p class="text-muted mb-2">Scholar No: <?php echo htmlspecialchars($student_data->scholar_number); ?></p>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Class:</small>
                                        <div><?php echo htmlspecialchars($class_data->class_name . ' ' . $class_data->section); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Admission No:</small>
                                        <div><?php echo htmlspecialchars($student_data->admission_number); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <div class="h6 mb-0"><?php echo count($performance_data); ?></div>
                                            <small class="text-muted">Total Exams</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <div class="h6 mb-0" id="avgPercentage">-</div>
                                            <small class="text-muted">Avg %</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Performance Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Performance Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Detailed Performance</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportPerformance()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="performanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Subject</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">Max Marks</th>
                                        <th class="text-center">Percentage</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalPercentage = 0;
                                    $examCount = 0;
                                    foreach ($performance_data as $record):
                                        $percentage = $record['max_marks'] > 0 ? round(($record['marks_obtained'] / $record['max_marks']) * 100, 1) : 0;
                                        $totalPercentage += $percentage;
                                        $examCount++;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['exam_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['subject_name']); ?></td>
                                            <td class="text-center fw-bold"><?php echo $record['marks_obtained']; ?></td>
                                            <td class="text-center"><?php echo $record['max_marks']; ?></td>
                                            <td class="text-center">
                                                <span class="fw-bold"><?php echo $percentage; ?>%</span>
                                                <div class="progress progress-custom mt-1">
                                                    <div class="progress-bar bg-<?php echo $this->getGradeColor($record['grade']); ?>"
                                                         style="width: <?php echo min($percentage, 100); ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge grade-badge bg-<?php echo $this->getGradeColor($record['grade']); ?>">
                                                    <?php echo $record['grade']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?php echo date('M d, Y', strtotime($record['start_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Performance Summary -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-primary shadow h-100 py-2 performance-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Average Percentage
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="summaryAvgPercentage">
                                            <?php echo $examCount > 0 ? round($totalPercentage / $examCount, 1) . '%' : '-'; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-graph-up fs-2 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-success shadow h-100 py-2 performance-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Highest Grade
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="highestGrade">
                                            <?php
                                            $grades = array_column($performance_data, 'grade');
                                            echo !empty($grades) ? max($grades) : '-';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-trophy fs-2 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-info shadow h-100 py-2 performance-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Exams
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count($performance_data); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-file-earmark-text fs-2 text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-warning shadow h-100 py-2 performance-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Grade Distribution
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <span id="gradeDistribution">-</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-bar-chart fs-2 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($class_id && !$student_id): ?>
                <!-- Student List for Selected Class -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Students in <?php echo htmlspecialchars($class_data->class_name . ' ' . $class_data->section); ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Scholar No.</th>
                                        <th>Student Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($students)): ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?></td>
                                                <td>
                                                    <a href="/teacher/exams/performance?class_id=<?php echo $class_id; ?>&student_id=<?php echo $student['id']; ?>"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye me-1"></i>View Performance
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                                                <p class="text-muted mb-0">No students found in this class.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif (!$class_id): ?>
                <!-- No selection made -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">Select Class and Student</h5>
                        <p class="text-muted mb-0">Choose a class and student to view their performance records.</p>
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

            // Initialize performance view
            initializePerformanceView();
        });

        // Load students when class is selected
        function loadStudents() {
            const classId = document.getElementById('class_id').value;
            const studentSelect = document.getElementById('student_id');

            if (!classId) {
                studentSelect.innerHTML = '<option value="">Choose student...</option>';
                return;
            }

            // Fetch students for the selected class
            fetch(`/teacher/classes/students?class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    studentSelect.innerHTML = '<option value="">Choose student...</option>';

                    if (data.students && data.students.length > 0) {
                        data.students.forEach(student => {
                            const option = document.createElement('option');
                            option.value = student.id;
                            option.textContent = `${student.scholar_number} - ${student.first_name} ${student.middle_name || ''} ${student.last_name}`;
                            studentSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    studentSelect.innerHTML = '<option value="">Error loading students</option>';
                });
        }

        // Initialize performance view
        function initializePerformanceView() {
            <?php if ($student_data && !empty($performance_data)): ?>
                // Initialize performance chart
                initializePerformanceChart();

                // Update summary statistics
                updatePerformanceSummary();
            <?php endif; ?>
        }

        // Initialize performance chart
        function initializePerformanceChart() {
            const performanceData = <?php echo json_encode($performance_data ?? []); ?>;

            if (performanceData.length === 0) return;

            const ctx = document.getElementById('performanceChart');
            if (!ctx) return;

            // Prepare data for chart
            const labels = performanceData.map(item => {
                const date = new Date(item.start_date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + item.subject_name;
            });

            const percentages = performanceData.map(item => {
                return item.max_marks > 0 ? Math.round((item.marks_obtained / item.max_marks) * 100 * 10) / 10 : 0;
            });

            const grades = performanceData.map(item => item.grade);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Percentage (%)',
                        data: percentages,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: percentages.map((percentage, index) => {
                            return getGradeColor(grades[index]);
                        }),
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
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
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const dataIndex = context.dataIndex;
                                    const item = performanceData[dataIndex];
                                    return [
                                        `Subject: ${item.subject_name}`,
                                        `Marks: ${item.marks_obtained}/${item.max_marks}`,
                                        `Percentage: ${context.parsed.y}%`,
                                        `Grade: ${item.grade}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }

        // Get color for grade
        function getGradeColor(grade) {
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
        }

        // Update performance summary
        function updatePerformanceSummary() {
            const performanceData = <?php echo json_encode($performance_data ?? []); ?>;

            if (performanceData.length === 0) return;

            // Calculate average percentage
            let totalPercentage = 0;
            const gradeCount = {};

            performanceData.forEach(item => {
                const percentage = item.max_marks > 0 ? (item.marks_obtained / item.max_marks) * 100 : 0;
                totalPercentage += percentage;

                if (!gradeCount[item.grade]) {
                    gradeCount[item.grade] = 0;
                }
                gradeCount[item.grade]++;
            });

            const avgPercentage = Math.round((totalPercentage / performanceData.length) * 10) / 10;

            // Update display
            document.getElementById('avgPercentage').textContent = avgPercentage + '%';
            document.getElementById('summaryAvgPercentage').textContent = avgPercentage + '%';

            // Update grade distribution
            const gradeEntries = Object.entries(gradeCount);
            if (gradeEntries.length > 0) {
                const topGrade = gradeEntries.reduce((a, b) => gradeCount[a[0]] > gradeCount[b[0]] ? a : b);
                document.getElementById('gradeDistribution').textContent = `${topGrade[0]} (${topGrade[1]})`;
            }
        }

        // Export performance data
        function exportPerformance() {
            const performanceData = <?php echo json_encode($performance_data ?? []); ?>;
            const studentData = <?php echo json_encode($student_data ? [
                'name' => $student_data->first_name . ' ' . ($student_data->middle_name ?? '') . ' ' . $student_data->last_name,
                'scholar_number' => $student_data->scholar_number,
                'class' => $class_data->class_name . ' ' . $class_data->section
            ] : null); ?>;

            if (!performanceData.length || !studentData) {
                alert('No data to export');
                return;
            }

            // Create CSV content
            let csvContent = `Student Performance Report\n`;
            csvContent += `Name: ${studentData.name}\n`;
            csvContent += `Scholar Number: ${studentData.scholar_number}\n`;
            csvContent += `Class: ${studentData.class}\n`;
            csvContent += `Generated on: ${new Date().toLocaleDateString()}\n\n`;

            csvContent += `Exam Name,Subject,Marks Obtained,Max Marks,Percentage,Grade,Date\n`;

            performanceData.forEach(item => {
                const percentage = item.max_marks > 0 ? Math.round((item.marks_obtained / item.max_marks) * 100 * 10) / 10 : 0;
                const date = new Date(item.start_date).toLocaleDateString();
                csvContent += `"${item.exam_name}","${item.subject_name}",${item.marks_obtained},${item.max_marks},${percentage},"${item.grade}","${date}"\n`;
            });

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `performance_${studentData.scholar_number}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>