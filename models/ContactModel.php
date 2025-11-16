<?php
/**
 * Contact Model
 * Handles contact information and form submissions
 */

class ContactModel extends BaseModel {

    /**
     * Get contact information from settings
     */
    public static function getContactInfo() {
        $contactInfo = [
            'school_name' => self::getSetting('school_name'),
            'address' => self::getSetting('school_address'),
            'phone' => self::getSetting('school_phone'),
            'email' => self::getSetting('school_email'),
            'principal_name' => self::getSetting('principal_name'),
            'working_hours' => self::getSetting('working_hours', 'Mon - Fri: 8:00 AM - 4:00 PM'),
            'emergency_contact' => self::getSetting('emergency_contact'),
            'map_location' => self::getSetting('map_location'),
            'social_facebook' => self::getSetting('social_facebook'),
            'social_twitter' => self::getSetting('social_twitter'),
            'social_instagram' => self::getSetting('social_instagram'),
            'social_youtube' => self::getSetting('social_youtube')
        ];

        return $contactInfo;
    }

    /**
     * Get department contacts
     */
    public static function getDepartmentContacts() {
        // For now, return default department contacts
        // In a real implementation, this could come from a departments table
        return [
            [
                'department' => 'Administration',
                'contact_person' => 'Principal',
                'phone' => self::getSetting('school_phone'),
                'email' => self::getSetting('school_email')
            ],
            [
                'department' => 'Admissions',
                'contact_person' => 'Admissions Officer',
                'phone' => self::getSetting('admissions_phone', self::getSetting('school_phone')),
                'email' => self::getSetting('admissions_email', 'admissions@school.com')
            ],
            [
                'department' => 'Academic Affairs',
                'contact_person' => 'Academic Coordinator',
                'phone' => self::getSetting('academic_phone', self::getSetting('school_phone')),
                'email' => self::getSetting('academic_email', 'academic@school.com')
            ],
            [
                'department' => 'Student Services',
                'contact_person' => 'Student Counselor',
                'phone' => self::getSetting('counseling_phone', self::getSetting('school_phone')),
                'email' => self::getSetting('counseling_email', 'counseling@school.com')
            ]
        ];
    }

    /**
     * Process contact form submission
     * Note: In a real implementation, you might want to store this in a contact_submissions table
     */
    public static function submitContactForm($data) {
        // Validate required fields
        $requiredFields = ['name', 'email', 'subject', 'message'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Here you could:
        // 1. Store in database (create contact_submissions table)
        // 2. Send email notification
        // 3. Send auto-reply to user

        // For now, we'll just return success
        // In a real implementation, you'd want to store this and send emails

        return [
            'success' => true,
            'message' => 'Thank you for your message. We will get back to you soon!',
            'data' => [
                'name' => htmlspecialchars($data['name']),
                'email' => htmlspecialchars($data['email']),
                'phone' => htmlspecialchars($data['phone'] ?? ''),
                'subject' => htmlspecialchars($data['subject']),
                'message' => htmlspecialchars($data['message']),
                'submitted_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Get contact form statistics (for admin dashboard)
     */
    public static function getContactStats() {
        // In a real implementation, this would query contact_submissions table
        return [
            'total_submissions' => 0, // Would be actual count
            'this_month' => 0,
            'unread' => 0,
            'responded' => 0
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