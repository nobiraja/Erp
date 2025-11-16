<?php
/**
 * Admin Dashboard Model
 * Handles dashboard statistics and data operations
 */

class AdminDashboardModel extends BaseModel {
    protected $table = 'users'; // Default table, but we'll use multiple tables

    /**
     * Get user statistics by role
     */
    public static function getUserStatistics() {
        $db = Database::getInstance();

        $stats = [];

        // Get total users by role
        $query = "
            SELECT
                ur.role_name,
                COUNT(u.id) as count
            FROM user_roles ur
            LEFT JOIN users u ON ur.id = u.role_id AND u.is_active = 1
            GROUP BY ur.id, ur.role_name
            ORDER BY ur.role_name
        ";

        $results = $db->fetchAll($query);
        foreach ($results as $result) {
            $stats[$result['role_name']] = (int)$result['count'];
        }

        // Get total active students
        $studentCount = $db->fetch("SELECT COUNT(*) as count FROM students WHERE is_active = 1")['count'];
        $stats['active_students'] = (int)$studentCount;

        // Get total active teachers
        $teacherCount = $db->fetch("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1")['count'];
        $stats['active_teachers'] = (int)$teacherCount;

        return $stats;
    }

    /**
     * Get attendance statistics
     */
    public static function getAttendanceStatistics() {
        $db = Database::getInstance();

        // Get attendance for current month
        $currentMonth = date('Y-m');
        $query = "
            SELECT
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND(
                    (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2
                ) as attendance_percentage
            FROM attendance
            WHERE DATE_FORMAT(attendance_date, '%Y-%m') = ?
        ";

        $result = $db->fetch($query, [$currentMonth]);

        if ($result && $result['total_records'] > 0) {
            return [
                'total_records' => (int)$result['total_records'],
                'present_count' => (int)$result['present_count'],
                'absent_count' => (int)$result['absent_count'],
                'late_count' => (int)$result['late_count'],
                'attendance_percentage' => (float)$result['attendance_percentage']
            ];
        }

        return [
            'total_records' => 0,
            'present_count' => 0,
            'absent_count' => 0,
            'late_count' => 0,
            'attendance_percentage' => 0.0
        ];
    }

    /**
     * Get fee collection statistics
     */
    public static function getFeeStatistics() {
        $db = Database::getInstance();

        // Get fee statistics for current academic year
        $currentYear = date('Y');

        $query = "
            SELECT
                COUNT(*) as total_fees,
                SUM(amount) as total_amount,
                SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as collected_amount,
                SUM(CASE WHEN is_paid = 0 THEN amount ELSE 0 END) as pending_amount,
                ROUND(
                    (SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) * 100.0 / SUM(amount)), 2
                ) as collection_percentage
            FROM fees
            WHERE academic_year = ?
        ";

        $result = $db->fetch($query, [$currentYear]);

        if ($result && $result['total_fees'] > 0) {
            return [
                'total_fees' => (int)$result['total_fees'],
                'total_amount' => (float)$result['total_amount'],
                'collected_amount' => (float)$result['collected_amount'],
                'pending_amount' => (float)$result['pending_amount'],
                'collection_percentage' => (float)$result['collection_percentage']
            ];
        }

        return [
            'total_fees' => 0,
            'total_amount' => 0.0,
            'collected_amount' => 0.0,
            'pending_amount' => 0.0,
            'collection_percentage' => 0.0
        ];
    }

    /**
     * Get recent activities from audit logs
     */
    public static function getRecentActivities($limit = 10) {
        $db = Database::getInstance();

        $query = "
            SELECT
                al.action,
                al.table_name,
                al.created_at,
                u.username,
                ur.role_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN user_roles ur ON u.role_id = ur.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ";

        $results = $db->fetchAll($query, [$limit]);

        $activities = [];
        foreach ($results as $result) {
            $activities[] = [
                'action' => $result['action'],
                'table_name' => $result['table_name'],
                'username' => $result['username'] ?: 'System',
                'role_name' => $result['role_name'] ?: 'System',
                'created_at' => $result['created_at']
            ];
        }

        return $activities;
    }

    /**
     * Get class-wise attendance statistics
     */
    public static function getClassAttendanceStats() {
        $db = Database::getInstance();

        $currentMonth = date('Y-m');

        $query = "
            SELECT
                c.class_name,
                c.section,
                COUNT(a.id) as total_records,
                ROUND(
                    (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 2
                ) as attendance_percentage
            FROM classes c
            LEFT JOIN attendance a ON c.id = a.class_id
                AND DATE_FORMAT(a.attendance_date, '%Y-%m') = ?
            GROUP BY c.id, c.class_name, c.section
            ORDER BY c.class_name, c.section
            LIMIT 10
        ";

        $results = $db->fetchAll($query, [$currentMonth]);

        $stats = [];
        foreach ($results as $result) {
            $stats[] = [
                'class_name' => $result['class_name'],
                'section' => $result['section'],
                'total_records' => (int)$result['total_records'],
                'attendance_percentage' => (float)$result['attendance_percentage']
            ];
        }

        return $stats;
    }

    /**
     * Get monthly fee collection trend
     */
    public static function getMonthlyFeeCollection() {
        $db = Database::getInstance();

        $currentYear = date('Y');

        $query = "
            SELECT
                MONTH(fp.payment_date) as month,
                MONTHNAME(fp.payment_date) as month_name,
                SUM(fp.amount_paid) as collected_amount
            FROM fee_payments fp
            WHERE YEAR(fp.payment_date) = ?
            GROUP BY MONTH(fp.payment_date), MONTHNAME(fp.payment_date)
            ORDER BY MONTH(fp.payment_date)
        ";

        $results = $db->fetchAll($query, [$currentYear]);

        $monthlyData = [];
        foreach ($results as $result) {
            $monthlyData[] = [
                'month' => (int)$result['month'],
                'month_name' => $result['month_name'],
                'collected_amount' => (float)$result['collected_amount']
            ];
        }

        return $monthlyData;
    }

    /**
     * Get upcoming events
     */
    public static function getUpcomingEvents($limit = 5) {
        $db = Database::getInstance();

        $query = "
            SELECT
                title,
                event_date,
                event_time,
                location
            FROM events
            WHERE is_active = 1
                AND event_date >= CURDATE()
            ORDER BY event_date ASC, event_time ASC
            LIMIT ?
        ";

        $results = $db->fetchAll($query, [$limit]);

        $events = [];
        foreach ($results as $result) {
            $events[] = [
                'title' => $result['title'],
                'event_date' => $result['event_date'],
                'event_time' => $result['event_time'],
                'location' => $result['location']
            ];
        }

        return $events;
    }

    /**
     * Get dashboard summary data
     */
    public static function getDashboardSummary() {
        return [
            'user_stats' => self::getUserStatistics(),
            'attendance_stats' => self::getAttendanceStatistics(),
            'fee_stats' => self::getFeeStatistics(),
            'recent_activities' => self::getRecentActivities(5),
            'class_attendance' => self::getClassAttendanceStats(),
            'monthly_fees' => self::getMonthlyFeeCollection(),
            'upcoming_events' => self::getUpcomingEvents(5)
        ];
    }
}