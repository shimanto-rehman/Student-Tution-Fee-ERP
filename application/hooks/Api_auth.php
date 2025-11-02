<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Key Authentication Hook
 * 
 * Validates API keys for API routes
 * Configure in application/config/hooks.php
 */
class Api_auth {
    
    /**
     * Validate API key for API requests
     * 
     * Excludes authentication endpoints and OPTIONS requests
     */
    public function validate_api_key() {
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
        
        // Skip validation for OPTIONS requests (CORS preflight)
        if ($CI->input->method() === 'options') {
            return;
        }
        
        log_message('info', 'API Auth: Validating API request - URI: ' . $uri_string . ', Controller: ' . $controller . ', Directory: ' . $directory);
        
        // Get API key from header or query parameter
        $api_key = $CI->input->get_request_header('X-API-Key') ?: $CI->input->get('api_key');
        
        if (empty($api_key)) {
            $this->send_unauthorized_response($CI, 'API key not provided');
            return;
        }
        
        // Validate API key
        $valid_api_key = $CI->config->item('api_secret_key');
        
        if ($api_key !== $valid_api_key) {
            log_message('info', 'API Auth: Invalid API key attempt for URI: ' . $uri_string);
            $this->send_unauthorized_response($CI, 'Invalid API key');
            return;
        }
        
        log_message('info', 'API Auth: API key validated successfully');
    }
    
    /**
     * Send unauthorized response
     * 
     * @param object $CI CodeIgniter instance
     * @param string $message Error message
     */
    private function send_unauthorized_response($CI, $message) {
        log_message('info', 'API Auth: Unauthorized access attempt - ' . $message);
        
        // Set headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
        
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

