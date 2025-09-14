<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {
    
    private $table = 'categories';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all categories
     */
    public function get_all($active_only = false) {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Get category by ID
     */
    public function get_by_id($id) {
        $query = $this->db->get_where($this->table, array('category_id' => $id));
        return $query->row_array();
    }
    
    /**
     * Get category by name
     */
    public function get_by_name($name) {
        $query = $this->db->get_where($this->table, array('name' => $name));
        return $query->row_array();
    }
    
    /**
     * Create new category
     */
    public function create($data) {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update category
     */
    public function update($id, $data) {
        $this->db->where('category_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        $this->db->where('category_id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Get categories with poster count
     */
    public function get_with_poster_count() {
        $this->db->select('c.*, COUNT(p.poster_id) as poster_count');
        $this->db->from($this->table . ' c');
        $this->db->join('posters p', 'c.category_id = p.category_id', 'left');
        $this->db->where('c.is_active', 1);
        $this->db->group_by('c.category_id');
        $this->db->order_by('c.name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get active categories
     */
    public function get_active() {
        return $this->get_all(true);
    }
    
    /**
     * Check if category name exists
     */
    public function name_exists($name, $exclude_id = null) {
        $this->db->where('name', $name);
        if ($exclude_id) {
            $this->db->where('category_id !=', $exclude_id);
        }
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
    
    /**
     * Count total categories
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }
    
    /**
     * Get popular categories based on poster downloads
     */
    public function get_popular($limit = 10) {
        $this->db->select('c.*, COUNT(p.poster_id) as poster_count, SUM(p.download_count) as total_downloads');
        $this->db->from($this->table . ' c');
        $this->db->join('posters p', 'c.category_id = p.category_id', 'left');
        $this->db->where('c.is_active', 1);
        $this->db->group_by('c.category_id');
        $this->db->order_by('total_downloads', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get();
        return $query->result_array();
    }
}