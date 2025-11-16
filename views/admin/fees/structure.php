<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Fee Structure Management'); ?></title>
    <meta name="description" content="Create and manage fee structures for different classes">

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
        .fee-row {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .student-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
        }
        .student-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
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
                        <h5 class="mb-0">Fee Structure Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/fees">Fees</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Structure</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="showCreateModal()" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Create Structure
                    </button>
                    <a href="/admin/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Fees
                    </a>
                </div>
            </div>
        </header>

        <!-- Fee Structure Content -->
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <select class="form-select" id="academic_year" name="academic_year">
                            <option value="">All Years</option>
                            <?php
                            $currentYear = date('Y');
                            for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++):
                                $yearRange = $i . '-' . ($i + 1);
                            ?>
                                <option value="<?php echo $yearRange; ?>" <?php echo ($filters['academic_year'] ?? '') === $yearRange ? 'selected' : ''; ?>>
                                    <?php echo $yearRange; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="/admin/fees/structure" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Fee Structure Display -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Fee Structures
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($feeStructure)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feeStructure as $fee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fee->getStudentName()); ?></td>
                                            <td><?php echo htmlspecialchars($fee->class_name . ' ' . $fee->section); ?></td>
                                            <td><?php echo htmlspecialchars($fee->fee_type); ?></td>
                                            <td>₹<?php echo number_format($fee->amount, 2); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($fee->due_date)); ?></td>
                                            <td>
                                                <?php if ($fee->is_paid): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button onclick="editFee(<?php echo $fee->id; ?>)" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button onclick="deleteFee(<?php echo $fee->id; ?>, '<?php echo htmlspecialchars($fee->fee_type); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-gear text-muted fs-1 mb-3"></i>
                            <h6 class="text-muted">No fee structures found</h6>
                            <p class="text-muted">Create a fee structure for a class to get started</p>
                            <button onclick="showCreateModal()" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create Fee Structure
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Fee Structure Modal -->
    <div class="modal fade" id="createStructureModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Fee Structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="feeStructureForm" action="/admin/fees/create-structure" method="POST">
                    <div class="modal-body">
                        <!-- Step 1: Select Class and Year -->
                        <div id="step1" class="step-content">
                            <h6 class="mb-3">Step 1: Select Class and Academic Year</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="modal_class_id" class="form-label">Class *</label>
                                    <select class="form-select" id="modal_class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>">
                                                <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="modal_academic_year" class="form-label">Academic Year *</label>
                                    <select class="form-select" id="modal_academic_year" name="academic_year" required>
                                        <?php
                                        $currentYear = date('Y');
                                        for ($i = $currentYear - 1; $i <= $currentYear + 1; $i++):
                                            $yearRange = $i . '-' . ($i + 1);
                                        ?>
                                            <option value="<?php echo $yearRange; ?>" <?php echo $yearRange === date('Y') . '-' . (date('Y') + 1) ? 'selected' : ''; ?>>
                                                <?php echo $yearRange; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" onclick="loadStudentsForStructure()">Load Students</button>
                            </div>
                        </div>

                        <!-- Step 2: Configure Fees -->
                        <div id="step2" class="step-content" style="display: none;">
                            <h6 class="mb-3">Step 2: Configure Fee Structure</h6>
                            <div id="studentsList" class="mb-3">
                                <!-- Students will be loaded here -->
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="backToStep1()">Back</button>
                                <button type="submit" class="btn btn-success">Create Fee Structure</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

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
        });

        // Show create modal
        function showCreateModal() {
            const modal = new bootstrap.Modal(document.getElementById('createStructureModal'));
            modal.show();
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
        }

        // Load students for structure creation
        function loadStudentsForStructure() {
            const classId = document.getElementById('modal_class_id').value;
            const academicYear = document.getElementById('modal_academic_year').value;

            if (!classId || !academicYear) {
                alert('Please select both class and academic year');
                return;
            }

            fetch(`/admin/fees/ajax-get-students?class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    displayStudentsForStructure(data.students, academicYear);
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    alert('Error loading students. Please try again.');
                });
        }

        // Display students for fee structure creation
        function displayStudentsForStructure(students, academicYear) {
            const studentsList = document.getElementById('studentsList');

            if (students.length === 0) {
                studentsList.innerHTML = '<div class="alert alert-warning">No students found in this class.</div>';
                return;
            }

            let html = '<div class="row g-3">';

            students.forEach(student => {
                html += `
                    <div class="col-md-6">
                        <div class="student-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}</h6>
                                    <small class="text-muted">Scholar No: ${student.scholar_number}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFeeRow(${student.id}, '${student.first_name} ${student.last_name}')">
                                    <i class="bi bi-plus-circle"></i> Add Fee
                                </button>
                            </div>
                            <div id="feeRows_${student.id}">
                                <!-- Fee rows will be added here -->
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            studentsList.innerHTML = html;
        }

        // Add fee row for student
        function addFeeRow(studentId, studentName) {
            const feeRowsContainer = document.getElementById(`feeRows_${studentId}`);
            const rowCount = feeRowsContainer.children.length + 1;

            const feeRow = document.createElement('div');
            feeRow.className = 'fee-row';
            feeRow.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Fee Type</label>
                        <select class="form-select" name="fee_data[${studentId}][${rowCount}][fee_type]" required>
                            <option value="">Select Type</option>
                            <option value="Tuition Fee">Tuition Fee</option>
                            <option value="Admission Fee">Admission Fee</option>
                            <option value="Exam Fee">Exam Fee</option>
                            <option value="Library Fee">Library Fee</option>
                            <option value="Sports Fee">Sports Fee</option>
                            <option value="Transport Fee">Transport Fee</option>
                            <option value="Development Fee">Development Fee</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="fee_data[${studentId}][${rowCount}][amount]" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="fee_data[${studentId}][${rowCount}][due_date]" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="fee_data[${studentId}][${rowCount}][description]" placeholder="Optional">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFeeRow(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            feeRowsContainer.appendChild(feeRow);
        }

        // Remove fee row
        function removeFeeRow(button) {
            button.closest('.fee-row').remove();
        }

        // Back to step 1
        function backToStep1() {
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
        }

        // Edit fee
        function editFee(feeId) {
            // This would open an edit modal - simplified for now
            alert('Edit functionality would be implemented here');
        }

        // Delete fee
        function deleteFee(feeId, feeType) {
            if (confirm(`Are you sure you want to delete the "${feeType}" fee? This action cannot be undone.`)) {
                fetch(`/admin/fees/delete-fee/${feeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Fee deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete fee: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting fee');
                    console.error('Delete error:', error);
                });
            }
        }
    </script>
</body>
</html>