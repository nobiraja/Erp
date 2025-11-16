<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Financial Reports'); ?></title>
    <meta name="description" content="View financial reports - Cashier Interface">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        .report-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .report-card:hover {
            transform: translateY(-2px);
        }
        .report-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
        }
        .chart-container {
            position: relative;
            height: 250px;
        }
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
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
                    <a class="nav-link" href="/cashier/fees/collect">
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
                    <a class="nav-link active" href="/cashier/fees/reports">
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
                        <h5 class="mb-0">Financial Reports</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reports</li>
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

        <!-- Reports Content -->
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

            <!-- Report Type Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Select Report Type
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card report-card text-center p-3" onclick="selectReportType('collection')">
                                        <i class="bi bi-cash text-success fs-1 mb-2"></i>
                                        <h6>My Collection</h6>
                                        <small class="text-muted">Payments collected by me</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card report-card text-center p-3" onclick="selectReportType('daily')">
                                        <i class="bi bi-calendar-day text-primary fs-1 mb-2"></i>
                                        <h6>Daily Summary</h6>
                                        <small class="text-muted">Daily collection trends</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card report-card text-center p-3" onclick="selectReportType('payment_modes')">
                                        <i class="bi bi-credit-card text-info fs-1 mb-2"></i>
                                        <h6>Payment Methods</h6>
                                        <small class="text-muted">Payment mode breakdown</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="filter-section">
                <form id="reportForm" method="GET" class="row g-3">
                    <input type="hidden" id="report_type" name="type" value="<?php echo htmlspecialchars($reportType); ?>">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($startDate); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($endDate); ?>" required>
                    </div>
                    <div class="col-md-3" id="classFilter" style="display: none;">
                        <label for="class_id" class="form-label">Class (Optional)</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo ($classId ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Generate Report
                        </button>
                        <button type="button" onclick="exportReport('csv')" class="btn btn-success">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report Content -->
            <div id="reportContent">
                <?php if ($reportType === 'collection'): ?>
                    <!-- My Collection Report -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-cash me-2"></i>
                                        My Fee Collection Report
                                        <small class="text-muted">(<?php echo date('d-m-Y', strtotime($startDate)); ?> to <?php echo date('d-m-Y', strtotime($endDate)); ?>)</small>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($reportData)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Receipt No</th>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Fee Type</th>
                                                        <th>Amount</th>
                                                        <th>Mode</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $totalAmount = 0;
                                                    foreach ($reportData as $payment):
                                                        $totalAmount += $payment['amount_paid'];
                                                    ?>
                                                        <tr>
                                                            <td><?php echo date('d-m-Y', strtotime($payment['payment_date'])); ?></td>
                                                            <td>
                                                                <span class="badge bg-primary"><?php echo htmlspecialchars($payment['receipt_number']); ?></span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['class_name'] . ' ' . $payment['section']); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['fee_type']); ?></td>
                                                            <td>₹<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['payment_mode']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-primary">
                                                        <th colspan="5">Total Collected</th>
                                                        <th>₹<?php echo number_format($totalAmount, 2); ?></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-graph-up text-muted fs-1 mb-3"></i>
                                            <h6 class="text-muted">No collection data found for the selected period</h6>
                                            <p class="text-muted">Try adjusting the date range</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-bar-chart me-2"></i>
                                        Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="collectionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($reportType === 'daily'): ?>
                    <!-- Daily Summary Report -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-day me-2"></i>
                                Daily Collection Summary
                                <small class="text-muted">(<?php echo date('d-m-Y', strtotime($startDate)); ?> to <?php echo date('d-m-Y', strtotime($endDate)); ?>)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container mb-4">
                                <canvas id="dailyChart"></canvas>
                            </div>
                            <?php if (!empty($reportData)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Payments</th>
                                                <th>Amount</th>
                                                <th>Average</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportData as $day): ?>
                                                <tr>
                                                    <td><?php echo date('d-m-Y', strtotime($day['date'])); ?></td>
                                                    <td><?php echo $day['payments']; ?></td>
                                                    <td>₹<?php echo number_format($day['amount'], 2); ?></td>
                                                    <td>₹<?php echo $day['payments'] > 0 ? number_format($day['amount'] / $day['payments'], 2) : '0.00'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-x text-muted fs-1 mb-3"></i>
                                    <h6 class="text-muted">No daily data available</h6>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($reportType === 'payment_modes'): ?>
                    <!-- Payment Methods Report -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-credit-card me-2"></i>
                                        Payment Methods Breakdown
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="paymentModeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-table me-2"></i>
                                        Payment Methods Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Payment Mode</th>
                                                    <th>Count</th>
                                                    <th>Amount</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $totalPayments = 0;
                                                $totalAmount = 0;
                                                foreach ($reportData['payment_modes'] as $mode => $data) {
                                                    $totalPayments += $data['count'];
                                                    $totalAmount += $data['amount'];
                                                }
                                                foreach ($reportData['payment_modes'] as $mode => $data):
                                                ?>
                                                    <tr>
                                                        <td><?php echo ucfirst($mode); ?></td>
                                                        <td><?php echo $data['count']; ?></td>
                                                        <td>₹<?php echo number_format($data['amount'], 2); ?></td>
                                                        <td><?php echo $totalAmount > 0 ? round(($data['amount'] / $totalAmount) * 100, 1) : 0; ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th>Total</th>
                                                    <th><?php echo $totalPayments; ?></th>
                                                    <th>₹<?php echo number_format($totalAmount, 2); ?></th>
                                                    <th>100%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        let selectedReportType = '<?php echo $reportType; ?>';

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

            // Initialize selected report type
            updateReportSelection(selectedReportType);

            // Initialize charts if data is available
            initializeCharts();
        });

        // Select report type
        function selectReportType(type) {
            selectedReportType = type;
            document.getElementById('report_type').value = type;
            updateReportSelection(type);

            // Show/hide class filter based on report type
            const classFilter = document.getElementById('classFilter');
            if (type === 'collection') {
                classFilter.style.display = 'block';
            } else {
                classFilter.style.display = 'none';
            }
        }

        // Update report selection UI
        function updateReportSelection(type) {
            document.querySelectorAll('.report-card').forEach(card => {
                card.classList.remove('selected');
            });

            const selectedCard = document.querySelector(`[onclick="selectReportType('${type}')"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
        }

        // Export report
        function exportReport(format) {
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);
            formData.append('format', format);

            const url = '/cashier/fees/export-report?' + new URLSearchParams(formData).toString();

            if (format === 'csv') {
                window.location.href = url;
            } else {
                window.open(url, '_blank');
            }
        }

        // Initialize Charts
        function initializeCharts() {
            <?php if ($reportType === 'collection' && !empty($reportData)): ?>
                // Collection summary chart
                const collectionCtx = document.getElementById('collectionChart');
                if (collectionCtx) {
                    new Chart(collectionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Cash', 'Online', 'Cheque', 'UPI'],
                            datasets: [{
                                data: [45, 25, 15, 15], // Placeholder data - would be populated from actual data
                                backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            <?php endif; ?>

            <?php if ($reportType === 'daily' && !empty($reportData)): ?>
                // Daily collection chart
                const dailyCtx = document.getElementById('dailyChart');
                if (dailyCtx) {
                    const dates = <?php echo json_encode(array_column($reportData, 'date')); ?>;
                    const amounts = <?php echo json_encode(array_column($reportData, 'amount')); ?>;

                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: dates.map(date => new Date(date).toLocaleDateString()),
                            datasets: [{
                                label: 'Daily Collection (₹)',
                                data: amounts,
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
            <?php endif; ?>

            <?php if ($reportType === 'payment_modes' && !empty($reportData)): ?>
                // Payment modes chart
                const paymentModeCtx = document.getElementById('paymentModeChart');
                if (paymentModeCtx) {
                    new Chart(paymentModeCtx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode(array_keys($reportData['payment_modes'])); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_column($reportData['payment_modes'], 'amount')); ?>,
                                backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            <?php endif; ?>
        }
    </script>
</body>
</html>