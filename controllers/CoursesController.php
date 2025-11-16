<?php
/**
 * Courses Controller
 * Handles public courses and curriculum page functionality
 */

class CoursesController extends BaseController {

    /**
     * Display courses page
     */
    public function index() {
        try {
            // Get all classes with subjects
            $classes = CoursesModel::getAllClassesWithSubjects();

            // Get curriculum information
            $curriculum = CoursesModel::getCurriculumInfo();

            // Get class statistics
            $stats = CoursesModel::getClassStats();

            // Get admission requirements
            $admissionReqs = CoursesModel::getAdmissionRequirements();
        } catch (Exception $e) {
            // Handle database errors gracefully
            $classes = $this->getDefaultClasses();
            $curriculum = $this->getDefaultCurriculum();
            $stats = $this->getDefaultStats();
            $admissionReqs = $this->getDefaultAdmissionReqs();
        }

        // Get school settings for meta tags
        $schoolName = $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Academic Programs & Courses',
            'meta_description' => 'Explore our comprehensive academic programs, curriculum details, and admission requirements at ' . $schoolName . '. Quality education for all grades.',
            'meta_keywords' => 'courses, curriculum, academic programs, subjects, admission requirements, school programs, education',
            'school_name' => $schoolName,
            'classes' => $classes,
            'curriculum' => $curriculum,
            'stats' => $stats,
            'admission_requirements' => $admissionReqs,
            'current_year' => date('Y')
        ];

        // Render courses view
        echo $this->view('public.courses.index', $data);
    }

    /**
     * AJAX endpoint to get subjects for a specific class
     */
    public function getSubjects() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $classId = $_GET['class_id'] ?? null;
        if (!$classId) {
            $this->error('Class ID is required');
        }

        try {
            $subjects = CoursesModel::getSubjectsByClass($classId);
            $this->success($subjects);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * Get setting value from database
     */
    private function getSetting($key, $default = null) {
        if (!$this->db) {
            return $default;
        }
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
     * Get default classes data when database is not available
     */
    private function getDefaultClasses() {
        return [
            [
                'id' => 1,
                'class_name' => '1',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'teacher_first_name' => 'Ms.',
                'teacher_last_name' => 'Johnson',
                'subjects' => 'English, Mathematics, Science, Social Studies, Art, Physical Education',
                'subject_count' => 6
            ],
            [
                'id' => 2,
                'class_name' => '5',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'teacher_first_name' => 'Mr.',
                'teacher_last_name' => 'Smith',
                'subjects' => 'English, Mathematics, Science, Social Studies, Hindi, Computer Science',
                'subject_count' => 6
            ],
            [
                'id' => 3,
                'class_name' => '10',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'teacher_first_name' => 'Mrs.',
                'teacher_last_name' => 'Davis',
                'subjects' => 'English, Mathematics, Physics, Chemistry, Biology, History, Geography',
                'subject_count' => 7
            ]
        ];
    }

    /**
     * Get default curriculum data
     */
    private function getDefaultCurriculum() {
        return [
            'primary' => 'Our primary curriculum focuses on building strong foundations in core subjects while encouraging creativity and physical development through art, music, and sports.',
            'secondary' => 'Secondary education emphasizes critical thinking, problem-solving, and subject specialization while maintaining a balanced approach to overall development.',
            'higher_secondary' => 'Higher secondary program prepares students for higher education with advanced coursework, career guidance, and college entrance exam preparation.',
            'admission_requirements' => 'Students must be of appropriate age for their grade level, submit previous academic records, birth certificate, and medical certificate.',
            'academic_year' => '2024-2025'
        ];
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats() {
        return [
            'total_classes' => 25,
            'total_subjects' => 15,
            'primary_classes' => 10,
            'secondary_classes' => 10,
            'higher_secondary_classes' => 5
        ];
    }

    /**
     * Get default admission requirements
     */
    private function getDefaultAdmissionReqs() {
        return [
            'age_criteria' => 'Children must be 5+ years for Class 1, and appropriate age for subsequent grades.',
            'documents_required' => 'Birth Certificate, Previous School Records, Transfer Certificate, Medical Certificate, Passport Size Photos',
            'eligibility_criteria' => 'Minimum 60% in previous grade, Good conduct certificate, Medical fitness certificate',
            'application_deadline' => 'March 31st for new academic session',
            'fee_structure' => 'Registration Fee: ₹500, Admission Fee: ₹2000, Monthly Tuition: ₹1500-3000 based on grade'
        ];
    }
}