<?php
/**
 * Teacher Class Timetable View
 * Shows the timetable/schedule for a specific class
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Class Timetable'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .timetable-cell {
            min-height: 80px;
            border: 1px solid #dee2e6;
            padding: 8px;
            position: relative;
        }
        .timetable-header {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .subject-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }
        .time-slot {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            padding: 12px 8px;
        }
        .day-header {
            background-color: #495057;
            color: white;
            text-align: center;
            padding: 12px 8px;
            font-weight: bold;
        }
        .current-day {
            background-color: #d4edda !important;
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
                            <a href="/teacher/classes/subjects" class="btn btn-outline-success">
                                <i class="fas fa-book"></i> Manage Subjects
                            </a>
                            <a href="/teacher/classes/timetable?class_id=<?php echo $class->id; ?>" class="btn btn-outline-info active">
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
                        <div class="mb-3">
                            <strong>My Subjects:</strong>
                            <div class="mt-2">
                                <?php foreach ($timetable as $subject): ?>
                                    <span class="badge bg-primary me-1 mb-1"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                <?php endforeach; ?>
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
                            <button class="btn btn-outline-primary btn-sm" onclick="editTimetable()">
                                <i class="fas fa-edit"></i> Edit Timetable
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="printTimetable()">
                                <i class="fas fa-print"></i> Print Timetable
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportTimetable()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-calendar-alt"></i> Class Timetable</h2>
                        <p class="text-muted"><?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?> - Weekly Schedule</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshTimetable()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Timetable Display -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Weekly Timetable</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="time-slot">Time</th>
                                        <th class="day-header <?php echo (date('N') == 1) ? 'current-day' : ''; ?>">Monday</th>
                                        <th class="day-header <?php echo (date('N') == 2) ? 'current-day' : ''; ?>">Tuesday</th>
                                        <th class="day-header <?php echo (date('N') == 3) ? 'current-day' : ''; ?>">Wednesday</th>
                                        <th class="day-header <?php echo (date('N') == 4) ? 'current-day' : ''; ?>">Thursday</th>
                                        <th class="day-header <?php echo (date('N') == 5) ? 'current-day' : ''; ?>">Friday</th>
                                        <th class="day-header <?php echo (date('N') == 6) ? 'current-day' : ''; ?>">Saturday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $timeSlots = [
                                        '09:00 - 10:00',
                                        '10:00 - 11:00',
                                        '11:00 - 12:00',
                                        '12:00 - 13:00',
                                        '13:00 - 14:00', // Lunch break
                                        '14:00 - 15:00',
                                        '15:00 - 16:00',
                                        '16:00 - 17:00'
                                    ];

                                    // Sample timetable data (in real implementation, this would come from database)
                                    $sampleTimetable = $this->generateSampleTimetable($timetable);

                                    foreach ($timeSlots as $index => $timeSlot):
                                    ?>
                                        <tr>
                                            <td class="time-slot"><?php echo $timeSlot; ?></td>
                                            <?php for ($day = 1; $day <= 6; $day++): ?>
                                                <td class="timetable-cell">
                                                    <?php
                                                    $schedule = $sampleTimetable[$day][$index] ?? null;
                                                    if ($schedule):
                                                    ?>
                                                        <div class="subject-card">
                                                            <div class="fw-bold"><?php echo htmlspecialchars($schedule['subject_name']); ?></div>
                                                            <small><?php echo htmlspecialchars($schedule['teacher_name'] ?? $teacher->getFullName()); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-muted small text-center">
                                                            <?php if ($timeSlot == '13:00 - 14:00'): ?>
                                                                <i class="fas fa-utensils"></i><br>Lunch Break
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Subject Legend -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Subject Legend</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($timetable as $subject): ?>
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="subject-card me-2" style="width: 20px; height: 20px; padding: 0; border-radius: 50%;"></div>
                                        <span><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-calendar-day"></i> Today's Schedule (<?php echo date('l, F j, Y'); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $todaySchedule = $sampleTimetable[date('N')] ?? [];
                        $hasClasses = false;
                        foreach ($todaySchedule as $period):
                            if ($period):
                                $hasClasses = true;
                                break;
                            endif;
                        endforeach;
                        ?>

                        <?php if ($hasClasses): ?>
                            <div class="row">
                                <?php
                                $periodIndex = 0;
                                foreach ($timeSlots as $timeSlot):
                                    $period = $todaySchedule[$periodIndex] ?? null;
                                    if ($period):
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($period['subject_name']); ?></h6>
                                                        <p class="card-text small text-muted mb-1"><?php echo $timeSlot; ?></p>
                                                        <p class="card-text small mb-0">Teacher: <?php echo htmlspecialchars($period['teacher_name'] ?? $teacher->getFullName()); ?></p>
                                                    </div>
                                                    <div class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="takeAttendance(<?php echo $period['subject_id']; ?>)">
                                                            <i class="fas fa-calendar-check"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    endif;
                                    $periodIndex++;
                                endforeach;
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No classes scheduled for today</h5>
                                <p class="text-muted">Enjoy your day off!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function refreshTimetable() {
            location.reload();
        }

        function editTimetable() {
            alert('Timetable editing feature would be implemented here');
            // In real implementation, redirect to edit timetable page
        }

        function printTimetable() {
            window.print();
        }

        function exportTimetable() {
            window.location.href = `/teacher/classes/export-timetable?class_id=<?php echo $class->id; ?>`;
        }

        function takeAttendance(subjectId) {
            window.location.href = `/teacher/attendance/mark?class_id=<?php echo $class->id; ?>&subject_id=${subjectId}&date=<?php echo date('Y-m-d'); ?>`;
        }
    </script>
</body>
</html>