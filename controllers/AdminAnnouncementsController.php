<?php
/**
 * Admin Announcements Controller
 * Handles admin announcement management operations
 */

class AdminAnnouncementsController extends BaseController {

    /**
     * Display announcements list
     */
    public function index() {
        try {
            // Get filter parameters
            $search = $this->input('search');
            $priority = $this->input('priority', 'all');
            $visibility = $this->input('visibility', 'all');
            $status = $this->input('status', 'all'); // all, active, expired
            $page = (int) $this->input('page', 1);
            $perPage = 25;

            // Build query
            $query = "SELECT a.*, u.username as created_by_name
                     FROM announcements a
                     LEFT JOIN users u ON a.created_by = u.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($search) {
                $conditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam]);
            }

            if ($priority !== 'all') {
                $conditions[] = "a.priority = ?";
                $params[] = $priority;
            }

            if ($visibility !== 'all') {
                $conditions[] = "a.visibility = ?";
                $params[] = $visibility;
            }

            if ($status !== 'all') {
                switch ($status) {
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

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count for pagination
            $countQuery = str_replace("SELECT a.*, u.username as created_by_name", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY
                CASE a.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                a.created_at DESC LIMIT " . (($page - 1) * $perPage) . ", {$perPage}";

            $announcements = $this->db->fetchAll($query, $params);

            // Calculate pagination
            $totalPages = ceil($total / $perPage);

            // Get announcements statistics
            $stats = AnnouncementsModel::getAnnouncementsStats();

            $data = [
                'title' => 'Announcements Management',
                'announcements' => $announcements,
                'filters' => [
                    'search' => $search,
                    'priority' => $priority,
                    'visibility' => $visibility,
                    'status' => $status
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                    'per_page' => $perPage
                ],
                'stats' => $stats
            ];

            echo $this->view('admin.announcements.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading announcements: ' . $e->getMessage());
            echo $this->view('admin.announcements.index', [
                'title' => 'Announcements Management',
                'announcements' => [],
                'filters' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 0, 'total' => 0, 'per_page' => 25],
                'stats' => ['total_announcements' => 0, 'active_announcements' => 0, 'expired_announcements' => 0]
            ]);
        }
    }

    /**
     * Show create announcement form
     */
    public function create() {
        $data = [
            'title' => 'Add New Announcement',
            'announcement' => null
        ];

        echo $this->view('admin.announcements.create', $data);
    }

    /**
     * Store new announcement
     */
    public function store() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/announcements');
        }

        // Validation rules
        $rules = [
            'title' => 'required|max:200',
            'content' => 'required|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'visibility' => 'required|in:all,students,teachers,parents,admin',
            'expires_at' => 'date',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/admin/announcements/create');
        }

        try {
            // Handle target audience (JSON)
            $targetAudience = null;
            if ($this->input('target_audience')) {
                $targetAudience = json_encode($this->input('target_audience'));
            }

            // Handle expiration date
            if (empty($validated['expires_at'])) {
                $validated['expires_at'] = null;
            }

            // Create announcement
            $announcementData = $validated;
            $announcementData['target_audience'] = $targetAudience;
            $announcementData['created_by'] = $this->getCurrentUserId();

            $announcement = AnnouncementsModel::create($announcementData);

            if ($announcement) {
                $this->flash('success', 'Announcement created successfully');
                $this->redirect('/admin/announcements');
            } else {
                $this->flash('error', 'Failed to create announcement');
                $this->redirect('/admin/announcements/create');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error creating announcement: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/admin/announcements/create');
        }
    }

    /**
     * Show edit announcement form
     */
    public function edit($id) {
        try {
            $announcement = AnnouncementsModel::getAnnouncementById($id);

            if (!$announcement) {
                $this->flash('error', 'Announcement not found');
                $this->redirect('/admin/announcements');
            }

            // Decode target audience
            if ($announcement['target_audience']) {
                $announcement['target_audience'] = json_decode($announcement['target_audience'], true);
            }

            $data = [
                'title' => 'Edit Announcement - ' . $announcement['title'],
                'announcement' => $announcement
            ];

            echo $this->view('admin.announcements.edit', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading edit form: ' . $e->getMessage());
            $this->redirect('/admin/announcements');
        }
    }

    /**
     * Update announcement
     */
    public function update($id) {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/announcements');
        }

        $announcement = AnnouncementsModel::getAnnouncementById($id);
        if (!$announcement) {
            $this->flash('error', 'Announcement not found');
            $this->redirect('/admin/announcements');
        }

        // Validation rules
        $rules = [
            'title' => 'required|max:200',
            'content' => 'required|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'visibility' => 'required|in:all,students,teachers,parents,admin',
            'expires_at' => 'date',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect("/admin/announcements/{$id}/edit");
        }

        try {
            // Handle target audience (JSON)
            $targetAudience = null;
            if ($this->input('target_audience')) {
                $targetAudience = json_encode($this->input('target_audience'));
            }

            // Handle expiration date
            if (empty($validated['expires_at'])) {
                $validated['expires_at'] = null;
            }

            // Update announcement
            $announcementModel = new AnnouncementsModel($announcement);
            $validated['target_audience'] = $targetAudience;
            $announcementModel->fill($validated);

            if ($announcementModel->save()) {
                $this->flash('success', 'Announcement updated successfully');
                $this->redirect('/admin/announcements');
            } else {
                $this->flash('error', 'Failed to update announcement');
                $this->redirect("/admin/announcements/{$id}/edit");
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error updating announcement: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect("/admin/announcements/{$id}/edit");
        }
    }

    /**
     * Delete announcement
     */
    public function destroy($id) {
        try {
            $announcement = AnnouncementsModel::getAnnouncementById($id);

            if (!$announcement) {
                if ($this->isAjax()) {
                    $this->error('Announcement not found');
                }
                $this->flash('error', 'Announcement not found');
                $this->redirect('/admin/announcements');
            }

            $announcementModel = new AnnouncementsModel($announcement);
            if ($announcementModel->delete()) {
                if ($this->isAjax()) {
                    $this->success(['message' => 'Announcement deleted successfully']);
                }
                $this->flash('success', 'Announcement deleted successfully');
            } else {
                if ($this->isAjax()) {
                    $this->error('Failed to delete announcement');
                }
                $this->flash('error', 'Failed to delete announcement');
            }

        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->error('Error deleting announcement: ' . $e->getMessage());
            }
            $this->flash('error', 'Error deleting announcement: ' . $e->getMessage());
        }

        if (!$this->isAjax()) {
            $this->redirect('/admin/announcements');
        }
    }

    /**
     * AJAX: Get announcements list for datatable
     */
    public function ajaxList() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $start = (int) $this->input('start', 0);
            $length = (int) $this->input('length', 10);
            $search = $this->input('search')['value'] ?? '';
            $priority = $this->input('priority', 'all');
            $visibility = $this->input('visibility', 'all');
            $status = $this->input('status', 'all');

            // Build query
            $query = "SELECT a.*, u.username as created_by_name
                     FROM announcements a
                     LEFT JOIN users u ON a.created_by = u.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($search) {
                $conditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam]);
            }

            if ($priority !== 'all') {
                $conditions[] = "a.priority = ?";
                $params[] = $priority;
            }

            if ($visibility !== 'all') {
                $conditions[] = "a.visibility = ?";
                $params[] = $visibility;
            }

            if ($status !== 'all') {
                switch ($status) {
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

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count
            $countQuery = str_replace("SELECT a.*, u.username as created_by_name", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY
                CASE a.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                a.created_at DESC LIMIT {$start}, {$length}";

            $announcements = $this->db->fetchAll($query, $params);

            // Format data for DataTables
            $data = [];
            foreach ($announcements as $announcement) {
                $priorityBadge = $this->getPriorityBadge($announcement['priority']);
                $visibilityBadge = $this->getVisibilityBadge($announcement['visibility']);
                $statusBadge = $this->getStatusBadge($announcement);
                $createdDate = date('d/m/Y H:i', strtotime($announcement['created_at']));

                $data[] = [
                    'id' => $announcement['id'],
                    'title' => $announcement['title'],
                    'priority' => $priorityBadge,
                    'visibility' => $visibilityBadge,
                    'status' => $statusBadge,
                    'created_by' => $announcement['created_by_name'] ?: 'System',
                    'created_at' => $createdDate,
                    'actions' => '<a href="/admin/announcements/' . $announcement['id'] . '/edit" class="btn btn-sm btn-warning">Edit</a> ' .
                                '<button onclick="deleteAnnouncement(' . $announcement['id'] . ')" class="btn btn-sm btn-danger">Delete</button>'
                ];
            }

            $this->json([
                'draw' => (int) $this->input('draw', 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data
            ]);

        } catch (Exception $e) {
            $this->error('Error loading announcements: ' . $e->getMessage());
        }
    }

    /**
     * Get priority badge HTML
     */
    private function getPriorityBadge($priority) {
        $badges = [
            'low' => '<span class="badge bg-secondary">Low</span>',
            'medium' => '<span class="badge bg-info">Medium</span>',
            'high' => '<span class="badge bg-warning">High</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>'
        ];
        return $badges[$priority] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get visibility badge HTML
     */
    private function getVisibilityBadge($visibility) {
        $badges = [
            'all' => '<span class="badge bg-primary">All Users</span>',
            'students' => '<span class="badge bg-success">Students</span>',
            'teachers' => '<span class="badge bg-info">Teachers</span>',
            'parents' => '<span class="badge bg-warning">Parents</span>',
            'admin' => '<span class="badge bg-danger">Admin Only</span>'
        ];
        return $badges[$visibility] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($announcement) {
        if (!$announcement['is_active']) {
            return '<span class="badge bg-secondary">Inactive</span>';
        }

        if ($announcement['expires_at'] && strtotime($announcement['expires_at']) <= time()) {
            return '<span class="badge bg-dark">Expired</span>';
        }

        return '<span class="badge bg-success">Active</span>';
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}