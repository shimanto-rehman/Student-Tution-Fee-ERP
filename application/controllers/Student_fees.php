<?php
// application/controllers/Student_fees.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_fees extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_fee_model');
        $this->load->model('Payment_model');
        $this->load->helper(['url', 'form']);
        $this->load->library(['form_validation', 'session']);
    }
    
    // List all student fees with pagination and search
    public function index() {
        $data['page_title'] = 'Student Fees Management';
        
        // Update late fees before displaying
        $this->Student_fee_model->update_all_late_fees();
        
        // Get filter parameters
        $search = $this->input->get('search');
        $status = $this->input->get('status');
        $month_year = $this->input->get('month_year');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 50;
        $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
        
        // Build filters array - only add non-empty values
        $filters = [];
        if (!empty($search) && trim($search) !== '') {
            $filters['search'] = trim($search);
        }
        if (!empty($status) && trim($status) !== '') {
            $filters['status'] = $status;
        }
        if (!empty($month_year) && trim($month_year) !== '') {
            $filters['month_year'] = $month_year;
        }
        
        // Debug: Log filter parameters
        log_message('debug', 'Filter Parameters: ' . json_encode($filters));
        log_message('debug', 'Per Page: ' . $per_page . ', Offset: ' . $offset);
        
        // Get total count for pagination
        $data['total_rows'] = $this->Student_fee_model->count_all($filters);
        
        // Get fees with filters and pagination
        $data['fees'] = $this->Student_fee_model->get_all($per_page, $offset, $filters);
        $data['offset'] = $offset;
        $data['per_page'] = $per_page;
        
        // Debug: Log results
        log_message('debug', 'Total Rows: ' . $data['total_rows']);
        log_message('debug', 'Fees Retrieved: ' . count($data['fees']));
        
        $this->load->view('templates/header', $data);
        $this->load->view('student_fees/index', $data);
        $this->load->view('templates/footer');
    }
    
    // View single fee details
    public function view($id) {
        $data['page_title'] = 'Fee Details';
        $data['fee'] = $this->Student_fee_model->get_by_id($id);
        
        if (!$data['fee']) {
            $this->session->set_flashdata('error', 'Fee not found');
            redirect('student-fees');
        }
        
        // Get payment history for this fee
        $data['payments'] = $this->Payment_model->get_by_student_fee($id);
        $data['total_paid'] = $this->Payment_model->get_total_paid_for_fee($id);
        $data['balance'] = $data['fee']->total_amount - $data['total_paid'];
        
        $this->load->view('templates/header', $data);
        $this->load->view('student_fees/view', $data);
        $this->load->view('templates/footer');
    }
    
    // Create new fee
    public function create() {
        $data['page_title'] = 'Create Student Fee';
        
        // Get students and fee types
        $data['students'] = $this->Student_model->get_all(1000);
        $data['fee_types'] = $this->db->where('status', '1')->get('fees_master')->result();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('student_id', 'Student', 'required|integer');
            $this->form_validation->set_rules('fee_id', 'Fee Type', 'required|integer');
            $this->form_validation->set_rules('month_year', 'Month/Year', 'required');
            $this->form_validation->set_rules('base_amount', 'Base Amount', 'required|decimal');
            $this->form_validation->set_rules('due_date', 'Due Date', 'required');
            
            if ($this->form_validation->run()) {
                // Extract month and year from month_year (format: YYYY-MM)
                $month_year = $this->input->post('month_year');
                $month_year_parts = explode('-', $month_year);
                $bill_year = (int)$month_year_parts[0];
                $bill_month = (int)$month_year_parts[1];

                $fee_data = [
                    'student_id' => $this->input->post('student_id'),
                    'fee_id' => $this->input->post('fee_id'),
                    'month_year' => $month_year,
                    'bill_month' => $bill_month,
                    'bill_year' => $bill_year,
                    'due_date' => $this->input->post('due_date'),
                    'base_amount' => $this->input->post('base_amount'),
                    'payment_status' => 'Unpaid',
                    'late_fee' => 0.00
                ];

                if ($this->Student_fee_model->create($fee_data)) {
                    $this->session->set_flashdata('success', 'Fee created successfully');
                    redirect('student-fees');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create fee');
                }
            }
        }
        
        $this->load->view('templates/header', $data);
        $this->load->view('student_fees/create', $data);
        $this->load->view('templates/footer');
    }

    // AJAX: Search student
    public function search_student() {
        $term = $this->input->get('term');
        $students = $this->Student_model->search_students($term);
        $data = [];

        foreach ($students as $s) {
            $data[] = [
                'id' => $s->id,
                'label' => $s->name . ' (' . $s->reg_no . ')',
                'value' => $s->name . ' (' . $s->reg_no . ')'
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    // Edit fee
    public function edit($id) {
        $data['page_title'] = 'Edit Student Fee';
        $data['fee'] = $this->Student_fee_model->get_by_id($id);
        
        if (!$data['fee']) {
            $this->session->set_flashdata('error', 'Fee not found');
            redirect('student-fees');
        }
        
        $data['students'] = $this->Student_model->get_all(1000);
        $data['fee_types'] = $this->db->where('status', '1')->get('fees_master')->result();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('base_amount', 'Base Amount', 'required|decimal');
            $this->form_validation->set_rules('due_date', 'Due Date', 'required');
            
            if ($this->form_validation->run()) {
                $fee_data = [
                    'base_amount' => $this->input->post('base_amount'),
                    'due_date' => $this->input->post('due_date'),
                    'late_fee' => $this->Student_fee_model->calculate_late_fee($this->input->post('due_date'))
                ];
                
                if ($this->Student_fee_model->update($id, $fee_data)) {
                    $this->session->set_flashdata('success', 'Fee updated successfully');
                    redirect('student-fees/view/' . $id);
                } else {
                    $this->session->set_flashdata('error', 'Failed to update fee');
                }
            }
        }
        
        $this->load->view('templates/header', $data);
        $this->load->view('student_fees/edit', $data);
        $this->load->view('templates/footer');
    }
    
    // Delete fee
    public function delete($id) {
        $fee = $this->Student_fee_model->get_by_id($id);
        
        if (!$fee) {
            $this->session->set_flashdata('error', 'Fee not found');
            redirect('student-fees');
        }
        
        // Check if there are payments
        $payments = $this->Payment_model->get_by_student_fee($id);
        if (!empty($payments)) {
            $this->session->set_flashdata('error', 'Cannot delete fee with existing payments');
            redirect('student-fees');
        }
        
        if ($this->Student_fee_model->delete($id)) {
            $this->session->set_flashdata('success', 'Fee deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete fee');
        }
        
        redirect('student-fees');
    }
    
    // Generate monthly fees for a student
    public function generate_monthly_fees($student_id) {
        $student = $this->Student_model->get_by_id($student_id);
        
        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found');
            redirect('student-fees');
        }
        
        if ($this->input->post()) {
            $fee_id = $this->input->post('fee_id');
            $months = $this->input->post('months') ?? 1;
            
            $generated = $this->Student_fee_model->generate_monthly_fees($student_id, $fee_id, $months);
            
            $this->session->set_flashdata('success', "$generated month(s) of fees generated successfully");
            redirect('student-fees');
        }
        
        $data['page_title'] = 'Generate Monthly Fees';
        $data['student'] = $student;
        $data['fee_types'] = $this->db->where('status', '1')->get('fees_master')->result();
        
        $this->load->view('templates/header', $data);
        $this->load->view('student_fees/generate', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Generate monthly fees for ALL active students
     * Can be triggered manually from the UI or automatically on the 1st
     */
    public function generate_all_monthly_fees() {
        // Check if this is an AJAX request or API call
        $is_ajax = $this->input->is_ajax_request();
        $is_api_call = $this->is_api_request();

        log_message('info', 'Monthly fee generation triggered - AJAX: ' . ($is_ajax ? 'Yes' : 'No') . ', API: ' . ($is_api_call ? 'Yes' : 'No'));

        // Get POST data (month, year, due_day)
        $post_data = json_decode($this->input->raw_input_stream, true);
        
        $month = isset($post_data['month']) ? (int)$post_data['month'] : date('n');
        $year = isset($post_data['year']) ? (int)$post_data['year'] : date('Y');
        $due_day = isset($post_data['due_day']) ? (int)$post_data['due_day'] : 20;
        
        // Validate month and year
        if ($month < 1 || $month > 12) {
            $result = [
                'success' => false,
                'message' => 'Invalid month selected'
            ];
        } elseif ($year < 2020 || $year > 2050) {
            $result = [
                'success' => false,
                'message' => 'Invalid year selected'
            ];
        } elseif ($due_day < 1 || $due_day > 31) {
            $result = [
                'success' => false,
                'message' => 'Invalid due day selected'
            ];
        } else {
            // Execute the generation with custom month/year
            $result = $this->Student_fee_model->generate_monthly_fees($month, $year, $due_day);
        }

        // Prepare response message
        if ($result['success']) {
            $month_name = date('F', mktime(0, 0, 0, $month, 1));
            $message = "Monthly fees generated successfully for {$month_name} {$year}! ";
            $message .= "Generated: {$result['generated']} bills, ";
            $message .= "Skipped: {$result['skipped']} (already exist), ";
            $message .= "Total students: {$result['total_students']}";
            $type = 'success';
        } else {
            $message = "Failed to generate monthly fees: {$result['message']}";
            $type = 'error';
        }

        // Return response based on request type
        if ($is_ajax || $is_api_call) {
            // AJAX/API request - return JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => $result['success'],
                    'message' => $message,
                    'data' => $result
                ]));
        } else {
            // Regular request - set flash message and redirect
            $this->session->set_flashdata($type, $message);
            redirect('student-fees');
        }
    }
}