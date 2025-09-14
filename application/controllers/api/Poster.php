<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Poster extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Poster_model');
        $this->load->model('Category_model');
        $this->load->library('jwt_library');
        $this->load->helper('url');
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
     * Get all posters
     */
    public function index() {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $page = (int)$this->input->get('page') ?: 1;
        $limit = (int)$this->input->get('limit') ?: 20;
        $category_id = $this->input->get('category_id');
        $search = $this->input->get('search');
        
        $posters = $this->Poster_model->get_all($page, $limit, $category_id, $search);
        $total = $this->Poster_model->count_all($category_id, $search);

        // Append base URL to images
        $base_url = rtrim(base_url('uploads/posters/'), '/');
        foreach ($posters as &$poster) {
            if (!empty($poster['image_url'])) {
                $poster['image_url'] = $base_url . '/' . ltrim($poster['image_url'], '/');
            }
            if (!empty($poster['thumbnail_url'])) {
                $poster['thumbnail_url'] = $base_url . '/thumbnails/'. ltrim($poster['thumbnail_url'], '/');
            }
        }
        unset($poster);
        
        echo json_encode([
            'success' => true,
            'data' => $posters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'per_page' => $limit
            ]
        ]);
    }
    
    /**
     * Get single poster
     */
    public function show($id) {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $poster = $this->Poster_model->get_by_id($id);
        
        if (!$poster) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Poster not found']);
            return;
        }
        
        // Increment view count
        $this->Poster_model->increment_views($id);
        
        echo json_encode([
            'success' => true,
            'data' => $poster
        ]);
    }
    
    /**
     * Create new poster
     */
    public function create() {
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        
        // Handle both JSON and FormData inputs
        $input = [];
        $content_type = $this->input->get_request_header('Content-Type', TRUE);
        
        if (strpos($content_type, 'multipart/form-data') !== false) {
            // Handle FormData (file upload)
            $input = $_POST;
            $uploaded_file = $this->handle_file_upload();
            if ($uploaded_file === false) {
                return; // Error already sent in handle_file_upload
            }
            if ($uploaded_file) {
                $input['image_url'] = $uploaded_file['image_url'];
                $input['thumbnail_url'] = $uploaded_file['thumbnail_url'];
            }
        } else {
            // Handle JSON input (existing functionality)
            $input = json_decode(trim(file_get_contents('php://input')), true);
        }
        
        $required_fields = ['title', 'image_url', 'category_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
                return;
            }
        }
        
        // Validate category exists
        if (!$this->Category_model->get_by_id($input['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category']);
            return;
        }
        
        $poster_data = [
            'title' => $input['title'],
            'description' => $input['description'] ?? '',
            'image_url' => $input['image_url'],
            'thumbnail_url' => $input['thumbnail_url'] ?? $input['image_url'],
            'category_id' => $input['category_id'],
            'tags' => $input['tags'] ?? '',
            'is_premium' => isset($input['is_premium']) ? (int)$input['is_premium'] : 0,
            'is_featured' => isset($input['is_featured']) ? (int)$input['is_featured'] : 0,
            'download_count' => 0,
            'view_count' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $poster_id = $this->Poster_model->create($poster_data);
        
        if ($poster_id) {
            $poster = $this->Poster_model->get_by_id($poster_id);
            echo json_encode([
                'success' => true,
                'message' => 'Poster created successfully',
                'data' => $poster
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Poster creation failed']);
        }
    }
    
    /**
     * Handle file upload for poster images
     */
    private function handle_file_upload() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            if (!isset($_FILES['image'])) {
                // No file uploaded, this is okay if image_url is provided in POST data
                return null;
            }
            
            $error_message = 'File upload failed';
            switch ($_FILES['image']['error']) {
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
        
        $file = $_FILES['image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
            return false;
        }
        
        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed']);
            return false;
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/posters/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                return false;
            }
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'poster_' . time() . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
            return false;
        }
        
        // Generate thumbnail
        $thumbnail_path = $this->generate_thumbnail($file_path, $upload_dir, $filename);
        
        // Get base URL for file URLs
        $base_url = rtrim($this->config->item('base_url'), '/');
        
       return [
                    'image_url'     => $filename, // only store filename in DB
                     'thumbnail_url' => $thumbnail_path ? basename($thumbnail_path) : $filename,
                    // 'full_image_url'     => $base_url . '/' . $file_path, // for API response
                    //  'full_thumbnail_url' => $thumbnail_path ? $base_url . '/' . $thumbnail_path : $base_url . '/' . $file_path
                ];
    }
    
    /**
     * Generate thumbnail for uploaded image
     */
    private function generate_thumbnail($source_path, $upload_dir, $filename) {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            return null; // Skip thumbnail generation if GD is not available
        }
        
        $thumbnail_dir = $upload_dir . 'thumbnails/';
        if (!is_dir($thumbnail_dir)) {
            if (!mkdir($thumbnail_dir, 0755, true)) {
                return null; // Skip thumbnail generation if directory creation fails
            }
        }
        
        $thumbnail_filename = 'thumb_' . $filename;
        $thumbnail_path = $thumbnail_dir . $thumbnail_filename;
        
        // Get image info
        $image_info = getimagesize($source_path);
        if (!$image_info) {
            return null;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        $type = $image_info[2];
        
        // Calculate thumbnail dimensions (max 300x300, maintain aspect ratio)
        $thumb_width = 300;
        $thumb_height = 300;
        
        if ($width > $height) {
            $thumb_height = ($height / $width) * $thumb_width;
        } else {
            $thumb_width = ($width / $height) * $thumb_height;
        }
        
        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($source_path);
                break;
            case IMAGETYPE_WEBP:
                $source_image = imagecreatefromwebp($source_path);
                break;
            default:
                return null;
        }
        
        if (!$source_image) {
            return null;
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumb_width, $thumb_height, $transparent);
        }
        
        // Resize image
        imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
        
        // Save thumbnail based on original type
        $success = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($thumbnail, $thumbnail_path, 85);
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($thumbnail, $thumbnail_path, 8);
                break;
            case IMAGETYPE_GIF:
                $success = imagegif($thumbnail, $thumbnail_path);
                break;
            case IMAGETYPE_WEBP:
                $success = imagewebp($thumbnail, $thumbnail_path, 85);
                break;
        }
        
        // Clean up memory
        imagedestroy($source_image);
        imagedestroy($thumbnail);
        
        return $success ? $thumbnail_path : null;
    }
    
    /**
     * Update poster
     */
    public function update($id) {
        if ($this->input->method() !== 'put') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        
        // Handle both JSON and FormData inputs for updates
        $input = [];
        $content_type = $this->input->get_request_header('Content-Type', TRUE);
        
        if (strpos($content_type, 'multipart/form-data') !== false) {
            // Handle FormData (file upload) - Note: PUT with files requires special handling
            parse_str(file_get_contents("php://input"), $input);
            $uploaded_file = $this->handle_file_upload();
            if ($uploaded_file === false) {
                return; // Error already sent in handle_file_upload
            }
            if ($uploaded_file) {
                $input['image_url'] = $uploaded_file['image_url'];
                $input['thumbnail_url'] = $uploaded_file['thumbnail_url'];
            }
        } else {
            // Handle JSON input (existing functionality)
            $input = json_decode(trim(file_get_contents('php://input')), true);
        }
        
        $poster = $this->Poster_model->get_by_id($id);
        if (!$poster) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Poster not found']);
            return;
        }
        
        $allowed_fields = ['title', 'description', 'image_url', 'thumbnail_url', 'category_id', 'tags', 'is_premium', 'is_featured'];
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
        
        // Validate category if provided
        if (isset($update_data['category_id']) && !$this->Category_model->get_by_id($update_data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category']);
            return;
        }
        
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($this->Poster_model->update($id, $update_data)) {
            $updated_poster = $this->Poster_model->get_by_id($id);
            echo json_encode([
                'success' => true,
                'message' => 'Poster updated successfully',
                'data' => $updated_poster
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Poster update failed']);
        }
    }
    
    /**
     * Delete poster
     */
    public function delete($id) {
        if ($this->input->method() !== 'delete') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $user_data = $this->validate_jwt();
        
        $poster = $this->Poster_model->get_by_id($id);
        if (!$poster) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Poster not found']);
            return;
        }
        
        if ($this->Poster_model->delete($id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Poster deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Poster deletion failed']);
        }
    }
    
    /**
     * Get trending posters
     */
    public function trending() {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $limit = (int)$this->input->get('limit') ?: 10;
        $days = (int)$this->input->get('days') ?: 7;
        
        $trending_posters = $this->Poster_model->get_trending($limit, $days);
        
        echo json_encode([
            'success' => true,
            'data' => $trending_posters
        ]);
    }
    
    /**
     * Get posters by category
     */
    public function by_category($category_id) {
        if ($this->input->method() !== 'get') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $page = (int)$this->input->get('page') ?: 1;
        $limit = (int)$this->input->get('limit') ?: 20;
        
        // Validate category exists
        if (!$this->Category_model->get_by_id($category_id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        $posters = $this->Poster_model->get_by_category($category_id, $page, $limit);
        $total = $this->Poster_model->count_by_category($category_id);
        
        echo json_encode([
            'success' => true,
            'data' => $posters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'per_page' => $limit
            ]
        ]);
    }
}