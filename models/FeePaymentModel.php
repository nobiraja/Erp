<?php
/**
 * Fee Payment Model
 * Handles fee payment transactions and receipts
 */

class FeePaymentModel extends BaseModel {
    protected $table = 'fee_payments';
    protected $fillable = [
        'fee_id',
        'payment_date',
        'amount_paid',
        'payment_mode',
        'transaction_id',
        'cheque_number',
        'receipt_number',
        'collected_by',
        'remarks'
    ];

    /**
     * Get payment with fee and student information
     */
    public static function withDetails($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT fp.*, f.fee_type, f.amount as fee_amount, f.due_date, f.academic_year,
                    s.first_name, s.middle_name, s.last_name, s.scholar_number,
                    s.admission_number, s.father_name, s.mobile, s.village_address,
                    c.class_name, c.section, u.username as collected_by_name
             FROM {$instance->table} fp
             LEFT JOIN fees f ON fp.fee_id = f.id
             LEFT JOIN students s ON f.student_id = s.id
             LEFT JOIN classes c ON s.class_id = c.id
             LEFT JOIN users u ON fp.collected_by = u.id
             WHERE fp.id = ?",
            [$id]
        );

        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }

        return null;
    }

    /**
     * Get payments by fee
     */
    public static function getByFee($feeId) {
        $instance = new static();
        $results = $instance->db->fetchAll(
            "SELECT fp.*, u.username as collected_by_name
             FROM {$instance->table} fp
             LEFT JOIN users u ON fp.collected_by = u.id
             WHERE fp.fee_id = ?
             ORDER BY fp.payment_date DESC",
            [$feeId]
        );

        $payments = [];
        foreach ($results as $result) {
            $payment = new static($result);
            $payment->original = $result;
            $payment->exists = true;
            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * Get payments by student
     */
    public static function getByStudent($studentId, $limit = null) {
        $instance = new static();
        $query = "SELECT fp.*, f.fee_type, f.amount as fee_amount, f.due_date,
                         u.username as collected_by_name
                  FROM {$instance->table} fp
                  LEFT JOIN fees f ON fp.fee_id = f.id
                  LEFT JOIN users u ON fp.collected_by = u.id
                  WHERE f.student_id = ?
                  ORDER BY fp.payment_date DESC";

        if ($limit) {
            $query .= " LIMIT ?";
            $results = $instance->db->fetchAll($query, [$studentId, $limit]);
        } else {
            $results = $instance->db->fetchAll($query, [$studentId]);
        }

        $payments = [];
        foreach ($results as $result) {
            $payment = new static($result);
            $payment->original = $result;
            $payment->exists = true;
            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * Get payments by date range
     */
    public static function getByDateRange($startDate, $endDate, $collectedBy = null) {
        $instance = new static();
        $query = "SELECT fp.*, f.fee_type, f.amount as fee_amount,
                         s.first_name, s.middle_name, s.last_name, s.scholar_number,
                         c.class_name, c.section, u.username as collected_by_name
                  FROM {$instance->table} fp
                  LEFT JOIN fees f ON fp.fee_id = f.id
                  LEFT JOIN students s ON f.student_id = s.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN users u ON fp.collected_by = u.id
                  WHERE fp.payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($collectedBy) {
            $query .= " AND fp.collected_by = ?";
            $params[] = $collectedBy;
        }

        $query .= " ORDER BY fp.payment_date DESC, fp.created_at DESC";

        $results = $instance->db->fetchAll($query, $params);

        $payments = [];
        foreach ($results as $result) {
            $payment = new static($result);
            $payment->original = $result;
            $payment->exists = true;
            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * Get payment statistics
     */
    public static function getPaymentStats($startDate = null, $endDate = null, $collectedBy = null) {
        if (!$startDate) $startDate = date('Y-m-01');
        if (!$endDate) $endDate = date('Y-m-t');

        $instance = new static();
        $query = "SELECT
                    COUNT(*) as total_payments,
                    SUM(amount_paid) as total_amount,
                    AVG(amount_paid) as average_amount,
                    MIN(amount_paid) as min_amount,
                    MAX(amount_paid) as max_amount,
                    payment_mode,
                    COUNT(*) as mode_count
                  FROM {$instance->table}
                  WHERE payment_date BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($collectedBy) {
            $query .= " AND collected_by = ?";
            $params[] = $collectedBy;
        }

        $query .= " GROUP BY payment_mode";

        $results = $instance->db->fetchAll($query, $params);

        $stats = [
            'total_payments' => 0,
            'total_amount' => 0,
            'average_amount' => 0,
            'payment_modes' => []
        ];

        foreach ($results as $result) {
            $stats['total_payments'] += $result['mode_count'];
            $stats['total_amount'] += $result['total_amount'];
            $stats['payment_modes'][$result['payment_mode']] = [
                'count' => $result['mode_count'],
                'amount' => $result['total_amount']
            ];
        }

        if ($stats['total_payments'] > 0) {
            $stats['average_amount'] = round($stats['total_amount'] / $stats['total_payments'], 2);
        }

        return $stats;
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber() {
        $date = date('Ymd');
        $instance = new static();

        // Get the last receipt number for today
        $result = $instance->db->fetch(
            "SELECT receipt_number FROM {$instance->table}
             WHERE receipt_number LIKE ?
             ORDER BY receipt_number DESC LIMIT 1",
            ["{$date}%"]
        );

        if ($result) {
            $lastNumber = intval(substr($result['receipt_number'], 8));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Record payment and update fee status
     */
    public static function recordPayment($feeId, $paymentData, $collectedBy) {
        $instance = new static();

        // Generate receipt number
        $receiptNumber = self::generateReceiptNumber();

        // Create payment record
        $payment = new static(array_merge($paymentData, [
            'fee_id' => $feeId,
            'receipt_number' => $receiptNumber,
            'collected_by' => $collectedBy
        ]));

        if ($payment->save()) {
            // Check if fee is fully paid and update status
            $fee = FeeModel::find($feeId);
            if ($fee) {
                $totalPaid = self::getTotalPaidForFee($feeId);
                if ($totalPaid >= $fee->amount) {
                    $fee->markAsPaid();
                }
            }

            return $payment;
        }

        return false;
    }

    /**
     * Get total paid amount for a fee
     */
    public static function getTotalPaidForFee($feeId) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT SUM(amount_paid) as total_paid FROM {$instance->table} WHERE fee_id = ?",
            [$feeId]
        );

        return $result['total_paid'] ?? 0;
    }

    /**
     * Get student name
     */
    public function getStudentName() {
        if (isset($this->attributes['first_name'])) {
            $name = $this->attributes['first_name'];
            if ($this->attributes['middle_name']) {
                $name .= ' ' . $this->attributes['middle_name'];
            }
            $name .= ' ' . $this->attributes['last_name'];
            return $name;
        }
        return '';
    }

    /**
     * Get payment mode text
     */
    public function getPaymentModeText() {
        $modes = [
            'cash' => 'Cash',
            'online' => 'Online',
            'cheque' => 'Cheque',
            'upi' => 'UPI'
        ];

        return $modes[$this->payment_mode] ?? ucfirst($this->payment_mode);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus() {
        $fee = FeeModel::find($this->fee_id);
        if ($fee) {
            $totalPaid = self::getTotalPaidForFee($this->fee_id);
            if ($totalPaid >= $fee->amount) {
                return 'Fully Paid';
            } elseif ($totalPaid > 0) {
                return 'Partially Paid';
            }
        }
        return 'Paid';
    }

    /**
     * Get remaining balance for fee
     */
    public function getRemainingBalance() {
        $fee = FeeModel::find($this->fee_id);
        if ($fee) {
            $totalPaid = self::getTotalPaidForFee($this->fee_id);
            return max(0, $fee->amount - $totalPaid);
        }
        return 0;
    }
}