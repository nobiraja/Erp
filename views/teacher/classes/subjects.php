<?php
/**
 * Teacher Subjects Management View
 * Shows and manages assigned subjects for the teacher
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Manage Subjects'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .subject-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .subject-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .class-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .performance-indicator {
            width: 100px;
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        .performance-fill {
            height: 100%;
            transition: width 0.3s ease;
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
                            <a href="/teacher/classes/subjects" class="btn btn-outline-success active">
                                <i class="fas fa-book"></i> Manage Subjects
                            </a>
                            <a href="/teacher/classes/timetable" class="btn btn-outline-info">
                                <i class="fas fa-calendar-alt"></i> View Timetable
                            </a>
                            <a href="/teacher/classes/materials" class="btn btn-outline-warning">
                                <i class="fas fa-upload"></i> Study Materials
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subject Statistics -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Subject Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-primary">
                                    <i class="fas fa-book fa-2x mb-2"></i>
                                    <div><strong><?php echo count($assignedSubjects); ?></strong></div>
                                    <div class="small">Total Subjects</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-success">
                                    <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                    <div><strong><?php echo count(array_unique(array_column($assignedSubjects, 'class_id'))); ?></strong></div>
                                    <div class="small">Classes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshSubjects()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportSubjects()">
                                <i class="fas fa-download"></i> Export List
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-book"></i> My Subjects</h2>
                        <p class="text-muted">Manage and view your assigned subjects</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshSubjects()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Subjects Grid -->
                <div class="row" id="subjectsContainer">
                    <?php if (empty($assignedSubjects)): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                                    <h4>No Subjects Assigned</h4>
                                    <p class="text-muted">You haven't been assigned to any subjects yet.</p>
                                    <p class="text-muted">Please contact the administrator for subject assignments.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($assignedSubjects as $subject): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card subject-card h-100">
                                    <div class="card-header class-badge">
                                        <h6 class="mb-0 text-white">
                                            <i class="fas fa-graduation-cap"></i>
                                            <?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                                                <p class="card-text small text-muted mb-0"><?php echo htmlspecialchars($subject['subject_code']); ?></p>
                                            </div>
                                            <div class="ms-2">
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="stats-card rounded p-2">
                                                        <i class="fas fa-users fa-lg mb-1"></i>
                                                        <div class="small">Students</div>
                                                        <div class="h6 mb-0"><?php echo $this->getSubjectStudentCount($subject['class_id'], $subject['subject_id']); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stats-card rounded p-2">
                                                        <i class="fas fa-calendar-check fa-lg mb-1"></i>
                                                        <div class="small">Attendance</div>
                                                        <div class="h6 mb-0"><?php echo $this->getSubjectAttendanceRate($subject['class_id'], $subject['subject_id']); ?>%</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stats-card rounded p-2">
                                                        <i class="fas fa-chart-line fa-lg mb-1"></i>
                                                        <div class="small">Performance</div>
                                                        <div class="h6 mb-0"><?php echo $this->getSubjectPerformance($subject['class_id'], $subject['subject_id']); ?>%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Performance Trend:</small>
                                            <div class="performance-indicator mt-1">
                                                <div class="performance-fill bg-success" style="width: <?php echo min(100, $this->getSubjectPerformance($subject['class_id'], $subject['subject_id'])); ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="viewSubjectDetails(<?php echo $subject['subject_id']; ?>, <?php echo $subject['class_id']; ?>)">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-success btn-sm" onclick="event.stopPropagation(); takeAttendance(<?php echo $subject['class_id']; ?>, <?php echo $subject['subject_id']; ?>)">
                                                    <i class="fas fa-calendar-check"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-sm" onclick="event.stopPropagation(); viewResults(<?php echo $subject['class_id']; ?>, <?php echo $subject['subject_id']; ?>)">
                                                    <i class="fas fa-chart-bar"></i>
                                                </button>
                                                <button class="btn btn-outline-warning btn-sm" onclick="event.stopPropagation(); uploadMaterials(<?php echo $subject['class_id']; ?>, <?php echo $subject['subject_id']; ?>)">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Subject Performance Summary -->
                <?php if (!empty($assignedSubjects)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Subject Performance Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Class</th>
                                                <th>Students</th>
                                                <th>Avg Attendance</th>
                                                <th>Avg Performance</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignedSubjects as $subject): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($subject['subject_code']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?></td>
                                                    <td><?php echo $this->getSubjectStudentCount($subject['class_id'], $subject['subject_id']); ?></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo $this->getSubjectAttendanceRate($subject['class_id'], $subject['subject_id']); ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo $this->getSubjectPerformance($subject['class_id'], $subject['subject_id']); ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewSubjectDetails(<?php echo $subject['subject_id']; ?>, <?php echo $subject['class_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success me-1" onclick="takeAttendance(<?php echo $subject['class_id']; ?>, <?php echo $subject['subject_id']; ?>)">
                                                            <i class="fas fa-calendar-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewResults(<?php echo $subject['class_id']; ?>, <?php echo $subject['subject_id']; ?>)">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function viewSubjectDetails(subjectId, classId) {
            // Redirect to subject details page
            window.location.href = `/teacher/subjects/details?subject_id=${subjectId}&class_id=${classId}`;
        }

        function takeAttendance(classId, subjectId) {
            // Redirect to attendance marking for this subject
            window.location.href = `/teacher/attendance/mark?class_id=${classId}&subject_id=${subjectId}&date=<?php echo date('Y-m-d'); ?>`;
        }

        function viewResults(classId, subjectId) {
            // Redirect to exam results for this subject
            window.location.href = `/teacher/exams/results?class_id=${classId}&subject_id=${subjectId}`;
        }

        function uploadMaterials(classId, subjectId) {
            // Redirect to materials upload for this subject
            window.location.href = `/teacher/classes/materials?class_id=${classId}&subject_id=${subjectId}`;
        }

        function refreshSubjects() {
            location.reload();
        }

        function exportSubjects() {
            window.location.href = `/teacher/classes/export-subjects`;
        }
    </script>
</body>
</html>