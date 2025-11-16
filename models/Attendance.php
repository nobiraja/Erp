<?php
/**
 * Attendance Model
 * Handles attendance-related database operations
 */

class Attendance extends BaseModel {
    protected $table = 'attendance';
    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'attendance_date',
        'status',
        'marked_by',
        'remarks'
    ];

    /**
     * Get attendance records for a specific date and class
     */
    public static function getByDateAndClass($date, $classId, $subjectId = null) {
        $query = self::where('attendance_date', $date)
                    ->where('class_id', $classId);

        if ($subjectId) {
            $query = $query->where('subject_id', $subjectId);
        }

        return $query->get();
    }

    /**
     * Get attendance records for a student within date range
     */
    public static function getStudentAttendance($studentId, $startDate = null, $endDate = null) {
        $query = self::where('student_id', $studentId);

        if ($startDate) {
            $query = $query->where('attendance_date', '>=', $startDate);
        }

        if ($endDate) {
            $query = $query->where('attendance_date', '<=', $endDate);
        }

        return $query->orderBy('attendance_date', 'DESC')->get();
    }

    /**
     * Get attendance summary for a class
     */
    public static function getClassAttendanceSummary($classId, $startDate = null, $endDate = null) {
        $instance = new self();

        $whereClause = "class_id = ?";
        $params = [$classId];

        if ($startDate) {
            $whereClause .= " AND attendance_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $whereClause .= " AND attendance_date <= ?";
            $params[] = $endDate;
        }

        $sql = "SELECT
                    student_id,
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
                FROM {$instance->table}
                WHERE {$whereClause}
                GROUP BY student_id
                ORDER BY attendance_percentage DESC";

        return $instance->db->fetchAll($sql, $params);
    }

    /**
     * Get monthly attendance trends
     */
    public static function getMonthlyTrends($classId = null, $year = null) {
        $instance = new self();

        $whereClause = "1=1";
        $params = [];

        if ($classId) {
            $whereClause .= " AND class_id = ?";
            $params[] = $classId;
        }

        if ($year) {
            $whereClause .= " AND YEAR(attendance_date) = ?";
            $params[] = $year;
        }

        $sql = "SELECT
                    DATE_FORMAT(attendance_date, '%Y-%m') as month,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                    ROUND(AVG(CASE
                        WHEN status = 'present' THEN 100
                        WHEN status = 'late' THEN 50
                        ELSE 0
                    END), 2) as avg_percentage
                FROM {$instance->table}
                WHERE {$whereClause}
                GROUP BY DATE_FORMAT(attendance_date, '%Y-%m')
                ORDER BY month";

        return $instance->db->fetchAll($sql, $params);
    }

    /**
     * Bulk insert attendance records
     */
    public static function bulkInsert($records) {
        if (empty($records)) {
            return false;
        }

        $instance = new self();
        return $instance->db->bulkInsert($instance->table, $records);
    }

    /**
     * Check if attendance already exists for student/date/subject
     */
    public static function attendanceExists($studentId, $date, $subjectId = null) {
        $query = self::where('student_id', $studentId)
                    ->where('attendance_date', $date);

        if ($subjectId) {
            $query = $query->where('subject_id', $subjectId);
        }

        return $query->first() !== null;
    }

    /**
     * Get attendance statistics for dashboard
     */
    public static function getDashboardStats($classId = null, $date = null) {
        $instance = new self();

        $whereClause = "1=1";
        $params = [];

        if ($classId) {
            $whereClause .= " AND class_id = ?";
            $params[] = $classId;
        }

        if ($date) {
            $whereClause .= " AND attendance_date = ?";
            $params[] = $date;
        } else {
            $whereClause .= " AND attendance_date = CURDATE()";
        }

        $sql = "SELECT
                    COUNT(*) as total_marked,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
                FROM {$instance->table}
                WHERE {$whereClause}";

        return $instance->db->fetch($sql, $params);
    }

    /**
     * Get student relationship
     */
    public function student() {
        return Student::find($this->student_id);
    }

    /**
     * Get class relationship
     */
    public function class() {
        return ClassModel::find($this->class_id);
    }

    /**
     * Get subject relationship
     */
    public function subject() {
        return $this->subject_id ? SubjectModel::find($this->subject_id) : null;
    }

    /**
     * Get marked by user relationship
     */
    public function markedBy() {
        return User::find($this->marked_by);
    }
}