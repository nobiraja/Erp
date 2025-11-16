<?php
/**
 * Events Model
 * Handles events data operations
 */

class EventsModel extends BaseModel {
    protected $table = 'events';
    protected $fillable = [
        'title',
        'description',
        'event_type',
        'event_date',
        'event_time',
        'registration_required',
        'location',
        'venue',
        'organizer',
        'contact_info',
        'additional_info',
        'max_participants',
        'registration_deadline',
        'image_path',
        'is_active'
    ];

    /**
     * Get upcoming events
     */
    public static function getUpcomingEvents($limit = null) {
        $query = "
            SELECT * FROM events
            WHERE is_active = 1
            AND event_date >= CURDATE()
            ORDER BY event_date ASC, event_time ASC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query);
    }

    /**
     * Get past events
     */
    public static function getPastEvents($limit = null) {
        $query = "
            SELECT * FROM events
            WHERE is_active = 1
            AND event_date < CURDATE()
            ORDER BY event_date DESC, event_time DESC
        ";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }

        return self::query($query);
    }

    /**
     * Get events by month
     */
    public static function getEventsByMonth($year, $month) {
        $query = "
            SELECT * FROM events
            WHERE is_active = 1
            AND YEAR(event_date) = ?
            AND MONTH(event_date) = ?
            ORDER BY event_date ASC, event_time ASC
        ";

        return self::query($query, [$year, $month]);
    }

    /**
     * Get events for calendar view
     */
    public static function getEventsForCalendar($startDate = null, $endDate = null) {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-t');

        $query = "
            SELECT
                id,
                title,
                description,
                event_type,
                event_date,
                event_time,
                registration_required,
                location,
                venue,
                organizer,
                contact_info,
                additional_info,
                max_participants,
                registration_deadline,
                image_path
            FROM events
            WHERE is_active = 1
            AND event_date BETWEEN ? AND ?
            ORDER BY event_date ASC, event_time ASC
        ";

        return self::query($query, [$startDate, $endDate]);
    }

    /**
     * Get event by ID
     */
    public static function getEventById($id) {
        return self::find($id);
    }

    /**
     * Get events statistics
     */
    public static function getEventsStats() {
        $stats = [];

        // Total events
        $result = self::query("SELECT COUNT(*) as total FROM events WHERE is_active = 1");
        $stats['total_events'] = $result[0]['total'] ?? 0;

        // Upcoming events
        $result = self::query("SELECT COUNT(*) as total FROM events WHERE is_active = 1 AND event_date >= CURDATE()");
        $stats['upcoming_events'] = $result[0]['total'] ?? 0;

        // Past events
        $result = self::query("SELECT COUNT(*) as total FROM events WHERE is_active = 1 AND event_date < CURDATE()");
        $stats['past_events'] = $result[0]['total'] ?? 0;

        // This month's events
        $result = self::query("SELECT COUNT(*) as total FROM events WHERE is_active = 1 AND YEAR(event_date) = YEAR(CURDATE()) AND MONTH(event_date) = MONTH(CURDATE())");
        $stats['this_month_events'] = $result[0]['total'] ?? 0;

        return $stats;
    }

    /**
     * Get events by category/type (if we add category field later)
     */
    public static function getEventsByCategory($category = null) {
        if (!$category) {
            return self::getUpcomingEvents();
        }

        // For now, return all upcoming events since we don't have category field
        // This can be extended when category field is added to events table
        return self::getUpcomingEvents();
    }

    /**
     * Create a new event
     */
    public static function createEvent($data) {
        $instance = new self($data);
        if ($instance->save()) {
            return $instance;
        }
        return false;
    }

    /**
     * Update an existing event
     */
    public static function updateEvent($id, $data) {
        $event = self::find($id);
        if (!$event) {
            return false;
        }

        $event->fill($data);
        return $event->save();
    }

    /**
     * Delete an event
     */
    public static function deleteEvent($id) {
        $event = self::find($id);
        if (!$event) {
            return false;
        }

        // Delete associated image if exists
        if ($event->image_path && file_exists($event->image_path)) {
            unlink($event->image_path);
        }

        return $event->delete();
    }

    /**
     * Get events for admin listing with filters
     */
    public static function getEventsForAdmin($filters = [], $limit = null, $offset = null) {
        $query = "
            SELECT e.*, u.username as created_by_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            switch ($filters['status']) {
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

        // Search filter
        if (!empty($filters['search'])) {
            $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR e.organizer LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY e.event_date DESC, e.event_time DESC";

        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }

        return self::query($query, $params);
    }

    /**
     * Get total count of events for admin with filters
     */
    public static function getEventsCountForAdmin($filters = []) {
        $query = "SELECT COUNT(*) as total FROM events e WHERE 1=1";

        $params = [];
        $conditions = [];

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            switch ($filters['status']) {
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

        // Search filter
        if (!empty($filters['search'])) {
            $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR e.organizer LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        $result = self::query($query, $params);
        return $result[0]['total'] ?? 0;
    }

    /**
     * Get registration count for an event
     */
    public static function getRegistrationCount($eventId) {
        $query = "SELECT COUNT(*) as total FROM event_registrations WHERE event_id = ? AND status = 'registered'";
        $result = self::query($query, [$eventId]);
        return $result[0]['total'] ?? 0;
    }

    /**
     * Check if parent is registered for an event
     */
    public static function isParentRegistered($eventId, $parentId, $studentId = null) {
        $query = "SELECT id FROM event_registrations WHERE event_id = ? AND parent_id = ? AND status = 'registered'";
        $params = [$eventId, $parentId];

        if ($studentId) {
            $query .= " AND student_id = ?";
            $params[] = $studentId;
        }

        $result = self::query($query, $params);
        return !empty($result);
    }

    /**
     * Register parent for event
     */
    public static function registerForEvent($eventId, $parentId, $studentId = null, $notes = null) {
        $instance = new self();

        // Check if already registered
        if (self::isParentRegistered($eventId, $parentId, $studentId)) {
            return ['success' => false, 'message' => 'Already registered for this event'];
        }

        // Check event capacity if limited
        $event = self::find($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if ($event->max_participants) {
            $currentRegistrations = self::getRegistrationCount($eventId);
            if ($currentRegistrations >= $event->max_participants) {
                return ['success' => false, 'message' => 'Event is full'];
            }
        }

        // Check registration deadline
        if ($event->registration_deadline && strtotime($event->registration_deadline) < time()) {
            return ['success' => false, 'message' => 'Registration deadline has passed'];
        }

        // Insert registration
        $data = [
            'event_id' => $eventId,
            'parent_id' => $parentId,
            'student_id' => $studentId,
            'status' => 'registered',
            'notes' => $notes
        ];

        $registrationId = $instance->db->insert('event_registrations', $data);

        if ($registrationId) {
            return ['success' => true, 'message' => 'Successfully registered for the event', 'registration_id' => $registrationId];
        }

        return ['success' => false, 'message' => 'Failed to register for the event'];
    }

    /**
     * Cancel event registration
     */
    public static function cancelRegistration($eventId, $parentId, $studentId = null) {
        $instance = new self();

        $query = "UPDATE event_registrations SET status = 'cancelled', updated_at = NOW() WHERE event_id = ? AND parent_id = ? AND status = 'registered'";
        $params = [$eventId, $parentId];

        if ($studentId) {
            $query .= " AND student_id = ?";
            $params[] = $studentId;
        }

        $result = $instance->db->query($query, $params);

        if ($result) {
            return ['success' => true, 'message' => 'Registration cancelled successfully'];
        }

        return ['success' => false, 'message' => 'Failed to cancel registration'];
    }

    /**
     * Get parent registrations
     */
    public static function getParentRegistrations($parentId) {
        $query = "
            SELECT er.*, e.title, e.event_date, e.event_time, e.location, e.venue,
                   s.first_name, s.last_name, s.scholar_number
            FROM event_registrations er
            LEFT JOIN events e ON er.event_id = e.id
            LEFT JOIN students s ON er.student_id = s.id
            WHERE er.parent_id = ? AND er.status = 'registered'
            ORDER BY e.event_date ASC
        ";

        return self::query($query, [$parentId]);
    }
}