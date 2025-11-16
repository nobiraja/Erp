<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Payment Receipt'); ?></title>
    <meta name="description" content="Payment receipt for fee collection">

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
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .receipt-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .receipt-section {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .receipt-section h6 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .amount-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            padding: 1rem;
            border: 2px solid #28a745;
            border-radius: 0.375rem;
            margin: 1rem 0;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }
        .copy-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
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
                        <h5 class="mb-0">Payment Receipt</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Receipt</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                    <a href="/cashier/fees/generate-receipt/<?php echo $payment->id; ?>" class="btn btn-success">
                        <i class="bi bi-download me-1"></i>Download PDF
                    </a>
                    <a href="/cashier/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Receipt Content -->
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

            <div class="receipt-container">
                <!-- Receipt Header -->
                <div class="receipt-header">
                    <h2 class="mb-2">SCHOOL MANAGEMENT SYSTEM</h2>
                    <h4 class="mb-2">FEE RECEIPT</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Receipt No:</strong> <?php echo htmlspecialchars($payment->receipt_number); ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($payment->payment_date)); ?>
                        </div>
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="amount-display">
                    Amount Paid: ₹<?php echo number_format($payment->amount_paid, 2); ?>
                </div>

                <!-- Receipt Details -->
                <div class="receipt-details">
                    <!-- Student Details -->
                    <div class="receipt-section">
                        <h6><i class="bi bi-person me-2"></i>Student Details</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <strong>Scholar No:</strong> <?php echo htmlspecialchars($payment->scholar_number); ?>
                            </div>
                            <div class="col-12">
                                <strong>Name:</strong> <?php echo htmlspecialchars($payment->first_name . ' ' . ($payment->middle_name ? $payment->middle_name . ' ' : '') . $payment->last_name); ?>
                            </div>
                            <div class="col-12">
                                <strong>Class:</strong> <?php echo htmlspecialchars($payment->class_name . ' ' . $payment->section); ?>
                            </div>
                            <div class="col-12">
                                <strong>Father:</strong> <?php echo htmlspecialchars($payment->father_name ?: 'N/A'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="receipt-section">
                        <h6><i class="bi bi-credit-card me-2"></i>Payment Details</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <strong>Fee Type:</strong> <?php echo htmlspecialchars($payment->fee_type); ?>
                            </div>
                            <div class="col-12">
                                <strong>Payment Mode:</strong> <?php echo htmlspecialchars($payment->getPaymentModeText()); ?>
                            </div>
                            <?php if ($payment->transaction_id): ?>
                                <div class="col-12">
                                    <strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment->transaction_id); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($payment->cheque_number): ?>
                                <div class="col-12">
                                    <strong>Cheque No:</strong> <?php echo htmlspecialchars($payment->cheque_number); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($payment->remarks): ?>
                                <div class="col-12">
                                    <strong>Remarks:</strong> <?php echo htmlspecialchars($payment->remarks); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Triple Receipt Preview -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            PDF Preview (3 Copies per Page)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This receipt will be printed as three copies on a single A4 page:
                            <strong>School Copy</strong>, <strong>Student Copy</strong>, and <strong>Accounts Copy</strong>.
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="border p-3 position-relative" style="min-height: 200px;">
                                    <div class="copy-indicator">SCHOOL COPY</div>
                                    <div class="text-center mb-2">
                                        <strong>SCHOOL MANAGEMENT SYSTEM</strong><br>
                                        <small>FEE RECEIPT</small>
                                    </div>
                                    <small>
                                        Receipt: <?php echo htmlspecialchars($payment->receipt_number); ?><br>
                                        Student: <?php echo htmlspecialchars($payment->first_name . ' ' . $payment->last_name); ?><br>
                                        Amount: ₹<?php echo number_format($payment->amount_paid, 2); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border p-3 position-relative" style="min-height: 200px;">
                                    <div class="copy-indicator" style="background: #28a745;">STUDENT COPY</div>
                                    <div class="text-center mb-2">
                                        <strong>SCHOOL MANAGEMENT SYSTEM</strong><br>
                                        <small>FEE RECEIPT</small>
                                    </div>
                                    <small>
                                        Receipt: <?php echo htmlspecialchars($payment->receipt_number); ?><br>
                                        Student: <?php echo htmlspecialchars($payment->first_name . ' ' . $payment->last_name); ?><br>
                                        Amount: ₹<?php echo number_format($payment->amount_paid, 2); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border p-3 position-relative" style="min-height: 200px;">
                                    <div class="copy-indicator" style="background: #dc3545;">ACCOUNTS COPY</div>
                                    <div class="text-center mb-2">
                                        <strong>SCHOOL MANAGEMENT SYSTEM</strong><br>
                                        <small>FEE RECEIPT</small>
                                    </div>
                                    <small>
                                        Receipt: <?php echo htmlspecialchars($payment->receipt_number); ?><br>
                                        Student: <?php echo htmlspecialchars($payment->first_name . ' ' . $payment->last_name); ?><br>
                                        Amount: ₹<?php echo number_format($payment->amount_paid, 2); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Footer -->
                <div class="receipt-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Cashier Signature:</strong>
                            <div class="mt-3" style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                        </div>
                        <div class="col-md-6">
                            <strong>Principal Signature:</strong>
                            <div class="mt-3" style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-muted">
                        <small>This is a computer-generated receipt and does not require a signature.</small>
                    </p>
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
        });
    </script>
</body>
</html>