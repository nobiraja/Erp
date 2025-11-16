<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Outstanding Fees'); ?></title>
    <meta name="description" content="View and manage outstanding fees - Cashier Interface">

    <!-- Bootstrap 5 CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">

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
        .overdue-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .amount-cell {
            font-weight: bold;
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
                    <a class="nav-link active" href="/cashier/fees/outstanding">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Outstanding Fees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cashier/fees/reports">
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
                        <h5 class="mb-0">Outstanding Fees</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Outstanding</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="sendReminders()" class="btn btn-warning">
                        <i class="bi bi-envelope me-1"></i>Send Reminders
                    </button>
                    <a href="/cashier/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Outstanding Fees Content -->
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

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $summaryStats['total_outstanding'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Total Outstanding</p>
                            <small class="text-muted">Pending fees</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-cash text-danger fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($summaryStats['total_amount'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Total Amount</p>
                            <small class="text-muted">Outstanding value</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-x text-danger fs-1 mb-2 overdue-badge"></i>
                            <h4 class="mb-1"><?php echo $summaryStats['overdue_count'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Overdue</p>
                            <small class="text-muted"><?php echo round(($summaryStats['overdue_count'] ?? 0) / max(1, $summaryStats['total_outstanding'] ?? 1) * 100, 1); ?>% of total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-people text-info fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $summaryStats['unique_students'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Students</p>
                            <small class="text-muted">Affected students</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Outstanding Fees by Class
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="classOutstandingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Overdue vs Pending
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="overdueStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend Analysis -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Outstanding Fees Trend (Last 6 Months)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>
                        Advanced Filters
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="bi bi-chevron-down me-1"></i>Toggle Advanced
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-select" id="class_id" name="class_id">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="village" class="form-label">Village</label>
                            <select class="form-select" id="village" name="village">
                                <option value="">All Villages</option>
                                <?php foreach ($villages as $village): ?>
                                    <option value="<?php echo htmlspecialchars($village['village']); ?>" <?php echo ($filters['village'] ?? '') === $village['village'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($village['village']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="">All Outstanding</option>
                                <option value="overdue" <?php echo ($filters['status_filter'] ?? '') === 'overdue' ? 'selected' : ''; ?>>Overdue Only</option>
                                <option value="due_soon" <?php echo ($filters['status_filter'] ?? '') === 'due_soon' ? 'selected' : ''; ?>>Due Within 7 Days</option>
                                <option value="due_month" <?php echo ($filters['status_filter'] ?? '') === 'due_month' ? 'selected' : ''; ?>>Due This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                            <a href="/cashier/fees/outstanding" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </a>
                            <button type="button" class="btn btn-outline-success" onclick="exportData()">
                                <i class="bi bi-download me-1"></i>Export
                            </button>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div class="col-12 collapse" id="advancedFilters">
                            <div class="row g-3 border-top pt-3">
                                <div class="col-md-3">
                                    <label for="min_amount" class="form-label">Min Amount</label>
                                    <input type="number" class="form-control" id="min_amount" name="min_amount" value="<?php echo $filters['min_amount'] ?? ''; ?>" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label for="max_amount" class="form-label">Max Amount</label>
                                    <input type="number" class="form-control" id="max_amount" name="max_amount" value="<?php echo $filters['max_amount'] ?? ''; ?>" placeholder="10000">
                                </div>
                                <div class="col-md-3">
                                    <label for="due_date_from" class="form-label">Due Date From</label>
                                    <input type="date" class="form-control" id="due_date_from" name="due_date_from" value="<?php echo $filters['due_date_from'] ?? ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="due_date_to" class="form-label">Due Date To</label>
                                    <input type="date" class="form-control" id="due_date_to" name="due_date_to" value="<?php echo $filters['due_date_to'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="search_student" class="form-label">Search Student</label>
                                    <input type="text" class="form-control" id="search_student" name="search_student" value="<?php echo $filters['search_student'] ?? ''; ?>" placeholder="Student name or scholar number">
                                </div>
                                <div class="col-md-4">
                                    <label for="fee_type" class="form-label">Fee Type</label>
                                    <select class="form-select" id="fee_type" name="fee_type">
                                        <option value="">All Fee Types</option>
                                        <option value="tuition" <?php echo ($filters['fee_type'] ?? '') === 'tuition' ? 'selected' : ''; ?>>Tuition Fee</option>
                                        <option value="exam" <?php echo ($filters['fee_type'] ?? '') === 'exam' ? 'selected' : ''; ?>>Exam Fee</option>
                                        <option value="transport" <?php echo ($filters['fee_type'] ?? '') === 'transport' ? 'selected' : ''; ?>>Transport Fee</option>
                                        <option value="other" <?php echo ($filters['fee_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="auto_refresh" name="auto_refresh" <?php echo ($filters['auto_refresh'] ?? '') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="auto_refresh">
                                            Auto-refresh every 5 min
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Operations -->
            <div class="card mb-4" id="bulkOperations" style="display: none;">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-check-square me-2"></i>
                        Bulk Operations (<span id="selectedCount">0</span> selected)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Action</label>
                            <select class="form-select" id="bulkAction">
                                <option value="">Select Action</option>
                                <option value="send_reminders">Send Reminders</option>
                                <option value="mark_urgent">Mark as Urgent</option>
                                <option value="extend_due_date">Extend Due Date</option>
                                <option value="apply_discount">Apply Discount</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="extendDateField" style="display: none;">
                            <label for="extend_date" class="form-label">New Due Date</label>
                            <input type="date" class="form-control" id="extend_date">
                        </div>
                        <div class="col-md-3" id="discountField" style="display: none;">
                            <label for="discount_amount" class="form-label">Discount Amount (%)</label>
                            <input type="number" class="form-control" id="discount_amount" min="0" max="100">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success me-2" onclick="executeBulkAction()">
                                <i class="bi bi-play me-1"></i>Execute
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                <i class="bi bi-x-circle me-1"></i>Clear Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outstanding Fees Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Outstanding Fees (<span id="totalRecords"><?php echo count($outstandingFees); ?></span>)
                    </h6>
                    <div class="d-flex gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($outstandingFees)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="outstandingTable">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="headerCheckbox" onchange="toggleSelectAll()">
                                        </th>
                                        <th>Student Details</th>
                                        <th>Class</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($outstandingFees as $fee): ?>
                                        <tr data-fee-id="<?php echo $fee->id; ?>" data-student-id="<?php echo $fee->student_id; ?>">
                                            <td>
                                                <input type="checkbox" class="form-check-input row-checkbox" value="<?php echo $fee->id; ?>" onchange="updateSelection()">
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($fee->getStudentName()); ?></div>
                                                <small class="text-muted">Scholar: <?php echo htmlspecialchars($fee->scholar_number); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($fee->class_name . ' ' . $fee->section); ?></td>
                                            <td><?php echo htmlspecialchars($fee->fee_type); ?></td>
                                            <td class="amount-cell">₹<?php echo number_format($fee->amount, 2); ?></td>
                                            <td>
                                                <?php echo date('d-m-Y', strtotime($fee->due_date)); ?>
                                                <?php if ($fee->isOverdue()): ?>
                                                    <br><small class="text-danger"><?php echo $fee->days_overdue; ?> days overdue</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee->isOverdue()): ?>
                                                    <span class="badge bg-danger overdue-badge">Overdue</span>
                                                <?php elseif ($fee->days_overdue > -7): ?>
                                                    <span class="badge bg-warning">Due Soon</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($fee->mobile): ?>
                                                    <i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($fee->mobile); ?><br>
                                                <?php endif; ?>
                                                <?php if ($fee->village_address): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars($fee->village_address); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/cashier/fees/collect?student_id=<?php echo $fee->student_id; ?>" class="btn btn-sm btn-outline-success" title="Collect Payment">
                                                        <i class="bi bi-cash"></i>
                                                    </a>
                                                    <button onclick="sendReminder(<?php echo $fee->id; ?>, '<?php echo htmlspecialchars($fee->getStudentName()); ?>')" class="btn btn-sm btn-outline-primary" title="Send Reminder">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                    <button onclick="viewDetails(<?php echo $fee->id; ?>)" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Outstanding fees pagination" class="mt-3">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Pagination will be generated by JavaScript -->
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3"></i>
                            <h6 class="text-muted">No outstanding fees found</h6>
                            <p class="text-muted">All fees are paid up to date!</p>
                            <a href="/cashier/fees" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
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
        let selectedFees = [];
        let currentPage = 1;
        let autoRefreshInterval;

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            initializeCharts();
            initializeFilters();
            initializeBulkOperations();
            initializeAutoRefresh();
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
            // Class outstanding chart
            const classChartCtx = document.getElementById('classOutstandingChart');
            if (classChartCtx) {
                new Chart(classChartCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Class 1A', 'Class 1B', 'Class 2A', 'Class 2B', 'Class 3A'],
                        datasets: [{
                            label: 'Outstanding Amount',
                            data: [15000, 22000, 18000, 25000, 12000],
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: 'rgba(220, 53, 69, 1)',
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

            // Overdue status chart
            const statusChartCtx = document.getElementById('overdueStatusChart');
            if (statusChartCtx) {
                new Chart(statusChartCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Overdue', 'Due Soon', 'Pending'],
                        datasets: [{
                            data: [35, 25, 40],
                            backgroundColor: ['#dc3545', '#ffc107', '#6c757d'],
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

            // Trend chart
            const trendChartCtx = document.getElementById('trendChart');
            if (trendChartCtx) {
                new Chart(trendChartCtx, {
                    type: 'line',
                    data: {
                        labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                        datasets: [{
                            label: 'Outstanding Amount',
                            data: [45000, 52000, 48000, 55000, 42000, 38000],
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

        // Initialize filters
        function initializeFilters() {
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    applyFilters();
                });
            }

            // Auto-submit on filter change
            document.querySelectorAll('select[name]').forEach(select => {
                select.addEventListener('change', function() {
                    if (this.name !== 'auto_refresh') {
                        applyFilters();
                    }
                });
            });
        }

        // Apply filters via AJAX
        function applyFilters() {
            const formData = new FormData(document.getElementById('filterForm'));
            const params = new URLSearchParams(formData);

            // Show loading
            showLoading();

            fetch('/cashier/fees/outstanding?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                updateTable(data.html);
                updateSummaryStats(data.stats);
                updateCharts(data.charts);
                hideLoading();
            })
            .catch(error => {
                console.error('Filter error:', error);
                hideLoading();
                alert('Error applying filters. Please try again.');
            });
        }

        // Initialize bulk operations
        function initializeBulkOperations() {
            const bulkAction = document.getElementById('bulkAction');
            if (bulkAction) {
                bulkAction.addEventListener('change', function() {
                    toggleBulkFields(this.value);
                });
            }
        }

        // Toggle bulk operation fields
        function toggleBulkFields(action) {
            document.getElementById('extendDateField').style.display = action === 'extend_due_date' ? 'block' : 'none';
            document.getElementById('discountField').style.display = action === 'apply_discount' ? 'block' : 'none';
        }

        // Execute bulk action
        function executeBulkAction() {
            const action = document.getElementById('bulkAction').value;
            if (!action) {
                alert('Please select an action.');
                return;
            }

            if (selectedFees.length === 0) {
                alert('Please select fees to perform bulk operation.');
                return;
            }

            let confirmMessage = `Are you sure you want to ${action.replace('_', ' ')} for ${selectedFees.length} selected fees?`;

            if (!confirm(confirmMessage)) {
                return;
            }

            const data = {
                action: action,
                fee_ids: selectedFees
            };

            // Add action-specific data
            if (action === 'extend_due_date') {
                data.new_due_date = document.getElementById('extend_date').value;
                if (!data.new_due_date) {
                    alert('Please select a new due date.');
                    return;
                }
            } else if (action === 'apply_discount') {
                data.discount_percentage = document.getElementById('discount_amount').value;
                if (!data.discount_percentage || data.discount_percentage <= 0) {
                    alert('Please enter a valid discount percentage.');
                    return;
                }
            }

            fetch('/cashier/fees/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    refreshData();
                    clearSelection();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Bulk action error:', error);
                alert('Error executing bulk action. Please try again.');
            });
        }

        // Selection management
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateSelection();
        }

        function updateSelection() {
            selectedFees = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
                selectedFees.push(parseInt(checkbox.value));
            });

            const bulkOps = document.getElementById('bulkOperations');
            const selectedCount = document.getElementById('selectedCount');

            if (selectedFees.length > 0) {
                bulkOps.style.display = 'block';
                selectedCount.textContent = selectedFees.length;
            } else {
                bulkOps.style.display = 'none';
            }
        }

        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateSelection();
        }

        // Send reminder to individual student
        function sendReminder(feeId, studentName) {
            if (confirm(`Send payment reminder to ${studentName}?`)) {
                fetch('/cashier/fees/send-reminder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ fee_id: feeId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminder sent successfully!');
                    } else {
                        alert('Failed to send reminder: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error sending reminder. Please try again.');
                    console.error('Send reminder error:', error);
                });
            }
        }

        // Send reminders to selected students
        function sendReminders() {
            if (selectedFees.length === 0) {
                alert('Please select fees to send reminders.');
                return;
            }

            if (confirm(`Send payment reminders to ${selectedFees.length} selected students?`)) {
                fetch('/cashier/fees/send-reminders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ fee_ids: selectedFees })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Reminders sent successfully to ${selectedFees.length} students!`);
                    } else {
                        alert('Failed to send reminders: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error sending reminders. Please try again.');
                    console.error('Send reminders error:', error);
                });
            }
        }

        // View fee details
        function viewDetails(feeId) {
            // Open modal or redirect to details page
            window.location.href = `/cashier/fees/view/${feeId}`;
        }

        // Export data
        function exportData() {
            const formData = new FormData(document.getElementById('filterForm'));
            const params = new URLSearchParams(formData);
            window.location.href = '/cashier/fees/export-outstanding?' + params.toString();
        }

        // Refresh data
        function refreshData() {
            applyFilters();
        }

        // Auto refresh functionality
        function initializeAutoRefresh() {
            const autoRefreshCheckbox = document.getElementById('auto_refresh');
            if (autoRefreshCheckbox && autoRefreshCheckbox.checked) {
                autoRefreshInterval = setInterval(refreshData, 300000); // 5 minutes
            }

            autoRefreshCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    autoRefreshInterval = setInterval(refreshData, 300000);
                } else {
                    clearInterval(autoRefreshInterval);
                }
            });
        }

        // Utility functions
        function showLoading() {
            // Add loading overlay
            const loading = document.createElement('div');
            loading.id = 'loadingOverlay';
            loading.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="height: 100vh; background: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100%; z-index: 9999;">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            document.body.appendChild(loading);
        }

        function hideLoading() {
            const loading = document.getElementById('loadingOverlay');
            if (loading) {
                loading.remove();
            }
        }

        function updateTable(html) {
            const tableContainer = document.querySelector('#outstandingTable tbody');
            if (tableContainer && html) {
                tableContainer.innerHTML = html;
            }
        }

        function updateSummaryStats(stats) {
            if (stats) {
                document.querySelector('.stats-card.border-warning .fs-1').textContent = stats.total_outstanding || 0;
                document.querySelector('.stats-card.border-danger .fs-1').textContent = '₹' + (stats.total_amount || 0).toLocaleString();
                document.querySelector('.stats-card.border-danger + .stats-card .fs-1').textContent = stats.overdue_count || 0;
                document.querySelector('.stats-card.border-info .fs-1').textContent = stats.unique_students || 0;
                document.getElementById('totalRecords').textContent = stats.total_outstanding || 0;
            }
        }

        function updateCharts(chartData) {
            // Update charts with new data if provided
            if (chartData) {
                // Implementation for updating charts dynamically
                console.log('Chart data updated:', chartData);
            }
        }
    </script>
</body>
</html>