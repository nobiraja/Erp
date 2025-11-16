<?php
/**
 * About Model
 * Handles about page data operations
 */

class AboutModel extends BaseModel {

    /**
     * Get school information from settings
     */
    public static function getSchoolInfo() {
        $settings = [
            'school_name' => self::getSetting('school_name'),
            'school_description' => self::getSetting('school_description'),
            'school_history' => self::getSetting('school_history'),
            'school_mission' => self::getSetting('school_mission'),
            'school_vision' => self::getSetting('school_vision'),
            'school_address' => self::getSetting('school_address'),
            'school_phone' => self::getSetting('school_phone'),
            'school_email' => self::getSetting('school_email'),
            'established_year' => self::getSetting('established_year'),
            'principal_name' => self::getSetting('principal_name'),
            'total_students' => self::getSetting('total_students'),
            'total_teachers' => self::getSetting('total_teachers')
        ];

        return $settings;
    }

    /**
     * Get faculty profiles (teachers)
     */
    public static function getFacultyProfiles($limit = null) {
        $query = "
            SELECT
                t.id,
                t.first_name,
                t.middle_name,
                t.last_name,
                t.designation,
                t.qualification,
                t.specialization,
                t.experience_years,
                t.photo_path,
                t.email,
                GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') as subjects
            FROM teachers t
            LEFT JOIN class_subjects cs ON t.id = cs.teacher_id
            LEFT JOIN subjects s ON cs.subject_id = s.id
            WHERE t.is_active = 1
            GROUP BY t.id
            ORDER BY t.designation DESC, t.first_name ASC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query);
    }

    /**
     * Get school statistics
     */
    public static function getSchoolStats() {
        $stats = [];

        // Total students
        $result = self::query("SELECT COUNT(*) as total FROM students WHERE is_active = 1");
        $stats['total_students'] = $result[0]['total'] ?? 0;

        // Total teachers
        $result = self::query("SELECT COUNT(*) as total FROM teachers WHERE is_active = 1");
        $stats['total_teachers'] = $result[0]['total'] ?? 0;

        // Total classes
        $result = self::query("SELECT COUNT(DISTINCT class_name) as total FROM classes");
        $stats['total_classes'] = $result[0]['total'] ?? 0;

        // Years of experience (average)
        $result = self::query("SELECT AVG(experience_years) as avg_experience FROM teachers WHERE is_active = 1");
        $stats['avg_teacher_experience'] = round($result[0]['avg_experience'] ?? 0, 1);

        return $stats;
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