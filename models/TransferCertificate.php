<?php
/**
 * Transfer Certificate Model
 * Handles transfer certificate data operations
 */

class TransferCertificate extends BaseModel {
    protected $table = 'transfer_certificates';
    protected $fillable = [
        'student_id',
        'certificate_number',
        'issue_date',
        'transfer_reason',
        'transfer_to_school',
        'academic_record',
        'conduct_grade',
        'remarks',
        'issued_by',
        'class_teacher_id',
        'principal_id',
        'is_active'
    ];

    /**
     * Get transfer certificate with student details
     */
    public static function withDetails($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT tc.*, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                    s.father_name, s.mother_name, s.dob, s.admission_date,
                    c.class_name, c.section, c.academic_year,
                    u.username as issued_by_name,
                    ct.first_name as class_teacher_first_name, ct.last_name as class_teacher_last_name,
                    p.first_name as principal_first_name, p.last_name as principal_last_name
             FROM {$instance->table} tc
             LEFT JOIN students s ON tc.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             LEFT JOIN users u ON tc.issued_by = u.id
             LEFT JOIN teachers ct ON tc.class_teacher_id = ct.id
             LEFT JOIN users pu ON tc.principal_id = pu.id
             LEFT JOIN teachers p ON pu.id = p.user_id
             WHERE tc.id = ?",
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
     * Get transfer certificates by student
     */
    public static function getByStudent($studentId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT tc.*, u.username as issued_by_name
             FROM {$instance->table} tc
             LEFT JOIN users u ON tc.issued_by = u.id
             WHERE tc.student_id = ? AND tc.is_active = 1
             ORDER BY tc.issue_date DESC",
            [$studentId]
        );

        $certificates = [];
        foreach ($results as $result) {
            $certificate = new static($result);
            $certificate->original = $result;
            $certificate->exists = true;
            $certificates[] = $certificate;
        }

        return $certificates;
    }

    /**
     * Get all transfer certificates with pagination
     */
    public static function getAllWithDetails($page = 1, $perPage = 10, $search = '') {
        $instance = new static();
        $offset = ($page - 1) * $perPage;

        $whereClause = "tc.is_active = 1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ? OR tc.certificate_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        $query = "SELECT tc.*, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                         s.father_name, c.class_name, c.section,
                         u.username as issued_by_name
                  FROM {$instance->table} tc
                  LEFT JOIN students s ON tc.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN users u ON tc.issued_by = u.id
                  WHERE {$whereClause}
                  ORDER BY tc.issue_date DESC
                  LIMIT {$perPage} OFFSET {$offset}";

        $results = $instance->db->fetchAll($query, $params);

        $certificates = [];
        foreach ($results as $result) {
            $certificate = new static($result);
            $certificate->original = $result;
            $certificate->exists = true;
            $certificates[] = $certificate;
        }

        return $certificates;
    }

    /**
     * Get total count for pagination
     */
    public static function getTotalCount($search = '') {
        $instance = new static();

        $whereClause = "tc.is_active = 1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ? OR tc.certificate_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        $query = "SELECT COUNT(*) as count
                  FROM {$instance->table} tc
                  LEFT JOIN students s ON tc.student_id = s.id
                  WHERE {$whereClause}";

        $result = $instance->db->fetch($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Generate next certificate number
     */
    public static function generateCertificateNumber() {
        $instance = new static();
        $year = date('Y');

        // Get the last certificate number for this year
        $result = $instance->db->fetch(
            "SELECT certificate_number FROM {$instance->table}
             WHERE certificate_number LIKE ?
             ORDER BY id DESC LIMIT 1",
            ["TC{$year}%"]
        );

        if ($result) {
            // Extract the sequential number and increment
            $lastNumber = intval(substr($result['certificate_number'], 6));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('TC%s%04d', $year, $nextNumber);
    }

    /**
     * Check if certificate number exists
     */
    public static function certificateNumberExists($certificateNumber, $excludeId = null) {
        $instance = new static();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE certificate_number = ?";
        $params = [$certificateNumber];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Get student academic record for transfer certificate
     */
    public function getAcademicRecord($studentId) {
        // Get student's exam results and attendance
        $examResults = $this->db->fetchAll(
            "SELECT e.exam_name, e.exam_type, er.marks_obtained, er.max_marks, er.percentage,
                    s.subject_name, c.class_name, c.section
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             LEFT JOIN subjects s ON er.subject_id = s.id
             LEFT JOIN students st ON er.student_id = st.id
             LEFT JOIN classes c ON st.class_id = c.id
             WHERE er.student_id = ?
             ORDER BY e.start_date DESC, s.subject_name",
            [$studentId]
        );

        // Get attendance summary
        $attendance = $this->db->fetch(
            "SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
             FROM attendance
             WHERE student_id = ? AND YEAR(attendance_date) = YEAR(CURDATE())",
            [$studentId]
        );

        $record = [];

        if (!empty($examResults)) {
            $record[] = "Academic Performance:";

            // Group by exam
            $exams = [];
            foreach ($examResults as $result) {
                $examKey = $result['exam_name'] . ' (' . $result['exam_type'] . ')';
                if (!isset($exams[$examKey])) {
                    $exams[$examKey] = [];
                }
                $exams[$examKey][] = $result;
            }

            foreach ($exams as $examName => $subjects) {
                $totalMarks = array_sum(array_column($subjects, 'marks_obtained'));
                $totalMax = array_sum(array_column($subjects, 'max_marks'));
                $percentage = $totalMax > 0 ? round(($totalMarks / $totalMax) * 100, 2) : 0;

                $record[] = "- {$examName}: {$totalMarks}/{$totalMax} ({$percentage}%)";
            }
        }

        if ($attendance && $attendance['total_days'] > 0) {
            $attendancePercentage = round(($attendance['present_days'] / $attendance['total_days']) * 100, 2);
            $record[] = "Attendance: {$attendance['present_days']}/{$attendance['total_days']} days ({$attendancePercentage}%)";
        }

        return implode("\n", $record);
    }

    /**
     * Get conduct grade text
     */
    public function getConductGradeText() {
        $grades = [
            'excellent' => 'Excellent',
            'very_good' => 'Very Good',
            'good' => 'Good',
            'satisfactory' => 'Satisfactory',
            'needs_improvement' => 'Needs Improvement'
        ];

        return $grades[$this->conduct_grade] ?? 'Good';
    }

    /**
     * Get student full name
     */
    public function getStudentFullName() {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }
}