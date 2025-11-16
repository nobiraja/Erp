<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Financial Reports'); ?></title>
    <meta name="description" content="Financial reports with revenue, expenses, and fee collection analysis">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Chart.js for interactive charts -->
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
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .report-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
        }
        .revenue-positive { color: #28a745; font-weight: bold; }
        .revenue-negative { color: #dc3545; font-weight: bold; }
        .expense-high { color: #dc3545; font-weight: bold; }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .financial-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
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
                    <a class="nav-link" href="/admin/fees">
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
                    <a class="nav-link active" href="/admin/reports">
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
                        <h5 class="mb-0"><?php echo htmlspecialchars($title); ?></h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/reports">Reports</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Financial</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/reports" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Reports
                    </a>
                    <button type="button" class="btn btn-primary" onclick="generateReport()">
                        <i class="bi bi-graph-up me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </header>

        <!-- Financial Reports Content -->
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

            <!-- Report Filters -->
            <div class="filter-section">
                <form id="reportForm" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Report Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue (Fee Collection)</option>
                            <option value="expenses" <?php echo $report_type === 'expenses' ? 'selected' : ''; ?>>Expenses</option>
                            <option value="summary" <?php echo $report_type === 'summary' ? 'selected' : ''; ?>>Financial Summary</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="tuition" <?php echo $category === 'tuition' ? 'selected' : ''; ?>>Tuition Fees</option>
                            <option value="transport" <?php echo $category === 'transport' ? 'selected' : ''; ?>>Transport Fees</option>
                            <option value="stationery" <?php echo $category === 'stationery' ? 'selected' : ''; ?>>Stationery</option>
                            <option value="maintenance" <?php echo $category === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="utilities" <?php echo $category === 'utilities' ? 'selected' : ''; ?>>Utilities</option>
                            <option value="salary" <?php echo $category === 'salary' ? 'selected' : ''; ?>>Salary</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Generate
                        </button>
                        <?php if ($report_data): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-info" onclick="exportReport('excel')">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Report Results -->
            <?php if ($report_data): ?>
                <?php if ($report_type === 'summary'): ?>
                    <!-- Financial Summary -->
                    <div class="financial-summary">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="h3 mb-1"><?php echo number_format($report_data['total_revenue'], 2); ?></div>
                                <small>Total Revenue</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 mb-1"><?php echo number_format($report_data['total_expenses'], 2); ?></div>
                                <small>Total Expenses</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 mb-1 <?php echo $report_data['net_income'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($report_data['net_income'], 2); ?>
                                </div>
                                <small>Net Income</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 mb-1"><?php echo $report_data['total_payments']; ?></div>
                                <small>Total Transactions</small>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Charts -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Revenue vs Expenses</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="revenueExpenseChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Monthly Trend</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlyTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Detailed Financial Report -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-cash me-2"></i>
                                <?php
                                echo $report_type === 'revenue' ? 'Revenue Report' :
                                     ($report_type === 'expenses' ? 'Expenses Report' : 'Financial Report');
                                ?>
                                <?php if ($start_date && $end_date): ?>
                                    (<?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?>)
                                <?php endif; ?>
                            </h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printReport()">
                                    <i class="bi bi-printer me-1"></i>Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Financial Charts -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <?php echo $report_type === 'revenue' ? 'Revenue by Category' : 'Expenses by Category'; ?>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="categoryChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Monthly Breakdown</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="monthlyChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transactions Table -->
                            <div class="table-responsive">
                                <table id="financialTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <?php if ($report_type === 'revenue'): ?>
                                                <th>Receipt No.</th>
                                            <?php else: ?>
                                                <th>Payment Mode</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $categoryTotals = [];
                                        $monthlyTotals = [];
                                        $totalAmount = 0;

                                        foreach ($report_data as $record):
                                            $amount = $record['amount'];
                                            $totalAmount += $amount;

                                            // Category totals
                                            $cat = $record['category'];
                                            if (!isset($categoryTotals[$cat])) {
                                                $categoryTotals[$cat] = 0;
                                            }
                                            $categoryTotals[$cat] += $amount;

                                            // Monthly totals
                                            $month = date('M Y', strtotime($record['date']));
                                            if (!isset($monthlyTotals[$month])) {
                                                $monthlyTotals[$month] = 0;
                                            }
                                            $monthlyTotals[$month] += $amount;
                                        ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                                                <td><?php echo htmlspecialchars($record['description']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars(ucfirst($record['category'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $record['type'] === 'Revenue' ? 'success' : 'danger'; ?>">
                                                        <?php echo htmlspecialchars($record['type']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="<?php echo $record['type'] === 'Revenue' ? 'revenue-positive' : 'expense-high'; ?>">
                                                        ₹<?php echo number_format($amount, 2); ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php if ($report_type === 'revenue'): ?>
                                                        <?php echo htmlspecialchars($record['receipt_number'] ?? 'N/A'); ?>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($record['payment_mode'] ?? 'N/A'); ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <th colspan="4" class="text-end">Total:</th>
                                            <th class="text-end">₹<?php echo number_format($totalAmount, 2); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Category Summary -->
                            <div class="row mt-4">
                                <?php foreach ($categoryTotals as $category => $total): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="text-center">
                                            <div class="h5 <?php echo $report_type === 'revenue' ? 'revenue-positive' : 'expense-high'; ?>">
                                                ₹<?php echo number_format($total, 2); ?>
                                            </div>
                                            <small class="text-muted"><?php echo ucfirst($category); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Data Message -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cash display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No Financial Data</h5>
                        <p class="text-muted">Please select filters and generate a financial report.</p>
                    </div>
                </div>
            <?php endif; ?>
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

            // Initialize DataTable if report data exists
            <?php if ($report_data && $report_type !== 'summary'): ?>
                $('#financialTable').DataTable({
                    "pageLength": 25,
                    "ordering": true,
                    "searching": true,
                    "paging": true,
                    "info": true,
                    "responsive": true,
                    "order": [[0, "desc"]] // Sort by date
                });
            <?php endif; ?>

            // Initialize charts
            <?php if ($report_data): ?>
                initializeCharts();
            <?php endif; ?>
        });

        // Initialize charts with report data
        function initializeCharts() {
            <?php if ($report_data): ?>
                <?php if ($report_type === 'summary'): ?>
                    // Revenue vs Expenses chart
                    const revExpCtx = document.getElementById('revenueExpenseChart').getContext('2d');
                    new Chart(revExpCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Revenue', 'Expenses'],
                            datasets: [{
                                data: [<?php echo $report_data['total_revenue']; ?>, <?php echo $report_data['total_expenses']; ?>],
                                backgroundColor: [
                                    'rgba(40, 167, 69, 0.8)',
                                    'rgba(220, 53, 69, 0.8)'
                                ],
                                borderWidth: 1
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

                    // Monthly trend chart (placeholder - would need monthly data)
                    const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
                    new Chart(monthlyCtx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                label: 'Net Income',
                                data: [12000, 15000, 18000, 16000, 20000, 19000, 22000, 21000, 24000, 23000, 25000, 26000],
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                <?php else: ?>
                    // Category breakdown chart
                    const categoryData = <?php echo json_encode($categoryTotals); ?>;
                    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
                    new Chart(categoryCtx, {
                        type: 'pie',
                        data: {
                            labels: Object.keys(categoryData),
                            datasets: [{
                                data: Object.values(categoryData),
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.8)',
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(255, 205, 86, 0.8)',
                                    'rgba(75, 192, 192, 0.8)',
                                    'rgba(153, 102, 255, 0.8)',
                                    'rgba(255, 159, 64, 0.8)'
                                ],
                                borderWidth: 1
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

                    // Monthly breakdown chart
                    const monthlyData = <?php echo json_encode($monthlyTotals); ?>;
                    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: Object.keys(monthlyData),
                            datasets: [{
                                label: '<?php echo ucfirst($report_type); ?> Amount',
                                data: Object.values(monthlyData),
                                backgroundColor: '<?php echo $report_type === 'revenue' ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)'; ?>',
                                borderColor: '<?php echo $report_type === 'revenue' ? 'rgba(40, 167, 69, 1)' : 'rgba(220, 53, 69, 1)'; ?>',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                <?php endif; ?>
            <?php endif; ?>
        }

        // Generate report
        function generateReport() {
            document.getElementById('reportForm').submit();
        }

        // Export report
        function exportReport(format) {
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);

            // Create export URL
            const exportUrl = format === 'pdf' ? '/admin/reports/export-pdf' : '/admin/reports/export-excel';

            // Create a temporary form for export
            const exportForm = document.createElement('form');
            exportForm.method = 'POST';
            exportForm.action = exportUrl;
            exportForm.style.display = 'none';

            // Add type parameter
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'financial';
            exportForm.appendChild(typeInput);

            // Copy form data
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                exportForm.appendChild(input);
            }

            document.body.appendChild(exportForm);
            exportForm.submit();
            document.body.removeChild(exportForm);
        }

        // Print report
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>