<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * JWT Authentication Hook
 * 
 * Validates Bearer tokens for API routes
 * Configure in application/config/hooks.php
 */
class Jwt_auth {
    
    /**
     * Validate JWT token for API requests
     * 
     * Excludes authentication endpoints and OPTIONS requests
     */
    public function validate_api_token() {
        $CI =& get_instance();
        
        // Get current URI string, segments, and controller info
        $uri_string = $CI->uri->uri_string();
        $router = $CI->router;
        $controller = strtolower($router->class);
        $directory = trim(strtolower($router->directory), '/');
        
        // Check if this is an API route:
        // 1. Primary check: Controller is in Api directory
        // 2. Fallback: URI string starts with 'api/'
        $is_api_route = false;
        
        // Check if controller is in Api directory (most reliable)
        if ($directory === 'api' || strpos($directory, 'api/') === 0) {
            $is_api_route = true;
        }
        // Fallback: Check URI string
        elseif (!empty($uri_string) && strpos($uri_string, 'api/') === 0) {
            $is_api_route = true;
        }
        
        // Skip validation for non-API routes
        if (!$is_api_route) {
            return;
        }
        
        // Skip validation for auth endpoints (controller name is 'auth')
        if ($controller === 'auth') {
            return;
        }
        
        // Skip validation for OPTIONS requests (CORS preflight)
        if ($CI->input->method() === 'options') {
            return;
        }
        
        log_message('info', 'JWT Auth: Validating API request - URI: ' . $uri_string . ', Controller: ' . $controller . ', Directory: ' . $directory);
        
        // Load JWT library
        $CI->load->library('jwt');
        
        // Extract Bearer token
        $token = $this->get_bearer_token($CI);
        
        if (!$token) {
            $this->send_unauthorized_response($CI, 'Bearer token not provided');
            return;
        }
        
        // Validate token
        $decoded = $CI->jwt->validate_token($token);
        
        if (!$decoded) {
            $this->send_unauthorized_response($CI, 'Invalid or expired token');
            return;
        }
        
        // Store decoded token data for use in controllers
        $token_data = isset($decoded['data']) ? $decoded['data'] : $decoded;
        $client_id = isset($token_data['client_id']) ? $token_data['client_id'] : null;
        
        $CI->jwt_data = $token_data;
        $CI->jwt_client_id = $client_id;
        
        log_message('info', 'JWT Auth: Token validated successfully for client_id: ' . ($client_id ?? 'unknown'));
    }
    
    /**
     * Extract Bearer token from Authorization header
     * 
     * @param object $CI CodeIgniter instance
     * @return string|false Token or false if not found
     */
    private function get_bearer_token($CI) {
        $headers = $CI->input->request_headers();
        
        // Check Authorization header (case-insensitive)
        $auth_header = null;
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $auth_header = $headers['authorization'];
        } elseif (function_exists('apache_request_headers')) {
            $all_headers = apache_request_headers();
            if (isset($all_headers['Authorization'])) {
                $auth_header = $all_headers['Authorization'];
            } elseif (isset($all_headers['authorization'])) {
                $auth_header = $all_headers['authorization'];
            }
        }
        
        // Also check SERVER variables (some servers put it here)
        if (empty($auth_header) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if (empty($auth_header)) {
            return false;
        }
        
        // Extract token from "Bearer <token>" format
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return trim($matches[1]);
        }
        
        return false;
    }
    
    /**
     * Send unauthorized response
     * 
     * @param object $CI CodeIgniter instance
     * @param string $message Error message
     */
    private function send_unauthorized_response($CI, $message) {
        log_message('info', 'JWT Auth: Unauthorized access attempt - ' . $message);
        
        // Set headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Send response
        $CI->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => $message,
                'error' => 'Unauthorized'
            ]));
        
        $CI->output->_display();
        exit();
    }
}

