<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TopCategory_model extends CI_Model {
    
    protected $table = 'top_categories';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function getAll() {
        $this->db->select('tc.*, c.name, c.description, c.icon, c.color');
        $this->db->from($this->table . ' tc');
        $this->db->join('categories c', 'c.category_id = tc.category_id');
        $this->db->where('tc.is_active', 1);
        $this->db->order_by('tc.sort_order', 'ASC');
        var_dump($this->db->get()->result());
        die();
        return $this->db->get()->result();
    }
    
    public function add($categoryIds) {
        $maxOrder = $this->getMaxSortOrder();
        $data = [];
        foreach ($categoryIds as $index => $categoryId) {
            $data[] = [
                'category_id' => $categoryId,
                'sort_order' => $maxOrder + $index + 1
            ];
        }
        return $this->db->insert_batch($this->table, $data);
    }
    
    public function getMaxSortOrder() {
        $this->db->select_max('sort_order');
        $result = $this->db->get($this->table)->row();
        return $result->sort_order ?? 0;
    }
    
    public function updateOrder($id, $direction) {
        $current = $this->db->get_where($this->table, ['id' => $id])->row();
        if (!$current) return false;
        
        $newOrder = $direction === 'up' ? $current->sort_order - 1 : $current->sort_order + 1;
        
        // Swap with adjacent item
        $this->db->where('sort_order', $newOrder);
        $this->db->update($this->table, ['sort_order' => $current->sort_order]);
        
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['sort_order' => $newOrder]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }
    
    public function exists($categoryId) {
        return $this->db->get_where($this->table, ['category_id' => $categoryId])->num_rows() > 0;
    }
}
