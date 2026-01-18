<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Subscription Model
 * 
 * Handles all subscription-related database operations
 * 
 * @author BannerBox
 * @version 1.0.0
 */
class Subscription_model extends CI_Model {
    
    private $table = 'subscriptions';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new subscription record
     * 
     * @param array $data Subscription data
     * @return int|bool Subscription ID or false on failure
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update subscription record
     * 
     * @param int $id Subscription ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('subscription_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Get subscription by ID
     * 
     * @param int $id Subscription ID
     * @return array|null Subscription record
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['subscription_id' => $id])->row_array();
    }
    
    /**
     * Get all subscriptions for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Optional limit
     * @return array List of subscriptions
     */
    public function get_by_user($user_id, $limit = 50) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get active subscription for a user
     * 
     * @param int $user_id User ID
     * @return array|null Active subscription or null
     */
    public function get_active_by_user($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'active');
        $this->db->where('current_period_end >=', date('Y-m-d H:i:s'));
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->row_array();
    }
    
    /**
     * Get subscription by Razorpay subscription ID
     * 
     * @param string $razorpay_subscription_id Razorpay subscription ID
     * @return array|null Subscription record
     */
    public function get_by_razorpay_id($razorpay_subscription_id) {
        return $this->db->get_where($this->table, [
            'razorpay_subscription_id' => $razorpay_subscription_id
        ])->row_array();
    }
    
    /**
     * Check if user has any active subscription
     * 
     * @param int $user_id User ID
     * @return bool True if user has active subscription
     */
    public function has_active_subscription($user_id) {
        $subscription = $this->get_active_by_user($user_id);
        return !empty($subscription);
    }
    
    /**
     * Get expired subscriptions
     * 
     * Used for cleanup/notification cron jobs
     * 
     * @param int $limit Limit results
     * @return array List of expired subscriptions
     */
    public function get_expired() {
        $this->db->where('status', 'active');
        $this->db->where('current_period_end <', date('Y-m-d H:i:s'));
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get subscriptions expiring soon
     * 
     * @param int $days_before Number of days before expiry
     * @return array List of subscriptions expiring soon
     */
    public function get_expiring_soon($days_before = 3) {
        $expiry_date = date('Y-m-d H:i:s', strtotime("+{$days_before} days"));
        
        $this->db->where('status', 'active');
        $this->db->where('current_period_end <=', $expiry_date);
        $this->db->where('current_period_end >=', date('Y-m-d H:i:s'));
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Mark subscription as expired
     * 
     * @param int $id Subscription ID
     * @return bool Success status
     */
    public function mark_expired($id) {
        return $this->update($id, ['status' => 'expired']);
    }
    
    /**
     * Get pending subscription for a user (created but not authenticated)
     * 
     * @param int $user_id User ID
     * @return array|null Pending subscription or null
     */
    public function get_pending_by_user($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'created');
        $this->db->where('razorpay_subscription_id IS NOT NULL', null, false);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->row_array();
    }
    
    /**
     * Get subscription by user and status
     * 
     * @param int $user_id User ID
     * @param string $status Subscription status
     * @return array|null Subscription or null
     */
    public function get_by_user_and_status($user_id, $status) {
        $this->db->where('user_id', $user_id);
        $this->db->where('status', $status);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->row_array();
    }
    
    /**
     * Get subscription statistics
     * 
     * @return array Statistics
     */
    public function get_stats() {
        $stats = [];
        
        // Total subscriptions
        $stats['total'] = $this->db->count_all($this->table);
        
        // Active subscriptions
        $this->db->where('status', 'active');
        $this->db->where('current_period_end >=', date('Y-m-d H:i:s'));
        $stats['active'] = $this->db->count_all_results($this->table);
        
        // Cancelled subscriptions
        $this->db->where('status', 'cancelled');
        $stats['cancelled'] = $this->db->count_all_results($this->table);
        
        // Expired subscriptions
        $this->db->where('status', 'expired');
        $stats['expired'] = $this->db->count_all_results($this->table);
        
        // Revenue this month
        $this->db->select('SUM(amount) as revenue');
        $this->db->where('status', 'active');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $result = $this->db->get($this->table)->row_array();
        $stats['monthly_revenue'] = $result['revenue'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Delete subscription record
     * 
     * @param int $id Subscription ID
     * @return bool Success status
     */
    public function delete($id) {
        $this->db->where('subscription_id', $id);
        return $this->db->delete($this->table);
    }
}
