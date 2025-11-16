<?php
/**
 * Student Controller
 * Handles student portal functionality
 */

class StudentController extends BaseController {

    /**
     * Display student dashboard
     */
    public function dashboard() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            // Get student data
            $studentId = $this->getUserId();
            $dashboardData = StudentDashboardModel::getDashboardData($studentId);

            // Prepare view data
            $data = [
                'title' => 'Student Dashboard',
                'student_data' => $dashboardData['student'],
                'attendance_percentage' => $dashboardData['attendance_percentage'],
                'exam_results' => $dashboardData['exam_results'],
                'upcoming_events' => $dashboardData['upcoming_events'],
                'fee_status' => $dashboardData['fee_status'],
                'notifications' => $dashboardData['notifications'],
                'current_year' => date('Y')
            ];

            // Render dashboard view
            echo $this->view('student.dashboard.index', $data);
        } catch (Exception $e) {
            // Handle errors gracefully
            error_log("Student dashboard error: " . $e->getMessage());
            $this->flash('error', 'Unable to load dashboard data. Please try again.');
            $this->redirect('/login');
        }
    }

    /**
     * AJAX endpoint to get attendance data
     */
    public function getAttendance() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $attendanceData = StudentDashboardModel::getAttendanceData($studentId);
            $this->success($attendanceData);
        } catch (Exception $e) {
            $this->error('Failed to load attendance data');
        }
    }

    /**
     * AJAX endpoint to get exam results
     */
    public function getExamResults() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $examResults = StudentDashboardModel::getExamResults($studentId);
            $this->success($examResults);
        } catch (Exception $e) {
            $this->error('Failed to load exam results');
        }
    }

    /**
     * AJAX endpoint to get fee status
     */
    public function getFeeStatus() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $feeStatus = StudentDashboardModel::getFeeStatus($studentId);
            $this->success($feeStatus);
        } catch (Exception $e) {
            $this->error('Failed to load fee status');
        }
    }

    /**
     * AJAX endpoint to get notifications
     */
    public function getNotifications() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $notifications = StudentDashboardModel::getNotifications($studentId);
            $this->success($notifications);
        } catch (Exception $e) {
            $this->error('Failed to load notifications');
        }
    }

    /**
     * AJAX endpoint to get upcoming events
     */
    public function getUpcomingEvents() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $upcomingEvents = StudentDashboardModel::getUpcomingEvents();
            $this->success($upcomingEvents);
        } catch (Exception $e) {
            $this->error('Failed to load upcoming events');
        }
    }

    /**
     * Display student attendance page
     */
    public function attendance() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();
            $studentData = StudentDashboardModel::getStudentInfo($studentId);

            // Get current month attendance data
            $currentMonth = date('m');
            $currentYear = date('Y');
            $attendanceData = Attendance::getStudentAttendance($studentId, null, null);

            // Calculate attendance statistics
            $stats = $this->calculateAttendanceStats($attendanceData, $currentMonth, $currentYear);

            // Get monthly trends for the last 12 months
            $monthlyTrends = Attendance::getMonthlyTrends(null, date('Y'));

            // Prepare view data
            $data = [
                'title' => 'My Attendance',
                'student_data' => $studentData,
                'attendance_data' => $attendanceData,
                'attendance_stats' => $stats,
                'monthly_trends' => $monthlyTrends,
                'current_month' => $currentMonth,
                'current_year' => $currentYear
            ];

            // Render attendance view
            echo $this->view('student.attendance.index', $data);
        } catch (Exception $e) {
            error_log("Student attendance error: " . $e->getMessage());
            $this->flash('error', 'Unable to load attendance data. Please try again.');
            $this->redirect('/student/dashboard');
        }
    }

    /**
     * AJAX endpoint to get attendance data for a specific date range
     */
    public function getAttendanceHistory() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $startDate = $this->getPost('start_date');
            $endDate = $this->getPost('end_date');

            $attendanceData = Attendance::getStudentAttendance($studentId, $startDate, $endDate);
            $stats = $this->calculateAttendanceStats($attendanceData);

            $this->success([
                'attendance' => $attendanceData,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load attendance history');
        }
    }

    /**
     * AJAX endpoint to get monthly attendance calendar data
     */
    public function getMonthlyCalendar() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $month = $this->getPost('month', date('m'));
            $year = $this->getPost('year', date('Y'));

            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $attendanceData = Attendance::getStudentAttendance($studentId, $startDate, $endDate);

            // Create calendar data
            $calendarData = $this->buildCalendarData($month, $year, $attendanceData);

            $this->success([
                'calendar' => $calendarData,
                'month' => $month,
                'year' => $year
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load calendar data');
        }
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats($attendanceData, $month = null, $year = null) {
        $total = count($attendanceData);
        $present = 0;
        $absent = 0;
        $late = 0;

        foreach ($attendanceData as $record) {
            switch ($record['status']) {
                case 'present':
                    $present++;
                    break;
                case 'absent':
                    $absent++;
                    break;
                case 'late':
                    $late++;
                    break;
            }
        }

        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        return [
            'total_days' => $total,
            'present_days' => $present,
            'absent_days' => $absent,
            'late_days' => $late,
            'attendance_percentage' => $percentage,
            'month' => $month,
            'year' => $year
        ];
    }

    /**
     * Build calendar data for monthly view
     */
    private function buildCalendarData($month, $year, $attendanceData) {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $startingDayOfWeek = date('w', $firstDay);

        // Create attendance lookup
        $attendanceLookup = [];
        foreach ($attendanceData as $record) {
            $day = date('j', strtotime($record['attendance_date']));
            $attendanceLookup[$day] = $record['status'];
        }

        $calendar = [];
        $dayCounter = 1;

        // Create 6 weeks (maximum needed)
        for ($week = 0; $week < 6; $week++) {
            $weekData = [];
            for ($day = 0; $day < 7; $day++) {
                if (($week == 0 && $day < $startingDayOfWeek) || $dayCounter > $daysInMonth) {
                    $weekData[] = null; // Empty cell
                } else {
                    $status = isset($attendanceLookup[$dayCounter]) ? $attendanceLookup[$dayCounter] : 'no_record';
                    $weekData[] = [
                        'day' => $dayCounter,
                        'status' => $status,
                        'date' => sprintf('%04d-%02d-%02d', $year, $month, $dayCounter)
                    ];
                    $dayCounter++;
                }
            }
            $calendar[] = $weekData;

            if ($dayCounter > $daysInMonth) {
                break; // Stop if we've filled all days
            }
        }

        return $calendar;
    }

    /**
     * Display student results page
     */
    public function results() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();

            // Get student info
            $studentData = StudentDashboardModel::getStudentInfo($studentId);

            // Get exam schedules
            $examSchedules = StudentResultsModel::getExamSchedules($studentId);

            // Get exam results
            $examResults = StudentResultsModel::getExamResults($studentId);

            // Get results summary
            $resultsSummary = StudentResultsModel::getResultsSummary($studentId);

            // Get student rank
            $studentRank = StudentResultsModel::getStudentRank($studentId);

            // Get performance analytics
            $performanceAnalytics = StudentResultsModel::getPerformanceAnalytics($studentId);

            // Get upcoming exams
            $upcomingExams = StudentResultsModel::getUpcomingExams($studentId);

            // Prepare view data
            $data = [
                'title' => 'My Exam Results',
                'student_data' => $studentData,
                'exam_schedules' => $examSchedules,
                'exam_results' => $examResults,
                'results_summary' => $resultsSummary,
                'student_rank' => $studentRank,
                'performance_analytics' => $performanceAnalytics,
                'upcoming_exams' => $upcomingExams,
                'current_year' => date('Y')
            ];

            // Render results view
            echo $this->view('student.results.index', $data);
        } catch (Exception $e) {
            error_log("Student results error: " . $e->getMessage());
            $this->flash('error', 'Unable to load results data. Please try again.');
            $this->redirect('/student/dashboard');
        }
    }

    /**
     * AJAX endpoint to get detailed exam results
     */
    public function getDetailedResults() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $examId = $this->getPost('exam_id');

            $results = StudentResultsModel::getExamResults($studentId, $examId);
            $summary = StudentResultsModel::getResultsSummary($studentId);

            $this->success([
                'results' => $results,
                'summary' => $summary
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load detailed results');
        }
    }

    /**
     * AJAX endpoint to get performance analytics
     */
    public function getPerformanceData() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $analytics = StudentResultsModel::getPerformanceAnalytics($studentId);

            $this->success($analytics);
        } catch (Exception $e) {
            $this->error('Failed to load performance data');
        }
    }

    /**
     * AJAX endpoint to get exam schedules
     */
    public function getExamSchedules() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $schedules = StudentResultsModel::getExamSchedules($studentId);

            $this->success($schedules);
        } catch (Exception $e) {
            $this->error('Failed to load exam schedules');
        }
    }

    /**
     * Generate and download PDF report card
     */
    public function downloadReportCard() {
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();
            $examId = $this->getGet('exam_id');

            $reportData = StudentResultsModel::getReportCardData($studentId, $examId);

            if (!$reportData) {
                $this->flash('error', 'Unable to generate report card. Student data not found.');
                $this->redirect('/student/results');
            }

            // Generate PDF using TCPDF
            $this->generatePDFReportCard($reportData);

        } catch (Exception $e) {
            error_log("Report card generation error: " . $e->getMessage());
            $this->flash('error', 'Unable to generate report card. Please try again.');
            $this->redirect('/student/results');
        }
    }

    /**
     * Display student fees page
     */
    public function fees() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();

            // Get student info
            $studentData = StudentDashboardModel::getStudentInfo($studentId);

            // Get fee status summary
            $feeStatus = StudentDashboardModel::getFeeStatus($studentId);

            // Get all fees for the student
            $allFees = FeeModel::getByStudent($studentId);

            // Get payment history
            $paymentHistory = FeePaymentModel::getByStudent($studentId);

            // Get pending dues with deadlines
            $pendingDues = $this->getPendingDues($studentId);

            // Prepare view data
            $data = [
                'title' => 'My Fees',
                'student_data' => $studentData,
                'fee_status' => $feeStatus,
                'all_fees' => $allFees,
                'payment_history' => $paymentHistory,
                'pending_dues' => $pendingDues,
                'current_year' => date('Y')
            ];

            // Render fees view
            echo $this->view('student.fees.index', $data);
        } catch (Exception $e) {
            error_log("Student fees error: " . $e->getMessage());
            $this->flash('error', 'Unable to load fee data. Please try again.');
            $this->redirect('/student/dashboard');
        }
    }

    /**
     * Get pending dues with deadlines
     */
    private function getPendingDues($studentId) {
        $allFees = FeeModel::getByStudent($studentId);
        $pendingDues = [];

        foreach ($allFees as $fee) {
            if (!$fee->is_paid) {
                $totalPaid = FeePaymentModel::getTotalPaidForFee($fee->id);
                $remaining = $fee->amount - $totalPaid;

                if ($remaining > 0) {
                    $pendingDues[] = [
                        'fee' => $fee,
                        'remaining_amount' => $remaining,
                        'is_overdue' => $fee->isOverdue(),
                        'days_overdue' => $fee->getDaysOverdue()
                    ];
                }
            }
        }

        // Sort by due date (earliest first)
        usort($pendingDues, function($a, $b) {
            return strtotime($a['fee']->due_date) - strtotime($b['fee']->due_date);
        });

        return $pendingDues;
    }

    /**
     * AJAX endpoint to get detailed fee information
     */
    public function getFeeDetails() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $feeId = $this->getPost('fee_id');

            $fee = FeeModel::withStudent($feeId);
            if (!$fee || $fee->student_id != $studentId) {
                $this->error('Fee not found');
            }

            $payments = FeePaymentModel::getByFee($feeId);
            $totalPaid = FeePaymentModel::getTotalPaidForFee($feeId);

            $this->success([
                'fee' => $fee,
                'payments' => $payments,
                'total_paid' => $totalPaid,
                'remaining' => $fee->amount - $totalPaid
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load fee details');
        }
    }

    /**
     * AJAX endpoint to get payment history
     */
    public function getPaymentHistory() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $limit = $this->getPost('limit', 10);

            $paymentHistory = FeePaymentModel::getByStudent($studentId, $limit);

            $this->success([
                'payments' => $paymentHistory
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load payment history');
        }
    }

    /**
     * AJAX endpoint to view payment receipt
     */
    public function viewReceipt() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $paymentId = $this->getPost('payment_id');

            $payment = FeePaymentModel::withDetails($paymentId);
            if (!$payment) {
                $this->error('Payment not found');
            }

            // Verify the payment belongs to the student
            $fee = FeeModel::find($payment->fee_id);
            if (!$fee || $fee->student_id != $studentId) {
                $this->error('Unauthorized access to receipt');
            }

            $this->success([
                'payment' => $payment,
                'fee' => $fee
            ]);
        } catch (Exception $e) {
            $this->error('Failed to load receipt');
        }
    }

    /**
     * Generate and download fee receipt PDF
     */
    public function downloadReceipt() {
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();
            $paymentId = $this->getGet('payment_id');

            $payment = FeePaymentModel::withDetails($paymentId);
            if (!$payment) {
                $this->flash('error', 'Payment not found');
                $this->redirect('/student/fees');
            }

            // Verify the payment belongs to the student
            $fee = FeeModel::find($payment->fee_id);
            if (!$fee || $fee->student_id != $studentId) {
                $this->flash('error', 'Unauthorized access');
                $this->redirect('/student/fees');
            }

            $receiptData = [
                'payment' => $payment,
                'fee' => $fee,
                'student' => StudentDashboardModel::getStudentInfo($studentId)
            ];

            $this->generateFeeReceiptPDF($receiptData);

        } catch (Exception $e) {
            error_log("Fee receipt generation error: " . $e->getMessage());
            $this->flash('error', 'Unable to generate receipt. Please try again.');
            $this->redirect('/student/fees');
        }
    }

    /**
     * Generate fee receipt PDF using TCPDF
     */
    private function generateFeeReceiptPDF($data) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Fee Receipt - ' . $data['payment']->receipt_number);

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
        $pdf->Cell(0, 8, 'Fee Payment Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Receipt details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Receipt Details', 0, 1);
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 8, 'Receipt Number:', 0, 0);
        $pdf->Cell(0, 8, $data['payment']->receipt_number, 0, 1);

        $pdf->Cell(50, 8, 'Payment Date:', 0, 0);
        $pdf->Cell(0, 8, date('d-m-Y', strtotime($data['payment']->payment_date)), 0, 1);

        $pdf->Cell(50, 8, 'Payment Mode:', 0, 0);
        $pdf->Cell(0, 8, $data['payment']->getPaymentModeText(), 0, 1);

        if ($data['payment']->transaction_id) {
            $pdf->Cell(50, 8, 'Transaction ID:', 0, 0);
            $pdf->Cell(0, 8, $data['payment']->transaction_id, 0, 1);
        }

        $pdf->Ln(10);

        // Student information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Student Information', 0, 1);
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 8, 'Student Name:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['first_name'] . ' ' . $data['student']['middle_name'] . ' ' . $data['student']['last_name'], 0, 1);

        $pdf->Cell(50, 8, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['scholar_number'], 0, 1);

        $pdf->Cell(50, 8, 'Class:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['class_name'] . ' - ' . $data['student']['class_section'], 0, 1);

        $pdf->Ln(10);

        // Fee details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Fee Details', 0, 1);
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 8, 'Fee Type:', 0, 0);
        $pdf->Cell(0, 8, $data['fee']->fee_type, 0, 1);

        $pdf->Cell(50, 8, 'Academic Year:', 0, 0);
        $pdf->Cell(0, 8, $data['fee']->academic_year, 0, 1);

        $pdf->Cell(50, 8, 'Amount Paid:', 0, 0);
        $pdf->Cell(0, 8, '₹' . number_format($data['payment']->amount_paid, 2), 0, 1);

        $pdf->Cell(50, 8, 'Total Fee Amount:', 0, 0);
        $pdf->Cell(0, 8, '₹' . number_format($data['fee']->amount, 2), 0, 1);

        $remaining = $data['fee']->amount - FeePaymentModel::getTotalPaidForFee($data['fee']->id);
        if ($remaining > 0) {
            $pdf->Cell(50, 8, 'Remaining Balance:', 0, 0);
            $pdf->Cell(0, 8, '₹' . number_format($remaining, 2), 0, 1);
        }

        $pdf->Ln(15);

        // Footer
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Generated on: ' . date('d-m-Y H:i:s'), 0, 1, 'R');
        $pdf->Cell(0, 10, 'This is a computer generated receipt', 0, 1, 'C');

        // Output PDF
        $filename = 'fee_receipt_' . $data['payment']->receipt_number . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Display student profile page
     */
    public function profile() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();

            // Get student data with class information
            $student = Student::withClass($studentId);
            if (!$student) {
                $this->flash('error', 'Student profile not found.');
                $this->redirect('/student/dashboard');
            }

            // Get class subjects
            $classSubjects = $this->getClassSubjects($student->class_id);

            // Get academic information
            $academicInfo = $this->getAcademicInfo($studentId);

            // Prepare view data
            $data = [
                'title' => 'My Profile',
                'student' => $student,
                'class_subjects' => $classSubjects,
                'academic_info' => $academicInfo,
                'current_year' => date('Y')
            ];

            // Render profile view
            echo $this->view('student.profile.index', $data);
        } catch (Exception $e) {
            error_log("Student profile error: " . $e->getMessage());
            $this->flash('error', 'Unable to load profile data. Please try again.');
            $this->redirect('/student/dashboard');
        }
    }

    /**
     * Display profile edit page
     */
    public function editProfile() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $studentId = $this->getUserId();

            // Get student data
            $student = Student::withClass($studentId);
            if (!$student) {
                $this->flash('error', 'Student profile not found.');
                $this->redirect('/student/profile');
            }

            // Prepare view data
            $data = [
                'title' => 'Edit Profile',
                'student' => $student,
                'current_year' => date('Y')
            ];

            // Render edit profile view
            echo $this->view('student.profile.edit', $data);
        } catch (Exception $e) {
            error_log("Student edit profile error: " . $e->getMessage());
            $this->flash('error', 'Unable to load profile edit form. Please try again.');
            $this->redirect('/student/profile');
        }
    }

    /**
     * Update student profile
     */
    public function updateProfile() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();

            // Get student data
            $student = Student::find($studentId);
            if (!$student) {
                $this->error('Student not found');
            }

            // Validate input data
            $data = $this->validateProfileData($this->getPost());

            // Update student profile
            $student->fill($data);
            $student->save();

            $this->success(['message' => 'Profile updated successfully']);
        } catch (Exception $e) {
            $this->error('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Display change password page
     */
    public function changePassword() {
        // Check if user is authenticated and is a student
        if (!$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->redirect('/login');
        }

        try {
            $data = [
                'title' => 'Change Password',
                'current_year' => date('Y')
            ];

            // Render change password view
            echo $this->view('student.profile.change_password', $data);
        } catch (Exception $e) {
            error_log("Student change password error: " . $e->getMessage());
            $this->flash('error', 'Unable to load change password form. Please try again.');
            $this->redirect('/student/profile');
        }
    }

    /**
     * Update student password
     */
    public function updatePassword() {
        if (!$this->isAjax() || !$this->isAuthenticated() || !$this->hasRole('student')) {
            $this->error('Unauthorized access');
        }

        try {
            $studentId = $this->getUserId();
            $currentPassword = $this->getPost('current_password');
            $newPassword = $this->getPost('new_password');
            $confirmPassword = $this->getPost('confirm_password');

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
            $user = User::find($studentId);
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

            $this->success(['message' => 'Password changed successfully']);
        } catch (Exception $e) {
            $this->error('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Get class subjects for a student
     */
    private function getClassSubjects($classId) {
        $instance = new static();
        $query = "SELECT cs.*, s.subject_name, s.subject_code, t.first_name, t.last_name
                  FROM class_subjects cs
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  LEFT JOIN teachers t ON cs.teacher_id = t.id
                  WHERE cs.class_id = ?
                  ORDER BY s.subject_name";

        return $instance->db->fetchAll($query, [$classId]);
    }

    /**
     * Get academic information for a student
     */
    private function getAcademicInfo($studentId) {
        $instance = new static();

        // Get attendance summary
        $attendanceQuery = "SELECT
                            COUNT(*) as total_days,
                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                            ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
                           FROM attendance
                           WHERE student_id = ? AND YEAR(attendance_date) = YEAR(CURDATE())";

        $attendance = $instance->db->fetch($attendanceQuery, [$studentId]);

        // Get latest exam results
        $resultsQuery = "SELECT er.*, s.subject_name, e.exam_name, e.exam_type
                        FROM exam_results er
                        LEFT JOIN subjects s ON er.subject_id = s.id
                        LEFT JOIN exams e ON er.exam_id = e.id
                        WHERE er.student_id = ?
                        ORDER BY e.end_date DESC, er.created_at DESC
                        LIMIT 10";

        $results = $instance->db->fetchAll($resultsQuery, [$studentId]);

        // Get fee status
        $feeQuery = "SELECT
                     SUM(amount) as total_fees,
                     SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as paid_fees
                    FROM fees
                    WHERE student_id = ?";

        $fees = $instance->db->fetch($feeQuery, [$studentId]);

        return [
            'attendance' => $attendance ?: ['total_days' => 0, 'present_days' => 0, 'percentage' => 0],
            'recent_results' => $results ?: [],
            'fee_status' => $fees ?: ['total_fees' => 0, 'paid_fees' => 0]
        ];
    }

    /**
     * Validate profile update data
     */
    private function validateProfileData($data) {
        $validated = [];

        // Personal information (read-only fields are not included)
        $allowedFields = [
            'temporary_address', 'mobile', 'email', 'medical_conditions'
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
     * Generate PDF report card using TCPDF
     */
    private function generatePDFReportCard($data) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Report Card - ' . $data['student']['full_name']);

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
        $pdf->Cell(0, 8, 'Academic Year: ' . $data['student']['academic_year'], 0, 1, 'C');
        $pdf->Ln(10);

        // Student information
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Student Report Card', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(50, 8, 'Student Name:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['full_name'], 0, 1);

        $pdf->Cell(50, 8, 'Class:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['class_name'] . ' - ' . $data['student']['section'], 0, 1);

        $pdf->Cell(50, 8, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['scholar_number'], 0, 1);

        $pdf->Cell(50, 8, 'Admission Number:', 0, 0);
        $pdf->Cell(0, 8, $data['student']['admission_number'], 0, 1);

        if ($data['rank']) {
            $pdf->Cell(50, 8, 'Class Rank:', 0, 0);
            $pdf->Cell(0, 8, $data['rank']['rank'] . ' out of ' . $data['rank']['total_students'], 0, 1);
        }

        $pdf->Ln(10);

        // Results summary
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Academic Performance Summary', 0, 1);
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(60, 8, 'Total Subjects:', 0, 0);
        $pdf->Cell(30, 8, $data['summary']['total_subjects'], 0, 0);
        $pdf->Cell(60, 8, 'Average Percentage:', 0, 0);
        $pdf->Cell(0, 8, $data['summary']['average_percentage'] . '%', 0, 1);

        $pdf->Cell(60, 8, 'Overall Grade:', 0, 0);
        $pdf->Cell(30, 8, $data['summary']['overall_grade'], 0, 0);
        $pdf->Cell(60, 8, 'Attendance:', 0, 0);
        $pdf->Cell(0, 8, $data['attendance']['percentage'] . '%', 0, 1);

        $pdf->Ln(10);

        // Subject-wise results
        if (!empty($data['results'])) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Subject-wise Results', 0, 1);

            // Table header
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(60, 8, 'Subject', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Marks', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Max Marks', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Percentage', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Grade', 1, 1, 'C', true);

            // Table data
            $pdf->SetFont('helvetica', '', 10);
            foreach ($data['results'] as $result) {
                $pdf->Cell(60, 8, $result['subject_name'], 1, 0);
                $pdf->Cell(25, 8, $result['marks_obtained'], 1, 0, 'C');
                $pdf->Cell(25, 8, $result['max_marks'], 1, 0, 'C');
                $pdf->Cell(25, 8, $result['percentage'] . '%', 1, 0, 'C');
                $pdf->Cell(20, 8, $result['calculated_grade'], 1, 1, 'C');
            }
        }

        $pdf->Ln(15);

        // Footer
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Generated on: ' . date('d-m-Y H:i:s', strtotime($data['generated_date'])), 0, 1, 'R');

        // Output PDF
        $filename = 'report_card_' . $data['student']['scholar_number'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }
}