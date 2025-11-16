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
        .receipt-copy {
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            page-break-after: always;
        }
        .receipt-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        .receipt-body {
            padding: 20px;
        }
        .receipt-footer {
            border-top: 1px solid #dee2e6;
            padding: 15px 20px;
        }
        .copy-label {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        @media print {
            .sidebar, .sidebar-overlay, header, .btn, .alert {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .receipt-container {
                box-shadow: none !important;
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
                        <h5 class="mb-0">Payment Receipt</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/admin/fees">Fees</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Receipt</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i>Print Receipt
                    </button>
                    <a href="/admin/fees/generate-receipt/<?php echo $payment->id; ?>" class="btn btn-success">
                        <i class="bi bi-download me-1"></i>Download PDF
                    </a>
                    <a href="/admin/fees/collect" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Collection
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
                <!-- School Copy -->
                <div class="receipt-copy position-relative">
                    <div class="copy-label">SCHOOL COPY</div>
                    <div class="receipt-header">
                        <h4 class="mb-1">SCHOOL MANAGEMENT SYSTEM</h4>
                        <h6 class="mb-0 text-muted">FEE RECEIPT</h6>
                    </div>
                    <div class="receipt-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Receipt No:</strong> <?php echo htmlspecialchars($payment->receipt_number); ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($payment->payment_date)); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Student Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Scholar No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->scholar_number); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Name:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->first_name . ' ' . ($payment->middle_name ? $payment->middle_name . ' ' : '') . $payment->last_name); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Class:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->class_name . ' ' . $payment->section); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Father:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->father_name ?: 'N/A'); ?></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Payment Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Fee Type:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->fee_type); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Amount Paid:</strong></div>
                            <div class="col-8">₹<?php echo number_format($payment->amount_paid, 2); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Payment Mode:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->getPaymentModeText()); ?></div>
                        </div>

                        <?php if ($payment->transaction_id): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Transaction ID:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->transaction_id); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->cheque_number): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Cheque No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->cheque_number); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->remarks): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Remarks:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->remarks); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="receipt-footer">
                        <div class="row">
                            <div class="col-6">
                                <strong>Cashier:</strong> ___________________
                            </div>
                            <div class="col-6 text-end">
                                <strong>Principal:</strong> ___________________
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Copy -->
                <div class="receipt-copy position-relative">
                    <div class="copy-label">STUDENT COPY</div>
                    <div class="receipt-header">
                        <h4 class="mb-1">SCHOOL MANAGEMENT SYSTEM</h4>
                        <h6 class="mb-0 text-muted">FEE RECEIPT</h6>
                    </div>
                    <div class="receipt-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Receipt No:</strong> <?php echo htmlspecialchars($payment->receipt_number); ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($payment->payment_date)); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Student Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Scholar No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->scholar_number); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Name:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->first_name . ' ' . ($payment->middle_name ? $payment->middle_name . ' ' : '') . $payment->last_name); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Class:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->class_name . ' ' . $payment->section); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Father:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->father_name ?: 'N/A'); ?></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Payment Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Fee Type:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->fee_type); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Amount Paid:</strong></div>
                            <div class="col-8">₹<?php echo number_format($payment->amount_paid, 2); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Payment Mode:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->getPaymentModeText()); ?></div>
                        </div>

                        <?php if ($payment->transaction_id): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Transaction ID:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->transaction_id); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->cheque_number): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Cheque No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->cheque_number); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->remarks): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Remarks:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->remarks); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="receipt-footer">
                        <div class="row">
                            <div class="col-6">
                                <strong>Cashier:</strong> ___________________
                            </div>
                            <div class="col-6 text-end">
                                <strong>Principal:</strong> ___________________
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accounts Copy -->
                <div class="receipt-copy position-relative">
                    <div class="copy-label">ACCOUNTS COPY</div>
                    <div class="receipt-header">
                        <h4 class="mb-1">SCHOOL MANAGEMENT SYSTEM</h4>
                        <h6 class="mb-0 text-muted">FEE RECEIPT</h6>
                    </div>
                    <div class="receipt-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Receipt No:</strong> <?php echo htmlspecialchars($payment->receipt_number); ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($payment->payment_date)); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Student Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Scholar No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->scholar_number); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Name:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->first_name . ' ' . ($payment->middle_name ? $payment->middle_name . ' ' : '') . $payment->last_name); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Class:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->class_name . ' ' . $payment->section); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Father:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->father_name ?: 'N/A'); ?></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Payment Details</h6>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Fee Type:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->fee_type); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Amount Paid:</strong></div>
                            <div class="col-8">₹<?php echo number_format($payment->amount_paid, 2); ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4"><strong>Payment Mode:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->getPaymentModeText()); ?></div>
                        </div>

                        <?php if ($payment->transaction_id): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Transaction ID:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->transaction_id); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->cheque_number): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Cheque No:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->cheque_number); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment->remarks): ?>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Remarks:</strong></div>
                            <div class="col-8"><?php echo htmlspecialchars($payment->remarks); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="receipt-footer">
                        <div class="row">
                            <div class="col-6">
                                <strong>Cashier:</strong> ___________________
                            </div>
                            <div class="col-6 text-end">
                                <strong>Principal:</strong> ___________________
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
        });
    </script>
</body>
</html>