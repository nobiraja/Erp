<?php
/**
 * Student Model
 * Handles student data operations
 */

class Student extends BaseModel {
    protected $table = 'students';
    protected $fillable = [
        'user_id',
        'scholar_number',
        'admission_number',
        'admission_date',
        'first_name',
        'middle_name',
        'last_name',
        'class_id',
        'section',
        'father_name',
        'mother_name',
        'guardian_name',
        'guardian_contact',
        'dob',
        'gender',
        'caste_category',
        'nationality',
        'religion',
        'blood_group',
        'village_address',
        'permanent_address',
        'temporary_address',
        'mobile',
        'email',
        'aadhar',
        'samagra',
        'apaar_id',
        'pan',
        'previous_school',
        'medical_conditions',
        'photo_path',
        'is_active'
    ];

    /**
     * Get student with class information
     */
    public static function withClass($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT s.*, c.class_name, c.section as class_section, c.academic_year
             FROM {$instance->table} s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.id = ?",
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
     * Get students by class
     */
    public static function getByClass($classId, $section = null) {
        $instance = new static();
        $query = "SELECT s.*, c.class_name, c.section as class_section
                  FROM {$instance->table} s
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE s.class_id = ? AND s.is_active = 1";

        $params = [$classId];

        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $results = $instance->db->fetchAll($query, $params);

        $students = [];
        foreach ($results as $result) {
            $student = new static($result);
            $student->original = $result;
            $student->exists = true;
            $students[] = $student;
        }

        return $students;
    }

    /**
     * Search students
     */
    public static function search($searchTerm, $classId = null, $section = null) {
        $instance = new static();
        $query = "SELECT s.*, c.class_name, c.section as class_section
                  FROM {$instance->table} s
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE s.is_active = 1 AND (
                      s.first_name LIKE ? OR
                      s.middle_name LIKE ? OR
                      s.last_name LIKE ? OR
                      s.scholar_number LIKE ? OR
                      s.admission_number LIKE ? OR
                      s.father_name LIKE ? OR
                      s.mother_name LIKE ? OR
                      CONCAT(s.first_name, ' ', s.last_name) LIKE ?
                  )";

        $searchParam = "%{$searchTerm}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam];

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $results = $instance->db->fetchAll($query, $params);

        $students = [];
        foreach ($results as $result) {
            $student = new static($result);
            $student->original = $result;
            $student->exists = true;
            $students[] = $student;
        }

        return $students;
    }

    /**
     * Get active students count
     */
    public static function getActiveCount() {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT COUNT(*) as count FROM {$instance->table} WHERE is_active = 1"
        );
        return $result['count'] ?? 0;
    }

    /**
     * Get students by gender
     */
    public static function getGenderStats() {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT gender, COUNT(*) as count FROM {$instance->table} WHERE is_active = 1 GROUP BY gender"
        );

        $stats = ['male' => 0, 'female' => 0, 'other' => 0];
        foreach ($results as $result) {
            $stats[$result['gender']] = $result['count'];
        }

        return $stats;
    }

    /**
     * Get full name
     */
    public function getFullName() {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

    /**
     * Get age
     */
    public function getAge() {
        if (!$this->dob) {
            return null;
        }

        $birthDate = new DateTime($this->dob);
        $today = new DateTime();
        return $today->diff($birthDate)->y;
    }

    /**
     * Check if scholar number exists
     */
    public static function scholarNumberExists($scholarNumber, $excludeId = null) {
        $instance = new static();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE scholar_number = ?";
        $params = [$scholarNumber];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Check if admission number exists
     */
    public static function admissionNumberExists($admissionNumber, $excludeId = null) {
        $instance = new static();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE admission_number = ?";
        $params = [$admissionNumber];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Get student attendance percentage
     */
    public function getAttendancePercentage($month = null, $year = null) {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');

        $instance = new static();
        $query = "SELECT
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
                  FROM attendance
                  WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";

        $result = $instance->db->fetch($query, [$this->id, $month, $year]);

        if ($result['total_days'] > 0) {
            return round(($result['present_days'] / $result['total_days']) * 100, 2);
        }

        return 0;
    }

    /**
     * Get student fees status
     */
    public function getFeesStatus() {
        $instance = new static();
        $query = "SELECT
                    SUM(amount) as total_fees,
                    SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as paid_fees
                  FROM fees
                  WHERE student_id = ?";

        $result = $instance->db->fetch($query, [$this->id]);

        $total = $result['total_fees'] ?? 0;
        $paid = $result['paid_fees'] ?? 0;
        $pending = $total - $paid;

        return [
            'total' => $total,
            'paid' => $paid,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($paid / $total) * 100, 2) : 0
        ];
    }
}