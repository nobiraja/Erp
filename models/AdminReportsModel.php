<?php
/**
 * Admin Reports Model
 * Handles comprehensive reporting data aggregation and analysis
 */

class AdminReportsModel extends BaseModel {
    protected $table = 'reports'; // Default table, but we'll use multiple tables

    /**
     * Get academic report data (student performance, class analytics)
     */
    public static function getAcademicReport($classId = null, $examId = null, $startDate = null, $endDate = null) {
        $db = Database::getInstance();

        $whereConditions = [];
        $params = [];

        if ($classId) {
            $whereConditions[] = "s.class_id = ?";
            $params[] = $classId;
        }

        if ($examId) {
            $whereConditions[] = "er.exam_id = ?";
            $params[] = $examId;
        }

        if ($startDate && $endDate) {
            $whereConditions[] = "e.exam_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $query = "
            SELECT
                s.scholar_number,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                c.class_name,
                c.section,
                sub.subject_name,
                e.exam_name,
                er.marks_obtained,
                er.total_marks,
                er.grade,
                ROUND((er.marks_obtained / er.total_marks) * 100, 2) as percentage,
                e.exam_date
            FROM exam_results er
            JOIN students s ON er.student_id = s.id
            JOIN classes c ON s.class_id = c.id
            JOIN subjects sub ON er.subject_id = sub.id
            JOIN exams e ON er.exam_id = e.id
            {$whereClause}
            ORDER BY c.class_name, c.section, s.first_name, s.last_name, sub.subject_name
        ";

        $results = $db->fetchAll($query, $params);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'scholar_number' => $result['scholar_number'],
                'student_name' => $result['student_name'],
                'class_name' => $result['class_name'] . ' ' . $result['section'],
                'subject_name' => $result['subject_name'],
                'exam_name' => $result['exam_name'],
                'marks' => $result['marks_obtained'] . '/' . $result['total_marks'],
                'grade' => $result['grade'],
                'percentage' => $result['percentage'],
                'exam_date' => $result['exam_date']
            ];
        }

        return $data;
    }

    /**
     * Get financial report data (revenue, expenses, fee collection)
     */
    public static function getFinancialReport($type = 'revenue', $startDate = null, $endDate = null, $category = null) {
        $db = Database::getInstance();

        $whereConditions = [];
        $params = [];

        if ($startDate && $endDate) {
            if ($type === 'revenue' || $type === 'fees') {
                $whereConditions[] = "fp.payment_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            } else {
                $whereConditions[] = "e.expense_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
        }

        if ($category && $type === 'expenses') {
            $whereConditions[] = "e.category = ?";
            $params[] = $category;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $data = [];

        if ($type === 'revenue' || $type === 'fees') {
            // Fee collection revenue
            $query = "
                SELECT
                    fp.payment_date as date,
                    CONCAT('Fee Payment - ', s.first_name, ' ', s.last_name) as description,
                    f.fee_type as category,
                    'Revenue' as type,
                    fp.amount_paid as amount,
                    fp.receipt_number
                FROM fee_payments fp
                JOIN fees f ON fp.fee_id = f.id
                JOIN students s ON f.student_id = s.id
                {$whereClause}
                ORDER BY fp.payment_date DESC
            ";

            $results = $db->fetchAll($query, $params);
            foreach ($results as $result) {
                $data[] = [
                    'date' => $result['date'],
                    'description' => $result['description'],
                    'category' => $result['category'],
                    'type' => $result['type'],
                    'amount' => (float)$result['amount'],
                    'receipt_number' => $result['receipt_number']
                ];
            }
        } elseif ($type === 'expenses') {
            // Expenses
            $query = "
                SELECT
                    e.expense_date as date,
                    e.description,
                    e.category,
                    'Expense' as type,
                    e.amount,
                    e.receipt_number,
                    e.payment_mode
                FROM expenses e
                {$whereClause}
                ORDER BY e.expense_date DESC
            ";

            $results = $db->fetchAll($query, $params);
            foreach ($results as $result) {
                $data[] = [
                    'date' => $result['date'],
                    'description' => $result['description'],
                    'category' => $result['category'],
                    'type' => $result['type'],
                    'amount' => (float)$result['amount'],
                    'receipt_number' => $result['receipt_number'],
                    'payment_mode' => $result['payment_mode']
                ];
            }
        } elseif ($type === 'summary') {
            // Financial summary
            $revenueQuery = "
                SELECT
                    SUM(fp.amount_paid) as total_revenue,
                    COUNT(fp.id) as total_payments
                FROM fee_payments fp
                WHERE fp.payment_date BETWEEN ? AND ?
            ";

            $expenseQuery = "
                SELECT
                    SUM(e.amount) as total_expenses,
                    COUNT(e.id) as total_expenses_count
                FROM expenses e
                WHERE e.expense_date BETWEEN ? AND ?
            ";

            $revenueResult = $db->fetch($revenueQuery, [$startDate, $endDate]);
            $expenseResult = $db->fetch($expenseQuery, [$startDate, $endDate]);

            $totalRevenue = (float)($revenueResult['total_revenue'] ?? 0);
            $totalExpenses = (float)($expenseResult['total_expenses'] ?? 0);
            $netIncome = $totalRevenue - $totalExpenses;

            $data = [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'total_payments' => (int)($revenueResult['total_payments'] ?? 0),
                'total_expenses_count' => (int)($expenseResult['total_expenses_count'] ?? 0),
                'period' => $startDate . ' to ' . $endDate
            ];
        }

        return $data;
    }

    /**
     * Get attendance report data
     */
    public static function getAttendanceReport($classId = null, $studentId = null, $startDate = null, $endDate = null) {
        $db = Database::getInstance();

        if ($studentId) {
            // Individual student attendance
            return self::getStudentAttendanceReport($studentId, $startDate, $endDate);
        } elseif ($classId) {
            // Class attendance summary
            return self::getClassAttendanceReport($classId, $startDate, $endDate);
        } else {
            // Overall attendance summary
            return self::getOverallAttendanceReport($startDate, $endDate);
        }
    }

    /**
     * Get individual student attendance report
     */
    private static function getStudentAttendanceReport($studentId, $startDate, $endDate) {
        $db = Database::getInstance();

        $query = "
            SELECT
                a.attendance_date,
                a.status,
                sub.subject_name,
                c.class_name,
                c.section,
                t.first_name as teacher_first_name,
                t.last_name as teacher_last_name
            FROM attendance a
            LEFT JOIN subjects sub ON a.subject_id = sub.id
            JOIN classes c ON a.class_id = c.id
            LEFT JOIN teachers t ON a.marked_by = t.id
            WHERE a.student_id = ?
                AND a.attendance_date BETWEEN ? AND ?
            ORDER BY a.attendance_date DESC
        ";

        $results = $db->fetchAll($query, [$studentId, $startDate, $endDate]);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'attendance_date' => $result['attendance_date'],
                'status' => $result['status'],
                'subject_name' => $result['subject_name'] ?: 'General',
                'class_name' => $result['class_name'] . ' ' . $result['section'],
                'marked_by' => $result['teacher_first_name'] && $result['teacher_last_name'] ?
                    $result['teacher_first_name'] . ' ' . $result['teacher_last_name'] : 'System'
            ];
        }

        return $data;
    }

    /**
     * Get class attendance summary report
     */
    private static function getClassAttendanceReport($classId, $startDate, $endDate) {
        $db = Database::getInstance();

        $query = "
            SELECT
                s.scholar_number,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                c.class_name,
                c.section,
                COUNT(a.id) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                ROUND(
                    (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 1
                ) as attendance_percentage
            FROM students s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN attendance a ON s.id = a.student_id
                AND a.attendance_date BETWEEN ? AND ?
            WHERE s.class_id = ? AND s.is_active = 1
            GROUP BY s.id, s.scholar_number, s.first_name, s.last_name, c.class_name, c.section
            ORDER BY s.first_name, s.last_name
        ";

        $results = $db->fetchAll($query, [$startDate, $endDate, $classId]);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'scholar_number' => $result['scholar_number'],
                'student_name' => $result['student_name'],
                'class_name' => $result['class_name'] . ' ' . $result['section'],
                'total_days' => (int)$result['total_days'],
                'present_days' => (int)$result['present_days'],
                'absent_days' => (int)$result['absent_days'],
                'late_days' => (int)$result['late_days'],
                'attendance_percentage' => (float)$result['attendance_percentage']
            ];
        }

        return $data;
    }

    /**
     * Get overall attendance report
     */
    private static function getOverallAttendanceReport($startDate, $endDate) {
        $db = Database::getInstance();

        $query = "
            SELECT
                c.class_name,
                c.section,
                COUNT(DISTINCT s.id) as total_students,
                COUNT(a.id) as total_records,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND(
                    (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 1
                ) as average_attendance
            FROM classes c
            LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
            LEFT JOIN attendance a ON s.id = a.student_id
                AND a.attendance_date BETWEEN ? AND ?
            GROUP BY c.id, c.class_name, c.section
            ORDER BY c.class_name, c.section
        ";

        $results = $db->fetchAll($query, [$startDate, $endDate]);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'class_name' => $result['class_name'] . ' ' . $result['section'],
                'total_students' => (int)$result['total_students'],
                'total_records' => (int)$result['total_records'],
                'present_count' => (int)$result['present_count'],
                'absent_count' => (int)$result['absent_count'],
                'late_count' => (int)$result['late_count'],
                'average_attendance' => (float)$result['average_attendance']
            ];
        }

        return $data;
    }

    /**
     * Get custom report with filters
     */
    public static function getCustomReport($filters = []) {
        $db = Database::getInstance();

        $whereConditions = [];
        $params = [];

        if (!empty($filters['class_id'])) {
            $whereConditions[] = "s.class_id = ?";
            $params[] = $filters['class_id'];
        }

        if (!empty($filters['subject_id'])) {
            $whereConditions[] = "cs.subject_id = ?";
            $params[] = $filters['subject_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $whereConditions[] = "cs.teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $whereConditions[] = "a.attendance_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "a.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $query = "
            SELECT
                s.scholar_number,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                c.class_name,
                c.section,
                sub.subject_name,
                t.first_name as teacher_first_name,
                t.last_name as teacher_last_name,
                a.attendance_date,
                a.status,
                a.created_at as marked_at
            FROM students s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN attendance a ON s.id = a.student_id
            LEFT JOIN subjects sub ON a.subject_id = sub.id
            LEFT JOIN class_subjects cs ON c.id = cs.class_id AND sub.id = cs.subject_id
            LEFT JOIN teachers t ON cs.teacher_id = t.id
            {$whereClause}
            ORDER BY a.attendance_date DESC, s.first_name, s.last_name
            LIMIT 1000
        ";

        $results = $db->fetchAll($query, $params);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'scholar_number' => $result['scholar_number'],
                'student_name' => $result['student_name'],
                'class_name' => $result['class_name'] . ' ' . $result['section'],
                'subject_name' => $result['subject_name'] ?: 'General',
                'teacher_name' => $result['teacher_first_name'] && $result['teacher_last_name'] ?
                    $result['teacher_first_name'] . ' ' . $result['teacher_last_name'] : 'Not Assigned',
                'attendance_date' => $result['attendance_date'],
                'status' => $result['status'],
                'marked_at' => $result['marked_at']
            ];
        }

        return $data;
    }

    /**
     * Get report data for export
     */
    public static function getReportData($reportType, $filters = []) {
        switch ($reportType) {
            case 'academic':
                return self::getAcademicReport(
                    $filters['class_id'] ?? null,
                    $filters['exam_id'] ?? null,
                    $filters['start_date'] ?? null,
                    $filters['end_date'] ?? null
                );
            case 'financial':
                return self::getFinancialReport(
                    $filters['type'] ?? 'revenue',
                    $filters['start_date'] ?? null,
                    $filters['end_date'] ?? null,
                    $filters['category'] ?? null
                );
            case 'attendance':
                return self::getAttendanceReport(
                    $filters['class_id'] ?? null,
                    $filters['student_id'] ?? null,
                    $filters['start_date'] ?? null,
                    $filters['end_date'] ?? null
                );
            case 'custom':
                return self::getCustomReport($filters);
            default:
                return [];
        }
    }

    /**
     * Get chart data for dashboards
     */
    public static function getChartData($chartType, $filters = []) {
        $db = Database::getInstance();

        switch ($chartType) {
            case 'attendance_trend':
                return self::getAttendanceTrendChart($filters);
            case 'fee_collection':
                return self::getFeeCollectionChart($filters);
            case 'exam_performance':
                return self::getExamPerformanceChart($filters);
            case 'financial_summary':
                return self::getFinancialSummaryChart($filters);
            default:
                return [];
        }
    }

    /**
     * Get attendance trend chart data
     */
    private static function getAttendanceTrendChart($filters) {
        $db = Database::getInstance();

        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');
        $classId = $filters['class_id'] ?? null;

        $whereClause = $classId ? "AND a.class_id = ?" : "";
        $params = [$startDate, $endDate];
        if ($classId) $params[] = $classId;

        $query = "
            SELECT
                DATE_FORMAT(a.attendance_date, '%Y-%m-%d') as date,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                COUNT(a.id) as total
            FROM attendance a
            WHERE a.attendance_date BETWEEN ? AND ? {$whereClause}
            GROUP BY DATE(a.attendance_date)
            ORDER BY a.attendance_date
        ";

        $results = $db->fetchAll($query, $params);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'date' => $result['date'],
                'present' => (int)$result['present'],
                'absent' => (int)$result['absent'],
                'late' => (int)$result['late'],
                'total' => (int)$result['total'],
                'percentage' => $result['total'] > 0 ? round(($result['present'] / $result['total']) * 100, 1) : 0
            ];
        }

        return $data;
    }

    /**
     * Get fee collection chart data
     */
    private static function getFeeCollectionChart($filters) {
        $db = Database::getInstance();

        $year = $filters['year'] ?? date('Y');

        $query = "
            SELECT
                MONTH(fp.payment_date) as month,
                MONTHNAME(fp.payment_date) as month_name,
                SUM(fp.amount_paid) as collected_amount,
                COUNT(fp.id) as payment_count
            FROM fee_payments fp
            WHERE YEAR(fp.payment_date) = ?
            GROUP BY MONTH(fp.payment_date), MONTHNAME(fp.payment_date)
            ORDER BY MONTH(fp.payment_date)
        ";

        $results = $db->fetchAll($query, [$year]);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'month' => (int)$result['month'],
                'month_name' => $result['month_name'],
                'collected_amount' => (float)$result['collected_amount'],
                'payment_count' => (int)$result['payment_count']
            ];
        }

        return $data;
    }

    /**
     * Get exam performance chart data
     */
    private static function getExamPerformanceChart($filters) {
        $db = Database::getInstance();

        $examId = $filters['exam_id'] ?? null;
        $classId = $filters['class_id'] ?? null;

        $whereConditions = [];
        $params = [];

        if ($examId) {
            $whereConditions[] = "er.exam_id = ?";
            $params[] = $examId;
        }

        if ($classId) {
            $whereConditions[] = "s.class_id = ?";
            $params[] = $classId;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $query = "
            SELECT
                CASE
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 90 THEN '90-100%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 80 THEN '80-89%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 70 THEN '70-79%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 60 THEN '60-69%'
                    ELSE 'Below 60%'
                END as grade_range,
                COUNT(*) as student_count
            FROM exam_results er
            JOIN students s ON er.student_id = s.id
            {$whereClause}
            GROUP BY
                CASE
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 90 THEN '90-100%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 80 THEN '80-89%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 70 THEN '70-79%'
                    WHEN (er.marks_obtained / er.total_marks) * 100 >= 60 THEN '60-69%'
                    ELSE 'Below 60%'
                END
            ORDER BY student_count DESC
        ";

        $results = $db->fetchAll($query, $params);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'grade_range' => $result['grade_range'],
                'student_count' => (int)$result['student_count']
            ];
        }

        return $data;
    }

    /**
     * Get financial summary chart data
     */
    private static function getFinancialSummaryChart($filters) {
        $db = Database::getInstance();

        $year = $filters['year'] ?? date('Y');

        $query = "
            SELECT
                MONTH(fp.payment_date) as month,
                MONTHNAME(fp.payment_date) as month_name,
                SUM(fp.amount_paid) as revenue,
                (
                    SELECT SUM(e.amount)
                    FROM expenses e
                    WHERE YEAR(e.expense_date) = YEAR(fp.payment_date)
                        AND MONTH(e.expense_date) = MONTH(fp.payment_date)
                ) as expenses
            FROM fee_payments fp
            WHERE YEAR(fp.payment_date) = ?
            GROUP BY MONTH(fp.payment_date), MONTHNAME(fp.payment_date)
            ORDER BY MONTH(fp.payment_date)
        ";

        $results = $db->fetchAll($query, [$year]);

        $data = [];
        foreach ($results as $result) {
            $revenue = (float)$result['revenue'];
            $expenses = (float)$result['expenses'];
            $net = $revenue - $expenses;

            $data[] = [
                'month' => (int)$result['month'],
                'month_name' => $result['month_name'],
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net_income' => $net
            ];
        }

        return $data;
    }
}