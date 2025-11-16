<?php
/**
 * Events Controller
 * Handles public events page functionality
 */

class EventsController extends BaseController {

    /**
     * Display events page
     */
    public function index() {
        try {
            // Get upcoming events
            $upcomingEvents = EventsModel::getUpcomingEvents(6);

            // Get past events
            $pastEvents = EventsModel::getPastEvents(3);

            // Get events statistics
            $stats = EventsModel::getEventsStats();

            // Get current month events for calendar
            $currentMonthEvents = EventsModel::getEventsByMonth(date('Y'), date('m'));
        } catch (Exception $e) {
            // Handle database errors gracefully
            $upcomingEvents = $this->getDefaultUpcomingEvents();
            $pastEvents = $this->getDefaultPastEvents();
            $stats = $this->getDefaultStats();
            $currentMonthEvents = [];
        }

        // Get school settings for meta tags
        $schoolName = $this->getSetting('school_name', 'School Management System');

        // Prepare view data
        $data = [
            'title' => $schoolName . ' - Events & Activities',
            'meta_description' => 'Stay updated with upcoming events, school activities, and celebrations at ' . $schoolName . '. Join us for memorable experiences and learning opportunities.',
            'meta_keywords' => 'school events, activities, celebrations, calendar, upcoming events, school activities, student events',
            'school_name' => $schoolName,
            'upcoming_events' => $upcomingEvents,
            'past_events' => $pastEvents,
            'stats' => $stats,
            'current_month_events' => $currentMonthEvents,
            'current_year' => date('Y'),
            'current_month' => date('m'),
            'current_month_name' => date('F')
        ];

        // Render events view
        echo $this->view('public.events.index', $data);
    }

    /**
     * AJAX endpoint to get events for calendar
     */
    public function getCalendar() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        try {
            $events = EventsModel::getEventsByMonth($year, $month);
            $this->success($events);
        } catch (Exception $e) {
            $this->success([]);
        }
    }

    /**
     * AJAX endpoint to get upcoming events
     */
    public function getUpcoming() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $limit = $_GET['limit'] ?? null;

        try {
            $events = EventsModel::getUpcomingEvents($limit);
            $this->success($events);
        } catch (Exception $e) {
            $this->success($this->getDefaultUpcomingEvents());
        }
    }

    /**
     * Get event details (for future single event view)
     */
    public function show($id) {
        try {
            $event = EventsModel::getEventById($id);

            if (!$event) {
                $this->error('Event not found', 404);
            }

            $schoolName = $this->getSetting('school_name', 'School Management System');

            $data = [
                'title' => $event['title'] . ' - ' . $schoolName . ' Events',
                'meta_description' => substr($event['description'] ?? '', 0, 160),
                'school_name' => $schoolName,
                'event' => $event,
                'current_year' => date('Y')
            ];

            echo $this->view('public.events.show', $data);
        } catch (Exception $e) {
            $this->error('Event not found', 404);
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
     * Get default upcoming events when database is not available
     */
    private function getDefaultUpcomingEvents() {
        return [
            [
                'id' => 1,
                'title' => 'Annual Sports Day',
                'description' => 'Join us for an exciting day of sports competitions, games, and celebrations.',
                'event_date' => date('Y-m-d', strtotime('+7 days')),
                'event_time' => '09:00:00',
                'location' => 'School Ground',
                'organizer' => 'Sports Department',
                'contact_info' => 'sports@school.com',
                'image_path' => '/images/sports-day.jpg'
            ],
            [
                'id' => 2,
                'title' => 'Science Fair 2024',
                'description' => 'Showcase your innovative science projects and compete for prizes.',
                'event_date' => date('Y-m-d', strtotime('+14 days')),
                'event_time' => '10:00:00',
                'location' => 'Science Lab',
                'organizer' => 'Science Department',
                'contact_info' => 'science@school.com',
                'image_path' => '/images/science-fair.jpg'
            ],
            [
                'id' => 3,
                'title' => 'Cultural Fest',
                'description' => 'Celebrate diversity through music, dance, and cultural performances.',
                'event_date' => date('Y-m-d', strtotime('+21 days')),
                'event_time' => '14:00:00',
                'location' => 'Auditorium',
                'organizer' => 'Cultural Committee',
                'contact_info' => 'cultural@school.com',
                'image_path' => '/images/cultural-fest.jpg'
            ]
        ];
    }

    /**
     * Get default past events
     */
    private function getDefaultPastEvents() {
        return [
            [
                'id' => 4,
                'title' => 'Independence Day Celebration',
                'description' => 'Patriotic celebrations with flag hoisting and cultural programs.',
                'event_date' => '2024-08-15',
                'event_time' => '08:00:00',
                'location' => 'School Assembly',
                'image_path' => '/images/independence-day.jpg'
            ],
            [
                'id' => 5,
                'title' => 'Parent-Teacher Meeting',
                'description' => 'Interactive session between parents and teachers to discuss student progress.',
                'event_date' => '2024-07-20',
                'event_time' => '09:00:00',
                'location' => 'Classrooms',
                'image_path' => '/images/ptm.jpg'
            ]
        ];
    }

    /**
     * Get default statistics
     */
    private function getDefaultStats() {
        return [
            'total_events' => 25,
            'upcoming_events' => 8,
            'past_events' => 17,
            'this_month_events' => 3
        ];
    }
}