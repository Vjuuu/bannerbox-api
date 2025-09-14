<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt_library {
    
    private $ci;
    private $secret_key;
    private $expire_time;
    
    public function __construct() {
        $this->ci = &get_instance();
        $this->ci->load->config('config');
        $this->secret_key = $this->ci->config->item('jwt_secret_key');
        $this->expire_time = $this->ci->config->item('jwt_expire_time');
    }
    
    /**
     * Generate JWT token
     */
    public function generate_token($user_data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $user_data['user_id'],
            'email' => $user_data['email'],
            'subscription_type' => $user_data['subscription_type'],
            'iat' => time(),
            'exp' => time() + $this->expire_time
        ]);
        
        $header_encoded = $this->base64url_encode($header);
        $payload_encoded = $this->base64url_encode($payload);
        
        $signature = hash_hmac('sha256', $header_encoded . "." . $payload_encoded, $this->secret_key, true);
        $signature_encoded = $this->base64url_encode($signature);
        
        return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
    }
    
    /**
     * Validate JWT token
     */
    public function validate_token($token) {
        $token_parts = explode('.', $token);
        
        if (count($token_parts) != 3) {
            return false;
        }
        
        $header = $this->base64url_decode($token_parts[0]);
        $payload = $this->base64url_decode($token_parts[1]);
        $signature_provided = $token_parts[2];
        
        $signature = hash_hmac('sha256', $token_parts[0] . "." . $token_parts[1], $this->secret_key, true);
        $signature_encoded = $this->base64url_encode($signature);
        
        if ($signature_encoded !== $signature_provided) {
            return false;
        }
        
        $payload_data = json_decode($payload, true);
        
        if ($payload_data['exp'] < time()) {
            return false;
        }
        
        return $payload_data;
    }
    
    /**
     * Get user from token
     */
    public function get_user_from_token($token) {
        $payload = $this->validate_token($token);
        
        if (!$payload) {
            return false;
        }
        
        return [
            'user_id' => $payload['user_id'],
            'email' => $payload['email'],
            'subscription_type' => $payload['subscription_type']
        ];
    }
    
    /**
     * Base64 URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}