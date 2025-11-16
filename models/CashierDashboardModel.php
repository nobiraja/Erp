<?php
/**
 * Cashier Dashboard Model
 * Handles dashboard data retrieval and calculations for cashier portal
 */

class CashierDashboardModel extends BaseModel {

    /**
     * Get comprehensive dashboard summary
     */
    public static function getDashboardSummary() {
        $instance = new static();

        // Get current month and year
        $currentMonth = date('m');
        $currentYear = date('Y');
        $academicYear = $currentYear . '-' . ($currentYear + 1);

        // Today's collection
        $todayCollection = self::getTodayCollection();

        // This month's collection
        $monthCollection = self::getMonthCollection($currentMonth, $currentYear);

        // Outstanding fees
        $outstandingFees = self::getOutstandingFeesSummary();

        // Overdue payments
        $overduePayments = self::getOverduePayments();

        // Recent payments
        $recentPayments = self::getRecentPayments(5);

        // Fee collection trends (last 6 months)
        $collectionTrends = self::getFeeCollectionTrends();

        // Expense summary
        $expenseSummary = self::getExpenseSummary($currentMonth, $currentYear);

        return [
            'today_collection' => $todayCollection,
            'month_collection' => $monthCollection,
            'outstanding_fees' => $outstandingFees,
            'overdue_payments' => $overduePayments,
            'recent_payments' => $recentPayments,
            'collection_trends' => $collectionTrends,
            'expense_summary' => $expenseSummary,
            'current_month' => date('F Y'),
            'academic_year' => $academicYear
        ];
    }

    /**
     * Get today's fee collection
     */
    public static function getTodayCollection() {
        $instance = new static();
        $today = date('Y-m-d');

        $result = $instance->db->fetch(
            "SELECT SUM(fp.amount_paid) as total_collection, COUNT(fp.id) as total_payments
             FROM fee_payments fp
             WHERE fp.payment_date = ?",
            [$today]
        );

        return [
            'amount' => $result['total_collection'] ?? 0,
            'count' => $result['total_payments'] ?? 0
        ];
    }

    /**
     * Get current month's fee collection
     */
    public static function getMonthCollection($month, $year) {
        $instance = new static();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $result = $instance->db->fetch(
            "SELECT SUM(fp.amount_paid) as total_collection, COUNT(fp.id) as total_payments
             FROM fee_payments fp
             WHERE fp.payment_date BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        return [
            'amount' => $result['total_collection'] ?? 0,
            'count' => $result['total_payments'] ?? 0
        ];
    }

    /**
     * Get outstanding fees summary
     */
    public static function getOutstandingFeesSummary() {
        $instance = new static();

        $result = $instance->db->fetch(
            "SELECT COUNT(*) as total_pending, SUM(f.amount) as total_amount
             FROM fees f
             WHERE f.is_paid = 0 AND f.due_date >= CURDATE()"
        );

        $overdueResult = $instance->db->fetch(
            "SELECT COUNT(*) as total_overdue, SUM(f.amount) as overdue_amount
             FROM fees f
             WHERE f.is_paid = 0 AND f.due_date < CURDATE()"
        );

        return [
            'pending_count' => $result['total_pending'] ?? 0,
            'pending_amount' => $result['total_amount'] ?? 0,
            'overdue_count' => $overdueResult['total_overdue'] ?? 0,
            'overdue_amount' => $overdueResult['overdue_amount'] ?? 0
        ];
    }

    /**
     * Get overdue payments
     */
    public static function getOverduePayments($limit = 10) {
        $instance = new static();

        $results = $instance->db->fetchAll(
            "SELECT f.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                    c.class_name, c.section,
                    DATEDIFF(CURDATE(), f.due_date) as days_overdue
             FROM fees f
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE f.is_paid = 0 AND f.due_date < CURDATE() AND s.is_active = 1
             ORDER BY f.due_date ASC, s.first_name, s.last_name
             LIMIT ?",
            [$limit]
        );

        $overdueFees = [];
        foreach ($results as $result) {
            $overdueFees[] = [
                'id' => $result['id'],
                'student_name' => trim($result['first_name'] . ' ' . ($result['middle_name'] ?? '') . ' ' . $result['last_name']),
                'scholar_number' => $result['scholar_number'],
                'class_section' => $result['class_name'] . ' ' . $result['section'],
                'fee_type' => $result['fee_type'],
                'amount' => $result['amount'],
                'due_date' => $result['due_date'],
                'days_overdue' => $result['days_overdue']
            ];
        }

        return $overdueFees;
    }

    /**
     * Get recent payments
     */
    public static function getRecentPayments($limit = 5) {
        $instance = new static();

        $results = $instance->db->fetchAll(
            "SELECT fp.*, f.fee_type, f.amount as fee_amount,
                    s.first_name, s.middle_name, s.last_name, s.scholar_number,
                    c.class_name, c.section
             FROM fee_payments fp
             LEFT JOIN fees f ON fp.fee_id = f.id
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             ORDER BY fp.created_at DESC
             LIMIT ?",
            [$limit]
        );

        $payments = [];
        foreach ($results as $result) {
            $payments[] = [
                'id' => $result['id'],
                'student_name' => trim($result['first_name'] . ' ' . ($result['middle_name'] ?? '') . ' ' . $result['last_name']),
                'scholar_number' => $result['scholar_number'],
                'class_section' => $result['class_name'] . ' ' . $result['section'],
                'fee_type' => $result['fee_type'],
                'amount_paid' => $result['amount_paid'],
                'payment_date' => $result['payment_date'],
                'payment_mode' => $result['payment_mode'],
                'receipt_number' => $result['receipt_number']
            ];
        }

        return $payments;
    }

    /**
     * Get fee collection trends (last 6 months)
     */
    public static function getFeeCollectionTrends() {
        $instance = new static();

        $results = $instance->db->fetchAll(
            "SELECT
                DATE_FORMAT(fp.payment_date, '%Y-%m') as month_year,
                DATE_FORMAT(fp.payment_date, '%M %Y') as month_name,
                SUM(fp.amount_paid) as collected_amount,
                COUNT(fp.id) as payment_count
             FROM fee_payments fp
             WHERE fp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m'), DATE_FORMAT(fp.payment_date, '%M %Y')
             ORDER BY month_year ASC"
        );

        $trends = [];
        foreach ($results as $result) {
            $trends[] = [
                'month_year' => $result['month_year'],
                'month_name' => $result['month_name'],
                'collected_amount' => $result['collected_amount'],
                'payment_count' => $result['payment_count']
            ];
        }

        return $trends;
    }

    /**
     * Get expense summary for current month
     */
    public static function getExpenseSummary($month, $year) {
        $instance = new static();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $result = $instance->db->fetch(
            "SELECT SUM(amount) as total_expenses, COUNT(*) as expense_count
             FROM expenses
             WHERE payment_date BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        return [
            'total_amount' => $result['total_expenses'] ?? 0,
            'count' => $result['expense_count'] ?? 0
        ];
    }

    /**
     * Get fee collection by payment mode
     */
    public static function getPaymentModeStats($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-01');
        if (!$endDate) $endDate = date('Y-m-t');

        $instance = new static();

        $results = $instance->db->fetchAll(
            "SELECT payment_mode, SUM(amount_paid) as total_amount, COUNT(*) as count
             FROM fee_payments
             WHERE payment_date BETWEEN ? AND ?
             GROUP BY payment_mode
             ORDER BY total_amount DESC",
            [$startDate, $endDate]
        );

        $stats = [];
        foreach ($results as $result) {
            $stats[] = [
                'mode' => $result['payment_mode'],
                'amount' => $result['total_amount'],
                'count' => $result['count']
            ];
        }

        return $stats;
    }

    /**
     * Get fee collection by class
     */
    public static function getClassWiseCollection($academicYear = null) {
        if (!$academicYear) {
            $academicYear = date('Y') . '-' . (date('Y') + 1);
        }

        $instance = new static();

        $results = $instance->db->fetchAll(
            "SELECT c.class_name, c.section,
                    SUM(fp.amount_paid) as collected_amount,
                    COUNT(DISTINCT f.student_id) as students_paid,
                    COUNT(DISTINCT s.id) as total_students
             FROM classes c
             LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
             LEFT JOIN fees f ON s.id = f.student_id AND f.academic_year = ?
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id
             GROUP BY c.id, c.class_name, c.section
             ORDER BY c.class_name, c.section",
            [$academicYear]
        );

        $classStats = [];
        foreach ($results as $result) {
            $classStats[] = [
                'class_section' => $result['class_name'] . ' ' . $result['section'],
                'collected_amount' => $result['collected_amount'] ?? 0,
                'students_paid' => $result['students_paid'] ?? 0,
                'total_students' => $result['total_students'] ?? 0,
                'collection_percentage' => $result['total_students'] > 0 ?
                    round(($result['students_paid'] / $result['total_students']) * 100, 1) : 0
            ];
        }

        return $classStats;
    }
}