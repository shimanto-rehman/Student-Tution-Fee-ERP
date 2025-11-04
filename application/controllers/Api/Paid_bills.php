<?php
// application/controllers/Api/Paid_bills.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paid_bills extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_fee_model');
        $this->load->model('Payment_model');
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Enable CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($this->input->method() === 'options') {
            exit();
        }
    }
    
    /**
     * Get paid bills for a student by student ID and/or phone number
     * POST /api/paid-bills/check
     * Body: {
     *   "student_id": 123 (optional if phone provided),
     *   "phone": "01708086211" (optional if student_id provided)
     * }
     */
    public function check() {
        // Get JSON input
        $json = $this->input->raw_input_stream ?: file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // If JSON decode fails, try form data
        if (!$data) {
            $data = [
                'student_id' => $this->input->post('student_id'),
                'phone' => $this->input->post('phone')
            ];
        }
        
        // Extract and sanitize inputs
        $student_id = isset($data['student_id']) ? trim($data['student_id']) : '';
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        
        // Validate: at least one identifier is required
        if (empty($student_id) && empty($phone)) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Either student_id or phone is required'
                ]));
            return;
        }
        
        // Find student using flexible authentication
        $student = $this->find_student_flexible($student_id, $phone);
        
        if (!$student['found']) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => $student['message']
                ]));
            return;
        }
        
        $student_record = $student['student'];
        
        // Get all fees for this student
        $all_fees = $this->Student_fee_model->get_by_student($student_record->id);
        
        // Filter for PAID or PARTIAL bills
        // Note: status='0' means active, status='1' means deleted
        $paid_bills = array_filter($all_fees, function($fee) {
            return in_array($fee->payment_status, ['Paid', 'Partial']) && $fee->status == '0';
        });
        
        // Get payment details for each paid/partial bill and compute remaining due
        $formatted_bills = [];
        foreach (array_values($paid_bills) as $fee) {
            // Get all payments for this fee - using direct DB query
            $this->db->where('student_fee_id', $fee->id);
            $this->db->order_by('payment_date', 'ASC');
            $payments_query = $this->db->get('payments');
            $payments = $payments_query->result();
            
            // Format payment history
            $payment_history = [];
            $first_payment_date = null;
            $total_paid = 0.0;
            
            foreach ($payments as $payment) {
                if ($first_payment_date === null) {
                    $first_payment_date = $payment->payment_date;
                }
                
                $payment_history[] = [
                    'payment_id' => $payment->id,
                    'amount_paid' => floatval(isset($payment->amount_paid) ? $payment->amount_paid : (isset($payment->amount) ? $payment->amount : 0)),
                    'payment_date' => isset($payment->payment_date) ? $payment->payment_date : null,
                    'payment_method' => isset($payment->payment_method) ? $payment->payment_method : null,
                    'transaction_id' => isset($payment->transaction_id) ? $payment->transaction_id : null,
                    'received_by' => isset($payment->received_by) ? $payment->received_by : null
                ];
                // Count only active/successful payments if flags exist; else include
                $is_active = !isset($payment->status) || $payment->status == '1';
                $is_success = !isset($payment->mtb_payment_status) || $payment->mtb_payment_status === 'success';
                if ($is_active && $is_success) {
                    $total_paid += floatval(isset($payment->amount_paid) ? $payment->amount_paid : (isset($payment->amount) ? $payment->amount : 0));
                }
            }
            
            $expected_total = floatval($fee->base_amount) + floatval($fee->late_fee);
            $remaining_due = max(0, $expected_total - $total_paid);

            $formatted_bills[] = [
                'bill_id' => $fee->id,
                'month_year' => $fee->month_year,
                'bill_month' => $fee->bill_month,
                'bill_year' => $fee->bill_year,
                'bill_unique_id' => $fee->bill_unique_id,
                'due_date' => $fee->due_date,
                'payment_status' => $fee->payment_status,
                'amount' => [
                    'base_amount' => floatval($fee->base_amount),
                    'late_fee' => floatval($fee->late_fee),
                    'total_amount' => floatval($fee->total_amount),
                    'total_paid' => $total_paid,
                    'remaining_due' => $remaining_due,
                    'currency' => 'BDT'
                ],
                'paid_on_time' => $first_payment_date ? (strtotime($first_payment_date) <= strtotime($fee->due_date)) : false,
                'payment_history' => $payment_history
            ];
        }
        
        // Calculate totals
        $total_amount = array_sum(array_map(function($bill) {
            return $bill['amount']['total_amount'];
        }, $formatted_bills));
        $total_late_fee = array_sum(array_map(function($bill) {
            return $bill['amount']['late_fee'];
        }, $formatted_bills));
        $total_base = array_sum(array_map(function($bill) {
            return $bill['amount']['base_amount'];
        }, $formatted_bills));
        
        // Build response
        $response = [
            'status' => 'success',
            'message' => 'Paid bills retrieved successfully',
            'student' => [
                'id' => $student_record->id,
                'name' => $student_record->name,
                'reg_no' => $student_record->reg_no,
                'phone' => $student_record->phone ?: null,
                'parent_phone' => $student_record->prt_phone ?: null,
                'address' => $student_record->address
            ],
            'bills' => $formatted_bills,
            'summary' => [
                'total_bills' => count($formatted_bills),
                'total_base_amount' => $total_base,
                'total_late_fee' => $total_late_fee,
                'total_amount_paid' => $total_amount,
                'currency' => 'BDT'
            ],
            'authentication' => [
                'matched_by' => $student['matched_by']
            ]
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    /**
     * Alternative endpoint using GET method
     * GET /api/paid-bills/get?student_id=123&phone=01708086211
     * Either student_id OR phone is required (or both)
     */
    public function get() {
        // Get data from GET parameters
        $student_id = $this->input->get('student_id');
        $phone = $this->input->get('phone');
        
        // Sanitize inputs
        $student_id = $student_id ? trim($student_id) : '';
        $phone = $phone ? trim($phone) : '';
        
        // Validate: at least one identifier is required
        if (empty($student_id) && empty($phone)) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Either student_id or phone is required'
                ]));
            return;
        }
        
        // Find student using flexible authentication
        $student = $this->find_student_flexible($student_id, $phone);
        
        if (!$student['found']) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => $student['message']
                ]));
            return;
        }
        
        $student_record = $student['student'];
        
        // Get all fees for this student
        $all_fees = $this->Student_fee_model->get_by_student($student_record->id);
        
        // Filter for PAID or PARTIAL bills
        $paid_bills = array_filter($all_fees, function($fee) {
            return in_array($fee->payment_status, ['Paid', 'Partial']) && $fee->status == '0';
        });
        
        // Get payment details for each paid/partial bill and compute remaining due
        $formatted_bills = [];
        foreach (array_values($paid_bills) as $fee) {
            // Get all payments for this fee - using direct DB query
            $this->db->where('student_fee_id', $fee->id);
            $this->db->order_by('payment_date', 'ASC');
            $payments_query = $this->db->get('payments');
            $payments = $payments_query->result();
            
            // Format payment history
            $payment_history = [];
            $first_payment_date = null;
            $total_paid = 0.0;
            
            foreach ($payments as $payment) {
                if ($first_payment_date === null) {
                    $first_payment_date = $payment->payment_date;
                }
                
                $payment_history[] = [
                    'payment_id' => $payment->id,
                    'amount_paid' => floatval(isset($payment->amount_paid) ? $payment->amount_paid : (isset($payment->amount) ? $payment->amount : 0)),
                    'payment_date' => isset($payment->payment_date) ? $payment->payment_date : null,
                    'payment_method' => isset($payment->payment_method) ? $payment->payment_method : null,
                    'transaction_id' => isset($payment->transaction_id) ? $payment->transaction_id : null,
                    'received_by' => isset($payment->received_by) ? $payment->received_by : null
                ];
                $is_active = !isset($payment->status) || $payment->status == '1';
                $is_success = !isset($payment->mtb_payment_status) || $payment->mtb_payment_status === 'success';
                if ($is_active && $is_success) {
                    $total_paid += floatval(isset($payment->amount_paid) ? $payment->amount_paid : (isset($payment->amount) ? $payment->amount : 0));
                }
            }
            
            $expected_total = floatval($fee->base_amount) + floatval($fee->late_fee);
            $remaining_due = max(0, $expected_total - $total_paid);

            $formatted_bills[] = [
                'bill_id' => $fee->id,
                'month_year' => $fee->month_year,
                'bill_month' => $fee->bill_month,
                'bill_year' => $fee->bill_year,
                'bill_unique_id' => $fee->bill_unique_id,
                'due_date' => $fee->due_date,
                'payment_status' => $fee->payment_status,
                'amount' => [
                    'base_amount' => floatval($fee->base_amount),
                    'late_fee' => floatval($fee->late_fee),
                    'total_amount' => floatval($fee->total_amount),
                    'total_paid' => $total_paid,
                    'remaining_due' => $remaining_due,
                    'currency' => 'BDT'
                ],
                'paid_on_time' => $first_payment_date ? (strtotime($first_payment_date) <= strtotime($fee->due_date)) : false,
                'payment_history' => $payment_history
            ];
        }
        
        // Calculate totals
        $total_amount = array_sum(array_map(function($bill) {
            return $bill['amount']['total_amount'];
        }, $formatted_bills));
        $total_late_fee = array_sum(array_map(function($bill) {
            return $bill['amount']['late_fee'];
        }, $formatted_bills));
        $total_base = array_sum(array_map(function($bill) {
            return $bill['amount']['base_amount'];
        }, $formatted_bills));
        
        // Build response
        $response = [
            'status' => 'success',
            'message' => 'Paid bills retrieved successfully',
            'student' => [
                'id' => $student_record->id,
                'name' => $student_record->name,
                'reg_no' => $student_record->reg_no,
                'phone' => $student_record->phone ?: null,
                'parent_phone' => $student_record->prt_phone ?: null,
                'address' => $student_record->address
            ],
            'bills' => $formatted_bills,
            'summary' => [
                'total_bills' => count($formatted_bills),
                'total_base_amount' => $total_base,
                'total_late_fee' => $total_late_fee,
                'total_amount_paid' => $total_amount,
                'currency' => 'BDT'
            ],
            'authentication' => [
                'matched_by' => $student['matched_by']
            ]
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    /**
     * Helper method: Find student using flexible authentication
     * Supports: student_id only, phone only, or both
     * Uses direct database queries - NO Student_model changes needed
     * 
     * @param string $student_id - Student ID or registration number
     * @param string $phone - Phone number
     * @return array ['found' => bool, 'student' => object|null, 'message' => string, 'matched_by' => string]
     */
    private function find_student_flexible($student_id, $phone) {
        $found_students = [];
        $matched_by = [];
        
        // Strategy 1: Try to find by student_id (ID or reg_no)
        if (!empty($student_id)) {
            // Try by numeric ID first
            if (is_numeric($student_id)) {
                $this->db->where('id', $student_id);
                $this->db->where('status', 1);
                $query = $this->db->get('student');
                
                if ($query->num_rows() > 0) {
                    $found_students[] = $query->row();
                    $matched_by[] = 'student_id';
                }
            }
            
            // If not found by ID, try by registration number
            if (empty($found_students)) {
                $this->db->where('reg_no', $student_id);
                $this->db->where('status', 1);
                $query = $this->db->get('student');
                
                if ($query->num_rows() > 0) {
                    $found_students[] = $query->row();
                    $matched_by[] = 'student_id';
                }
            }
        }
        
        // Strategy 2: Try to find by phone number
        if (!empty($phone)) {
            // Clean phone number (remove spaces, dashes, etc.)
            $clean_phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Search in both phone and prt_phone fields - DIRECT DB QUERY
            $this->db->select('*');
            $this->db->from('student');
            $this->db->where('status', 1);
            $this->db->group_start();
            $this->db->where('phone', $clean_phone);
            $this->db->or_where('phone', $phone); // Try with original format too
            $this->db->or_where('prt_phone', $clean_phone);
            $this->db->or_where('prt_phone', $phone); // Try with original format too
            $this->db->group_end();
            
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $student) {
                    // Avoid duplicates if already found by ID
                    if (!$this->student_in_array($student, $found_students)) {
                        $found_students[] = $student;
                        $matched_by[] = 'phone';
                    } else {
                        // If found by both, update matched_by
                        $matched_by[] = 'phone';
                    }
                }
            }
        }
        
        // Evaluation
        if (empty($found_students)) {
            return [
                'found' => false,
                'student' => null,
                'message' => 'Student not found with provided credentials',
                'matched_by' => null
            ];
        }
        
        // If multiple students found (should be rare), we need both credentials to disambiguate
        if (count($found_students) > 1 && !empty($student_id) && !empty($phone)) {
            // Find the student that matches BOTH
            foreach ($found_students as $student) {
                $student_phone = trim($student->phone);
                $parent_phone = trim($student->prt_phone);
                $clean_phone = preg_replace('/[^0-9+]/', '', $phone);
                
                // Check if this student matches the phone
                if ($clean_phone === preg_replace('/[^0-9+]/', '', $student_phone) || 
                    $clean_phone === preg_replace('/[^0-9+]/', '', $parent_phone)) {
                    return [
                        'found' => true,
                        'student' => $student,
                        'message' => 'Student found and verified',
                        'matched_by' => 'student_id_and_phone'
                    ];
                }
            }
            
            // If we reach here, credentials don't match
            return [
                'found' => false,
                'student' => null,
                'message' => 'Multiple students found but phone does not match student_id',
                'matched_by' => null
            ];
        }
        
        // Single student found - verify phone if both provided
        $student = $found_students[0];
        
        if (!empty($student_id) && !empty($phone)) {
            $student_phone = trim($student->phone);
            $parent_phone = trim($student->prt_phone);
            $clean_phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Verify phone matches
            if ($clean_phone !== preg_replace('/[^0-9+]/', '', $student_phone) && 
                $clean_phone !== preg_replace('/[^0-9+]/', '', $parent_phone)) {
                return [
                    'found' => false,
                    'student' => null,
                    'message' => 'Phone number does not match student record',
                    'matched_by' => null
                ];
            }
            
            $matched_by_str = 'student_id_and_phone';
        } else {
            $matched_by_str = implode('_and_', array_unique($matched_by));
        }
        
        return [
            'found' => true,
            'student' => $student,
            'message' => 'Student found',
            'matched_by' => $matched_by_str
        ];
    }
    
    /**
     * Helper: Check if student is in array
     */
    private function student_in_array($student, $array) {
        foreach ($array as $item) {
            if ($item->id === $student->id) {
                return true;
            }
        }
        return false;
    }
}

// http://localhost/pioneer-dental/api/paid-bills/get?student_id=1829&phone=01711781724
// http://localhost/pioneer-dental/api/paid-bills/get?student_id=1829