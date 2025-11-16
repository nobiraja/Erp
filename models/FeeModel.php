<?php
/**
 * Fee Model
 * Handles fee structure and management operations
 */

class FeeModel extends BaseModel {
    protected $table = 'fees';
    protected $fillable = [
        'student_id',
        'fee_type',
        'amount',
        'due_date',
        'academic_year',
        'description',
        'is_paid',
        'created_by'
    ];

    /**
     * Get fees with student information
     */
    public static function withStudent($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT f.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                    s.admission_number, c.class_name, c.section
             FROM {$instance->table} f
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE f.id = ?",
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
     * Get fees by student
     */
    public static function getByStudent($studentId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT f.*, fp.amount_paid, fp.payment_date, fp.payment_mode, fp.receipt_number
             FROM {$instance->table} f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id
             WHERE f.student_id = ?
             ORDER BY f.due_date DESC, fp.payment_date DESC",
            [$studentId]
        );

        $fees = [];
        foreach ($results as $result) {
            $fee = new static($result);
            $fee->original = $result;
            $fee->exists = true;
            $fees[] = $fee;
        }

        return $fees;
    }

    /**
     * Get outstanding fees
     */
    public static function getOutstandingFees($classId = null, $section = null, $village = null) {
        $instance = new static();
        $query = "SELECT f.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         s.admission_number, s.father_name, s.mobile, s.village_address,
                         c.class_name, c.section,
                         DATEDIFF(CURDATE(), f.due_date) as days_overdue
                  FROM {$instance->table} f
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE f.is_paid = 0 AND s.is_active = 1";

        $params = [];

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        if ($village) {
            $query .= " AND s.village_address LIKE ?";
            $params[] = "%{$village}%";
        }

        $query .= " ORDER BY f.due_date ASC, s.first_name, s.last_name";

        $results = $instance->db->fetchAll($query, $params);

        $fees = [];
        foreach ($results as $result) {
            $fee = new static($result);
            $fee->original = $result;
            $fee->exists = true;
            $fees[] = $fee;
        }

        return $fees;
    }

    /**
     * Get fee statistics
     */
    public static function getFeeStats($academicYear = null) {
        if (!$academicYear) {
            $academicYear = date('Y') . '-' . (date('Y') + 1);
        }

        $instance = new static();
        $query = "SELECT
                    SUM(amount) as total_fees,
                    SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as collected_fees,
                    COUNT(*) as total_students,
                    SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as pending_students
                  FROM {$instance->table}
                  WHERE academic_year = ?";

        $result = $instance->db->fetch($query, [$academicYear]);

        return [
            'total_fees' => $result['total_fees'] ?? 0,
            'collected_fees' => $result['collected_fees'] ?? 0,
            'pending_fees' => ($result['total_fees'] ?? 0) - ($result['collected_fees'] ?? 0),
            'collection_percentage' => ($result['total_fees'] ?? 0) > 0 ?
                round((($result['collected_fees'] ?? 0) / ($result['total_fees'] ?? 0)) * 100, 2) : 0,
            'total_students' => $result['total_students'] ?? 0,
            'pending_students' => $result['pending_students'] ?? 0
        ];
    }

    /**
     * Get fees by class
     */
    public static function getByClass($classId, $section = null, $academicYear = null) {
        if (!$academicYear) {
            $academicYear = date('Y') . '-' . (date('Y') + 1);
        }

        $instance = new static();
        $query = "SELECT f.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         s.admission_number, s.father_name, s.mobile
                  FROM {$instance->table} f
                  LEFT JOIN students s ON f.student_id = s.id
                  WHERE s.class_id = ? AND f.academic_year = ? AND s.is_active = 1";

        $params = [$classId, $academicYear];

        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        $query .= " ORDER BY s.first_name, s.last_name, f.fee_type";

        $results = $instance->db->fetchAll($query, $params);

        $fees = [];
        foreach ($results as $result) {
            $fee = new static($result);
            $fee->original = $result;
            $fee->exists = true;
            $fees[] = $fee;
        }

        return $fees;
    }

    /**
     * Create fee structure for class
     */
    public static function createFeeStructure($classId, $feeData, $createdBy) {
        $instance = new static();
        $createdFees = [];

        foreach ($feeData as $fee) {
            $feeRecord = new static([
                'student_id' => $fee['student_id'],
                'fee_type' => $fee['fee_type'],
                'amount' => $fee['amount'],
                'due_date' => $fee['due_date'],
                'academic_year' => $fee['academic_year'] ?? date('Y') . '-' . (date('Y') + 1),
                'description' => $fee['description'] ?? '',
                'is_paid' => 0,
                'created_by' => $createdBy
            ]);

            if ($feeRecord->save()) {
                $createdFees[] = $feeRecord;
            }
        }

        return $createdFees;
    }

    /**
     * Mark fee as paid
     */
    public function markAsPaid() {
        $this->is_paid = 1;
        return $this->save();
    }

    /**
     * Get student full name
     */
    public function getStudentName() {
        if (isset($this->attributes['first_name'])) {
            $name = $this->attributes['first_name'];
            if ($this->attributes['middle_name']) {
                $name .= ' ' . $this->attributes['middle_name'];
            }
            $name .= ' ' . $this->attributes['last_name'];
            return $name;
        }
        return '';
    }

    /**
     * Get fee status text
     */
    public function getStatusText() {
        return $this->is_paid ? 'Paid' : 'Pending';
    }

    /**
     * Get fee status badge class
     */
    public function getStatusBadgeClass() {
        return $this->is_paid ? 'badge bg-success' : 'badge bg-warning';
    }

    /**
     * Check if fee is overdue
     */
    public function isOverdue() {
        if ($this->is_paid) {
            return false;
        }

        $dueDate = new DateTime($this->due_date);
        $today = new DateTime();
        return $today > $dueDate;
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue() {
        if (!$this->isOverdue()) {
            return 0;
        }

        $dueDate = new DateTime($this->due_date);
        $today = new DateTime();
        return $today->diff($dueDate)->days;
    }
}