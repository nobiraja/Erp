<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Outstanding Fees'); ?></title>
    <meta name="description" content="Manage outstanding fees and send payment reminders">

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
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .overdue-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .student-info {
            max-width: 200px;
        }
        .amount-cell {
            font-weight: bold;
            color: #dc3545;
        }
        .paid-amount {
            color: #28a745;
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
                    <a class="nav-link" href="/admin/exams">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/fees">
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
                        <h5 class="mb-0">Outstanding Fees</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/fees">Fees</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Outstanding</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="sendReminders()" class="btn btn-warning" id="sendRemindersBtn">
                        <i class="bi bi-envelope me-1"></i>Send Reminders
                    </button>
                    <a href="/admin/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Fees
                    </a>
                </div>
            </div>
        </header>

        <!-- Outstanding Fees Content -->
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

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="section" class="form-label">Section</label>
                        <select class="form-select" id="section" name="section">
                            <option value="">All Sections</option>
                            <option value="A" <?php echo ($filters['section'] ?? '') === 'A' ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo ($filters['section'] ?? '') === 'B' ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo ($filters['section'] ?? '') === 'C' ? 'selected' : ''; ?>>C</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="village" class="form-label">Village</label>
                        <select class="form-select" id="village" name="village">
                            <option value="">All Villages</option>
                            <?php foreach ($villages as $village): ?>
                                <option value="<?php echo htmlspecialchars($village['village']); ?>" <?php echo ($filters['village'] ?? '') === $village['village'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($village['village']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overdue_only" name="overdue_only" value="1" <?php echo ($filters['overdue_only'] ?? '') === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="overdue_only">
                                Overdue Only
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/fees/outstanding" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Outstanding Fees Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Outstanding Fees (<?php echo count($outstandingFees); ?>)
                    </h6>
                    <div class="d-flex gap-2">
                        <button onclick="selectAll()" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check-all me-1"></i>Select All
                        </button>
                        <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x me-1"></i>Clear
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($outstandingFees)): ?>
                        <div class="table-responsive">
                            <table id="outstandingTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllCheckboxes()">
                                        </th>
                                        <th>Student Details</th>
                                        <th>Class</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($outstandingFees as $fee): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="fee-checkbox" value="<?php echo $fee->id; ?>">
                                            </td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($fee->getStudentName()); ?></div>
                                                    <small class="text-muted">Scholar: <?php echo htmlspecialchars($fee->scholar_number); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($fee->class_name . ' ' . $fee->section); ?></td>
                                            <td><?php echo htmlspecialchars($fee->fee_type); ?></td>
                                            <td class="amount-cell">₹<?php echo number_format($fee->amount, 2); ?></td>
                                            <td>
                                                <?php echo date('d-m-Y', strtotime($fee->due_date)); ?>
                                                <?php if ($fee->isOverdue()): ?>
                                                    <br><small class="text-danger"><?php echo $fee->days_overdue; ?> days overdue</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee->isOverdue()): ?>
                                                    <span class="badge bg-danger overdue-badge">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee->mobile): ?>
                                                    <i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($fee->mobile); ?><br>
                                                <?php endif; ?>
                                                <?php if ($fee->village_address): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars($fee->village_address); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/fees/collect?student_id=<?php echo $fee->student_id; ?>" class="btn btn-sm btn-outline-success" title="Collect Payment">
                                                        <i class="bi bi-cash"></i>
                                                    </a>
                                                    <button onclick="sendIndividualReminder(<?php echo $fee->id; ?>, '<?php echo htmlspecialchars($fee->getStudentName()); ?>')" class="btn btn-sm btn-outline-primary" title="Send Reminder">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span id="selectedCount" class="text-muted">0 students selected</span>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button onclick="sendBulkReminders()" class="btn btn-warning" id="bulkRemindersBtn" disabled>
                                        <i class="bi bi-envelope me-1"></i>Send Bulk Reminders
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3"></i>
                            <h6 class="text-muted">No outstanding fees found</h6>
                            <p class="text-muted">All fees are paid up to date!</p>
                            <a href="/admin/fees" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Fees Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let selectedFees = [];

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

            // Initialize DataTable
            $('#outstandingTable').DataTable({
                "pageLength": 25,
                "ordering": true,
                "searching": true,
                "paging": true,
                "info": true,
                "responsive": true,
                "order": [[5, 'asc']] // Sort by due date
            });

            // Handle individual checkbox changes
            document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
        });

        // Toggle all checkboxes
        function toggleAllCheckboxes() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.fee-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateSelectedCount();
        }

        // Select all visible checkboxes
        function selectAll() {
            document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            updateSelectedCount();
        }

        // Clear all selections
        function clearSelection() {
            document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            updateSelectedCount();
        }

        // Update selected count display
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.fee-checkbox:checked');
            selectedFees = Array.from(checkboxes).map(cb => cb.value);

            const selectedCount = document.getElementById('selectedCount');
            const bulkRemindersBtn = document.getElementById('bulkRemindersBtn');

            selectedCount.textContent = selectedFees.length + ' students selected';

            if (selectedFees.length > 0) {
                bulkRemindersBtn.disabled = false;
                bulkRemindersBtn.textContent = `Send Bulk Reminders (${selectedFees.length})`;
            } else {
                bulkRemindersBtn.disabled = true;
                bulkRemindersBtn.textContent = 'Send Bulk Reminders';
            }
        }

        // Send bulk reminders
        function sendBulkReminders() {
            if (selectedFees.length === 0) {
                alert('Please select students to send reminders to.');
                return;
            }

            if (confirm(`Send payment reminders to ${selectedFees.length} students?`)) {
                const formData = new FormData();
                selectedFees.forEach(feeId => {
                    formData.append('fee_ids[]', feeId);
                });

                fetch('/admin/fees/send-reminders', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminders sent successfully!');
                        clearSelection();
                    } else {
                        alert('Failed to send reminders: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error sending reminders. Please try again.');
                    console.error('Send reminders error:', error);
                });
            }
        }

        // Send individual reminder
        function sendIndividualReminder(feeId, studentName) {
            if (confirm(`Send payment reminder to ${studentName}?`)) {
                const formData = new FormData();
                formData.append('fee_ids[]', feeId);

                fetch('/admin/fees/send-reminders', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminder sent successfully!');
                    } else {
                        alert('Failed to send reminder: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error sending reminder. Please try again.');
                    console.error('Send reminder error:', error);
                });
            }
        }

        // Send reminders to all filtered results
        function sendReminders() {
            if (confirm('Send payment reminders to all students in the current filter?')) {
                document.getElementById('sendRemindersBtn').innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Sending...';
                document.getElementById('sendRemindersBtn').disabled = true;

                // Get current filter parameters
                const urlParams = new URLSearchParams(window.location.search);
                const formData = new FormData();

                // Add all visible fee IDs (this is a simplified version - in production you'd get all filtered IDs)
                document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
                    formData.append('fee_ids[]', checkbox.value);
                });

                fetch('/admin/fees/send-reminders', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminders sent successfully!');
                    } else {
                        alert('Failed to send reminders: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error sending reminders. Please try again.');
                    console.error('Send reminders error:', error);
                })
                .finally(() => {
                    document.getElementById('sendRemindersBtn').innerHTML = '<i class="bi bi-envelope me-1"></i>Send Reminders';
                    document.getElementById('sendRemindersBtn').disabled = false;
                });
            }
        }
    </script>
</body>
</html>