<?php
/**
 * Admin Gallery Controller
 * Handles admin gallery management operations
 */

class AdminGalleryController extends BaseController {

    public function __construct() {
        // Check if user is logged in and has admin role
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/login');
        }
    }

    /**
     * Gallery dashboard - main gallery management page
     */
    public function index() {
        try {
            // Get gallery statistics
            $stats = GalleryModel::getGalleryStats();

            // Get recent uploads
            $recentUploads = GalleryModel::getRecentUploads(6);

            // Get categories
            $categories = GalleryCategoryModel::getAllCategoriesHierarchy();

            // Get popular items
            $popularItems = GalleryModel::getPopularItems(6);

            $data = [
                'title' => 'Gallery Management',
                'stats' => $stats,
                'recent_uploads' => $recentUploads,
                'categories' => $categories,
                'popular_items' => $popularItems,
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.index', $data);
        } catch (Exception $e) {
            $this->error('Failed to load gallery dashboard: ' . $e->getMessage());
        }
    }

    /**
     * List all gallery items with pagination and filters
     */
    public function media() {
        try {
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 12;
            $categoryId = $_GET['category_id'] ?? null;
            $mediaType = $_GET['media_type'] ?? null;
            $search = $_GET['search'] ?? null;

            $filters = [];
            if ($categoryId) $filters['category_id'] = $categoryId;
            if ($mediaType) $filters['media_type'] = $mediaType;

            // Get paginated results
            $result = GalleryModel::getPaginated($page, $perPage, $filters);

            // Get categories for filter dropdown
            $categories = GalleryCategoryModel::getAllCategoriesHierarchy();

            $data = [
                'title' => 'Gallery Media',
                'items' => $result['items'],
                'pagination' => $result,
                'categories' => $categories,
                'filters' => [
                    'category_id' => $categoryId,
                    'media_type' => $mediaType,
                    'search' => $search
                ],
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.media', $data);
        } catch (Exception $e) {
            $this->error('Failed to load gallery media: ' . $e->getMessage());
        }
    }

    /**
     * Upload media page
     */
    public function upload() {
        try {
            $categories = GalleryCategoryModel::getAllCategoriesHierarchy();

            $data = [
                'title' => 'Upload Media',
                'categories' => $categories,
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.upload', $data);
        } catch (Exception $e) {
            $this->error('Failed to load upload page: ' . $e->getMessage());
        }
    }

    /**
     * Handle single file upload
     */
    public function uploadSingle() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $categoryId = $_POST['category_id'] ?? null;
            $tags = $_POST['tags'] ?? '';
            $altText = $_POST['alt_text'] ?? '';
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

            // Validate required fields
            if (empty($title)) {
                $this->error('Title is required');
            }

            if (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
                $this->error('No file uploaded or upload error');
            }

            $file = $_FILES['media_file'];
            $uploadResult = $this->processFileUpload($file, $categoryId);

            if (!$uploadResult['success']) {
                $this->error($uploadResult['message']);
            }

            // Save to database
            $data = [
                'title' => $title,
                'description' => $description,
                'category_id' => $categoryId,
                'tags' => $tags,
                'alt_text' => $altText,
                'is_featured' => $isFeatured,
                'uploaded_by' => $_SESSION['user_id'],
                'media_type' => $uploadResult['media_type'],
                'file_size' => $uploadResult['file_size'],
                'mime_type' => $uploadResult['mime_type']
            ];

            if ($uploadResult['media_type'] === 'image') {
                $data['image_path'] = $uploadResult['file_path'];
                $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
            } else {
                $data['video_path'] = $uploadResult['file_path'];
                $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
            }

            $galleryId = GalleryModel::create($data);

            if ($galleryId) {
                $this->success([
                    'message' => 'Media uploaded successfully',
                    'gallery_id' => $galleryId,
                    'file_path' => $uploadResult['file_path']
                ]);
            } else {
                // Clean up uploaded files
                $this->cleanupFiles([$uploadResult['file_path'], $uploadResult['thumbnail_path']]);
                $this->error('Failed to save media to database');
            }

        } catch (Exception $e) {
            $this->error('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle bulk file upload
     */
    public function uploadBulk() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $categoryId = $_POST['category_id'] ?? null;
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

            if (!isset($_FILES['media_files'])) {
                $this->error('No files uploaded');
            }

            $files = $_FILES['media_files'];
            $results = [];
            $uploadedCount = 0;
            $failedCount = 0;

            // Create upload session
            $sessionData = [
                'session_name' => 'Bulk Upload ' . date('Y-m-d H:i:s'),
                'category_id' => $categoryId,
                'uploaded_by' => $_SESSION['user_id']
            ];
            $sessionId = GalleryModel::createUploadSession($sessionData);

            // Process each file
            for ($i = 0; $i < count($files['name']); $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $results[] = [
                        'file' => $file['name'],
                        'success' => false,
                        'message' => 'Upload error: ' . $this->getUploadErrorMessage($file['error'])
                    ];
                    $failedCount++;
                    continue;
                }

                $uploadResult = $this->processFileUpload($file, $categoryId);

                if ($uploadResult['success']) {
                    // Save to database
                    $data = [
                        'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                        'category_id' => $categoryId,
                        'is_featured' => $isFeatured,
                        'uploaded_by' => $_SESSION['user_id'],
                        'media_type' => $uploadResult['media_type'],
                        'file_size' => $uploadResult['file_size'],
                        'mime_type' => $uploadResult['mime_type']
                    ];

                    if ($uploadResult['media_type'] === 'image') {
                        $data['image_path'] = $uploadResult['file_path'];
                        $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
                    } else {
                        $data['video_path'] = $uploadResult['file_path'];
                        $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
                    }

                    $galleryId = GalleryModel::create($data);

                    if ($galleryId) {
                        $results[] = [
                            'file' => $file['name'],
                            'success' => true,
                            'gallery_id' => $galleryId
                        ];
                        $uploadedCount++;
                    } else {
                        $this->cleanupFiles([$uploadResult['file_path'], $uploadResult['thumbnail_path']]);
                        $results[] = [
                            'file' => $file['name'],
                            'success' => false,
                            'message' => 'Failed to save to database'
                        ];
                        $failedCount++;
                    }
                } else {
                    $results[] = [
                        'file' => $file['name'],
                        'success' => false,
                        'message' => $uploadResult['message']
                    ];
                    $failedCount++;
                }
            }

            // Update session
            GalleryModel::updateUploadSession($sessionId, $uploadedCount, $failedCount, 'completed');

            $this->success([
                'message' => "Bulk upload completed: $uploadedCount uploaded, $failedCount failed",
                'results' => $results,
                'session_id' => $sessionId
            ]);

        } catch (Exception $e) {
            $this->error('Bulk upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Edit media item
     */
    public function edit($id) {
        try {
            $item = GalleryModel::getGalleryItemById($id);

            if (!$item) {
                $this->error('Gallery item not found', 404);
            }

            $categories = GalleryCategoryModel::getAllCategoriesHierarchy();

            $data = [
                'title' => 'Edit Media',
                'item' => $item,
                'categories' => $categories,
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.edit', $data);
        } catch (Exception $e) {
            $this->error('Failed to load edit page: ' . $e->getMessage());
        }
    }

    /**
     * Update media item
     */
    public function update($id) {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $item = GalleryModel::getGalleryItemById($id);
            if (!$item) {
                $this->error('Gallery item not found');
            }

            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'tags' => $_POST['tags'] ?? '',
                'alt_text' => $_POST['alt_text'] ?? '',
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'display_order' => $_POST['display_order'] ?? 0
            ];

            // Validate required fields
            if (empty($data['title'])) {
                $this->error('Title is required');
            }

            // Handle file replacement
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['media_file'];
                $uploadResult = $this->processFileUpload($file, $data['category_id']);

                if (!$uploadResult['success']) {
                    $this->error($uploadResult['message']);
                }

                // Delete old files
                $oldFiles = [];
                if ($item['image_path']) $oldFiles[] = $item['image_path'];
                if ($item['video_path']) $oldFiles[] = $item['video_path'];
                if ($item['thumbnail_path']) $oldFiles[] = $item['thumbnail_path'];
                $this->cleanupFiles($oldFiles);

                // Update file paths
                $data['media_type'] = $uploadResult['media_type'];
                $data['file_size'] = $uploadResult['file_size'];
                $data['mime_type'] = $uploadResult['mime_type'];

                if ($uploadResult['media_type'] === 'image') {
                    $data['image_path'] = $uploadResult['file_path'];
                    $data['video_path'] = null;
                    $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
                } else {
                    $data['video_path'] = $uploadResult['file_path'];
                    $data['image_path'] = null;
                    $data['thumbnail_path'] = $uploadResult['thumbnail_path'];
                }
            }

            $result = GalleryModel::update($id, $data);

            if ($result) {
                $this->success(['message' => 'Media updated successfully']);
            } else {
                $this->error('Failed to update media');
            }

        } catch (Exception $e) {
            $this->error('Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete media item
     */
    public function delete($id) {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $item = GalleryModel::getGalleryItemById($id);
            if (!$item) {
                $this->error('Gallery item not found');
            }

            // Delete physical files
            $files = [];
            if ($item['image_path']) $files[] = $item['image_path'];
            if ($item['video_path']) $files[] = $item['video_path'];
            if ($item['thumbnail_path']) $files[] = $item['thumbnail_path'];

            $result = GalleryModel::delete($id);

            if ($result) {
                $this->cleanupFiles($files);
                $this->success(['message' => 'Media deleted successfully']);
            } else {
                $this->error('Failed to delete media');
            }

        } catch (Exception $e) {
            $this->error('Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk operations
     */
    public function bulkOperation() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $operation = $_POST['operation'] ?? '';
            $ids = $_POST['ids'] ?? [];

            if (empty($ids) || !is_array($ids)) {
                $this->error('No items selected');
            }

            switch ($operation) {
                case 'delete':
                    $result = GalleryModel::bulkDelete($ids, $_SESSION['user_id']);
                    $message = $result ? 'Selected items deleted successfully' : 'Failed to delete items';
                    break;

                case 'move_category':
                    $categoryId = $_POST['category_id'] ?? null;
                    if (!$categoryId) {
                        $this->error('Category is required for move operation');
                    }
                    $result = GalleryModel::bulkMoveToCategory($ids, $categoryId, $_SESSION['user_id']);
                    $message = $result ? 'Items moved to category successfully' : 'Failed to move items';
                    break;

                case 'feature':
                    $result = GalleryModel::bulkUpdate($ids, ['is_featured' => 1], $_SESSION['user_id']);
                    $message = $result ? 'Items featured successfully' : 'Failed to feature items';
                    break;

                case 'unfeature':
                    $result = GalleryModel::bulkUpdate($ids, ['is_featured' => 0], $_SESSION['user_id']);
                    $message = $result ? 'Items unfeatured successfully' : 'Failed to unfeature items';
                    break;

                default:
                    $this->error('Invalid operation');
            }

            if ($result) {
                $this->success(['message' => $message]);
            } else {
                $this->error('Operation failed');
            }

        } catch (Exception $e) {
            $this->error('Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Categories management page
     */
    public function categories() {
        try {
            $categories = GalleryCategoryModel::getCategoriesTree();

            $data = [
                'title' => 'Gallery Categories',
                'categories' => $categories,
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.categories', $data);
        } catch (Exception $e) {
            $this->error('Failed to load categories: ' . $e->getMessage());
        }
    }

    /**
     * Create new category
     */
    public function createCategory() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $name = trim($_POST['name'] ?? '');
            $parentId = $_POST['parent_id'] ?? null;
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $this->error('Category name is required');
            }

            // Generate slug
            $slug = GalleryCategoryModel::generateSlug($name);

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parentId,
                'created_by' => $_SESSION['user_id']
            ];

            $categoryId = GalleryCategoryModel::create($data);

            if ($categoryId) {
                $this->success([
                    'message' => 'Category created successfully',
                    'category_id' => $categoryId
                ]);
            } else {
                $this->error('Failed to create category');
            }

        } catch (Exception $e) {
            $this->error('Create category failed: ' . $e->getMessage());
        }
    }

    /**
     * Update category
     */
    public function updateCategory($id) {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $category = GalleryCategoryModel::find($id);
            if (!$category) {
                $this->error('Category not found');
            }

            $name = trim($_POST['name'] ?? '');
            $parentId = $_POST['parent_id'] ?? null;
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $this->error('Category name is required');
            }

            // Check for circular reference
            if ($parentId && !GalleryCategoryModel::moveCategory($id, $parentId)) {
                $this->error('Invalid parent category selection');
            }

            // Generate slug
            $slug = GalleryCategoryModel::generateSlug($name, $id);

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parentId
            ];

            $result = GalleryCategoryModel::update($id, $data);

            if ($result) {
                $this->success(['message' => 'Category updated successfully']);
            } else {
                $this->error('Failed to update category');
            }

        } catch (Exception $e) {
            $this->error('Update category failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory($id) {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $category = GalleryCategoryModel::find($id);
            if (!$category) {
                $this->error('Category not found');
            }

            // Check if category has children or media
            if (GalleryCategoryModel::hasChildren($id)) {
                $this->error('Cannot delete category with subcategories');
            }

            if (GalleryCategoryModel::hasMedia($id)) {
                $this->error('Cannot delete category with media items');
            }

            $result = GalleryCategoryModel::delete($id);

            if ($result) {
                $this->success(['message' => 'Category deleted successfully']);
            } else {
                $this->error('Failed to delete category');
            }

        } catch (Exception $e) {
            $this->error('Delete category failed: ' . $e->getMessage());
        }
    }

    /**
     * Settings page
     */
    public function settings() {
        try {
            // Get current settings
            $settings = $this->getGallerySettings();

            $data = [
                'title' => 'Gallery Settings',
                'settings' => $settings,
                'current_page' => 'gallery'
            ];

            echo $this->view('admin.gallery.settings', $data);
        } catch (Exception $e) {
            $this->error('Failed to load settings: ' . $e->getMessage());
        }
    }

    /**
     * Update settings
     */
    public function updateSettings() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $this->validateCsrfToken();

            $settings = $_POST['settings'] ?? [];

            foreach ($settings as $key => $value) {
                $this->updateSetting($key, $value);
            }

            $this->success(['message' => 'Settings updated successfully']);
        } catch (Exception $e) {
            $this->error('Update settings failed: ' . $e->getMessage());
        }
    }

    /**
     * Process file upload
     */
    private function processFileUpload($file, $categoryId = null) {
        $uploadConfig = $this->getUploadConfig();

        // Validate file size
        if ($file['size'] > $uploadConfig['max_size']) {
            return [
                'success' => false,
                'message' => 'File size exceeds maximum allowed size'
            ];
        }

        // Validate file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        $mediaType = null;
        if (in_array($extension, $uploadConfig['image_types'])) {
            $mediaType = 'image';
        } elseif (in_array($extension, $uploadConfig['video_types'])) {
            $mediaType = 'video';
        } else {
            return [
                'success' => false,
                'message' => 'Unsupported file type'
            ];
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $uploadDir = $uploadConfig['upload_dir'] . '/' . $mediaType . 's/';

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'success' => false,
                'message' => 'Failed to save uploaded file'
            ];
        }

        // Generate thumbnail for images and videos
        $thumbnailPath = null;
        if ($mediaType === 'image') {
            $thumbnailPath = $this->generateImageThumbnail($filePath, $uploadConfig);
        } elseif ($mediaType === 'video') {
            $thumbnailPath = $this->generateVideoThumbnail($filePath, $uploadConfig);
        }

        return [
            'success' => true,
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'media_type' => $mediaType,
            'file_size' => $file['size'],
            'mime_type' => $file['type']
        ];
    }

    /**
     * Generate image thumbnail
     */
    private function generateImageThumbnail($imagePath, $config) {
        $thumbnailDir = $config['upload_dir'] . '/thumbnails/';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $thumbnailFilename = 'thumb_' . basename($imagePath);
        $thumbnailPath = $thumbnailDir . $thumbnailFilename;

        // Use GD to create thumbnail
        $image = imagecreatefromstring(file_get_contents($imagePath));
        $width = imagesx($image);
        $height = imagesy($image);

        $thumbWidth = $config['thumbnail_width'];
        $thumbHeight = $config['thumbnail_height'];

        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Calculate dimensions
        $aspectRatio = $width / $height;
        if ($thumbWidth / $thumbHeight > $aspectRatio) {
            $newWidth = $thumbHeight * $aspectRatio;
            $newHeight = $thumbHeight;
        } else {
            $newWidth = $thumbWidth;
            $newHeight = $thumbWidth / $aspectRatio;
        }

        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($thumbnail, $thumbnailPath, 85);

        imagedestroy($image);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    /**
     * Generate video thumbnail (placeholder - would need ffmpeg)
     */
    private function generateVideoThumbnail($videoPath, $config) {
        // For now, return a default video thumbnail
        // In production, this would use ffmpeg to extract a frame
        $thumbnailDir = $config['upload_dir'] . '/thumbnails/';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $thumbnailFilename = 'thumb_' . basename($videoPath) . '.jpg';
        $thumbnailPath = $thumbnailDir . $thumbnailFilename;

        // Create a simple placeholder thumbnail
        $thumbnail = imagecreatetruecolor(300, 200);
        $bgColor = imagecolorallocate($thumbnail, 40, 40, 40);
        $textColor = imagecolorallocate($thumbnail, 200, 200, 200);

        imagefill($thumbnail, 0, 0, $bgColor);
        imagestring($thumbnail, 5, 100, 90, 'VIDEO', $textColor);

        imagejpeg($thumbnail, $thumbnailPath, 85);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    /**
     * Get upload configuration
     */
    private function getUploadConfig() {
        return [
            'max_size' => $this->getSetting('max_file_size', 10485760), // 10MB
            'image_types' => explode(',', $this->getSetting('allowed_image_types', 'jpg,jpeg,png,gif,webp')),
            'video_types' => explode(',', $this->getSetting('allowed_video_types', 'mp4,avi,mov,wmv,flv,webm')),
            'upload_dir' => 'uploads/gallery',
            'thumbnail_width' => $this->getSetting('thumbnail_width', 300),
            'thumbnail_height' => $this->getSetting('thumbnail_height', 200)
        ];
    }

    /**
     * Get gallery settings
     */
    private function getGallerySettings() {
        $query = "SELECT * FROM gallery_settings ORDER BY setting_group, setting_key";
        $results = $this->db->fetchAll($query);

        $settings = [];
        foreach ($results as $setting) {
            $settings[$setting['setting_key']] = $setting;
        }

        return $settings;
    }

    /**
     * Get setting value
     */
    private function getSetting($key, $default = null) {
        $query = "SELECT setting_value FROM gallery_settings WHERE setting_key = ?";
        $result = $this->db->fetch($query, [$key]);
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Update setting
     */
    private function updateSetting($key, $value) {
        $query = "UPDATE gallery_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
        return $this->db->query($query, [$value, $key]);
    }

    /**
     * Clean up files
     */
    private function cleanupFiles($files) {
        foreach ($files as $file) {
            if ($file && file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $messages[$errorCode] ?? 'Unknown upload error';
    }
}