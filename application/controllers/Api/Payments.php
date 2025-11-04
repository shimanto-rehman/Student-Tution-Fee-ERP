<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payments extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_fee_model');
        $this->load->model('Payment_model');

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($this->input->method() === 'options') {
            exit();
        }
    }

    /**
     * POST /api/payments/process
     * Body JSON:
     * {
     *   "student_id": 1829,
     *   "payments": [
     *     {"bill_month": 9,  "bill_year": 2025, "amount": 1000},
     *     {"bill_month": 10, "bill_year": 2025, "amount": 1200}
     *   ],
     *   "payment_method": "gateway",            // optional
     *   "external_ref": "PGW-REF-123456"       // optional
     * }
     *
     * Returns: { status, transaction_id, results: [...] }
     */
    public function process() {
        $json = $this->input->raw_input_stream ?: file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON body'
                ]));
            return;
        }

        $student_id = isset($data['student_id']) ? trim($data['student_id']) : '';
        // Normalize payments: allow single item or array under payments, or top-level single fields
        $payments   = [];
        if (isset($data['payments'])) {
            if (is_array($data['payments']) && array_values($data['payments']) === $data['payments']) {
                $payments = $data['payments'];
            } elseif (is_array($data['payments'])) {
                $payments = [$data['payments']];
            }
        } elseif (isset($data['bill_month']) || isset($data['bill_year']) || isset($data['amount'])) {
            $payments = [[
                'bill_month' => isset($data['bill_month']) ? $data['bill_month'] : null,
                'bill_year'  => isset($data['bill_year']) ? $data['bill_year'] : null,
                'amount'     => isset($data['amount']) ? $data['amount'] : null
            ]];
        }
        $payment_method = isset($data['payment_method']) ? trim($data['payment_method']) : 'api';
        $external_ref   = isset($data['external_ref']) ? trim($data['external_ref']) : null;
        // New: bank-provided transaction reference only (college generates transaction_id)
        $transaction_ref     = isset($data['transaction_ref']) ? trim($data['transaction_ref']) : null;

        if (empty($student_id) || empty($payments)) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'student_id and payments[] are required'
                ]));
            return;
        }

        // Resolve student by id or reg_no
        $student = $this->resolve_student($student_id);
        if (!$student) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Student not found'
                ]));
            return;
        }

        $batch_txn_id = $this->Payment_model->generate_transaction_id();
        $results = [];
        $all_success = true;
        $success_count = 0;

        $this->db->trans_start();

        foreach ($payments as $idx => $item) {
            $bill_month = isset($item['bill_month']) ? (int)$item['bill_month'] : null;
            $bill_year  = isset($item['bill_year']) ? (int)$item['bill_year'] : null;
            $amount     = isset($item['amount']) ? (float)$item['amount'] : null;

            // Basic validation
            if (!$bill_month || $bill_month < 1 || $bill_month > 12 || !$bill_year || $bill_year < 2020 || $bill_year > 2050 || $amount === null || $amount < 0) {
                $all_success = false;
                $results[] = [
                    'bill_month' => $bill_month,
                    'bill_year' => $bill_year,
                    'status' => 'error',
                    'message' => 'Invalid bill_month, bill_year, or amount'
                ];
                continue;
            }

            // Find the fee row for this student and month/year
            $fee = $this->db
                ->where('student_id', $student->id)
                ->where('bill_month', $bill_month)
                ->where('bill_year', $bill_year)
                ->where('status', '0')
                ->get('student_fees')
                ->row();

            if (!$fee) {
                $all_success = false;
                $results[] = [
                    'bill_month' => $bill_month,
                    'bill_year'  => $bill_year,
                    'status' => 'error',
                    'message' => 'Bill not found for student/month/year'
                ];
                continue;
            }

            // Compute expected and remaining amounts
            $expected_total = (float)$fee->base_amount + (float)$fee->late_fee;
            $already_paid   = (float)$this->Payment_model->get_total_paid_for_fee($fee->id);
            $remaining_due  = max(0, $expected_total - $already_paid);

            // If fully paid, skip gracefully
            if ($remaining_due <= 0) {
                $results[] = [
                    'bill_id' => $fee->id,
                    'bill_month' => $bill_month,
                    'bill_year' => $bill_year,
                    'status' => 'skipped',
                    'message' => 'Bill already fully paid',
                    'expected_total' => $expected_total,
                    'already_paid_before' => $already_paid,
                    'remaining_due_after' => 0
                ];
                continue;
            }

            // If amount not provided, pay remaining due by default
            if ($amount === null) {
                $amount = $remaining_due;
            }

            // Validate amount
            if ($amount <= 0) {
                $all_success = false;
                $results[] = [
                    'bill_id' => $fee->id,
                    'bill_month' => $bill_month,
                    'bill_year' => $bill_year,
                    'status' => 'error',
                    'message' => 'Amount must be greater than 0'
                ];
                continue;
            }
            if ($amount > $remaining_due) {
                $all_success = false;
                $results[] = [
                    'bill_id' => $fee->id,
                    'bill_month' => $bill_month,
                    'bill_year' => $bill_year,
                    'status' => 'error',
                    'message' => 'Amount exceeds remaining due',
                    'expected_total' => $expected_total,
                    'already_paid_before' => $already_paid,
                    'remaining_due' => $remaining_due
                ];
                continue;
            }

            // Create payment row
            $payment_data = [
                'student_fee_id'   => $fee->id,
                'amount_paid'      => $amount,
                'payment_date'     => date('Y-m-d'),
                'payment_method'   => $payment_method,
                // College-generated transaction id (batch)
                'transaction_id'   => $batch_txn_id,
                'mtb_transaction_ref' => $transaction_ref,
                'mtb_payment_status' => 'success'
            ];

            $payment_id = $this->Payment_model->create($payment_data);

            if ($payment_id) {
                // Update student_fees with bank transaction id (store transaction_ref)
                if (!empty($transaction_ref)) {
                    $this->db->where('id', $fee->id);
                    $this->db->update('student_fees', ['bank_transaction_id' => $transaction_ref]);
                }
                $results[] = [
                    'bill_id'     => $fee->id,
                    'bill_month'  => $bill_month,
                    'bill_year'   => $bill_year,
                    'expected_total' => $expected_total,
                    'already_paid_before' => $already_paid,
                    'amount_paid' => $amount,
                    'remaining_due_after' => max(0, $expected_total - ($already_paid + $amount)),
                    'status'      => 'success',
                    'payment_id'  => $payment_id
                ];
                $success_count++;
            } else {
                $all_success = false;
                $results[] = [
                    'bill_id'    => $fee->id,
                    'bill_month' => $bill_month,
                    'bill_year'  => $bill_year,
                    'status'     => 'error',
                    'message'    => 'Failed to record payment'
                ];
            }
        }

        // Commit/rollback
        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            $all_success = false;
        }

        // Always respond 200 to avoid CI status text issues; expose outcome via body status
        $overall_status = $all_success ? 'success' : ($success_count > 0 ? 'partial' : 'error');
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => $overall_status,
                // College-generated transaction id (batch)
                'transaction_id' => $batch_txn_id,
                'transaction_ref' => $transaction_ref,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'reg_no' => $student->reg_no
                ],
                'results' => $results
            ], JSON_PRETTY_PRINT));
    }

    private function resolve_student($student_id) {
        // Try by numeric ID first if numeric
        if (is_numeric($student_id)) {
            $this->db->where('id', (int)$student_id);
            $this->db->where('status', 1);
            $row = $this->db->get('student')->row();
            if ($row) return $row;
            // Fallback to reg_no if no id match
            $this->db->where('reg_no', (string)$student_id);
            $this->db->where('status', 1);
            return $this->db->get('student')->row();
        }
        // Non-numeric: treat as reg_no
        $this->db->where('reg_no', $student_id);
        $this->db->where('status', 1);
        return $this->db->get('student')->row();
    }
}


