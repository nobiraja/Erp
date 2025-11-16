<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Marks Entry'); ?></title>
    <meta name="description" content="Enter exam marks for students">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

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
        .marks-input {
            width: 80px;
            text-align: center;
        }
        .grade-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .student-row:hover {
            background-color: #f8f9fa;
        }
        .marks-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                        <h5 class="mb-0">Marks Entry</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/teacher/exams">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Marks Entry</li>
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
                    <h6 class="mb-0"><i class="bi bi-filter me-2"></i>Select Exam & Subject</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="/teacher/exams/marks" class="row g-3">
                        <div class="col-md-4">
                            <label for="exam_id" class="form-label">Exam</label>
                            <select class="form-select" id="exam_id" name="exam_id" required>
                                <option value="">Choose exam...</option>
                                <?php foreach ($exams ?? [] as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>" <?php echo ($exam_id == $exam['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($exam['exam_name'] . ' - ' . $exam['class_name'] . ' ' . $exam['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">Choose subject...</option>
                                <?php foreach ($assignedSubjects ?? [] as $subject): ?>
                                    <option value="<?php echo $subject['subject_id']; ?>" <?php echo ($subject_id == $subject['subject_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Load Students
                                </button>
                                <a href="/teacher/exams" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($exam_data && !empty($students)): ?>
                <!-- Exam Information -->
                <div class="card mb-4">
                    <div class="card-header marks-summary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-white">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    <?php echo htmlspecialchars($exam_data->exam_name); ?>
                                </h6>
                                <small class="text-white-50">
                                    <?php echo htmlspecialchars($exam_data->class_name . ' ' . $exam_data->section); ?> |
                                    <?php echo date('M d, Y', strtotime($exam_data->start_date)); ?> - <?php echo date('M d, Y', strtotime($exam_data->end_date)); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="text-white-50 small">Students</div>
                                <div class="h4 text-white mb-0"><?php echo count($students); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marks Entry Form -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Enter Marks
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success" id="saveMarksBtn">
                                <i class="bi bi-check-circle me-1"></i>Save All Marks
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="calculateGradesBtn">
                                <i class="bi bi-calculator me-1"></i>Calculate Grades
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="marksForm">
                            <div class="table-responsive">
                                <table class="table table-hover" id="marksTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Scholar No.</th>
                                            <th>Student Name</th>
                                            <th class="text-center">Marks Obtained</th>
                                            <th class="text-center">Max Marks</th>
                                            <th class="text-center">Percentage</th>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $counter = 1; ?>
                                        <?php foreach ($students as $student): ?>
                                            <?php
                                            $existingMarks = $existing_marks[$student['id']] ?? null;
                                            $marksObtained = $existingMarks ? $existingMarks['marks_obtained'] : '';
                                            $maxMarks = $existingMarks ? $existingMarks['max_marks'] : '';
                                            $grade = $existingMarks ? $existingMarks['grade'] : '';
                                            $percentage = ($marksObtained && $maxMarks) ? round(($marksObtained / $maxMarks) * 100, 1) : '';
                                            ?>
                                            <tr class="student-row" data-student-id="<?php echo $student['id']; ?>">
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                           class="form-control form-control-sm marks-input marks-obtained"
                                                           name="marks[<?php echo $student['id']; ?>][obtained]"
                                                           value="<?php echo $marksObtained; ?>"
                                                           min="0"
                                                           step="0.5"
                                                           placeholder="0">
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                           class="form-control form-control-sm marks-input max-marks"
                                                           name="marks[<?php echo $student['id']; ?>][max]"
                                                           value="<?php echo $maxMarks ?: 100; ?>"
                                                           min="1"
                                                           step="0.5"
                                                           placeholder="100">
                                                </td>
                                                <td class="text-center">
                                                    <span class="percentage-display fw-bold"><?php echo $percentage ? $percentage . '%' : '-'; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($grade): ?>
                                                        <span class="badge grade-badge bg-<?php echo $this->getGradeColor($grade); ?>">
                                                            <?php echo $grade; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge grade-badge bg-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($existingMarks): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Entered
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-pencil me-1"></i>Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Students
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalStudents">
                                            <?php echo count($students); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people-fill fs-2 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Marks Entered
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="marksEntered">
                                            <?php echo count(array_filter($existing_marks)); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Average %
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="averagePercentage">
                                            -
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-graph-up fs-2 text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingMarks">
                                            <?php echo count($students) - count(array_filter($existing_marks)); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock fs-2 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($exam_id && $subject_id): ?>
                <!-- No students found -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">No Students Found</h5>
                        <p class="text-muted mb-0">No students are enrolled in the selected class.</p>
                        <a href="/teacher/exams/marks" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left me-1"></i>Back to Selection
                        </a>
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

            // Initialize marks entry functionality
            initializeMarksEntry();
        });

        // Initialize marks entry functionality
        function initializeMarksEntry() {
            // Auto-calculate percentage and grade on input change
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('marks-obtained') || e.target.classList.contains('max-marks')) {
                    const row = e.target.closest('tr');
                    calculateRowValues(row);
                    updateSummaryStats();
                }
            });

            // Save marks button
            const saveMarksBtn = document.getElementById('saveMarksBtn');
            if (saveMarksBtn) {
                saveMarksBtn.addEventListener('click', saveMarks);
            }

            // Calculate grades button
            const calculateGradesBtn = document.getElementById('calculateGradesBtn');
            if (calculateGradesBtn) {
                calculateGradesBtn.addEventListener('click', calculateAllGrades);
            }

            // Initial calculation for existing data
            updateSummaryStats();
        }

        // Calculate values for a single row
        function calculateRowValues(row) {
            const marksObtained = parseFloat(row.querySelector('.marks-obtained').value) || 0;
            const maxMarks = parseFloat(row.querySelector('.max-marks').value) || 100;
            const percentageDisplay = row.querySelector('.percentage-display');
            const gradeBadge = row.querySelector('.grade-badge');

            if (marksObtained > 0 && maxMarks > 0) {
                const percentage = Math.round((marksObtained / maxMarks) * 100 * 10) / 10; // Round to 1 decimal
                const grade = calculateGrade(percentage);

                percentageDisplay.textContent = percentage + '%';
                gradeBadge.textContent = grade;
                gradeBadge.className = `badge grade-badge bg-${getGradeColor(grade)}`;
            } else {
                percentageDisplay.textContent = '-';
                gradeBadge.textContent = '-';
                gradeBadge.className = 'badge grade-badge bg-secondary';
            }
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

        // Get Bootstrap color class for grade
        function getGradeColor(grade) {
            const colors = {
                'A+': 'success',
                'A': 'success',
                'B+': 'primary',
                'B': 'primary',
                'C+': 'warning',
                'C': 'warning',
                'D': 'danger',
                'F': 'danger'
            };
            return colors[grade] || 'secondary';
        }

        // Calculate grades for all rows
        function calculateAllGrades() {
            const rows = document.querySelectorAll('#marksTable tbody tr');
            rows.forEach(row => {
                calculateRowValues(row);
            });
            updateSummaryStats();
        }

        // Update summary statistics
        function updateSummaryStats() {
            const rows = document.querySelectorAll('#marksTable tbody tr');
            let totalStudents = rows.length;
            let marksEntered = 0;
            let totalPercentage = 0;
            let validPercentages = 0;

            rows.forEach(row => {
                const marksObtained = parseFloat(row.querySelector('.marks-obtained').value) || 0;
                const maxMarks = parseFloat(row.querySelector('.max-marks').value) || 100;

                if (marksObtained > 0) {
                    marksEntered++;
                    if (maxMarks > 0) {
                        const percentage = (marksObtained / maxMarks) * 100;
                        totalPercentage += percentage;
                        validPercentages++;
                    }
                }
            });

            // Update display
            document.getElementById('totalStudents').textContent = totalStudents;
            document.getElementById('marksEntered').textContent = marksEntered;
            document.getElementById('pendingMarks').textContent = totalStudents - marksEntered;

            if (validPercentages > 0) {
                const averagePercentage = Math.round((totalPercentage / validPercentages) * 10) / 10;
                document.getElementById('averagePercentage').textContent = averagePercentage + '%';
            } else {
                document.getElementById('averagePercentage').textContent = '-';
            }
        }

        // Save marks via AJAX
        function saveMarks() {
            const formData = new FormData(document.getElementById('marksForm'));
            const examId = <?php echo json_encode($exam_id); ?>;
            const subjectId = <?php echo json_encode($subject_id); ?>;

            if (!examId || !subjectId) {
                alert('Exam and subject must be selected');
                return;
            }

            // Convert form data to marks object
            const marksData = {};
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('marks[')) {
                    const matches = key.match(/marks\[(\d+)\]\[(\w+)\]/);
                    if (matches) {
                        const studentId = matches[1];
                        const field = matches[2];
                        if (!marksData[studentId]) {
                            marksData[studentId] = {};
                        }
                        marksData[studentId][field] = value;
                    }
                }
            }

            // Show loading state
            const saveBtn = document.getElementById('saveMarksBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
            saveBtn.disabled = true;

            // Send AJAX request
            fetch('/teacher/exams/saveMarks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    exam_id: examId,
                    subject_id: subjectId,
                    marks: marksData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message || 'Marks saved successfully!');

                    // Update status badges
                    Object.keys(marksData).forEach(studentId => {
                        const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
                        if (row) {
                            const statusCell = row.querySelector('td:last-child');
                            statusCell.innerHTML = `
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Entered
                                </span>
                            `;
                        }
                    });

                    // Update summary stats
                    updateSummaryStats();
                } else {
                    showAlert('danger', data.message || 'Failed to save marks');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while saving marks');
            })
            .finally(() => {
                // Restore button state
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        }

        // Show alert message
        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>