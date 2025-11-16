<?php
/**
 * Exam Model
 * Handles exam-related database operations
 */

class Exam extends BaseModel {
    protected $table = 'exams';
    protected $fillable = [
        'exam_name',
        'exam_type',
        'class_id',
        'academic_year',
        'start_date',
        'end_date',
        'created_by',
        'is_active'
    ];

    /**
     * Get exams by class
     */
    public static function getByClass($classId, $academicYear = null) {
        $query = self::where('class_id', $classId);

        if ($academicYear) {
            $query = $query->where('academic_year', $academicYear);
        }

        return $query->orderBy('start_date', 'DESC')->get();
    }

    /**
     * Get active exams
     */
    public static function getActive($classId = null) {
        $query = self::where('is_active', true);

        if ($classId) {
            $query = $query->where('class_id', $classId);
        }

        return $query->orderBy('start_date', 'ASC')->get();
    }

    /**
     * Get exam results for a specific exam
     */
    public function getResults() {
        $instance = new self();

        $sql = "SELECT er.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                       sub.subject_name, sub.subject_code
                FROM exam_results er
                LEFT JOIN students s ON er.student_id = s.id
                LEFT JOIN subjects sub ON er.subject_id = sub.id
                WHERE er.exam_id = ?
                ORDER BY s.first_name, s.last_name, sub.subject_name";

        return $instance->db->fetchAll($sql, [$this->id]);
    }

    /**
     * Get exam results grouped by student
     */
    public function getResultsByStudent() {
        $instance = new self();

        $sql = "SELECT s.id as student_id, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                       GROUP_CONCAT(er.marks_obtained ORDER BY sub.subject_name) as marks,
                       GROUP_CONCAT(er.max_marks ORDER BY sub.subject_name) as max_marks,
                       GROUP_CONCAT(sub.subject_name ORDER BY sub.subject_name) as subjects,
                       SUM(er.marks_obtained) as total_marks,
                       SUM(er.max_marks) as total_max_marks,
                       ROUND((SUM(er.marks_obtained) / SUM(er.max_marks)) * 100, 2) as percentage
                FROM exam_results er
                LEFT JOIN students s ON er.student_id = s.id
                LEFT JOIN subjects sub ON er.subject_id = sub.id
                WHERE er.exam_id = ?
                GROUP BY s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name
                ORDER BY s.first_name, s.last_name";

        return $instance->db->fetchAll($sql, [$this->id]);
    }

    /**
     * Calculate grades and ranks for exam results
     */
    public function calculateGradesAndRanks() {
        $results = $this->getResultsByStudent();

        // Sort by percentage for ranking
        usort($results, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        $rank = 1;
        $previousPercentage = null;

        foreach ($results as &$result) {
            // Calculate grade
            $result['grade'] = $this->calculateGrade($result['percentage']);

            // Calculate rank (handle ties)
            if ($previousPercentage !== null && $result['percentage'] == $previousPercentage) {
                $result['rank'] = $rank - 1; // Same rank for ties
            } else {
                $result['rank'] = $rank;
            }

            $previousPercentage = $result['percentage'];
            $rank++;
        }

        return $results;
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage) {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        if ($percentage >= 33) return 'D';
        return 'F';
    }

    /**
     * Get exam subjects with schedule
     */
    public function getSubjectsWithSchedule() {
        $instance = new self();

        $sql = "SELECT es.*, sub.subject_name, sub.subject_code
                FROM exam_subjects es
                LEFT JOIN subjects sub ON es.subject_id = sub.id
                WHERE es.exam_id = ?
                ORDER BY es.exam_date, es.start_time";

        return $instance->db->fetchAll($sql, [$this->id]);
    }

    /**
     * Get students for this exam
     */
    public function getStudents() {
        $instance = new self();

        $sql = "SELECT s.*, c.class_name, c.section
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.class_id = ? AND s.is_active = 1
                ORDER BY s.first_name, s.last_name";

        return $instance->db->fetchAll($sql, [$this->class_id]);
    }

    /**
     * Check if exam results are entered for a student
     */
    public function hasResults($studentId) {
        $instance = new self();

        $sql = "SELECT COUNT(*) as count FROM exam_results WHERE exam_id = ? AND student_id = ?";
        $result = $instance->db->fetch($sql, [$this->id, $studentId]);

        return $result['count'] > 0;
    }

    /**
     * Get class relationship
     */
    public function class() {
        return ClassModel::find($this->class_id);
    }

    /**
     * Get created by user relationship
     */
    public function createdBy() {
        return User::find($this->created_by);
    }

    /**
     * Get exam statistics
     */
    public function getStatistics() {
        $instance = new self();

        $sql = "SELECT
                    COUNT(DISTINCT er.student_id) as total_students,
                    COUNT(er.id) as total_results,
                    AVG(er.marks_obtained) as avg_marks,
                    MAX(er.marks_obtained) as highest_marks,
                    MIN(er.marks_obtained) as lowest_marks,
                    AVG((er.marks_obtained/er.max_marks)*100) as avg_percentage
                FROM exam_results er
                WHERE er.exam_id = ?";

        $stats = $instance->db->fetch($sql, [$this->id]);

        if ($stats) {
            $stats['avg_marks'] = round($stats['avg_marks'], 2);
            $stats['avg_percentage'] = round($stats['avg_percentage'], 2);
        }

        return $stats;
    }
}