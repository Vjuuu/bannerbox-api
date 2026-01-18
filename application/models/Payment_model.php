<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment Model
 * 
 * Handles all payment-related database operations
 * 
 * @author BannerBox
 * @version 1.0.0
 */
class Payment_model extends CI_Model {
    
    private $table = 'payments';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new payment record
     * 
     * @param array $data Payment data
     * @return int|bool Payment ID or false on failure
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update payment record
     * 
     * @param int $id Payment ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('payment_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Get payment by ID
     * 
     * @param int $id Payment ID
     * @return array|null Payment record
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['payment_id' => $id])->row_array();
    }
    
    /**
     * Get payment by Razorpay order ID
     * 
     * @param string $order_id Razorpay order ID
     * @return array|null Payment record
     */
    public function get_by_order_id($order_id) {
        return $this->db->get_where($this->table, ['razorpay_order_id' => $order_id])->row_array();
    }
    
    /**
     * Get payment by Razorpay payment ID
     * 
     * @param string $payment_id Razorpay payment ID
     * @return array|null Payment record
     */
    public function get_by_payment_id($payment_id) {
        return $this->db->get_where($this->table, ['razorpay_payment_id' => $payment_id])->row_array();
    }
    
    /**
     * Alias for get_by_payment_id (for clarity in webhook handler)
     * 
     * @param string $razorpay_payment_id Razorpay payment ID
     * @return array|null Payment record
     */
    public function get_by_razorpay_payment_id($razorpay_payment_id) {
        return $this->get_by_payment_id($razorpay_payment_id);
    }
    
    /**
     * Get all payments for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Optional limit
     * @return array List of payments
     */
    public function get_by_user($user_id, $limit = 50) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get payments for a subscription
     * 
     * @param int $subscription_id Subscription ID
     * @return array List of payments
     */
    public function get_by_subscription($subscription_id) {
        $this->db->where('subscription_id', $subscription_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get successful payments for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Optional limit
     * @return array List of successful payments
     */
    public function get_successful_by_user($user_id, $limit = 50) {
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'captured');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get failed payments for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Optional limit
     * @return array List of failed payments
     */
    public function get_failed_by_user($user_id, $limit = 50) {
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'failed');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get pending payments (created but not completed)
     * 
     * @param int $hours_old Payments older than this many hours
     * @return array List of pending payments
     */
    public function get_pending($hours_old = 24) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours_old} hours"));
        
        $this->db->where('status', 'created');
        $this->db->where('created_at <', $cutoff);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get payment statistics
     * 
     * @return array Statistics
     */
    public function get_stats() {
        $stats = [];
        
        // Total payments
        $stats['total'] = $this->db->count_all($this->table);
        
        // Successful payments
        $this->db->where('status', 'captured');
        $stats['successful'] = $this->db->count_all_results($this->table);
        
        // Failed payments
        $this->db->where('status', 'failed');
        $stats['failed'] = $this->db->count_all_results($this->table);
        
        // Total revenue
        $this->db->select('SUM(amount) as total_revenue');
        $this->db->where('status', 'captured');
        $result = $this->db->get($this->table)->row_array();
        $stats['total_revenue'] = $result['total_revenue'] ?? 0;
        
        // Revenue this month
        $this->db->select('SUM(amount) as monthly_revenue');
        $this->db->where('status', 'captured');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $result = $this->db->get($this->table)->row_array();
        $stats['monthly_revenue'] = $result['monthly_revenue'] ?? 0;
        
        // Revenue today
        $this->db->select('SUM(amount) as daily_revenue');
        $this->db->where('status', 'captured');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $result = $this->db->get($this->table)->row_array();
        $stats['daily_revenue'] = $result['daily_revenue'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get recent payments with user info
     * 
     * @param int $limit Number of payments to fetch
     * @return array List of payments with user info
     */
    public function get_recent_with_user($limit = 20) {
        $this->db->select('payments.*, users.email, users.full_name');
        $this->db->from($this->table);
        $this->db->join('users', 'users.user_id = payments.user_id', 'left');
        $this->db->order_by('payments.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }
    
    /**
     * Check if payment already exists
     * 
     * @param string $razorpay_payment_id Razorpay payment ID
     * @return bool True if payment exists
     */
    public function payment_exists($razorpay_payment_id) {
        $this->db->where('razorpay_payment_id', $razorpay_payment_id);
        return $this->db->count_all_results($this->table) > 0;
    }
    
    /**
     * Delete payment record
     * 
     * @param int $id Payment ID
     * @return bool Success status
     */
    public function delete($id) {
        $this->db->where('payment_id', $id);
        return $this->db->delete($this->table);
    }
}
