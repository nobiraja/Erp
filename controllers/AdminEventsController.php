<?php
/**
 * Admin Events Controller
 * Handles admin event management operations
 */

class AdminEventsController extends BaseController {

    /**
     * Display events list
     */
    public function index() {
        try {
            // Get filter parameters
            $search = $this->input('search');
            $status = $this->input('status', 'all'); // all, active, inactive, upcoming, past
            $page = (int) $this->input('page', 1);
            $perPage = 25;

            // Build query
            $query = "SELECT e.*, u.username as created_by_name
                     FROM events e
                     LEFT JOIN users u ON e.created_by = u.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($search) {
                $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR e.organizer LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
            }

            if ($status !== 'all') {
                switch ($status) {
                    case 'active':
                        $conditions[] = "e.is_active = 1";
                        break;
                    case 'inactive':
                        $conditions[] = "e.is_active = 0";
                        break;
                    case 'upcoming':
                        $conditions[] = "e.is_active = 1 AND e.event_date >= CURDATE()";
                        break;
                    case 'past':
                        $conditions[] = "e.is_active = 1 AND e.event_date < CURDATE()";
                        break;
                }
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count for pagination
            $countQuery = str_replace("SELECT e.*, u.username as created_by_name", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY e.event_date DESC, e.event_time DESC LIMIT " . (($page - 1) * $perPage) . ", {$perPage}";

            $events = $this->db->fetchAll($query, $params);

            // Calculate pagination
            $totalPages = ceil($total / $perPage);

            // Get events statistics
            $stats = EventsModel::getEventsStats();

            $data = [
                'title' => 'Events Management',
                'events' => $events,
                'filters' => [
                    'search' => $search,
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

            echo $this->view('admin.events.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading events: ' . $e->getMessage());
            echo $this->view('admin.events.index', [
                'title' => 'Events Management',
                'events' => [],
                'filters' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 0, 'total' => 0, 'per_page' => 25],
                'stats' => ['total_events' => 0, 'upcoming_events' => 0, 'past_events' => 0, 'this_month_events' => 0]
            ]);
        }
    }

    /**
     * Show create event form
     */
    public function create() {
        $data = [
            'title' => 'Add New Event',
            'event' => null
        ];

        echo $this->view('admin.events.create', $data);
    }

    /**
     * Store new event
     */
    public function store() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/events');
        }

        // Validation rules
        $rules = [
            'title' => 'required|max:200',
            'description' => 'max:5000',
            'event_date' => 'required|date',
            'event_time' => 'time',
            'location' => 'max:200',
            'organizer' => 'max:100',
            'contact_info' => 'max:100',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/admin/events/create');
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($this->file('image')) {
                $upload = $this->handleUpload('image', 'uploads/events/images', ['jpg', 'jpeg', 'png', 'gif'], 5 * 1024 * 1024); // 5MB limit
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                } else {
                    $this->flash('error', 'Image upload failed: ' . $upload['error']);
                    $this->flash('old_input', $this->all());
                    $this->redirect('/admin/events/create');
                }
            }

            // Create event
            $eventData = $validated;
            $eventData['image_path'] = $imagePath;
            $eventData['created_by'] = $this->getCurrentUserId();

            $event = EventsModel::create($eventData);

            if ($event) {
                $this->flash('success', 'Event created successfully');
                $this->redirect('/admin/events');
            } else {
                $this->flash('error', 'Failed to create event');
                $this->redirect('/admin/events/create');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error creating event: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/admin/events/create');
        }
    }

    /**
     * Show edit event form
     */
    public function edit($id) {
        try {
            $event = EventsModel::getEventById($id);

            if (!$event) {
                $this->flash('error', 'Event not found');
                $this->redirect('/admin/events');
            }

            $data = [
                'title' => 'Edit Event - ' . $event['title'],
                'event' => $event
            ];

            echo $this->view('admin.events.edit', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading edit form: ' . $e->getMessage());
            $this->redirect('/admin/events');
        }
    }

    /**
     * Update event
     */
    public function update($id) {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/events');
        }

        $event = EventsModel::getEventById($id);
        if (!$event) {
            $this->flash('error', 'Event not found');
            $this->redirect('/admin/events');
        }

        // Validation rules
        $rules = [
            'title' => 'required|max:200',
            'description' => 'max:5000',
            'event_date' => 'required|date',
            'event_time' => 'time',
            'location' => 'max:200',
            'organizer' => 'max:100',
            'contact_info' => 'max:100',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect("/admin/events/{$id}/edit");
        }

        try {
            // Handle image upload
            if ($this->file('image')) {
                $upload = $this->handleUpload('image', 'uploads/events/images', ['jpg', 'jpeg', 'png', 'gif'], 5 * 1024 * 1024);
                if ($upload['success']) {
                    // Delete old image if exists
                    if ($event['image_path'] && file_exists($event['image_path'])) {
                        unlink($event['image_path']);
                    }
                    $validated['image_path'] = $upload['path'];
                } else {
                    $this->flash('error', 'Image upload failed: ' . $upload['error']);
                    $this->flash('old_input', $this->all());
                    $this->redirect("/admin/events/{$id}/edit");
                }
            }

            // Update event
            $eventModel = new EventsModel($event);
            $eventModel->fill($validated);

            if ($eventModel->save()) {
                $this->flash('success', 'Event updated successfully');
                $this->redirect('/admin/events');
            } else {
                $this->flash('error', 'Failed to update event');
                $this->redirect("/admin/events/{$id}/edit");
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error updating event: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect("/admin/events/{$id}/edit");
        }
    }

    /**
     * Delete event
     */
    public function destroy($id) {
        try {
            $event = EventsModel::getEventById($id);

            if (!$event) {
                if ($this->isAjax()) {
                    $this->error('Event not found');
                }
                $this->flash('error', 'Event not found');
                $this->redirect('/admin/events');
            }

            // Delete image if exists
            if ($event['image_path'] && file_exists($event['image_path'])) {
                unlink($event['image_path']);
            }

            $eventModel = new EventsModel($event);
            if ($eventModel->delete()) {
                if ($this->isAjax()) {
                    $this->success(['message' => 'Event deleted successfully']);
                }
                $this->flash('success', 'Event deleted successfully');
            } else {
                if ($this->isAjax()) {
                    $this->error('Failed to delete event');
                }
                $this->flash('error', 'Failed to delete event');
            }

        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->error('Error deleting event: ' . $e->getMessage());
            }
            $this->flash('error', 'Error deleting event: ' . $e->getMessage());
        }

        if (!$this->isAjax()) {
            $this->redirect('/admin/events');
        }
    }

    /**
     * Show calendar view
     */
    public function calendar() {
        try {
            $data = [
                'title' => 'Events Calendar',
                'current_year' => date('Y'),
                'current_month' => date('m')
            ];

            echo $this->view('admin.events.calendar', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading calendar: ' . $e->getMessage());
            $this->redirect('/admin/events');
        }
    }

    /**
     * AJAX: Get events for calendar
     */
    public function ajaxCalendar() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        $year = $this->input('year', date('Y'));
        $month = $this->input('month', date('m'));

        try {
            $events = EventsModel::getEventsByMonth($year, $month);

            // Format events for calendar
            $formattedEvents = [];
            foreach ($events as $event) {
                $formattedEvents[] = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'date' => $event['event_date'],
                    'time' => $event['event_time'],
                    'location' => $event['location'],
                    'description' => $event['description'],
                    'image' => $event['image_path'],
                    'is_active' => $event['is_active']
                ];
            }

            $this->success($formattedEvents);

        } catch (Exception $e) {
            $this->error('Error loading calendar events: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get events list for datatable
     */
    public function ajaxList() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $start = (int) $this->input('start', 0);
            $length = (int) $this->input('length', 10);
            $search = $this->input('search')['value'] ?? '';
            $status = $this->input('status', 'all');

            // Build query
            $query = "SELECT e.*, u.username as created_by_name
                     FROM events e
                     LEFT JOIN users u ON e.created_by = u.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($search) {
                $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }

            if ($status !== 'all') {
                switch ($status) {
                    case 'active':
                        $conditions[] = "e.is_active = 1";
                        break;
                    case 'inactive':
                        $conditions[] = "e.is_active = 0";
                        break;
                    case 'upcoming':
                        $conditions[] = "e.is_active = 1 AND e.event_date >= CURDATE()";
                        break;
                    case 'past':
                        $conditions[] = "e.is_active = 1 AND e.event_date < CURDATE()";
                        break;
                }
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count
            $countQuery = str_replace("SELECT e.*, u.username as created_by_name", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY e.event_date DESC, e.event_time DESC LIMIT {$start}, {$length}";

            $events = $this->db->fetchAll($query, $params);

            // Format data for DataTables
            $data = [];
            foreach ($events as $event) {
                $statusBadge = $event['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                $eventDate = date('d/m/Y', strtotime($event['event_date']));
                $eventTime = $event['event_time'] ? date('H:i', strtotime($event['event_time'])) : '-';

                $data[] = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'date' => $eventDate,
                    'time' => $eventTime,
                    'location' => $event['location'] ?: '-',
                    'organizer' => $event['organizer'] ?: '-',
                    'status' => $statusBadge,
                    'created_by' => $event['created_by_name'] ?: 'System',
                    'actions' => '<a href="/admin/events/' . $event['id'] . '/edit" class="btn btn-sm btn-warning">Edit</a> ' .
                                '<button onclick="deleteEvent(' . $event['id'] . ')" class="btn btn-sm btn-danger">Delete</button>'
                ];
            }

            $this->json([
                'draw' => (int) $this->input('draw', 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data
            ]);

        } catch (Exception $e) {
            $this->error('Error loading events: ' . $e->getMessage());
        }
    }


    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}