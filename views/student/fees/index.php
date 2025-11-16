<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - School Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .fee-card {
            transition: transform 0.2s ease-in-out;
        }
        .fee-card:hover {
            transform: translateY(-2px);
        }
        .overdue-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .payment-status-paid { color: #198754; }
        .payment-status-partial { color: #ffc107; }
        .payment-status-pending { color: #dc3545; }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Student Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/student/dashboard">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/attendance">
                            <i class="bi bi-calendar-check me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/results">
                            <i class="bi bi-clipboard-data me-1"></i>Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/student/fees">
                            <i class="bi bi-cash-coin me-1"></i>Fees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student/profile">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($student_data['first_name'] ?? 'Student'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/student/profile"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/student/dashboard">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/student/attendance">
                                <i class="bi bi-calendar-check"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/student/results">
                                <i class="bi bi-clipboard-data"></i> Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/student/fees">
                                <i class="bi bi-cash-coin"></i> Fees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/student/profile">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-cash-coin text-primary"></i> My Fees
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshFeeData()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fee Status Overview -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card fee-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-pie-chart"></i> Fee Status Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100 border-success">
                                            <div class="card-body">
                                                <div class="h4 text-success mb-1">₹<?= number_format($fee_status['paid'], 2) ?></div>
                                                <small class="text-muted">Total Paid</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body">
                                                <div class="h4 text-warning mb-1">₹<?= number_format($fee_status['pending'], 2) ?></div>
                                                <small class="text-muted">Pending</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body">
                                                <div class="h4 text-danger mb-1">₹<?= number_format($fee_status['overdue'], 2) ?></div>
                                                <small class="text-muted">Overdue</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100 border-info">
                                            <div class="card-body">
                                                <div class="h4 text-info mb-1"><?= $fee_status['percentage'] ?>%</div>
                                                <small class="text-muted">Payment Progress</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: <?= $fee_status['percentage'] ?>%"
                                             aria-valuenow="<?= $fee_status['percentage'] ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= $fee_status['percentage'] ?>% Paid
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Dues Alert -->
                <?php if (!empty($pending_dues)): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i> Pending Fee Payments
                            </h6>
                            <p class="mb-2">You have <?= count($pending_dues) ?> pending fee payment(s). Please make payment before the due dates to avoid penalties.</p>
                            <div class="row">
                                <?php foreach (array_slice($pending_dues, 0, 3) as $due): ?>
                                <div class="col-md-4">
                                    <div class="card border-<?= $due['is_overdue'] ? 'danger' : 'warning' ?> mb-2">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="text-<?= $due['is_overdue'] ? 'danger' : 'warning' ?>">
                                                        <?= htmlspecialchars($due['fee']->fee_type) ?>
                                                    </strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Due: <?= date('M d, Y', strtotime($due['fee']->due_date)) ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h6 text-<?= $due['is_overdue'] ? 'danger' : 'warning' ?> mb-0">
                                                        ₹<?= number_format($due['remaining_amount'], 2) ?>
                                                    </div>
                                                    <?php if ($due['is_overdue']): ?>
                                                        <small class="text-danger overdue-badge">
                                                            <?= $due['days_overdue'] ?> days overdue
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($pending_dues) > 3): ?>
                            <small class="text-muted">And <?= count($pending_dues) - 3 ?> more pending payments...</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Main Content Tabs -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs" id="feeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                                                data-bs-target="#overview" type="button" role="tab">
                                            <i class="bi bi-eye"></i> Overview
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="history-tab" data-bs-toggle="tab"
                                                data-bs-target="#history" type="button" role="tab">
                                            <i class="bi bi-clock-history"></i> Payment History
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pending-tab" data-bs-toggle="tab"
                                                data-bs-target="#pending" type="button" role="tab">
                                            <i class="bi bi-hourglass-split"></i> Pending Dues
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pay-tab" data-bs-toggle="tab"
                                                data-bs-target="#pay" type="button" role="tab">
                                            <i class="bi bi-credit-card"></i> Pay Online
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="feeTabsContent">
                                    <!-- Overview Tab -->
                                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="feesTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Fee Type</th>
                                                        <th>Academic Year</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid Amount</th>
                                                        <th>Balance</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($all_fees as $fee): ?>
                                                    <?php
                                                        $totalPaid = \FeePaymentModel::getTotalPaidForFee($fee->id);
                                                        $balance = $fee->amount - $totalPaid;
                                                        $isOverdue = $fee->isOverdue() && $balance > 0;
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars($fee->fee_type) ?></strong>
                                                            <?php if ($fee->description): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars($fee->description) ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($fee->academic_year) ?></td>
                                                        <td class="text-primary fw-bold">₹<?= number_format($fee->amount, 2) ?></td>
                                                        <td class="text-success">₹<?= number_format($totalPaid, 2) ?></td>
                                                        <td class="<?= $balance > 0 ? 'text-warning fw-bold' : 'text-success' ?>">
                                                            ₹<?= number_format($balance, 2) ?>
                                                        </td>
                                                        <td>
                                                            <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                                                <?= date('M d, Y', strtotime($fee->due_date)) ?>
                                                            </span>
                                                            <?php if ($isOverdue): ?>
                                                                <br><small class="text-danger">
                                                                    <i class="bi bi-exclamation-triangle"></i>
                                                                    <?= $fee->getDaysOverdue() ?> days overdue
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge fs-6 <?= $fee->is_paid ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-warning') ?>">
                                                                <?= $fee->getStatusText() ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="viewFeeDetails(<?= $fee->id ?>)">
                                                                <i class="bi bi-eye"></i> Details
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Payment History Tab -->
                                    <div class="tab-pane fade" id="history" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="paymentHistoryTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Receipt No</th>
                                                        <th>Fee Type</th>
                                                        <th>Payment Date</th>
                                                        <th>Amount Paid</th>
                                                        <th>Payment Mode</th>
                                                        <th>Collected By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($payment_history)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                                                            <p class="mt-2 text-muted">No payment history found.</p>
                                                        </td>
                                                    </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($payment_history as $payment): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-info fs-6">
                                                                    <?= htmlspecialchars($payment->receipt_number) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong><?= htmlspecialchars($payment->fee_type) ?></strong>
                                                            </td>
                                                            <td>
                                                                <i class="bi bi-calendar me-1"></i>
                                                                <?= date('M d, Y', strtotime($payment->payment_date)) ?>
                                                            </td>
                                                            <td class="text-success fw-bold">₹<?= number_format($payment->amount_paid, 2) ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    <i class="bi bi-<?= $payment->payment_mode == 'online' ? 'globe' : ($payment->payment_mode == 'cash' ? 'cash' : 'credit-card') ?> me-1"></i>
                                                                    <?= htmlspecialchars($payment->getPaymentModeText()) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <i class="bi bi-person me-1"></i>
                                                                <?= htmlspecialchars($payment->collected_by_name) ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <button class="btn btn-sm btn-outline-info" onclick="viewReceipt(<?= $payment->id ?>)">
                                                                        <i class="bi bi-receipt"></i> View
                                                                    </button>
                                                                    <a href="/student/fees/downloadReceipt?payment_id=<?= $payment->id ?>"
                                                                       class="btn btn-sm btn-outline-success" target="_blank">
                                                                        <i class="bi bi-download"></i> PDF
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Pending Dues Tab -->
                                    <div class="tab-pane fade" id="pending" role="tabpanel">
                                        <?php if (empty($pending_dues)): ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                            <h4 class="mt-3 text-success">All Caught Up!</h4>
                                            <p class="text-muted">You have no pending fee payments at this time.</p>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Note:</strong> Please make payments before the due dates to avoid late fees and penalties.
                                        </div>
                                        <div class="row">
                                            <?php foreach ($pending_dues as $due): ?>
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card fee-card border-<?= $due['is_overdue'] ? 'danger' : 'warning' ?> h-100">
                                                    <div class="card-header bg-<?= $due['is_overdue'] ? 'danger' : 'warning' ?> text-white">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="card-title mb-0">
                                                                <i class="bi bi-cash-coin me-2"></i>
                                                                <?= htmlspecialchars($due['fee']->fee_type) ?>
                                                            </h6>
                                                            <?php if ($due['is_overdue']): ?>
                                                                <span class="badge bg-light text-danger">Overdue</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-warning">Pending</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row text-center mb-3">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Amount Due</small>
                                                                <div class="h5 text-<?= $due['is_overdue'] ? 'danger' : 'warning' ?> mb-0 fw-bold">
                                                                    ₹<?= number_format($due['remaining_amount'], 2) ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Due Date</small>
                                                                <div class="text-<?= $due['is_overdue'] ? 'danger fw-bold' : 'dark' ?>">
                                                                    <i class="bi bi-calendar me-1"></i>
                                                                    <?= date('M d, Y', strtotime($due['fee']->due_date)) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php if ($due['is_overdue']): ?>
                                                        <div class="alert alert-danger py-2">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            <strong><?= $due['days_overdue'] ?> days overdue</strong>
                                                        </div>
                                                        <?php endif; ?>
                                                        <div class="d-grid">
                                                            <button class="btn btn-outline-primary" onclick="payFee(<?= $due['fee']->id ?>)">
                                                                <i class="bi bi-credit-card me-2"></i>Pay Now
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Pay Online Tab -->
                                    <div class="tab-pane fade" id="pay" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="card">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="card-title mb-0">
                                                            <i class="bi bi-credit-card"></i> Online Payment Options
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="alert alert-info">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            <strong>Coming Soon:</strong> Online payment feature will be available soon.
                                                            Please contact the school administration for payment instructions in the meantime.
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card border-primary h-100">
                                                                    <div class="card-body text-center">
                                                                        <i class="bi bi-phone text-primary" style="font-size: 2rem;"></i>
                                                                        <h6 class="mt-2">UPI Payment</h6>
                                                                        <small class="text-muted">Pay using UPI apps like Google Pay, PhonePe, Paytm</small>
                                                                        <br>
                                                                        <button class="btn btn-outline-primary mt-3" disabled>
                                                                            <i class="bi bi-lock"></i> Coming Soon
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card border-success h-100">
                                                                    <div class="card-body text-center">
                                                                        <i class="bi bi-credit-card text-success" style="font-size: 2rem;"></i>
                                                                        <h6 class="mt-2">Card Payment</h6>
                                                                        <small class="text-muted">Debit/Credit cards, Net Banking</small>
                                                                        <br>
                                                                        <button class="btn btn-outline-success mt-3" disabled>
                                                                            <i class="bi bi-lock"></i> Coming Soon
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="alert alert-warning mt-3">
                                                            <h6><i class="bi bi-exclamation-triangle me-2"></i>Important Notice</h6>
                                                            <p class="mb-0">Online payment gateway integration is currently under development. All payments should be made through the school office or authorized payment channels only.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="card-title mb-0">
                                                            <i class="bi bi-info-circle"></i> Payment Instructions
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <h6>Current Payment Methods:</h6>
                                                        <div class="list-group list-group-flush">
                                                            <div class="list-group-item px-0">
                                                                <i class="bi bi-cash-coin text-success me-2"></i>
                                                                <strong>Cash Payment</strong><br>
                                                                <small class="text-muted">Visit school office with fee receipt</small>
                                                            </div>
                                                            <div class="list-group-item px-0">
                                                                <i class="bi bi-bank text-primary me-2"></i>
                                                                <strong>Bank Transfer</strong><br>
                                                                <small class="text-muted">Direct bank transfer to school account</small>
                                                            </div>
                                                            <div class="list-group-item px-0">
                                                                <i class="bi bi-receipt text-info me-2"></i>
                                                                <strong>Cheque Payment</strong><br>
                                                                <small class="text-muted">Crossed cheque in favor of school</small>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="text-center">
                                                            <h6>Contact Information</h6>
                                                            <p class="mb-1">
                                                                <i class="bi bi-telephone me-1"></i>
                                                                +91-XXXXXXXXXX<br>
                                                                <i class="bi bi-envelope me-1"></i>
                                                                fees@school.com
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

    <!-- Fee Details Modal -->
    <div class="modal fade" id="feeDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="feeDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="receiptContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadReceiptBtn" style="display: none;">
                        <i class="bi bi-download"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Initialize DataTables
    $(document).ready(function() {
        $('#feesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, 'asc']], // Sort by due date
            columnDefs: [
                { orderable: false, targets: 7 } // Actions column
            ]
        });

        $('#paymentHistoryTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[2, 'desc']], // Sort by payment date
            columnDefs: [
                { orderable: false, targets: 6 } // Actions column
            ]
        });
    });

    // Refresh fee data
    function refreshFeeData() {
        location.reload();
    }

    // View fee details
    function viewFeeDetails(feeId) {
        $('#feeDetailsModal').modal('show');

        $.ajax({
            url: '/student/fees/getFeeDetails',
            method: 'POST',
            data: { fee_id: feeId },
            success: function(response) {
                if (response.success) {
                    displayFeeDetails(response.data);
                } else {
                    $('#feeDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#feeDetailsContent').html('<div class="alert alert-danger">Failed to load fee details.</div>');
            }
        });
    }

    // Display fee details in modal
    function displayFeeDetails(data) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Fee Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Type:</strong></td><td>${data.fee.fee_type}</td></tr>
                        <tr><td><strong>Academic Year:</strong></td><td>${data.fee.academic_year}</td></tr>
                        <tr><td><strong>Total Amount:</strong></td><td>₹${parseFloat(data.fee.amount).toFixed(2)}</td></tr>
                        <tr><td><strong>Due Date:</strong></td><td>${new Date(data.fee.due_date).toLocaleDateString()}</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge ${data.fee.is_paid ? 'bg-success' : 'bg-warning'}">${data.fee.is_paid ? 'Paid' : 'Pending'}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Payment Summary</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Total Paid:</strong></td><td class="text-success">₹${parseFloat(data.total_paid).toFixed(2)}</td></tr>
                        <tr><td><strong>Remaining:</strong></td><td class="${data.remaining > 0 ? 'text-warning' : 'text-success'}">₹${parseFloat(data.remaining).toFixed(2)}</td></tr>
                    </table>
                </div>
            </div>
        `;

        if (data.payments && data.payments.length > 0) {
            html += `
                <h6 class="mt-3">Payment History</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.payments.forEach(function(payment) {
                html += `
                    <tr>
                        <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                        <td>₹${parseFloat(payment.amount_paid).toFixed(2)}</td>
                        <td>${payment.payment_mode}</td>
                        <td><span class="badge bg-info">${payment.receipt_number}</span></td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        $('#feeDetailsContent').html(html);
    }

    // View receipt
    function viewReceipt(paymentId) {
        $('#receiptModal').modal('show');

        $.ajax({
            url: '/student/fees/viewReceipt',
            method: 'POST',
            data: { payment_id: paymentId },
            success: function(response) {
                if (response.success) {
                    displayReceipt(response.data);
                } else {
                    $('#receiptContent').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#receiptContent').html('<div class="alert alert-danger">Failed to load receipt.</div>');
            }
        });
    }

    // Display receipt in modal
    function displayReceipt(data) {
        let html = `
            <div class="text-center mb-3">
                <h4>School Management System</h4>
                <h6>Fee Payment Receipt</h6>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Receipt Number:</strong> ${data.payment.receipt_number}<br>
                    <strong>Payment Date:</strong> ${new Date(data.payment.payment_date).toLocaleDateString()}<br>
                    <strong>Payment Mode:</strong> ${data.payment.payment_mode}<br>
                    ${data.payment.transaction_id ? '<strong>Transaction ID:</strong> ' + data.payment.transaction_id + '<br>' : ''}
                </div>
                <div class="col-md-6">
                    <strong>Student Name:</strong> ${data.student.first_name} ${data.student.middle_name} ${data.student.last_name}<br>
                    <strong>Scholar Number:</strong> ${data.student.scholar_number}<br>
                    <strong>Class:</strong> ${data.student.class_name} - ${data.student.class_section}<br>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Fee Type:</strong> ${data.fee.fee_type}<br>
                    <strong>Academic Year:</strong> ${data.fee.academic_year}<br>
                </div>
                <div class="col-md-6">
                    <strong>Amount Paid:</strong> ₹${parseFloat(data.payment.amount_paid).toFixed(2)}<br>
                    <strong>Total Fee:</strong> ₹${parseFloat(data.fee.amount).toFixed(2)}<br>
                </div>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">This is a computer generated receipt</small>
            </div>
        `;

        $('#receiptContent').html(html);
        $('#downloadReceiptBtn').attr('onclick', `window.open('/student/fees/downloadReceipt?payment_id=${data.payment.id}', '_blank')`);
        $('#downloadReceiptBtn').show();
    }

    // Pay fee (placeholder for future implementation)
    function payFee(feeId) {
        // Switch to pay online tab
        $('#pay-tab').tab('show');

        // Show alert for now
        alert('Online payment feature will be available soon. Please contact the school administration for payment.');
    }
    </script>
</body>
</html>