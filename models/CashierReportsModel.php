<?php
/**
 * Cashier Reports Model
 * Handles data retrieval for financial reports
 */

class CashierReportsModel extends BaseModel {

    /**
     * Get comprehensive collection summary
     */
    public static function getCollectionSummary($startDate, $endDate, $userId = null) {
        $instance = new static();

        $query = "SELECT
                  DATE(fp.payment_date) as date,
                  COUNT(fp.id) as payments_count,
                  SUM(fp.amount_paid) as total_amount,
                  fp.payment_mode,
                  COUNT(fp.id) as mode_count,
                  GROUP_CONCAT(DISTINCT f.fee_type) as fee_types
                 FROM fee_payments fp
                 LEFT JOIN fees f ON fp.fee_id = f.id
                 WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($userId) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $userId;
        }

        $query .= " GROUP BY DATE(fp.payment_date), fp.payment_mode
                   ORDER BY fp.payment_date DESC, fp.payment_mode";

        $results = $instance->db->fetchAll($query, $params);

        $summary = [
            'daily_totals' => [],
            'payment_modes' => [],
            'fee_types' => [],
            'grand_total' => 0,
            'total_payments' => 0,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ];

        foreach ($results as $result) {
            $date = $result['date'];

            if (!isset($summary['daily_totals'][$date])) {
                $summary['daily_totals'][$date] = [
                    'date' => $date,
                    'payments' => 0,
                    'amount' => 0,
                    'modes' => [],
                    'fee_types' => []
                ];
            }

            $summary['daily_totals'][$date]['payments'] += $result['payments_count'];
            $summary['daily_totals'][$date]['amount'] += $result['total_amount'];
            $summary['daily_totals'][$date]['modes'][$result['payment_mode']] = [
                'count' => $result['mode_count'],
                'amount' => $result['total_amount']
            ];

            if ($result['fee_types']) {
                $feeTypes = explode(',', $result['fee_types']);
                $summary['daily_totals'][$date]['fee_types'] = array_unique(array_merge(
                    $summary['daily_totals'][$date]['fee_types'],
                    $feeTypes
                ));
            }

            if (!isset($summary['payment_modes'][$result['payment_mode']])) {
                $summary['payment_modes'][$result['payment_mode']] = [
                    'count' => 0,
                    'amount' => 0
                ];
            }
            $summary['payment_modes'][$result['payment_mode']]['count'] += $result['mode_count'];
            $summary['payment_modes'][$result['payment_mode']]['amount'] += $result['total_amount'];

            $summary['grand_total'] += $result['total_amount'];
            $summary['total_payments'] += $result['payments_count'];
        }

        $summary['daily_totals'] = array_values($summary['daily_totals']);

        return $summary;
    }

    /**
     * Get detailed collection data with student information
     */
    public static function getDetailedCollections($startDate, $endDate, $userId = null, $limit = null) {
        $instance = new static();

        $query = "SELECT fp.*, f.fee_type, f.amount as fee_amount, f.due_date,
                         s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         s.admission_number, c.class_name, c.section,
                         u.username as collected_by_name
                 FROM fee_payments fp
                 LEFT JOIN fees f ON fp.fee_id = f.id
                 LEFT JOIN students s ON f.student_id = s.id
                 LEFT JOIN classes c ON s.class_id = c.id
                 LEFT JOIN users u ON fp.collected_by = u.id
                 WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($userId) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $userId;
        }

        $query .= " ORDER BY fp.payment_date DESC, fp.created_at DESC";

        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
        }

        return $instance->db->fetchAll($query, $params);
    }

    /**
     * Get expense analysis data
     */
    public static function getExpenseAnalysis($startDate, $endDate, $category = null, $minAmount = null, $maxAmount = null) {
        $instance = new static();

        $query = "SELECT
                  DATE(e.payment_date) as date,
                  e.expense_category,
                  COUNT(e.id) as expense_count,
                  SUM(e.amount) as total_amount,
                  AVG(e.amount) as avg_amount,
                  GROUP_CONCAT(DISTINCT e.payment_mode) as payment_modes
                 FROM expenses e
                 WHERE e.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($category) {
            $query .= " AND e.expense_category = ?";
            $params[] = $category;
        }

        if ($minAmount !== null && $minAmount !== '') {
            $query .= " AND e.amount >= ?";
            $params[] = $minAmount;
        }

        if ($maxAmount !== null && $maxAmount !== '') {
            $query .= " AND e.amount <= ?";
            $params[] = $maxAmount;
        }

        $query .= " GROUP BY DATE(e.payment_date), e.expense_category
                   ORDER BY e.payment_date DESC, e.expense_category";

        $results = $instance->db->fetchAll($query, $params);

        $analysis = [
            'daily_expenses' => [],
            'category_totals' => [],
            'grand_total' => 0,
            'total_expenses' => 0
        ];

        foreach ($results as $result) {
            $date = $result['date'];
            $category = $result['expense_category'];

            if (!isset($analysis['daily_expenses'][$date])) {
                $analysis['daily_expenses'][$date] = [
                    'date' => $date,
                    'total_amount' => 0,
                    'categories' => []
                ];
            }

            $analysis['daily_expenses'][$date]['categories'][$category] = [
                'count' => $result['expense_count'],
                'amount' => $result['total_amount'],
                'avg_amount' => round($result['avg_amount'], 2)
            ];

            $analysis['daily_expenses'][$date]['total_amount'] += $result['total_amount'];

            if (!isset($analysis['category_totals'][$category])) {
                $analysis['category_totals'][$category] = [
                    'count' => 0,
                    'amount' => 0,
                    'avg_amount' => 0
                ];
            }

            $analysis['category_totals'][$category]['count'] += $result['expense_count'];
            $analysis['category_totals'][$category]['amount'] += $result['total_amount'];

            $analysis['grand_total'] += $result['total_amount'];
            $analysis['total_expenses'] += $result['expense_count'];
        }

        // Calculate averages for categories
        foreach ($analysis['category_totals'] as $cat => $data) {
            if ($data['count'] > 0) {
                $analysis['category_totals'][$cat]['avg_amount'] = round($data['amount'] / $data['count'], 2);
            }
        }

        $analysis['daily_expenses'] = array_values($analysis['daily_expenses']);

        return $analysis;
    }

    /**
     * Get category-wise spending trend data
     */
    public static function getCategoryTrendData($startDate, $endDate, $category = null, $minAmount = null, $maxAmount = null) {
        $instance = new static();

        $query = "SELECT
                  DATE(e.payment_date) as date,
                  e.expense_category,
                  SUM(e.amount) as total_amount
                 FROM expenses e
                 WHERE e.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($category) {
            $query .= " AND e.expense_category = ?";
            $params[] = $category;
        }

        if ($minAmount !== null && $minAmount !== '') {
            $query .= " AND e.amount >= ?";
            $params[] = $minAmount;
        }

        if ($maxAmount !== null && $maxAmount !== '') {
            $query .= " AND e.amount <= ?";
            $params[] = $maxAmount;
        }

        $query .= " GROUP BY DATE(e.payment_date), e.expense_category
                   ORDER BY e.payment_date ASC, e.expense_category";

        $results = $instance->db->fetchAll($query, $params);

        $categories = ExpenseModel::getCategories();
        $dates = [];
        $categoryData = [];

        // Initialize category data
        foreach ($categories as $key => $name) {
            $categoryData[$key] = [
                'label' => $name,
                'data' => [],
                'borderColor' => self::getCategoryColor($key),
                'backgroundColor' => self::getCategoryColor($key, 0.1),
                'fill' => false
            ];
        }

        // Group by date
        $dateGroups = [];
        foreach ($results as $result) {
            $date = $result['date'];
            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }
            if (!isset($dateGroups[$date])) {
                $dateGroups[$date] = [];
            }
            $dateGroups[$date][$result['expense_category']] = $result['total_amount'];
        }

        sort($dates);

        // Fill data for each category
        foreach ($dates as $date) {
            foreach ($categoryData as $catKey => &$catInfo) {
                $amount = $dateGroups[$date][$catKey] ?? 0;
                $catInfo['data'][] = $amount;
            }
        }

        return [
            'labels' => $dates,
            'datasets' => array_values($categoryData)
        ];
    }

    /**
     * Get color for category chart
     */
    private static function getCategoryColor($category, $alpha = 1) {
        $colors = [
            'diesel' => [220, 53, 69, $alpha], // red
            'staff' => [255, 193, 7, $alpha], // yellow
            'bus' => [40, 167, 69, $alpha], // green
            'maintenance' => [0, 123, 255, $alpha], // blue
            'misc' => [108, 117, 125, $alpha], // gray
            'custom' => [111, 66, 193, $alpha] // purple
        ];

        $color = $colors[$category] ?? [108, 117, 125, $alpha]; // default gray

        if ($alpha < 1) {
            return "rgba({$color[0]}, {$color[1]}, {$color[2]}, {$color[3]})";
        } else {
            return "rgb({$color[0]}, {$color[1]}, {$color[2]})";
        }
    }

    /**
     * Get financial analytics data
     */
    public static function getFinancialAnalytics($startDate, $endDate, $userId = null) {
        $instance = new static();

        // Revenue metrics
        $revenueQuery = "SELECT
                         COUNT(fp.id) as total_collections,
                         SUM(fp.amount_paid) as total_revenue,
                         AVG(fp.amount_paid) as avg_collection,
                         MIN(fp.amount_paid) as min_collection,
                         MAX(fp.amount_paid) as max_collection,
                         COUNT(DISTINCT DATE(fp.payment_date)) as active_days
                        FROM fee_payments fp
                        WHERE fp.payment_date BETWEEN ? AND ?";

        $revenueParams = [$startDate, $endDate];
        if ($userId) {
            $revenueQuery .= " AND fp.collected_by = ?";
            $revenueParams[] = $userId;
        }

        $revenue = $instance->db->fetch($revenueQuery, $revenueParams);

        // Expense metrics
        $expenseQuery = "SELECT
                         COUNT(e.id) as total_expenses,
                         SUM(e.amount) as total_expenses_amount,
                         AVG(e.amount) as avg_expense,
                         MIN(e.amount) as min_expense,
                         MAX(e.amount) as max_expense
                        FROM expenses e
                        WHERE e.payment_date BETWEEN ? AND ?";

        $expense = $instance->db->fetch($expenseQuery, [$startDate, $endDate]);

        // Payment mode distribution
        $modeQuery = "SELECT
                      fp.payment_mode,
                      COUNT(fp.id) as transaction_count,
                      SUM(fp.amount_paid) as total_amount,
                      AVG(fp.amount_paid) as avg_amount
                     FROM fee_payments fp
                     WHERE fp.payment_date BETWEEN ? AND ?";

        $modeParams = [$startDate, $endDate];
        if ($userId) {
            $modeQuery .= " AND fp.collected_by = ?";
            $modeParams[] = $userId;
        }

        $modeQuery .= " GROUP BY fp.payment_mode ORDER BY total_amount DESC";
        $paymentModes = $instance->db->fetchAll($modeQuery, $modeParams);

        // Monthly trends (last 12 months)
        $trendQuery = "SELECT
                       DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
                       COUNT(fp.id) as collections,
                       SUM(fp.amount_paid) as revenue,
                       (SELECT SUM(e.amount) FROM expenses e WHERE DATE_FORMAT(e.payment_date, '%Y-%m') = DATE_FORMAT(fp.payment_date, '%Y-%m')) as expenses
                      FROM fee_payments fp
                      WHERE fp.payment_date >= DATE_SUB(?, INTERVAL 12 MONTH)";

        $trendParams = [$startDate];
        if ($userId) {
            $trendQuery .= " AND fp.collected_by = ?";
            $trendParams[] = $userId;
        }

        $trendQuery .= " GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m') ORDER BY month";
        $trends = $instance->db->fetchAll($trendQuery, $trendParams);

        // Outstanding fees analysis
        $outstandingQuery = "SELECT
                            COUNT(f.id) as total_outstanding,
                            SUM(f.amount) as outstanding_amount,
                            AVG(f.amount) as avg_outstanding,
                            COUNT(CASE WHEN DATEDIFF(CURDATE(), f.due_date) > 30 THEN 1 END) as overdue_30_plus
                           FROM fees f
                           WHERE f.is_paid = 0";

        $outstanding = $instance->db->fetch($outstandingQuery);

        // Calculate derived metrics
        $netProfit = ($revenue['total_revenue'] ?? 0) - ($expense['total_expenses_amount'] ?? 0);
        $profitMargin = ($revenue['total_revenue'] ?? 0) > 0 ?
            round(($netProfit / $revenue['total_revenue']) * 100, 2) : 0;

        $collectionRate = 0;
        if (($revenue['total_collections'] ?? 0) > 0 && ($revenue['active_days'] ?? 0) > 0) {
            $collectionRate = round($revenue['total_collections'] / $revenue['active_days'], 2);
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => ceil((strtotime($endDate) - strtotime($startDate)) / (60*60*24)) + 1
            ],
            'revenue' => [
                'total_collections' => $revenue['total_collections'] ?? 0,
                'total_revenue' => $revenue['total_revenue'] ?? 0,
                'avg_collection' => round($revenue['avg_collection'] ?? 0, 2),
                'min_collection' => $revenue['min_collection'] ?? 0,
                'max_collection' => $revenue['max_collection'] ?? 0,
                'active_days' => $revenue['active_days'] ?? 0,
                'daily_avg' => $revenue['active_days'] > 0 ?
                    round(($revenue['total_revenue'] ?? 0) / $revenue['active_days'], 2) : 0
            ],
            'expenses' => [
                'total_expenses' => $expense['total_expenses'] ?? 0,
                'total_amount' => $expense['total_expenses_amount'] ?? 0,
                'avg_expense' => round($expense['avg_expense'] ?? 0, 2),
                'min_expense' => $expense['min_expense'] ?? 0,
                'max_expense' => $expense['max_expense'] ?? 0
            ],
            'profitability' => [
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
                'status' => $netProfit >= 0 ? 'profit' : 'loss'
            ],
            'payment_modes' => $paymentModes,
            'trends' => $trends,
            'outstanding' => $outstanding,
            'ratios' => [
                'expense_to_revenue' => ($revenue['total_revenue'] ?? 0) > 0 ?
                    round(($expense['total_expenses_amount'] ?? 0) / $revenue['total_revenue'] * 100, 2) : 0,
                'collection_rate' => $collectionRate
            ]
        ];
    }

    /**
     * Get revenue vs expense comparison
     */
    public static function getRevenueExpenseComparison($startDate, $endDate, $userId = null) {
        $instance = new static();

        // Daily revenue
        $revenueQuery = "SELECT
                         DATE(fp.payment_date) as date,
                         SUM(fp.amount_paid) as revenue
                        FROM fee_payments fp
                        WHERE fp.payment_date BETWEEN ? AND ?";

        $revenueParams = [$startDate, $endDate];
        if ($userId) {
            $revenueQuery .= " AND fp.collected_by = ?";
            $revenueParams[] = $userId;
        }

        $revenueQuery .= " GROUP BY DATE(fp.payment_date) ORDER BY date";
        $revenueData = $instance->db->fetchAll($revenueQuery, $revenueParams);

        // Daily expenses
        $expenseQuery = "SELECT
                         DATE(e.payment_date) as date,
                         SUM(e.amount) as expenses
                        FROM expenses e
                        WHERE e.payment_date BETWEEN ? AND ?
                        GROUP BY DATE(e.payment_date) ORDER BY date";

        $expenseData = $instance->db->fetchAll($expenseQuery, [$startDate, $endDate]);

        // Merge data by date
        $comparison = [];
        $allDates = array_unique(array_merge(
            array_column($revenueData, 'date'),
            array_column($expenseData, 'date')
        ));
        sort($allDates);

        $revenueMap = array_column($revenueData, 'revenue', 'date');
        $expenseMap = array_column($expenseData, 'expenses', 'date');

        foreach ($allDates as $date) {
            $revenue = $revenueMap[$date] ?? 0;
            $expenses = $expenseMap[$date] ?? 0;
            $net = $revenue - $expenses;

            $comparison[] = [
                'date' => $date,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net' => $net,
                'status' => $net >= 0 ? 'surplus' : 'deficit'
            ];
        }

        return [
            'comparison' => $comparison,
            'summary' => [
                'total_revenue' => array_sum(array_column($comparison, 'revenue')),
                'total_expenses' => array_sum(array_column($comparison, 'expenses')),
                'net_total' => array_sum(array_column($comparison, 'net')),
                'surplus_days' => count(array_filter($comparison, fn($d) => $d['status'] === 'surplus')),
                'deficit_days' => count(array_filter($comparison, fn($d) => $d['status'] === 'deficit'))
            ]
        ];
    }

    /**
     * Get payment mode analysis
     */
    public static function getPaymentModeAnalysis($startDate, $endDate, $userId = null) {
        $instance = new static();

        $query = "SELECT
                  fp.payment_mode,
                  COUNT(fp.id) as transaction_count,
                  SUM(fp.amount_paid) as total_amount,
                  AVG(fp.amount_paid) as avg_amount,
                  MIN(fp.amount_paid) as min_amount,
                  MAX(fp.amount_paid) as max_amount,
                  COUNT(DISTINCT DATE(fp.payment_date)) as active_days
                 FROM fee_payments fp
                 WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($userId) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $userId;
        }

        $query .= " GROUP BY fp.payment_mode ORDER BY total_amount DESC";

        $results = $instance->db->fetchAll($query, $params);

        $analysis = [
            'modes' => $results,
            'summary' => [
                'total_modes' => count($results),
                'total_transactions' => array_sum(array_column($results, 'transaction_count')),
                'total_amount' => array_sum(array_column($results, 'total_amount')),
                'most_used' => !empty($results) ? $results[0]['payment_mode'] : null,
                'least_used' => !empty($results) ? end($results)['payment_mode'] : null
            ]
        ];

        // Calculate percentages
        if ($analysis['summary']['total_amount'] > 0) {
            foreach ($analysis['modes'] as &$mode) {
                $mode['percentage'] = round(($mode['total_amount'] / $analysis['summary']['total_amount']) * 100, 2);
                $mode['transaction_percentage'] = round(($mode['transaction_count'] / $analysis['summary']['total_transactions']) * 100, 2);
            }
        }

        return $analysis;
    }

    /**
     * Get class-wise collection analysis
     */
    public static function getClassWiseAnalysis($startDate, $endDate, $userId = null) {
        $instance = new static();

        $query = "SELECT
                  c.class_name,
                  c.section,
                  COUNT(fp.id) as collections,
                  SUM(fp.amount_paid) as total_amount,
                  AVG(fp.amount_paid) as avg_collection,
                  COUNT(DISTINCT s.id) as students_count,
                  COUNT(DISTINCT f.id) as fees_count
                 FROM fee_payments fp
                 LEFT JOIN fees f ON fp.fee_id = f.id
                 LEFT JOIN students s ON f.student_id = s.id
                 LEFT JOIN classes c ON s.class_id = c.id
                 WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($userId) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $userId;
        }

        $query .= " GROUP BY c.id, c.class_name, c.section
                   ORDER BY c.class_name, c.section";

        $results = $instance->db->fetchAll($query, $params);

        $analysis = [
            'classes' => $results,
            'summary' => [
                'total_classes' => count($results),
                'total_collections' => array_sum(array_column($results, 'collections')),
                'total_amount' => array_sum(array_column($results, 'total_amount')),
                'total_students' => array_sum(array_column($results, 'students_count'))
            ]
        ];

        // Calculate per student averages
        foreach ($analysis['classes'] as &$class) {
            $class['per_student_avg'] = $class['students_count'] > 0 ?
                round($class['total_amount'] / $class['students_count'], 2) : 0;
        }

        return $analysis;
    }

    /**
     * Get fee type analysis
     */
    public static function getFeeTypeAnalysis($startDate, $endDate, $userId = null) {
        $instance = new static();

        $query = "SELECT
                  f.fee_type,
                  COUNT(fp.id) as collections,
                  SUM(fp.amount_paid) as total_amount,
                  AVG(fp.amount_paid) as avg_amount,
                  COUNT(DISTINCT f.student_id) as students_count,
                  COUNT(DISTINCT f.id) as fees_count
                 FROM fee_payments fp
                 LEFT JOIN fees f ON fp.fee_id = f.id
                 WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($userId) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $userId;
        }

        $query .= " GROUP BY f.fee_type ORDER BY total_amount DESC";

        $results = $instance->db->fetchAll($query, $params);

        return [
            'fee_types' => $results,
            'summary' => [
                'total_types' => count($results),
                'total_collections' => array_sum(array_column($results, 'collections')),
                'total_amount' => array_sum(array_column($results, 'total_amount'))
            ]
        ];
    }
}