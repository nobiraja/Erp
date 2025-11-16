<?php
/**
 * Courses Model
 * Handles courses and curriculum data operations
 */

class CoursesModel extends BaseModel {

    /**
     * Get all classes with their subjects
     */
    public static function getAllClassesWithSubjects() {
        $query = "
            SELECT
                c.id,
                c.class_name,
                c.section,
                c.academic_year,
                c.class_teacher_id,
                t.first_name as teacher_first_name,
                t.last_name as teacher_last_name,
                GROUP_CONCAT(DISTINCT s.subject_name ORDER BY s.subject_name SEPARATOR ', ') as subjects,
                COUNT(DISTINCT cs.subject_id) as subject_count
            FROM classes c
            LEFT JOIN teachers t ON c.class_teacher_id = t.id
            LEFT JOIN class_subjects cs ON c.id = cs.class_id
            LEFT JOIN subjects s ON cs.subject_id = s.id
            GROUP BY c.id, c.class_name, c.section, c.academic_year, c.class_teacher_id, t.first_name, t.last_name
            ORDER BY CAST(c.class_name AS UNSIGNED), c.section
        ";

        return self::query($query);
    }

    /**
     * Get subjects by class
     */
    public static function getSubjectsByClass($classId) {
        $query = "
            SELECT
                s.id,
                s.subject_name,
                s.subject_code,
                s.description,
                t.first_name,
                t.last_name,
                t.designation
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.id
            LEFT JOIN teachers t ON cs.teacher_id = t.id
            WHERE cs.class_id = ?
            ORDER BY s.subject_name
        ";

        return self::query($query, [$classId]);
    }

    /**
     * Get all subjects
     */
    public static function getAllSubjects() {
        return self::where('subjects')
                  ->orderBy('subject_name')
                  ->get();
    }

    /**
     * Get curriculum information
     */
    public static function getCurriculumInfo() {
        $curriculum = [
            'primary' => self::getSetting('curriculum_primary'),
            'secondary' => self::getSetting('curriculum_secondary'),
            'higher_secondary' => self::getSetting('curriculum_higher_secondary'),
            'admission_requirements' => self::getSetting('admission_requirements'),
            'academic_year' => self::getSetting('academic_year')
        ];

        return $curriculum;
    }

    /**
     * Get class statistics
     */
    public static function getClassStats() {
        $stats = [];

        // Total classes
        $result = self::query("SELECT COUNT(DISTINCT CONCAT(class_name, section)) as total FROM classes");
        $stats['total_classes'] = $result[0]['total'] ?? 0;

        // Total subjects
        $result = self::query("SELECT COUNT(*) as total FROM subjects");
        $stats['total_subjects'] = $result[0]['total'] ?? 0;

        // Classes by level
        $result = self::query("SELECT COUNT(*) as total FROM classes WHERE CAST(class_name AS UNSIGNED) <= 5");
        $stats['primary_classes'] = $result[0]['total'] ?? 0;

        $result = self::query("SELECT COUNT(*) as total FROM classes WHERE CAST(class_name AS UNSIGNED) BETWEEN 6 AND 10");
        $stats['secondary_classes'] = $result[0]['total'] ?? 0;

        $result = self::query("SELECT COUNT(*) as total FROM classes WHERE CAST(class_name AS UNSIGNED) BETWEEN 11 AND 12");
        $stats['higher_secondary_classes'] = $result[0]['total'] ?? 0;

        return $stats;
    }

    /**
     * Get admission requirements
     */
    public static function getAdmissionRequirements() {
        $requirements = [
            'age_criteria' => self::getSetting('admission_age_criteria'),
            'documents_required' => self::getSetting('admission_documents'),
            'eligibility_criteria' => self::getSetting('admission_eligibility'),
            'application_deadline' => self::getSetting('admission_deadline'),
            'fee_structure' => self::getSetting('admission_fee_structure')
        ];

        return $requirements;
    }

    /**
     * Get setting value from database
     */
    private static function getSetting($key) {
        try {
            $result = self::query("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            return $result[0]['setting_value'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}