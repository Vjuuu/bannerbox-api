<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TopCategories extends CI_Controller {
    
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
    // public function index() {
    //     if ($this->input->method() !== 'get') {
    //         http_response_code(405);
    //         echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    //         return;
    //     }
        
    //     $categories = $this->Category_model->get_all();
        
    //     echo json_encode([
    //         'success' => true,
    //         'data' => $categories
    //     ]);
    // }
    
    // GET /api/top-categories
    public function index() {

        $topCategories = $this->TopCategory_model->getAll();
        var_dump($topCategories);
        die();
        
        echo json_encode([
            'success' => true,
            'data' => $topCategories
        ]);
    }
    
    // POST /api/top-categories/add
    public function add() {
        $input = json_decode(file_get_contents('php://input'), true);
        $categoryIds = $input['category_ids'] ?? [];
        
        if (empty($categoryIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No categories selected']);
            return;
        }
        
        // Filter out already existing categories
        $newCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            if (!$this->TopCategory_model->exists($categoryId)) {
                $newCategoryIds[] = $categoryId;
            }
        }
        
        if (empty($newCategoryIds)) {
            echo json_encode(['success' => false, 'message' => 'All categories already exist in top categories']);
            return;
        }
        
        $result = $this->TopCategory_model->add($newCategoryIds);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Categories added successfully' : 'Failed to add categories',
            'data' => $this->TopCategory_model->getAll()
        ]);
    }
    
    // PUT /api/top-categories/update-order/{id}
    public function updateOrder($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $direction = $input['direction'] ?? 'up'; // 'up' or 'down'
        
        $result = $this->TopCategory_model->updateOrder($id, $direction);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Order updated successfully' : 'Failed to update order',
            'data' => $this->TopCategory_model->getAll()
        ]);
    }
    
    // DELETE /api/top-categories/delete/{id}
    public function delete($id) {
        $result = $this->TopCategory_model->delete($id);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Top category removed successfully' : 'Failed to remove top category'
        ]);
    }
}