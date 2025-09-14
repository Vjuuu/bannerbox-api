<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    
    private $table = 'users';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all users
     */
    public function get_all() {
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    
    /**
     * Get user by ID
     */
    public function get_by_id($id) {
        $query = $this->db->get_where($this->table, array('user_id' => $id));
        return $query->row_array();
    }
    
    /**
     * Get user by email
     */
    public function get_by_email($email) {
        $query = $this->db->get_where($this->table, array('email' => $email));
        return $query->row_array();
    }
    
    /**
     * Get user by username
     */
    public function get_by_username($username) {
        $query = $this->db->get_where($this->table, array('username' => $username));
        return $query->row_array();
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $this->db->where('user_id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Check if email exists
     */
    public function email_exists($email, $exclude_id = null) {
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('user_id !=', $exclude_id);
        }
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
    
    /**
     * Check if username exists
     */
    public function username_exists($username, $exclude_id = null) {
        $this->db->where('username', $username);
        if ($exclude_id) {
            $this->db->where('user_id !=', $exclude_id);
        }
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
    
    /**
     * Get users by subscription type
     */
    public function get_by_subscription($subscription_type) {
        $query = $this->db->get_where($this->table, array('subscription_type' => $subscription_type));
        return $query->result_array();
    }
    
    /**
     * Count total users
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }
    
    /**
     * Get recent users
     */
    public function get_recent($limit = 10) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
}