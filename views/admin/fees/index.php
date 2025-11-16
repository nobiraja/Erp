<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Fees Management'); ?></title>
    <meta name="description" content="Manage school fees, payments, and financial reports">

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
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                    <a class="nav-link active" href="/admin/fees">
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
                    <a class="nav-link" href="/admin/reports">
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
                        <h5 class="mb-0">Fees Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Fees</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="/admin/fees/collect" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Collect Fee
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/fees/structure">
                                <i class="bi bi-gear me-2"></i>Fee Structure
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/fees/outstanding">
                                <i class="bi bi-exclamation-triangle me-2"></i>Outstanding Fees
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/fees/reports">
                                <i class="bi bi-graph-up me-2"></i>Reports
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Fees Content -->
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-cash text-primary fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($stats['total_fees'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Total Fees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($stats['collected_fees'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Collected</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($stats['pending_fees'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-percent text-info fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo number_format($stats['collection_percentage'] ?? 0, 1); ?>%</h4>
                            <p class="text-muted mb-0">Collection Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Fee Collection Overview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="collectionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Payment Methods
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Payments
                    </h6>
                    <a href="/admin/fees/reports?type=collection" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPayments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt No</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Collected By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td><?php echo date('d-m-Y', strtotime($payment->payment_date)); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($payment->receipt_number); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment->getStudentName()); ?></td>
                                            <td><?php echo htmlspecialchars($payment->class_name . ' ' . $payment->section); ?></td>
                                            <td>₹<?php echo number_format($payment->amount_paid, 2); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($payment->getPaymentModeText()); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment->collected_by_name); ?></td>
                                            <td>
                                                <a href="/admin/fees/receipt/<?php echo $payment->id; ?>" class="btn btn-sm btn-outline-info" title="View Receipt">
                                                    <i class="bi bi-receipt"></i>
                                                </a>
                                                <a href="/admin/fees/generate-receipt/<?php echo $payment->id; ?>" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-receipt-x text-muted fs-1 mb-3"></i>
                            <h6 class="text-muted">No recent payments found</h6>
                            <a href="/admin/fees/collect" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Collect First Payment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-lightning me-2"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="/admin/fees/collect" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        <div>Collect Fee</div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="/admin/fees/structure" class="btn btn-primary w-100">
                                        <i class="bi bi-gear me-2"></i>
                                        <div>Fee Structure</div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="/admin/fees/outstanding" class="btn btn-warning w-100">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <div>Outstanding Fees</div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="/admin/fees/reports" class="btn btn-info w-100">
                                        <i class="bi bi-graph-up me-2"></i>
                                        <div>Reports</div>
                                    </a>
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
            // Collection Overview Chart
            const collectionCtx = document.getElementById('collectionChart').getContext('2d');
            new Chart(collectionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Collected', 'Pending'],
                    datasets: [{
                        data: [
                            <?php echo $stats['collected_fees'] ?? 0; ?>,
                            <?php echo $stats['pending_fees'] ?? 0; ?>
                        ],
                        backgroundColor: ['#28a745', '#ffc107'],
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

            // Payment Methods Chart (placeholder data - would be populated from actual data)
            const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            new Chart(paymentMethodCtx, {
                type: 'bar',
                data: {
                    labels: ['Cash', 'Online', 'Cheque', 'UPI'],
                    datasets: [{
                        label: 'Payments',
                        data: [12, 8, 3, 5], // Placeholder data
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                        borderWidth: 0
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
    </script>
</body>
</html>