<?php
/**
 * Parent Model
 * Handles parent portal data retrieval
 */

class ParentModel extends BaseModel {

    /**
     * Get all dashboard data for a parent
     */
    public static function getDashboardData($parentId) {
        $instance = new static();

        return [
            'parent' => self::getParentInfo($parentId),
            'children' => self::getChildrenOverview($parentId),
            'notifications' => self::getNotifications($parentId),
            'upcoming_events' => self::getUpcomingEvents()
        ];
    }

    /**
     * Get parent basic information
     */
    public static function getParentInfo($parentId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT u.*, p.first_name, p.middle_name, p.last_name, p.mobile, p.email as parent_email,
                    p.permanent_address, p.temporary_address, p.occupation, p.relationship_to_student
             FROM users u
             LEFT JOIN parents p ON u.id = p.user_id
             WHERE u.id = ? AND u.role = 'parent'",
            [$parentId]
        );

        return $result ?: [];
    }

    /**
     * Get children overview with snapshots
     */
    public static function getChildrenOverview($parentId) {
        $instance = new static();
        $children = $instance->db->fetchAll(
            "SELECT s.id, s.scholar_number, s.admission_number,
                    CONCAT(s.first_name, ' ', s.middle_name, ' ', s.last_name) as full_name,
                    c.class_name, c.section as class_section, c.academic_year,
                    ROUND(AVG(CASE WHEN a.status = 'present' THEN 100 ELSE 0 END), 1) as attendance_percentage,
                    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_days,
                    COUNT(a.id) as total_days
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             LEFT JOIN attendance a ON s.id = a.student_id AND MONTH(a.attendance_date) = MONTH(CURDATE()) AND YEAR(a.attendance_date) = YEAR(CURDATE())
             WHERE s.parent_id = ? AND s.is_active = 1
             GROUP BY s.id, s.scholar_number, s.admission_number, s.first_name, s.middle_name, s.last_name, c.class_name, c.section, c.academic_year",
            [$parentId]
        );

        // Add fee status and recent results to each child
        foreach ($children as &$child) {
            $child['fee_status'] = self::getChildFeeStatus($child['id']);
            $child['recent_results'] = self::getChildRecentResults($child['id']);
        }

        return $children ?: [];
    }

    /**
     * Get children with detailed academic snapshots
     */
    public static function getChildrenWithSnapshots($parentId) {
        $instance = new static();
        $children = $instance->db->fetchAll(
            "SELECT s.*, c.class_name, c.section as class_section, c.academic_year
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.parent_id = ? AND s.is_active = 1
             ORDER BY s.first_name, s.last_name",
            [$parentId]
        );

        // Add comprehensive data for each child
        foreach ($children as &$child) {
            $childId = $child['id'];
            $child['attendance'] = self::getChildAttendanceSummary($childId);
            $child['exam_results'] = self::getChildExamSummary($childId);
            $child['fee_status'] = self::getChildFeeStatus($childId);
            $child['performance_trend'] = self::getChildPerformanceTrend($childId);
        }

        return $children ?: [];
    }

    /**
     * Get children attendance data
     */
    public static function getChildrenAttendance($parentId) {
        $instance = new static();
        $children = $instance->db->fetchAll(
            "SELECT s.id, s.scholar_number,
                    CONCAT(s.first_name, ' ', s.middle_name, ' ', s.last_name) as full_name,
                    c.class_name, c.section as class_section
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.parent_id = ? AND s.is_active = 1
             ORDER BY s.first_name, s.last_name",
            [$parentId]
        );

        // Add attendance data for each child
        foreach ($children as &$child) {
            $child['attendance_summary'] = self::getChildAttendanceSummary($child['id']);
            $child['monthly_trends'] = self::getChildMonthlyTrends($child['id']);
        }

        return $children ?: [];
    }

    /**
     * Get children exam results
     */
    public static function getChildrenResults($parentId) {
        $instance = new static();
        $children = $instance->db->fetchAll(
            "SELECT s.id, s.scholar_number,
                    CONCAT(s.first_name, ' ', s.middle_name, ' ', s.last_name) as full_name,
                    c.class_name, c.section as class_section
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.parent_id = ? AND s.is_active = 1
             ORDER BY s.first_name, s.last_name",
            [$parentId]
        );

        // Add results data for each child
        foreach ($children as &$child) {
            $child['exam_summary'] = self::getChildExamSummary($child['id']);
            $child['recent_exams'] = self::getChildRecentExams($child['id']);
        }

        return $children ?: [];
    }

    /**
     * Get children fee data
     */
    public static function getChildrenFees($parentId) {
        $instance = new static();
        $children = $instance->db->fetchAll(
            "SELECT s.id, s.scholar_number,
                    CONCAT(s.first_name, ' ', s.middle_name, ' ', s.last_name) as full_name,
                    c.class_name, c.section as class_section
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.parent_id = ? AND s.is_active = 1
             ORDER BY s.first_name, s.last_name",
            [$parentId]
        );

        // Add fee data for each child
        foreach ($children as &$child) {
            $child['fee_status'] = self::getChildFeeStatus($child['id']);
            $child['payment_history'] = self::getChildPaymentHistory($child['id']);
            $child['pending_dues'] = self::getChildPendingDues($child['id']);
        }

        return $children ?: [];
    }

    /**
     * Get child attendance summary
     */
    private static function getChildAttendanceSummary($childId) {
        $instance = new static();

        // Current month attendance
        $result = $instance->db->fetch(
            "SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = MONTH(CURDATE()) AND YEAR(attendance_date) = YEAR(CURDATE())",
            [$childId]
        );

        if ($result && $result['total_days'] > 0) {
            $percentage = round(($result['present_days'] / $result['total_days']) * 100, 1);
            return [
                'total_days' => (int)$result['total_days'],
                'present_days' => (int)$result['present_days'],
                'absent_days' => (int)$result['absent_days'],
                'late_days' => (int)$result['late_days'],
                'percentage' => $percentage
            ];
        }

        return [
            'total_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'percentage' => 0
        ];
    }

    /**
     * Get child monthly attendance trends
     */
    private static function getChildMonthlyTrends($childId) {
        $instance = new static();
        $trends = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            list($year, $month) = explode('-', $date);

            $result = $instance->db->fetch(
                "SELECT
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
                 FROM attendance
                 WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
                [$childId, $month, $year]
            );

            $percentage = $result && $result['total_days'] > 0 ?
                round(($result['present_days'] / $result['total_days']) * 100, 1) : 0;

            $trends[] = [
                'month' => date('M Y', strtotime($date)),
                'percentage' => $percentage,
                'total_days' => (int)($result['total_days'] ?? 0),
                'present_days' => (int)($result['present_days'] ?? 0)
            ];
        }

        return $trends;
    }

    /**
     * Get child exam summary
     */
    private static function getChildExamSummary($childId) {
        $instance = new static();

        $result = $instance->db->fetch(
            "SELECT
                COUNT(DISTINCT er.exam_id) as total_exams,
                AVG(er.percentage) as average_percentage,
                MAX(er.percentage) as highest_percentage,
                MIN(er.percentage) as lowest_percentage,
                COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as grade_a_count,
                COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as grade_b_count,
                COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as grade_c_count
             FROM exam_results er
             WHERE er.student_id = ? AND er.exam_id IN (
                 SELECT id FROM exams WHERE academic_year = (
                     SELECT academic_year FROM classes c
                     LEFT JOIN students s ON c.id = s.class_id
                     WHERE s.id = ?
                 )
             )",
            [$childId, $childId]
        );

        // Get class rank information
        $rankInfo = self::getChildClassRank($childId);

        if ($result) {
            return [
                'total_exams' => (int)$result['total_exams'],
                'average_percentage' => round((float)$result['average_percentage'], 1),
                'highest_percentage' => round((float)$result['highest_percentage'], 1),
                'lowest_percentage' => round((float)$result['lowest_percentage'], 1),
                'grade_distribution' => [
                    'A' => (int)$result['grade_a_count'],
                    'B' => (int)$result['grade_b_count'],
                    'C' => (int)$result['grade_c_count']
                ],
                'class_rank' => $rankInfo['current_rank'],
                'total_students' => $rankInfo['total_students'],
                'rank_trend' => $rankInfo['rank_trend']
            ];
        }

        return [
            'total_exams' => 0,
            'average_percentage' => 0,
            'highest_percentage' => 0,
            'lowest_percentage' => 0,
            'grade_distribution' => ['A' => 0, 'B' => 0, 'C' => 0],
            'class_rank' => 0,
            'total_students' => 0,
            'rank_trend' => 'stable'
        ];
    }

    /**
     * Get child class rank information
     */
    private static function getChildClassRank($childId) {
        $instance = new static();

        // Get student's class
        $studentClass = $instance->db->fetch(
            "SELECT class_id FROM students WHERE id = ?",
            [$childId]
        );

        if (!$studentClass) {
            return ['current_rank' => 0, 'total_students' => 0, 'rank_trend' => 'stable'];
        }

        $classId = $studentClass['class_id'];

        // Get current academic year
        $academicYear = $instance->db->fetch(
            "SELECT academic_year FROM classes WHERE id = ?",
            [$classId]
        )['academic_year'] ?? date('Y');

        // Calculate average percentage for all students in the class
        $classAverages = $instance->db->fetchAll(
            "SELECT
                s.id as student_id,
                AVG(er.percentage) as average_percentage
             FROM students s
             LEFT JOIN exam_results er ON s.id = er.student_id
             LEFT JOIN exams e ON er.exam_id = e.id AND e.academic_year = ?
             WHERE s.class_id = ? AND s.is_active = 1
             GROUP BY s.id
             ORDER BY average_percentage DESC",
            [$academicYear, $classId]
        );

        $totalStudents = count($classAverages);
        $currentRank = 0;
        $previousRank = 0;

        foreach ($classAverages as $index => $student) {
            if ($student['student_id'] == $childId) {
                $currentRank = $index + 1;
                break;
            }
        }

        // Calculate rank trend (simplified - compare with previous term)
        $rankTrend = 'stable';
        if ($currentRank > 0) {
            // For now, we'll assume stable. In a real implementation,
            // you'd compare with previous academic period
            $rankTrend = 'stable';
        }

        return [
            'current_rank' => $currentRank,
            'total_students' => $totalStudents,
            'rank_trend' => $rankTrend
        ];
    }

    /**
     * Get child recent exam results
     */
    private static function getChildRecentResults($childId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT er.*, e.exam_name, e.exam_type, s.subject_name
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             LEFT JOIN subjects s ON er.subject_id = s.id
             WHERE er.student_id = ?
             ORDER BY e.exam_date DESC, er.created_at DESC
             LIMIT 3",
            [$childId]
        );

        return $results ?: [];
    }

    /**
     * Get child recent exams
     */
    private static function getChildRecentExams($childId) {
        $instance = new static();
        $exams = $instance->db->fetchAll(
            "SELECT e.*, s.subject_name
             FROM exams e
             LEFT JOIN subjects s ON e.subject_id = s.id
             LEFT JOIN students st ON e.class_id = st.class_id
             WHERE st.id = ?
             ORDER BY e.exam_date DESC
             LIMIT 5",
            [$childId]
        );

        // Add results for each exam
        foreach ($exams as &$exam) {
            $result = $instance->db->fetch(
                "SELECT * FROM exam_results
                 WHERE student_id = ? AND exam_id = ?",
                [$childId, $exam['id']]
            );
            $exam['result'] = $result;
        }

        return $exams ?: [];
    }

    /**
     * Get child fee status
     */
    private static function getChildFeeStatus($childId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT
                SUM(f.amount) as total_fees,
                SUM(CASE WHEN fp.is_paid = 1 THEN fp.amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN fp.due_date < CURDATE() AND fp.is_paid = 0 THEN fp.amount ELSE 0 END) as overdue_amount
             FROM fees f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id
             WHERE f.student_id = ?",
            [$childId]
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
     * Get child payment history
     */
    private static function getChildPaymentHistory($childId) {
        $instance = new static();
        $payments = $instance->db->fetchAll(
            "SELECT fp.*, f.fee_type, f.academic_year
             FROM fee_payments fp
             LEFT JOIN fees f ON fp.fee_id = f.id
             WHERE f.student_id = ?
             ORDER BY fp.payment_date DESC
             LIMIT 10",
            [$childId]
        );

        return $payments ?: [];
    }

    /**
     * Get child pending dues
     */
    private static function getChildPendingDues($childId) {
        $instance = new static();
        $dues = $instance->db->fetchAll(
            "SELECT f.*, fp.amount as paid_amount,
                    (f.amount - COALESCE(fp.amount, 0)) as remaining_amount,
                    DATEDIFF(CURDATE(), f.due_date) as days_overdue
             FROM fees f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id AND fp.is_paid = 1
             WHERE f.student_id = ? AND (fp.id IS NULL OR f.amount > fp.amount)
             ORDER BY f.due_date ASC",
            [$childId]
        );

        return $dues ?: [];
    }

    /**
     * Get child performance trend
     */
    private static function getChildPerformanceTrend($childId) {
        $instance = new static();
        $trends = $instance->db->fetchAll(
            "SELECT e.exam_name, e.exam_date, AVG(er.percentage) as average_percentage,
                    COUNT(er.id) as subjects_count
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             WHERE er.student_id = ?
             GROUP BY e.id, e.exam_name, e.exam_date
             ORDER BY e.exam_date DESC
             LIMIT 6",
            [$childId]
        );

        // Reverse to chronological order and format for charts
        $trends = array_reverse($trends ?: []);

        $formattedTrends = [];
        foreach ($trends as $trend) {
            $formattedTrends[] = [
                'exam_name' => $trend['exam_name'],
                'date' => date('M Y', strtotime($trend['exam_date'])),
                'percentage' => round((float)$trend['average_percentage'], 1),
                'subjects_count' => (int)$trend['subjects_count']
            ];
        }

        return $formattedTrends;
    }

    /**
     * Get upcoming events with registration status for parent
     */
    public static function getUpcomingEvents($parentId = null) {
        $instance = new static();
        $today = date('Y-m-d');

        $results = $instance->db->fetchAll(
            "SELECT e.* FROM events e
             WHERE e.event_date >= ? AND e.is_active = 1
             ORDER BY e.event_date ASC
             LIMIT 20",
            [$today]
        );

        // Add registration status if parent ID provided
        if ($parentId && !empty($results)) {
            foreach ($results as &$event) {
                $event['is_registered'] = EventsModel::isParentRegistered($event['id'], $parentId);
                $event['registration_count'] = EventsModel::getRegistrationCount($event['id']);
            }
        }

        return $results ?: [];
    }

    /**
     * Get parent event registrations
     */
    public static function getParentEventRegistrations($parentId) {
        return EventsModel::getParentRegistrations($parentId);
    }

    /**
     * Get notifications for parent
     */
    public static function getNotifications($parentId) {
        $instance = new static();
        $notifications = [];

        // Get children for this parent
        $children = $instance->db->fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM students WHERE parent_id = ?",
            [$parentId]
        );

        foreach ($children as $child) {
            $childId = $child['id'];
            $childName = $child['full_name'];

            // Fee overdue notifications
            $feeStatus = self::getChildFeeStatus($childId);
            if ($feeStatus['overdue'] > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Fee Payment Overdue - ' . $childName,
                    'message' => '₹' . number_format($feeStatus['overdue']) . ' in overdue fees. Please make payment immediately.',
                    'icon' => 'exclamation-triangle',
                    'action_url' => '/parent/fees'
                ];
            }

            // Low attendance warning
            $attendance = self::getChildAttendanceSummary($childId);
            if ($attendance['percentage'] < 75 && $attendance['total_days'] > 0) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'Low Attendance Alert - ' . $childName,
                    'message' => 'Attendance is ' . $attendance['percentage'] . '%. Please ensure regular attendance.',
                    'icon' => 'calendar-times',
                    'action_url' => '/parent/attendance'
                ];
            }
        }

        // Upcoming exams for all children
        $upcomingExams = $instance->db->fetchAll(
            "SELECT e.exam_name, e.exam_date, s.subject_name, st.first_name, st.last_name
             FROM exams e
             LEFT JOIN subjects s ON e.subject_id = s.id
             LEFT JOIN students st ON e.class_id = st.class_id
             WHERE st.parent_id = ? AND e.exam_date >= CURDATE() AND e.exam_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY e.exam_date ASC
             LIMIT 5",
            [$parentId]
        );

        foreach ($upcomingExams as $exam) {
            $notifications[] = [
                'type' => 'info',
                'title' => 'Upcoming Exam - ' . $exam['first_name'] . ' ' . $exam['last_name'],
                'message' => $exam['subject_name'] . ' exam on ' . date('M d, Y', strtotime($exam['exam_date'])),
                'icon' => 'clipboard-list',
                'action_url' => '/parent/results'
            ];
        }

        // Recent announcements
        $announcements = $instance->db->fetchAll(
            "SELECT title, content, created_at
             FROM announcements
             WHERE target_audience IN ('all', 'parents')
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY created_at DESC
             LIMIT 3"
        );

        foreach ($announcements as $announcement) {
            $notifications[] = [
                'type' => 'success',
                'title' => 'School Announcement',
                'message' => substr($announcement['title'], 0, 50) . '...',
                'icon' => 'bullhorn',
                'action_url' => '/parent/events'
            ];
        }

        return array_slice($notifications, 0, 8); // Limit to 8 notifications
    }

    /**
     * Get detailed child information
     */
    public static function getChildDetails($parentId, $childId) {
        $instance = new static();

        // Verify parent-child relationship
        $child = $instance->db->fetch(
            "SELECT s.*, c.class_name, c.section as class_section, c.academic_year
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.id = ? AND s.parent_id = ? AND s.is_active = 1",
            [$childId, $parentId]
        );

        if (!$child) {
            return null;
        }

        // Add comprehensive data
        $child['attendance'] = self::getChildAttendanceSummary($childId);
        $child['exam_results'] = self::getChildExamSummary($childId);
        $child['fee_status'] = self::getChildFeeStatus($childId);
        $child['subjects'] = self::getChildSubjects($childId);

        return $child;
    }

    /**
     * Get child subjects
     */
    private static function getChildSubjects($childId) {
        $instance = new static();
        $subjects = $instance->db->fetchAll(
            "SELECT cs.*, s.subject_name, s.subject_code,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name
             FROM class_subjects cs
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN teachers t ON cs.teacher_id = t.id
             LEFT JOIN students st ON cs.class_id = st.class_id
             WHERE st.id = ?
             ORDER BY s.subject_name",
            [$childId]
        );

        return $subjects ?: [];
    }

    /**
     * Get child attendance data for specific date range
     */
    public static function getChildAttendance($parentId, $childId, $startDate = null, $endDate = null) {
        $instance = new static();

        // Verify parent-child relationship
        $verification = $instance->db->fetch(
            "SELECT id FROM students WHERE id = ? AND parent_id = ? AND is_active = 1",
            [$childId, $parentId]
        );

        if (!$verification) {
            return [];
        }

        $query = "SELECT attendance_date, status, remarks
                  FROM attendance
                  WHERE student_id = ?";

        $params = [$childId];

        if ($startDate && $endDate) {
            $query .= " AND attendance_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $query .= " ORDER BY attendance_date DESC";

        $results = $instance->db->fetchAll($query, $params);

        return $results ?: [];
    }

    /**
     * Get monthly attendance summary for a child
     */
    public static function getChildMonthlyAttendanceSummary($parentId, $childId, $month, $year) {
        $instance = new static();

        // Verify parent-child relationship
        $verification = $instance->db->fetch(
            "SELECT id FROM students WHERE id = ? AND parent_id = ? AND is_active = 1",
            [$childId, $parentId]
        );

        if (!$verification) {
            return [
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'late_days' => 0,
                'percentage' => 0
            ];
        }

        $result = $instance->db->fetch(
            "SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$childId, $month, $year]
        );

        if ($result && $result['total_days'] > 0) {
            $percentage = round(($result['present_days'] / $result['total_days']) * 100, 1);
            return [
                'total_days' => (int)$result['total_days'],
                'present_days' => (int)$result['present_days'],
                'absent_days' => (int)$result['absent_days'],
                'late_days' => (int)$result['late_days'],
                'percentage' => $percentage
            ];
        }

        return [
            'total_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'percentage' => 0
        ];
    }

    /**
     * Get child exam results
     */
    public static function getChildResults($parentId, $childId, $examId = null) {
        $instance = new static();

        // Verify parent-child relationship
        $verification = $instance->db->fetch(
            "SELECT id FROM students WHERE id = ? AND parent_id = ? AND is_active = 1",
            [$childId, $parentId]
        );

        if (!$verification) {
            return [];
        }

        $query = "SELECT er.*, e.exam_name, e.exam_type, e.exam_date, s.subject_name,
                         CONCAT(t.first_name, ' ', t.last_name) as teacher_name
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN subjects s ON er.subject_id = s.id
                  LEFT JOIN teachers t ON e.teacher_id = t.id
                  WHERE er.student_id = ?";

        $params = [$childId];

        if ($examId) {
            $query .= " AND er.exam_id = ?";
            $params[] = $examId;
        }

        $query .= " ORDER BY e.exam_date DESC, s.subject_name ASC";

        $results = $instance->db->fetchAll($query, $params);

        return $results ?: [];
    }

    /**
     * Get child fee data
     */
    public static function getChildFees($parentId, $childId) {
        $instance = new static();

        // Verify parent-child relationship
        $verification = $instance->db->fetch(
            "SELECT id FROM students WHERE id = ? AND parent_id = ? AND is_active = 1",
            [$childId, $parentId]
        );

        if (!$verification) {
            return [];
        }

        $fees = $instance->db->fetchAll(
            "SELECT f.*, fp.amount as paid_amount, fp.payment_date, fp.receipt_number,
                    (f.amount - COALESCE(fp.amount, 0)) as remaining_amount
             FROM fees f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id AND fp.is_paid = 1
             WHERE f.student_id = ?
             ORDER BY f.due_date ASC",
            [$childId]
        );

        // Add payment history for each fee
        foreach ($fees as &$fee) {
            $fee['payments'] = $instance->db->fetchAll(
                "SELECT fp.* FROM fee_payments fp
                 WHERE fp.fee_id = ? AND fp.is_paid = 1
                 ORDER BY fp.payment_date DESC",
                [$fee['id']]
            ) ?: [];
        }

        return $fees ?: [];
    }

    /**
     * Get parent profile
     */
    public static function getParentProfile($parentId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT u.*, p.first_name, p.middle_name, p.last_name, p.mobile, p.email as parent_email,
                    p.permanent_address, p.temporary_address, p.occupation, p.relationship_to_student,
                    p.date_of_birth, p.gender, p.photo_path
             FROM users u
             LEFT JOIN parents p ON u.id = p.user_id
             WHERE u.id = ? AND u.role = 'parent'",
            [$parentId]
        );

        return $result ?: [];
    }

    /**
     * Update parent profile
     */
    public static function updateProfile($parentId, $data) {
        $instance = new static();

        // Check if parents table record exists
        $existing = $instance->db->fetch(
            "SELECT id FROM parents WHERE user_id = ?",
            [$parentId]
        );

        if ($existing) {
            // Update existing record
            $instance->db->update('parents', $data, ['user_id' => $parentId]);
        } else {
            // Create new record
            $data['user_id'] = $parentId;
            $instance->db->insert('parents', $data);
        }

        // Update users table if email is provided
        if (!empty($data['email'])) {
            $instance->db->update('users', ['email' => $data['email']], ['id' => $parentId]);
        }

        // Log activity
        self::logActivity($parentId, 'profile_updated', 'Profile information was updated', [
            'fields' => array_keys($data)
        ]);
    }

    /**
     * Log user activity
     */
    public static function logActivity($userId, $action, $description, $metadata = null) {
        $instance = new static();

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'metadata' => $metadata ? json_encode($metadata) : null
        ];

        $instance->db->insert('user_activity_logs', $data);
    }

    /**
     * Get user activity logs
     */
    public static function getActivityLogs($userId, $limit = 20) {
        $instance = new static();

        $logs = $instance->db->fetchAll(
            "SELECT * FROM user_activity_logs
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );

        // Format the logs for display
        foreach ($logs as &$log) {
            $log['time_ago'] = self::timeAgo($log['created_at']);
            $log['metadata'] = $log['metadata'] ? json_decode($log['metadata'], true) : null;
        }

        return $logs ?: [];
    }

    /**
     * Convert timestamp to "time ago" format
     */
    private static function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M d, Y', $time);
        }
    }
}