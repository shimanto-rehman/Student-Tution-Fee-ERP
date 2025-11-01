<?php
// application/controllers/Api/Due_bills.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Due_bills extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_fee_model');
        
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
     * Get due bills for a student by student ID and phone number
     * POST /api/due-bills/check
     * Body: {
     *   "student_id": 123,
     *   "phone": "01708086211" (optional)
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
        
        // Validate student_id is required
        if (empty($data['student_id'])) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'student_id is required'
                ]));
            return;
        }
        
        // Get student by ID or reg_no
        // Try by ID first, then by registration number
        $student = $this->Student_model->get_by_id($data['student_id']);
        
        if (!$student) {
            // If not found by ID, try by registration number
            $student = $this->Student_model->get_by_reg_no($data['student_id']);
        }
        
        if (!$student) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Student not found'
                ]));
            return;
        }
        
        // Phone verification logic
        // If student has phone in database, phone is REQUIRED for security
        // If student has no phone, skip verification
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        $student_phone = trim($student->phone);
        $parent_phone = trim($student->prt_phone);
        $phone_verified = false;
        
        // Check if student has phone number in database
        // Treat 'N/A' as empty (no phone)
        $has_phone_in_db = !empty($student_phone) && strtolower($student_phone) !== 'n/a';
        
        if ($has_phone_in_db) {
            // Student has phone, so phone is REQUIRED
            if (empty($phone)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Phone number is required for this student'
                    ]));
                return;
            }
            
            // Verify phone matches student or parent phone
            if ($phone === $student_phone || $phone === $parent_phone) {
                $phone_verified = true;
            } else {
                // Phone provided but doesn't match
                $this->output
                    ->set_status_header(401)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Phone number does not match our records'
                    ]));
                return;
            }
        } else {
            // Student has no phone in database, skip verification
            $phone_verified = true;
        }
        
        // Get all fees for this student (use the actual student ID from database)
        $all_fees = $this->Student_fee_model->get_by_student($student->id);
        
        // Filter for due bills (Unpaid or Partial)
        // Note: status='0' means active, status='1' means deleted
        $due_bills = array_filter($all_fees, function($fee) {
            return in_array($fee->payment_status, ['Unpaid', 'Partial']) && $fee->status == '0';
        });
        
        // Format the response
        $formatted_bills = array_map(function($fee) {
            return [
                'bill_id' => $fee->id,
                'month_year' => $fee->month_year,
                'bill_month' => $fee->bill_month,
                'bill_year' => $fee->bill_year,
                'due_date' => $fee->due_date,
                'payment_status' => $fee->payment_status,
                'amount' => [
                    'base_amount' => floatval($fee->base_amount),
                    'late_fee' => floatval($fee->late_fee),
                    'total_amount' => floatval($fee->total_amount),
                    'currency' => 'BDT'
                ],
                'is_overdue' => strtotime($fee->due_date) < strtotime(date('Y-m-d'))
            ];
        }, array_values($due_bills));
        
        // Calculate totals
        $total_amount = array_sum(array_map(function($bill) {
            return $bill['amount']['total_amount'];
        }, $formatted_bills));
        $total_late_fee = array_sum(array_map(function($bill) {
            return $bill['amount']['late_fee'];
        }, $formatted_bills));
        
        // Build response
        $response = [
            'status' => 'success',
            'message' => 'Due bills retrieved successfully',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'reg_no' => $student->reg_no,
                'phone' => $student->phone ?: null,
                'parent_phone' => $student->prt_phone ?: null,
                'address' => $student->address
            ],
            'bills' => $formatted_bills,
            'summary' => [
                'total_bills' => count($formatted_bills),
                'total_amount' => $total_amount,
                'total_late_fee' => $total_late_fee,
                'currency' => 'BDT'
            ],
            'phone_verified' => $phone_verified
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    /**
     * Alternative endpoint using GET method
     * GET /api/due-bills/get?student_id=123&phone=01708086211 (phone optional)
     */
    public function get() {
        // Get data from GET parameters
        $student_id = $this->input->get('student_id');
        $phone = $this->input->get('phone');
        
        // Validate student_id is required
        if (empty($student_id)) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'student_id is required'
                ]));
            return;
        }
        
        // Get student by ID or reg_no
        // Try by ID first, then by registration number
        $student = $this->Student_model->get_by_id($student_id);
        
        if (!$student) {
            // If not found by ID, try by registration number
            $student = $this->Student_model->get_by_reg_no($student_id);
        }
        
        if (!$student) {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Student not found'
                ]));
            return;
        }
        
        // Phone verification logic
        // If student has phone in database, phone is REQUIRED for security
        // If student has no phone, skip verification
        $phone = $phone ? trim($phone) : '';
        $student_phone = trim($student->phone);
        $parent_phone = trim($student->prt_phone);
        $phone_verified = false;
        
        // Check if student has phone number in database
        // Treat 'N/A' as empty (no phone)
        $has_phone_in_db = !empty($student_phone) && strtolower($student_phone) !== 'n/a';
        
        if ($has_phone_in_db) {
            // Student has phone, so phone is REQUIRED
            if (empty($phone)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Phone number is required for this student'
                    ]));
                return;
            }
            
            // Verify phone matches student or parent phone
            if ($phone === $student_phone || $phone === $parent_phone) {
                $phone_verified = true;
            } else {
                // Phone provided but doesn't match
                $this->output
                    ->set_status_header(401)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Phone number does not match our records'
                    ]));
                return;
            }
        } else {
            // Student has no phone in database, skip verification
            $phone_verified = true;
        }
        
        // Get all fees for this student (use the actual student ID from database)
        $all_fees = $this->Student_fee_model->get_by_student($student->id);
        
        // Filter for due bills (Unpaid or Partial)
        // Note: status='0' means active, status='1' means deleted
        $due_bills = array_filter($all_fees, function($fee) {
            return in_array($fee->payment_status, ['Unpaid', 'Partial']) && $fee->status == '0';
        });
        
        // Format the response
        $formatted_bills = array_map(function($fee) {
            return [
                'bill_id' => $fee->id,
                'month_year' => $fee->month_year,
                'bill_month' => $fee->bill_month,
                'bill_year' => $fee->bill_year,
                'due_date' => $fee->due_date,
                'payment_status' => $fee->payment_status,
                'amount' => [
                    'base_amount' => floatval($fee->base_amount),
                    'late_fee' => floatval($fee->late_fee),
                    'total_amount' => floatval($fee->total_amount),
                    'currency' => 'BDT'
                ],
                'is_overdue' => strtotime($fee->due_date) < strtotime(date('Y-m-d'))
            ];
        }, array_values($due_bills));
        
        // Calculate totals
        $total_amount = array_sum(array_map(function($bill) {
            return $bill['amount']['total_amount'];
        }, $formatted_bills));
        $total_late_fee = array_sum(array_map(function($bill) {
            return $bill['amount']['late_fee'];
        }, $formatted_bills));
        
        // Build response
        $response = [
            'status' => 'success',
            'message' => 'Due bills retrieved successfully',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'reg_no' => $student->reg_no,
                'phone' => $student->phone ?: null,
                'parent_phone' => $student->prt_phone ?: null,
                'address' => $student->address
            ],
            'bills' => $formatted_bills,
            'summary' => [
                'total_bills' => count($formatted_bills),
                'total_amount' => $total_amount,
                'total_late_fee' => $total_late_fee,
                'currency' => 'BDT'
            ],
            'phone_verified' => $phone_verified
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }
}
