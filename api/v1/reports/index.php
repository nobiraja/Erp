<?php
/**
 * Reports API Endpoints
 * Handles analytics and export functionality
 */

require_once '../../../controllers/ApiController.php';

class ReportsApiController extends ApiController {
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? 'list';

        switch ($this->requestMethod) {
            case 'GET':
                $this->handleGet($action);
                break;
            case 'POST':
                $this->handlePost($action);
                break;
            default:
                $this->methodNotAllowed();
        }
    }

    /**
     * Handle GET requests
     */
    private function handleGet($action) {
        switch ($action) {
            case 'list':
                $this->getAvailableReports();
                break;
            case 'generate':
                $this->generateReport();
                break;
            case 'export':
                $this->exportReport();
                break;
            case 'analytics':
                $this->getAnalytics();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Handle POST requests
     */
    private function handlePost($action) {
        switch ($action) {
            case 'generate':
                $this->generateReport();
                break;
            case 'export':
                $this->exportReport();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Get available reports list
     */
    private function getAvailableReports() {
        $this->requireAuth();

        $reports = [
            [
                'id' => 'student_list',
                'name' => 'Student List Report',
                'description' => 'Complete list of students with details',
                'category' => 'academic',
                'parameters' => ['class_id', 'section', 'academic_year'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'teacher_list',
                'name' => 'Teacher List Report',
                'description' => 'Complete list of teachers with assignments',
                'category' => 'academic',
                'parameters' => ['department', 'designation'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'attendance_report',
                'name' => 'Attendance Report',
                'description' => 'Student attendance analytics',
                'category' => 'academic',
                'parameters' => ['class_id', 'month', 'year', 'student_id'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'exam_results',
                'name' => 'Exam Results Report',
                'description' => 'Examination results and analytics',
                'category' => 'academic',
                'parameters' => ['exam_id', 'class_id', 'student_id'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'fee_collection',
                'name' => 'Fee Collection Report',
                'description' => 'Fee payment and outstanding amounts',
                'category' => 'financial',
                'parameters' => ['start_date', 'end_date', 'class_id', 'academic_year'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'financial_summary',
                'name' => 'Financial Summary',
                'description' => 'Overall financial statistics',
                'category' => 'financial',
                'parameters' => ['academic_year', 'start_date', 'end_date'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'class_performance',
                'name' => 'Class Performance Report',
                'description' => 'Academic performance by class',
                'category' => 'academic',
                'parameters' => ['class_id', 'academic_year', 'exam_type'],
                'formats' => ['json', 'csv', 'pdf']
            ],
            [
                'id' => 'enrollment_trends',
                'name' => 'Enrollment Trends',
                'description' => 'Student enrollment statistics over time',
                'category' => 'analytics',
                'parameters' => ['start_year', 'end_year'],
                'formats' => ['json', 'csv', 'pdf']
            ]
        ];

        $this->successResponse(['reports' => $reports]);
    }

    /**
     * Generate report
     */
    private function generateReport() {
        $this->requireAuth();

        $reportId = $this->requestData['report_id'] ?? null;
        $format = $this->requestData['format'] ?? 'json';
        $parameters = $this->requestData['parameters'] ?? [];

        if (!$reportId) {
            $this->errorResponse('Report ID required', 400);
        }

        // Check access permissions
        $this->checkReportAccess($reportId);

        $reportData = $this->generateReportData($reportId, $parameters);

        if ($format === 'json') {
            $this->successResponse($reportData);
        } else {
            $this->exportReportData($reportData, $reportId, $format);
        }
    }

    /**
     * Export report
     */
    private function exportReport() {
        $this->requireAuth();

        $reportId = $this->requestData['report_id'] ?? null;
        $format = $this->requestData['format'] ?? 'csv';
        $parameters = $this->requestData['parameters'] ?? [];

        if (!$reportId) {
            $this->errorResponse('Report ID required', 400);
        }

        // Check access permissions
        $this->checkReportAccess($reportId);

        $reportData = $this->generateReportData($reportId, $parameters);
        $this->exportReportData($reportData, $reportId, $format);
    }

    /**
     * Get analytics dashboard data
     */
    private function getAnalytics() {
        $this->requireAuth();

        $type = $this->requestData['type'] ?? 'dashboard';

        switch ($type) {
            case 'dashboard':
                $this->getDashboardAnalytics();
                break;
            case 'academic':
                $this->getAcademicAnalytics();
                break;
            case 'financial':
                $this->getFinancialAnalytics();
                break;
            default:
                $this->errorResponse('Invalid analytics type', 400);
        }
    }

    /**
     * Generate report data based on report ID
     */
    private function generateReportData($reportId, $parameters) {
        switch ($reportId) {
            case 'student_list':
                return $this->generateStudentListReport($parameters);
            case 'teacher_list':
                return $this->generateTeacherListReport($parameters);
            case 'attendance_report':
                return $this->generateAttendanceReport($parameters);
            case 'exam_results':
                return $this->generateExamResultsReport($parameters);
            case 'fee_collection':
                return $this->generateFeeCollectionReport($parameters);
            case 'financial_summary':
                return $this->generateFinancialSummaryReport($parameters);
            case 'class_performance':
                return $this->generateClassPerformanceReport($parameters);
            case 'enrollment_trends':
                return $this->generateEnrollmentTrendsReport($parameters);
            default:
                $this->errorResponse('Invalid report ID', 400);
        }
    }

    /**
     * Generate student list report
     */
    private function generateStudentListReport($params) {
        $query = "SELECT s.*, c.class_name, c.section, c.academic_year,
                         f.father_name, f.mother_name
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN (
                      SELECT student_id, GROUP_CONCAT(fee_type) as fee_types,
                             SUM(amount) as total_fees, SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as paid_fees
                      FROM fees GROUP BY student_id
                  ) f ON s.id = f.student_id
                  WHERE s.is_active = 1";

        $queryParams = [];

        if (!empty($params['class_id'])) {
            $query .= " AND s.class_id = ?";
            $queryParams[] = $params['class_id'];
        }

        if (!empty($params['section'])) {
            $query .= " AND s.section = ?";
            $queryParams[] = $params['section'];
        }

        if (!empty($params['academic_year'])) {
            $query .= " AND c.academic_year = ?";
            $queryParams[] = $params['academic_year'];
        }

        $query .= " ORDER BY c.class_name, c.section, s.first_name, s.last_name";

        $students = $this->db->fetchAll($query, $queryParams);

        return [
            'report_type' => 'student_list',
            'generated_at' => date('Y-m-d H:i:s'),
            'parameters' => $params,
            'total_records' => count($students),
            'data' => $students
        ];
    }

    /**
     * Generate attendance report
     */
    private function generateAttendanceReport($params) {
        $month = $params['month'] ?? date('m');
        $year = $params['year'] ?? date('Y');

        $query = "SELECT s.id, s.first_name, s.last_name, s.scholar_number,
                         c.class_name, c.section,
                         COUNT(a.id) as total_days,
                         SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                         ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_percentage
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN attendance a ON s.id = a.student_id
                      AND MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?
                  WHERE s.is_active = 1";

        $queryParams = [$month, $year];

        if (!empty($params['class_id'])) {
            $query .= " AND s.class_id = ?";
            $queryParams[] = $params['class_id'];
        }

        if (!empty($params['student_id'])) {
            $query .= " AND s.id = ?";
            $queryParams[] = $params['student_id'];
        }

        $query .= " GROUP BY s.id, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section
                   ORDER BY c.class_name, c.section, s.first_name, s.last_name";

        $attendance = $this->db->fetchAll($query, $queryParams);

        return [
            'report_type' => 'attendance_report',
            'generated_at' => date('Y-m-d H:i:s'),
            'parameters' => array_merge($params, ['month' => $month, 'year' => $year]),
            'total_records' => count($attendance),
            'data' => $attendance
        ];
    }

    /**
     * Generate fee collection report
     */
    private function generateFeeCollectionReport($params) {
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');

        $query = "SELECT s.id, s.first_name, s.last_name, s.scholar_number,
                         c.class_name, c.section,
                         SUM(f.amount) as total_fees,
                         SUM(fp.amount_paid) as amount_paid,
                         SUM(f.amount) - COALESCE(SUM(fp.amount_paid), 0) as outstanding_amount,
                         GROUP_CONCAT(DISTINCT fp.receipt_number) as receipts
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN fees f ON s.id = f.student_id
                  LEFT JOIN fee_payments fp ON f.id = fp.fee_id AND fp.payment_date BETWEEN ? AND ?
                  WHERE s.is_active = 1";

        $queryParams = [$startDate, $endDate];

        if (!empty($params['class_id'])) {
            $query .= " AND s.class_id = ?";
            $queryParams[] = $params['class_id'];
        }

        if (!empty($params['academic_year'])) {
            $query .= " AND f.academic_year = ?";
            $queryParams[] = $params['academic_year'];
        }

        $query .= " GROUP BY s.id, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section
                   HAVING total_fees > 0
                   ORDER BY c.class_name, c.section, s.first_name, s.last_name";

        $fees = $this->db->fetchAll($query, $queryParams);

        return [
            'report_type' => 'fee_collection',
            'generated_at' => date('Y-m-d H:i:s'),
            'parameters' => array_merge($params, ['start_date' => $startDate, 'end_date' => $endDate]),
            'total_records' => count($fees),
            'data' => $fees
        ];
    }

    /**
     * Get dashboard analytics
     */
    private function getDashboardAnalytics() {
        $analytics = [
            'students' => [
                'total' => $this->db->fetch("SELECT COUNT(*) as count FROM students WHERE is_active = 1")['count'],
                'by_class' => $this->db->fetchAll("SELECT c.class_name, c.section, COUNT(s.id) as count
                                                  FROM classes c
                                                  LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
                                                  GROUP BY c.id, c.class_name, c.section
                                                  ORDER BY c.class_name, c.section")
            ],
            'teachers' => [
                'total' => $this->db->fetch("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1")['count'],
                'by_department' => $this->db->fetchAll("SELECT department, COUNT(*) as count
                                                       FROM teachers
                                                       WHERE is_active = 1 AND department IS NOT NULL
                                                       GROUP BY department")
            ],
            'attendance' => [
                'today' => $this->db->fetch("SELECT
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
                    FROM attendance WHERE attendance_date = CURDATE()") ?: ['total_sessions' => 0, 'present_count' => 0, 'attendance_rate' => 0]
            ],
            'fees' => [
                'total_collected' => $this->db->fetch("SELECT SUM(amount_paid) as total FROM fee_payments
                                                      WHERE MONTH(payment_date) = MONTH(CURDATE())
                                                      AND YEAR(payment_date) = YEAR(CURDATE())")['total'] ?? 0,
                'outstanding' => $this->db->fetch("SELECT SUM(amount) as total FROM fees WHERE is_paid = 0")['total'] ?? 0
            ]
        ];

        $this->successResponse([
            'analytics_type' => 'dashboard',
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $analytics
        ]);
    }

    /**
     * Get academic analytics
     */
    private function getAcademicAnalytics() {
        $analytics = [
            'exam_performance' => $this->db->fetchAll("SELECT e.exam_name, e.exam_type,
                AVG(er.percentage) as avg_percentage,
                COUNT(er.id) as total_results,
                SUM(CASE WHEN er.percentage >= 40 THEN 1 ELSE 0 END) as passed_count
                FROM exams e
                LEFT JOIN exam_results er ON e.id = er.exam_id
                WHERE e.is_active = 1
                GROUP BY e.id, e.exam_name, e.exam_type
                ORDER BY e.start_date DESC LIMIT 10"),
            'class_performance' => $this->db->fetchAll("SELECT c.class_name, c.section,
                COUNT(DISTINCT s.id) as total_students,
                AVG(CASE WHEN er.percentage THEN er.percentage END) as avg_performance
                FROM classes c
                LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
                LEFT JOIN exam_results er ON s.id = er.student_id
                GROUP BY c.id, c.class_name, c.section
                ORDER BY c.class_name, c.section")
        ];

        $this->successResponse([
            'analytics_type' => 'academic',
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $analytics
        ]);
    }

    /**
     * Get financial analytics
     */
    private function getFinancialAnalytics() {
        $currentYear = date('Y');
        $analytics = [
            'monthly_collection' => $this->db->fetchAll("SELECT
                MONTH(payment_date) as month,
                YEAR(payment_date) as year,
                SUM(amount_paid) as total_collected,
                COUNT(id) as payment_count
                FROM fee_payments
                WHERE YEAR(payment_date) = ?
                GROUP BY YEAR(payment_date), MONTH(payment_date)
                ORDER BY month", [$currentYear]),
            'fee_types' => $this->db->fetchAll("SELECT
                fee_type,
                SUM(amount) as total_amount,
                SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as collected_amount,
                SUM(CASE WHEN is_paid = 0 THEN amount ELSE 0 END) as outstanding_amount
                FROM fees
                GROUP BY fee_type
                ORDER BY total_amount DESC"),
            'payment_methods' => $this->db->fetchAll("SELECT
                payment_mode,
                SUM(amount_paid) as total_amount,
                COUNT(id) as transaction_count
                FROM fee_payments
                WHERE YEAR(payment_date) = ?
                GROUP BY payment_mode
                ORDER BY total_amount DESC", [$currentYear])
        ];

        $this->successResponse([
            'analytics_type' => 'financial',
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $analytics
        ]);
    }

    /**
     * Export report data
     */
    private function exportReportData($reportData, $reportId, $format) {
        $filename = $reportId . '_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->exportAsCSV($reportData, $filename);
                break;
            case 'pdf':
                $this->exportAsPDF($reportData, $filename);
                break;
            default:
                $this->errorResponse('Unsupported export format', 400);
        }
    }

    /**
     * Export as CSV
     */
    private function exportAsCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        if (isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
            // Write headers
            fputcsv($output, array_keys($data['data'][0]));

            // Write data
            foreach ($data['data'] as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export as PDF (simplified - would need TCPDF library)
     */
    private function exportAsPDF($data, $filename) {
        // This would require TCPDF or similar library
        // For now, return JSON with note about PDF export
        $this->errorResponse('PDF export not implemented yet. Use CSV or JSON format.', 501);
    }

    /**
     * Check report access permissions
     */
    private function checkReportAccess($reportId) {
        $userRole = $this->user['role_name'];

        // Define role-based access for reports
        $reportPermissions = [
            'admin' => ['*'], // All reports
            'teacher' => ['student_list', 'attendance_report', 'exam_results', 'class_performance'],
            'cashier' => ['fee_collection', 'financial_summary'],
            'student' => [], // No report access
            'parent' => [] // No report access
        ];

        $allowedReports = $reportPermissions[$userRole] ?? [];

        if (!in_array('*', $allowedReports) && !in_array($reportId, $allowedReports)) {
            $this->errorResponse('Access denied to this report', 403);
        }
    }

    // Placeholder methods for other reports - implement as needed
    private function generateTeacherListReport($params) { return ['data' => []]; }
    private function generateExamResultsReport($params) { return ['data' => []]; }
    private function generateFinancialSummaryReport($params) { return ['data' => []]; }
    private function generateClassPerformanceReport($params) { return ['data' => []]; }
    private function generateEnrollmentTrendsReport($params) { return ['data' => []]; }
}

// Initialize and handle request
$controller = new ReportsApiController();
$controller->handleRequest();