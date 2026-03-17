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
    
    /**
     * Get users with active subscriptions
     */
    public function get_active_subscribers() {
        $this->db->where('subscription_status', 'active');
        $this->db->where('subscription_expires_at >=', date('Y-m-d H:i:s'));
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get users with expiring subscriptions
     * 
     * @param int $days_before Number of days before expiry
     */
    public function get_expiring_subscriptions($days_before = 3) {
        $expiry_date = date('Y-m-d H:i:s', strtotime("+{$days_before} days"));
        
        $this->db->where('subscription_status', 'active');
        $this->db->where('subscription_expires_at <=', $expiry_date);
        $this->db->where('subscription_expires_at >=', date('Y-m-d H:i:s'));
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get users with expired subscriptions that need updating
     */
    public function get_expired_subscriptions() {
        $this->db->where('subscription_status', 'active');
        $this->db->where('subscription_expires_at <', date('Y-m-d H:i:s'));
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Update expired subscriptions to basic
     * 
     * Call this from a cron job
     */
    public function expire_subscriptions() {
        $expired_users = $this->get_expired_subscriptions();
        $count = 0;
        
        foreach ($expired_users as $user) {
            $this->update($user['user_id'], [
                'subscription_type' => 'basic',
                'subscription_status' => 'expired'
            ]);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Check if user has premium access
     * 
     * @param int $user_id User ID
     * @return bool True if user has active premium subscription
     */
    public function has_premium_access($user_id) {
        $user = $this->get_by_id($user_id);
        
        if (!$user) {
            return false;
        }
        
        // Check if subscription is active and not expired
        if ($user['subscription_status'] === 'active' && 
            !empty($user['subscription_expires_at']) &&
            strtotime($user['subscription_expires_at']) > time()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get subscription statistics
     */
    public function get_subscription_stats() {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->count_all($this->table);
        
        // Active premium subscribers
        $this->db->where('subscription_status', 'active');
        $this->db->where('subscription_expires_at >=', date('Y-m-d H:i:s'));
        $stats['active_premium'] = $this->db->count_all_results($this->table);
        
        // Basic users
        $this->db->where('subscription_type', 'basic');
        $stats['basic_users'] = $this->db->count_all_results($this->table);
        
        // Expired subscriptions
        $this->db->where('subscription_status', 'expired');
        $stats['expired'] = $this->db->count_all_results($this->table);
        
        // Cancelled subscriptions
        $this->db->where('subscription_status', 'cancelled');
        $stats['cancelled'] = $this->db->count_all_results($this->table);
        
        return $stats;
    }
}