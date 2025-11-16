<?php
/**
 * Gallery Model
 * Handles gallery media data operations with enhanced features
 */

class GalleryModel extends BaseModel {
    protected $table = 'gallery';
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'media_type',
        'video_path',
        'file_size',
        'mime_type',
        'thumbnail_path',
        'category',
        'category_id',
        'tags',
        'alt_text',
        'display_order',
        'is_featured',
        'view_count',
        'download_count',
        'is_active',
        'uploaded_by'
    ];

    /**
     * Get all active gallery items with category info
     */
    public static function getAllActiveGallery($limit = null, $offset = null) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1
            ORDER BY g.display_order ASC, g.created_at DESC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        if ($offset) {
            $query .= " OFFSET " . (int)$offset;
        }

        return self::query($query);
    }

    /**
     * Get gallery items by category ID
     */
    public static function getGalleryByCategoryId($categoryId, $limit = null, $includeSubcategories = false) {
        $params = [$categoryId];
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1
        ";

        if ($includeSubcategories) {
            // Get all subcategory IDs
            $subcategories = self::getSubcategoryIds($categoryId);
            $categoryIds = array_merge([$categoryId], $subcategories);
            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $query .= " AND g.category_id IN ($placeholders)";
            $params = $categoryIds;
        } else {
            $query .= " AND g.category_id = ?";
        }

        $query .= " ORDER BY g.display_order ASC, g.created_at DESC";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query, $params);
    }

    /**
     * Get subcategory IDs recursively
     */
    private static function getSubcategoryIds($parentId) {
        $ids = [];
        $subcategories = GalleryCategoryModel::getSubcategories($parentId);

        foreach ($subcategories as $sub) {
            $ids[] = $sub['id'];
            $ids = array_merge($ids, self::getSubcategoryIds($sub['id']));
        }

        return $ids;
    }

    /**
     * Get gallery items by media type
     */
    public static function getGalleryByType($mediaType, $limit = null) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1 AND g.media_type = ?
            ORDER BY g.display_order ASC, g.created_at DESC
        ";

        $params = [$mediaType];

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query, $params);
    }

    /**
     * Get featured gallery items
     */
    public static function getFeaturedGallery($limit = 6) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1 AND g.is_featured = 1
            ORDER BY g.display_order ASC, g.created_at DESC
            LIMIT ?
        ";

        return self::query($query, [$limit]);
    }

    /**
     * Get gallery statistics
     */
    public static function getGalleryStats() {
        $stats = [];

        // Total media
        $result = self::query("SELECT COUNT(*) as total FROM gallery WHERE is_active = 1");
        $stats['total_media'] = $result[0]['total'] ?? 0;

        // Media types
        $result = self::query("SELECT media_type, COUNT(*) as count FROM gallery WHERE is_active = 1 GROUP BY media_type");
        $stats['media_types'] = array_column($result, 'count', 'media_type');

        // Categories count
        $result = self::query("SELECT COUNT(*) as total FROM gallery_categories WHERE is_active = 1");
        $stats['total_categories'] = $result[0]['total'] ?? 0;

        // Recent uploads (last 30 days)
        $result = self::query("SELECT COUNT(*) as total FROM gallery WHERE is_active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent_uploads'] = $result[0]['total'] ?? 0;

        // Total file size
        $result = self::query("SELECT SUM(file_size) as total_size FROM gallery WHERE is_active = 1");
        $stats['total_size'] = $result[0]['total_size'] ?? 0;

        // Featured items
        $result = self::query("SELECT COUNT(*) as total FROM gallery WHERE is_active = 1 AND is_featured = 1");
        $stats['featured_count'] = $result[0]['total'] ?? 0;

        return $stats;
    }

    /**
     * Advanced search gallery items
     */
    public static function searchGallery($searchTerm, $filters = [], $limit = null, $offset = null) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1
        ";

        $params = [];

        // Search term
        if (!empty($searchTerm)) {
            $query .= " AND (g.title LIKE ? OR g.description LIKE ? OR g.tags LIKE ? OR g.alt_text LIKE ?)";
            $searchPattern = '%' . $searchTerm . '%';
            $params = array_merge($params, [$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        }

        // Filters
        if (!empty($filters['category_id'])) {
            $query .= " AND g.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['media_type'])) {
            $query .= " AND g.media_type = ?";
            $params[] = $filters['media_type'];
        }

        if (!empty($filters['is_featured'])) {
            $query .= " AND g.is_featured = ?";
            $params[] = $filters['is_featured'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND g.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND g.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $query .= " ORDER BY g.display_order ASC, g.created_at DESC";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        if ($offset) {
            $query .= " OFFSET " . (int)$offset;
        }

        return self::query($query, $params);
    }

    /**
     * Get gallery item with full details
     */
    public static function getGalleryItemById($id) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug,
                   u.username as uploaded_by_name
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            LEFT JOIN users u ON g.uploaded_by = u.id
            WHERE g.id = ? AND g.is_active = 1
        ";

        $result = self::query($query, [$id]);
        return $result ? $result[0] : null;
    }

    /**
     * Get related gallery items
     */
    public static function getRelatedGalleryItems($categoryId, $excludeId = null, $limit = 4) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1 AND g.category_id = ?
        ";

        $params = [$categoryId];

        if ($excludeId) {
            $query .= " AND g.id != ?";
            $params[] = $excludeId;
        }

        $query .= " ORDER BY g.display_order ASC, g.created_at DESC LIMIT ?";

        return self::query($query, array_merge($params, [$limit]));
    }

    /**
     * Bulk update gallery items
     */
    public static function bulkUpdate($ids, $data, $userId) {
        $updateFields = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $this->fillable)) {
                $updateFields[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "UPDATE gallery SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id IN ($placeholders)";

        $params = array_merge($params, $ids);

        return self::query($query, $params);
    }

    /**
     * Bulk delete gallery items
     */
    public static function bulkDelete($ids, $userId) {
        // Get file paths before deletion
        $items = self::query("SELECT image_path, video_path, thumbnail_path FROM gallery WHERE id IN (" . str_repeat('?,', count($ids) - 1) . "?)", $ids);

        // Delete from database
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "UPDATE gallery SET is_active = 0, updated_at = NOW() WHERE id IN ($placeholders)";

        $result = self::query($query, $ids);

        if ($result) {
            // Delete physical files
            foreach ($items as $item) {
                self::deleteFile($item['image_path']);
                self::deleteFile($item['video_path']);
                self::deleteFile($item['thumbnail_path']);
            }
        }

        return $result;
    }

    /**
     * Bulk move to category
     */
    public static function bulkMoveToCategory($ids, $categoryId, $userId) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "UPDATE gallery SET category_id = ?, updated_at = NOW() WHERE id IN ($placeholders)";

        return self::query($query, array_merge([$categoryId], $ids));
    }

    /**
     * Increment view count
     */
    public static function incrementViewCount($id) {
        return self::query("UPDATE gallery SET view_count = view_count + 1 WHERE id = ?", [$id]);
    }

    /**
     * Increment download count
     */
    public static function incrementDownloadCount($id) {
        return self::query("UPDATE gallery SET download_count = download_count + 1 WHERE id = ?", [$id]);
    }

    /**
     * Get popular items
     */
    public static function getPopularItems($limit = 10) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1
            ORDER BY g.view_count DESC, g.created_at DESC
            LIMIT ?
        ";

        return self::query($query, [$limit]);
    }

    /**
     * Get recently uploaded items
     */
    public static function getRecentUploads($limit = 12) {
        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1
            ORDER BY g.created_at DESC
            LIMIT ?
        ";

        return self::query($query, [$limit]);
    }

    /**
     * Get items by tags
     */
    public static function getItemsByTags($tags, $limit = null) {
        $tagConditions = [];
        $params = [];

        foreach ($tags as $tag) {
            $tagConditions[] = "FIND_IN_SET(?, g.tags)";
            $params[] = $tag;
        }

        $query = "
            SELECT g.*, c.name as category_name, c.slug as category_slug
            FROM gallery g
            LEFT JOIN gallery_categories c ON g.category_id = c.id
            WHERE g.is_active = 1 AND (" . implode(' OR ', $tagConditions) . ")
            ORDER BY g.display_order ASC, g.created_at DESC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query, $params);
    }

    /**
     * Get all unique tags
     */
    public static function getAllTags() {
        $query = "
            SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(g.tags, ',', n.n), ',', -1)) as tag
            FROM gallery g
            CROSS JOIN (
                SELECT a.N + b.N * 10 + 1 n
                FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
                ,(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
                ORDER BY n
            ) n
            WHERE n.n <= 1 + (LENGTH(g.tags) - LENGTH(REPLACE(g.tags, ',', '')))
            AND g.tags IS NOT NULL AND g.tags != '' AND g.is_active = 1
            ORDER BY tag
        ";

        return self::query($query);
    }

    /**
     * Delete physical file
     */
    private static function deleteFile($filePath) {
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Get upload session details
     */
    public static function getUploadSession($sessionId) {
        return self::query("SELECT * FROM gallery_upload_sessions WHERE id = ?", [$sessionId]);
    }

    /**
     * Create upload session
     */
    public static function createUploadSession($data) {
        return self::query(
            "INSERT INTO gallery_upload_sessions (session_name, category_id, uploaded_by, created_at) VALUES (?, ?, ?, NOW())",
            [$data['session_name'], $data['category_id'], $data['uploaded_by']]
        );
    }

    /**
     * Update upload session progress
     */
    public static function updateUploadSession($sessionId, $uploadedFiles, $failedFiles, $status = null) {
        $query = "UPDATE gallery_upload_sessions SET uploaded_files = ?, failed_files = ?, updated_at = NOW()";
        $params = [$uploadedFiles, $failedFiles];

        if ($status) {
            $query .= ", status = ?";
            $params[] = $status;
        }

        $query .= " WHERE id = ?";
        $params[] = $sessionId;

        return self::query($query, $params);
    }

    /**
     * Get paginated results
     */
    public static function getPaginated($page = 1, $perPage = 12, $filters = []) {
        $offset = ($page - 1) * $perPage;

        $items = self::searchGallery('', $filters, $perPage, $offset);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM gallery g WHERE g.is_active = 1";
        $countParams = [];

        if (!empty($filters['category_id'])) {
            $countQuery .= " AND g.category_id = ?";
            $countParams[] = $filters['category_id'];
        }

        if (!empty($filters['media_type'])) {
            $countQuery .= " AND g.media_type = ?";
            $countParams[] = $filters['media_type'];
        }

        $totalResult = self::query($countQuery, $countParams);
        $total = $totalResult[0]['total'] ?? 0;

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}