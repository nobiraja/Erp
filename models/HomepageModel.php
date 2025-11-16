<?php
/**
 * Homepage Model
 * Handles homepage content data operations
 */

class HomepageModel extends BaseModel {
    protected $table = 'homepage_content';
    protected $fillable = [
        'section_name',
        'content_type',
        'title',
        'content',
        'image_path',
        'video_url',
        'link_url',
        'display_order',
        'is_active'
    ];

    /**
     * Get active content by section
     */
    public static function getActiveContentBySection($sectionName) {
        return self::where('section_name', $sectionName)
                  ->where('is_active', true)
                  ->orderBy('display_order')
                  ->get();
    }

    /**
     * Get carousel images
     */
    public static function getCarouselImages() {
        return self::getActiveContentBySection('carousel');
    }

    /**
     * Get about section content
     */
    public static function getAboutContent() {
        $content = self::where('section_name', 'about')
                      ->where('is_active', true)
                      ->first();
        return $content ? $content->toArray() : null;
    }

    /**
     * Get courses content
     */
    public static function getCoursesContent() {
        return self::getActiveContentBySection('courses');
    }

    /**
     * Get events content
     */
    public static function getEventsContent() {
        return self::getActiveContentBySection('events');
    }

    /**
     * Get achievements content
     */
    public static function getAchievementsContent() {
        return self::getActiveContentBySection('achievements');
    }

    /**
     * Get gallery preview content
     */
    public static function getGalleryPreview() {
        return self::where('section_name', 'gallery')
                  ->where('is_active', true)
                  ->orderBy('display_order')
                  ->limit(6)
                  ->get();
    }

    /**
     * Get testimonials content
     */
    public static function getTestimonialsContent() {
        return self::getActiveContentBySection('testimonials');
    }

    /**
     * Get call-to-action content
     */
    public static function getCTAContent() {
        $content = self::where('section_name', 'cta')
                      ->where('is_active', true)
                      ->first();
        return $content ? $content->toArray() : null;
    }

    /**
     * Get all homepage content for AJAX loading
     */
    public static function getAllHomepageContent() {
        $sections = [
            'carousel' => self::getCarouselImages(),
            'about' => self::getAboutContent(),
            'courses' => self::getCoursesContent(),
            'events' => self::getEventsContent(),
            'achievements' => self::getAchievementsContent(),
            'gallery' => self::getGalleryPreview(),
            'testimonials' => self::getTestimonialsContent(),
            'cta' => self::getCTAContent()
        ];

        return $sections;
    }
}