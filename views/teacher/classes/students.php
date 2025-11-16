<?php
/**
 * Teacher Class Students View
 * Shows list of students in a specific class with their details and performance
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Class Students'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .student-card {
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-present { color: #28a745; }
        .status-absent { color: #dc3545; }
        .status-late { color: #ffc107; }
        .performance-good { color: #28a745; }
        .performance-average { color: #ffc107; }
        .performance-poor { color: #dc3545; }
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
                        <a class="nav-link active" href="/teacher/classes">
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
                        <h5 class="mb-0"><i class="fas fa-chalkboard"></i> Class Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/teacher/classes" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> View Classes
                            </a>
                            <a href="/teacher/classes/subjects" class="btn btn-outline-success">
                                <i class="fas fa-book"></i> Manage Subjects
                            </a>
                            <a href="/teacher/classes/timetable?class_id=<?php echo $class->id; ?>" class="btn btn-outline-info">
                                <i class="fas fa-calendar-alt"></i> View Timetable
                            </a>
                            <a href="/teacher/classes/materials?class_id=<?php echo $class->id; ?>" class="btn btn-outline-warning">
                                <i class="fas fa-upload"></i> Study Materials
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Class Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Class Information</h6>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></h5>
                        <p class="text-muted mb-2">Academic Year: <?php echo htmlspecialchars($class->academic_year); ?></p>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-primary">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <div><strong><?php echo count($students); ?></strong></div>
                                    <div class="small">Students</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-success">
                                    <i class="fas fa-book fa-2x mb-2"></i>
                                    <div><strong><?php echo $this->getSubjectCount($class->id); ?></strong></div>
                                    <div class="small">Subjects</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-users"></i> Students - <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></h2>
                        <p class="text-muted">Manage and view student information</p>
                    </div>
                    <div>
                        <button class="btn btn-primary me-2" onclick="exportStudents()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-success" onclick="addStudent()">
                            <i class="fas fa-plus"></i> Add Student
                        </button>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Student List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Scholar No</th>
                                        <th>Name</th>
                                        <th>Roll No</th>
                                        <th>Attendance</th>
                                        <th>Performance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <?php
                                        $attendanceRate = $this->getStudentAttendanceRate($student->id);
                                        $performanceData = $this->getStudentPerformance($student->id);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student->scholar_number); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($student->photo_path): ?>
                                                        <img src="/<?php echo htmlspecialchars($student->photo_path); ?>"
                                                             class="rounded-circle me-2" width="32" height="32" alt="Photo">
                                                    <?php else: ?>
                                                        <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                             style="width: 32px; height: 32px;">
                                                            <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($student->email ?? 'No email'); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student->roll_number ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $this->getAttendanceBadgeClass($attendanceRate); ?>">
                                                    <?php echo round($attendanceRate, 1); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar <?php echo $this->getPerformanceBarClass($performanceData['average_marks']); ?>"
                                                             style="width: <?php echo min(100, $performanceData['average_marks']); ?>%"></div>
                                                    </div>
                                                    <span class="small"><?php echo round($performanceData['average_marks'], 1); ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewStudent(<?php echo $student->id; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="markAttendance(<?php echo $student->id; ?>)">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewResults(<?php echo $student->id; ?>)">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Student Details Modal -->
                <div class="modal fade" id="studentModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-user"></i> Student Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="studentModalBody">
                                <!-- Student details will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#studentsTable').DataTable({
                "pageLength": 25,
                "order": [[0, "asc"]],
                "responsive": true,
                "language": {
                    "search": "Search students:",
                    "lengthMenu": "Show _MENU_ students per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ students"
                }
            });
        });

        function viewStudent(studentId) {
            // Load student details via AJAX
            $.get(`/api/teacher/students/${studentId}`)
                .done(function(data) {
                    $('#studentModalBody').html(data.html);
                    $('#studentModal').modal('show');
                })
                .fail(function() {
                    alert('Failed to load student details');
                });
        }

        function markAttendance(studentId) {
            // Redirect to attendance marking for this student
            window.location.href = `/teacher/attendance/mark?student_id=${studentId}&class_id=<?php echo $class->id; ?>`;
        }

        function viewResults(studentId) {
            // Redirect to student results
            window.location.href = `/teacher/exams/results?student_id=${studentId}&class_id=<?php echo $class->id; ?>`;
        }

        function exportStudents() {
            window.location.href = `/teacher/classes/export-students?class_id=<?php echo $class->id; ?>`;
        }

        function addStudent() {
            window.location.href = `/admin/students/add?class_id=<?php echo $class->id; ?>`;
        }
    </script>
</body>
</html>