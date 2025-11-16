<?php
/**
 * Teacher Attendance Management - Main Page
 * Allows teachers to select class, subject, and date for attendance marking
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Attendance Management'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .attendance-card {
            transition: transform 0.2s;
        }
        .attendance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                        <a class="nav-link active" href="/teacher/attendance">
                            <i class="fas fa-calendar-check"></i> Attendance
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Attendance Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/teacher/attendance" class="btn btn-outline-primary active">
                                <i class="fas fa-plus-circle"></i> Mark Attendance
                            </a>
                            <a href="/teacher/attendance/reports" class="btn btn-outline-success">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                            <a href="/teacher/attendance/history" class="btn btn-outline-info">
                                <i class="fas fa-history"></i> Attendance History
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Today's Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="status-present">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    <div class="mt-1">Present</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="status-absent">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                    <div class="mt-1">Absent</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="status-late">
                                    <i class="fas fa-clock fa-2x"></i>
                                    <div class="mt-1">Late</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check"></i> Mark Daily Attendance</h4>
                    </div>
                    <div class="card-body">
                        <!-- Class and Subject Selection -->
                        <form id="attendanceForm" method="GET" action="/teacher/attendance/mark">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="class_id" class="form-label">Select Class</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">Choose a class...</option>
                                        <?php foreach ($assignedSubjects as $subject): ?>
                                            <?php
                                            $classKey = $subject['class_id'] . '-' . $subject['section'];
                                            if (!isset($displayedClasses[$classKey])) {
                                                $displayedClasses[$classKey] = true;
                                            ?>
                                            <option value="<?php echo $subject['class_id']; ?>">
                                                <?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?>
                                            </option>
                                            <?php } ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="subject_id" class="form-label">Select Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id" required disabled>
                                        <option value="">Choose a subject...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary" id="markAttendanceBtn">
                                    <i class="fas fa-check-circle"></i> Mark Attendance
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </form>

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card attendance-card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-bolt text-primary fa-3x mb-3"></i>
                                        <h5>Bulk Mark Present</h5>
                                        <p class="text-muted">Mark all students as present for the selected class</p>
                                        <button class="btn btn-outline-primary" onclick="bulkMark('present')">
                                            <i class="fas fa-check"></i> Mark All Present
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card attendance-card border-warning">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-day text-warning fa-3x mb-3"></i>
                                        <h5>Today's Overview</h5>
                                        <p class="text-muted">View attendance status for today</p>
                                        <button class="btn btn-outline-warning" onclick="viewTodayAttendance()">
                                            <i class="fas fa-eye"></i> View Today
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Store assigned subjects data
        const assignedSubjects = <?php echo json_encode($assignedSubjects); ?>;

        $(document).ready(function() {
            // Class change handler
            $('#class_id').change(function() {
                const classId = $(this).val();
                const subjectSelect = $('#subject_id');

                subjectSelect.empty().append('<option value="">Choose a subject...</option>');

                if (classId) {
                    // Filter subjects for selected class
                    const classSubjects = assignedSubjects.filter(subject => subject.class_id == classId);

                    classSubjects.forEach(subject => {
                        subjectSelect.append(
                            `<option value="${subject.subject_id}">${subject.subject_name}</option>`
                        );
                    });

                    subjectSelect.prop('disabled', false);
                } else {
                    subjectSelect.prop('disabled', true);
                }
            });

            // Form validation
            $('#attendanceForm').submit(function(e) {
                const classId = $('#class_id').val();
                const subjectId = $('#subject_id').val();
                const date = $('#date').val();

                if (!classId || !subjectId || !date) {
                    e.preventDefault();
                    alert('Please select class, subject, and date');
                    return false;
                }

                $('#markAttendanceBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            });
        });

        function resetForm() {
            $('#attendanceForm')[0].reset();
            $('#subject_id').prop('disabled', true).empty().append('<option value="">Choose a subject...</option>');
        }

        function bulkMark(status) {
            const classId = $('#class_id').val();
            const subjectId = $('#subject_id').val();

            if (!classId || !subjectId) {
                alert('Please select class and subject first');
                return;
            }

            if (!confirm(`Are you sure you want to mark all students as ${status}?`)) {
                return;
            }

            // This would typically make an AJAX call to bulk mark attendance
            alert('Bulk marking feature would be implemented here');
        }

        function viewTodayAttendance() {
            const classId = $('#class_id').val();
            const subjectId = $('#subject_id').val();

            if (!classId || !subjectId) {
                alert('Please select class and subject first');
                return;
            }

            // Redirect to reports with today's date
            window.location.href = `/teacher/attendance/reports?class_id=${classId}&subject_id=${subjectId}&start_date=${new Date().toISOString().split('T')[0]}&end_date=${new Date().toISOString().split('T')[0]}`;
        }
    </script>
</body>
</html>