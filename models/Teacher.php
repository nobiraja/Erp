<?php
/**
 * Teacher Model
 * Handles teacher data operations
 */

class Teacher extends BaseModel {
    protected $table = 'teachers';
    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'gender',
        'marital_status',
        'blood_group',
        'qualification',
        'specialization',
        'designation',
        'department',
        'date_of_joining',
        'experience_years',
        'permanent_address',
        'temporary_address',
        'mobile',
        'email',
        'aadhar',
        'pan',
        'samagra_id',
        'medical_conditions',
        'photo_path',
        'is_active'
    ];

    /**
     * Get teacher with user information
     */
    public static function withUser($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT t.*, u.username, u.email as user_email, u.role_id, r.role_name
             FROM {$instance->table} t
             LEFT JOIN users u ON t.user_id = u.id
             LEFT JOIN user_roles r ON u.role_id = r.id
             WHERE t.id = ?",
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
     * Get teachers by department
     */
    public static function getByDepartment($department, $activeOnly = true) {
        $instance = new static();
        $query = "SELECT t.*, u.username, u.email as user_email
                  FROM {$instance->table} t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.department = ?";

        $params = [$department];

        if ($activeOnly) {
            $query .= " AND t.is_active = 1";
        }

        $query .= " ORDER BY t.first_name, t.last_name";

        $results = $instance->db->fetchAll($query, $params);

        $teachers = [];
        foreach ($results as $result) {
            $teacher = new static($result);
            $teacher->original = $result;
            $teacher->exists = true;
            $teachers[] = $teacher;
        }

        return $teachers;
    }

    /**
     * Search teachers
     */
    public static function search($searchTerm, $department = null, $designation = null) {
        $instance = new static();
        $query = "SELECT t.*, u.username, u.email as user_email
                  FROM {$instance->table} t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.is_active = 1 AND (
                      t.first_name LIKE ? OR
                      t.middle_name LIKE ? OR
                      t.last_name LIKE ? OR
                      t.employee_id LIKE ? OR
                      t.email LIKE ? OR
                      CONCAT(t.first_name, ' ', t.last_name) LIKE ?
                  )";

        $searchParam = "%{$searchTerm}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam];

        if ($department) {
            $query .= " AND t.department = ?";
            $params[] = $department;
        }

        if ($designation) {
            $query .= " AND t.designation = ?";
            $params[] = $designation;
        }

        $query .= " ORDER BY t.first_name, t.last_name";

        $results = $instance->db->fetchAll($query, $params);

        $teachers = [];
        foreach ($results as $result) {
            $teacher = new static($result);
            $teacher->original = $result;
            $teacher->exists = true;
            $teachers[] = $teacher;
        }

        return $teachers;
    }

    /**
     * Get active teachers count
     */
    public static function getActiveCount() {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT COUNT(*) as count FROM {$instance->table} WHERE is_active = 1"
        );
        return $result['count'] ?? 0;
    }

    /**
     * Get teachers by gender
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
     * Check if employee ID exists
     */
    public static function employeeIdExists($employeeId, $excludeId = null) {
        $instance = new static();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE employee_id = ?";
        $params = [$employeeId];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Get teacher's assigned subjects
     */
    public function getAssignedSubjects() {
        $instance = new static();
        $query = "SELECT cs.*, c.class_name, c.section, s.subject_name, s.subject_code
                  FROM class_subjects cs
                  LEFT JOIN classes c ON cs.class_id = c.id
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  WHERE cs.teacher_id = ?
                  ORDER BY c.class_name, c.section, s.subject_name";

        return $instance->db->fetchAll($query, [$this->id]);
    }

    /**
     * Get teacher's workload (total classes per week)
     */
    public function getWorkload() {
        $subjects = $this->getAssignedSubjects();
        return count($subjects);
    }

    /**
     * Get teacher's performance metrics
     */
    public function getPerformanceMetrics($month = null, $year = null) {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');

        $instance = new static();

        // Get attendance marked by this teacher
        $query = "SELECT COUNT(*) as total_sessions FROM attendance WHERE marked_by = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";
        $result = $instance->db->fetch($query, [$this->user_id, $month, $year]);
        $totalSessions = $result['total_sessions'] ?? 0;

        // Get exam results entered by this teacher
        $query = "SELECT COUNT(*) as total_results FROM exam_results WHERE entered_by = ?";
        $result = $instance->db->fetch($query, [$this->user_id]);
        $totalResults = $result['total_results'] ?? 0;

        return [
            'total_sessions' => $totalSessions,
            'total_results_entered' => $totalResults,
            'workload' => $this->getWorkload(),
            'assigned_subjects' => count($this->getAssignedSubjects())
        ];
    }

    /**
     * Assign subject to teacher
     */
    public function assignSubject($classId, $subjectId) {
        $instance = new static();
        return $instance->db->insert('class_subjects', [
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => $this->id
        ]);
    }

    /**
     * Remove subject assignment
     */
    public function removeSubjectAssignment($classId, $subjectId) {
        $instance = new static();
        return $instance->db->delete(
            'class_subjects',
            'class_id = ? AND subject_id = ? AND teacher_id = ?',
            [$classId, $subjectId, $this->id]
        );
    }
}