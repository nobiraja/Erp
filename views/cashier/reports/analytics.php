<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Financial Analytics'); ?></title>
    <meta name="description" content="Advanced financial analytics with revenue vs expenses analysis">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <style>
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #343a40; color: white; transition: all 0.3s; z-index: 1000; overflow-y: auto; }
        .sidebar.collapsed { width: 70px; }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: 0.75rem 1rem; transition: all 0.3s; }
        .sidebar .nav-link:hover { color: white; background: rgba(255,255,255,.1); }
        .sidebar .nav-link.active { color: white; background: #007bff; }
        .sidebar .nav-link i { width: 20px; margin-right: 10px; }
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .main-content { margin-left: 250px; transition: margin-left 0.3s; }
        .main-content.expanded { margin-left: 70px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-overlay.show { display: block; }
        }
        .chart-container { position: relative; height: 300px; }
        .filter-section { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; margin-bottom: 1rem; }
        .analytics-card { transition: transform 0.2s; }
        .analytics-card:hover { transform: translateY(-2px); }
        .profit-positive { color: #28a745; }
        .profit-negative { color: #dc3545; }
        .metric-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <img src="/images/logo-small.png" alt="Logo" class="me-2" style="height: 30px; width: auto;" onerror="this.style.display='none'">
                <span class="fw-bold">SMS</span>
                <button class="hamburger-menu d-none d-md-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="/cashier/fees"><i class="bi bi-house-door"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/cashier/fees/collect"><i class="bi bi-cash"></i><span>Fee Collection</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/cashier/fees/outstanding"><i class="bi bi-exclamation-triangle"></i><span>Outstanding Fees</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="/cashier/reports"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/cashier/expenses"><i class="bi bi-receipt"></i><span>Expenses</span></a></li>
            </ul>
            <div class="mt-auto pt-4 border-top">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-2"></i>
                    <div class="flex-grow-1 ms-2">
                        <div class="fw-bold small">Cashier</div>
                        <div class="text-muted small">Cashier</div>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="/logout" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <header class="bg-white shadow-sm border-bottom">
            <div class="d-flex align-items-center justify-content-between px-4 py-3">
                <div>
                    <h5 class="mb-0">Financial Analytics</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/cashier/reports">Reports</a></li>
                            <li class="breadcrumb-item active">Analytics</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="/cashier/reports" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Reports
                    </a>
                    <button class="btn btn-success" onclick="exportReport('pdf')">
                        <i class="bi bi-download me-1"></i>Export PDF
                    </button>
                    <button class="btn btn-primary" onclick="exportReport('excel')">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                    </button>
                </div>
            </div>
        </header>

        <main class="p-4">
            <!-- Filters -->
            <div class="filter-section">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card metric-card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1">₹<?php echo number_format($analytics['revenue']['total_revenue']); ?></h3>
                                        <p class="mb-0 opacity-75">Total Revenue</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1">₹<?php echo number_format($analytics['expenses']['total_amount']); ?></h3>
                                        <p class="mb-0 opacity-75">Total Expenses</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1 <?php echo $analytics['profitability']['status'] === 'profit' ? 'profit-positive' : 'profit-negative'; ?>">
                                            ₹<?php echo number_format(abs($analytics['net_profit'])); ?>
                                        </h3>
                                        <p class="mb-0 opacity-75">
                                            Net <?php echo $analytics['profitability']['status'] === 'profit' ? 'Profit' : 'Loss'; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h3 class="mb-1"><?php echo number_format($analytics['profitability']['profit_margin'], 1); ?>%</h3>
                                    <p class="mb-0 opacity-75">Profit Margin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue vs Expenses Comparison -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Revenue vs Expenses Trend
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueExpenseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Revenue Breakdown
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueBreakdownChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Collection Performance
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary"><?php echo $analytics['revenue']['total_collections']; ?></h4>
                                    <small class="text-muted">Total Collections</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success">₹<?php echo number_format($analytics['revenue']['avg_collection'], 2); ?></h4>
                                    <small class="text-muted">Avg per Collection</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-info"><?php echo $analytics['revenue']['active_days']; ?></h5>
                                    <small class="text-muted">Active Days</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-warning">₹<?php echo number_format($analytics['revenue']['daily_avg']); ?></h5>
                                    <small class="text-muted">Daily Average</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-cash me-2"></i>
                                Expense Analysis
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-danger"><?php echo $analytics['expenses']['total_expenses']; ?></h4>
                                    <small class="text-muted">Total Expenses</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">₹<?php echo number_format($analytics['expenses']['avg_expense'], 2); ?></h4>
                                    <small class="text-muted">Avg per Expense</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-info"><?php echo number_format($analytics['ratios']['expense_to_revenue'], 1); ?>%</h5>
                                    <small class="text-muted">Expense Ratio</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-secondary"><?php echo number_format($analytics['ratios']['collection_rate'], 1); ?></h5>
                                    <small class="text-muted">Collection Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Modes and Trends -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Payment Methods Analysis
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Method</th>
                                            <th>Count</th>
                                            <th>Amount</th>
                                            <th>% of Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics['payment_modes'] as $mode): ?>
                                            <tr>
                                                <td><?php echo ucfirst($mode['payment_mode']); ?></td>
                                                <td><?php echo $mode['transaction_count']; ?></td>
                                                <td>₹<?php echo number_format($mode['total_amount']); ?></td>
                                                <td><?php echo number_format(($mode['total_amount'] / $analytics['revenue']['total_revenue']) * 100, 1); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-month me-2"></i>
                                Monthly Trends (Last 6 Months)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outstanding Fees Summary -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Outstanding Fees Overview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h4 class="text-warning"><?php echo $analytics['outstanding']['total_outstanding']; ?></h4>
                                    <small class="text-muted">Outstanding Fees</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-danger">₹<?php echo number_format($analytics['outstanding']['outstanding_amount']); ?></h4>
                                    <small class="text-muted">Outstanding Amount</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-info">₹<?php echo number_format($analytics['outstanding']['total_outstanding'] > 0 ? $analytics['outstanding']['outstanding_amount'] / $analytics['outstanding']['total_outstanding'] : 0, 2); ?></h4>
                                    <small class="text-muted">Avg Outstanding</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-secondary"><?php echo $analytics['outstanding']['overdue_30_plus']; ?></h4>
                                    <small class="text-muted">Overdue >30 Days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    const icon = this.querySelector('i');
                    icon.className = sidebar.classList.contains('collapsed') ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
                });
            }

            initializeCharts();
        });

        // Initialize Charts
        function initializeCharts() {
            // Revenue vs Expenses chart
            const revExpCtx = document.getElementById('revenueExpenseChart');
            if (revExpCtx) {
                const trends = <?php echo json_encode($analytics['trends']); ?>;
                const labels = trends.map(t => t.month);
                const revenueData = trends.map(t => t.revenue || 0);
                const expenseData = trends.map(t => t.expenses || 0);

                new Chart(revExpCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue (₹)',
                            data: revenueData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Expenses (₹)',
                            data: expenseData,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { position: 'top' } }
                    }
                });
            }

            // Revenue breakdown chart
            const breakdownCtx = document.getElementById('revenueBreakdownChart');
            if (breakdownCtx) {
                const paymentModes = <?php echo json_encode($analytics['payment_modes']); ?>;
                new Chart(breakdownCtx, {
                    type: 'pie',
                    data: {
                        labels: paymentModes.map(m => m.payment_mode.charAt(0).toUpperCase() + m.payment_mode.slice(1)),
                        datasets: [{
                            data: paymentModes.map(m => m.total_amount),
                            backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Monthly trend chart
            const trendCtx = document.getElementById('monthlyTrendChart');
            if (trendCtx) {
                const trends = <?php echo json_encode($analytics['trends']); ?>;
                const labels = trends.map(t => t.month);
                const netData = trends.map(t => (t.revenue || 0) - (t.expenses || 0));

                new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Net Profit/Loss (₹)',
                            data: netData,
                            backgroundColor: netData.map(val => val >= 0 ? '#28a745' : '#dc3545'),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }

        // Apply filters
        function applyFilters() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            window.location.href = `/cashier/reports/analytics?start_date=${startDate}&end_date=${endDate}`;
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('start_date').value = '<?php echo date('Y-m-01'); ?>';
            document.getElementById('end_date').value = '<?php echo date('Y-m-t'); ?>';
        }

        // Export functions
        function exportReport(format) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const url = `/cashier/reports/export-${format}?type=analytics&start_date=${startDate}&end_date=${endDate}`;

            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>