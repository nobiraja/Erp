<?php
/**
 * Student Dashboard Model
 * Handles student dashboard data retrieval
 */

class StudentDashboardModel extends BaseModel {

    /**
     * Get all dashboard data for a student
     */
    public static function getDashboardData($studentId) {
        $instance = new static();

        return [
            'student' => self::getStudentInfo($studentId),
            'attendance_percentage' => self::getAttendancePercentage($studentId),
            'exam_results' => self::getRecentExamResults($studentId),
            'upcoming_events' => self::getUpcomingEvents(),
            'fee_status' => self::getFeeStatus($studentId),
            'notifications' => self::getNotifications($studentId)
        ];
    }

    /**
     * Get student basic information
     */
    public static function getStudentInfo($studentId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT s.*, c.class_name, c.section as class_section, c.academic_year,
                    u.username, u.email as user_email
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             LEFT JOIN users u ON s.user_id = u.id
             WHERE s.id = ? AND s.is_active = 1",
            [$studentId]
        );

        return $result ?: [];
    }

    /**
     * Get attendance percentage for current month
     */
    public static function getAttendancePercentage($studentId) {
        $instance = new static();
        $currentMonth = date('m');
        $currentYear = date('Y');

        $result = $instance->db->fetch(
            "SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$studentId, $currentMonth, $currentYear]
        );

        if ($result && $result['total_days'] > 0) {
            return round(($result['present_days'] / $result['total_days']) * 100, 1);
        }

        return 0;
    }

    /**
     * Get recent exam results
     */
    public static function getRecentExamResults($studentId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT er.*, e.exam_name, e.exam_type, s.subject_name,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             LEFT JOIN subjects s ON er.subject_id = s.id
             LEFT JOIN teachers t ON e.teacher_id = t.id
             WHERE er.student_id = ?
             ORDER BY e.exam_date DESC, er.created_at DESC
             LIMIT 5",
            [$studentId]
        );

        return $results ?: [];
    }

    /**
     * Get upcoming events
     */
    public static function getUpcomingEvents() {
        $instance = new static();
        $today = date('Y-m-d');

        $results = $instance->db->fetchAll(
            "SELECT * FROM events
             WHERE event_date >= ? AND is_active = 1
             ORDER BY event_date ASC
             LIMIT 5",
            [$today]
        );

        return $results ?: [];
    }

    /**
     * Get fee status
     */
    public static function getFeeStatus($studentId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT
                SUM(f.amount) as total_fees,
                SUM(CASE WHEN fp.is_paid = 1 THEN fp.amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN fp.due_date < CURDATE() AND fp.is_paid = 0 THEN fp.amount ELSE 0 END) as overdue_amount
             FROM fees f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id
             WHERE f.student_id = ?",
            [$studentId]
        );

        if ($result) {
            $total = (float)($result['total_fees'] ?: 0);
            $paid = (float)($result['paid_amount'] ?: 0);
            $overdue = (float)($result['overdue_amount'] ?: 0);
            $pending = $total - $paid;

            return [
                'total' => $total,
                'paid' => $paid,
                'pending' => $pending,
                'overdue' => $overdue,
                'percentage' => $total > 0 ? round(($paid / $total) * 100, 1) : 0
            ];
        }

        return [
            'total' => 0,
            'paid' => 0,
            'pending' => 0,
            'overdue' => 0,
            'percentage' => 0
        ];
    }

    /**
     * Get notifications for student
     */
    public static function getNotifications($studentId) {
        $instance = new static();

        // Get student class for targeted notifications
        $studentInfo = self::getStudentInfo($studentId);
        $classId = $studentInfo['class_id'] ?? null;

        $notifications = [];

        // Fee overdue notifications
        $feeStatus = self::getFeeStatus($studentId);
        if ($feeStatus['overdue'] > 0) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Fee Payment Overdue',
                'message' => 'You have ₹' . number_format($feeStatus['overdue']) . ' in overdue fees. Please make payment immediately.',
                'icon' => 'exclamation-triangle',
                'action_url' => '/student/fees'
            ];
        }

        // Low attendance warning
        $attendance = self::getAttendancePercentage($studentId);
        if ($attendance < 75) {
            $notifications[] = [
                'type' => 'danger',
                'title' => 'Low Attendance Alert',
                'message' => 'Your attendance is ' . $attendance . '%. Please maintain at least 75% attendance.',
                'icon' => 'calendar-times',
                'action_url' => '/student/attendance'
            ];
        }

        // Upcoming exams
        $upcomingExams = $instance->db->fetchAll(
            "SELECT e.exam_name, e.exam_date, s.subject_name
             FROM exams e
             LEFT JOIN subjects s ON e.subject_id = s.id
             WHERE e.class_id = ? AND e.exam_date >= CURDATE() AND e.exam_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY e.exam_date ASC
             LIMIT 3",
            [$classId]
        );

        foreach ($upcomingExams as $exam) {
            $notifications[] = [
                'type' => 'info',
                'title' => 'Upcoming Exam',
                'message' => $exam['subject_name'] . ' exam on ' . date('M d, Y', strtotime($exam['exam_date'])),
                'icon' => 'clipboard-list',
                'action_url' => '/student/results'
            ];
        }

        // Recent announcements
        $announcements = $instance->db->fetchAll(
            "SELECT title, content, created_at
             FROM announcements
             WHERE (target_audience = 'all' OR target_audience = 'students' OR target_class = ?)
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY created_at DESC
             LIMIT 2",
            [$classId]
        );

        foreach ($announcements as $announcement) {
            $notifications[] = [
                'type' => 'success',
                'title' => 'School Announcement',
                'message' => substr($announcement['title'], 0, 50) . '...',
                'icon' => 'bullhorn',
                'action_url' => '/student/announcements'
            ];
        }

        return array_slice($notifications, 0, 5); // Limit to 5 notifications
    }

    /**
     * Get detailed attendance data
     */
    public static function getAttendanceData($studentId) {
        $instance = new static();
        $currentMonth = date('m');
        $currentYear = date('Y');

        $results = $instance->db->fetchAll(
            "SELECT attendance_date, status, remarks
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?
             ORDER BY attendance_date DESC",
            [$studentId, $currentMonth, $currentYear]
        );

        return $results ?: [];
    }

    /**
     * Get exam results (detailed)
     */
    public static function getExamResults($studentId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT er.*, e.exam_name, e.exam_type, e.exam_date, s.subject_name,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             LEFT JOIN subjects s ON er.subject_id = s.id
             LEFT JOIN teachers t ON e.teacher_id = t.id
             WHERE er.student_id = ?
             ORDER BY e.exam_date DESC, s.subject_name ASC",
            [$studentId]
        );

        return $results ?: [];
    }
}