<?php
/**
 * Fees API Endpoints
 * Handles fee collection, payments, and financial reports
 */

require_once '../../../controllers/ApiController.php';

class FeesApiController extends ApiController {
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
                $this->getFees();
                break;
            case 'payments':
                $this->getPayments();
                break;
            case 'outstanding':
                $this->getOutstandingFees();
                break;
            case 'reports':
                $this->getFeesReports();
                break;
            case 'student':
                $this->getStudentFees();
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
            case 'collect':
                $this->collectPayment();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Get fees list with filtering
     */
    private function getFees() {
        $this->requireAuth();
        $this->requireRole(['admin', 'cashier']);

        $studentId = $this->requestData['student_id'] ?? null;
        $classId = $this->requestData['class_id'] ?? null;
        $academicYear = $this->requestData['academic_year'] ?? null;
        $feeType = $this->requestData['fee_type'] ?? null;
        $isPaid = $this->requestData['is_paid'] ?? null;

        $query = "SELECT f.*, s.first_name, s.last_name, s.scholar_number,
                         c.class_name, c.section
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE 1=1";

        $params = [];

        if ($studentId) {
            $query .= " AND f.student_id = ?";
            $params[] = $studentId;
        }

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($academicYear) {
            $query .= " AND f.academic_year = ?";
            $params[] = $academicYear;
        }

        if ($feeType) {
            $query .= " AND f.fee_type = ?";
            $params[] = $feeType;
        }

        if ($isPaid !== null) {
            $query .= " AND f.is_paid = ?";
            $params[] = $isPaid;
        }

        $query .= " ORDER BY f.due_date DESC, s.first_name, s.last_name";

        $result = $this->getPaginatedResults('fees', '', $params, 'f.due_date DESC');

        $this->successResponse($result);
    }

    /**
     * Get payments list
     */
    private function getPayments() {
        $this->requireAuth();
        $this->requireRole(['admin', 'cashier']);

        $studentId = $this->requestData['student_id'] ?? null;
        $startDate = $this->requestData['start_date'] ?? null;
        $endDate = $this->requestData['end_date'] ?? null;
        $paymentMode = $this->requestData['payment_mode'] ?? null;

        $query = "SELECT fp.*, f.fee_type, f.amount as fee_amount,
                         s.first_name, s.last_name, s.scholar_number,
                         c.class_name, c.section,
                         u.username as collected_by_name
                  FROM fee_payments fp
                  LEFT JOIN fees f ON fp.fee_id = f.id
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN users u ON fp.collected_by = u.id
                  WHERE 1=1";

        $params = [];

        if ($studentId) {
            $query .= " AND f.student_id = ?";
            $params[] = $studentId;
        }

        if ($startDate) {
            $query .= " AND fp.payment_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND fp.payment_date <= ?";
            $params[] = $endDate;
        }

        if ($paymentMode) {
            $query .= " AND fp.payment_mode = ?";
            $params[] = $paymentMode;
        }

        $query .= " ORDER BY fp.payment_date DESC, fp.created_at DESC";

        $result = $this->getPaginatedResults('fee_payments', '', $params, 'fp.payment_date DESC');

        $this->successResponse($result);
    }

    /**
     * Get outstanding fees
     */
    private function getOutstandingFees() {
        $this->requireAuth();
        $this->requireRole(['admin', 'cashier']);

        $classId = $this->requestData['class_id'] ?? null;
        $academicYear = $this->requestData['academic_year'] ?? date('Y');

        $query = "SELECT s.id, s.first_name, s.last_name, s.scholar_number,
                         c.class_name, c.section,
                         SUM(f.amount) as total_fees,
                         SUM(CASE WHEN f.is_paid = 1 THEN f.amount ELSE 0 END) as paid_amount,
                         SUM(CASE WHEN f.is_paid = 0 THEN f.amount ELSE 0 END) as outstanding_amount,
                         COUNT(CASE WHEN f.is_paid = 0 THEN 1 END) as outstanding_count
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN fees f ON s.id = f.student_id";

        $params = [];

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($academicYear) {
            $query .= " AND f.academic_year = ?";
            $params[] = $academicYear;
        }

        $query .= " WHERE s.is_active = 1
                   GROUP BY s.id, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section
                   HAVING outstanding_amount > 0
                   ORDER BY outstanding_amount DESC, s.first_name, s.last_name";

        $results = $this->db->fetchAll($query, $params);

        // Add pagination manually since it's a complex query
        $page = (int) ($this->requestData['page'] ?? 1);
        $limit = (int) ($this->requestData['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        $total = count($results);
        $paginatedResults = array_slice($results, $offset, $limit);

        $result = [
            'data' => $paginatedResults,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];

        $this->successResponse($result);
    }

    /**
     * Get fees reports
     */
    private function getFeesReports() {
        $this->requireAuth();
        $this->requireRole(['admin', 'cashier']);

        $reportType = $this->requestData['type'] ?? 'summary';
        $startDate = $this->requestData['start_date'] ?? date('Y-m-01');
        $endDate = $this->requestData['end_date'] ?? date('Y-m-t');
        $academicYear = $this->requestData['academic_year'] ?? date('Y');

        switch ($reportType) {
            case 'summary':
                $this->getFeesSummaryReport($startDate, $endDate, $academicYear);
                break;
            case 'collection':
                $this->getCollectionReport($startDate, $endDate);
                break;
            case 'outstanding':
                $this->getOutstandingReport($academicYear);
                break;
            default:
                $this->errorResponse('Invalid report type', 400);
        }
    }

    /**
     * Get student fees
     */
    private function getStudentFees() {
        $this->requireAuth();

        $studentId = $this->requestData['student_id'] ?? null;

        if (!$studentId) {
            $this->errorResponse('Student ID required', 400);
        }

        // Check access permissions
        $this->checkStudentFeesAccess($studentId);

        $fees = $this->db->fetchAll(
            "SELECT f.*, fp.payment_date, fp.amount_paid, fp.payment_mode, fp.receipt_number
             FROM fees f
             LEFT JOIN fee_payments fp ON f.id = fp.fee_id
             WHERE f.student_id = ?
             ORDER BY f.due_date DESC",
            [$studentId]
        );

        // Calculate totals
        $totalFees = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($fees as &$fee) {
            $totalFees += $fee['amount'];
            if ($fee['is_paid']) {
                $totalPaid += $fee['amount'];
            } else {
                $totalOutstanding += $fee['amount'];
            }
        }

        $result = [
            'student_id' => $studentId,
            'fees' => $fees,
            'summary' => [
                'total_fees' => $totalFees,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'payment_percentage' => $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 2) : 0
            ]
        ];

        $this->successResponse($result);
    }

    /**
     * Collect payment
     */
    private function collectPayment() {
        $this->requireAuth();
        $this->requireRole(['admin', 'cashier']);

        $this->validateRequired(['fee_id', 'amount_paid', 'payment_mode']);

        $feeId = $this->requestData['fee_id'];
        $amountPaid = $this->requestData['amount_paid'];
        $paymentMode = $this->requestData['payment_mode'];

        // Validate fee exists and is not already paid
        $fee = $this->db->fetch("SELECT * FROM fees WHERE id = ?", [$feeId]);
        if (!$fee) {
            $this->errorResponse('Fee not found', 404);
        }

        if ($fee['is_paid']) {
            $this->errorResponse('Fee is already paid', 400);
        }

        // Generate receipt number
        $receiptNumber = $this->generateReceiptNumber();

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Insert payment record
            $paymentData = [
                'fee_id' => $feeId,
                'payment_date' => $this->requestData['payment_date'] ?? date('Y-m-d'),
                'amount_paid' => $amountPaid,
                'payment_mode' => $paymentMode,
                'transaction_id' => $this->requestData['transaction_id'] ?? null,
                'cheque_number' => $this->requestData['cheque_number'] ?? null,
                'receipt_number' => $receiptNumber,
                'collected_by' => $this->user['user_id'],
                'remarks' => $this->requestData['remarks'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $paymentId = $this->db->insert('fee_payments', $paymentData);

            // Update fee status if fully paid
            if ($amountPaid >= $fee['amount']) {
                $this->db->update('fees', ['is_paid' => 1], 'id = ?', [$feeId]);
            }

            $this->db->commit();

            $this->successResponse([
                'payment_id' => $paymentId,
                'receipt_number' => $receiptNumber,
                'amount_paid' => $amountPaid,
                'payment_mode' => $paymentMode
            ], 'Payment collected successfully', 201);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->errorResponse('Failed to process payment', 500);
        }
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber() {
        $date = date('Ymd');
        $count = $this->db->fetch(
            "SELECT COUNT(*) as count FROM fee_payments WHERE DATE(created_at) = CURDATE()"
        )['count'] ?? 0;

        return 'RCP' . $date . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get fees summary report
     */
    private function getFeesSummaryReport($startDate, $endDate, $academicYear) {
        $summary = $this->db->fetch(
            "SELECT
                SUM(f.amount) as total_fees,
                SUM(CASE WHEN f.is_paid = 1 THEN f.amount ELSE 0 END) as total_collected,
                SUM(CASE WHEN f.is_paid = 0 THEN f.amount ELSE 0 END) as total_outstanding,
                COUNT(DISTINCT f.student_id) as total_students,
                COUNT(CASE WHEN f.is_paid = 1 THEN 1 END) as paid_fees_count,
                COUNT(CASE WHEN f.is_paid = 0 THEN 1 END) as outstanding_fees_count
             FROM fees f
             WHERE f.academic_year = ?",
            [$academicYear]
        );

        $this->successResponse([
            'period' => ['start_date' => $startDate, 'end_date' => $endDate, 'academic_year' => $academicYear],
            'summary' => $summary
        ]);
    }

    /**
     * Get collection report
     */
    private function getCollectionReport($startDate, $endDate) {
        $collections = $this->db->fetchAll(
            "SELECT DATE(fp.payment_date) as date,
                    SUM(fp.amount_paid) as total_collected,
                    COUNT(fp.id) as payments_count,
                    fp.payment_mode
             FROM fee_payments fp
             WHERE fp.payment_date BETWEEN ? AND ?
             GROUP BY DATE(fp.payment_date), fp.payment_mode
             ORDER BY date DESC",
            [$startDate, $endDate]
        );

        $this->successResponse([
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'collections' => $collections
        ]);
    }

    /**
     * Get outstanding report
     */
    private function getOutstandingReport($academicYear) {
        $outstanding = $this->db->fetchAll(
            "SELECT c.class_name, c.section,
                    COUNT(DISTINCT s.id) as students_count,
                    SUM(f.amount) as total_outstanding
             FROM fees f
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE f.is_paid = 0 AND f.academic_year = ? AND s.is_active = 1
             GROUP BY c.class_name, c.section
             ORDER BY c.class_name, c.section",
            [$academicYear]
        );

        $this->successResponse([
            'academic_year' => $academicYear,
            'outstanding_by_class' => $outstanding
        ]);
    }

    /**
     * Check access to student fees
     */
    private function checkStudentFeesAccess($studentId) {
        $userRole = $this->user['role_name'];

        switch ($userRole) {
            case 'admin':
            case 'cashier':
                return; // Full access

            case 'student':
                // Students can only view their own fees
                $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$studentId]);
                if (!$student || $student['user_id'] != $this->user['user_id']) {
                    $this->errorResponse('Access denied', 403);
                }
                break;

            case 'parent':
                // Parents can view their children's fees
                // This would require parent-child relationship
                $this->errorResponse('Parent access not implemented', 403);
                break;

            default:
                $this->errorResponse('Access denied', 403);
        }
    }
}

// Initialize and handle request
$controller = new FeesApiController();
$controller->handleRequest();