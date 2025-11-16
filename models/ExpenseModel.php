<?php
/**
 * Expense Model
 * Handles school expense tracking and management
 */

class ExpenseModel extends BaseModel {
    protected $table = 'expenses';
    protected $fillable = [
        'expense_category',
        'amount',
        'payment_date',
        'receipt_number',
        'reason',
        'payment_mode',
        'transaction_id',
        'cheque_number',
        'created_by',
        'approved_by'
    ];

    /**
     * Get expense with creator and approver information
     */
    public static function withDetails($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT e.*, uc.username as created_by_name, ua.username as approved_by_name
             FROM {$instance->table} e
             LEFT JOIN users uc ON e.created_by = uc.id
             LEFT JOIN users ua ON e.approved_by = ua.id
             WHERE e.id = ?",
            [$id]
        );

        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }

        return null;
    }

    /**
     * Get expenses by date range
     */
    public static function getByDateRange($startDate, $endDate, $category = null, $minAmount = null, $maxAmount = null) {
        $instance = new static();
        $query = "SELECT e.*, uc.username as created_by_name, ua.username as approved_by_name
                  FROM {$instance->table} e
                  LEFT JOIN users uc ON e.created_by = uc.id
                  LEFT JOIN users ua ON e.approved_by = ua.id
                  WHERE e.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($category) {
            $query .= " AND e.expense_category = ?";
            $params[] = $category;
        }

        if ($minAmount !== null && $minAmount !== '') {
            $query .= " AND e.amount >= ?";
            $params[] = $minAmount;
        }

        if ($maxAmount !== null && $maxAmount !== '') {
            $query .= " AND e.amount <= ?";
            $params[] = $maxAmount;
        }

        $query .= " ORDER BY e.payment_date DESC, e.created_at DESC";

        $results = $instance->db->fetchAll($query, $params);

        $expenses = [];
        foreach ($results as $result) {
            $expense = new static($result);
            $expense->original = $result;
            $expense->exists = true;
            $expenses[] = $expense;
        }

        return $expenses;
    }

    /**
     * Get expenses by category
     */
    public static function getByCategory($category, $limit = null) {
        $instance = new static();
        $query = "SELECT e.*, uc.username as created_by_name, ua.username as approved_by_name
                  FROM {$instance->table} e
                  LEFT JOIN users uc ON e.created_by = uc.id
                  LEFT JOIN users ua ON e.approved_by = ua.id
                  WHERE e.expense_category = ?
                  ORDER BY e.payment_date DESC";

        if ($limit) {
            $query .= " LIMIT ?";
            $results = $instance->db->fetchAll($query, [$category, $limit]);
        } else {
            $results = $instance->db->fetchAll($query, [$category]);
        }

        $expenses = [];
        foreach ($results as $result) {
            $expense = new static($result);
            $expense->original = $result;
            $expense->exists = true;
            $expenses[] = $expense;
        }

        return $expenses;
    }

    /**
     * Get expense statistics
     */
    public static function getExpenseStats($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-01');
        if (!$endDate) $endDate = date('Y-m-t');

        $instance = new static();
        $query = "SELECT
                    COUNT(*) as total_expenses,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount,
                    expense_category,
                    COUNT(*) as category_count,
                    SUM(amount) as category_amount
                  FROM {$instance->table}
                  WHERE payment_date BETWEEN ? AND ?
                  GROUP BY expense_category";

        $results = $instance->db->fetchAll($query, [$startDate, $endDate]);

        $stats = [
            'total_expenses' => 0,
            'total_amount' => 0,
            'average_amount' => 0,
            'categories' => []
        ];

        foreach ($results as $result) {
            $stats['total_expenses'] += $result['category_count'];
            $stats['total_amount'] += $result['category_amount'];
            $stats['categories'][$result['expense_category']] = [
                'count' => $result['category_count'],
                'amount' => $result['category_amount']
            ];
        }

        if ($stats['total_expenses'] > 0) {
            $stats['average_amount'] = round($stats['total_amount'] / $stats['total_expenses'], 2);
        }

        return $stats;
    }

    /**
     * Get monthly expense trend
     */
    public static function getMonthlyTrend($year = null) {
        if (!$year) $year = date('Y');

        $instance = new static();
        $query = "SELECT
                    MONTH(payment_date) as month,
                    COUNT(*) as expense_count,
                    SUM(amount) as total_amount
                  FROM {$instance->table}
                  WHERE YEAR(payment_date) = ?
                  GROUP BY MONTH(payment_date)
                  ORDER BY MONTH(payment_date)";

        $results = $instance->db->fetchAll($query, [$year]);

        $trend = [];
        for ($i = 1; $i <= 12; $i++) {
            $trend[$i] = [
                'month' => $i,
                'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
                'count' => 0,
                'amount' => 0
            ];
        }

        foreach ($results as $result) {
            $trend[$result['month']] = [
                'month' => $result['month'],
                'month_name' => date('F', mktime(0, 0, 0, $result['month'], 1)),
                'count' => $result['expense_count'],
                'amount' => $result['total_amount']
            ];
        }

        return array_values($trend);
    }

    /**
     * Generate unique receipt number for expenses
     */
    public static function generateReceiptNumber() {
        $date = date('Ymd');
        $instance = new static();

        // Get the last receipt number for today
        $result = $instance->db->fetch(
            "SELECT receipt_number FROM {$instance->table}
             WHERE receipt_number LIKE ?
             ORDER BY receipt_number DESC LIMIT 1",
            ["EXP{$date}%"]
        );

        if ($result) {
            $lastNumber = intval(substr($result['receipt_number'], 11));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'EXP' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get expense categories
     */
    public static function getCategories() {
        return [
            'diesel' => 'Diesel',
            'staff' => 'Staff Salary',
            'bus' => 'Bus Maintenance',
            'maintenance' => 'General Maintenance',
            'misc' => 'Miscellaneous',
            'custom' => 'Custom'
        ];
    }

    /**
     * Get payment modes
     */
    public static function getPaymentModes() {
        return [
            'cash' => 'Cash',
            'online' => 'Online Transfer',
            'cheque' => 'Cheque',
            'upi' => 'UPI'
        ];
    }

    /**
     * Approve expense
     */
    public function approve($approvedBy) {
        $this->approved_by = $approvedBy;
        return $this->save();
    }

    /**
     * Check if expense is approved
     */
    public function isApproved() {
        return !empty($this->approved_by);
    }

    /**
     * Get approval status text
     */
    public function getApprovalStatus() {
        return $this->isApproved() ? 'Approved' : 'Pending';
    }

    /**
     * Get approval status badge class
     */
    public function getApprovalBadgeClass() {
        return $this->isApproved() ? 'badge bg-success' : 'badge bg-warning';
    }

    /**
     * Get category text
     */
    public function getCategoryText() {
        $categories = self::getCategories();
        return $categories[$this->expense_category] ?? ucfirst($this->expense_category);
    }

    /**
     * Get payment mode text
     */
    public function getPaymentModeText() {
        $modes = self::getPaymentModes();
        return $modes[$this->payment_mode] ?? ucfirst($this->payment_mode);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount() {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate() {
        return date('d-m-Y', strtotime($this->payment_date));
    }
}