<?php
/**
 * Fees Controller
 * Handles admin fee management operations
 */

class FeesController extends BaseController {

    /**
     * Display fees dashboard
     */
    public function index() {
        try {
            $academicYear = $this->input('academic_year', date('Y') . '-' . (date('Y') + 1));

            // Get fee statistics
            $stats = FeeModel::getFeeStats($academicYear);

            // Get recent payments
            $recentPayments = FeePaymentModel::getByDateRange(
                date('Y-m-01'),
                date('Y-m-t'),
                null,
                10
            );

            // Get classes for filter
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            $data = [
                'title' => 'Fees Management',
                'stats' => $stats,
                'recentPayments' => $recentPayments,
                'classes' => $classes,
                'academic_year' => $academicYear
            ];

            echo $this->view('admin.fees.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading fees dashboard: ' . $e->getMessage());
            echo $this->view('admin.fees.index', [
                'title' => 'Fees Management',
                'stats' => [],
                'recentPayments' => [],
                'classes' => [],
                'academic_year' => date('Y') . '-' . (date('Y') + 1)
            ]);
        }
    }

    /**
     * Display fee collection form
     */
    public function collect() {
        try {
            $classId = $this->input('class_id');
            $section = $this->input('section');
            $village = $this->input('village');

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
                $students = $this->db->fetchAll(
                    "SELECT s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                            s.father_name, s.mobile, s.village_address,
                            c.class_name, c.section
                     FROM students s
                     LEFT JOIN classes c ON s.class_id = c.id
                     WHERE s.is_active = 1 AND s.class_id = ?
                     ORDER BY s.first_name, s.last_name",
                    [$classId]
                );
            }

            $data = [
                'title' => 'Fee Collection',
                'classes' => $classes,
                'villages' => $villages,
                'students' => $students,
                'filters' => [
                    'class_id' => $classId,
                    'section' => $section,
                    'village' => $village
                ]
            ];

            echo $this->view('admin.fees.collect', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading fee collection form: ' . $e->getMessage());
            $this->redirect('/admin/fees');
        }
    }

    /**
     * Process fee payment
     */
    public function processPayment() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/fees/collect');
        }

        $rules = [
            'fee_id' => 'required|integer',
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
            $this->redirect('/admin/fees/collect');
        }

        try {
            // Check if fee exists
            $fee = FeeModel::find($validated['fee_id']);
            if (!$fee) {
                $this->flash('error', 'Fee record not found');
                $this->redirect('/admin/fees/collect');
            }

            // Record payment
            $paymentData = [
                'payment_date' => $validated['payment_date'],
                'amount_paid' => $validated['amount_paid'],
                'payment_mode' => $validated['payment_mode'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'cheque_number' => $validated['cheque_number'] ?? null,
                'remarks' => $validated['remarks'] ?? ''
            ];

            $payment = FeePaymentModel::recordPayment(
                $validated['fee_id'],
                $paymentData,
                $this->getCurrentUserId()
            );

            if ($payment) {
                $this->flash('success', 'Payment recorded successfully. Receipt Number: ' . $payment->receipt_number);
                $this->redirect('/admin/fees/receipt/' . $payment->id);
            } else {
                $this->flash('error', 'Failed to record payment');
                $this->redirect('/admin/fees/collect');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error processing payment: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/admin/fees/collect');
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
                $this->redirect('/admin/fees');
            }

            $data = [
                'title' => 'Payment Receipt - ' . $payment->receipt_number,
                'payment' => $payment
            ];

            echo $this->view('admin.fees.receipt', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading receipt: ' . $e->getMessage());
            $this->redirect('/admin/fees');
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
                $this->redirect('/admin/fees');
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
            $this->redirect('/admin/fees');
        }
    }

    /**
     * Display outstanding fees
     */
    public function outstanding() {
        try {
            $classId = $this->input('class_id');
            $section = $this->input('section');
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

            $outstandingFees = FeeModel::getOutstandingFees($classId, $section, $village);

            // Filter overdue only if requested
            if ($overdueOnly) {
                $outstandingFees = array_filter($outstandingFees, function($fee) {
                    return $fee->isOverdue();
                });
            }

            $data = [
                'title' => 'Outstanding Fees',
                'outstandingFees' => $outstandingFees,
                'classes' => $classes,
                'villages' => $villages,
                'filters' => [
                    'class_id' => $classId,
                    'section' => $section,
                    'village' => $village,
                    'overdue_only' => $overdueOnly
                ]
            ];

            echo $this->view('admin.fees.outstanding', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading outstanding fees: ' . $e->getMessage());
            echo $this->view('admin.fees.outstanding', [
                'title' => 'Outstanding Fees',
                'outstandingFees' => [],
                'classes' => [],
                'villages' => [],
                'filters' => []
            ]);
        }
    }

    /**
     * Display fee structure management
     */
    public function structure() {
        try {
            $classId = $this->input('class_id');
            $academicYear = $this->input('academic_year', date('Y') . '-' . (date('Y') + 1));

            // Get classes
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            $feeStructure = [];
            if ($classId) {
                $feeStructure = FeeModel::getByClass($classId, null, $academicYear);
            }

            $data = [
                'title' => 'Fee Structure Management',
                'classes' => $classes,
                'feeStructure' => $feeStructure,
                'filters' => [
                    'class_id' => $classId,
                    'academic_year' => $academicYear
                ]
            ];

            echo $this->view('admin.fees.structure', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading fee structure: ' . $e->getMessage());
            echo $this->view('admin.fees.structure', [
                'title' => 'Fee Structure Management',
                'classes' => [],
                'feeStructure' => [],
                'filters' => []
            ]);
        }
    }

    /**
     * Create fee structure for class
     */
    public function createStructure() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/fees/structure');
        }

        $rules = [
            'class_id' => 'required|integer',
            'academic_year' => 'required|max:10',
            'fee_data' => 'required|array'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/admin/fees/structure');
        }

        try {
            $feeData = [];
            foreach ($validated['fee_data'] as $studentId => $fees) {
                foreach ($fees as $fee) {
                    if (!empty($fee['fee_type']) && !empty($fee['amount'])) {
                        $feeData[] = [
                            'student_id' => $studentId,
                            'fee_type' => $fee['fee_type'],
                            'amount' => $fee['amount'],
                            'due_date' => $fee['due_date'] ?: date('Y-m-d'),
                            'academic_year' => $validated['academic_year'],
                            'description' => $fee['description'] ?? ''
                        ];
                    }
                }
            }

            if (empty($feeData)) {
                $this->flash('error', 'No fee data provided');
                $this->redirect('/admin/fees/structure');
            }

            $createdFees = FeeModel::createFeeStructure($feeData, $this->getCurrentUserId());

            if ($createdFees) {
                $this->flash('success', count($createdFees) . ' fee records created successfully');
            } else {
                $this->flash('error', 'Failed to create fee structure');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error creating fee structure: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
        }

        $this->redirect('/admin/fees/structure');
    }

    /**
     * Display financial reports
     */
    public function reports() {
        try {
            $reportType = $this->input('type', 'collection');
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $classId = $this->input('class_id');

            $data = [
                'title' => 'Financial Reports',
                'reportType' => $reportType,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'classId' => $classId
            ];

            // Get classes for filter
            $data['classes'] = $this->db->fetchAll(
                "SELECT id, class_name, section FROM classes ORDER BY class_name, section"
            );

            // Generate report based on type
            switch ($reportType) {
                case 'collection':
                    $data['reportData'] = $this->getCollectionReport($startDate, $endDate, $classId);
                    break;
                case 'outstanding':
                    $data['reportData'] = $this->getOutstandingReport($classId);
                    break;
                case 'payment_modes':
                    $data['reportData'] = $this->getPaymentModeReport($startDate, $endDate);
                    break;
                default:
                    $data['reportData'] = [];
            }

            echo $this->view('admin.fees.reports', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading reports: ' . $e->getMessage());
            echo $this->view('admin.fees.reports', [
                'title' => 'Financial Reports',
                'reportType' => 'collection',
                'startDate' => date('Y-m-01'),
                'endDate' => date('Y-m-t'),
                'classId' => null,
                'classes' => [],
                'reportData' => []
            ]);
        }
    }

    /**
     * Get collection report data
     */
    private function getCollectionReport($startDate, $endDate, $classId = null) {
        $query = "SELECT fp.*, f.fee_type, f.academic_year,
                         s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         c.class_name, c.section, u.username as collected_by_name
                  FROM fee_payments fp
                  LEFT JOIN fees f ON fp.fee_id = f.id
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN users u ON fp.collected_by = u.id
                  WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        $query .= " ORDER BY fp.payment_date DESC";

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get outstanding report data
     */
    private function getOutstandingReport($classId = null) {
        return FeeModel::getOutstandingFees($classId);
    }

    /**
     * Get payment mode report data
     */
    private function getPaymentModeReport($startDate, $endDate) {
        return FeePaymentModel::getPaymentStats($startDate, $endDate);
    }

    /**
     * Export report to PDF/Excel
     */
    public function exportReport() {
        try {
            $reportType = $this->input('type');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $classId = $this->input('class_id');
            $format = $this->input('format', 'pdf');

            // Get report data
            switch ($reportType) {
                case 'collection':
                    $data = $this->getCollectionReport($startDate, $endDate, $classId);
                    $filename = 'Fee_Collection_Report_' . date('Y-m-d');
                    break;
                case 'outstanding':
                    $data = $this->getOutstandingReport($classId);
                    $filename = 'Outstanding_Fees_Report_' . date('Y-m-d');
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
            $this->redirect('/admin/fees/reports');
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
            $pdf->Cell(20, 8, 'Mode', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Collected By', 1, 1, 'C');
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
                $pdf->Cell(20, 6, $row['payment_mode'], 1, 0, 'C');
                $pdf->Cell(30, 6, substr($row['collected_by_name'], 0, 20), 1, 1, 'L');
            }
        }

        $pdf->Output($filename, 'D');
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
     * AJAX: Get students for fee structure creation
     */
    public function ajaxGetStudentsForStructure() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $classId = $this->input('class_id');

            if (!$classId) {
                $this->json(['students' => []]);
            }

            $students = Student::getByClass($classId);

            // Format for response
            $formattedStudents = [];
            foreach ($students as $student) {
                $formattedStudents[] = [
                    'id' => $student->id,
                    'scholar_number' => $student->scholar_number,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'father_name' => $student->father_name,
                    'mobile' => $student->mobile,
                    'village_address' => $student->village_address
                ];
            }

            $this->json(['students' => $formattedStudents]);

        } catch (Exception $e) {
            $this->error('Error loading students: ' . $e->getMessage());
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
     * Delete fee record
     */
    public function deleteFee($feeId) {
        if (!$this->isAjax()) {
            $this->redirect('/admin/fees');
        }

        try {
            $fee = FeeModel::find($feeId);
            if (!$fee) {
                $this->json(['success' => false, 'message' => 'Fee record not found']);
            }

            if ($fee->delete()) {
                $this->json(['success' => true, 'message' => 'Fee deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete fee']);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error deleting fee: ' . $e->getMessage()]);
        }
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}