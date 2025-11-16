<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Collection Summary Report'); ?></title>
    <meta name="description" content="Detailed collection summary with payment modes and trends">

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
        .summary-card { transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <!-- Sidebar (same as index) -->
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
                    <h5 class="mb-0">Collection Summary Report</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/cashier/reports">Reports</a></li>
                            <li class="breadcrumb-item active">Collection Summary</li>
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

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card summary-card border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-cash text-success fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($summary['grand_total']); ?></h4>
                            <p class="text-muted mb-0">Total Collections</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt text-primary fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $summary['total_payments']; ?></h4>
                            <p class="text-muted mb-0">Total Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-day text-info fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo count($summary['daily_totals']); ?></h4>
                            <p class="text-muted mb-0">Active Days</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo count($summary['daily_totals']) > 0 ? number_format($summary['grand_total'] / count($summary['daily_totals'])) : '0'; ?></h4>
                            <p class="text-muted mb-0">Daily Average</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Daily Collection Trend
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="collectionChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-table me-2"></i>
                                Daily Collection Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Payments</th>
                                            <th>Total Amount</th>
                                            <th>Payment Modes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($summary['daily_totals'] as $day): ?>
                                            <tr>
                                                <td><?php echo date('d-m-Y', strtotime($day['date'])); ?></td>
                                                <td><?php echo $day['payments']; ?></td>
                                                <td>₹<?php echo number_format($day['amount']); ?></td>
                                                <td>
                                                    <?php foreach ($day['modes'] as $mode => $data): ?>
                                                        <span class="badge bg-secondary me-1">
                                                            <?php echo ucfirst($mode); ?>: ₹<?php echo number_format($data['amount']); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <th>Total</th>
                                            <th><?php echo $summary['total_payments']; ?></th>
                                            <th>₹<?php echo number_format($summary['grand_total']); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Payment Modes Breakdown -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Payment Methods
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentModeChart"></canvas>
                            </div>
                            <div class="mt-3">
                                <?php foreach ($summary['payment_modes'] as $mode => $amount): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted"><?php echo ucfirst($mode); ?></span>
                                        <span class="fw-bold">₹<?php echo number_format($amount); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Quick Stats
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h5 class="text-success mb-1">
                                            ₹<?php echo $summary['total_payments'] > 0 ? number_format($summary['grand_total'] / $summary['total_payments'], 2) : '0.00'; ?>
                                        </h5>
                                        <small class="text-muted">Avg per Payment</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-primary mb-1">
                                        <?php echo count($summary['payment_modes']); ?>
                                    </h5>
                                    <small class="text-muted">Payment Types</small>
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
            // Collection trend chart
            const collectionCtx = document.getElementById('collectionChart');
            if (collectionCtx) {
                const chartData = <?php echo json_encode($chartData); ?>;
                new Chart(collectionCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels.map(date => new Date(date).toLocaleDateString()),
                        datasets: [{
                            label: 'Daily Collection (₹)',
                            data: chartData.data,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
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

            // Payment mode chart
            const modeCtx = document.getElementById('paymentModeChart');
            if (modeCtx) {
                const modeData = <?php echo json_encode($summary['payment_modes']); ?>;
                new Chart(modeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(modeData),
                        datasets: [{
                            data: Object.values(modeData),
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
        }

        // Apply filters
        function applyFilters() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            window.location.href = `/cashier/reports/collection-summary?start_date=${startDate}&end_date=${endDate}`;
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
            const url = `/cashier/reports/export-${format}?type=collection_summary&start_date=${startDate}&end_date=${endDate}`;

            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>