<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Standalone JWT Implementation
 * 
 * Pure PHP JWT implementation without external dependencies
 * Implements JWT encoding and decoding with HS256 algorithm
 */
class Jwt_standalone {
    
    /**
     * Base64 URL safe encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL safe decode
     */
    private function base64url_decode($data) {
        // Add padding if needed (base64_decode handles this, but let's be explicit)
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        
        // Verify decode was successful
        if ($decoded === false) {
            throw new Exception('Base64 decode failed');
        }
        
        return $decoded;
    }
    
    /**
     * Generate HMAC SHA256 signature
     */
    private function sign($message, $key, $algorithm = 'HS256') {
        if ($algorithm !== 'HS256') {
            throw new Exception('Only HS256 algorithm is supported');
        }
        return hash_hmac('sha256', $message, $key, true);
    }
    
    /**
     * Encode JWT token
     * 
     * @param array $payload Token payload data
     * @param string $key Secret key for signing
     * @param string $algorithm Algorithm (default: HS256)
     * @return string JWT token
     */
    public function encode($payload, $key, $algorithm = 'HS256') {
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm
        ];
        
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        $message = $header_encoded . '.' . $payload_encoded;
        $signature = $this->sign($message, $key, $algorithm);
        $signature_encoded = $this->base64url_encode($signature);
        
        return $message . '.' . $signature_encoded;
    }
    
    /**
     * Decode and validate JWT token
     * 
     * @param string $token JWT token
     * @param string $key Secret key for validation
     * @param array $allowed_algorithms Allowed algorithms (default: ['HS256'])
     * @return object Decoded token payload
     * @throws Exception If token is invalid
     */
    public function decode($token, $key, $allowed_algorithms = ['HS256']) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        
        // Decode header
        $header = json_decode($this->base64url_decode($header_encoded), true);
        if (!$header) {
            throw new Exception('Invalid token header');
        }
        
        // Decode payload
        $payload = json_decode($this->base64url_decode($payload_encoded), true);
        if (!$payload) {
            throw new Exception('Invalid token payload');
        }
        
        // Verify algorithm BEFORE signature verification (important for security)
        if (!isset($header['alg']) || !in_array($header['alg'], $allowed_algorithms)) {
            throw new Exception('Algorithm not allowed');
        }
        
        // Decode and verify signature
        try {
            $signature = $this->base64url_decode($signature_encoded);
        } catch (Exception $e) {
            throw new Exception('Invalid token signature encoding');
        }
        
        // Verify signature length matches expected (HMAC SHA256 produces 32 bytes)
        if (strlen($signature) !== 32) {
            throw new Exception('Invalid token signature length');
        }
        
        // Reconstruct message and compute expected signature
        $message = $header_encoded . '.' . $payload_encoded;
        $expected_signature = $this->sign($message, $key, $header['alg']);
        
        // Verify expected signature length
        if (strlen($expected_signature) !== 32) {
            throw new Exception('Signature generation error');
        }
        
        // Use hash_equals for constant-time comparison (critical for security)
        if (!hash_equals($signature, $expected_signature)) {
            throw new Exception('Invalid token signature');
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        // Check not before
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            throw new Exception('Token not yet valid');
        }
        
        return (object) $payload;
    }
}

