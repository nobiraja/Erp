<?php
/**
 * Admission Model
 * Handles admission information and fee structure data operations
 */

class AdmissionModel extends BaseModel {

    /**
     * Get fee structure by class
     */
    public static function getFeeStructure() {
        $query = "
            SELECT
                f.fee_type,
                f.amount,
                f.academic_year,
                c.class_name,
                c.section,
                COUNT(DISTINCT f.student_id) as students_count
            FROM fees f
            LEFT JOIN students s ON f.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE f.is_paid = 0
            GROUP BY f.fee_type, f.academic_year, c.class_name, c.section, f.amount
            ORDER BY CAST(c.class_name AS UNSIGNED), c.section, f.fee_type
        ";

        return self::query($query);
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
            'application_process' => self::getSetting('admission_process'),
            'interview_process' => self::getSetting('admission_interview'),
            'medical_requirements' => self::getSetting('admission_medical'),
            'transfer_requirements' => self::getSetting('admission_transfer')
        ];

        return $requirements;
    }

    /**
     * Get admission statistics
     */
    public static function getAdmissionStats() {
        $stats = [];

        // Total applications (if we had an applications table)
        // For now, we'll use student count as proxy
        $result = self::query("SELECT COUNT(*) as total FROM students WHERE admission_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        $stats['applications_this_year'] = $result[0]['total'] ?? 0;

        // Available seats by class
        $result = self::query("
            SELECT
                c.class_name,
                c.section,
                COUNT(s.id) as enrolled_students
            FROM classes c
            LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
            GROUP BY c.id, c.class_name, c.section
            ORDER BY CAST(c.class_name AS UNSIGNED), c.section
        ");
        $stats['class_capacity'] = $result;

        // Fee ranges
        $result = self::query("SELECT MIN(amount) as min_fee, MAX(amount) as max_fee FROM fees WHERE academic_year = (SELECT setting_value FROM settings WHERE setting_key = 'academic_year')");
        $stats['fee_range'] = $result[0] ?? ['min_fee' => 0, 'max_fee' => 0];

        return $stats;
    }

    /**
     * Get application process steps
     */
    public static function getApplicationProcess() {
        // Return default process steps since we don't have a dedicated table
        return [
            [
                'step' => 1,
                'title' => 'Online Application',
                'description' => 'Fill out the online application form with student and parent details.',
                'icon' => 'bi-file-earmark-text'
            ],
            [
                'step' => 2,
                'title' => 'Document Submission',
                'description' => 'Submit required documents including birth certificate, previous school records, and medical certificate.',
                'icon' => 'bi-upload'
            ],
            [
                'step' => 3,
                'title' => 'Entrance Assessment',
                'description' => 'Take the entrance examination and/or interview as per grade level requirements.',
                'icon' => 'bi-clipboard-check'
            ],
            [
                'step' => 4,
                'title' => 'Fee Payment',
                'description' => 'Pay the admission and first term fees to confirm seat reservation.',
                'icon' => 'bi-credit-card'
            ],
            [
                'step' => 5,
                'title' => 'Admission Confirmation',
                'description' => 'Receive admission confirmation letter and join orientation program.',
                'icon' => 'bi-check-circle'
            ]
        ];
    }

    /**
     * Get important dates
     */
    public static function getImportantDates() {
        return [
            [
                'event' => 'Application Start Date',
                'date' => date('M d, Y', strtotime('January 1')),
                'description' => 'Online applications open for new academic session'
            ],
            [
                'event' => 'Application Deadline',
                'date' => date('M d, Y', strtotime('March 31')),
                'description' => 'Last date to submit admission applications'
            ],
            [
                'event' => 'Entrance Tests',
                'date' => date('M d, Y', strtotime('April 15-20')),
                'description' => 'Written tests and interviews for eligible candidates'
            ],
            [
                'event' => 'Result Declaration',
                'date' => date('M d, Y', strtotime('April 30')),
                'description' => 'Admission test results and interview outcomes'
            ],
            [
                'event' => 'Fee Payment Deadline',
                'date' => date('M d, Y', strtotime('May 15')),
                'description' => 'Last date to pay admission fees and secure seat'
            ],
            [
                'event' => 'Orientation Program',
                'date' => date('M d, Y', strtotime('June 1')),
                'description' => 'Welcome program for new students and parents'
            ]
        ];
    }

    /**
     * Get setting value from database
     */
    private static function getSetting($key, $default = null) {
        try {
            $result = self::query("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            return $result[0]['setting_value'] ?? $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}