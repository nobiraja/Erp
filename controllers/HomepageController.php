<?php
/**
 * Homepage Controller
 * Handles public website homepage functionality
 */

class HomepageController extends BaseController {

    /**
     * Display homepage
     */
    public function index() {
        try {
            // Load homepage content
            $homepageData = HomepageModel::getAllHomepageContent();
        } catch (Exception $e) {
            // Handle database errors gracefully
            $homepageData = $this->getDefaultHomepageData();
        }

        // Get school settings for meta tags
        $schoolName = $this->getSetting('school_name', 'School Management System');
        $schoolDescription = $this->getSetting('school_description', 'A comprehensive school management system for modern educational institutions.');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Homepage',
            'meta_description' => $schoolDescription,
            'school_name' => $schoolName,
            'homepage_data' => $homepageData,
            'current_year' => date('Y')
        ];

        // Render homepage view
        echo $this->view('public.homepage.index', $data);
    }

    /**
     * AJAX endpoint to get homepage content
     */
    public function getContent() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $content = HomepageModel::getAllHomepageContent();
            $this->success($content);
        } catch (Exception $e) {
            $this->success($this->getDefaultHomepageData());
        }
    }

    /**
     * AJAX endpoint to get carousel images
     */
    public function getCarousel() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $carousel = HomepageModel::getCarouselImages();
            $this->success($carousel);
        } catch (Exception $e) {
            $this->success([[
                'title' => 'Welcome to Our School',
                'content' => 'Excellence in Education, Building Tomorrow\'s Leaders',
                'image_path' => '/images/default-hero.jpg',
                'link_url' => '/admission'
            ]]);
        }
    }

    /**
     * AJAX endpoint to get events
     */
    public function getEvents() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $events = HomepageModel::getEventsContent();
            $this->success($events);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * AJAX endpoint to get gallery preview
     */
    public function getGallery() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $gallery = HomepageModel::getGalleryPreview();
            $this->success($gallery);
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
     * Get default homepage data when database is not available
     */
    private function getDefaultHomepageData() {
        return [
            'carousel' => [
                [
                    'title' => 'Welcome to Our School',
                    'content' => 'Excellence in Education, Building Tomorrow\'s Leaders',
                    'image_path' => '/images/default-hero.jpg',
                    'link_url' => '/admission'
                ]
            ],
            'about' => [
                'title' => 'About Our School',
                'content' => 'We are committed to providing quality education and nurturing the potential of every student. Our school offers a comprehensive curriculum, experienced faculty, and modern facilities to ensure the best learning environment for our students.'
            ],
            'courses' => [
                [
                    'title' => 'Primary Education',
                    'content' => 'Foundation building with comprehensive primary curriculum'
                ],
                [
                    'title' => 'Secondary Education',
                    'content' => 'Advanced learning with specialized subject streams'
                ],
                [
                    'title' => 'Higher Secondary',
                    'content' => 'College preparation with career guidance'
                ]
            ],
            'events' => [],
            'achievements' => [],
            'gallery' => [],
            'testimonials' => [],
            'cta' => [
                'title' => 'Ready to Join Our Community?',
                'content' => 'Take the first step towards a brighter future for your child',
                'link_url' => '/admission'
            ]
        ];
    }
}