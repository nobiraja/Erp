<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Expense Management'); ?></title>
    <meta name="description" content="Manage school expenses - Cashier Interface">

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
        .expense-card {
            transition: transform 0.2s;
        }
        .expense-card:hover {
            transform: translateY(-2px);
        }
        .amount-display {
            font-size: 1.25rem;
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
                    <a class="nav-link active" href="/cashier/expenses">
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
                        <h5 class="mb-0">Expense Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/cashier/fees">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Expenses</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="showAddExpenseModal()" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Add Expense
                    </button>
                    <a href="/cashier/fees" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Expense Management Content -->
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

            <!-- Today's Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-cash text-danger fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($todayStats['total_amount'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">Today's Expenses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt text-info fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $todayStats['total_expenses'] ?? 0; ?></h4>
                            <p class="text-muted mb-0">Transactions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-month text-warning fs-1 mb-2"></i>
                            <h4 class="mb-1">₹<?php echo number_format($monthStats['total_amount'] ?? 0); ?></h4>
                            <p class="text-muted mb-0">This Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-clock text-secondary fs-1 mb-2"></i>
                            <h4 class="mb-1"><?php echo $pendingCount; ?></h4>
                            <p class="text-muted mb-0">Pending Approval</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>
                        Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="<?php echo htmlspecialchars($filters['start_date'] ?? date('Y-m-01')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?php echo htmlspecialchars($filters['end_date'] ?? date('Y-m-t')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach (ExpenseModel::getCategories() as $key => $value): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($filters['category'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                            <a href="/cashier/expenses" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        Expenses (<?php echo count($expenses); ?>)
                    </h6>
                    <div class="d-flex gap-2">
                        <button onclick="exportExpenses('csv')" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($expenses)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt No</th>
                                        <th>Category</th>
                                        <th>Reason</th>
                                        <th>Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenses as $expense): ?>
                                        <tr>
                                            <td><?php echo date('d-m-Y', strtotime($expense->payment_date)); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($expense->receipt_number); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($expense->getCategoryText()); ?></td>
                                            <td><?php echo htmlspecialchars(substr($expense->reason, 0, 50)) . (strlen($expense->reason) > 50 ? '...' : ''); ?></td>
                                            <td class="amount-display">₹<?php echo number_format($expense->amount, 2); ?></td>
                                            <td><?php echo htmlspecialchars($expense->getPaymentModeText()); ?></td>
                                            <td>
                                                <?php if ($expense->isApproved()): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button onclick="viewExpense(<?php echo $expense->id; ?>)" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if (!$expense->isApproved()): ?>
                                                        <button onclick="editExpense(<?php echo $expense->id; ?>)" class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button onclick="deleteExpense(<?php echo $expense->id; ?>, '<?php echo htmlspecialchars($expense->receipt_number); ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-receipt-x text-muted fs-1 mb-3"></i>
                            <h6 class="text-muted">No expenses found</h6>
                            <p class="text-muted">Add your first expense to get started</p>
                            <button onclick="showAddExpenseModal()" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Add Expense
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="expenseForm">
                    <div class="modal-body">
                        <div id="formMessages"></div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="expense_category" class="form-label">Category *</label>
                                <select class="form-select" id="expense_category" name="expense_category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach (ExpenseModel::getCategories() as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_mode" class="form-label">Payment Mode *</label>
                                <select class="form-select" id="payment_mode" name="payment_mode" required onchange="toggleExpensePaymentFields()">
                                    <option value="">Select Payment Mode</option>
                                    <?php foreach (ExpenseModel::getPaymentModes() as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="expenseTransactionField" style="display: none;">
                                <label for="transaction_id" class="form-label">Transaction ID *</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                            </div>
                            <div class="col-md-6" id="expenseChequeField" style="display: none;">
                                <label for="cheque_number" class="form-label">Cheque Number *</label>
                                <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                            </div>
                            <div class="col-12">
                                <label for="reason" class="form-label">Reason/Details *</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Describe the expense purpose"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="submitExpenseForm()">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>
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

        // Show add expense modal
        function showAddExpenseModal() {
            const modal = new bootstrap.Modal(document.getElementById('addExpenseModal'));
            modal.show();
        }

        // Toggle payment fields based on payment mode
        function toggleExpensePaymentFields() {
            const paymentMode = document.getElementById('payment_mode').value;
            const transactionField = document.getElementById('expenseTransactionField');
            const chequeField = document.getElementById('expenseChequeField');
            const transactionInput = document.getElementById('transaction_id');
            const chequeInput = document.getElementById('cheque_number');

            // Reset required attributes
            transactionInput.required = false;
            chequeInput.required = false;

            // Hide all fields first
            transactionField.style.display = 'none';
            chequeField.style.display = 'none';

            // Show relevant field
            if (paymentMode === 'online') {
                transactionField.style.display = 'block';
                transactionInput.required = true;
            } else if (paymentMode === 'cheque') {
                chequeField.style.display = 'block';
                chequeInput.required = true;
            }
        }

        // View expense details
        function viewExpense(expenseId) {
            // This would open a view modal - simplified for now
            alert('View expense functionality would be implemented here');
        }

        // Edit expense
        function editExpense(expenseId) {
            // This would open an edit modal - simplified for now
            alert('Edit expense functionality would be implemented here');
        }

        // Delete expense
        function deleteExpense(expenseId, receiptNumber) {
            if (confirm(`Are you sure you want to delete expense "${receiptNumber}"? This action cannot be undone.`)) {
                fetch(`/cashier/expenses/delete/${expenseId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Expense deleted successfully');
                        location.reload();
                    } else {
                        alert('Failed to delete expense: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error deleting expense');
                    console.error('Delete error:', error);
                });
            }
        }

        // Submit expense form via AJAX
        function submitExpenseForm() {
            const form = document.getElementById('expenseForm');
            const formData = new FormData(form);
            const messagesDiv = document.getElementById('formMessages');

            // Clear previous messages
            messagesDiv.innerHTML = '';

            // Basic validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="button"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Adding...';
            submitBtn.disabled = true;

            // Send AJAX request
            fetch('/cashier/expenses/add', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    messagesDiv.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';

                    // Reset form
                    form.reset();
                    document.getElementById('payment_date').value = '<?php echo date('Y-m-d'); ?>';

                    // Hide conditional fields
                    document.getElementById('expenseTransactionField').style.display = 'none';
                    document.getElementById('expenseChequeField').style.display = 'none';

                    // Close modal after delay
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addExpenseModal'));
                        modal.hide();
                        // Reload page to update stats
                        location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    messagesDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        (data.message || 'Failed to add expense') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messagesDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    'An error occurred while adding the expense<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // Export expenses
        function exportExpenses(format) {
            const url = '/cashier/expenses/export?format=' + format +
                        '&start_date=' + document.getElementById('start_date').value +
                        '&end_date=' + document.getElementById('end_date').value +
                        '&category=' + document.getElementById('category').value;

            if (format === 'csv') {
                window.location.href = url;
            } else {
                window.open(url, '_blank');
            }
        }
    </script>
</body>
</html>