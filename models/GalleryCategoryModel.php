<?php
/**
 * Gallery Category Model
 * Handles gallery category data operations with hierarchical support
 */

class GalleryCategoryModel extends BaseModel {
    protected $table = 'gallery_categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'display_order',
        'is_active',
        'created_by'
    ];

    /**
     * Get all active categories with hierarchy
     */
    public static function getAllCategoriesHierarchy() {
        $query = "
            SELECT
                c1.id,
                c1.name,
                c1.slug,
                c1.description,
                c1.parent_id,
                c1.display_order,
                c1.is_active,
                c1.created_at,
                c2.name as parent_name,
                COUNT(g.id) as media_count
            FROM gallery_categories c1
            LEFT JOIN gallery_categories c2 ON c1.parent_id = c2.id
            LEFT JOIN gallery g ON c1.id = g.category_id AND g.is_active = 1
            WHERE c1.is_active = 1
            GROUP BY c1.id, c1.name, c1.slug, c1.description, c1.parent_id, c1.display_order, c1.is_active, c1.created_at, c2.name
            ORDER BY c1.display_order ASC, c1.name ASC
        ";

        return self::query($query);
    }

    /**
     * Get categories as nested tree structure
     */
    public static function getCategoriesTree() {
        $categories = self::getAllCategoriesHierarchy();
        return self::buildTree($categories);
    }

    /**
     * Build hierarchical tree from flat category list
     */
    private static function buildTree($categories, $parentId = null) {
        $tree = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = self::buildTree($categories, $category['id']);
                if (!empty($children)) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * Get category by slug
     */
    public static function getCategoryBySlug($slug) {
        $query = "
            SELECT
                c1.*,
                c2.name as parent_name
            FROM gallery_categories c1
            LEFT JOIN gallery_categories c2 ON c1.parent_id = c2.id
            WHERE c1.slug = ? AND c1.is_active = 1
        ";

        $result = self::query($query, [$slug]);
        return $result ? $result[0] : null;
    }

    /**
     * Get parent categories (for dropdown)
     */
    public static function getParentCategories($excludeId = null) {
        $query = "SELECT id, name, slug FROM gallery_categories WHERE parent_id IS NULL AND is_active = 1";
        $params = [];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $query .= " ORDER BY display_order ASC, name ASC";

        return self::query($query, $params);
    }

    /**
     * Get subcategories of a parent category
     */
    public static function getSubcategories($parentId) {
        $query = "
            SELECT
                c.*,
                COUNT(g.id) as media_count
            FROM gallery_categories c
            LEFT JOIN gallery g ON c.id = g.category_id AND g.is_active = 1
            WHERE c.parent_id = ? AND c.is_active = 1
            GROUP BY c.id
            ORDER BY c.display_order ASC, c.name ASC
        ";

        return self::query($query, [$parentId]);
    }

    /**
     * Get category with statistics
     */
    public static function getCategoryWithStats($id) {
        $query = "
            SELECT
                c.*,
                c2.name as parent_name,
                COUNT(g.id) as total_media,
                SUM(CASE WHEN g.media_type = 'image' THEN 1 ELSE 0 END) as image_count,
                SUM(CASE WHEN g.media_type = 'video' THEN 1 ELSE 0 END) as video_count,
                SUM(g.file_size) as total_size,
                MAX(g.created_at) as last_upload,
                COUNT(CASE WHEN g.is_featured = 1 THEN 1 END) as featured_count
            FROM gallery_categories c
            LEFT JOIN gallery_categories c2 ON c.parent_id = c2.id
            LEFT JOIN gallery g ON c.id = g.category_id AND g.is_active = 1
            WHERE c.id = ? AND c.is_active = 1
            GROUP BY c.id, c2.name
        ";

        $result = self::query($query, [$id]);
        return $result ? $result[0] : null;
    }

    /**
     * Check if category has children
     */
    public static function hasChildren($id) {
        $query = "SELECT COUNT(*) as count FROM gallery_categories WHERE parent_id = ? AND is_active = 1";
        $result = self::query($query, [$id]);
        return $result[0]['count'] > 0;
    }

    /**
     * Check if category has media
     */
    public static function hasMedia($id) {
        $query = "SELECT COUNT(*) as count FROM gallery WHERE category_id = ? AND is_active = 1";
        $result = self::query($query, [$id]);
        return $result[0]['count'] > 0;
    }

    /**
     * Get category path (breadcrumb)
     */
    public static function getCategoryPath($id) {
        $path = [];
        $currentId = $id;

        while ($currentId) {
            $category = self::find($currentId);
            if (!$category) break;

            array_unshift($path, $category);
            $currentId = $category['parent_id'];
        }

        return $path;
    }

    /**
     * Update display order for categories
     */
    public static function updateDisplayOrder($orders) {
        foreach ($orders as $id => $order) {
            self::query(
                "UPDATE gallery_categories SET display_order = ?, updated_at = NOW() WHERE id = ?",
                [$order, $id]
            );
        }
        return true;
    }

    /**
     * Generate unique slug for category
     */
    public static function generateSlug($name, $excludeId = null) {
        $slug = self::slugify($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private static function slugExists($slug, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM gallery_categories WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = self::query($query, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Convert string to slug
     */
    private static function slugify($text) {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Trim
        $text = trim($text, '-');

        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // Lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * Move category to new parent
     */
    public static function moveCategory($id, $newParentId) {
        // Prevent circular reference
        if ($newParentId && self::isDescendant($id, $newParentId)) {
            return false;
        }

        return self::query(
            "UPDATE gallery_categories SET parent_id = ?, updated_at = NOW() WHERE id = ?",
            [$newParentId ?: null, $id]
        );
    }

    /**
     * Check if category is descendant of another
     */
    private static function isDescendant($childId, $parentId) {
        $currentId = $parentId;

        while ($currentId) {
            if ($currentId == $childId) {
                return true;
            }

            $category = self::find($currentId);
            if (!$category) break;

            $currentId = $category['parent_id'];
        }

        return false;
    }

    /**
     * Get categories for dropdown (flat list with indentation)
     */
    public static function getCategoriesForDropdown() {
        $categories = self::getAllCategoriesHierarchy();
        $options = [];

        foreach ($categories as $category) {
            $indent = str_repeat('— ', self::getCategoryLevel($category['id'], $categories));
            $options[] = [
                'id' => $category['id'],
                'name' => $indent . $category['name'],
                'level' => self::getCategoryLevel($category['id'], $categories)
            ];
        }

        return $options;
    }

    /**
     * Get category level in hierarchy
     */
    private static function getCategoryLevel($id, $categories) {
        $level = 0;
        $currentId = $id;

        while ($currentId) {
            $found = false;
            foreach ($categories as $category) {
                if ($category['id'] == $currentId && $category['parent_id']) {
                    $level++;
                    $currentId = $category['parent_id'];
                    $found = true;
                    break;
                }
            }
            if (!$found) break;
        }

        return $level;
    }
}