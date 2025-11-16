<?php
/**
 * Gallery Controller
 * Handles public gallery page functionality
 */

class GalleryController extends BaseController {

    /**
     * Display gallery page
     */
    public function index() {
        try {
            // Get all gallery items
            $galleryItems = GalleryModel::getAllActiveGallery();

            // Get categories
            $categories = GalleryModel::getAllCategories();

            // Get gallery statistics
            $stats = GalleryModel::getGalleryStats();

            // Get featured items for preview
            $featuredItems = GalleryModel::getFeaturedGallery(8);
        } catch (Exception $e) {
            // Handle database errors gracefully
            $galleryItems = $this->getDefaultGalleryItems();
            $categories = $this->getDefaultCategories();
            $stats = $this->getDefaultStats();
            $featuredItems = $this->getDefaultGalleryItems(8);
        }

        // Get school settings for meta tags
        $schoolName = $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Photo Gallery',
            'meta_description' => 'Explore our photo gallery showcasing school events, activities, achievements, and memorable moments at ' . $schoolName . '.',
            'meta_keywords' => 'photo gallery, school photos, events, activities, achievements, school life, student photos',
            'school_name' => $schoolName,
            'gallery_items' => $galleryItems,
            'categories' => $categories,
            'stats' => $stats,
            'featured_items' => $featuredItems,
            'current_year' => date('Y')
        ];

        // Render gallery view
        echo $this->view('public.gallery.index', $data);
    }

    /**
     * AJAX endpoint to get gallery items by category
     */
    public function getByCategory() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $category = $_GET['category'] ?? null;
        $limit = $_GET['limit'] ?? null;

        try {
            if ($category) {
                $items = GalleryModel::getGalleryByCategory($category, $limit);
            } else {
                $items = GalleryModel::getAllActiveGallery($limit);
            }
            $this->success($items);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * AJAX endpoint to search gallery
     */
    public function search() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $searchTerm = $_GET['q'] ?? '';
        $limit = $_GET['limit'] ?? null;

        try {
            if (!empty($searchTerm)) {
                $items = GalleryModel::searchGallery($searchTerm, $limit);
            } else {
                $items = GalleryModel::getAllActiveGallery($limit);
            }
            $this->success($items);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * Get single gallery item details (for modal/lightbox)
     */
    public function show($id) {
        try {
            $item = GalleryModel::getGalleryItemById($id);

            if (!$item) {
                $this->error('Gallery item not found', 404);
            }

            // Get related items
            $relatedItems = GalleryModel::getRelatedGalleryItems($item['category'], $item['id'], 4);

            $this->success([
                'item' => $item,
                'related' => $relatedItems
            ]);
        } catch (Exception $e) {
            $this->error('Gallery item not found', 404);
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
     * Get default gallery items when database is not available
     */
    private function getDefaultGalleryItems($limit = null) {
        $items = [
            [
                'id' => 1,
                'title' => 'Annual Sports Day',
                'description' => 'Students participating in various sports activities',
                'image_path' => '/images/gallery/sports-day-1.jpg',
                'category' => 'Sports',
                'display_order' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'title' => 'Science Fair Winners',
                'description' => 'Proud winners of the inter-school science fair',
                'image_path' => '/images/gallery/science-fair-1.jpg',
                'category' => 'Achievements',
                'display_order' => 2,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'title' => 'Cultural Dance Performance',
                'description' => 'Students showcasing traditional dance forms',
                'image_path' => '/images/gallery/cultural-1.jpg',
                'category' => 'Cultural',
                'display_order' => 3,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'title' => 'Classroom Learning',
                'description' => 'Interactive learning session in progress',
                'image_path' => '/images/gallery/classroom-1.jpg',
                'category' => 'Academics',
                'display_order' => 4,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'title' => 'Art Exhibition',
                'description' => 'Creative artwork displayed by students',
                'image_path' => '/images/gallery/art-1.jpg',
                'category' => 'Arts',
                'display_order' => 5,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 6,
                'title' => 'School Assembly',
                'description' => 'Morning assembly with students and teachers',
                'image_path' => '/images/gallery/assembly-1.jpg',
                'category' => 'Daily Life',
                'display_order' => 6,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        if ($limit) {
            return array_slice($items, 0, $limit);
        }

        return $items;
    }

    /**
     * Get default categories
     */
    private function getDefaultCategories() {
        return [
            ['category' => 'Sports', 'count' => 15],
            ['category' => 'Achievements', 'count' => 12],
            ['category' => 'Cultural', 'count' => 18],
            ['category' => 'Academics', 'count' => 20],
            ['category' => 'Arts', 'count' => 10],
            ['category' => 'Daily Life', 'count' => 25]
        ];
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats() {
        return [
            'total_media' => 100,
            'total_categories' => 6,
            'recent_uploads' => 15,
            'popular_category' => 'Daily Life'
        ];
    }
}