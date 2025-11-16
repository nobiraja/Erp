<?php
/**
 * Cashier Reports Controller
 * Handles comprehensive financial reporting for cashier module
 */

class CashierReportsController extends BaseController {

    /**
     * Display main reports dashboard
     */
    public function index() {
        try {
            $userId = $this->getCurrentUserId();

            // Get quick stats for the last 30 days
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-30 days'));

            $quickStats = $this->getQuickStats($startDate, $endDate, $userId);

            $data = [
                'title' => 'Financial Reports',
                'quickStats' => $quickStats,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            echo $this->view('cashier.reports.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading reports dashboard: ' . $e->getMessage());
            echo $this->view('cashier.reports.index', [
                'title' => 'Financial Reports',
                'quickStats' => [],
                'startDate' => date('Y-m-d', strtotime('-30 days')),
                'endDate' => date('Y-m-d')
            ]);
        }
    }

    /**
     * Collection summary report
     */
    public function collectionSummary() {
        try {
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $userId = $this->getCurrentUserId();

            $summary = CashierReportsModel::getCollectionSummary($startDate, $endDate, $userId);
            $chartData = [
                'labels' => array_column($summary['daily_totals'], 'date'),
                'data' => array_column($summary['daily_totals'], 'amount')
            ];

            $data = [
                'title' => 'Collection Summary Report',
                'summary' => $summary,
                'chartData' => $chartData,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            echo $this->view('cashier.reports.collection_summary', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading collection summary: ' . $e->getMessage());
            $this->redirect('/cashier/reports');
        }
    }

    /**
     * Expense reports
     */
    public function expenseReports() {
        try {
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $category = $this->input('category');
            $minAmount = $this->input('min_amount');
            $maxAmount = $this->input('max_amount');

            $analysis = CashierReportsModel::getExpenseAnalysis($startDate, $endDate, $category, $minAmount, $maxAmount);
            $expenses = ExpenseModel::getByDateRange($startDate, $endDate, $category, $minAmount, $maxAmount);
            $chartData = [
                'labels' => array_column($analysis['daily_expenses'], 'date'),
                'data' => array_column($analysis['daily_expenses'], 'total_amount')
            ];
            $categoryTrendData = CashierReportsModel::getCategoryTrendData($startDate, $endDate, $category, $minAmount, $maxAmount);

            $data = [
                'title' => 'Expense Reports',
                'expenses' => $expenses,
                'stats' => $analysis['category_totals'],
                'chartData' => $chartData,
                'categoryTrendData' => $categoryTrendData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'category' => $category,
                'minAmount' => $minAmount,
                'maxAmount' => $maxAmount
            ];

            echo $this->view('cashier.reports.expenses', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading expense reports: ' . $e->getMessage());
            $this->redirect('/cashier/reports');
        }
    }

    /**
     * Financial analytics
     */
    public function analytics() {
        try {
            $startDate = $this->input('start_date', date('Y-m-01'));
            $endDate = $this->input('end_date', date('Y-m-t'));
            $userId = $this->getCurrentUserId();

            $analytics = CashierReportsModel::getFinancialAnalytics($startDate, $endDate, $userId);

            $data = [
                'title' => 'Financial Analytics',
                'analytics' => $analytics,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            echo $this->view('cashier.reports.analytics', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading analytics: ' . $e->getMessage());
            $this->redirect('/cashier/reports');
        }
    }

    /**
     * Export report to PDF
     */
    public function exportPdf() {
        try {
            $reportType = $this->input('type');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $userId = $this->getCurrentUserId();

            require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('School Management System');
            $pdf->SetTitle(ucfirst(str_replace('_', ' ', $reportType)) . ' Report');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();

            // Generate content based on report type
            $this->generatePdfContent($pdf, $reportType, $startDate, $endDate, $userId);

            $filename = $reportType . '_report_' . date('Y-m-d') . '.pdf';
            $pdf->Output($filename, 'D');

        } catch (Exception $e) {
            $this->flash('error', 'PDF export failed: ' . $e->getMessage());
            $this->redirect('/cashier/reports');
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel() {
        try {
            $reportType = $this->input('type');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $userId = $this->getCurrentUserId();

            // For now, use CSV export since PhpSpreadsheet is not installed
            $this->exportToCSV($reportType, $startDate, $endDate, $userId);

        } catch (Exception $e) {
            $this->flash('error', 'Excel export failed: ' . $e->getMessage());
            $this->redirect('/cashier/reports');
        }
    }

    /**
     * AJAX: Get collection data
     */
    public function ajaxGetCollectionData() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $userId = $this->getCurrentUserId();

            // Get collection summary and extract chart data
            $summary = CashierReportsModel::getCollectionSummary($startDate, $endDate, $userId);
            $data = [
                'labels' => array_column($summary['daily_totals'], 'date'),
                'data' => array_column($summary['daily_totals'], 'amount')
            ];

            $this->json(['success' => true, 'data' => $data]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Get expense data
     */
    public function ajaxGetExpenseData() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            // Get expense analysis and extract chart data
            $analysis = CashierReportsModel::getExpenseAnalysis($startDate, $endDate);
            $data = [
                'labels' => array_column($analysis['daily_expenses'], 'date'),
                'data' => array_column($analysis['daily_expenses'], 'total_amount')
            ];

            $this->json(['success' => true, 'data' => $data]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get quick statistics for dashboard
     */
    private function getQuickStats($startDate, $endDate, $userId) {
        // Collection stats
        $collectionQuery = "SELECT
                            COUNT(*) as total_payments,
                            SUM(amount_paid) as total_amount,
                            AVG(amount_paid) as avg_payment
                           FROM fee_payments
                           WHERE collected_by = ? AND payment_date BETWEEN ? AND ?";

        $collectionStats = $this->db->fetch($collectionQuery, [$userId, $startDate, $endDate]);

        // Expense stats
        $expenseQuery = "SELECT
                         COUNT(*) as total_expenses,
                         SUM(amount) as total_expenses_amount,
                         AVG(amount) as avg_expense
                        FROM expenses
                        WHERE payment_date BETWEEN ? AND ?";

        $expenseStats = $this->db->fetch($expenseQuery, [$startDate, $endDate]);

        // Outstanding fees
        $outstandingQuery = "SELECT COUNT(*) as outstanding_count, SUM(amount) as outstanding_amount
                            FROM fees WHERE is_paid = 0";
        $outstandingStats = $this->db->fetch($outstandingQuery);

        return [
            'collection' => [
                'total_payments' => $collectionStats['total_payments'] ?? 0,
                'total_amount' => $collectionStats['total_amount'] ?? 0,
                'avg_payment' => round($collectionStats['avg_payment'] ?? 0, 2)
            ],
            'expenses' => [
                'total_expenses' => $expenseStats['total_expenses'] ?? 0,
                'total_amount' => $expenseStats['total_expenses_amount'] ?? 0,
                'avg_expense' => round($expenseStats['avg_expense'] ?? 0, 2)
            ],
            'outstanding' => [
                'count' => $outstandingStats['outstanding_count'] ?? 0,
                'amount' => $outstandingStats['outstanding_amount'] ?? 0
            ],
            'net_position' => ($collectionStats['total_amount'] ?? 0) - ($expenseStats['total_expenses_amount'] ?? 0)
        ];
    }


    /**
     * Generate PDF content
     */
    private function generatePdfContent($pdf, $reportType, $startDate, $endDate, $userId) {
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, strtoupper(str_replace('_', ' ', $reportType)) . ' REPORT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Period: ' . date('d-m-Y', strtotime($startDate)) . ' to ' . date('d-m-Y', strtotime($endDate)), 0, 1, 'C');
        $pdf->Ln(10);

        switch ($reportType) {
            case 'collection_summary':
                $this->generateCollectionSummaryPdf($pdf, $startDate, $endDate, $userId);
                break;
            case 'expenses':
                $this->generateExpensesPdf($pdf, $startDate, $endDate);
                break;
            case 'analytics':
                $this->generateAnalyticsPdf($pdf, $startDate, $endDate, $userId);
                break;
        }
    }

    /**
     * Generate collection summary PDF
     */
    private function generateCollectionSummaryPdf($pdf, $startDate, $endDate, $userId) {
        $summary = CashierReportsModel::getCollectionSummary($startDate, $endDate, $userId);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Collection Summary', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Total Payments:', 0, 0);
        $pdf->Cell(0, 6, $summary['total_payments'], 0, 1);

        $pdf->Cell(50, 6, 'Total Amount:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($summary['grand_total'], 2), 0, 1);

        $pdf->Ln(5);

        // Payment modes
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Payment Modes:', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($summary['payment_modes'] as $mode => $amount) {
            $pdf->Cell(30, 5, ucfirst($mode) . ':', 0, 0);
            $pdf->Cell(0, 5, '₹' . number_format($amount, 2), 0, 1);
        }
    }

    /**
     * Generate expenses PDF
     */
    private function generateExpensesPdf($pdf, $startDate, $endDate) {
        $expenses = ExpenseModel::getByDateRange($startDate, $endDate);
        $stats = ExpenseModel::getExpenseStats($startDate, $endDate);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Expense Summary', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Total Expenses:', 0, 0);
        $pdf->Cell(0, 6, $stats['total_expenses'], 0, 1);

        $pdf->Cell(50, 6, 'Total Amount:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($stats['total_amount'], 2), 0, 1);

        $pdf->Ln(5);

        // Category breakdown
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Category Breakdown:', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($stats['categories'] as $category => $data) {
            $pdf->Cell(40, 5, ucfirst($category) . ':', 0, 0);
            $pdf->Cell(20, 5, $data['count'] . ' items', 0, 0);
            $pdf->Cell(0, 5, '₹' . number_format($data['amount'], 2), 0, 1);
        }
    }

    /**
     * Generate analytics PDF
     */
    private function generateAnalyticsPdf($pdf, $startDate, $endDate, $userId) {
        $analytics = CashierReportsModel::getFinancialAnalytics($startDate, $endDate, $userId);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Financial Analytics', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(50, 6, 'Total Revenue:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($analytics['revenue']['total_revenue'] ?? 0, 2), 0, 1);

        $pdf->Cell(50, 6, 'Total Expenses:', 0, 0);
        $pdf->Cell(0, 6, '₹' . number_format($analytics['expenses']['total_expenses'] ?? 0, 2), 0, 1);

        $pdf->Cell(50, 6, 'Net Profit:', 0, 0);
        $netProfit = $analytics['net_profit'];
        $pdf->Cell(0, 6, '₹' . number_format($netProfit, 2) . ' (' . ($netProfit >= 0 ? 'Profit' : 'Loss') . ')', 0, 1);
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($reportType, $startDate, $endDate, $userId) {
        $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        switch ($reportType) {
            case 'collection_summary':
                $this->exportCollectionSummaryCSV($output, $startDate, $endDate, $userId);
                break;
            case 'expenses':
                $this->exportExpensesCSV($output, $startDate, $endDate);
                break;
            case 'analytics':
                $this->exportAnalyticsCSV($output, $startDate, $endDate, $userId);
                break;
        }

        fclose($output);
        exit;
    }

    /**
     * Export collection summary to CSV
     */
    private function exportCollectionSummaryCSV($output, $startDate, $endDate, $userId) {
        fputcsv($output, ['Date', 'Payments', 'Amount', 'Payment Modes']);

        $summary = CashierReportsModel::getCollectionSummary($startDate, $endDate, $userId);

        foreach ($summary['daily_totals'] as $day) {
            $modes = [];
            foreach ($day['modes'] as $mode => $amount) {
                $modes[] = ucfirst($mode) . ': ₹' . number_format($amount, 2);
            }

            fputcsv($output, [
                $day['date'],
                $day['payments'],
                number_format($day['amount'], 2),
                implode(', ', $modes)
            ]);
        }
    }

    /**
     * Export expenses to CSV
     */
    private function exportExpensesCSV($output, $startDate, $endDate) {
        fputcsv($output, ['Date', 'Category', 'Amount', 'Reason', 'Payment Mode']);

        $expenses = ExpenseModel::getByDateRange($startDate, $endDate);

        foreach ($expenses as $expense) {
            fputcsv($output, [
                $expense->payment_date,
                $expense->expense_category,
                number_format($expense->amount, 2),
                $expense->reason,
                $expense->payment_mode
            ]);
        }
    }

    /**
     * Export analytics to CSV
     */
    private function exportAnalyticsCSV($output, $startDate, $endDate, $userId) {
        $analytics = CashierReportsModel::getFinancialAnalytics($startDate, $endDate, $userId);

        fputcsv($output, ['Metric', 'Value']);

        fputcsv($output, ['Total Revenue', number_format($analytics['revenue']['total_revenue'] ?? 0, 2)]);
        fputcsv($output, ['Total Collections', $analytics['revenue']['total_collections'] ?? 0]);
        fputcsv($output, ['Average Collection', number_format($analytics['revenue']['avg_collection'] ?? 0, 2)]);
        fputcsv($output, ['Total Expenses', number_format($analytics['expenses']['total_expenses'] ?? 0, 2)]);
        fputcsv($output, ['Net Profit', number_format($analytics['net_profit'], 2)]);

        fputcsv($output, ['', '']);
        fputcsv($output, ['Payment Modes', '']);

        foreach ($analytics['payment_modes'] as $mode) {
            fputcsv($output, [ucfirst($mode['payment_mode']), number_format($mode['amount'], 2)]);
        }
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 1;
    }
}