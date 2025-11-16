<?php
/**
 * Teacher Profile Index View
 * Displays comprehensive teacher profile information
 */

// Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#personal" data-bs-toggle="tab">
                            <i class="fas fa-user"></i> Personal Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#assignments" data-bs-toggle="tab">
                            <i class="fas fa-book"></i> Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#timetable" data-bs-toggle="tab">
                            <i class="fas fa-calendar"></i> Timetable
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#attendance" data-bs-toggle="tab">
                            <i class="fas fa-clipboard-check"></i> Attendance Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#performance" data-bs-toggle="tab">
                            <i class="fas fa-chart-line"></i> Performance Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#settings" data-bs-toggle="tab">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user-circle text-primary"></i> My Profile
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProfile()">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changePassword()">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Photo and Basic Info -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?= $teacher->photo_path ? '/uploads/' . $teacher->photo_path : '/assets/images/default-avatar.png' ?>"
                                 alt="Profile Photo" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <h5 class="card-title mb-1"><?= htmlspecialchars($teacher->getFullName()) ?></h5>
                            <p class="text-muted mb-2"><?= htmlspecialchars($teacher->designation ?? 'Teacher') ?></p>
                            <span class="badge bg-primary"><?= htmlspecialchars($teacher->employee_id) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle text-info"></i> Quick Stats
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h4 class="text-primary mb-0" id="totalSubjects">--</h4>
                                                <small class="text-muted">Subjects</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h4 class="text-success mb-0" id="totalClasses">--</h4>
                                                <small class="text-muted">Classes</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-warning mb-0" id="workload">--</h4>
                                            <small class="text-muted">Workload</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-calendar-check text-success"></i> This Month
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-info mb-0" id="attendanceMarked">--</h4>
                                                <small class="text-muted">Attendance Marked</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-primary mb-0" id="resultsEntered">--</h4>
                                            <small class="text-muted">Results Entered</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Personal Details Tab -->
                <div class="tab-pane fade show active" id="personal">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user"></i> Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Employee ID:</td>
                                            <td><?= htmlspecialchars($teacher->employee_id) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Full Name:</td>
                                            <td><?= htmlspecialchars($teacher->getFullName()) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Date of Birth:</td>
                                            <td><?= $teacher->dob ? date('d M Y', strtotime($teacher->dob)) : 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Gender:</td>
                                            <td><?= ucfirst($teacher->gender ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Marital Status:</td>
                                            <td><?= ucfirst($teacher->marital_status ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Blood Group:</td>
                                            <td><?= $teacher->blood_group ?? 'Not specified' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Qualification:</td>
                                            <td><?= htmlspecialchars($teacher->qualification ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Specialization:</td>
                                            <td><?= htmlspecialchars($teacher->specialization ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Designation:</td>
                                            <td><?= htmlspecialchars($teacher->designation ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Department:</td>
                                            <td><?= htmlspecialchars($teacher->department ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Date of Joining:</td>
                                            <td><?= $teacher->date_of_joining ? date('d M Y', strtotime($teacher->date_of_joining)) : 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Experience:</td>
                                            <td><?= $teacher->experience_years ? $teacher->experience_years . ' years' : 'Not specified' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Contact Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Mobile:</td>
                                            <td><?= htmlspecialchars($teacher->mobile ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td><?= htmlspecialchars($teacher->email ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Permanent Address:</td>
                                            <td><?= htmlspecialchars($teacher->permanent_address ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Temporary Address:</td>
                                            <td><?= htmlspecialchars($teacher->temporary_address ?? 'Not specified') ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Identification</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Aadhar Number:</td>
                                            <td><?= htmlspecialchars($teacher->aadhar ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">PAN Number:</td>
                                            <td><?= htmlspecialchars($teacher->pan ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Samagra ID:</td>
                                            <td><?= htmlspecialchars($teacher->samagra_id ?? 'Not specified') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Medical Conditions:</td>
                                            <td><?= htmlspecialchars($teacher->medical_conditions ?? 'None') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div class="tab-pane fade" id="assignments">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-book"></i> Assigned Classes & Subjects
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="assignmentsTable">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th>Subject Code</th>
                                            <th>Students</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timetable Tab -->
                <div class="tab-pane fade" id="timetable">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar"></i> Weekly Timetable
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="timetableTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time</th>
                                            <th>Monday</th>
                                            <th>Tuesday</th>
                                            <th>Wednesday</th>
                                            <th>Thursday</th>
                                            <th>Friday</th>
                                            <th>Saturday</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Timetable data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records Tab -->
                <div class="tab-pane fade" id="attendance">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-check"></i> Attendance Records
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="attendanceMonth">
                                        <option value="">Select Month</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="attendanceYear">
                                        <option value="">Select Year</option>
                                        <?php for($i = date('Y') - 2; $i <= date('Y'); $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary" onclick="loadAttendanceRecords()">
                                        <i class="fas fa-search"></i> Load Records
                                    </button>
                                </div>
                            </div>
                            <div id="attendanceRecords">
                                <!-- Attendance records will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Analytics Tab -->
                <div class="tab-pane fade" id="performance">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line"></i> Performance Analytics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Monthly Attendance Marked</h6>
                                            <canvas id="attendanceChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Results Entry Summary</h6>
                                            <canvas id="resultsChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Class Performance Overview</h6>
                                            <div class="table-responsive">
                                                <table class="table table-striped" id="performanceTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Class</th>
                                                            <th>Subject</th>
                                                            <th>Avg Attendance</th>
                                                            <th>Avg Performance</th>
                                                            <th>Students</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Performance data will be loaded via AJAX -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cog"></i> Account Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="editProfile()">
                                            <i class="fas fa-edit"></i> Edit Profile Information
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="changePassword()">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="uploadPhoto()">
                                            <i class="fas fa-camera"></i> Update Profile Photo
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Account Information</h6>
                                        <p class="mb-1"><strong>Last Login:</strong> <span id="lastLogin">--</span></p>
                                        <p class="mb-1"><strong>Account Status:</strong> <span class="badge bg-success">Active</span></p>
                                        <p class="mb-0"><strong>Profile Completion:</strong> <span id="profileCompletion">--</span>%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProfileForm">
                <div class="modal-body">
                    <!-- Form content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize when document is ready
$(document).ready(function() {
    loadProfileStats();
    loadAssignments();
    loadTimetable();
    loadPerformanceData();

    // Handle tab changes
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#attendance') {
            loadAttendanceRecords();
        }
    });
});

// Load profile statistics
function loadProfileStats() {
    $.ajax({
        url: '/teacher/profile/get-stats',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#totalSubjects').text(response.data.total_subjects);
                $('#totalClasses').text(response.data.total_classes);
                $('#workload').text(response.data.workload);
                $('#attendanceMarked').text(response.data.attendance_marked);
                $('#resultsEntered').text(response.data.results_entered);
                $('#lastLogin').text(response.data.last_login);
                $('#profileCompletion').text(response.data.profile_completion);
            }
        }
    });
}

// Load assignments data
function loadAssignments() {
    $.ajax({
        url: '/teacher/profile/get-assignments',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(function(assignment) {
                    html += `
                        <tr>
                            <td>${assignment.class_name} ${assignment.section}</td>
                            <td>${assignment.subject_name}</td>
                            <td>${assignment.subject_code}</td>
                            <td>${assignment.student_count}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewClassDetails(${assignment.class_id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#assignmentsTable tbody').html(html);
            }
        }
    });
}

// Load timetable data
function loadTimetable() {
    $.ajax({
        url: '/teacher/profile/get-timetable',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Build timetable HTML
                const timeSlots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '14:00-15:00', '15:00-16:00'];
                let html = '';

                timeSlots.forEach(function(slot, index) {
                    html += `<tr><td class="fw-bold">${slot}</td>`;
                    for (let day = 1; day <= 6; day++) {
                        const period = response.data.timetable[day] ? response.data.timetable[day][index] : null;
                        if (period) {
                            html += `<td class="bg-light">${period.subject_name}<br><small class="text-muted">${period.class_name} ${period.section}</small></td>`;
                        } else {
                            html += `<td class="text-muted">--</td>`;
                        }
                    }
                    html += '</tr>';
                });

                $('#timetableTable tbody').html(html);
            }
        }
    });
}

// Load attendance records
function loadAttendanceRecords() {
    const month = $('#attendanceMonth').val();
    const year = $('#attendanceYear').val();

    if (!month || !year) {
        $('#attendanceRecords').html('<div class="alert alert-warning">Please select month and year</div>');
        return;
    }

    $.ajax({
        url: '/teacher/profile/get-attendance-records',
        method: 'GET',
        data: { month: month, year: year },
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-striped">';
                html += '<thead><tr><th>Date</th><th>Class</th><th>Subject</th><th>Present</th><th>Absent</th><th>Late</th><th>Total</th></tr></thead><tbody>';

                response.data.forEach(function(record) {
                    html += `
                        <tr>
                            <td>${record.date}</td>
                            <td>${record.class_name} ${record.section}</td>
                            <td>${record.subject_name}</td>
                            <td><span class="badge bg-success">${record.present}</span></td>
                            <td><span class="badge bg-danger">${record.absent}</span></td>
                            <td><span class="badge bg-warning">${record.late}</span></td>
                            <td>${record.total}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                $('#attendanceRecords').html(html);
            }
        }
    });
}

// Load performance data and charts
function loadPerformanceData() {
    $.ajax({
        url: '/teacher/profile/get-performance-data',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Load performance table
                let html = '';
                response.data.performance.forEach(function(item) {
                    html += `
                        <tr>
                            <td>${item.class_name} ${item.section}</td>
                            <td>${item.subject_name}</td>
                            <td><span class="badge bg-${getAttendanceBadge(item.attendance_rate)}">${item.attendance_rate}%</span></td>
                            <td><span class="badge bg-${getPerformanceBadge(item.avg_performance)}">${item.avg_performance}%</span></td>
                            <td>${item.student_count}</td>
                        </tr>
                    `;
                });
                $('#performanceTable tbody').html(html);

                // Initialize charts
                initCharts(response.data.charts);
            }
        }
    });
}

// Initialize charts
function initCharts(chartData) {
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: chartData.attendance,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Results Chart
    const resultsCtx = document.getElementById('resultsChart').getContext('2d');
    new Chart(resultsCtx, {
        type: 'bar',
        data: chartData.results,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Helper functions for badges
function getAttendanceBadge(rate) {
    if (rate >= 85) return 'success';
    if (rate >= 70) return 'warning';
    return 'danger';
}

function getPerformanceBadge(rate) {
    if (rate >= 85) return 'success';
    if (rate >= 70) return 'primary';
    return 'warning';
}

// Edit profile
function editProfile() {
    $.ajax({
        url: '/teacher/profile/edit',
        method: 'GET',
        success: function(response) {
            $('#editProfileModal .modal-body').html(response);
            $('#editProfileModal').modal('show');
        }
    });
}

// Change password
function changePassword() {
    $('#changePasswordModal').modal('show');
}

// Handle password change form
$('#changePasswordForm').on('submit', function(e) {
    e.preventDefault();

    const currentPassword = $('#currentPassword').val();
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();

    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return;
    }

    $.ajax({
        url: '/teacher/profile/change-password',
        method: 'POST',
        data: {
            current_password: currentPassword,
            new_password: newPassword
        },
        success: function(response) {
            if (response.success) {
                $('#changePasswordModal').modal('hide');
                alert('Password changed successfully');
                $('#changePasswordForm')[0].reset();
            } else {
                alert(response.message || 'Failed to change password');
            }
        }
    });
});

// View class details
function viewClassDetails(classId) {
    window.location.href = `/teacher/classes/students?class_id=${classId}`;
}

// Upload photo
function uploadPhoto() {
    // Implement photo upload functionality
    alert('Photo upload functionality will be implemented');
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>