<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * JWT Library for CodeIgniter
 * 
 * Standalone JWT implementation - no external dependencies required
 * Uses pure PHP implementation for JWT token generation and validation
 */
class Jwt {
    
    private $secret_key;
    private $algorithm = 'HS256';
    private $expiration_time = 86400; // 24 hours in seconds
    private $jwt_standalone;
    
    public function __construct() {
        $CI =& get_instance();
        $CI->config->load('config');
        
        // Get JWT secret key from config
        $this->secret_key = $CI->config->item('jwt_secret_key');
        
        // Validate secret key exists
        if (empty($this->secret_key)) {
            log_message('error', 'JWT: Secret key not configured in config.php');
            show_error('JWT secret key not configured. Please set jwt_secret_key in config.php');
        }
        
        // Load standalone JWT implementation
        require_once(APPPATH . 'libraries/Jwt_standalone.php');
        $this->jwt_standalone = new Jwt_standalone();
    }
    
    /**
     * Generate a JWT token
     * 
     * @param array $payload Array of data to encode in the token
     * @param int $expiration Optional expiration time in seconds (default: 24 hours)
     * @return string JWT token
     */
    public function generate_token($payload, $expiration = null) {
        $expiration = $expiration !== null ? $expiration : $this->expiration_time;
        
        // Prepare token payload
        $token_payload = [
            'iat' => time(), // Issued at time
            'exp' => time() + $expiration, // Expiration time
            'data' => $payload // Custom data
        ];
        
        try {
            $token = $this->jwt_standalone->encode($token_payload, $this->secret_key, $this->algorithm);
            return $token;
        } catch (Exception $e) {
            log_message('error', 'JWT: Token generation failed - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate and decode a JWT token
     * 
     * @param string $token JWT token to validate
     * @return array|false Decoded token data or false on failure
     */
    public function validate_token($token) {
        if (empty($token)) {
            return false;
        }
        
        try {
            $decoded = $this->jwt_standalone->decode($token, $this->secret_key, [$this->algorithm]);
            return (array) $decoded;
        } catch (Exception $e) {
            // Check for specific error types
            $message = $e->getMessage();
            if (strpos($message, 'expired') !== false) {
                log_message('info', 'JWT: Token expired - ' . $message);
            } elseif (strpos($message, 'signature') !== false) {
                log_message('info', 'JWT: Invalid token signature - ' . $message);
            } else {
                log_message('error', 'JWT: Token validation failed - ' . $message);
            }
            return false;
        }
    }
    
    /**
     * Get token payload data (without validation)
     * Note: Use validate_token() for secure validation
     * 
     * @param string $token JWT token
     * @return array|false Token payload or false on failure
     */
    public function decode_token($token) {
        if (empty($token)) {
            return false;
        }
        
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }
            
            $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $parts[1]))), true);
            return $payload;
        } catch (Exception $e) {
            log_message('error', 'JWT: Token decode failed - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set custom expiration time
     * 
     * @param int $seconds Expiration time in seconds
     */
    public function set_expiration($seconds) {
        $this->expiration_time = $seconds;
    }
    
    /**
     * Get current expiration time
     * 
     * @return int Expiration time in seconds
     */
    public function get_expiration() {
        return $this->expiration_time;
    }
}
