<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Authentication Controller
 * 
 * Handles JWT token generation for payment gateway partners
 * Note: This controller does NOT require JWT (excluded by hook)
 */
class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('jwt');
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Enable CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($this->input->method() === 'options') {
            exit();
        }
    }
    
    /**
     * Authenticate and generate JWT token
     * 
     * POST /api/auth/login
     * Body: {
     *   "client_id": "mtb_gateway",
     *   "client_secret": "mtb_secret_key_12345"
     * }
     * 
     * Response: {
     *   "success": true,
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
     *   "expires_in": 86400,
     *   "token_type": "Bearer"
     * }
     */
    public function login() {
        // Get JSON input
        $json = $this->input->raw_input_stream ?: file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // If JSON decode fails, try form data
        if (!$data) {
            $data = [
                'client_id' => $this->input->post('client_id'),
                'client_secret' => $this->input->post('client_secret')
            ];
        }
        
        // Validate input
        $client_id = isset($data['client_id']) ? trim($data['client_id']) : '';
        $client_secret = isset($data['client_secret']) ? trim($data['client_secret']) : '';
        
        if (empty($client_id) || empty($client_secret)) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'client_id and client_secret are required'
                ]));
            return;
        }
        
        // Validate credentials
        $api_clients = $this->config->item('api_clients');
        
        if (!isset($api_clients[$client_id]) || $api_clients[$client_id] !== $client_secret) {
            log_message('info', 'API Auth: Invalid credentials attempt for client_id: ' . $client_id);
            $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid client credentials'
                ]));
            return;
        }
        
        // Generate JWT token
        $token_payload = [
            'client_id' => $client_id,
            'issued_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address()
        ];
        
        $token = $this->jwt->generate_token($token_payload);
        
        if (!$token) {
            log_message('error', 'API Auth: Token generation failed for client_id: ' . $client_id);
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Token generation failed'
                ]));
            return;
        }
        
        log_message('info', 'API Auth: Token generated successfully for client_id: ' . $client_id);
        
        // Return success response
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'success' => true,
                'token' => $token,
                'expires_in' => $this->jwt->get_expiration(),
                'token_type' => 'Bearer'
            ]));
    }
    
    /**
     * Validate token (for testing)
     * 
     * GET /api/auth/validate
     * Header: Authorization: Bearer <token>
     */
    public function validate() {
        // This endpoint requires authentication itself, but we'll allow it for testing
        // In production, you might want to remove this endpoint
        
        $token = $this->get_bearer_token();
        
        if (!$token) {
            $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Bearer token not provided'
                ]));
            return;
        }
        
        $decoded = $this->jwt->validate_token($token);
        
        if (!$decoded) {
            $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ]));
            return;
        }
        
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Token is valid',
                'data' => $decoded['data'] ?? $decoded
            ]));
    }
    
    /**
     * Helper: Extract Bearer token from Authorization header
     * 
     * @return string|false Token or false if not found
     */
    private function get_bearer_token() {
        $headers = $this->input->request_headers();
        
        // Check Authorization header
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $auth_header = $headers['authorization'];
        } elseif (function_exists('apache_request_headers')) {
            $all_headers = apache_request_headers();
            $auth_header = isset($all_headers['Authorization']) ? $all_headers['Authorization'] : 
                          (isset($all_headers['authorization']) ? $all_headers['authorization'] : null);
        } else {
            return false;
        }
        
        if (empty($auth_header)) {
            return false;
        }
        
        // Extract token from "Bearer <token>" format
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return $matches[1];
        }
        
        return false;
    }
}

