<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Financial Reports'); ?></title>
    <meta name="description" content="Financial reports dashboard for cashier module">

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
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .chart-container {
            position: relative;
            height: 200px;
        }
        .quick-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                    <a class="nav-link active" href="/cashier/reports">
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
                        <h5 class="mb-0">Financial Reports Dashboard</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reports</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end">
                        <div class="small text-muted">Last 30 Days</div>
                        <div class="fw-bold"><?php echo date('M j - ') . date('M j, Y', strtotime('-29 days')); ?></div>
                    </div>
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

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card quick-stats text-white">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1">₹<?php echo number_format($quickStats['collection']['total_amount'] ?? 0); ?></h3>
                                        <p class="mb-0 opacity-75">Total Collections</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1"><?php echo $quickStats['collection']['total_payments'] ?? 0; ?></h3>
                                        <p class="mb-0 opacity-75">Payments</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border-end border-white border-opacity-25">
                                        <h3 class="mb-1">₹<?php echo number_format($quickStats['expenses']['total_amount'] ?? 0); ?></h3>
                                        <p class="mb-0 opacity-75">Total Expenses</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h3 class="mb-1">₹<?php echo number_format($quickStats['net_position'], 2); ?></h3>
                                    <p class="mb-0 opacity-75">Net Position</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Types -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="mb-3">
                        <i class="bi bi-graph-up me-2"></i>
                        Available Reports
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <div class="card report-card h-100" onclick="window.location.href='/cashier/reports/collection-summary'">
                                <div class="card-body text-center">
                                    <i class="bi bi-cash text-success fs-1 mb-3"></i>
                                    <h6 class="card-title">Collection Summary</h6>
                                    <p class="card-text text-muted small">Detailed collection reports with payment modes and trends</p>
                                    <span class="badge bg-success">Most Used</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card report-card h-100" onclick="window.location.href='/cashier/reports/expenses'">
                                <div class="card-body text-center">
                                    <i class="bi bi-receipt text-danger fs-1 mb-3"></i>
                                    <h6 class="card-title">Expense Reports</h6>
                                    <p class="card-text text-muted small">Expense analysis by category and time period</p>
                                    <span class="badge bg-danger">Critical</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card report-card h-100" onclick="window.location.href='/cashier/reports/analytics'">
                                <div class="card-body text-center">
                                    <i class="bi bi-bar-chart text-primary fs-1 mb-3"></i>
                                    <h6 class="card-title">Financial Analytics</h6>
                                    <p class="card-text text-muted small">Revenue vs expenses, profit analysis, and trends</p>
                                    <span class="badge bg-primary">Advanced</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card report-card h-100" onclick="window.location.href='/cashier/fees/reports'">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history text-info fs-1 mb-3"></i>
                                    <h6 class="card-title">Quick Reports</h6>
                                    <p class="card-text text-muted small">Basic collection and payment mode reports</p>
                                    <span class="badge bg-info">Legacy</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-activity me-2"></i>
                                Collection Trends (Last 7 Days)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="collectionTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Payment Methods (Last 30 Days)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentModeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-download me-2"></i>
                                Export Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <button class="btn btn-success w-100" onclick="exportReport('collection_summary', 'pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        Collection PDF
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success w-100" onclick="exportReport('expenses', 'pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        Expenses PDF
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary w-100" onclick="exportReport('collection_summary', 'excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Collection Excel
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary w-100" onclick="exportReport('analytics', 'excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Analytics Excel
                                    </button>
                                </div>
                            </div>
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

            // Initialize charts
            initializeCharts();
        });

        // Initialize Charts
        function initializeCharts() {
            // Collection trend chart (placeholder data)
            const trendCtx = document.getElementById('collectionTrendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Daily Collection (₹)',
                            data: [2500, 3200, 2800, 4100, 3800, 1500, 2200],
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Payment mode chart (placeholder data)
            const modeCtx = document.getElementById('paymentModeChart');
            if (modeCtx) {
                new Chart(modeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Cash', 'Online', 'Cheque', 'UPI'],
                        datasets: [{
                            data: [45, 25, 15, 15],
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
        }

        // Export report
        function exportReport(type, format) {
            const startDate = '<?php echo $startDate; ?>';
            const endDate = '<?php echo $endDate; ?>';

            const url = `/cashier/reports/export-${format}?type=${type}&start_date=${startDate}&end_date=${endDate}`;

            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>