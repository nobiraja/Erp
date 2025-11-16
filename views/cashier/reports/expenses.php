<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Expense Reports'); ?></title>
    <meta name="description" content="Detailed expense analysis by category and time period">

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
        .expense-card { transition: transform 0.2s; }
        .expense-card:hover { transform: translateY(-2px); }
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
                    <h5 class="mb-0">Expense Reports</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/cashier/reports">Reports</a></li>
                            <li class="breadcrumb-item active">Expenses</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="/cashier/reports" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Reports
                    </a>
                    <button class="btn btn-info" onclick="printReport()">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
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
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="diesel" <?php echo $category === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="staff" <?php echo $category === 'staff' ? 'selected' : ''; ?>>Staff Salary</option>
                            <option value="bus" <?php echo $category === 'bus' ? 'selected' : ''; ?>>Bus Maintenance</option>
                            <option value="maintenance" <?php echo $category === 'maintenance' ? 'selected' : ''; ?>>General Maintenance</option>
                            <option value="misc" <?php echo $category === 'misc' ? 'selected' : ''; ?>>Miscellaneous</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min Amount</label>
                        <input type="number" class="form-control" id="min_amount" name="min_amount" step="0.01" min="0" value="<?php echo htmlspecialchars($minAmount ?? ''); ?>" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Amount</label>
                        <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" min="0" value="<?php echo htmlspecialchars($maxAmount ?? ''); ?>" placeholder="No limit">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card expense-card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-cash text-danger fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($stats['total_amount']); ?></h4>
                            <p class="text-muted mb-0">Total Expenses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card expense-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $stats['total_expenses']; ?></h4>
                            <p class="text-muted mb-0">Total Transactions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card expense-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up text-info fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($stats['average_amount'], 2); ?></h4>
                            <p class="text-muted mb-0">Average Expense</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card expense-card border-secondary">
                        <div class="card-body text-center">
                            <i class="bi bi-tags text-secondary fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo count($stats['categories']); ?></h4>
                            <p class="text-muted mb-0">Categories Used</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Expense Trend Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Daily Expense Trend
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="expenseChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Category-wise Spending Trend -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Category-wise Spending Trend
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Expense List -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-table me-2"></i>
                                Expense Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Category</th>
                                            <th>Reason</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td><?php echo date('d-m-Y', strtotime($expense->payment_date)); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($expense->getCategoryText()); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($expense->reason, 0, 50)) . (strlen($expense->reason) > 50 ? '...' : ''); ?></td>
                                                <td>₹<?php echo number_format($expense->amount, 2); ?></td>
                                                <td><?php echo htmlspecialchars($expense->getPaymentModeText()); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-danger">
                                            <th colspan="3">Total Expenses</th>
                                            <th>₹<?php echo number_format($stats['total_amount']); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Category Breakdown -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Category Breakdown
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                            <div class="mt-3">
                                <?php foreach ($stats['categories'] as $cat => $data): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted"><?php echo ucfirst($cat); ?></span>
                                        <div class="text-end">
                                            <div class="fw-bold">₹<?php echo number_format($data['amount']); ?></div>
                                            <small class="text-muted"><?php echo $data['count']; ?> items</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top Expenses -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-star me-2"></i>
                                Largest Expenses
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            usort($expenses, fn($a, $b) => $b->amount <=> $a->amount);
                            $topExpenses = array_slice($expenses, 0, 5);
                            ?>
                            <?php foreach ($topExpenses as $expense): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <div class="fw-bold">₹<?php echo number_format($expense->amount, 2); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($expense->reason, 0, 30)); ?></small>
                                    </div>
                                    <span class="badge bg-danger"><?php echo date('d-m', strtotime($expense->payment_date)); ?></span>
                                </div>
                            <?php endforeach; ?>
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
            // Expense trend chart
            const expenseCtx = document.getElementById('expenseChart');
            if (expenseCtx) {
                const chartData = <?php echo json_encode($chartData); ?>;
                new Chart(expenseCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels.map(date => new Date(date).toLocaleDateString()),
                        datasets: [{
                            label: 'Daily Expenses (₹)',
                            data: chartData.data,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.4,
                            fill: true
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

            // Category trend chart
            const categoryTrendCtx = document.getElementById('categoryTrendChart');
            if (categoryTrendCtx) {
                const categoryTrendData = <?php echo json_encode($categoryTrendData); ?>;
                new Chart(categoryTrendCtx, {
                    type: 'line',
                    data: {
                        labels: categoryTrendData.labels.map(date => new Date(date).toLocaleDateString()),
                        datasets: categoryTrendData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { position: 'top' } }
                    }
                });
            }

            // Category chart
            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                const categoryData = <?php echo json_encode($stats['categories']); ?>;
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(categoryData).map(cat => cat.charAt(0).toUpperCase() + cat.slice(1)),
                        datasets: [{
                            data: Object.values(categoryData).map(cat => cat.amount),
                            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#007bff', '#6f42c1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }

        // Apply filters
        function applyFilters() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const category = document.getElementById('category').value;
            const minAmount = document.getElementById('min_amount').value;
            const maxAmount = document.getElementById('max_amount').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            let url = `/cashier/reports/expenses?start_date=${startDate}&end_date=${endDate}`;
            if (category) {
                url += `&category=${category}`;
            }
            if (minAmount) {
                url += `&min_amount=${minAmount}`;
            }
            if (maxAmount) {
                url += `&max_amount=${maxAmount}`;
            }

            window.location.href = url;
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('start_date').value = '<?php echo date('Y-m-01'); ?>';
            document.getElementById('end_date').value = '<?php echo date('Y-m-t'); ?>';
            document.getElementById('category').value = '';
            document.getElementById('min_amount').value = '';
            document.getElementById('max_amount').value = '';
        }

        // Print report
        function printReport() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Expense Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .summary { display: flex; justify-content: space-around; margin: 20px 0; }
                        .summary-item { text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .total-row { font-weight: bold; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Expense Report</h1>
                        <p>Period: ${document.getElementById('start_date').value} to ${document.getElementById('end_date').value}</p>
                    </div>
                    <div class="summary">
                        <div class="summary-item">
                            <h3><?php echo number_format($stats['total_amount']); ?></h3>
                            <p>Total Expenses</p>
                        </div>
                        <div class="summary-item">
                            <h3><?php echo $stats['total_expenses']; ?></h3>
                            <p>Total Transactions</p>
                        </div>
                        <div class="summary-item">
                            <h3>₹<?php echo number_format($stats['average_amount'], 2); ?></h3>
                            <p>Average Expense</p>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Reason</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($expense->payment_date)); ?></td>
                                <td><?php echo htmlspecialchars($expense->getCategoryText()); ?></td>
                                <td><?php echo htmlspecialchars($expense->reason); ?></td>
                                <td>₹<?php echo number_format($expense->amount, 2); ?></td>
                                <td><?php echo htmlspecialchars($expense->getPaymentModeText()); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="3">Total</td>
                                <td>₹<?php echo number_format($stats['total_amount']); ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Export functions
        function exportReport(format) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const category = document.getElementById('category').value;
            let url = `/cashier/reports/export-${format}?type=expenses&start_date=${startDate}&end_date=${endDate}`;
            if (category) {
                url += `&category=${category}`;
            }

            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>