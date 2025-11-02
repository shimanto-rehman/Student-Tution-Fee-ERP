<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generate_bill extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Student_fee_model');
        $this->load->helper(['url', 'form']);
        $this->load->library(['session']);
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Enable CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
        
        // Handle preflight requests
        if ($this->input->method() === 'options') {
            exit();
        }
    }

    /**
     * API Endpoint: Generate monthly fees for ALL students
     * 
     * Note: API key authentication is automatically validated by the Api_auth hook
     * This endpoint requires a valid API key
     * 
     * Example:
     * GET/POST: /api/generate-bill/api_generate_monthly_fees
     */
    public function api_generate_monthly_fees() {
        // API key authentication is handled automatically by Api_auth hook
        // If we reach here, authentication has passed

        log_message('info', 'CronJob API: Monthly fee generation triggered');

        // Get parameters from JSON body first, then GET or POST
        $json_body = json_decode($this->input->raw_input_stream, true);
        
        $month = isset($json_body['month']) ? $json_body['month'] : null;
        if ($month === null) {
            $month = $this->input->get('month');
        }
        if ($month === null || $month === '') {
            $month = $this->input->post('month');
        }
        
        $year = isset($json_body['year']) ? $json_body['year'] : null;
        if ($year === null) {
            $year = $this->input->get('year');
        }
        if ($year === null || $year === '') {
            $year = $this->input->post('year');
        }
        
        $due_day = isset($json_body['due_day']) ? $json_body['due_day'] : null;
        if ($due_day === null) {
            $due_day = $this->input->get('due_day');
        }
        if ($due_day === null || $due_day === '') {
            $due_day = $this->input->post('due_day');
        }

        // Use defaults if not provided
        $month = ($month !== null && $month !== '') ? (int)$month : date('n');
        $year = ($year !== null && $year !== '') ? (int)$year : date('Y');
        $due_day = ($due_day !== null && $due_day !== '') ? (int)$due_day : 20;
        
        log_message('info', "API Generate Fees - Received: month={$month}, year={$year}, due_day={$due_day}");
        log_message('info', "API Generate Fees - JSON body: " . json_encode($json_body));
        log_message('info', "API Generate Fees - Raw input: " . $this->input->raw_input_stream);

        // Validate parameters
        if ($month < 1 || $month > 12) {
            $this->respond_error('Invalid month parameter (must be 1-12)', 400);
            return;
        }
        if ($year < 2020 || $year > 2050) {
            $this->respond_error('Invalid year parameter (must be 2020-2050)', 400);
            return;
        }
        if ($due_day < 1 || $due_day > 31) {
            $this->respond_error('Invalid due_day parameter (must be 1-31)', 400);
            return;
        }

        // Execute generation
        $result = $this->Student_fee_model->generate_monthly_fees($month, $year, $due_day);
        $month_name = date('F', mktime(0, 0, 0, $month, 1));
        $status_code = $result['success'] ? 200 : 500;

        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => $month_name,
                    'due_day' => $due_day,
                    'generated' => $result['generated'],
                    'skipped' => $result['skipped'],
                    'total_students' => $result['total_students'],
                    'errors' => $result['errors'] ?? []
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]));
    }

    /**
     * Helper: Return standardized error response
     */
    private function respond_error($message, $status_code = 400) {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => $message
            ]));
    }
}

// Usage Example:
// curl -X POST "http://localhost/pioneer-dental/api/generate-monthly-fees" \
//  -H "X-API-Key: YOUR_API_KEY_HERE" \
//  -H "Content-Type: application/json" \
//  -d '{"month": 11, "year": 2025, "due_day": 20}'
//
// Cron job Command:
// 0 2 2 * * curl -X POST "http://localhost/pioneer-dental/api/generate-monthly-fees" \
//  -H "X-API-Key: YOUR_API_KEY_HERE" >> /var/log/monthly_fees.log 2>&1