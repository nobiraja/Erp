<?php
/**
 * Cashier Controller
 * Handles cashier portal functionality with role-based access control
 */

class CashierController extends BaseController {

    /**
     * Constructor - Apply cashier middleware
     */
    public function __construct() {
        parent::__construct();
        $this->middleware(RoleCheckMiddleware::cashier());
    }

    /**
     * Display cashier dashboard
     */
    public function dashboard() {
        try {
            // Get dashboard data
            $dashboardData = CashierDashboardModel::getDashboardSummary();

            // Get school settings
            $schoolName = $this->getSetting('school_name', 'School Management System');
            $schoolLogo = $this->getSetting('school_logo', '/images/logo.png');

            // Prepare view data
            $data = [
                'title' => 'Cashier Dashboard - ' . $schoolName,
                'school_name' => $schoolName,
                'school_logo' => $schoolLogo,
                'dashboard_data' => $dashboardData,
                'current_user' => $this->getUserData(),
                'current_year' => date('Y'),
                'current_month' => date('F Y')
            ];

            // Render dashboard view
            echo $this->view('cashier.dashboard.index', $data);

        } catch (Exception $e) {
            // Log error and show error page
            error_log("Cashier Dashboard error: " . $e->getMessage());
            $this->error('Failed to load dashboard data', [], 500);
        }
    }

    /**
     * Display fee collection page
     */
    public function fees() {
        try {
            $data = [
                'title' => 'Fee Collection',
                'current_user' => $this->getUserData()
            ];

            echo $this->view('cashier.fees.index', $data);

        } catch (Exception $e) {
            error_log("Fees page error: " . $e->getMessage());
            $this->error('Failed to load fees page', [], 500);
        }
    }

    /**
     * Display outstanding fees page
     */
    public function outstanding() {
        try {
            $data = [
                'title' => 'Outstanding Fees',
                'current_user' => $this->getUserData()
            ];

            echo $this->view('cashier.outstanding.index', $data);

        } catch (Exception $e) {
            error_log("Outstanding fees page error: " . $e->getMessage());
            $this->error('Failed to load outstanding fees page', [], 500);
        }
    }

    /**
     * Display financial reports page
     */
    public function reports() {
        try {
            $data = [
                'title' => 'Financial Reports',
                'current_user' => $this->getUserData()
            ];

            echo $this->view('cashier.reports.index', $data);

        } catch (Exception $e) {
            error_log("Reports page error: " . $e->getMessage());
            $this->error('Failed to load reports page', [], 500);
        }
    }

    /**
     * Display expenses management page
     */
    public function expenses() {
        try {
            $data = [
                'title' => 'Expense Management',
                'current_user' => $this->getUserData()
            ];

            echo $this->view('cashier.expenses.index', $data);

        } catch (Exception $e) {
            error_log("Expenses page error: " . $e->getMessage());
            $this->error('Failed to load expenses page', [], 500);
        }
    }

    /**
     * AJAX endpoint to get dashboard statistics
     */
    public function getDashboardStats() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $stats = CashierDashboardModel::getDashboardSummary();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('Failed to load dashboard statistics');
        }
    }

    /**
     * AJAX endpoint to get fee collection trends
     */
    public function getFeeCollectionTrends() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $data = CashierDashboardModel::getFeeCollectionTrends();
            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load fee collection trends');
        }
    }

    /**
     * AJAX endpoint to get overdue payments
     */
    public function getOverduePayments() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $data = CashierDashboardModel::getOverduePayments();
            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load overdue payments');
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