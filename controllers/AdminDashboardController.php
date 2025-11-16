<?php
/**
 * Admin Dashboard Controller
 * Handles admin dashboard functionality with role-based access control
 */

class AdminDashboardController extends BaseController {

    /**
     * Constructor - Apply admin middleware
     */
    public function __construct() {
        parent::__construct();
        $this->middleware(RoleCheckMiddleware::admin());
    }

    /**
     * Display admin dashboard
     */
    public function index() {
        try {
            // Get dashboard data
            $dashboardData = AdminDashboardModel::getDashboardSummary();

            // Get school settings
            $schoolName = $this->getSetting('school_name', 'School Management System');
            $schoolLogo = $this->getSetting('school_logo', '/images/logo.png');

            // Prepare view data
            $data = [
                'title' => 'Admin Dashboard - ' . $schoolName,
                'school_name' => $schoolName,
                'school_logo' => $schoolLogo,
                'dashboard_data' => $dashboardData,
                'current_user' => $this->getUserData(),
                'current_year' => date('Y'),
                'current_month' => date('F Y')
            ];

            // Render dashboard view
            echo $this->view('admin.dashboard.index', $data);

        } catch (Exception $e) {
            // Log error and show error page
            error_log("Dashboard error: " . $e->getMessage());
            $this->error('Failed to load dashboard data', [], 500);
        }
    }

    /**
     * AJAX endpoint to get dashboard statistics
     */
    public function getStats() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = AdminDashboardModel::getDashboardSummary();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load statistics');
        }
    }

    /**
     * AJAX endpoint to get user statistics
     */
    public function getUserStats() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = AdminDashboardModel::getUserStatistics();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load user statistics');
        }
    }

    /**
     * AJAX endpoint to get attendance statistics
     */
    public function getAttendanceStats() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = AdminDashboardModel::getAttendanceStatistics();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load attendance statistics');
        }
    }

    /**
     * AJAX endpoint to get fee statistics
     */
    public function getFeeStats() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = AdminDashboardModel::getFeeStatistics();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load fee statistics');
        }
    }

    /**
     * AJAX endpoint to get recent activities
     */
    public function getRecentActivities() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $limit = $this->input('limit', 10);

        try {
            $activities = AdminDashboardModel::getRecentActivities($limit);
            $this->success($activities);
        } catch (Exception $e) {
            $this->error('Failed to load recent activities');
        }
    }

    /**
     * AJAX endpoint to get class attendance stats for charts
     */
    public function getClassAttendanceChart() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = AdminDashboardModel::getClassAttendanceStats();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load class attendance data');
        }
    }

    /**
     * AJAX endpoint to get monthly fee collection for charts
     */
    public function getMonthlyFeeChart() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $data = AdminDashboardModel::getMonthlyFeeCollection();
            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load monthly fee data');
        }
    }

    /**
     * AJAX endpoint to get upcoming events
     */
    public function getUpcomingEvents() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $limit = $this->input('limit', 5);

        try {
            $events = AdminDashboardModel::getUpcomingEvents($limit);
            $this->success($events);
        } catch (Exception $e) {
            $this->error('Failed to load upcoming events');
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
}