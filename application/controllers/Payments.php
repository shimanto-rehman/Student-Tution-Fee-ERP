<?php
// application/controllers/Payments.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payments extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_fee_model');
        $this->load->model('Payment_model');
        $this->load->helper(['url', 'form']);
        $this->load->library(['form_validation', 'session']);
        
        // Debug: Log when controller is loaded
        log_message('info', 'Payments Controller Loaded');
    }
    
    // List all payments
    public function index() {
        $data['page_title'] = 'Payments';
        
        $limit = 50;
        $offset = $this->input->get('offset') ?? 0;

        // Collect filters from query string
        $filters = [
            'search' => $this->input->get('search'),
            'mode' => $this->input->get('mode'),
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date')
        ];

        $data['payments'] = $this->Payment_model->get_all($limit, $offset, $filters);
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        
        $this->load->view('templates/header', $data);
        $this->load->view('payments/index', $data);
        $this->load->view('templates/footer');
    }
    
    // Create payment for a student fee
    public function create($student_fee_id) {
        $data['page_title'] = 'Make Payment';
        $data['fee'] = $this->Student_fee_model->get_by_id($student_fee_id);
        
        if (!$data['fee']) {
            $this->session->set_flashdata('error', 'Fee not found');
            redirect('student-fees');
        }
        
        // Calculate remaining balance
        $total_paid = $this->Payment_model->get_total_paid_for_fee($student_fee_id);
        $data['total_paid'] = $total_paid;
        $data['balance'] = $data['fee']->total_amount - $total_paid;
        
        if ($data['balance'] <= 0) {
            $this->session->set_flashdata('info', 'This fee has been fully paid');
            redirect('student-fees/view/' . $student_fee_id);
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('amount_paid', 'Amount', 'required|numeric');
            $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required');
            $this->form_validation->set_rules('payment_date', 'Payment Date', 'required');
            
            if ($this->form_validation->run()) {
                $amount_paid = $this->input->post('amount_paid');
                
                // Validate amount
                if ($amount_paid > $data['balance']) {
                    $this->session->set_flashdata('error', 'Amount cannot exceed balance');
                } else {
                    $payment_data = [
                        'student_fee_id' => $student_fee_id,
                        'payment_date' => $this->input->post('payment_date'),
                        'amount_paid' => $amount_paid,
                        'payment_mode' => $this->input->post('payment_mode'),
                        'payment_method' => $this->input->post('payment_method') ?? null,
                        'transaction_id' => $this->input->post('transaction_id') ?? null,
                        'remarks' => $this->input->post('remarks') ?? null,
                        'mtb_payment_status' => 'success'
                    ];
                    
                    $payment_id = $this->Payment_model->create($payment_data);
                    
                    if ($payment_id) {
                        $this->session->set_flashdata('success', 'Payment recorded successfully');
                        redirect('payments/receipt/' . $payment_id);
                    } else {
                        $this->session->set_flashdata('error', 'Failed to record payment');
                    }
                }
            }
        }
        
        $this->load->view('templates/header', $data);
        $this->load->view('payments/create', $data);
        $this->load->view('templates/footer');
    }
    
    // Process payment via MTB Gateway
    public function process($student_fee_id) {
        $fee = $this->Student_fee_model->get_by_id($student_fee_id);
        
        if (!$fee) {
            $this->session->set_flashdata('error', 'Fee not found');
            redirect('student-fees');
        }
        
        // Calculate balance
        $total_paid = $this->Payment_model->get_total_paid_for_fee($student_fee_id);
        $balance = $fee->total_amount - $total_paid;
        
        if ($balance <= 0) {
            $this->session->set_flashdata('info', 'This fee has been fully paid');
            redirect('student-fees/view/' . $student_fee_id);
        }
        
        // Get billing info from MTB API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, base_url('api/mtb/billing-info'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'student_id' => $fee->student_id,
            'fee_ids' => [$student_fee_id]
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $billing_info = json_decode($response, true);
        
        if ($billing_info['status'] === 'success') {
            $data['page_title'] = 'Process Payment';
            $data['billing_info'] = $billing_info['data'];
            $data['fee'] = $fee;
            
            $this->load->view('templates/header', $data);
            $this->load->view('payments/process', $data);
            $this->load->view('templates/footer');
        } else {
            $this->session->set_flashdata('error', 'Failed to get billing information');
            redirect('student-fees/view/' . $student_fee_id);
        }
    }
    
    // Payment receipt
    public function receipt($payment_id) {
        $data['page_title'] = 'Payment Receipt';
        $data['payment'] = $this->Payment_model->get_by_id($payment_id);
        
        if (!$data['payment']) {
            $this->session->set_flashdata('error', 'Payment not found');
            redirect('payments');
        }
        
        $this->load->view('templates/header', $data);
        $this->load->view('payments/receipt', $data);
        $this->load->view('templates/footer');
    }
}