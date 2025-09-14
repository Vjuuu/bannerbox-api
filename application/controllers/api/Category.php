<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Category_model');
        $this->load->library('jwt_library');
        header('Content-Type: application/json');
        
        // Enable CORS
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-API-KEY');
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
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
     * Get all categories
     */
    public function index() {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $categories = $this->Category_model->get_all();
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * Get single category
     */
    public function show($id) {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $category = $this->Category_model->get_by_id($id);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $category
        ]);
    }
    
    /**
     * Create new category
     */
    public function create() {
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        if (!isset($input['name']) || empty($input['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            return;
        }
        
        // Check if category already exists
        if ($this->Category_model->get_by_name($input['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category already exists']);
            return;
        }
        
        $category_data = [
            'name' => $input['name'],
            'description' => $input['description'] ?? '',
            'icon' => $input['icon'] ?? '',
            'color' => $input['color'] ?? '#000000',
            'is_active' => isset($input['is_active']) ? (int)$input['is_active'] : 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $category_id = $this->Category_model->create($category_data);
        
        if ($category_id) {
            $category = $this->Category_model->get_by_id($category_id);
            echo json_encode([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category creation failed']);
        }
    }
    
    /**
     * Update category
     */
    public function update($id) {
        if ($this->input->method() !== 'put') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        $allowed_fields = ['name', 'description', 'icon', 'color', 'is_active'];
        $update_data = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $update_data[$field] = $input[$field];
            }
        }
        
        if (empty($update_data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            return;
        }
        
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($this->Category_model->update($id, $update_data)) {
            $updated_category = $this->Category_model->get_by_id($id);
            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $updated_category
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category update failed']);
        }
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        if ($this->input->method() !== 'delete') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        
        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        if ($this->Category_model->delete($id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category deletion failed']);
        }
    }
}