<?php
/**
 * Subject Model
 * Handles subject-related database operations
 */

class SubjectModel extends BaseModel {
    protected $table = 'subjects';
    protected $fillable = ['subject_name', 'subject_code', 'description'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Get subject with class assignments
     */
    public static function withClasses($id) {
        $instance = new self();
        $result = $instance->db->fetch(
            "SELECT s.*, COUNT(cs.id) as class_count
             FROM subjects s
             LEFT JOIN class_subjects cs ON s.id = cs.subject_id
             WHERE s.id = ?
             GROUP BY s.id",
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
     * Get all subjects with class count
     */
    public static function allWithClassCount() {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT s.*, COUNT(cs.id) as class_count
             FROM subjects s
             LEFT JOIN class_subjects cs ON s.id = cs.subject_id
             GROUP BY s.id
             ORDER BY s.subject_name"
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
     * Get classes for this subject
     */
    public function classes() {
        $instance = new self();
        $query = "SELECT c.*, cs.teacher_id, t.first_name, t.last_name
                  FROM classes c
                  LEFT JOIN class_subjects cs ON c.id = cs.class_id
                  LEFT JOIN teachers t ON cs.teacher_id = t.id
                  WHERE cs.subject_id = ?
                  ORDER BY c.class_name, c.section";

        $results = $instance->db->fetchAll($query, [$this->id]);

        $classes = [];
        foreach ($results as $result) {
            $class = new ClassModel($result);
            $class->original = $result;
            $class->exists = true;
            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * Get teachers assigned to this subject
     */
    public function teachers() {
        $instance = new self();
        $query = "SELECT DISTINCT t.*, u.username
                  FROM teachers t
                  LEFT JOIN users u ON t.user_id = u.id
                  LEFT JOIN class_subjects cs ON t.id = cs.teacher_id
                  WHERE cs.subject_id = ?
                  ORDER BY t.first_name, t.last_name";

        $results = $instance->db->fetchAll($query, [$this->id]);

        $teachers = [];
        foreach ($results as $result) {
            $teacher = new Teacher($result);
            $teacher->original = $result;
            $teacher->exists = true;
            $teachers[] = $teacher;
        }

        return $teachers;
    }

    /**
     * Check if subject code exists
     */
    public static function codeExists($code, $excludeId = null) {
        $instance = new self();
        $query = "SELECT COUNT(*) as count FROM {$instance->table} WHERE subject_code = ?";
        $params = [$code];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $instance->db->fetch($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Search subjects
     */
    public static function search($query) {
        $instance = new self();
        $sql = "SELECT s.*, COUNT(cs.id) as class_count
                FROM subjects s
                LEFT JOIN class_subjects cs ON s.id = cs.subject_id
                WHERE s.subject_name LIKE ? OR s.subject_code LIKE ? OR s.description LIKE ?
                GROUP BY s.id
                ORDER BY s.subject_name";

        $searchParam = "%$query%";
        $results = $instance->db->fetchAll($sql, [$searchParam, $searchParam, $searchParam]);

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
     * Get subjects by teacher
     */
    public static function getByTeacher($teacherId) {
        $instance = new self();
        $query = "SELECT DISTINCT s.*, COUNT(cs.id) as class_count
                  FROM subjects s
                  LEFT JOIN class_subjects cs ON s.id = cs.subject_id
                  WHERE cs.teacher_id = ?
                  GROUP BY s.id
                  ORDER BY s.subject_name";

        $results = $instance->db->fetchAll($query, [$teacherId]);

        $subjects = [];
        foreach ($results as $result) {
            $subject = new self($result);
            $subject->original = $result;
            $subject->exists = true;
            $subjects[] = $subject;
        }

        return $subjects;
    }

    /**
     * Get subjects by class
     */
    public static function getByClass($classId) {
        $instance = new self();
        $query = "SELECT s.*, cs.teacher_id, t.first_name, t.last_name
                  FROM subjects s
                  LEFT JOIN class_subjects cs ON s.id = cs.subject_id
                  LEFT JOIN teachers t ON cs.teacher_id = t.id
                  WHERE cs.class_id = ?
                  ORDER BY s.subject_name";

        $results = $instance->db->fetchAll($query, [$classId]);

        $subjects = [];
        foreach ($results as $result) {
            $subject = new self($result);
            $subject->original = $result;
            $subject->exists = true;
            $subjects[] = $subject;
        }

        return $subjects;
    }
}