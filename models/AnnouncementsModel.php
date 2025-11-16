<?php
/**
 * Announcements Model
 * Handles announcements data operations
 */

class AnnouncementsModel extends BaseModel {
    protected $table = 'announcements';
    protected $fillable = [
        'title',
        'content',
        'priority',
        'visibility',
        'target_audience',
        'is_active',
        'expires_at'
    ];

    /**
     * Get active announcements
     */
    public static function getActiveAnnouncements($limit = null) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.is_active = 1
            AND (a.expires_at IS NULL OR a.expires_at > NOW())
            ORDER BY
                CASE a.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                a.created_at DESC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query);
    }

    /**
     * Get announcements by priority
     */
    public static function getAnnouncementsByPriority($priority) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.is_active = 1
            AND a.priority = ?
            AND (a.expires_at IS NULL OR a.expires_at > NOW())
            ORDER BY a.created_at DESC
        ";

        return self::query($query, [$priority]);
    }

    /**
     * Get announcements by visibility
     */
    public static function getAnnouncementsByVisibility($visibility) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.is_active = 1
            AND a.visibility = ?
            AND (a.expires_at IS NULL OR a.expires_at > NOW())
            ORDER BY
                CASE a.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                a.created_at DESC
        ";

        return self::query($query, [$visibility]);
    }

    /**
     * Get announcement by ID with creator info
     */
    public static function getAnnouncementById($id) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.id = ?
        ";

        $results = self::query($query, [$id]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get announcements statistics
     */
    public static function getAnnouncementsStats() {
        $stats = [];

        // Total announcements
        $result = self::query("SELECT COUNT(*) as total FROM announcements WHERE is_active = 1");
        $stats['total_announcements'] = $result[0]['total'] ?? 0;

        // Active announcements
        $result = self::query("SELECT COUNT(*) as total FROM announcements WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())");
        $stats['active_announcements'] = $result[0]['total'] ?? 0;

        // Expired announcements
        $result = self::query("SELECT COUNT(*) as total FROM announcements WHERE is_active = 1 AND expires_at IS NOT NULL AND expires_at <= NOW()");
        $stats['expired_announcements'] = $result[0]['total'] ?? 0;

        // By priority
        $result = self::query("SELECT priority, COUNT(*) as count FROM announcements WHERE is_active = 1 GROUP BY priority");
        $stats['priority_breakdown'] = array_column($result, 'count', 'priority');

        // By visibility
        $result = self::query("SELECT visibility, COUNT(*) as count FROM announcements WHERE is_active = 1 GROUP BY visibility");
        $stats['visibility_breakdown'] = array_column($result, 'count', 'visibility');

        return $stats;
    }

    /**
     * Get announcements for specific user role
     */
    public static function getAnnouncementsForUser($userRole = 'all', $limit = null) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.is_active = 1
            AND (a.expires_at IS NULL OR a.expires_at > NOW())
            AND (a.visibility = 'all' OR a.visibility = ?)
            ORDER BY
                CASE a.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                a.created_at DESC
        ";

        $params = [$userRole];

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            // No additional params for limit
        }

        return self::query($query, $params);
    }

    /**
     * Check if announcement is visible to user role
     */
    public static function isVisibleToUser($announcementId, $userRole) {
        $announcement = self::getAnnouncementById($announcementId);

        if (!$announcement) {
            return false;
        }

        // Check if expired
        if ($announcement['expires_at'] && strtotime($announcement['expires_at']) <= time()) {
            return false;
        }

        // Check visibility
        if ($announcement['visibility'] === 'all') {
            return true;
        }

        return $announcement['visibility'] === $userRole;
    }

    /**
     * Mark announcement as read (for future implementation)
     */
    public static function markAsRead($announcementId, $userId) {
        // This would require a user_announcement_reads table
        // For now, just return true
        return true;
    }

    /**
     * Create a new announcement
     */
    public static function createAnnouncement($data) {
        $instance = new self($data);
        if ($instance->save()) {
            return $instance;
        }
        return false;
    }

    /**
     * Update an existing announcement
     */
    public static function updateAnnouncement($id, $data) {
        $announcement = self::find($id);
        if (!$announcement) {
            return false;
        }

        $announcement->fill($data);
        return $announcement->save();
    }

    /**
     * Delete an announcement
     */
    public static function deleteAnnouncement($id) {
        $announcement = self::find($id);
        if (!$announcement) {
            return false;
        }

        return $announcement->delete();
    }

    /**
     * Get announcements for admin listing with filters
     */
    public static function getAnnouncementsForAdmin($filters = [], $limit = null, $offset = null) {
        $query = "
            SELECT a.*, u.username as created_by_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        // Priority filter
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $conditions[] = "a.priority = ?";
            $params[] = $filters['priority'];
        }

        // Visibility filter
        if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
            $conditions[] = "a.visibility = ?";
            $params[] = $filters['visibility'];
        }

        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'active':
                    $conditions[] = "a.is_active = 1 AND (a.expires_at IS NULL OR a.expires_at > NOW())";
                    break;
                case 'expired':
                    $conditions[] = "a.is_active = 1 AND a.expires_at IS NOT NULL AND a.expires_at <= NOW()";
                    break;
                case 'inactive':
                    $conditions[] = "a.is_active = 0";
                    break;
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $conditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY a.created_at DESC";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }

        return self::query($query, $params);
    }

    /**
     * Get total count of announcements for admin with filters
     */
    public static function getAnnouncementsCountForAdmin($filters = []) {
        $query = "SELECT COUNT(*) as total FROM announcements a WHERE 1=1";

        $params = [];
        $conditions = [];

        // Priority filter
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $conditions[] = "a.priority = ?";
            $params[] = $filters['priority'];
        }

        // Visibility filter
        if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
            $conditions[] = "a.visibility = ?";
            $params[] = $filters['visibility'];
        }

        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'active':
                    $conditions[] = "a.is_active = 1 AND (a.expires_at IS NULL OR a.expires_at > NOW())";
                    break;
                case 'expired':
                    $conditions[] = "a.is_active = 1 AND a.expires_at IS NOT NULL AND a.expires_at <= NOW()";
                    break;
                case 'inactive':
                    $conditions[] = "a.is_active = 0";
                    break;
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $conditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        $result = self::query($query, $params);
        return $result[0]['total'] ?? 0;
    }
}