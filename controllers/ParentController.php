<?php
/**
 * Parent Controller
 * Handles parent portal functionality
 */

class ParentController extends BaseController {

    /**
     * Display parent dashboard
     */
    public function dashboard() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            // Get parent data
            $parentId = $this->getUserId();
            $dashboardData = ParentModel::getDashboardData($parentId);

            // Prepare view data
            $data = [
                'title' => 'Parent Dashboard',
                'parent_data' => $dashboardData['parent'],
                'children' => $dashboardData['children'],
                'notifications' => $dashboardData['notifications'],
                'upcoming_events' => $dashboardData['upcoming_events'],
                'current_year' => date('Y')
            ];

            // Render dashboard view
            echo $this->view('parent.dashboard.index', $data);
        } catch (Exception $e) {
            // Handle errors gracefully
            error_log("Parent dashboard error: " . $e->getMessage());
            $this->flash('error', 'Unable to load dashboard data. Please try again.');
            $this->redirect('/login');
        }
    }

    /**
     * Display children overview page
     */
    public function children() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get children data with academic snapshots
            $children = ParentModel::getChildrenWithSnapshots($parentId);

            // Prepare view data
            $data = [
                'title' => 'My Children',
                'children' => $children,
                'current_year' => date('Y')
            ];

            // Render children view
            echo $this->view('parent.children.index', $data);
        } catch (Exception $e) {
            error_log("Parent children error: " . $e->getMessage());
            $this->flash('error', 'Unable to load children data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * Display attendance tracking page
     */
    public function attendance() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get children with attendance data
            $children = ParentModel::getChildrenAttendance($parentId);

            // Prepare view data
            $data = [
                'title' => 'Attendance Tracking',
                'children' => $children,
                'current_year' => date('Y')
            ];

            // Render attendance view
            echo $this->view('parent.attendance.index', $data);
        } catch (Exception $e) {
            error_log("Parent attendance error: " . $e->getMessage());
            $this->flash('error', 'Unable to load attendance data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * Display academic results page
     */
    public function results() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get children with exam results
            $children = ParentModel::getChildrenResults($parentId);

            // Prepare view data
            $data = [
                'title' => 'Academic Results',
                'children' => $children,
                'current_year' => date('Y')
            ];

            // Render results view
            echo $this->view('parent.results.index', $data);
        } catch (Exception $e) {
            error_log("Parent results error: " . $e->getMessage());
            $this->flash('error', 'Unable to load results data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * Display fee payments page
     */
    public function fees() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get children with fee status
            $children = ParentModel::getChildrenFees($parentId);

            // Prepare view data
            $data = [
                'title' => 'Fee Payments',
                'children' => $children,
                'current_year' => date('Y')
            ];

            // Render fees view
            echo $this->view('parent.fees.index', $data);
        } catch (Exception $e) {
            error_log("Parent fees error: " . $e->getMessage());
            $this->flash('error', 'Unable to load fee data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * Display school events page
     */
    public function events() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get upcoming events with registration status
            $events = ParentModel::getUpcomingEvents($parentId);

            // Get parent registrations
            $registrations = ParentModel::getParentEventRegistrations($parentId);

            // Prepare view data
            $data = [
                'title' => 'School Events',
                'events' => $events,
                'registrations' => $registrations,
                'current_year' => date('Y')
            ];

            // Render events view
            echo $this->view('parent.events.index', $data);
        } catch (Exception $e) {
            error_log("Parent events error: " . $e->getMessage());
            $this->flash('error', 'Unable to load events data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * Display profile management page
     */
    public function profile() {
        // Check if user is authenticated and is a parent
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();

            // Get parent profile data
            $parent = ParentModel::getParentProfile($parentId);

            // Get activity logs
            $activityLogs = ParentModel::getActivityLogs($parentId);

            // Prepare view data
            $data = [
                'title' => 'My Profile',
                'parent' => $parent,
                'activity_logs' => $activityLogs,
                'current_year' => date('Y')
            ];

            // Render profile view
            echo $this->view('parent.profile.index', $data);
        } catch (Exception $e) {
            error_log("Parent profile error: " . $e->getMessage());
            $this->flash('error', 'Unable to load profile data. Please try again.');
            $this->redirect('/parent/dashboard');
        }
    }

    /**
     * AJAX endpoint to get child details
     */
    public function getChildDetails() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            $childDetails = ParentModel::getChildDetails($parentId, $childId);
            $this->success($childDetails);
        } catch (Exception $e) {
            $this->error('Failed to load child details');
        }
    }

    /**
     * AJAX endpoint to get attendance data for a child
     */
    public function getChildAttendance() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            $attendanceData = ParentModel::getChildAttendance($parentId, $childId, $startDate, $endDate);
            $this->success($attendanceData);
        } catch (Exception $e) {
            $this->error('Failed to load attendance data');
        }
    }

    /**
     * AJAX endpoint to get monthly attendance summary for a child
     */
    public function getChildAttendanceSummary() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');
            $month = $this->input('month');
            $year = $this->input('year');

            $summary = ParentModel::getChildMonthlyAttendanceSummary($parentId, $childId, $month, $year);
            $this->success($summary);
        } catch (Exception $e) {
            $this->error('Failed to load attendance summary');
        }
    }

    /**
     * AJAX endpoint to get exam results for a child
     */
    public function getChildResults() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');
            $examId = $this->input('exam_id');

            $results = ParentModel::getChildResults($parentId, $childId, $examId);
            $this->success($results);
        } catch (Exception $e) {
            $this->error('Failed to load exam results');
        }
    }

    /**
     * AJAX endpoint to get fee status for a child
     */
    public function getChildFees() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            $feeData = ParentModel::getChildFees($parentId, $childId);
            $this->success($feeData);
        } catch (Exception $e) {
            $this->error('Failed to load fee data');
        }
    }

    /**
     * AJAX endpoint to get notifications
     */
    public function getNotifications() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $notifications = ParentModel::getNotifications($parentId);
            $this->success($notifications);
        } catch (Exception $e) {
            $this->error('Failed to load notifications');
        }
    }

    /**
     * AJAX endpoint to update profile
     */
    public function updateProfile() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();

            // Validate input data
            $data = $this->validateProfileData($this->all());

            // Update parent profile
            ParentModel::updateProfile($parentId, $data);

            $this->success(['message' => 'Profile updated successfully']);
        } catch (Exception $e) {
            $this->error('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to change password
     */
    public function changePassword() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $currentPassword = $this->input('current_password');
            $newPassword = $this->input('new_password');
            $confirmPassword = $this->input('confirm_password');

            // Validate password inputs
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->error('All password fields are required');
            }

            if ($newPassword !== $confirmPassword) {
                $this->error('New password and confirmation do not match');
            }

            if (strlen($newPassword) < 8) {
                $this->error('New password must be at least 8 characters long');
            }

            // Get user data
            $user = User::find($parentId);
            if (!$user) {
                $this->error('User not found');
            }

            // Verify current password
            if (!password_verify($currentPassword, $user->password_hash)) {
                $this->error('Current password is incorrect');
            }

            // Update password
            $user->password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $user->save();

            // Log activity
            ParentModel::logActivity($parentId, 'password_changed', 'Password was changed successfully');

            $this->success(['message' => 'Password changed successfully']);
        } catch (Exception $e) {
            $this->error('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Download child report card
     */
    public function download() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            // Get child details
            $childDetails = ParentModel::getChildDetails($parentId, $childId);

            if (!$childDetails) {
                $this->flash('error', 'Child not found or access denied.');
                $this->redirect('/parent/results');
            }

            // Generate PDF report
            $this->generateChildReportPDF($childDetails);
        } catch (Exception $e) {
            error_log("Parent download child report error: " . $e->getMessage());
            $this->flash('error', 'Unable to generate report. Please try again.');
            $this->redirect('/parent/results');
        }
    }

    /**
     * Export child report
     */
    public function exportChildReport() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            // Get child details
            $childDetails = ParentModel::getChildDetails($parentId, $childId);

            if (!$childDetails) {
                $this->flash('error', 'Child not found or access denied.');
                $this->redirect('/parent/children');
            }

            // Generate PDF report
            $this->generateChildReportPDF($childDetails);
        } catch (Exception $e) {
            error_log("Parent export child report error: " . $e->getMessage());
            $this->flash('error', 'Unable to generate report. Please try again.');
            $this->redirect('/parent/children');
        }
    }

    /**
     * Generate PDF report for child
     */
    private function generateChildReportPDF($childData) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Student Report - ' . $childData['first_name'] . ' ' . $childData['last_name']);
        $pdf->SetSubject('Student Academic Report');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // School header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'School Management System', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Comprehensive Student Academic Report', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Generated on: ' . date('F d, Y'), 0, 1, 'C');
        $pdf->Ln(10);

        // Student information
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Student Information', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Name:', 0, 0);
        $pdf->Cell(0, 6, $childData['first_name'] . ' ' . $childData['middle_name'] . ' ' . $childData['last_name'], 0, 1);

        $pdf->Cell(50, 6, 'Class:', 0, 0);
        $pdf->Cell(0, 6, $childData['class_name'] . ' - ' . $childData['class_section'], 0, 1);

        $pdf->Cell(50, 6, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 6, $childData['scholar_number'], 0, 1);

        $pdf->Cell(50, 6, 'Admission Number:', 0, 0);
        $pdf->Cell(0, 6, $childData['admission_number'], 0, 1);

        $pdf->Ln(5);

        // Academic performance with rank
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Academic Performance & Rankings', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Average Score:', 0, 0);
        $pdf->Cell(0, 6, $childData['exam_results']['average_percentage'] . '%', 0, 1);

        $pdf->Cell(50, 6, 'Highest Score:', 0, 0);
        $pdf->Cell(0, 6, $childData['exam_results']['highest_percentage'] . '%', 0, 1);

        $pdf->Cell(50, 6, 'Lowest Score:', 0, 0);
        $pdf->Cell(0, 6, $childData['exam_results']['lowest_percentage'] . '%', 0, 1);

        $pdf->Cell(50, 6, 'Class Rank:', 0, 0);
        $rankDisplay = $childData['exam_results']['class_rank'] > 0 ?
            $childData['exam_results']['class_rank'] . ' out of ' . $childData['exam_results']['total_students'] :
            'Not available';
        $pdf->Cell(0, 6, $rankDisplay, 0, 1);

        $pdf->Cell(50, 6, 'Total Exams:', 0, 0);
        $pdf->Cell(0, 6, $childData['exam_results']['total_exams'], 0, 1);

        // Grade distribution
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Grade Distribution:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $grades = $childData['exam_results']['grade_distribution'];
        $pdf->Cell(30, 6, 'Grade A:', 0, 0);
        $pdf->Cell(20, 6, $grades['A'], 0, 0);
        $pdf->Cell(30, 6, 'Grade B:', 0, 0);
        $pdf->Cell(20, 6, $grades['B'], 0, 0);
        $pdf->Cell(30, 6, 'Grade C:', 0, 0);
        $pdf->Cell(20, 6, $grades['C'], 0, 1);

        $pdf->Ln(5);

        // Attendance
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Attendance Record', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Attendance Rate:', 0, 0);
        $pdf->Cell(0, 6, $childData['attendance']['percentage'] . '%', 0, 1);

        $pdf->Cell(50, 6, 'Present Days:', 0, 0);
        $pdf->Cell(0, 6, $childData['attendance']['present_days'] . '/' . $childData['attendance']['total_days'], 0, 1);

        $pdf->Cell(50, 6, 'Absent Days:', 0, 0);
        $pdf->Cell(0, 6, $childData['attendance']['absent_days'], 0, 1);

        $pdf->Cell(50, 6, 'Late Days:', 0, 0);
        $pdf->Cell(0, 6, $childData['attendance']['late_days'], 0, 1);

        $pdf->Ln(5);

        // Fee status
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Fee Status', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Total Fees:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['total']), 0, 1);

        $pdf->Cell(50, 6, 'Paid:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['paid']), 0, 1);

        $pdf->Cell(50, 6, 'Pending:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['pending']), 0, 1);

        $pdf->Cell(50, 6, 'Overdue:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['overdue']), 0, 1);

        $pdf->Ln(10);

        // Performance Analytics Summary
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Performance Analytics', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->MultiCell(0, 6, 'This report provides a comprehensive overview of the student\'s academic performance, attendance record, and financial status. The class rank is calculated based on average percentage across all subjects and exams in the current academic year.', 0, 'L');

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 6, 'Report generated by School Management System - ' . date('Y-m-d H:i:s'), 0, 1, 'C');

        // Output the PDF
        $filename = 'student_report_' . $childData['scholar_number'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Export detailed results
     */
    public function export() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');
            $examId = $this->input('exam_id');

            // Get child results
            $results = ParentModel::getChildResults($parentId, $childId, $examId);

            if (empty($results)) {
                $this->flash('error', 'No results found to export.');
                $this->redirect('/parent/results');
            }

            // Generate CSV export
            $this->generateResultsCSV($results, $childId, $examId);
        } catch (Exception $e) {
            error_log("Parent export results error: " . $e->getMessage());
            $this->flash('error', 'Unable to export results. Please try again.');
            $this->redirect('/parent/results');
        }
    }

    /**
     * Generate CSV export for results
     */
    private function generateResultsCSV($results, $childId, $examId = null) {
        // Get child info for filename
        $instance = new static();
        $child = $instance->db->fetch(
            "SELECT CONCAT(first_name, ' ', last_name) as full_name, scholar_number FROM students WHERE id = ?",
            [$childId]
        );

        $filename = 'exam_results_' . $child['scholar_number'] . '_' . date('Y-m-d') . '.csv';

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Exam Name', 'Subject', 'Exam Date', 'Marks Obtained', 'Max Marks', 'Percentage', 'Grade', 'Teacher']);

        // CSV data
        foreach ($results as $result) {
            fputcsv($output, [
                $result['exam_name'] ?? '',
                $result['subject_name'] ?? '',
                $result['exam_date'] ? date('Y-m-d', strtotime($result['exam_date'])) : '',
                $result['marks_obtained'] ?? 0,
                $result['max_marks'] ?? 0,
                $result['percentage'] ?? 0,
                $result['grade'] ?? '',
                $result['teacher_name'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Download fee statement for a child
     */
    public function downloadFeeStatement() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            // Get child details
            $childDetails = ParentModel::getChildDetails($parentId, $childId);

            if (!$childDetails) {
                $this->flash('error', 'Child not found or access denied.');
                $this->redirect('/parent/fees');
            }

            // Generate PDF fee statement
            $this->generateFeeStatementPDF($childDetails);
        } catch (Exception $e) {
            error_log("Parent download fee statement error: " . $e->getMessage());
            $this->flash('error', 'Unable to generate fee statement. Please try again.');
            $this->redirect('/parent/fees');
        }
    }

    /**
     * Export fee details for a child
     */
    public function exportFeeDetails() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $childId = $this->input('child_id');

            // Get child fee data
            $feeData = ParentModel::getChildFees($parentId, $childId);

            if (empty($feeData)) {
                $this->flash('error', 'No fee data found to export.');
                $this->redirect('/parent/fees');
            }

            // Generate CSV export
            $this->generateFeeDetailsCSV($feeData, $childId);
        } catch (Exception $e) {
            error_log("Parent export fee details error: " . $e->getMessage());
            $this->flash('error', 'Unable to export fee details. Please try again.');
            $this->redirect('/parent/fees');
        }
    }

    /**
     * View receipt for a payment
     */
    public function viewReceipt() {
        if (!$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->redirect('/login');
        }

        try {
            $parentId = $this->getUserId();
            $paymentId = $this->input('payment_id');

            // Get payment details with verification
            $payment = $this->getPaymentDetails($parentId, $paymentId);

            if (!$payment) {
                $this->flash('error', 'Receipt not found or access denied.');
                $this->redirect('/parent/fees');
            }

            // Generate PDF receipt
            $this->generateReceiptPDF($payment);
        } catch (Exception $e) {
            error_log("Parent view receipt error: " . $e->getMessage());
            $this->flash('error', 'Unable to view receipt. Please try again.');
            $this->redirect('/parent/fees');
        }
    }

    /**
     * Generate PDF fee statement for child
     */
    private function generateFeeStatementPDF($childData) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Fee Statement - ' . $childData['first_name'] . ' ' . $childData['last_name']);
        $pdf->SetSubject('Fee Payment Statement');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // School header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'School Management System', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Fee Payment Statement', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Generated on: ' . date('F d, Y'), 0, 1, 'C');
        $pdf->Ln(10);

        // Student information
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Student Information', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Name:', 0, 0);
        $pdf->Cell(0, 6, $childData['first_name'] . ' ' . $childData['middle_name'] . ' ' . $childData['last_name'], 0, 1);

        $pdf->Cell(50, 6, 'Class:', 0, 0);
        $pdf->Cell(0, 6, $childData['class_name'] . ' - ' . $childData['class_section'], 0, 1);

        $pdf->Cell(50, 6, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 6, $childData['scholar_number'], 0, 1);

        $pdf->Cell(50, 6, 'Admission Number:', 0, 0);
        $pdf->Cell(0, 6, $childData['admission_number'], 0, 1);

        $pdf->Ln(5);

        // Fee summary
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Fee Summary', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 6, 'Total Fees:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['total']), 0, 1);

        $pdf->Cell(50, 6, 'Paid:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['paid']), 0, 1);

        $pdf->Cell(50, 6, 'Pending:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['pending']), 0, 1);

        $pdf->Cell(50, 6, 'Overdue:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($childData['fee_status']['overdue']), 0, 1);

        $pdf->Ln(10);

        // Fee details table
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Fee Details', 0, 1, 'L');

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(40, 8, 'Fee Type', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Academic Year', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Paid', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Remaining', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Due Date', 1, 1, 'C', true);

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        if (!empty($childData['fee_details'])) {
            foreach ($childData['fee_details'] as $fee) {
                $remaining = $fee['amount'] - ($fee['paid_amount'] ?? 0);
                $pdf->Cell(40, 6, $fee['fee_type'] ?? '', 1, 0, 'L');
                $pdf->Cell(30, 6, $fee['academic_year'] ?? '', 1, 0, 'C');
                $pdf->Cell(30, 6, '₹' . number_format($fee['amount']), 1, 0, 'R');
                $pdf->Cell(30, 6, '₹' . number_format($fee['paid_amount'] ?? 0), 1, 0, 'R');
                $pdf->Cell(30, 6, '₹' . number_format($remaining), 1, 0, 'R');
                $pdf->Cell(30, 6, $fee['due_date'] ? date('d/m/Y', strtotime($fee['due_date'])) : '', 1, 1, 'C');
            }
        } else {
            $pdf->Cell(190, 6, 'No fee records found', 1, 1, 'C');
        }

        $pdf->Ln(10);

        // Payment history
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Payment History', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(30, 8, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Fee Type', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Receipt Number', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Remarks', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        if (!empty($childData['payment_history'])) {
            foreach ($childData['payment_history'] as $payment) {
                $pdf->Cell(30, 6, date('d/m/Y', strtotime($payment['payment_date'])), 1, 0, 'C');
                $pdf->Cell(40, 6, $payment['fee_type'] ?? '', 1, 0, 'L');
                $pdf->Cell(30, 6, '₹' . number_format($payment['amount_paid']), 1, 0, 'R');
                $pdf->Cell(40, 6, $payment['receipt_number'] ?? '', 1, 0, 'C');
                $pdf->Cell(50, 6, $payment['remarks'] ?? '', 1, 1, 'L');
            }
        } else {
            $pdf->Cell(190, 6, 'No payment history found', 1, 1, 'C');
        }

        $pdf->Ln(15);

        // Footer
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 6, 'This is a computer generated statement. No signature required.', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Generated by School Management System - ' . date('Y-m-d H:i:s'), 0, 1, 'C');

        // Output the PDF
        $filename = 'fee_statement_' . $childData['scholar_number'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Generate CSV export for fee details
     */
    private function generateFeeDetailsCSV($feeData, $childId) {
        // Get child info for filename
        $instance = new static();
        $child = $instance->db->fetch(
            "SELECT CONCAT(first_name, ' ', last_name) as full_name, scholar_number FROM students WHERE id = ?",
            [$childId]
        );

        $filename = 'fee_details_' . $child['scholar_number'] . '_' . date('Y-m-d') . '.csv';

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Fee Type', 'Academic Year', 'Total Amount', 'Paid Amount', 'Remaining', 'Due Date', 'Status', 'Payment Date', 'Receipt Number']);

        // CSV data
        foreach ($feeData as $fee) {
            $remaining = $fee['amount'] - ($fee['paid_amount'] ?? 0);
            $status = $remaining > 0 ? 'Pending' : 'Paid';

            fputcsv($output, [
                $fee['fee_type'] ?? '',
                $fee['academic_year'] ?? '',
                $fee['amount'] ?? 0,
                $fee['paid_amount'] ?? 0,
                $remaining,
                $fee['due_date'] ? date('Y-m-d', strtotime($fee['due_date'])) : '',
                $status,
                $fee['payment_date'] ? date('Y-m-d', strtotime($fee['payment_date'])) : '',
                $fee['receipt_number'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get payment details for receipt generation
     */
    private function getPaymentDetails($parentId, $paymentId) {
        $instance = new static();

        // Get payment with child verification
        $payment = $instance->db->fetch(
            "SELECT fp.*, f.fee_type, f.academic_year, f.student_id,
                    s.first_name, s.middle_name, s.last_name, s.scholar_number, s.admission_number,
                    c.class_name, c.section as class_section
             FROM fee_payments fp
             LEFT JOIN fees f ON fp.fee_id = f.id
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE fp.id = ? AND s.parent_id = ? AND fp.is_paid = 1",
            [$paymentId, $parentId]
        );

        return $payment ?: null;
    }

    /**
     * Generate PDF receipt for payment
     */
    private function generateReceiptPDF($payment) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Payment Receipt - ' . $payment['receipt_number']);
        $pdf->SetSubject('Fee Payment Receipt');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // School header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'School Management System', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'FEE PAYMENT RECEIPT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Receipt Number: ' . $payment['receipt_number'], 0, 1, 'C');
        $pdf->Cell(0, 6, 'Date: ' . date('F d, Y'), 0, 1, 'C');
        $pdf->Ln(10);

        // Receipt border
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(10, 40, 190, 120);

        // Student information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Student Details', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Name:', 0, 0);
        $pdf->Cell(0, 6, $payment['first_name'] . ' ' . $payment['middle_name'] . ' ' . $payment['last_name'], 0, 1);

        $pdf->Cell(50, 6, 'Class:', 0, 0);
        $pdf->Cell(0, 6, $payment['class_name'] . ' - ' . $payment['class_section'], 0, 1);

        $pdf->Cell(50, 6, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 6, $payment['scholar_number'], 0, 1);

        $pdf->Cell(50, 6, 'Admission Number:', 0, 0);
        $pdf->Cell(0, 6, $payment['admission_number'], 0, 1);

        $pdf->Ln(5);

        // Payment details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Payment Details', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Fee Type:', 0, 0);
        $pdf->Cell(0, 6, $payment['fee_type'], 0, 1);

        $pdf->Cell(50, 6, 'Academic Year:', 0, 0);
        $pdf->Cell(0, 6, $payment['academic_year'], 0, 1);

        $pdf->Cell(50, 6, 'Payment Date:', 0, 0);
        $pdf->Cell(0, 6, date('d/m/Y', strtotime($payment['payment_date'])), 0, 1);

        $pdf->Cell(50, 6, 'Amount Paid:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, '₹' . number_format($payment['amount_paid']), 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Payment Method:', 0, 0);
        $pdf->Cell(0, 6, ucfirst($payment['payment_method'] ?? 'Cash'), 0, 1);

        if (!empty($payment['transaction_id'])) {
            $pdf->Cell(50, 6, 'Transaction ID:', 0, 0);
            $pdf->Cell(0, 6, $payment['transaction_id'], 0, 1);
        }

        if (!empty($payment['remarks'])) {
            $pdf->Cell(50, 6, 'Remarks:', 0, 0);
            $pdf->Cell(0, 6, $payment['remarks'], 0, 1);
        }

        $pdf->Ln(10);

        // Amount in words
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Amount in Words: ' . $this->numberToWords($payment['amount_paid']) . ' Rupees Only', 0, 1, 'L');

        $pdf->Ln(15);

        // Signatures
        $pdf->SetFont('helvetica', '', 10);

        // Left side - Received by
        $pdf->Cell(95, 6, 'Received By:', 0, 0, 'L');
        $pdf->Cell(95, 6, 'Authorized Signature:', 0, 1, 'R');

        $pdf->Ln(15);
        $pdf->Cell(95, 6, '_______________________', 0, 0, 'L');
        $pdf->Cell(95, 6, '_______________________', 0, 1, 'R');

        $pdf->Cell(95, 6, 'Cashier/Administrator', 0, 0, 'L');
        $pdf->Cell(95, 6, 'Principal/Director', 0, 1, 'R');

        $pdf->Ln(10);

        // Footer note
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 6, 'This is a computer generated receipt. No signature required for validity.', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Generated by School Management System - ' . date('Y-m-d H:i:s'), 0, 1, 'C');

        // Output the PDF
        $filename = 'receipt_' . $payment['receipt_number'] . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Convert number to words
     */
    private function numberToWords($number) {
        $words = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];

        if ($number < 21) {
            return $words[$number];
        } elseif ($number < 100) {
            return $words[10 * floor($number/10)] . ($number % 10 ? ' ' . $words[$number % 10] : '');
        } elseif ($number < 1000) {
            return $words[floor($number/100)] . ' Hundred' . ($number % 100 ? ' ' . $this->numberToWords($number % 100) : '');
        } elseif ($number < 100000) {
            return $this->numberToWords(floor($number/1000)) . ' Thousand' . ($number % 1000 ? ' ' . $this->numberToWords($number % 1000) : '');
        } elseif ($number < 10000000) {
            return $this->numberToWords(floor($number/100000)) . ' Lakh' . ($number % 100000 ? ' ' . $this->numberToWords($number % 100000) : '');
        }

        return (string)$number; // Fallback for very large numbers
    }

    /**
     * AJAX endpoint to register for an event
     */
    public function registerForEvent() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $eventId = $this->input('event_id');
            $studentId = $this->input('student_id'); // Optional
            $notes = $this->input('notes');

            if (empty($eventId)) {
                $this->error('Event ID is required');
            }

            $result = EventsModel::registerForEvent($eventId, $parentId, $studentId, $notes);

            if ($result['success']) {
                $this->success($result);
            } else {
                $this->error($result['message']);
            }
        } catch (Exception $e) {
            $this->error('Failed to register for event: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to cancel event registration
     */
    public function cancelEventRegistration() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $eventId = $this->input('event_id');
            $studentId = $this->input('student_id'); // Optional

            if (empty($eventId)) {
                $this->error('Event ID is required');
            }

            $result = EventsModel::cancelRegistration($eventId, $parentId, $studentId);

            if ($result['success']) {
                $this->success($result);
            } else {
                $this->error($result['message']);
            }
        } catch (Exception $e) {
            $this->error('Failed to cancel registration: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to get event details
     */
    public function getEventDetails() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $eventId = $this->input('event_id');

            if (empty($eventId)) {
                $this->error('Event ID is required');
            }

            $event = EventsModel::getEventById($eventId);

            if (!$event) {
                $this->error('Event not found');
            }

            // Add registration status
            $event['is_registered'] = EventsModel::isParentRegistered($eventId, $parentId);
            $event['registration_count'] = EventsModel::getRegistrationCount($eventId);

            $this->success($event);
        } catch (Exception $e) {
            $this->error('Failed to load event details: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to get events by month for calendar
     */
    public function getEventsByMonth() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();
            $year = $this->input('year');
            $month = $this->input('month');

            if (empty($year) || empty($month)) {
                $this->error('Year and month are required');
            }

            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $events = EventsModel::getEventsForCalendar($startDate, $endDate);

            // Add registration status for each event
            foreach ($events as &$event) {
                $event['is_registered'] = EventsModel::isParentRegistered($event['id'], $parentId);
            }

            $this->success($events);
        } catch (Exception $e) {
            $this->error('Failed to load events: ' . $e->getMessage());
        }
    }

    /**
     * Validate profile update data
     */
    private function validateProfileData($data) {
        $validated = [];

        // Personal information
        $allowedFields = [
            'first_name', 'middle_name', 'last_name', 'email', 'mobile', 'temporary_address'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $validated[$field] = trim($data[$field]);
            }
        }

        // Validate email if provided
        if (!empty($validated['email']) && !filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Validate mobile if provided
        if (!empty($validated['mobile']) && !preg_match('/^[0-9+\-\s()]+$/', $validated['mobile'])) {
            throw new Exception('Invalid mobile number format');
        }

        return $validated;
    }

    /**
     * AJAX endpoint to upload profile photo
     */
    public function uploadPhoto() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('parent')) {
            $this->error('Unauthorized access');
        }

        try {
            $parentId = $this->getUserId();

            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $this->error('No photo file uploaded or upload error');
            }

            $file = $_FILES['photo'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                $this->error('Invalid file type. Only JPEG, PNG, and GIF are allowed');
            }

            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $this->error('File size too large. Maximum size is 5MB');
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'parent_' . $parentId . '_' . time() . '.' . $extension;

            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/parents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->error('Failed to save uploaded file');
            }

            // Update parent record with photo path
            ParentModel::updateProfile($parentId, ['photo_path' => $filepath]);

            // Log activity
            ParentModel::logActivity($parentId, 'photo_updated', 'Profile photo was updated');

            $this->success([
                'message' => 'Photo uploaded successfully',
                'photo_path' => $filepath
            ]);
        } catch (Exception $e) {
            $this->error('Failed to upload photo: ' . $e->getMessage());
        }
    }
}