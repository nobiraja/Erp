<?php
/**
 * Contact Controller
 * Handles public contact page functionality
 */

class ContactController extends BaseController {

    /**
     * Display contact page
     */
    public function index() {
        try {
            // Get contact information
            $contactInfo = ContactModel::getContactInfo();

            // Get department contacts
            $departments = ContactModel::getDepartmentContacts();
        } catch (Exception $e) {
            // Handle database errors gracefully
            $contactInfo = $this->getDefaultContactInfo();
            $departments = $this->getDefaultDepartments();
        }

        // Get school settings for meta tags
        $schoolName = $contactInfo['school_name'] ?? $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Contact Us',
            'meta_description' => 'Get in touch with ' . $schoolName . '. Find our contact information, location, and send us a message.',
            'meta_keywords' => 'contact, school contact, address, phone, email, location, contact form',
            'school_name' => $schoolName,
            'contact_info' => $contactInfo,
            'departments' => $departments,
            'current_year' => date('Y'),
            'success_message' => $_GET['success'] ?? null,
            'error_message' => $_GET['error'] ?? null
        ];

        // Render contact view
        echo $this->view('public.contact.index', $data);
    }

    /**
     * Process contact form submission
     */
    public function submit() {
        if (!$this->isPost()) {
            $this->error('Invalid request method');
        }

        try {
            // Get form data
            $formData = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'message' => $_POST['message'] ?? ''
            ];

            // Submit contact form
            $result = ContactModel::submitContactForm($formData);

            // Redirect with success message
            header('Location: /contact?success=' . urlencode($result['message']));
            exit;

        } catch (Exception $e) {
            // Redirect with error message
            header('Location: /contact?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * AJAX endpoint to get contact information
     */
    public function getInfo() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $contactInfo = ContactModel::getContactInfo();
            $departments = ContactModel::getDepartmentContacts();
            $this->success([
                'contact_info' => $contactInfo,
                'departments' => $departments
            ]);
        } catch (Exception $e) {
            $this->success([
                'contact_info' => $this->getDefaultContactInfo(),
                'departments' => $this->getDefaultDepartments()
            ]);
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
     * Get default contact information when database is not available
     */
    private function getDefaultContactInfo() {
        return [
            'school_name' => 'School Management System',
            'address' => '123 School Street, City, State 12345, Country',
            'phone' => '+1 (555) 123-4567',
            'email' => 'info@school.com',
            'principal_name' => 'Dr. John Smith',
            'working_hours' => 'Mon - Fri: 8:00 AM - 4:00 PM',
            'emergency_contact' => '+1 (555) 123-4568',
            'map_location' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.1!2d-73.9!3d40.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
            'social_facebook' => 'https://facebook.com/school',
            'social_twitter' => 'https://twitter.com/school',
            'social_instagram' => 'https://instagram.com/school',
            'social_youtube' => 'https://youtube.com/school'
        ];
    }

    /**
     * Get default department contacts
     */
    private function getDefaultDepartments() {
        return [
            [
                'department' => 'Administration',
                'contact_person' => 'Principal',
                'phone' => '+1 (555) 123-4567',
                'email' => 'principal@school.com'
            ],
            [
                'department' => 'Admissions',
                'contact_person' => 'Admissions Officer',
                'phone' => '+1 (555) 123-4567',
                'email' => 'admissions@school.com'
            ],
            [
                'department' => 'Academic Affairs',
                'contact_person' => 'Academic Coordinator',
                'phone' => '+1 (555) 123-4567',
                'email' => 'academic@school.com'
            ],
            [
                'department' => 'Student Services',
                'contact_person' => 'Student Counselor',
                'phone' => '+1 (555) 123-4567',
                'email' => 'counseling@school.com'
            ]
        ];
    }
}