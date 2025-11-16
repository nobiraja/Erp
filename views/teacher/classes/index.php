<?php
/**
 * Teacher Class Management - Main Page
 * Shows overview of assigned classes with student counts, subject assignments, and performance metrics
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'My Classes'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .class-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .class-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .performance-good { color: #28a745; }
        .performance-average { color: #ffc107; }
        .performance-poor { color: #dc3545; }
        .stats-icon {
            opacity: 0.8;
            font-size: 2rem;
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
                            <a href="/teacher/classes" class="btn btn-outline-primary active">
                                <i class="fas fa-eye"></i> View Classes
                            </a>
                            <a href="/teacher/classes/subjects" class="btn btn-outline-success">
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

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-primary">
                                    <i class="fas fa-users stats-icon"></i>
                                    <div class="mt-2">
                                        <strong><?php echo count($assignedSubjects); ?></strong>
                                        <div class="small">Classes</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-success">
                                    <i class="fas fa-book-open stats-icon"></i>
                                    <div class="mt-2">
                                        <strong><?php echo count($assignedSubjects); ?></strong>
                                        <div class="small">Subjects</div>
                                    </div>
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
                        <h2><i class="fas fa-chalkboard"></i> My Classes</h2>
                        <p class="text-muted">Manage your assigned classes and subjects</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Classes Grid -->
                <div class="row" id="classesContainer">
                    <?php if (empty($assignedSubjects)): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
                                    <h4>No Classes Assigned</h4>
                                    <p class="text-muted">You haven't been assigned to any classes yet.</p>
                                    <p class="text-muted">Please contact the administrator for class assignments.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                        $displayedClasses = [];
                        foreach ($assignedSubjects as $subject):
                            $classKey = $subject['class_id'] . '-' . $subject['section'];
                            if (!isset($displayedClasses[$classKey])):
                                $displayedClasses[$classKey] = true;
                                $classData = $this->getClassData($subject['class_id']);
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card class-card h-100" onclick="viewClassDetails(<?php echo $subject['class_id']; ?>)">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-graduation-cap"></i>
                                            <?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <div class="metric-card rounded p-2">
                                                    <i class="fas fa-users fa-lg mb-1"></i>
                                                    <div class="small">Students</div>
                                                    <div class="h5 mb-0"><?php echo $classData['student_count'] ?? 0; ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="metric-card rounded p-2">
                                                    <i class="fas fa-book fa-lg mb-1"></i>
                                                    <div class="small">Subjects</div>
                                                    <div class="h5 mb-0"><?php echo $classData['subject_count'] ?? 0; ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="metric-card rounded p-2">
                                                    <i class="fas fa-chart-line fa-lg mb-1"></i>
                                                    <div class="small">Attendance</div>
                                                    <div class="h5 mb-0 <?php echo $this->getAttendanceClass($classData['attendance_rate'] ?? 0); ?>">
                                                        <?php echo round($classData['attendance_rate'] ?? 0, 1); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <h6><i class="fas fa-book-open"></i> My Subjects:</h6>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php
                                                $classSubjects = array_filter($assignedSubjects, function($s) use ($subject) {
                                                    return $s['class_id'] == $subject['class_id'];
                                                });
                                                foreach ($classSubjects as $classSubject):
                                                ?>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($classSubject['subject_name']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); viewStudents(<?php echo $subject['class_id']; ?>)">
                                                <i class="fas fa-users"></i> View Students
                                            </button>
                                            <button class="btn btn-outline-success btn-sm" onclick="event.stopPropagation(); viewTimetable(<?php echo $subject['class_id']; ?>)">
                                                <i class="fas fa-calendar-alt"></i> Timetable
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    <?php endif; ?>
                </div>

                <!-- Performance Summary -->
                <?php if (!empty($assignedSubjects)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Class Performance Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Students</th>
                                                <th>Attendance Rate</th>
                                                <th>Subjects</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $displayedClasses = [];
                                            foreach ($assignedSubjects as $subject):
                                                $classKey = $subject['class_id'] . '-' . $subject['section'];
                                                if (!isset($displayedClasses[$classKey])):
                                                    $displayedClasses[$classKey] = true;
                                                    $classData = $this->getClassData($subject['class_id']);
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?></strong>
                                                    </td>
                                                    <td><?php echo $classData['student_count'] ?? 0; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $this->getAttendanceBadgeClass($classData['attendance_rate'] ?? 0); ?>">
                                                            <?php echo round($classData['attendance_rate'] ?? 0, 1); ?>%
                                                        </span>
                                                    </td>
                                                    <td><?php echo $classData['subject_count'] ?? 0; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewStudents(<?php echo $subject['class_id']; ?>)">
                                                            <i class="fas fa-users"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewTimetable(<?php echo $subject['class_id']; ?>)">
                                                            <i class="fas fa-calendar"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success" onclick="uploadMaterials(<?php echo $subject['class_id']; ?>)">
                                                            <i class="fas fa-upload"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php
                                                endif;
                                            endforeach;
                                            ?>
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
        function viewClassDetails(classId) {
            window.location.href = `/teacher/classes/students?class_id=${classId}`;
        }

        function viewStudents(classId) {
            window.location.href = `/teacher/classes/students?class_id=${classId}`;
        }

        function viewTimetable(classId) {
            window.location.href = `/teacher/classes/timetable?class_id=${classId}`;
        }

        function uploadMaterials(classId) {
            window.location.href = `/teacher/classes/materials?class_id=${classId}`;
        }

        function refreshData() {
            location.reload();
        }

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            // Optional: Implement AJAX refresh for dynamic data
            console.log('Refreshing class data...');
        }, 300000);
    </script>
</body>
</html>