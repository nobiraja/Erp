<?php
/**
 * Teacher Attendance Marking Form
 * Allows teachers to mark attendance for students in their assigned classes
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Mark Attendance'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .student-row {
            transition: background-color 0.2s;
        }
        .student-row:hover {
            background-color: #f8f9fa;
        }
        .status-btn {
            min-width: 100px;
        }
        .status-present {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .status-absent {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .status-late {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .bulk-actions {
            position: sticky;
            top: 20px;
            z-index: 100;
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
            <!-- Sidebar with Bulk Actions -->
            <div class="col-md-3">
                <div class="card bulk-actions">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tasks"></i> Bulk Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-sm" onclick="bulkMark('present')">
                                <i class="fas fa-check-circle"></i> Mark All Present
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="bulkMark('absent')">
                                <i class="fas fa-times-circle"></i> Mark All Absent
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="bulkMark('late')">
                                <i class="fas fa-clock"></i> Mark All Late
                            </button>
                            <hr>
                            <button class="btn btn-secondary btn-sm" onclick="clearAll()">
                                <i class="fas fa-undo"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Class Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Class Information</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Class:</strong> <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?></p>
                        <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($subject->subject_name); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($date)); ?></p>
                        <p class="mb-0"><strong>Students:</strong> <?php echo count($students); ?></p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Current Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <span class="badge bg-success fs-6" id="present-count">0</span>
                                <div class="mt-1 small">Present</div>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-danger fs-6" id="absent-count">0</span>
                                <div class="mt-1 small">Absent</div>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-warning fs-6" id="late-count">0</span>
                                <div class="mt-1 small">Late</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-check"></i>
                            Mark Attendance - <?php echo htmlspecialchars($class->class_name . ' ' . $class->section); ?>
                        </h4>
                        <div>
                            <a href="/teacher/attendance" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="attendanceForm" method="POST" action="/teacher/attendance/save">
                            <input type="hidden" name="class_id" value="<?php echo $class->id; ?>">
                            <input type="hidden" name="subject_id" value="<?php echo $subject->id; ?>">
                            <input type="hidden" name="date" value="<?php echo $date; ?>">

                            <!-- Student List -->
                            <div class="table-responsive">
                                <table class="table table-hover" id="attendanceTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Scholar No.</th>
                                            <th width="35%">Student Name</th>
                                            <th width="25%">Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $counter = 1; ?>
                                        <?php foreach ($students as $student): ?>
                                            <?php
                                            $existingStatus = isset($existing_attendance[$student->id]) ? $existing_attendance[$student->id]->status : '';
                                            ?>
                                            <tr class="student-row">
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($student->scholar_number); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name); ?>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="attendance[<?php echo $student->id; ?>]" value="" id="status_<?php echo $student->id; ?>">
                                                    <span class="status-display" id="display_<?php echo $student->id; ?>">
                                                        <?php if ($existingStatus): ?>
                                                            <span class="badge status-<?php echo $existingStatus; ?>">
                                                                <i class="fas fa-<?php echo $existingStatus === 'present' ? 'check' : ($existingStatus === 'absent' ? 'times' : 'clock'); ?>"></i>
                                                                <?php echo ucfirst($existingStatus); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not marked</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success status-btn"
                                                                onclick="setStatus(<?php echo $student->id; ?>, 'present')">
                                                            <i class="fas fa-check"></i> P
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger status-btn"
                                                                onclick="setStatus(<?php echo $student->id; ?>, 'absent')">
                                                            <i class="fas fa-times"></i> A
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning status-btn"
                                                                onclick="setStatus(<?php echo $student->id; ?>, 'late')">
                                                            <i class="fas fa-clock"></i> L
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-4 d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-secondary" onclick="resetAttendance()">
                                        <i class="fas fa-undo"></i> Reset Changes
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">
                                        <i class="fas fa-save"></i> Save Attendance
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="messageToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="messageContent"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Store initial attendance data
        let attendanceData = {};
        let initialData = {};

        $(document).ready(function() {
            // Initialize attendance data
            <?php foreach ($students as $student): ?>
                <?php $existingStatus = isset($existing_attendance[$student->id]) ? $existing_attendance[$student->id]->status : ''; ?>
                attendanceData[<?php echo $student->id; ?>] = '<?php echo $existingStatus; ?>';
                initialData[<?php echo $student->id; ?>] = '<?php echo $existingStatus; ?>';
            <?php endforeach; ?>

            updateCounts();
            updateFormData();

            // Form submission
            $('#attendanceForm').submit(function(e) {
                e.preventDefault();

                const hasChanges = hasUnsavedChanges();
                if (!hasChanges) {
                    showMessage('No changes to save', 'warning');
                    return;
                }

                if (!confirm('Are you sure you want to save the attendance?')) {
                    return;
                }

                $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message || 'Attendance saved successfully!', 'success');
                            // Update initial data to reflect saved state
                            initialData = {...attendanceData};
                            updateCounts();
                        } else {
                            showMessage(response.message || 'Failed to save attendance', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        let message = 'An error occurred while saving attendance';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showMessage(message, 'danger');
                    },
                    complete: function() {
                        $('#saveBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Attendance');
                    }
                });
            });
        });

        function setStatus(studentId, status) {
            attendanceData[studentId] = status;
            updateStudentDisplay(studentId);
            updateCounts();
            updateFormData();
        }

        function updateStudentDisplay(studentId) {
            const status = attendanceData[studentId];
            const displayElement = $(`#display_${studentId}`);

            if (status) {
                const iconClass = status === 'present' ? 'check' : (status === 'absent' ? 'times' : 'clock');
                const badgeClass = status === 'present' ? 'bg-success' : (status === 'absent' ? 'bg-danger' : 'bg-warning');
                displayElement.html(`
                    <span class="badge ${badgeClass}">
                        <i class="fas fa-${iconClass}"></i> ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                `);
            } else {
                displayElement.html('<span class="text-muted">Not marked</span>');
            }
        }

        function bulkMark(status) {
            if (!confirm(`Mark all students as ${status}?`)) {
                return;
            }

            Object.keys(attendanceData).forEach(studentId => {
                attendanceData[studentId] = status;
                updateStudentDisplay(studentId);
            });

            updateCounts();
            updateFormData();
        }

        function clearAll() {
            if (!confirm('Clear all attendance markings?')) {
                return;
            }

            Object.keys(attendanceData).forEach(studentId => {
                attendanceData[studentId] = '';
                updateStudentDisplay(studentId);
            });

            updateCounts();
            updateFormData();
        }

        function resetAttendance() {
            if (!confirm('Reset to last saved state?')) {
                return;
            }

            attendanceData = {...initialData};
            Object.keys(attendanceData).forEach(studentId => {
                updateStudentDisplay(studentId);
            });

            updateCounts();
            updateFormData();
        }

        function updateCounts() {
            let present = 0, absent = 0, late = 0;

            Object.values(attendanceData).forEach(status => {
                if (status === 'present') present++;
                else if (status === 'absent') absent++;
                else if (status === 'late') late++;
            });

            $('#present-count').text(present);
            $('#absent-count').text(absent);
            $('#late-count').text(late);
        }

        function updateFormData() {
            Object.keys(attendanceData).forEach(studentId => {
                $(`#status_${studentId}`).val(attendanceData[studentId]);
            });
        }

        function hasUnsavedChanges() {
            return JSON.stringify(attendanceData) !== JSON.stringify(initialData);
        }

        function showMessage(message, type) {
            const toast = $('#messageToast');
            const content = $('#messageContent');

            toast.removeClass('bg-success bg-danger bg-warning');
            toast.addClass(`bg-${type}`);
            content.text(message);

            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
        }
    </script>
</body>
</html>