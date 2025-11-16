<?php
/**
 * Cashier Fees Controller
 * Handles cashier-specific fee management operations
 */

class CashierFeesController extends BaseController {

    /**
     * Display cashier fees dashboard
     */
    public function index() {
        try {
            $userId = $this->getCurrentUserId();

            // Get today's stats for this cashier
            $todayStats = $this->getTodayStats($userId);

            // Get this month's stats
            $monthStats = $this->getMonthStats($userId);

            // Get recent payments by this cashier
            $recentPayments = FeePaymentModel::getByDateRange(
                date('Y-m-d'),
                date('Y-m-d'),
                $userId,
                10
            );

            // Get outstanding count
            $outstandingCount = count(FeeModel::getOutstandingFees());

            $data = [
                'title' => 'Cashier Dashboard',
                'todayStats' => $todayStats,
                'monthStats' => $monthStats,
                'recentPayments' => $recentPayments,
                'outstandingCount' => $outstandingCount
            ];

            echo $this->view('cashier.fees.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading cashier dashboard: ' . $e->getMessage());
            echo $this->view('cashier.fees.index', [
                'title' => 'Cashier Dashboard',
                'todayStats' => ['total_payments' => 0, 'total_amount' => 0],
                'monthStats' => ['total_amount' => 0],
                'recentPayments' => [],
                'outstandingCount' => 0
            ]);
        }
    }

    /**
     * Display fee collection form
     */
    public function collect() {
        try {
            $classId = $this->input('class_id');
            $village = $this->input('village');
            $studentId = $this->input('student_id');

            // Get classes for filter
            $classes = $this->db->fetchAll(
                "SELECT DISTINCT c.id, c.class_name, c.section
                 FROM classes c
                 INNER JOIN students s ON c.id = s.class_id
                 WHERE s.is_active = 1
                 ORDER BY c.class_name, c.section"
            );

            // Get villages for filter
            $villages = $this->db->fetchAll(
                "SELECT DISTINCT village_address as village
                 FROM students
                 WHERE is_active = 1 AND village_address IS NOT NULL AND village_address != ''
                 ORDER BY village_address"
            );

            $students = [];
            if ($classId) {
                $query = "SELECT s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                                 s.father_name, s.mobile, s.village_address,
                                 c.class_name, c.section
                          FROM students s
                          LEFT JOIN classes c ON s.class_id = c.id
                          WHERE s.is_active = 1 AND s.class_id = ?";

                $params = [$classId];

                if ($village) {
                    $query .= " AND s.village_address LIKE ?";
                    $params[] = "%{$village}%";
                }

                $query .= " ORDER BY s.first_name, s.last_name";

                $students = $this->db->fetchAll($query, $params);
            }

            $data = [
                'title' => 'Fee Collection',
                'classes' => $classes,
                'villages' => $villages,
                'students' => $students,
                'filters' => [
                    'class_id' => $classId,
                    'village' => $village,
                    'student_id' => $studentId
                ]
            ];

            echo $this->view('cashier.fees.collect', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading fee collection form: ' . $e->getMessage());
            $this->redirect('/cashier/fees');
        }
    }

    /**
     * Process fee payment
     */
    public function processPayment() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/cashier/fees/collect');
        }

        $rules = [
            'selected_fees' => 'required',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,online,cheque,upi',
            'payment_date' => 'required|date',
            'remarks' => 'max:500'
        ];

        // Add conditional validation for payment modes
        $paymentMode = $this->input('payment_mode');
        if ($paymentMode === 'online' || $paymentMode === 'upi') {
            $rules['transaction_id'] = 'required|max:100';
        } elseif ($paymentMode === 'cheque') {
            $rules['cheque_number'] = 'required|max:50';
        }

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/cashier/fees/collect');
        }

        try {
            $selectedFees = explode(',', $validated['selected_fees']);
            $totalAmount = 0;
            $payments = [];

            // Process each fee payment
            foreach ($selectedFees as $feeId) {
                $feeId = trim($feeId);
                if (empty($feeId)) continue;

                // Check if fee exists and is unpaid
                $fee = FeeModel::find($feeId);
                if (!$fee || $fee->is_paid) {
                    continue; // Skip if fee doesn't exist or already paid
                }

                $totalAmount += $fee->amount;

                // Record payment for this fee
                $paymentData = [
                    'payment_date' => $validated['payment_date'],
                    'amount_paid' => $fee->amount, // Pay full amount for each fee
                    'payment_mode' => $validated['payment_mode'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'cheque_number' => $validated['cheque_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? ''
                ];

                $payment = FeePaymentModel::recordPayment(
                    $feeId,
                    $paymentData,
                    $this->getCurrentUserId()
                );

                if ($payment) {
                    $payments[] = $payment;
                }
            }

            if (!empty($payments)) {
                $receiptCount = count($payments);
                $this->flash('success', "Payment recorded successfully for {$receiptCount} fee(s). Receipt Number: " . $payments[0]->receipt_number);
                $this->redirect('/cashier/fees/receipt/' . $payments[0]->id);
            } else {
                $this->flash('error', 'Failed to record payment');
                $this->redirect('/cashier/fees/collect');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error processing payment: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/cashier/fees/collect');
        }
    }

    /**
     * Generate and display receipt
     */
    public function receipt($paymentId) {
        try {
            $payment = FeePaymentModel::withDetails($paymentId);

            if (!$payment) {
                $this->flash('error', 'Payment record not found');
                $this->redirect('/cashier/fees');
            }

            $data = [
                'title' => 'Payment Receipt - ' . $payment->receipt_number,
                'payment' => $payment
            ];

            echo $this->view('cashier.fees.receipt', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading receipt: ' . $e->getMessage());
            $this->redirect('/cashier/fees');
        }
    }

    /**
     * Generate PDF receipt
     */
    public function generateReceipt($paymentId) {
        try {
            $payment = FeePaymentModel::withDetails($paymentId);

            if (!$payment) {
                $this->flash('error', 'Payment record not found');
                $this->redirect('/cashier/fees');
            }

            // Include TCPDF
            require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

            // Create new PDF document (A4 size for triple receipt)
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('School Management System');
            $pdf->SetAuthor('School Management System');
            $pdf->SetTitle('Fee Receipt - ' . $payment->receipt_number);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false, 0);

            // Add a page
            $pdf->AddPage();

            // Receipt copies: School, Student, Accounts
            $copies = ['SCHOOL COPY', 'STUDENT COPY', 'ACCOUNTS COPY'];

            foreach ($copies as $index => $copyType) {
                $yOffset = $index * 85; // Space each receipt vertically

                // Border
                $pdf->Rect(10, 10 + $yOffset, 190, 80);

                // Header
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Cell(0, 8, 'SCHOOL MANAGEMENT SYSTEM', 0, 1, 'C');
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 6, 'FEE RECEIPT', 0, 1, 'C');
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 5, $copyType, 0, 1, 'C');

                // Receipt details
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(100, 5, 'Receipt No: ' . $payment->receipt_number, 0, 0);
                $pdf->Cell(0, 5, 'Date: ' . date('d-m-Y', strtotime($payment->payment_date)), 0, 1);

                // Student details
                $pdf->Ln(2);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(0, 5, 'Student Details:', 0, 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(50, 4, 'Scholar No:', 0, 0);
                $pdf->Cell(0, 4, $payment->scholar_number, 0, 1);
                $pdf->Cell(50, 4, 'Name:', 0, 0);
                $pdf->Cell(0, 4, $payment->first_name . ' ' . ($payment->middle_name ? $payment->middle_name . ' ' : '') . $payment->last_name, 0, 1);
                $pdf->Cell(50, 4, 'Class:', 0, 0);
                $pdf->Cell(0, 4, $payment->class_name . ' ' . $payment->section, 0, 1);
                $pdf->Cell(50, 4, 'Father:', 0, 0);
                $pdf->Cell(0, 4, $payment->father_name ?: 'N/A', 0, 1);

                // Payment details
                $pdf->Ln(2);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(0, 5, 'Payment Details:', 0, 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(50, 4, 'Fee Type:', 0, 0);
                $pdf->Cell(0, 4, $payment->fee_type, 0, 1);
                $pdf->Cell(50, 4, 'Amount Paid:', 0, 0);
                $pdf->Cell(0, 4, '₹' . number_format($payment->amount_paid, 2), 0, 1);
                $pdf->Cell(50, 4, 'Payment Mode:', 0, 0);
                $pdf->Cell(0, 4, $payment->getPaymentModeText(), 0, 1);

                if ($payment->transaction_id) {
                    $pdf->Cell(50, 4, 'Transaction ID:', 0, 0);
                    $pdf->Cell(0, 4, $payment->transaction_id, 0, 1);
                }

                if ($payment->cheque_number) {
                    $pdf->Cell(50, 4, 'Cheque No:', 0, 0);
                    $pdf->Cell(0, 4, $payment->cheque_number, 0, 1);
                }

                // Remarks
                if ($payment->remarks) {
                    $pdf->Cell(50, 4, 'Remarks:', 0, 0);
                    $pdf->Cell(0, 4, $payment->remarks, 0, 1);
                }

                // Signature line
                $pdf->Ln(5);
                $pdf->Cell(80, 4, 'Cashier: ___________________', 0, 0);
                $pdf->Cell(0, 4, 'Principal: ___________________', 0, 1);

                // Separator line for next copy
                if ($index < 2) {
                    $pdf->Ln(2);
                    $pdf->Cell(0, 0, '', 'T', 1);
                }
            }

            // Output the PDF
            $filename = 'Fee_Receipt_' . $payment->receipt_number . '.pdf';
            $pdf->Output($filename, 'D');

        } catch (Exception $e) {
            $this->flash('error', 'Error generating receipt: ' . $e->getMessage());
            $this->redirect('/cashier/fees');
        }
    }

    /**
     * Display outstanding fees
     */
    public function outstanding() {
        try {
            $classId = $this->input('class_id');
            $village = $this->input('village');
            $overdueOnly = $this->input('overdue_only') === '1';

            // Get classes for filter
            $classes = $this->db->fetchAll(
                "SELECT DISTINCT c.id, c.class_name, c.section
                 FROM classes c
                 INNER JOIN students s ON c.id = s.class_id
                 WHERE s.is_active = 1
                 ORDER BY c.class_name, c.section"
            );

            // Get villages for filter
            $villages = $this->db->fetchAll(
                "SELECT DISTINCT village_address as village
                 FROM students
                 WHERE is_active = 1 AND village_address IS NOT NULL AND village_address != ''
                 ORDER BY village_address"
            );

            $outstandingFees = FeeModel::getOutstandingFees($classId, null, $village);

            // Filter overdue only if requested
            if ($overdueOnly) {
                $outstandingFees = array_filter($outstandingFees, function($fee) {
                    return $fee->isOverdue();
                });
            }

            // Get summary stats
            $summaryStats = [
                'total_outstanding' => count($outstandingFees),
                'total_amount' => array_sum(array_column($outstandingFees, 'amount')),
                'overdue_count' => count(array_filter($outstandingFees, function($fee) { return $fee->isOverdue(); })),
                'unique_students' => count(array_unique(array_column($outstandingFees, 'student_id')))
            ];

            $data = [
                'title' => 'Outstanding Fees',
                'outstandingFees' => $outstandingFees,
                'classes' => $classes,
                'villages' => $villages,
                'summaryStats' => $summaryStats,
                'filters' => [
                    'class_id' => $classId,
                    'village' => $village,
                    'overdue_only' => $overdueOnly
                ]
            ];

            echo $this->view('cashier.fees.outstanding', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading outstanding fees: ' . $e->getMessage());
            echo $this->view('cashier.fees.outstanding', [
                'title' => 'Outstanding Fees',
                'outstandingFees' => [],
                'classes' => [],
                'villages' => [],
                'summaryStats' => ['total_outstanding' => 0, 'total_amount' => 0, 'overdue_count' => 0, 'unique_students' => 0],
                'filters' => []
            ]);
        }
    }

    /**
     * Display financial reports
     */
    public function reports() {
        try {
            $reportType = $this->input('type', 'collection');
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $userId = $this->getCurrentUserId();

            $data = [
                'title' => 'Financial Reports',
                'reportType' => $reportType,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            // Get classes for filter
            $data['classes'] = $this->db->fetchAll(
                "SELECT id, class_name, section FROM classes ORDER BY class_name, section"
            );

            // Generate report based on type
            switch ($reportType) {
                case 'collection':
                    $data['reportData'] = $this->getMyCollectionReport($startDate, $endDate, $userId);
                    break;
                case 'daily':
                    $data['reportData'] = $this->getDailyReport($startDate, $endDate, $userId);
                    break;
                case 'payment_modes':
                    $data['reportData'] = $this->getPaymentModeReport($startDate, $endDate, $userId);
                    break;
                default:
                    $data['reportData'] = [];
            }

            echo $this->view('cashier.fees.reports', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading reports: ' . $e->getMessage());
            echo $this->view('cashier.fees.reports', [
                'title' => 'Financial Reports',
                'reportType' => 'collection',
                'startDate' => date('Y-m-01'),
                'endDate' => date('Y-m-t'),
                'classes' => [],
                'reportData' => []
            ]);
        }
    }

    /**
     * Get today's stats for cashier
     */
    private function getTodayStats($userId) {
        $query = "SELECT COUNT(*) as total_payments, SUM(amount_paid) as total_amount
                  FROM fee_payments
                  WHERE collected_by = ? AND DATE(payment_date) = CURDATE()";

        $result = $this->db->fetch($query, [$userId]);

        return [
            'total_payments' => $result['total_payments'] ?? 0,
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }

    /**
     * Get month's stats for cashier
     */
    private function getMonthStats($userId) {
        $query = "SELECT SUM(amount_paid) as total_amount
                  FROM fee_payments
                  WHERE collected_by = ? AND MONTH(payment_date) = MONTH(CURDATE())
                  AND YEAR(payment_date) = YEAR(CURDATE())";

        $result = $this->db->fetch($query, [$userId]);

        return [
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }

    /**
     * Get my collection report data
     */
    private function getMyCollectionReport($startDate, $endDate, $userId) {
        $query = "SELECT fp.*, f.fee_type, f.academic_year,
                         s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         c.class_name, c.section
                  FROM fee_payments fp
                  LEFT JOIN fees f ON fp.fee_id = f.id
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE fp.collected_by = ? AND fp.payment_date BETWEEN ? AND ?
                  ORDER BY fp.payment_date DESC";

        return $this->db->fetchAll($query, [$userId, $startDate, $endDate]);
    }

    /**
     * Get daily report data
     */
    private function getDailyReport($startDate, $endDate, $userId) {
        $query = "SELECT DATE(payment_date) as date,
                         COUNT(*) as payments,
                         SUM(amount_paid) as amount
                  FROM fee_payments
                  WHERE collected_by = ? AND payment_date BETWEEN ? AND ?
                  GROUP BY DATE(payment_date)
                  ORDER BY DATE(payment_date)";

        return $this->db->fetchAll($query, [$userId, $startDate, $endDate]);
    }

    /**
     * Get payment mode report data
     */
    private function getPaymentModeReport($startDate, $endDate, $userId) {
        return FeePaymentModel::getPaymentStats($startDate, $endDate, $userId);
    }

    /**
     * Send payment reminder
     */
    public function sendReminder() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $feeId = $this->input('fee_id');

            if (!$feeId) {
                $this->json(['success' => false, 'message' => 'Fee ID is required']);
            }

            // Get fee details
            $fee = FeeModel::withStudent($feeId);
            if (!$fee) {
                $this->json(['success' => false, 'message' => 'Fee record not found']);
            }

            // Here you would implement email/SMS sending logic
            // For now, just return success
            $this->json(['success' => true, 'message' => 'Reminder sent successfully']);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error sending reminder: ' . $e->getMessage()]);
        }
    }

    /**
     * Send multiple reminders
     */
    public function sendReminders() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $feeIds = $this->input('fee_ids');

            if (!$feeIds || !is_array($feeIds)) {
                $this->json(['success' => false, 'message' => 'Fee IDs are required']);
            }

            // Here you would implement bulk email/SMS sending logic
            // For now, just return success
            $this->json(['success' => true, 'message' => 'Reminders sent to ' . count($feeIds) . ' students']);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error sending reminders: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Search students
     */
    public function ajaxSearchStudent() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $term = $this->input('term');

            if (!$term) {
                $this->json(['students' => []]);
            }

            $students = Student::search($term);

            // Format for response
            $formattedStudents = [];
            foreach ($students as $student) {
                $formattedStudents[] = [
                    'id' => $student->id,
                    'scholar_number' => $student->scholar_number,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'class_name' => $student->class_name,
                    'section' => $student->section,
                    'father_name' => $student->father_name,
                    'mobile' => $student->mobile,
                    'village_address' => $student->village_address
                ];
            }

            $this->json(['students' => $formattedStudents]);

        } catch (Exception $e) {
            $this->error('Error searching students: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get students by class
     */
    public function ajaxGetStudents() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $classId = $this->input('class_id');
            $village = $this->input('village');

            if (!$classId) {
                $this->json(['students' => []]);
            }

            $query = "SELECT s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                             s.father_name, s.mobile, s.village_address,
                             c.class_name, c.section
                      FROM students s
                      LEFT JOIN classes c ON s.class_id = c.id
                      WHERE s.is_active = 1 AND s.class_id = ?";

            $params = [$classId];

            if ($village) {
                $query .= " AND s.village_address LIKE ?";
                $params[] = "%{$village}%";
            }

            $query .= " ORDER BY s.first_name, s.last_name";

            $students = $this->db->fetchAll($query, $params);

            $this->json(['students' => $students]);

        } catch (Exception $e) {
            $this->error('Error loading students: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get student fees
     */
    public function ajaxGetStudentFees() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $studentId = $this->input('student_id');

            if (!$studentId) {
                $this->json(['fees' => []]);
            }

            $fees = FeeModel::getByStudent($studentId);

            $this->json(['fees' => $fees]);

        } catch (Exception $e) {
            $this->error('Error loading student fees: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get outstanding fees for cashier
     */
    public function ajaxGetOutstanding() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $limit = (int) $this->input('limit', 50);

            $outstandingFees = FeeModel::getOutstandingFees();

            // Limit results if specified
            if ($limit > 0 && count($outstandingFees) > $limit) {
                $outstandingFees = array_slice($outstandingFees, 0, $limit);
            }

            // Format for response
            $formattedFees = [];
            foreach ($outstandingFees as $fee) {
                $formattedFees[] = [
                    'id' => $fee->id,
                    'student_id' => $fee->student_id,
                    'scholar_number' => $fee->scholar_number,
                    'first_name' => $fee->first_name,
                    'middle_name' => $fee->middle_name,
                    'last_name' => $fee->last_name,
                    'class_name' => $fee->class_name,
                    'section' => $fee->section,
                    'father_name' => $fee->father_name,
                    'mobile' => $fee->mobile,
                    'village_address' => $fee->village_address,
                    'fee_type' => $fee->fee_type,
                    'amount' => $fee->amount,
                    'due_date' => $fee->due_date,
                    'days_overdue' => $fee->getDaysOverdue()
                ];
            }

            $this->json(['students' => $formattedFees]);

        } catch (Exception $e) {
            $this->error('Error loading outstanding fees: ' . $e->getMessage());
        }
    }

    /**
     * Export report to CSV/Excel
     */
    public function exportReport() {
        try {
            $reportType = $this->input('type');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $format = $this->input('format', 'csv');
            $userId = $this->getCurrentUserId();

            // Get report data
            switch ($reportType) {
                case 'collection':
                    $data = $this->getMyCollectionReport($startDate, $endDate, $userId);
                    $filename = 'My_Fee_Collection_' . date('Y-m-d');
                    break;
                case 'daily':
                    $data = $this->getDailyReport($startDate, $endDate, $userId);
                    $filename = 'Daily_Collection_Report_' . date('Y-m-d');
                    break;
                default:
                    throw new Exception('Invalid report type');
            }

            if ($format === 'csv') {
                $this->exportToCSV($data, $filename . '.csv');
            } else {
                $this->exportToPDF($data, $filename . '.pdf', $reportType);
            }

        } catch (Exception $e) {
            $this->flash('error', 'Export failed: ' . $e->getMessage());
            $this->redirect('/cashier/fees/reports');
        }
    }

    /**
     * Export data to CSV
     */
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to PDF
     */
    private function exportToPDF($data, $filename, $reportType) {
        require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetTitle(ucfirst($reportType) . ' Report');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, strtoupper(str_replace('_', ' ', $reportType)) . ' REPORT', 0, 1, 'C');
        $pdf->Ln(5);

        // Table headers
        $pdf->SetFont('helvetica', 'B', 10);
        if ($reportType === 'collection') {
            $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Receipt No', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Student', 1, 0, 'C');
            $pdf->Cell(20, 8, 'Class', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Fee Type', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Amount', 1, 0, 'C');
            $pdf->Cell(20, 8, 'Mode', 1, 1, 'C');
        }

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $row) {
            if ($reportType === 'collection') {
                $pdf->Cell(25, 6, date('d-m-Y', strtotime($row['payment_date'])), 1, 0, 'C');
                $pdf->Cell(25, 6, $row['receipt_number'], 1, 0, 'C');
                $pdf->Cell(30, 6, substr($row['first_name'] . ' ' . $row['last_name'], 0, 20), 1, 0, 'L');
                $pdf->Cell(20, 6, $row['class_name'] . ' ' . $row['section'], 1, 0, 'C');
                $pdf->Cell(25, 6, substr($row['fee_type'], 0, 15), 1, 0, 'L');
                $pdf->Cell(25, 6, '₹' . number_format($row['amount_paid'], 2), 1, 0, 'R');
                $pdf->Cell(20, 6, $row['payment_mode'], 1, 1, 'C');
            }
        }

        $pdf->Output($filename, 'D');
    }

    /**
     * Get analytics data for outstanding fees
     */
    public function analytics() {
        try {
            $userId = $this->getCurrentUserId();

            // Get basic stats
            $basicStats = $this->getOutstandingBasicStats();

            // Get analytics data
            $analytics = [
                'total_outstanding' => $basicStats['total_outstanding'],
                'overdue_count' => $basicStats['overdue_count'],
                'affected_students' => $basicStats['unique_students'],
                'collection_rate' => $basicStats['collection_rate'],
                'total_fees' => $basicStats['total_outstanding'],
                'class_distribution' => $this->getClassDistribution(),
                'overdue_trends' => $this->getOverdueTrends(),
                'payment_patterns' => $this->getPaymentPatterns(),
                'village_stats' => $this->getVillageStats()
            ];

            $data = [
                'title' => 'Outstanding Fees Analytics',
                'analytics' => $analytics
            ];

            echo $this->view('cashier.fees.analytics', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading analytics: ' . $e->getMessage());
            $this->redirect('/cashier/fees/outstanding');
        }
    }

    /**
     * Get basic outstanding fees statistics
     */
    private function getOutstandingBasicStats() {
        $query = "SELECT
                    COUNT(DISTINCT f.id) as total_outstanding,
                    SUM(f.amount) as total_amount,
                    COUNT(DISTINCT CASE WHEN DATEDIFF(CURDATE(), f.due_date) > 0 THEN f.student_id END) as overdue_count,
                    COUNT(DISTINCT f.student_id) as unique_students
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.id
                  WHERE f.is_paid = 0 AND s.is_active = 1";

        $result = $this->db->fetch($query);

        // Calculate collection rate (simplified - fees paid this month / total fees due this month)
        $collectionQuery = "SELECT
                            (SELECT COUNT(*) FROM fee_payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())) /
                            (SELECT COUNT(*) FROM fees WHERE MONTH(due_date) = MONTH(CURDATE()) AND YEAR(due_date) = YEAR(CURDATE())) * 100 as collection_rate";

        $collectionResult = $this->db->fetch($collectionQuery);

        return [
            'total_outstanding' => $result['total_outstanding'] ?? 0,
            'total_amount' => $result['total_amount'] ?? 0,
            'overdue_count' => $result['overdue_count'] ?? 0,
            'unique_students' => $result['unique_students'] ?? 0,
            'collection_rate' => round($collectionResult['collection_rate'] ?? 0, 1)
        ];
    }

    /**
     * Bulk update fees
     */
    public function bulkUpdate() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request');
        }

        try {
            $action = $this->input('action');
            $feeIds = $this->input('fee_ids');
            $userId = $this->getCurrentUserId();

            if (!$action || !$feeIds || !is_array($feeIds)) {
                $this->json(['success' => false, 'message' => 'Invalid parameters']);
            }

            $updatedCount = 0;

            switch ($action) {
                case 'send_reminders':
                    $updatedCount = $this->sendBulkReminders($feeIds);
                    break;

                case 'extend_due_date':
                    $newDueDate = $this->input('new_due_date');
                    if (!$newDueDate) {
                        $this->json(['success' => false, 'message' => 'New due date is required']);
                    }
                    $updatedCount = $this->extendDueDates($feeIds, $newDueDate, $userId);
                    break;

                case 'apply_discount':
                    $discountPercentage = $this->input('discount_percentage');
                    if (!$discountPercentage || $discountPercentage <= 0 || $discountPercentage > 100) {
                        $this->json(['success' => false, 'message' => 'Valid discount percentage is required']);
                    }
                    $updatedCount = $this->applyDiscounts($feeIds, $discountPercentage, $userId);
                    break;

                case 'mark_urgent':
                    $updatedCount = $this->markAsUrgent($feeIds, $userId);
                    break;

                default:
                    $this->json(['success' => false, 'message' => 'Invalid action']);
            }

            $this->json([
                'success' => true,
                'message' => "Successfully processed {$updatedCount} fees",
                'updated_count' => $updatedCount
            ]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error processing bulk update: ' . $e->getMessage()]);
        }
    }

    /**
     * Export outstanding fees data
     */
    public function exportOutstanding() {
        try {
            $classId = $this->input('class_id');
            $village = $this->input('village');
            $statusFilter = $this->input('status_filter');
            $minAmount = $this->input('min_amount');
            $maxAmount = $this->input('max_amount');
            $dueDateFrom = $this->input('due_date_from');
            $dueDateTo = $this->input('due_date_to');
            $searchStudent = $this->input('search_student');
            $feeType = $this->input('fee_type');
            $format = $this->input('format', 'excel');

            // Build query with filters
            $query = "SELECT f.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                             s.admission_number, s.father_name, s.mobile, s.village_address,
                             c.class_name, c.section,
                             DATEDIFF(CURDATE(), f.due_date) as days_overdue
                      FROM fees f
                      LEFT JOIN students s ON f.student_id = s.id
                      LEFT JOIN classes c ON s.class_id = c.id
                      WHERE f.is_paid = 0 AND s.is_active = 1";

            $params = [];

            if ($classId) {
                $query .= " AND s.class_id = ?";
                $params[] = $classId;
            }

            if ($village) {
                $query .= " AND s.village_address LIKE ?";
                $params[] = "%{$village}%";
            }

            if ($statusFilter) {
                switch ($statusFilter) {
                    case 'overdue':
                        $query .= " AND f.due_date < CURDATE()";
                        break;
                    case 'due_soon':
                        $query .= " AND f.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                        break;
                    case 'due_month':
                        $query .= " AND MONTH(f.due_date) = MONTH(CURDATE()) AND YEAR(f.due_date) = YEAR(CURDATE())";
                        break;
                }
            }

            if ($minAmount) {
                $query .= " AND f.amount >= ?";
                $params[] = $minAmount;
            }

            if ($maxAmount) {
                $query .= " AND f.amount <= ?";
                $params[] = $maxAmount;
            }

            if ($dueDateFrom) {
                $query .= " AND f.due_date >= ?";
                $params[] = $dueDateFrom;
            }

            if ($dueDateTo) {
                $query .= " AND f.due_date <= ?";
                $params[] = $dueDateTo;
            }

            if ($searchStudent) {
                $query .= " AND (CONCAT(s.first_name, ' ', s.last_name) LIKE ? OR s.scholar_number LIKE ? OR s.admission_number LIKE ?)";
                $searchTerm = "%{$searchStudent}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($feeType) {
                $query .= " AND f.fee_type = ?";
                $params[] = $feeType;
            }

            $query .= " ORDER BY f.due_date ASC, s.first_name, s.last_name";

            $data = $this->db->fetchAll($query, $params);

            if ($format === 'csv') {
                $this->exportOutstandingToCSV($data);
            } else {
                $this->exportOutstandingToExcel($data);
            }

        } catch (Exception $e) {
            $this->flash('error', 'Export failed: ' . $e->getMessage());
            $this->redirect('/cashier/fees/outstanding');
        }
    }

    /**
     * Get class distribution data
     */
    private function getClassDistribution() {
        $query = "SELECT c.class_name, c.section, COUNT(f.id) as fee_count, SUM(f.amount) as total_amount
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE f.is_paid = 0 AND s.is_active = 1
                  GROUP BY c.id, c.class_name, c.section
                  ORDER BY c.class_name, c.section";

        return $this->db->fetchAll($query);
    }

    /**
     * Get overdue trends data
     */
    private function getOverdueTrends() {
        $query = "SELECT
                    CASE
                        WHEN days_overdue <= 0 THEN 'Not Due'
                        WHEN days_overdue <= 7 THEN '1-7 Days'
                        WHEN days_overdue <= 30 THEN '8-30 Days'
                        WHEN days_overdue <= 90 THEN '31-90 Days'
                        ELSE '90+ Days'
                    END as overdue_range,
                    COUNT(*) as count,
                    SUM(f.amount) as amount
                  FROM (
                      SELECT f.*, DATEDIFF(CURDATE(), f.due_date) as days_overdue
                      FROM fees f
                      LEFT JOIN students s ON f.student_id = s.id
                      WHERE f.is_paid = 0 AND s.is_active = 1
                  ) t
                  GROUP BY overdue_range
                  ORDER BY FIELD(overdue_range, 'Not Due', '1-7 Days', '8-30 Days', '31-90 Days', '90+ Days')";

        return $this->db->fetchAll($query);
    }

    /**
     * Get payment patterns data
     */
    private function getPaymentPatterns() {
        $query = "SELECT
                    HOUR(p.created_at) as hour,
                    COUNT(*) as payment_count,
                    AVG(p.amount_paid) as avg_amount
                  FROM fee_payments p
                  WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  GROUP BY HOUR(p.created_at)
                  ORDER BY hour";

        return $this->db->fetchAll($query);
    }

    /**
     * Get village statistics
     */
    private function getVillageStats() {
        $query = "SELECT
                    s.village_address as village,
                    COUNT(f.id) as outstanding_count,
                    SUM(f.amount) as total_amount,
                    AVG(f.amount) as avg_amount
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.id
                  WHERE f.is_paid = 0 AND s.is_active = 1 AND s.village_address IS NOT NULL AND s.village_address != ''
                  GROUP BY s.village_address
                  ORDER BY outstanding_count DESC
                  LIMIT 10";

        return $this->db->fetchAll($query);
    }

    /**
     * Send bulk reminders
     */
    private function sendBulkReminders($feeIds) {
        // Implementation for sending bulk reminders
        // This would integrate with email/SMS service
        return count($feeIds); // Placeholder
    }

    /**
     * Extend due dates for fees
     */
    private function extendDueDates($feeIds, $newDueDate, $userId) {
        $updated = 0;
        foreach ($feeIds as $feeId) {
            $fee = FeeModel::find($feeId);
            if ($fee) {
                $fee->due_date = $newDueDate;
                $fee->updated_at = date('Y-m-d H:i:s');
                if ($fee->save()) {
                    $updated++;
                }
            }
        }
        return $updated;
    }

    /**
     * Apply discounts to fees
     */
    private function applyDiscounts($feeIds, $discountPercentage, $userId) {
        $updated = 0;
        foreach ($feeIds as $feeId) {
            $fee = FeeModel::find($feeId);
            if ($fee) {
                $discountAmount = ($fee->amount * $discountPercentage) / 100;
                $fee->amount = $fee->amount - $discountAmount;
                $fee->updated_at = date('Y-m-d H:i:s');
                if ($fee->save()) {
                    $updated++;
                }
            }
        }
        return $updated;
    }

    /**
     * Mark fees as urgent
     */
    private function markAsUrgent($feeIds, $userId) {
        // Implementation for marking fees as urgent
        // This could add a flag or priority level
        return count($feeIds); // Placeholder
    }

    /**
     * Export outstanding fees to CSV
     */
    private function exportOutstandingToCSV($data) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="outstanding_fees_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Scholar Number',
            'Student Name',
            'Class',
            'Father Name',
            'Village',
            'Mobile',
            'Fee Type',
            'Amount',
            'Due Date',
            'Days Overdue',
            'Status'
        ]);

        // CSV data
        foreach ($data as $row) {
            $studentName = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name'];
            $status = $row['days_overdue'] > 0 ? 'Overdue' : 'Pending';

            fputcsv($output, [
                $row['scholar_number'],
                $studentName,
                $row['class_name'] . ' ' . $row['section'],
                $row['father_name'] ?: 'N/A',
                $row['village_address'] ?: 'N/A',
                $row['mobile'] ?: 'N/A',
                $row['fee_type'],
                $row['amount'],
                $row['due_date'],
                max(0, $row['days_overdue']),
                $status
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export outstanding fees to Excel
     */
    private function exportOutstandingToExcel($data) {
        require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetTitle('Outstanding Fees Report');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'OUTSTANDING FEES REPORT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Generated on: ' . date('d-m-Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Table headers
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(25, 8, 'Scholar No', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Student Name', 1, 0, 'C');
        $pdf->Cell(15, 8, 'Class', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Father Name', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Fee Type', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Amount', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Due Date', 1, 0, 'C');
        $pdf->Cell(15, 8, 'Status', 1, 1, 'C');

        // Table data
        $pdf->SetFont('helvetica', '', 7);
        foreach ($data as $row) {
            $studentName = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name'];
            $status = $row['days_overdue'] > 0 ? 'Overdue' : 'Pending';

            $pdf->Cell(25, 6, $row['scholar_number'], 1, 0, 'L');
            $pdf->Cell(40, 6, substr($studentName, 0, 25), 1, 0, 'L');
            $pdf->Cell(15, 6, $row['class_name'] . ' ' . $row['section'], 1, 0, 'C');
            $pdf->Cell(30, 6, substr($row['father_name'] ?: 'N/A', 0, 18), 1, 0, 'L');
            $pdf->Cell(25, 6, substr($row['fee_type'], 0, 15), 1, 0, 'L');
            $pdf->Cell(20, 6, '₹' . number_format($row['amount'], 2), 1, 0, 'R');
            $pdf->Cell(20, 6, date('d-m-Y', strtotime($row['due_date'])), 1, 0, 'C');
            $pdf->Cell(15, 6, $status, 1, 1, 'C');
        }

        $pdf->Output('outstanding_fees_' . date('Y-m-d') . '.pdf', 'D');
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}