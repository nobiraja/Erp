<?php
/**
 * Class Subject Model
 * Handles class-subject-teacher assignments
 */

class ClassSubjectModel extends BaseModel {
    protected $table = 'class_subjects';
    protected $fillable = ['class_id', 'subject_id', 'teacher_id'];
    protected $guarded = ['id', 'created_at'];

    /**
     * Get assignment with full details
     */
    public static function withDetails($id) {
        $instance = new self();
        $result = $instance->db->fetch(
            "SELECT cs.*, c.class_name, c.section, c.academic_year,
                    s.subject_name, s.subject_code,
                    t.first_name, t.last_name, t.employee_id
             FROM class_subjects cs
             LEFT JOIN classes c ON cs.class_id = c.id
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN teachers t ON cs.teacher_id = t.id
             WHERE cs.id = ?",
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
     * Get all assignments with details
     */
    public static function allWithDetails() {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT cs.*, c.class_name, c.section, c.academic_year,
                    s.subject_name, s.subject_code,
                    t.first_name, t.last_name, t.employee_id
             FROM class_subjects cs
             LEFT JOIN classes c ON cs.class_id = c.id
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN teachers t ON cs.teacher_id = t.id
             ORDER BY c.class_name, c.section, s.subject_name"
        );

        $models = [];
        foreach ($results as $result) {
            $model = new self($result);
            $model->original = $result;
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Get assignments by class
     */
    public static function getByClass($classId) {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT cs.*, s.subject_name, s.subject_code,
                    t.first_name, t.last_name, t.employee_id
             FROM class_subjects cs
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN teachers t ON cs.teacher_id = t.id
             WHERE cs.class_id = ?
             ORDER BY s.subject_name",
            [$classId]
        );

        $assignments = [];
        foreach ($results as $result) {
            $assignment = new self($result);
            $assignment->original = $result;
            $assignment->exists = true;
            $assignments[] = $assignment;
        }

        return $assignments;
    }

    /**
     * Get assignments by teacher
     */
    public static function getByTeacher($teacherId) {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT cs.*, c.class_name, c.section, c.academic_year,
                    s.subject_name, s.subject_code
             FROM class_subjects cs
             LEFT JOIN classes c ON cs.class_id = c.id
             LEFT JOIN subjects s ON cs.subject_id = s.id
             WHERE cs.teacher_id = ?
             ORDER BY c.class_name, c.section, s.subject_name",
            [$teacherId]
        );

        $assignments = [];
        foreach ($results as $result) {
            $assignment = new self($result);
            $assignment->original = $result;
            $assignment->exists = true;
            $assignments[] = $assignment;
        }

        return $assignments;
    }

    /**
     * Get assignments by subject
     */
    public static function getBySubject($subjectId) {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT cs.*, c.class_name, c.section, c.academic_year,
                    t.first_name, t.last_name, t.employee_id
             FROM class_subjects cs
             LEFT JOIN classes c ON cs.class_id = c.id
             LEFT JOIN teachers t ON cs.teacher_id = t.id
             WHERE cs.subject_id = ?
             ORDER BY c.class_name, c.section",
            [$subjectId]
        );

        $assignments = [];
        foreach ($results as $result) {
            $assignment = new self($result);
            $assignment->original = $result;
            $assignment->exists = true;
            $assignments[] = $assignment;
        }

        return $assignments;
    }

    /**
     * Check if assignment exists
     */
    public static function assignmentExists($classId, $subjectId, $excludeId = null) {
        $instance = new self();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE class_id = ? AND subject_id = ?";
        $params = [$classId, $subjectId];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Assign teacher to class-subject
     */
    public static function assignTeacher($classId, $subjectId, $teacherId) {
        $instance = new self();

        // Check if assignment exists
        $existing = $instance->db->fetch(
            "SELECT id FROM {$instance->table} WHERE class_id = ? AND subject_id = ?",
            [$classId, $subjectId]
        );

        if ($existing) {
            // Update existing assignment
            return $instance->db->update(
                $instance->table,
                ['teacher_id' => $teacherId],
                'id = ?',
                [$existing['id']]
            );
        } else {
            // Create new assignment
            return $instance->db->insert($instance->table, [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId
            ]);
        }
    }

    /**
     * Remove teacher assignment
     */
    public static function removeTeacher($classId, $subjectId) {
        $instance = new self();
        return $instance->db->update(
            $instance->table,
            ['teacher_id' => null],
            'class_id = ? AND subject_id = ?',
            [$classId, $subjectId]
        );
    }

    /**
     * Get class object
     */
    public function getClass() {
        return ClassModel::find($this->class_id);
    }

    /**
     * Get subject object
     */
    public function getSubject() {
        return SubjectModel::find($this->subject_id);
    }

    /**
     * Get teacher object
     */
    public function getTeacher() {
        if ($this->teacher_id) {
            return Teacher::find($this->teacher_id);
        }
        return null;
    }

    /**
     * Search assignments
     */
    public static function search($query) {
        $instance = new self();
        $sql = "SELECT cs.*, c.class_name, c.section, c.academic_year,
                       s.subject_name, s.subject_code,
                       t.first_name, t.last_name, t.employee_id
                FROM class_subjects cs
                LEFT JOIN classes c ON cs.class_id = c.id
                LEFT JOIN subjects s ON cs.subject_id = s.id
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                WHERE c.class_name LIKE ? OR c.section LIKE ? OR
                      s.subject_name LIKE ? OR s.subject_code LIKE ? OR
                      CONCAT(t.first_name, ' ', t.last_name) LIKE ?
                ORDER BY c.class_name, c.section, s.subject_name";

        $searchParam = "%$query%";
        $results = $instance->db->fetchAll($sql, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);

        $assignments = [];
        foreach ($results as $result) {
            $assignment = new self($result);
            $assignment->original = $result;
            $assignment->exists = true;
            $assignments[] = $assignment;
        }

        return $assignments;
    }
}