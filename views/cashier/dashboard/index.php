<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Cashier Dashboard'); ?></title>
    <meta name="description" content="Cashier dashboard for school management system">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
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
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .alert-card {
            border-left: 4px solid #dc3545;
        }
        .quick-action-btn {
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
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
                    <img src="<?php echo htmlspecialchars($school_logo ?? '/images/logo-small.png'); ?>" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                    <span class="fw-bold" id="sidebarTitle"><?php echo htmlspecialchars(substr($school_name ?? 'SMS', 0, 10)); ?></span>
                </div>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/cashier/dashboard">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/fees">
                        <i class="bi bi-cash"></i>
                        <span>Fee Collection</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/outstanding">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Outstanding Fees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Financial Reports</span>
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
                        <div class="fw-bold small"><?php echo htmlspecialchars($current_user['username'] ?? 'Cashier'); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($current_user['role'] ?? 'Cashier'); ?></div>
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
                        <h5 class="mb-0">Cashier Dashboard</h5>
                        <small class="text-muted">Financial Overview - <?php echo htmlspecialchars($dashboard_data['current_month'] ?? date('F Y')); ?></small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <small class="text-muted"><?php echo htmlspecialchars($dashboard_data['academic_year'] ?? '2024-2025'); ?></small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username'] ?? 'Cashier'); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/cashier/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/cashier/change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-4">
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-success w-100 quick-action-btn" onclick="quickAction('collectFee')">
                                        <i class="bi bi-cash d-block fs-4 mb-1"></i>
                                        <small>Collect Fee</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-primary w-100 quick-action-btn" onclick="quickAction('viewOutstanding')">
                                        <i class="bi bi-exclamation-triangle d-block fs-4 mb-1"></i>
                                        <small>Outstanding</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-info w-100 quick-action-btn" onclick="quickAction('generateReceipt')">
                                        <i class="bi bi-receipt d-block fs-4 mb-1"></i>
                                        <small>Receipt</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-warning w-100 quick-action-btn" onclick="quickAction('addExpense')">
                                        <i class="bi bi-plus-circle d-block fs-4 mb-1"></i>
                                        <small>Add Expense</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-secondary w-100 quick-action-btn" onclick="quickAction('viewReports')">
                                        <i class="bi bi-graph-up d-block fs-4 mb-1"></i>
                                        <small>Reports</small>
                                    </button>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <button class="btn btn-danger w-100 quick-action-btn" onclick="quickAction('overdueAlerts')">
                                        <i class="bi bi-bell d-block fs-4 mb-1"></i>
                                        <small>Alerts</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsCards">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Today's Collection
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="todayCollection">
                                        ₹<?php echo number_format($dashboard_data['today_collection']['amount'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        <?php echo $dashboard_data['today_collection']['count'] ?? 0; ?> payments
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash-stack fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        This Month
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthCollection">
                                        ₹<?php echo number_format($dashboard_data['month_collection']['amount'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        <?php echo $dashboard_data['month_collection']['count'] ?? 0; ?> payments
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Outstanding Fees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="outstandingAmount">
                                        ₹<?php echo number_format($dashboard_data['outstanding_fees']['pending_amount'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        <?php echo $dashboard_data['outstanding_fees']['pending_count'] ?? 0; ?> students
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Overdue Amount
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="overdueAmount">
                                        ₹<?php echo number_format($dashboard_data['outstanding_fees']['overdue_amount'] ?? 0, 2); ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        <?php echo $dashboard_data['outstanding_fees']['overdue_count'] ?? 0; ?> students
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-circle fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts Section -->
            <div class="row mb-4" id="alertsSection">
                <div class="col-12">
                    <div class="card alert-card">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Overdue Payment Alerts</h6>
                        </div>
                        <div class="card-body" id="overdueAlerts">
                            <?php if (!empty($dashboard_data['overdue_payments'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Fee Type</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                                <th>Days Overdue</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($dashboard_data['overdue_payments'], 0, 5) as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['class_section']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['fee_type']); ?></td>
                                                    <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($payment['due_date'])); ?></td>
                                                    <td><span class="badge bg-danger"><?php echo $payment['days_overdue']; ?> days</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success" onclick="collectFee(<?php echo $payment['id']; ?>)">
                                                            Collect
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No overdue payments at this time.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Fee Collection Trends (Last 6 Months)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="collectionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Monthly Expenses</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <h4 class="text-info">₹<?php echo number_format($dashboard_data['expense_summary']['total_amount'] ?? 0, 2); ?></h4>
                                <p class="text-muted"><?php echo $dashboard_data['expense_summary']['count'] ?? 0; ?> expenses this month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities and Outstanding -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Fee Collections</h6>
                        </div>
                        <div class="card-body">
                            <div id="recentCollections">
                                <?php if (!empty($dashboard_data['recent_payments'])): ?>
                                    <?php foreach ($dashboard_data['recent_payments'] as $payment): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($payment['student_name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-cash me-1"></i>₹<?php echo number_format($payment['amount_paid'], 2); ?> - <?php echo htmlspecialchars($payment['fee_type']); ?>
                                                    <br>
                                                    <i class="bi bi-calendar me-1"></i><?php echo date('d-m-Y', strtotime($payment['payment_date'])); ?> - Receipt #<?php echo htmlspecialchars($payment['receipt_number']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No recent collections</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Outstanding Fee Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-warning"><?php echo $dashboard_data['outstanding_fees']['pending_count'] ?? 0; ?></h5>
                                    <small class="text-muted">Pending Students</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-danger"><?php echo $dashboard_data['outstanding_fees']['overdue_count'] ?? 0; ?></h5>
                                    <small class="text-muted">Overdue Students</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Total Outstanding:</span>
                                <strong>₹<?php echo number_format(($dashboard_data['outstanding_fees']['pending_amount'] ?? 0) + ($dashboard_data['outstanding_fees']['overdue_amount'] ?? 0), 2); ?></strong>
                            </div>
                            <div class="mt-3">
                                <a href="/cashier/outstanding" class="btn btn-primary btn-sm w-100">View All Outstanding</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

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

            // Load dashboard data
            loadDashboardData();

            // Initialize charts
            initializeCharts();
        });

        // Quick actions
        function quickAction(action) {
            const urls = {
                collectFee: '/cashier/fees',
                viewOutstanding: '/cashier/outstanding',
                generateReceipt: '/cashier/fees',
                addExpense: '/cashier/expenses',
                viewReports: '/cashier/reports',
                overdueAlerts: '/cashier/outstanding'
            };

            if (urls[action]) {
                window.location.href = urls[action];
            }
        }

        // Collect fee for specific student
        function collectFee(feeId) {
            window.location.href = '/cashier/fees?fee_id=' + feeId;
        }

        // Load dashboard data via AJAX
        function loadDashboardData() {
            // Load updated statistics
            fetch('/cashier/dashboard/getDashboardStats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data.data);
                    }
                })
                .catch(error => console.log('Failed to load dashboard stats:', error));
        }

        // Update statistics display
        function updateStats(data) {
            if (data.today_collection) {
                document.getElementById('todayCollection').textContent = '₹' + (data.today_collection.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            }

            if (data.month_collection) {
                document.getElementById('monthCollection').textContent = '₹' + (data.month_collection.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            }

            if (data.outstanding_fees) {
                document.getElementById('outstandingAmount').textContent = '₹' + (data.outstanding_fees.pending_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
                document.getElementById('overdueAmount').textContent = '₹' + (data.outstanding_fees.overdue_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            }
        }

        // Initialize charts
        function initializeCharts() {
            // Fee collection trends chart
            fetch('/cashier/dashboard/getFeeCollectionTrends')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        createCollectionChart(data.data);
                    }
                })
                .catch(error => console.log('Failed to load collection trends:', error));
        }

        // Create collection trends chart
        function createCollectionChart(data) {
            const ctx = document.getElementById('collectionChart').getContext('2d');
            const labels = data.map(item => item.month_name);
            const values = data.map(item => item.collected_amount);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Fee Collection (₹)',
                        data: values,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Auto refresh data every 5 minutes
        setInterval(loadDashboardData, 300000);
    </script>
</body>
</html>