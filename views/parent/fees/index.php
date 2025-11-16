<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .child-fees-card {
            transition: transform 0.2s ease-in-out;
            margin-bottom: 2rem;
        }
        .child-fees-card:hover {
            transform: translateY(-2px);
        }
        .fee-summary-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
        }
        .pending-fees-alert {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
        }
        .payment-history-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .status-paid { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-overdue { color: #dc3545; }
        .fee-breakdown {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .progress-custom {
            height: 12px;
            border-radius: 6px;
        }
        .payment-method-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-house-heart-fill me-2"></i>
                Parent Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/children">
                            <i class="bi bi-people me-1"></i>My Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/parent/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/parent/events">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>Parent
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/parent/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Fee Payments</h2>
                        <p class="text-muted mb-0">Manage fee payments and track outstanding dues for your children</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="refreshAllFees()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Fee Cards -->
        <?php foreach ($children as $child): ?>
            <div class="card child-fees-card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($child['full_name']); ?>
                            </h5>
                            <small>
                                <?php echo htmlspecialchars($child['class_name'] . ' - ' . $child['class_section']); ?> |
                                Scholar No: <?php echo htmlspecialchars($child['scholar_number']); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-outline-dark btn-sm" onclick="viewFeeDetails(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </button>
                                <button class="btn btn-success btn-sm" onclick="makePayment(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-credit-card me-1"></i>Pay Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Fee Summary -->
                        <div class="col-lg-4 mb-3">
                            <div class="fee-summary-card">
                                <h6 class="mb-3">
                                    <i class="bi bi-cash-coin me-2"></i>Fee Summary
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="h4 mb-1">₹<?php echo number_format($child['fee_status']['total']); ?></div>
                                        <small>Total</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 mb-1">₹<?php echo number_format($child['fee_status']['paid']); ?></div>
                                        <small>Paid</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 mb-1">₹<?php echo number_format($child['fee_status']['pending']); ?></div>
                                        <small>Pending</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress progress-custom">
                                        <div class="progress-bar bg-white" role="progressbar"
                                             style="width: <?php echo $child['fee_status']['percentage']; ?>%"
                                             aria-valuenow="<?php echo $child['fee_status']['percentage']; ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $child['fee_status']['percentage']; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Dues Alert -->
                        <div class="col-lg-4 mb-3">
                            <?php if ($child['fee_status']['pending'] > 0): ?>
                                <div class="pending-fees-alert">
                                    <h6 class="mb-3">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Pending Dues
                                    </h6>
                                    <div class="h4 mb-2">₹<?php echo number_format($child['fee_status']['pending']); ?></div>
                                    <p class="mb-2">Outstanding fee amount that needs to be paid</p>
                                    <?php if ($child['fee_status']['overdue'] > 0): ?>
                                        <div class="alert alert-light text-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            ₹<?php echo number_format($child['fee_status']['overdue']); ?> is overdue
                                        </div>
                                    <?php endif; ?>
                                    <button class="btn btn-light btn-sm" onclick="makePayment(<?php echo $child['id']; ?>)">
                                        Pay Now
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="fee-summary-card">
                                    <h6 class="mb-3">
                                        <i class="bi bi-check-circle me-2"></i>All Clear
                                    </h6>
                                    <div class="text-center">
                                        <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                                        <p class="mt-2">All fees are paid up to date</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Recent Payments -->
                        <div class="col-lg-4 mb-3">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-clock-history me-2"></i>Recent Payments
                            </h6>
                            <div id="recent-payments-<?php echo $child['id']; ?>">
                                <?php if (empty($child['payment_history'])): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No payment history</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach (array_slice($child['payment_history'], 0, 3) as $payment): ?>
                                        <div class="payment-history-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>₹<?php echo number_format($payment['amount_paid']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?> -
                                                        <?php echo htmlspecialchars($payment['fee_type']); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-success">Paid</span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($payment['receipt_number']); ?></small>
                                                    <?php if (!empty($payment['id'])): ?>
                                                        <br>
                                                        <button class="btn btn-sm btn-outline-primary mt-1" onclick="viewReceipt(<?php echo $payment['id']; ?>)">
                                                            <i class="bi bi-receipt me-1"></i>View Receipt
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Fee Breakdown -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-table me-2"></i>Fee Breakdown
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Academic Year</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Remaining</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fees-table-<?php echo $child['id']; ?>">
                                        <!-- Fee details will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-warning btn-sm" onclick="loadDetailedFees(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>View All Fees
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="downloadFeeStatement(<?php echo $child['id']; ?>)">
                                    <i class="bi bi-download me-1"></i>Fee Statement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Fee Details Modal -->
        <div class="modal fade" id="feeDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detailed Fee Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="detailedFeesTable">
                                <thead>
                                    <tr>
                                        <th>Fee Type</th>
                                        <th>Academic Year</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Remaining</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Payment History</th>
                                    </tr>
                                </thead>
                                <tbody id="detailedFeesBody">
                                    <!-- Detailed fee information will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportFeeDetails()">Export</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Make Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="paymentForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="paymentAmount" class="form-label">Payment Amount (₹)</label>
                                    <input type="number" class="form-control" id="paymentAmount" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="paymentMethod" class="form-label">Payment Method</label>
                                    <select class="form-select" id="paymentMethod" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="online">Online Banking</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="upi">UPI</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transactionId" class="form-label">Transaction ID (if applicable)</label>
                                    <input type="text" class="form-control" id="transactionId">
                                </div>
                                <div class="col-md-6">
                                    <label for="paymentRemarks" class="form-label">Remarks</label>
                                    <input type="text" class="form-control" id="paymentRemarks">
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Payment will be processed and receipt will be generated upon successful transaction.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="processPayment()">Process Payment</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Modal -->
        <div class="modal fade" id="paymentHistoryModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="paymentHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Receipt</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentHistoryBody">
                                    <!-- Payment history will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AJAX Functions -->
    <script>
        let currentChildId = null;

        // Refresh all fees
        function refreshAllFees() {
            <?php foreach ($children as $child): ?>
                loadChildFees(<?php echo $child['id']; ?>);
            <?php endforeach; ?>
        }

        // Load child fees
        function loadChildFees(childId) {
            fetch(`/parent/dashboard/getChildFees?child_id=${childId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChildFees(childId, data.data);
                }
            })
            .catch(error => console.error('Error loading fees:', error));
        }

        // Update child fees display
        function updateChildFees(childId, fees) {
            const tbody = document.getElementById(`fees-table-${childId}`);
            if (fees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No fee records found</td></tr>';
                return;
            }

            let html = '';
            fees.slice(0, 5).forEach(fee => { // Show last 5 fees
                const remaining = fee.amount - (fee.paid_amount || 0);
                const statusClass = remaining > 0 ? (fee.is_overdue ? 'status-overdue' : 'status-pending') : 'status-paid';
                const statusText = remaining > 0 ? (fee.is_overdue ? 'Overdue' : 'Pending') : 'Paid';

                html += `
                    <tr>
                        <td>${fee.fee_type || ''}</td>
                        <td>${fee.academic_year || ''}</td>
                        <td>₹${fee.amount ? fee.amount.toLocaleString() : 0}</td>
                        <td>₹${fee.paid_amount ? fee.paid_amount.toLocaleString() : 0}</td>
                        <td>₹${remaining.toLocaleString()}</td>
                        <td>${fee.due_date ? new Date(fee.due_date).toLocaleDateString() : ''}</td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>
                            ${remaining > 0 ?
                                `<button class="btn btn-sm btn-success" onclick="makePayment(${childId}, ${fee.id})">Pay</button>` :
                                '<span class="text-success">✓</span>'
                            }
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // View fee details
        function viewFeeDetails(childId) {
            currentChildId = childId;
            const modal = new bootstrap.Modal(document.getElementById('feeDetailsModal'));

            fetch(`/parent/dashboard/getChildFees?child_id=${childId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDetailedFees(data.data);
                }
            })
            .catch(error => console.error('Error loading detailed fees:', error));

            modal.show();
        }

        // Display detailed fees
        function displayDetailedFees(fees) {
            const tbody = document.getElementById('detailedFeesBody');
            if (fees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No fee records found</td></tr>';
                return;
            }

            let html = '';
            fees.forEach(fee => {
                const remaining = fee.amount - (fee.paid_amount || 0);
                const statusClass = remaining > 0 ? (fee.is_overdue ? 'status-overdue' : 'status-pending') : 'status-paid';
                const statusText = remaining > 0 ? (fee.is_overdue ? 'Overdue' : 'Pending') : 'Paid';

                html += `
                    <tr>
                        <td>${fee.fee_type || ''}</td>
                        <td>${fee.academic_year || ''}</td>
                        <td>₹${fee.amount ? fee.amount.toLocaleString() : 0}</td>
                        <td>₹${fee.paid_amount ? fee.paid_amount.toLocaleString() : 0}</td>
                        <td>₹${remaining.toLocaleString()}</td>
                        <td>${fee.due_date ? new Date(fee.due_date).toLocaleDateString() : ''}</td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>
                            ${fee.payments && fee.payments.length > 0 ?
                                `<button class="btn btn-sm btn-info" onclick="viewPaymentHistory(${fee.id})">History</button>` :
                                'No payments'
                            }
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Make payment
        function makePayment(childId, feeId = null) {
            currentChildId = childId;
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));

            // Pre-fill amount if specific fee is selected
            if (feeId) {
                fetch(`/parent/dashboard/getChildFees?child_id=${childId}&fee_id=${feeId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        const fee = data.data[0];
                        const remaining = fee.amount - (fee.paid_amount || 0);
                        document.getElementById('paymentAmount').value = remaining;
                    }
                });
            }

            modal.show();
        }

        // Process payment
        function processPayment() {
            const amount = document.getElementById('paymentAmount').value;
            const method = document.getElementById('paymentMethod').value;
            const transactionId = document.getElementById('transactionId').value;
            const remarks = document.getElementById('paymentRemarks').value;

            if (!amount || !method) {
                alert('Please fill in all required fields');
                return;
            }

            // In a real implementation, this would integrate with a payment gateway
            alert('Payment processing would be implemented here with payment gateway integration');

            // Close modal and refresh data
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            refreshAllFees();
        }

        // Download fee statement
        function downloadFeeStatement(childId) {
            window.open(`/parent/fees/download?child_id=${childId}`, '_blank');
        }

        // Export fee details
        function exportFeeDetails() {
            window.open(`/parent/fees/export?child_id=${currentChildId}`, '_blank');
        }

        // View payment history for a specific fee
        function viewPaymentHistory(feeId) {
            const modal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
            const tbody = document.getElementById('paymentHistoryBody');

            // Show loading
            tbody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>';

            fetch(`/parent/dashboard/getChildFees?fee_id=${feeId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    displayPaymentHistory(data.data[0].payments || []);
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No payment history found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading payment history:', error);
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading payment history</td></tr>';
            });

            modal.show();
        }

        // Display payment history in modal
        function displayPaymentHistory(payments) {
            const tbody = document.getElementById('paymentHistoryBody');

            if (payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No payments found</td></tr>';
                return;
            }

            let html = '';
            payments.forEach(payment => {
                const statusClass = payment.is_paid ? 'success' : 'warning';
                const statusText = payment.is_paid ? 'Paid' : 'Pending';

                html += `
                    <tr>
                        <td>${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : ''}</td>
                        <td>${payment.fee_type || ''}</td>
                        <td>₹${payment.amount_paid ? payment.amount_paid.toLocaleString() : 0}</td>
                        <td>${payment.payment_method ? payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1) : ''}</td>
                        <td>${payment.receipt_number || 'N/A'}</td>
                        <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                        <td>
                            ${payment.is_paid && payment.id ?
                                `<button class="btn btn-sm btn-outline-primary" onclick="viewReceipt(${payment.id})">
                                    <i class="bi bi-receipt me-1"></i>View
                                </button>` :
                                '-'
                            }
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // View receipt for a payment
        function viewReceipt(paymentId) {
            window.open(`/parent/fees/receipt?payment_id=${paymentId}`, '_blank');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial fee data
            refreshAllFees();
        });
    </script>
</body>
</html>