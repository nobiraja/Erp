<?php
/**
 * Cashier Expenses Controller
 * Handles expense management operations for cashiers
 */

class CashierExpensesController extends BaseController {

    /**
     * Display expenses dashboard
     */
    public function index() {
        try {
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $category = $this->input('category');
            $userId = $this->getCurrentUserId();

            // Get expenses
            $expenses = ExpenseModel::getByDateRange($startDate, $endDate, $category);

            // Get today's stats
            $todayStats = $this->getTodayStats($userId);

            // Get this month's stats
            $monthStats = $this->getMonthStats($userId);

            // Get pending count
            $pendingCount = $this->getPendingCount($userId);

            $data = [
                'title' => 'Expense Management',
                'expenses' => $expenses,
                'todayStats' => $todayStats,
                'monthStats' => $monthStats,
                'pendingCount' => $pendingCount,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'category' => $category
                ]
            ];

            echo $this->view('cashier.expenses.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading expenses: ' . $e->getMessage());
            echo $this->view('cashier.expenses.index', [
                'title' => 'Expense Management',
                'expenses' => [],
                'todayStats' => ['total_expenses' => 0, 'total_amount' => 0],
                'monthStats' => ['total_amount' => 0],
                'pendingCount' => 0,
                'filters' => []
            ]);
        }
    }

    /**
     * Add new expense
     */
    public function add() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/cashier/expenses');
        }

        $rules = [
            'expense_category' => 'required|in:' . implode(',', array_keys(ExpenseModel::getCategories())),
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'reason' => 'required|max:500',
            'payment_mode' => 'required|in:' . implode(',', array_keys(ExpenseModel::getPaymentModes()))
        ];

        // Add conditional validation for payment modes
        $paymentMode = $this->input('payment_mode');
        if ($paymentMode === 'online') {
            $rules['transaction_id'] = 'required|max:100';
        } elseif ($paymentMode === 'cheque') {
            $rules['cheque_number'] = 'required|max:50';
        }

        $validated = $this->validate($rules);

        if (!$validated) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Validation failed: ' . implode(', ', $this->getValidationErrors())]);
            } else {
                $this->flash('error', 'Validation failed');
                $this->flash('old_input', $this->all());
                $this->flash('validation_errors', $this->getValidationErrors());
                $this->redirect('/cashier/expenses');
            }
        }

        try {
            // Create expense
            $expenseData = [
                'expense_category' => $validated['expense_category'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'reason' => $validated['reason'],
                'payment_mode' => $validated['payment_mode'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'cheque_number' => $validated['cheque_number'] ?? null,
                'created_by' => $this->getCurrentUserId(),
                'approved_by' => null // Pending approval
            ];

            $expense = ExpenseModel::create($expenseData);

            if ($expense) {
                $message = 'Expense recorded successfully. Receipt Number: ' . $expense->receipt_number . ' (Pending Approval)';
                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => $message, 'receipt_number' => $expense->receipt_number]);
                } else {
                    $this->flash('success', $message);
                    $this->redirect('/cashier/expenses');
                }
            } else {
                $error = 'Failed to record expense';
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => $error]);
                } else {
                    $this->flash('error', $error);
                    $this->redirect('/cashier/expenses');
                }
            }

        } catch (Exception $e) {
            $error = 'Error recording expense: ' . $e->getMessage();
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $error]);
            } else {
                $this->flash('error', $error);
                $this->flash('old_input', $this->all());
                $this->redirect('/cashier/expenses');
            }
        }
    }

    /**
     * Delete expense
     */
    public function delete($expenseId) {
        if (!$this->isAjax()) {
            $this->redirect('/cashier/expenses');
        }

        try {
            $expense = ExpenseModel::find($expenseId);
            if (!$expense) {
                $this->json(['success' => false, 'message' => 'Expense record not found']);
            }

            // Check if user owns this expense and it's not approved
            if ($expense->created_by != $this->getCurrentUserId()) {
                $this->json(['success' => false, 'message' => 'You can only delete your own expenses']);
            }

            if ($expense->isApproved()) {
                $this->json(['success' => false, 'message' => 'Cannot delete approved expenses']);
            }

            if ($expense->delete()) {
                $this->json(['success' => true, 'message' => 'Expense deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete expense']);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error deleting expense: ' . $e->getMessage()]);
        }
    }

    /**
     * Export expenses
     */
    public function export() {
        try {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $category = $this->input('category');
            $format = $this->input('format', 'csv');
            $userId = $this->getCurrentUserId();

            // Get expenses for this user
            $expenses = ExpenseModel::getByDateRange($startDate, $endDate, $category);

            // Filter by user
            $userExpenses = array_filter($expenses, function($expense) use ($userId) {
                return $expense->created_by == $userId;
            });

            $filename = 'My_Expenses_' . date('Y-m-d');

            if ($format === 'csv') {
                $this->exportToCSV($userExpenses, $filename . '.csv');
            } else {
                $this->exportToPDF($userExpenses, $filename . '.pdf');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Export failed: ' . $e->getMessage());
            $this->redirect('/cashier/expenses');
        }
    }

    /**
     * Get today's stats for cashier
     */
    private function getTodayStats($userId) {
        $query = "SELECT COUNT(*) as total_expenses, SUM(amount) as total_amount
                  FROM expenses
                  WHERE created_by = ? AND DATE(payment_date) = CURDATE()";

        $result = $this->db->fetch($query, [$userId]);

        return [
            'total_expenses' => $result['total_expenses'] ?? 0,
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }

    /**
     * Get month's stats for cashier
     */
    private function getMonthStats($userId) {
        $query = "SELECT SUM(amount) as total_amount
                  FROM expenses
                  WHERE created_by = ? AND MONTH(payment_date) = MONTH(CURDATE())
                  AND YEAR(payment_date) = YEAR(CURDATE())";

        $result = $this->db->fetch($query, [$userId]);

        return [
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }

    /**
     * Get pending expenses count
     */
    private function getPendingCount($userId) {
        $query = "SELECT COUNT(*) as count
                  FROM expenses
                  WHERE created_by = ? AND approved_by IS NULL";

        $result = $this->db->fetch($query, [$userId]);

        return $result['count'] ?? 0;
    }

    /**
     * Export data to CSV
     */
    private function exportToCSV($expenses, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($expenses)) {
            // Headers
            fputcsv($output, ['Date', 'Receipt No', 'Category', 'Reason', 'Amount', 'Payment Mode', 'Status']);

            // Data
            foreach ($expenses as $expense) {
                fputcsv($output, [
                    date('d-m-Y', strtotime($expense->payment_date)),
                    $expense->receipt_number,
                    $expense->getCategoryText(),
                    $expense->reason,
                    $expense->amount,
                    $expense->getPaymentModeText(),
                    $expense->getApprovalStatus()
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to PDF
     */
    private function exportToPDF($expenses, $filename) {
        require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetTitle('Expenses Report');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'EXPENSES REPORT', 0, 1, 'C');
        $pdf->Ln(5);

        // Table headers
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Receipt No', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Category', 1, 0, 'C');
        $pdf->Cell(60, 8, 'Reason', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Amount', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Mode', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Status', 1, 1, 'C');

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        foreach ($expenses as $expense) {
            $pdf->Cell(25, 6, date('d-m-Y', strtotime($expense->payment_date)), 1, 0, 'C');
            $pdf->Cell(25, 6, $expense->receipt_number, 1, 0, 'C');
            $pdf->Cell(30, 6, substr($expense->getCategoryText(), 0, 15), 1, 0, 'L');
            $pdf->Cell(60, 6, substr($expense->reason, 0, 30), 1, 0, 'L');
            $pdf->Cell(25, 6, '₹' . number_format($expense->amount, 2), 1, 0, 'R');
            $pdf->Cell(25, 6, $expense->getPaymentModeText(), 1, 0, 'C');
            $pdf->Cell(20, 6, $expense->getApprovalStatus(), 1, 1, 'C');
        }

        $pdf->Output($filename, 'D');
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}