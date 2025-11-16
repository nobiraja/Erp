<?php
/**
 * Admin Reports Controller
 * Handles comprehensive reporting functionality for the school management system
 */

class AdminReportsController extends BaseController {

    /**
     * Constructor - Apply admin middleware
     */
    public function __construct() {
        parent::__construct();
        $this->middleware(RoleCheckMiddleware::admin());
    }

    /**
     * Display main reports dashboard
     */
    public function index() {
        try {
            // Get school settings
            $schoolName = $this->getSetting('school_name', 'School Management System');

            // Prepare view data
            $data = [
                'title' => 'Reports Dashboard - ' . $schoolName,
                'school_name' => $schoolName,
                'current_user' => $this->getUserData(),
                'current_year' => date('Y'),
                'current_month' => date('F Y')
            ];

            // Render reports dashboard view
            echo $this->view('admin.reports.index', $data);

        } catch (Exception $e) {
            // Log error and show error page
            error_log("Reports dashboard error: " . $e->getMessage());
            $this->error('Failed to load reports dashboard', [], 500);
        }
    }

    /**
     * Academic Reports - Student Performance
     */
    public function academic() {
        try {
            $classId = $this->input('class_id');
            $examId = $this->input('exam_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            // Get filter data
            $classes = ClassModel::allWithTeachers();
            $exams = Exam::getActive();

            $reportData = null;
            if ($classId || $examId || ($startDate && $endDate)) {
                $reportData = AdminReportsModel::getAcademicReport($classId, $examId, $startDate, $endDate);
            }

            $data = [
                'title' => 'Academic Reports - Student Performance',
                'classes' => $classes,
                'exams' => $exams,
                'report_data' => $reportData,
                'class_id' => $classId,
                'exam_id' => $examId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'current_user' => $this->getUserData()
            ];

            echo $this->view('admin.reports.academic', $data);

        } catch (Exception $e) {
            error_log("Academic reports error: " . $e->getMessage());
            $this->error('Failed to load academic reports', [], 500);
        }
    }

    /**
     * Financial Reports
     */
    public function financial() {
        try {
            $reportType = $this->input('type', 'revenue');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $category = $this->input('category');

            $reportData = null;
            if ($startDate && $endDate) {
                $reportData = AdminReportsModel::getFinancialReport($reportType, $startDate, $endDate, $category);
            }

            $data = [
                'title' => 'Financial Reports',
                'report_type' => $reportType,
                'report_data' => $reportData,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'category' => $category,
                'current_user' => $this->getUserData()
            ];

            echo $this->view('admin.reports.financial', $data);

        } catch (Exception $e) {
            error_log("Financial reports error: " . $e->getMessage());
            $this->error('Failed to load financial reports', [], 500);
        }
    }

    /**
     * Attendance Reports
     */
    public function attendance() {
        try {
            $classId = $this->input('class_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $studentId = $this->input('student_id');

            // Get filter data
            $classes = ClassModel::allWithTeachers();

            $reportData = null;
            if ($startDate && $endDate) {
                if ($studentId) {
                    $reportData = Attendance::getStudentAttendance($studentId, $startDate, $endDate);
                    $student = Student::find($studentId);
                } elseif ($classId) {
                    $reportData = Attendance::getClassAttendanceSummary($classId, $startDate, $endDate);
                }
            }

            $data = [
                'title' => 'Attendance Reports',
                'classes' => $classes,
                'report_data' => $reportData,
                'class_id' => $classId,
                'student_id' => $studentId,
                'student' => $student ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'current_user' => $this->getUserData()
            ];

            echo $this->view('admin.reports.attendance', $data);

        } catch (Exception $e) {
            error_log("Attendance reports error: " . $e->getMessage());
            $this->error('Failed to load attendance reports', [], 500);
        }
    }

    /**
     * Custom Reports with filters
     */
    public function custom() {
        try {
            $filters = [
                'class_id' => $this->input('class_id'),
                'subject_id' => $this->input('subject_id'),
                'teacher_id' => $this->input('teacher_id'),
                'start_date' => $this->input('start_date'),
                'end_date' => $this->input('end_date'),
                'status' => $this->input('status')
            ];

            // Get filter data
            $classes = ClassModel::allWithTeachers();
            $subjects = SubjectModel::all();
            $teachers = Teacher::all();

            $reportData = null;
            if (array_filter($filters)) {
                $reportData = AdminReportsModel::getCustomReport($filters);
            }

            $data = [
                'title' => 'Custom Reports',
                'classes' => $classes,
                'subjects' => $subjects,
                'teachers' => $teachers,
                'report_data' => $reportData,
                'filters' => $filters,
                'current_user' => $this->getUserData()
            ];

            echo $this->view('admin.reports.custom', $data);

        } catch (Exception $e) {
            error_log("Custom reports error: " . $e->getMessage());
            $this->error('Failed to load custom reports', [], 500);
        }
    }

    /**
     * Export report to PDF
     */
    public function exportPdf() {
        try {
            $reportType = $this->input('type');
            $filters = $this->input('filters', []);

            if (!$reportType) {
                $this->error('Report type is required');
            }

            $data = AdminReportsModel::getReportData($reportType, $filters);

            // Generate PDF using TCPDF
            $this->generatePdf($reportType, $data, $filters);

        } catch (Exception $e) {
            error_log("PDF export error: " . $e->getMessage());
            $this->error('Failed to export PDF', [], 500);
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel() {
        try {
            $reportType = $this->input('type');
            $filters = $this->input('filters', []);

            if (!$reportType) {
                $this->error('Report type is required');
            }

            $data = AdminReportsModel::getReportData($reportType, $filters);

            // Generate Excel
            $this->generateExcel($reportType, $data, $filters);

        } catch (Exception $e) {
            error_log("Excel export error: " . $e->getMessage());
            $this->error('Failed to export Excel', [], 500);
        }
    }

    /**
     * AJAX endpoint to get report data
     */
    public function getData() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $reportType = $this->input('type');
            $filters = $this->input('filters', []);

            if (!$reportType) {
                $this->error('Report type is required');
            }

            $data = AdminReportsModel::getReportData($reportType, $filters);
            $this->success($data);

        } catch (Exception $e) {
            $this->error('Failed to load report data');
        }
    }

    /**
     * AJAX endpoint to get chart data
     */
    public function getChartData() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $chartType = $this->input('chart_type');
            $filters = $this->input('filters', []);

            if (!$chartType) {
                $this->error('Chart type is required');
            }

            $data = AdminReportsModel::getChartData($chartType, $filters);
            $this->success($data);

        } catch (Exception $e) {
            $this->error('Failed to load chart data');
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdf($reportType, $data, $filters) {
        require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle(ucfirst($reportType) . ' Report');

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Title
        $pdf->Cell(0, 10, ucfirst($reportType) . ' Report', 0, 1, 'C');
        $pdf->Ln(5);

        // Filters info
        if (!empty($filters)) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, 'Filters Applied:', 0, 1);
            $pdf->SetFont('helvetica', '', 9);

            foreach ($filters as $key => $value) {
                if ($value) {
                    $pdf->Cell(0, 6, ucfirst(str_replace('_', ' ', $key)) . ': ' . $value, 0, 1);
                }
            }
            $pdf->Ln(5);
        }

        // Generate content based on report type
        $this->generatePdfContent($pdf, $reportType, $data);

        // Output PDF
        $filename = $reportType . '_report_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Generate Excel report
     */
    private function generateExcel($reportType, $data, $filters) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $reportType . '_report_' . date('Y-m-d_H-i-s') . '.xls"');
        header('Cache-Control: max-age=0');

        echo "<table border='1'>";
        echo "<tr><th colspan='100%'>" . ucfirst($reportType) . " Report</th></tr>";

        if (!empty($filters)) {
            echo "<tr><th colspan='100%'>Filters Applied:</th></tr>";
            foreach ($filters as $key => $value) {
                if ($value) {
                    echo "<tr><td>" . ucfirst(str_replace('_', ' ', $key)) . "</td><td>" . $value . "</td></tr>";
                }
            }
        }

        // Generate content based on report type
        $this->generateExcelContent($reportType, $data);

        echo "</table>";
        exit;
    }

    /**
     * Generate PDF content based on report type
     */
    private function generatePdfContent($pdf, $reportType, $data) {
        switch ($reportType) {
            case 'academic':
                $this->generateAcademicPdfContent($pdf, $data);
                break;
            case 'financial':
                $this->generateFinancialPdfContent($pdf, $data);
                break;
            case 'attendance':
                $this->generateAttendancePdfContent($pdf, $data);
                break;
            default:
                $pdf->Cell(0, 10, 'Report content not available', 0, 1);
        }
    }

    /**
     * Generate Excel content based on report type
     */
    private function generateExcelContent($reportType, $data) {
        switch ($reportType) {
            case 'academic':
                $this->generateAcademicExcelContent($data);
                break;
            case 'financial':
                $this->generateFinancialExcelContent($data);
                break;
            case 'attendance':
                $this->generateAttendanceExcelContent($data);
                break;
            default:
                echo "<tr><td>Report content not available</td></tr>";
        }
    }

    /**
     * Generate academic report PDF content
     */
    private function generateAcademicPdfContent($pdf, $data) {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 8, 'Student Name', 1);
        $pdf->Cell(30, 8, 'Class', 1);
        $pdf->Cell(25, 8, 'Subject', 1);
        $pdf->Cell(20, 8, 'Marks', 1);
        $pdf->Cell(20, 8, 'Grade', 1);
        $pdf->Cell(25, 8, 'Percentage', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $record) {
            $pdf->Cell(40, 6, $record['student_name'] ?? '', 1);
            $pdf->Cell(30, 6, $record['class_name'] ?? '', 1);
            $pdf->Cell(25, 6, $record['subject_name'] ?? '', 1);
            $pdf->Cell(20, 6, $record['marks'] ?? '', 1);
            $pdf->Cell(20, 6, $record['grade'] ?? '', 1);
            $pdf->Cell(25, 6, $record['percentage'] ?? '', 1);
            $pdf->Ln();
        }
    }

    /**
     * Generate financial report PDF content
     */
    private function generateFinancialPdfContent($pdf, $data) {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 8, 'Date', 1);
        $pdf->Cell(40, 8, 'Description', 1);
        $pdf->Cell(25, 8, 'Category', 1);
        $pdf->Cell(25, 8, 'Type', 1);
        $pdf->Cell(30, 8, 'Amount', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $record) {
            $pdf->Cell(30, 6, $record['date'] ?? '', 1);
            $pdf->Cell(40, 6, $record['description'] ?? '', 1);
            $pdf->Cell(25, 6, $record['category'] ?? '', 1);
            $pdf->Cell(25, 6, $record['type'] ?? '', 1);
            $pdf->Cell(30, 6, number_format($record['amount'] ?? 0, 2), 1);
            $pdf->Ln();
        }
    }

    /**
     * Generate attendance report PDF content
     */
    private function generateAttendancePdfContent($pdf, $data) {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 8, 'Student Name', 1);
        $pdf->Cell(30, 8, 'Class', 1);
        $pdf->Cell(25, 8, 'Total Days', 1);
        $pdf->Cell(25, 8, 'Present', 1);
        $pdf->Cell(25, 8, 'Absent', 1);
        $pdf->Cell(25, 8, 'Percentage', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $record) {
            $pdf->Cell(40, 6, $record['student_name'] ?? '', 1);
            $pdf->Cell(30, 6, $record['class_name'] ?? '', 1);
            $pdf->Cell(25, 6, $record['total_days'] ?? '', 1);
            $pdf->Cell(25, 6, $record['present_days'] ?? '', 1);
            $pdf->Cell(25, 6, $record['absent_days'] ?? '', 1);
            $pdf->Cell(25, 6, number_format($record['attendance_percentage'] ?? 0, 1) . '%', 1);
            $pdf->Ln();
        }
    }

    /**
     * Generate academic report Excel content
     */
    private function generateAcademicExcelContent($data) {
        if (empty($data)) return;

        echo "<tr>";
        echo "<th>Student Name</th>";
        echo "<th>Class</th>";
        echo "<th>Subject</th>";
        echo "<th>Marks</th>";
        echo "<th>Grade</th>";
        echo "<th>Percentage</th>";
        echo "</tr>";

        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . ($record['student_name'] ?? '') . "</td>";
            echo "<td>" . ($record['class_name'] ?? '') . "</td>";
            echo "<td>" . ($record['subject_name'] ?? '') . "</td>";
            echo "<td>" . ($record['marks'] ?? '') . "</td>";
            echo "<td>" . ($record['grade'] ?? '') . "</td>";
            echo "<td>" . ($record['percentage'] ?? '') . "</td>";
            echo "</tr>";
        }
    }

    /**
     * Generate financial report Excel content
     */
    private function generateFinancialExcelContent($data) {
        if (empty($data)) return;

        echo "<tr>";
        echo "<th>Date</th>";
        echo "<th>Description</th>";
        echo "<th>Category</th>";
        echo "<th>Type</th>";
        echo "<th>Amount</th>";
        echo "</tr>";

        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . ($record['date'] ?? '') . "</td>";
            echo "<td>" . ($record['description'] ?? '') . "</td>";
            echo "<td>" . ($record['category'] ?? '') . "</td>";
            echo "<td>" . ($record['type'] ?? '') . "</td>";
            echo "<td>" . number_format($record['amount'] ?? 0, 2) . "</td>";
            echo "</tr>";
        }
    }

    /**
     * Generate attendance report Excel content
     */
    private function generateAttendanceExcelContent($data) {
        if (empty($data)) return;

        echo "<tr>";
        echo "<th>Student Name</th>";
        echo "<th>Class</th>";
        echo "<th>Total Days</th>";
        echo "<th>Present</th>";
        echo "<th>Absent</th>";
        echo "<th>Percentage</th>";
        echo "</tr>";

        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . ($record['student_name'] ?? '') . "</td>";
            echo "<td>" . ($record['class_name'] ?? '') . "</td>";
            echo "<td>" . ($record['total_days'] ?? '') . "</td>";
            echo "<td>" . ($record['present_days'] ?? '') . "</td>";
            echo "<td>" . ($record['absent_days'] ?? '') . "</td>";
            echo "<td>" . number_format($record['attendance_percentage'] ?? 0, 1) . "%</td>";
            echo "</tr>";
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