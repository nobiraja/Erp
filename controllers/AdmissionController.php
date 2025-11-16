<?php
/**
 * Admission Controller
 * Handles public admission page functionality
 */

class AdmissionController extends BaseController {

    /**
     * Display admission page
     */
    public function index() {
        try {
            // Get fee structure
            $feeStructure = AdmissionModel::getFeeStructure();

            // Get admission requirements
            $requirements = AdmissionModel::getAdmissionRequirements();

            // Get admission statistics
            $stats = AdmissionModel::getAdmissionStats();

            // Get application process
            $process = AdmissionModel::getApplicationProcess();

            // Get important dates
            $importantDates = AdmissionModel::getImportantDates();
        } catch (Exception $e) {
            // Handle database errors gracefully
            $feeStructure = $this->getDefaultFeeStructure();
            $requirements = $this->getDefaultRequirements();
            $stats = $this->getDefaultStats();
            $process = $this->getDefaultProcess();
            $importantDates = $this->getDefaultImportantDates();
        }

        // Get school settings for meta tags
        $schoolName = $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Admissions',
            'meta_description' => 'Apply for admission at ' . $schoolName . '. Learn about our admission process, requirements, fee structure, and important dates.',
            'meta_keywords' => 'admission, apply, school admission, fee structure, admission requirements, application process',
            'school_name' => $schoolName,
            'fee_structure' => $feeStructure,
            'requirements' => $requirements,
            'stats' => $stats,
            'process' => $process,
            'important_dates' => $importantDates,
            'current_year' => date('Y')
        ];

        // Render admission view
        echo $this->view('public.admission.index', $data);
    }

    /**
     * Handle admission application submission (future implementation)
     */
    public function apply() {
        // This would handle the actual application form submission
        // For now, redirect to contact page
        header('Location: /contact?success=' . urlencode('Thank you for your interest. Our admissions team will contact you soon.'));
        exit;
    }

    /**
     * Get fee structure via AJAX
     */
    public function getFees() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $fees = AdmissionModel::getFeeStructure();
            $this->success($fees);
        } catch (Exception $e) {
            $this->success($this->getDefaultFeeStructure());
        }
    }

    /**
     * Get admission requirements via AJAX
     */
    public function getRequirements() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $requirements = AdmissionModel::getAdmissionRequirements();
            $this->success($requirements);
        } catch (Exception $e) {
            $this->success($this->getDefaultRequirements());
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
     * Get default fee structure when database is not available
     */
    private function getDefaultFeeStructure() {
        return [
            [
                'fee_type' => 'Admission Fee',
                'amount' => 2000.00,
                'academic_year' => '2024-2025',
                'class_name' => 'All Classes',
                'students_count' => 0
            ],
            [
                'fee_type' => 'Tuition Fee (Primary)',
                'amount' => 1500.00,
                'academic_year' => '2024-2025',
                'class_name' => '1-5',
                'students_count' => 0
            ],
            [
                'fee_type' => 'Tuition Fee (Secondary)',
                'amount' => 2000.00,
                'academic_year' => '2024-2025',
                'class_name' => '6-10',
                'students_count' => 0
            ],
            [
                'fee_type' => 'Tuition Fee (Higher Secondary)',
                'amount' => 2500.00,
                'academic_year' => '2024-2025',
                'class_name' => '11-12',
                'students_count' => 0
            ]
        ];
    }

    /**
     * Get default admission requirements
     */
    private function getDefaultRequirements() {
        return [
            'age_criteria' => 'Children must be 5+ years for Class 1, and appropriate age for subsequent grades.',
            'documents_required' => 'Birth Certificate, Previous School Records, Transfer Certificate, Medical Certificate, Passport Size Photos (4), Aadhar Card',
            'eligibility_criteria' => 'Minimum 60% in previous grade, Good conduct certificate, Medical fitness certificate',
            'application_deadline' => 'March 31st for new academic session',
            'application_process' => 'Online application followed by document verification and entrance assessment',
            'interview_process' => 'Personal interview for classes 6 and above, parent interview mandatory',
            'medical_requirements' => 'Complete medical checkup certificate, vaccination records, no contagious diseases',
            'transfer_requirements' => 'Original Transfer Certificate, Progress Report, Character Certificate from previous school'
        ];
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats() {
        return [
            'applications_this_year' => 250,
            'class_capacity' => [
                ['class_name' => '1', 'section' => 'A', 'enrolled_students' => 25],
                ['class_name' => '5', 'section' => 'A', 'enrolled_students' => 22],
                ['class_name' => '10', 'section' => 'A', 'enrolled_students' => 28]
            ],
            'fee_range' => ['min_fee' => 1500, 'max_fee' => 3000]
        ];
    }

    /**
     * Get default application process
     */
    private function getDefaultProcess() {
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
     * Get default important dates
     */
    private function getDefaultImportantDates() {
        return [
            [
                'event' => 'Application Start Date',
                'date' => 'Jan 01, ' . date('Y'),
                'description' => 'Online applications open for new academic session'
            ],
            [
                'event' => 'Application Deadline',
                'date' => 'Mar 31, ' . date('Y'),
                'description' => 'Last date to submit admission applications'
            ],
            [
                'event' => 'Entrance Tests',
                'date' => 'Apr 15-20, ' . date('Y'),
                'description' => 'Written tests and interviews for eligible candidates'
            ],
            [
                'event' => 'Result Declaration',
                'date' => 'Apr 30, ' . date('Y'),
                'description' => 'Admission test results and interview outcomes'
            ],
            [
                'event' => 'Fee Payment Deadline',
                'date' => 'May 15, ' . date('Y'),
                'description' => 'Last date to pay admission fees and secure seat'
            ],
            [
                'event' => 'Orientation Program',
                'date' => 'Jun 01, ' . date('Y'),
                'description' => 'Welcome program for new students and parents'
            ]
        ];
    }
}