<?php
// application/controllers/api/Mtb_gateway.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mtb_gateway extends CI_Controller {
    
    private $api_key = 'YOUR_MTB_API_KEY'; // Set your API key
    private $merchant_id = 'PIONEER_DENTAL_001'; // Your merchant ID
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_fee_model');
        $this->load->model('Payment_model');
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Enable CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
    
    /**
     * Authenticate API request
     */
    private function authenticate() {
        $headers = $this->input->request_headers();
        $api_key = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
        
        // In production, validate against database
        if ($api_key !== $this->api_key) {
            $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized. Invalid API key.'
                ]));
            exit;
        }
        
        return true;
    }
    
    /**
     * Get all bills - MTB can fetch all pending bills
     * GET /api/mtb/bills
     * Query params: student_id, reg_no, bill_month, bill_year, payment_status, limit, offset
     */
    public function get_bills() {
        $this->authenticate();
        
        $filters = [
            'student_id' => $this->input->get('student_id'),
            'reg_no' => $this->input->get('reg_no'),
            'bill_month' => $this->input->get('bill_month'),
            'bill_year' => $this->input->get('bill_year'),
            'payment_status' => $this->input->get('payment_status') ?? 'Unpaid',
            'limit' => $this->input->get('limit') ?? 100,
            'offset' => $this->input->get('offset') ?? 0
        ];
        
        $bills = $this->Student_fee_model->get_bills_for_mtb($filters);
        
        // Format response
        $formatted_bills = array_map(function($bill) {
            return [
                'bill_unique_id' => $bill->bill_unique_id,
                'student' => [
                    'id' => $bill->student_id,
                    'name' => $bill->name,
                    'reg_no' => $bill->reg_no,
                    'phone' => $bill->phone,
                    'parent_phone' => $bill->prt_phone,
                    'address' => $bill->address
                ],
                'bill_details' => [
                    'month' => $bill->bill_month,
                    'year' => $bill->bill_year,
                    'month_year' => $bill->month_year,
                    'fee_type' => $bill->fee_name,
                    'due_date' => $bill->due_date
                ],
                'amount' => [
                    'base_amount' => (float)$bill->base_amount,
                    'late_fee' => (float)$bill->late_fee,
                    'total_amount' => (float)$bill->total_amount,
                    'currency' => 'BDT'
                ],
                'payment_status' => $bill->payment_status,
                'bank_transaction_id' => $bill->bank_transaction_id
            ];
        }, $bills);
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'count' => count($formatted_bills),
                'data' => $formatted_bills
            ]));
    }
    
    /**
     * Get single bill by unique ID
     * GET /api/mtb/bill/{bill_unique_id}
     */
    public function get_bill($bill_unique_id = null) {
        $this->authenticate();
        
        if (!$bill_unique_id) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Bill unique ID is required'
                ]));
            return;
        }
        
        $bill = $this->Student_fee_model->get_bill_by_unique_id($bill_unique_id);
        
        if (!$bill) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Bill not found'
                ]));
            return;
        }
        
        $formatted_bill = [
            'bill_unique_id' => $bill->bill_unique_id,
            'student' => [
                'id' => $bill->student_id,
                'name' => $bill->name,
                'reg_no' => $bill->reg_no,
                'phone' => $bill->phone,
                'parent_phone' => $bill->prt_phone,
                'address' => $bill->address
            ],
            'bill_details' => [
                'month' => $bill->bill_month,
                'year' => $bill->bill_year,
                'month_year' => $bill->month_year,
                'fee_type' => $bill->fee_name,
                'due_date' => $bill->due_date,
                'created' => $bill->created
            ],
            'amount' => [
                'base_amount' => (float)$bill->base_amount,
                'late_fee' => (float)$bill->late_fee,
                'total_amount' => (float)$bill->total_amount,
                'currency' => 'BDT'
            ],
            'payment_status' => $bill->payment_status,
            'paid_date' => $bill->paid_date,
            'bank_transaction_id' => $bill->bank_transaction_id,
            'comments' => $bill->comments
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $formatted_bill
            ]));
    }
    
    /**
     * Payment callback from MTB
     * POST /api/mtb/payment-callback
     * Body: {
     *   "bill_unique_id": 202501000535,
     *   "bank_transaction_id": "MTB-TXN-123456",
     *   "amount_paid": 10000.00,
     *   "payment_status": "success",
     *   "payment_date": "2025-10-30",
     *   "payment_method": "Card",
     *   "remarks": "Payment successful"
     * }
     */
    public function payment_callback() {
        $this->authenticate();
        
        $json = $this->input->raw_input_stream;
        $data = json_decode($json, true);
        
        // Validate required fields
        $required = ['bill_unique_id', 'bank_transaction_id', 'amount_paid', 'payment_status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => "Missing required field: $field"
                    ]));
                return;
            }
        }
        
        // Get the bill
        $bill = $this->Student_fee_model->get_bill_by_unique_id($data['bill_unique_id']);
        
        if (!$bill) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Bill not found'
                ]));
            return;
        }
        
        // Check if already paid
        if ($bill->payment_status === 'Paid') {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Bill already paid',
                    'transaction_id' => $bill->bank_transaction_id
                ]));
            return;
        }
        
        // Validate amount
        if ((float)$data['amount_paid'] < (float)$bill->total_amount) {
            $payment_status = 'Partial';
        } else {
            $payment_status = 'Paid';
        }
        
        // Update student_fees table
        $update_result = $this->Student_fee_model->update_payment_from_mtb(
            $data['bill_unique_id'],
            $data['bank_transaction_id'],
            $payment_status,
            $data['remarks'] ?? 'Payment via MTB Gateway'
        );
        
        // Create payment record in payments table
        $payment_data = [
            'student_fee_id' => $bill->id,
            'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
            'amount_paid' => $data['amount_paid'],
            'payment_mode' => 'online',
            'payment_method' => 'MTB Gateway - ' . ($data['payment_method'] ?? 'Card'),
            'transaction_id' => $data['bank_transaction_id'],
            'mtb_transaction_ref' => $data['bank_transaction_id'],
            'mtb_payment_status' => $data['payment_status'] === 'success' ? 'success' : 'failed',
            'mtb_response' => json_encode($data),
            'remarks' => $data['remarks'] ?? 'Payment via MTB Gateway'
        ];
        
        $payment_id = $this->Payment_model->create($payment_data);
        
        if ($update_result && $payment_id) {
            // Log the activity
            $this->Payment_model->log_activity(
                $bill->id,
                'MTB Payment Callback',
                json_encode($data)
            );
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Payment recorded successfully',
                    'data' => [
                        'bill_unique_id' => $data['bill_unique_id'],
                        'payment_id' => $payment_id,
                        'payment_status' => $payment_status,
                        'amount_paid' => $data['amount_paid'],
                        'bank_transaction_id' => $data['bank_transaction_id']
                    ]
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Failed to record payment'
                ]));
        }
    }
    
    /**
     * Get payment statistics
     * GET /api/mtb/statistics
     */
    public function get_statistics() {
        $this->authenticate();
        
        $month = $this->input->get('month') ?? date('m');
        $year = $this->input->get('year') ?? date('Y');
        
        // Total bills for the month
        $total_bills = $this->db->where('bill_month', $month)
                                ->where('bill_year', $year)
                                ->where('status', '1')
                                ->count_all_results('student_fees');
        
        // Paid bills
        $paid_bills = $this->db->where('bill_month', $month)
                               ->where('bill_year', $year)
                               ->where('payment_status', 'Paid')
                               ->where('status', '1')
                               ->count_all_results('student_fees');
        
        // Unpaid bills
        $unpaid_bills = $this->db->where('bill_month', $month)
                                 ->where('bill_year', $year)
                                 ->where('payment_status', 'Unpaid')
                                 ->where('status', '1')
                                 ->count_all_results('student_fees');
        
        // Total amount collected
        $this->db->select_sum('total_amount');
        $this->db->where('bill_month', $month);
        $this->db->where('bill_year', $year);
        $this->db->where('payment_status', 'Paid');
        $this->db->where('status', '1');
        $collected = $this->db->get('student_fees')->row()->total_amount ?? 0;
        
        // Outstanding amount
        $this->db->select_sum('total_amount');
        $this->db->where('bill_month', $month);
        $this->db->where('bill_year', $year);
        $this->db->where('payment_status', 'Unpaid');
        $this->db->where('status', '1');
        $outstanding = $this->db->get('student_fees')->row()->total_amount ?? 0;
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'data' => [
                    'month' => (int)$month,
                    'year' => (int)$year,
                    'total_bills' => $total_bills,
                    'paid_bills' => $paid_bills,
                    'unpaid_bills' => $unpaid_bills,
                    'amount_collected' => (float)$collected,
                    'amount_outstanding' => (float)$outstanding,
                    'currency' => 'BDT'
                ]
            ]));
    }
}