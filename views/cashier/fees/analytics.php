<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Outstanding Fees Analytics'); ?></title>
    <meta name="description" content="Detailed analytics for outstanding fees - Cashier Interface">

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
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }
        .metric-card {
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-2px);
        }
        .trend-up {
            color: #28a745;
        }
        .trend-down {
            color: #dc3545;
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
                    <a class="nav-link active" href="/cashier/fees/analytics">
                        <i class="bi bi-graph-up"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/fees/reports">
                        <i class="bi bi-file-earmark-bar-graph"></i>
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
                        <h5 class="mb-0">Outstanding Fees Analytics</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="refreshAnalytics()" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                    <a href="/cashier/fees/outstanding" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Outstanding
                    </a>
                </div>
            </div>
        </header>

        <!-- Analytics Content -->
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

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card metric-card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-rupee text-primary fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($analytics['total_outstanding'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Total Outstanding</p>
                            <small class="text-muted">Across all classes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-x text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $analytics['overdue_count'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Overdue Fees</p>
                            <small class="text-muted"><?php echo round(($analytics['overdue_count'] ?? 0) / max(1, $analytics['total_fees'] ?? 1) * 100, 1); ?>% of total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-people text-info fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $analytics['affected_students'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Affected Students</p>
                            <small class="text-muted">Unique students</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up text-success fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $analytics['collection_rate'] ?? 0; ?>%</h4>
                            <p class="text-muted mb-0">Collection Rate</p>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Outstanding by Class
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="classChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Overdue Distribution
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="overdueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-clock me-2"></i>
                                Payment Patterns (Last 30 Days)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentPatternsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-geo-alt me-2"></i>
                                Top Villages by Outstanding
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="villageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-table me-2"></i>
                                Class-wise Breakdown
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Count</th>
                                            <th>Amount</th>
                                            <th>% of Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="classTableBody">
                                        <!-- Data will be populated by JavaScript -->
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
                                <i class="bi bi-calendar-week me-2"></i>
                                Overdue Ranges
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Range</th>
                                            <th>Count</th>
                                            <th>Amount</th>
                                            <th>% of Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overdueTableBody">
                                        <!-- Data will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights & Recommendations -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb me-2"></i>
                                Insights & Recommendations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="insightsContainer">
                                <!-- Insights will be populated by JavaScript -->
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
        let analyticsData = <?php echo json_encode($analytics); ?>;

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            initializeCharts();
            populateTables();
            generateInsights();
        });

        // Sidebar functionality
        function initializeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

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

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
        }

        // Initialize charts
        function initializeCharts() {
            // Class distribution chart
            const classCtx = document.getElementById('classChart');
            if (classCtx && analyticsData.class_distribution) {
                const classData = analyticsData.class_distribution;
                new Chart(classCtx, {
                    type: 'bar',
                    data: {
                        labels: classData.map(item => item.class_name + ' ' + item.section),
                        datasets: [{
                            label: 'Outstanding Amount',
                            data: classData.map(item => item.total_amount),
                            backgroundColor: 'rgba(13, 110, 253, 0.8)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1
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
                                        return '₹' + value.toLocaleString();
                                    }
                                }
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

            // Overdue distribution chart
            const overdueCtx = document.getElementById('overdueChart');
            if (overdueCtx && analyticsData.overdue_trends) {
                const overdueData = analyticsData.overdue_trends;
                new Chart(overdueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: overdueData.map(item => item.overdue_range),
                        datasets: [{
                            data: overdueData.map(item => item.count),
                            backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1'],
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

            // Payment patterns chart
            const patternsCtx = document.getElementById('paymentPatternsChart');
            if (patternsCtx && analyticsData.payment_patterns) {
                const patternsData = analyticsData.payment_patterns;
                new Chart(patternsCtx, {
                    type: 'line',
                    data: {
                        labels: patternsData.map(item => item.hour + ':00'),
                        datasets: [{
                            label: 'Payments',
                            data: patternsData.map(item => item.payment_count),
                            borderColor: '#20c997',
                            backgroundColor: 'rgba(32, 201, 151, 0.1)',
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

            // Village chart
            const villageCtx = document.getElementById('villageChart');
            if (villageCtx && analyticsData.village_stats) {
                const villageData = analyticsData.village_stats;
                new Chart(villageCtx, {
                    type: 'horizontalBar',
                    data: {
                        labels: villageData.map(item => item.village.substring(0, 20) + (item.village.length > 20 ? '...' : '')),
                        datasets: [{
                            label: 'Outstanding Amount',
                            data: villageData.map(item => item.total_amount),
                            backgroundColor: 'rgba(255, 193, 7, 0.8)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
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
        }

        // Populate tables
        function populateTables() {
            // Class table
            const classTableBody = document.getElementById('classTableBody');
            if (classTableBody && analyticsData.class_distribution) {
                const totalAmount = analyticsData.class_distribution.reduce((sum, item) => sum + parseFloat(item.total_amount), 0);
                classTableBody.innerHTML = analyticsData.class_distribution.map(item => `
                    <tr>
                        <td>${item.class_name} ${item.section}</td>
                        <td>${item.fee_count}</td>
                        <td>₹${parseFloat(item.total_amount).toLocaleString()}</td>
                        <td>${((parseFloat(item.total_amount) / totalAmount) * 100).toFixed(1)}%</td>
                    </tr>
                `).join('');
            }

            // Overdue table
            const overdueTableBody = document.getElementById('overdueTableBody');
            if (overdueTableBody && analyticsData.overdue_trends) {
                const totalCount = analyticsData.overdue_trends.reduce((sum, item) => sum + parseInt(item.count), 0);
                overdueTableBody.innerHTML = analyticsData.overdue_trends.map(item => `
                    <tr>
                        <td>${item.overdue_range}</td>
                        <td>${item.count}</td>
                        <td>₹${parseFloat(item.amount).toLocaleString()}</td>
                        <td>${((parseInt(item.count) / totalCount) * 100).toFixed(1)}%</td>
                    </tr>
                `).join('');
            }
        }

        // Generate insights
        function generateInsights() {
            const insightsContainer = document.getElementById('insightsContainer');
            if (!insightsContainer) return;

            const insights = [];

            // Generate insights based on data
            if (analyticsData.overdue_count > analyticsData.total_outstanding * 0.5) {
                insights.push({
                    type: 'warning',
                    icon: 'bi-exclamation-triangle',
                    title: 'High Overdue Rate',
                    message: `${((analyticsData.overdue_count / analyticsData.total_outstanding) * 100).toFixed(1)}% of fees are overdue. Consider sending reminders.`
                });
            }

            if (analyticsData.class_distribution && analyticsData.class_distribution.length > 0) {
                const topClass = analyticsData.class_distribution.reduce((max, item) =>
                    parseFloat(item.total_amount) > parseFloat(max.total_amount) ? item : max
                );
                insights.push({
                    type: 'info',
                    icon: 'bi-info-circle',
                    title: 'Highest Outstanding Class',
                    message: `Class ${topClass.class_name} ${topClass.section} has the highest outstanding amount of ₹${parseFloat(topClass.total_amount).toLocaleString()}.`
                });
            }

            if (analyticsData.village_stats && analyticsData.village_stats.length > 0) {
                const topVillage = analyticsData.village_stats[0];
                insights.push({
                    type: 'info',
                    icon: 'bi-geo-alt',
                    title: 'Village Concentration',
                    message: `${topVillage.village} has ${topVillage.outstanding_count} outstanding fees worth ₹${parseFloat(topVillage.total_amount).toLocaleString()}.`
                });
            }

            insights.push({
                type: 'success',
                icon: 'bi-check-circle',
                title: 'Collection Rate',
                message: `Current collection rate is ${analyticsData.collection_rate}%. Keep up the good work!`
            });

            insightsContainer.innerHTML = insights.map(insight => `
                <div class="col-md-6 mb-3">
                    <div class="alert alert-${insight.type} d-flex align-items-start">
                        <i class="bi ${insight.icon} me-2 mt-1"></i>
                        <div>
                            <strong>${insight.title}</strong><br>
                            <small>${insight.message}</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Refresh analytics
        function refreshAnalytics() {
            location.reload();
        }
    </script>
</body>
</html>