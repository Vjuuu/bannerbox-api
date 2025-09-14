<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Poster_model extends CI_Model {
    
    private $table = 'posters';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all posters with pagination
     */
    public function get_all($page = 1, $limit = 20, $category_id = null, $search = null) {
        $offset = ($page - 1) * $limit;
        
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        
        if ($category_id) {
            $this->db->where('p.category_id', $category_id);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.description', $search);
            $this->db->or_like('p.tags', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get poster by ID
     */
    public function get_by_id($id) {
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->where('p.poster_id', $id);
        
        $query = $this->db->get();
        return $query->row_array();
    }
    
    /**
     * Create new poster
     */
    public function create($data) {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update poster
     */
    public function update($id, $data) {
        $this->db->where('poster_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Delete poster
     */
    public function delete($id) {
        $this->db->where('poster_id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Get posters by category
     */
    public function get_by_category($category_id, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->where('p.category_id', $category_id);
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get trending posters
     */
    public function get_trending($limit = 10, $days = 7) {
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->where('p.created_at >=', $date_from);
        $this->db->order_by('(p.view_count + p.download_count * 2)', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get featured posters
     */
    public function get_featured($limit = 10) {
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->where('p.is_featured', 1);
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get premium posters
     */
    public function get_premium($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->where('p.is_premium', 1);
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Search posters
     */
    public function search($query, $page = 1, $limit = 20) {
        return $this->get_all($page, $limit, null, $query);
    }
    
    /**
     * Count all posters
     */
    public function count_all($category_id = null, $search = null) {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('description', $search);
            $this->db->or_like('tags', $search);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Count posters by category
     */
    public function count_by_category($category_id) {
        $this->db->where('category_id', $category_id);
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Increment view count
     */
    public function increment_views($id) {
        $this->db->set('view_count', 'view_count + 1', FALSE);
        $this->db->where('poster_id', $id);
        return $this->db->update($this->table);
    }
    
    /**
     * Increment download count
     */
    public function increment_downloads($id) {
        $this->db->set('download_count', 'download_count + 1', FALSE);
        $this->db->where('poster_id', $id);
        return $this->db->update($this->table);
    }
    
    /**
     * Get recent posters
     */
    public function get_recent($limit = 10) {
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get popular posters
     */
    public function get_popular($limit = 10) {
        $this->db->select('p.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table . ' p');
        $this->db->join('categories c', 'p.category_id = c.category_id', 'left');
        $this->db->order_by('(p.view_count + p.download_count * 2)', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
}