<?php
/**
 * Class Model
 * Handles class-related database operations
 */

class ClassModel extends BaseModel {
    protected $table = 'classes';
    protected $fillable = ['class_name', 'section', 'academic_year', 'class_teacher_id'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Get class with teacher information
     */
    public static function withTeacher($id) {
        $instance = new self();
        $result = $instance->db->fetch(
            "SELECT c.*, u.username as teacher_username, t.first_name, t.last_name
             FROM classes c
             LEFT JOIN teachers t ON c.class_teacher_id = t.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE c.id = ?",
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
     * Get all classes with teacher information
     */
    public static function allWithTeachers() {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT c.*, u.username as teacher_username, t.first_name, t.last_name
             FROM classes c
             LEFT JOIN teachers t ON c.class_teacher_id = t.id
             LEFT JOIN users u ON t.user_id = u.id
             ORDER BY c.class_name, c.section"
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
     * Get subjects for this class
     */
    public function subjects() {
        return ClassSubjectModel::where('class_id', $this->id)->get();
    }

    /**
     * Get students in this class
     */
    public function students() {
        return Student::getByClass($this->id, $this->section);
    }

    /**
     * Get class teacher
     */
    public function teacher() {
        if ($this->class_teacher_id) {
            return Teacher::find($this->class_teacher_id);
        }
        return null;
    }

    /**
     * Get classes by academic year
     */
    public static function byAcademicYear($year) {
        return self::where('academic_year', $year)->orderBy('class_name')->orderBy('section')->get();
    }

    /**
     * Get unique academic years
     */
    public static function getAcademicYears() {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC"
        );
        return array_column($results, 'academic_year');
    }

    /**
     * Get unique class names
     */
    public static function getClassNames() {
        $instance = new self();
        $results = $instance->db->fetchAll(
            "SELECT DISTINCT class_name FROM classes ORDER BY class_name"
        );
        return array_column($results, 'class_name');
    }

    /**
     * Search classes
     */
    public static function search($query, $academic_year = null) {
        $instance = new self();
        $sql = "SELECT c.*, u.username as teacher_username, t.first_name, t.last_name
                FROM classes c
                LEFT JOIN teachers t ON c.class_teacher_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE (c.class_name LIKE ? OR c.section LIKE ?)";
        $params = ["%$query%", "%$query%"];

        if ($academic_year) {
            $sql .= " AND c.academic_year = ?";
            $params[] = $academic_year;
        }

        $sql .= " ORDER BY c.class_name, c.section";

        $results = $instance->db->fetchAll($sql, $params);

        $models = [];
        foreach ($results as $result) {
            $model = new self($result);
            $model->original = $result;
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }
}