<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
         $this->load->helper('url');
        $this->load->library('jwt_library');

        header('Content-Type: application/json');
        
        // Enable CORS
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-API-KEY');
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
        
        $this->validate_api_key();
    }
    
    /**
     * Validate API Key
     */
    private function validate_api_key() {
        $api_key = $this->input->get_request_header('X-API-KEY', TRUE);
        $valid_api_key = $this->config->item('api_key');
        
        if (!$api_key || $api_key !== $valid_api_key) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            exit;
        }
    }
    
    /**
     * Validate JWT Token
     */
    private function validate_jwt() {
        $auth_header = $this->input->get_request_header('Authorization', TRUE);
        
        if (!$auth_header) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authorization header missing']);
            exit;
        }
        
        $token = str_replace('Bearer ', '', $auth_header);
        $user_data = $this->jwt_library->get_user_from_token($token);
        
        if (!$user_data) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
        
        return $user_data;
    }
    
    /**
     * User Registration
     */
    public function register() {
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        $validation_rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[2]|max_length[100]'
        ];
        
        if (!$this->validate_input($input, $validation_rules)) {
            return;
        }
        
        // Check if user already exists
        if ($this->User_model->get_by_email($input['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }
        
        $user_data = [
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'full_name' => $input['full_name'],
            'subscription_type' => 'basic',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $user_id = $this->User_model->create($user_data);
        
        if ($user_id) {
            $user = $this->User_model->get_by_id($user_id);
            unset($user['password']);
            
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
    }
    
    /**
     * User Login
     */
    public function login() {
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
       
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        if (!isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }
        
        $user = $this->User_model->get_by_email($input['email']);

       

        if (!empty($user['logo'])) {
            $user['logo'] = base_url('uploads/logos/' . $user['logo']);
        }
        
        if (!$user || !password_verify($input['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            return;
        }
        // var_dump($user);
        // die();
        // Update last login
        $this->User_model->update($user['user_id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        // Generate JWT token
        $token = $this->jwt_library->generate_token([
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'subscription_type' => $user['subscription_type']
        ]);
        
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }
    
    /**
     * Get User Profile
     */
    public function profile() {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        $user = $this->User_model->get_by_id($user_data['user_id']);

         if (!empty($user['logo'])) {
            $user['logo'] = base_url('uploads/logos/' . $user['logo']);
        }
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    }
    
    /**
     * Update User Profile
     */
    // public function update_profile() {
    //     if ($this->input->method() !== 'put') {
    //         http_response_code(405);
    //         echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    //         return;
    //     }
        
    //     $user_data = $this->validate_jwt();
    //     $input = json_decode(trim(file_get_contents('php://input')), true);
        
    //     $allowed_fields = ['username', 'full_name', 'phone', 'address', 'subscription_type'];
    //     $update_data = [];
        
    //     foreach ($allowed_fields as $field) {
    //         if (isset($input[$field])) {
    //             $update_data[$field] = $input[$field];
    //         }
    //     }
        
    //     if (empty($update_data)) {
    //         http_response_code(400);
    //         echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
    //         return;
    //     }
        
    //     $update_data['updated_at'] = date('Y-m-d H:i:s');
        
    //     if ($this->User_model->update($user_data['user_id'], $update_data)) {
    //         $updated_user = $this->User_model->get_by_id($user_data['user_id']);
    //         unset($updated_user['password']);
            
    //         echo json_encode([
    //             'success' => true,
    //             'message' => 'Profile updated successfully',
    //             'data' => $updated_user
    //         ]);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode(['success' => false, 'message' => 'Update failed']);
    //     }
    // }

    public function update_profile() {
    // Use POST instead of PUT for file upload
    if ($this->input->method() !== 'post') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $user_data = $this->validate_jwt();

    // Collect input fields
    $allowed_fields = ['username', 'full_name', 'phone', 'address', 'subscription_type'];
    $update_data = [];

    foreach ($allowed_fields as $field) {
        if ($this->input->post($field)) {
            $update_data[$field] = $this->input->post($field);
        }
    }

    // ✅ Use handle_file_upload() for logo
    $logo = $this->handle_file_upload('logo');
    if ($logo !== null && $logo !== false) {
        $update_data['logo'] = $logo;
    }

    if (empty($update_data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        return;
    }

    $update_data['updated_at'] = date('Y-m-d H:i:s');

    if ($this->User_model->update($user_data['user_id'], $update_data)) {
        $updated_user = $this->User_model->get_by_id($user_data['user_id']);
        unset($updated_user['password']);
        if (!empty($updated_user['logo'])) {
            $updated_user['logo'] = base_url('uploads/logos/' . $updated_user['logo']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $updated_user
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}



    
    /**
     * Change Password
     */
    public function change_password() {
        if ($this->input->method() !== 'put') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        if (!isset($input['current_password']) || !isset($input['new_password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Current password and new password required']);
            return;
        }
        
        $user = $this->User_model->get_by_id($user_data['user_id']);
        
        if (!password_verify($input['current_password'], $user['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            return;
        }
        
        if (strlen($input['new_password']) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
            return;
        }
        
        $update_data = [
            'password' => password_hash($input['new_password'], PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->User_model->update($user_data['user_id'], $update_data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Password change failed']);
        }
    }
    
    /**
     * Get All Users (Admin only)
     */
    public function users() {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        $users = $this->User_model->get_all();
        
        // Remove passwords from response
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    }
    
    /**
     * Validate input data
     */
    private function validate_input($input, $rules) {
        foreach ($rules as $field => $rule) {
            $rule_parts = explode('|', $rule);
            
            foreach ($rule_parts as $rule_part) {
                if ($rule_part === 'required') {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
                        return false;
                    }
                }
                
                if (strpos($rule_part, 'min_length') === 0) {
                    $min_length = (int) str_replace(['min_length[', ']'], '', $rule_part);
                    if (isset($input[$field]) && strlen($input[$field]) < $min_length) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' must be at least ' . $min_length . ' characters']);
                        return false;
                    }
                }
                
                if (strpos($rule_part, 'max_length') === 0) {
                    $max_length = (int) str_replace(['max_length[', ']'], '', $rule_part);
                    if (isset($input[$field]) && strlen($input[$field]) > $max_length) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' must not exceed ' . $max_length . ' characters']);
                        return false;
                    }
                }
                
                if ($rule_part === 'valid_email') {
                    if (isset($input[$field]) && !filter_var($input[$field], FILTER_VALIDATE_EMAIL)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                        return false;
                    }
                }
            }
        }
        
        return true;
    }

    private function handle_file_upload($field_name = 'logo')
{
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        if (!isset($_FILES[$field_name])) {
            // No file uploaded → that’s okay, return null
            return null;
        }

        $error_message = 'File upload failed';
        switch ($_FILES[$field_name]['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'File size too large';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'File upload incomplete';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'No file uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = 'Temporary directory missing';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = 'Failed to write file';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = 'File upload stopped by extension';
                break;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $error_message]);
        return false;
    }

    $file = $_FILES[$field_name];

    // ✅ Validate MIME type properly
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
        return false;
    }

    // ✅ Max size 5MB
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed']);
        return false;
    }

    // ✅ Create upload dir if not exists
    $upload_dir = 'uploads/logos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            return false;
        }
    }

    // ✅ Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '_' . uniqid() . '.' . strtolower($file_extension);
    $file_path = $upload_dir . $filename;

    // ✅ Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        return false;
    }

    return $filename; // save only filename in DB
}

}