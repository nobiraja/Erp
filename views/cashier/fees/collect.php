<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Fee Collection'); ?></title>
    <meta name="description" content="Collect fees from students - Cashier Interface">

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
        .student-card {
            cursor: pointer;
            transition: all 0.2s;
        }
        .student-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transform: translateY(-1px);
        }
        .student-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
        }
        .fee-details {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .payment-form {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
        }
        .amount-display {
            font-size: 1.5rem;
            font-weight: bold;
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
                    <a class="nav-link" href="/cashier/fees">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/cashier/fees/collect">
                        <i class="bi bi-cash"></i>
                        <span>Fee Collection</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/fees/outstanding">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Outstanding Fees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/fees/reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/expenses">
                        <i class="bi bi-receipt"></i>
                        <span>Expenses</span>
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
                        <div class="fw-bold small">Cashier</div>
                        <div class="text-muted small">Cashier</div>
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
                        <h5 class="mb-0">Fee Collection</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Collect</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/cashier/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Fee Collection Content -->
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
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_class" class="form-label">Class</label>
                        <select class="form-select" id="filter_class" onchange="loadStudents()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter_village" class="form-label">Village</label>
                        <select class="form-select" id="filter_village" onchange="loadStudents()">
                            <option value="">All Villages</option>
                            <?php foreach ($villages as $village): ?>
                                <option value="<?php echo htmlspecialchars($village['village']); ?>" <?php echo ($filters['village'] ?? '') == $village['village'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($village['village']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search_student" class="form-label">Search Student</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search_student" placeholder="Enter scholar number or student name">
                            <button class="btn btn-outline-primary" type="button" onclick="searchStudent()">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="show_outstanding_only" checked onchange="loadStudents()">
                            <label class="form-check-label" for="show_outstanding_only">
                                Outstanding only
                            </label>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="loadOutstandingFees()">
                            <i class="bi bi-list-ul me-1"></i>Load Outstanding
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Students List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Students (<?php echo count($students); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="studentsList">
                                <?php if (!empty($students)): ?>
                                    <div class="row g-3">
                                        <?php foreach ($students as $student): ?>
                                            <div class="col-md-6">
                                                <div class="card student-card border" onclick="selectStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?>')">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-person text-white"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></h6>
                                                                <p class="mb-1 small text-muted">
                                                                    Scholar No: <?php echo htmlspecialchars($student['scholar_number']); ?><br>
                                                                    Class: <?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?>
                                                                </p>
                                                                <?php if ($student['village_address']): ?>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($student['village_address']); ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-people text-muted fs-1 mb-3"></i>
                                        <h6 class="text-muted">No students found</h6>
                                        <p class="text-muted">Use the search or load outstanding fees</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="col-md-4">
                    <div class="card payment-form" id="paymentForm" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Collect Fee
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="feePaymentForm" action="/cashier/fees/process-payment" method="POST">
                                <div id="studentInfo" class="mb-3 p-3 bg-light rounded">
                                    <!-- Student info will be populated here -->
                                </div>

                                <div id="feeDetails" class="fee-details mb-3">
                                    <!-- Fee details will be populated here -->
                                </div>

                                <div class="mb-3">
                                    <label for="amount_paid" class="form-label">Total Amount to Pay *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control amount-display" id="amount_paid" name="amount_paid" step="0.01" min="0" required readonly>
                                    </div>
                                    <div class="form-text">Amount is automatically calculated from selected fees</div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_mode" class="form-label">Payment Mode *</label>
                                    <select class="form-select" id="payment_mode" name="payment_mode" required onchange="togglePaymentFields()">
                                        <option value="">Select Payment Mode</option>
                                        <option value="cash">Cash</option>
                                        <option value="online">Online Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="transactionField" style="display: none;">
                                    <label for="transaction_id" class="form-label">Transaction ID *</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                                </div>

                                <div class="mb-3" id="chequeField" style="display: none;">
                                    <label for="cheque_number" class="form-label">Cheque Number *</label>
                                    <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                                </div>

                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date *</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Optional remarks"></textarea>
                                </div>

                                <input type="hidden" id="selected_fees" name="selected_fees">

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>Collect Payment
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="cancelSelection()">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="card" id="instructionsCard">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Instructions
                            </h6>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0 small">
                                <li>Search for a student or load outstanding fees</li>
                                <li>Click on a student to select them</li>
                                <li>Select the fees to collect</li>
                                <li>Choose payment mode and submit</li>
                                <li>Print the receipt</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let selectedStudentId = null;
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
        });

        // Load students based on filters
        function loadStudents() {
            const classId = document.getElementById('filter_class').value;
            const village = document.getElementById('filter_village').value;
            const showOutstandingOnly = document.getElementById('show_outstanding_only').checked;

            if (!classId && showOutstandingOnly) {
                // Load outstanding fees
                loadOutstandingFees();
                return;
            }

            if (!classId) {
                // Clear students list
                updateStudentsList([]);
                return;
            }

            const params = new URLSearchParams({
                class_id: classId,
                village: village
            });

            fetch('/cashier/fees/ajax-get-students?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    updateStudentsList(data.students);
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    alert('Error loading students. Please try again.');
                });
        }

        // Search student
        function searchStudent() {
            const searchTerm = document.getElementById('search_student').value.trim();
            if (!searchTerm) {
                alert('Please enter a search term');
                return;
            }

            fetch('/cashier/fees/ajax-search-student?term=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(data => {
                    updateStudentsList(data.students);
                })
                .catch(error => {
                    console.error('Error searching students:', error);
                    alert('Error searching students. Please try again.');
                });
        }

        // Load outstanding fees
        function loadOutstandingFees() {
            fetch('/cashier/fees/ajax-get-outstanding?limit=50')
                .then(response => response.json())
                .then(data => {
                    updateStudentsList(data.students);
                })
                .catch(error => {
                    console.error('Error loading outstanding fees:', error);
                    alert('Error loading outstanding fees. Please try again.');
                });
        }

        // Update students list
        function updateStudentsList(students) {
            const studentsList = document.getElementById('studentsList');

            if (students.length === 0) {
                studentsList.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted fs-1 mb-3"></i>
                        <h6 class="text-muted">No students found</h6>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="row g-3">';
            students.forEach(student => {
                const fullName = `${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}`;
                html += `
                    <div class="col-md-6">
                        <div class="card student-card border" onclick="selectStudent(${student.id}, '${fullName.replace(/'/g, "\\'")}')">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${fullName}</h6>
                                        <p class="mb-1 small text-muted">
                                            Scholar No: ${student.scholar_number}<br>
                                            Class: ${student.class_name} ${student.section}
                                        </p>
                                        ${student.village_address ? `<small class="text-muted">${student.village_address}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            studentsList.innerHTML = html;
        }

        // Select student
        function selectStudent(studentId, studentName) {
            selectedStudentId = studentId;

            // Update UI
            document.querySelectorAll('.student-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');

            // Load student fees
            loadStudentFees(studentId, studentName);
        }

        // Load student fees
        function loadStudentFees(studentId, studentName) {
            fetch('/cashier/fees/ajax-get-student-fees?student_id=' + studentId)
                .then(response => response.json())
                .then(data => {
                    showPaymentForm(studentId, studentName, data.fees);
                })
                .catch(error => {
                    console.error('Error loading student fees:', error);
                    alert('Error loading student fees. Please try again.');
                });
        }

        // Show payment form
        function showPaymentForm(studentId, studentName, fees) {
            const paymentForm = document.getElementById('paymentForm');
            const instructionsCard = document.getElementById('instructionsCard');
            const studentInfo = document.getElementById('studentInfo');
            const feeDetails = document.getElementById('feeDetails');

            // Hide instructions, show form
            instructionsCard.style.display = 'none';
            paymentForm.style.display = 'block';

            // Populate student info
            studentInfo.innerHTML = `
                <h6 class="mb-2">${studentName}</h6>
                <p class="mb-0 small text-muted">Student ID: ${studentId}</p>
            `;

            // Populate fee details
            if (fees.length === 0) {
                feeDetails.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No outstanding fees found for this student.
                    </div>
                `;
                document.getElementById('feePaymentForm').style.display = 'none';
                return;
            }

            document.getElementById('feePaymentForm').style.display = 'block';

            let feeOptions = '<div class="mb-3"><label class="form-label">Select Fees to Collect *</label>';
            let totalAmount = 0;

            fees.forEach(fee => {
                if (!fee.is_paid) {
                    const statusBadge = fee.days_overdue > 0 ?
                        '<span class="badge bg-danger ms-1">Overdue</span>' :
                        '<span class="badge bg-warning ms-1">Pending</span>';

                    feeOptions += `
                        <div class="form-check">
                            <input class="form-check-input fee-checkbox" type="checkbox" value="${fee.id}" id="fee_${fee.id}" data-amount="${fee.amount}" onchange="updateTotalAmount()">
                            <label class="form-check-label" for="fee_${fee.id}">
                                <strong>${fee.fee_type}</strong> - ₹${parseFloat(fee.amount).toFixed(2)}
                                ${statusBadge}
                                <br><small class="text-muted">Due: ${new Date(fee.due_date).toLocaleDateString()}</small>
                            </label>
                        </div>
                    `;
                }
            });
            feeOptions += '</div>';

            feeDetails.innerHTML = feeOptions;

            // Reset total amount
            updateTotalAmount();
        }

        // Update total amount when fees are selected
        function updateTotalAmount() {
            const checkboxes = document.querySelectorAll('.fee-checkbox:checked');
            selectedFees = Array.from(checkboxes).map(cb => cb.value);

            let totalAmount = 0;
            checkboxes.forEach(cb => {
                totalAmount += parseFloat(cb.getAttribute('data-amount')) || 0;
            });

            document.getElementById('amount_paid').value = totalAmount.toFixed(2);
            document.getElementById('selected_fees').value = selectedFees.join(',');
        }

        // Toggle payment fields based on payment mode
        function togglePaymentFields() {
            const paymentMode = document.getElementById('payment_mode').value;
            const transactionField = document.getElementById('transactionField');
            const chequeField = document.getElementById('chequeField');
            const transactionInput = document.getElementById('transaction_id');
            const chequeInput = document.getElementById('cheque_number');

            // Reset required attributes
            transactionInput.required = false;
            chequeInput.required = false;

            // Hide all fields first
            transactionField.style.display = 'none';
            chequeField.style.display = 'none';

            // Show relevant field
            if (paymentMode === 'online' || paymentMode === 'upi') {
                transactionField.style.display = 'block';
                transactionInput.required = true;
            } else if (paymentMode === 'cheque') {
                chequeField.style.display = 'block';
                chequeInput.required = true;
            }
        }

        // Cancel selection
        function cancelSelection() {
            selectedStudentId = null;
            selectedFees = [];

            // Reset UI
            document.querySelectorAll('.student-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Hide form, show instructions
            document.getElementById('paymentForm').style.display = 'none';
            document.getElementById('instructionsCard').style.display = 'block';

            // Reset form
            document.getElementById('feePaymentForm').reset();
        }

        // Handle enter key in search
        document.getElementById('search_student').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchStudent();
            }
        });
    </script>
</body>
</html>