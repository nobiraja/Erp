<?php
/**
 * About Controller
 * Handles public about page functionality
 */

class AboutController extends BaseController {

    /**
     * Display about page
     */
    public function index() {
        try {
            // Get school information
            $schoolInfo = AboutModel::getSchoolInfo();

            // Get faculty profiles
            $faculty = AboutModel::getFacultyProfiles(12); // Limit to 12 faculty members

            // Get school statistics
            $stats = AboutModel::getSchoolStats();
        } catch (Exception $e) {
            // Handle database errors gracefully
            $schoolInfo = $this->getDefaultSchoolInfo();
            $faculty = [];
            $stats = $this->getDefaultStats();
        }

        // Get school settings for meta tags
        $schoolName = $schoolInfo['school_name'] ?? $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - About Us',
            'meta_description' => 'Learn about ' . $schoolName . ', our history, mission, vision, and meet our dedicated faculty members.',
            'meta_keywords' => 'about school, school history, mission, vision, faculty, teachers, education',
            'school_name' => $schoolName,
            'school_info' => $schoolInfo,
            'faculty' => $faculty,
            'stats' => $stats,
            'current_year' => date('Y')
        ];

        // Render about view
        echo $this->view('public.about.index', $data);
    }

    /**
     * AJAX endpoint to get faculty profiles
     */
    public function getFaculty() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $limit = $_GET['limit'] ?? null;
            $faculty = AboutModel::getFacultyProfiles($limit);
            $this->success($faculty);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * Get setting value from database
     */
    private function getSetting($key, $default = null) {
        try {
            $result = $this->db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = ?",
                [$key]
            );
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * Get default school information when database is not available
     */
    private function getDefaultSchoolInfo() {
        return [
            'school_name' => 'School Management System',
            'school_description' => 'A comprehensive school management system for modern educational institutions.',
            'school_history' => 'Founded with a vision to provide quality education, our school has been serving the community for over 25 years, continuously adapting to meet the evolving needs of students and parents.',
            'school_mission' => 'To provide holistic education that nurtures intellectual, emotional, and physical development of every student, preparing them to become responsible global citizens.',
            'school_vision' => 'To be a leading educational institution that inspires excellence, innovation, and character development in all our students.',
            'school_address' => '123 School Street, City, State 12345',
            'school_phone' => '+1 (555) 123-4567',
            'school_email' => 'info@school.com',
            'established_year' => date('Y') - 25,
            'principal_name' => 'Dr. John Smith',
            'total_students' => 1500,
            'total_teachers' => 50
        ];
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats() {
        return [
            'total_students' => 1500,
            'total_teachers' => 50,
            'total_classes' => 25,
            'avg_teacher_experience' => 8.5
        ];
    }
}