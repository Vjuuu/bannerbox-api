<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Plan Model
 * 
 * Handles CRUD operations for subscription plans
 * Plans link to Razorpay Plan IDs for recurring billing
 */
class Plan_model extends CI_Model
{
    private $table = 'subscription_plans';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Format plan data consistently
     * 
     * @param array $plan
     * @return array
     */
    private function format_plan($plan)
    {
        if (!$plan) return null;
        
        $plan['features'] = json_decode($plan['features'], true) ?: [];
        $plan['amount'] = (float) $plan['amount'];
        $plan['amount_in_paise'] = (int) ($plan['amount'] * 100);
        $plan['billing_period'] = $plan['billing_period'] ?? 'monthly';
        $plan['billing_interval'] = (int) ($plan['billing_interval'] ?? 1);
        $plan['duration_days'] = (int) ($plan['duration_days'] ?? 30);
        $plan['total_billing_cycles'] = isset($plan['total_billing_cycles']) ? (int) $plan['total_billing_cycles'] : null;
        
        return $plan;
    }

    /**
     * Get all active plans
     * 
     * @return array
     */
    public function get_all_active()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get($this->table);
        
        $plans = $query->result_array();
        
        // Format each plan
        foreach ($plans as &$plan) {
            $plan = $this->format_plan($plan);
        }
        
        return $plans;
    }

    /**
     * Get plan by ID
     * 
     * @param int $plan_id
     * @return array|null
     */
    public function get_by_id($plan_id)
    {
        $this->db->where('plan_id', $plan_id);
        $query = $this->db->get($this->table);
        return $this->format_plan($query->row_array());
    }

    /**
     * Get plan by code
     * 
     * @param string $plan_code
     * @return array|null
     */
    public function get_by_code($plan_code)
    {
        $this->db->where('plan_code', $plan_code);
        $query = $this->db->get($this->table);
        return $this->format_plan($query->row_array());
    }
    
    /**
     * Get plan by Razorpay Plan ID
     * 
     * @param string $razorpay_plan_id
     * @return array|null
     */
    public function get_by_razorpay_id($razorpay_plan_id)
    {
        $this->db->where('razorpay_plan_id', $razorpay_plan_id);
        $query = $this->db->get($this->table);
        return $this->format_plan($query->row_array());
    }

    /**
     * Get active plan by ID
     * 
     * @param int $plan_id
     * @return array|null
     */
    public function get_active_by_id($plan_id)
    {
        $this->db->where('plan_id', $plan_id);
        $this->db->where('is_active', 1);
        $query = $this->db->get($this->table);
        return $this->format_plan($query->row_array());
    }
    
    /**
     * Get plans that have Razorpay Plan IDs configured
     * 
     * @return array
     */
    public function get_razorpay_configured()
    {
        $this->db->where('is_active', 1);
        $this->db->where('razorpay_plan_id IS NOT NULL', null, false);
        $this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get($this->table);
        
        $plans = $query->result_array();
        foreach ($plans as &$plan) {
            $plan = $this->format_plan($plan);
        }
        
        return $plans;
    }

    /**
     * Create a new plan
     * 
     * @param array $data
     * @return int|false
     */
    public function create($data)
    {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Update a plan
     * 
     * @param int $plan_id
     * @param array $data
     * @return bool
     */
    public function update($plan_id, $data)
    {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('plan_id', $plan_id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Update Razorpay Plan ID for a local plan
     * 
     * @param int $plan_id
     * @param string $razorpay_plan_id
     * @return bool
     */
    public function set_razorpay_plan_id($plan_id, $razorpay_plan_id)
    {
        return $this->update($plan_id, ['razorpay_plan_id' => $razorpay_plan_id]);
    }

    /**
     * Deactivate a plan (soft delete)
     * 
     * @param int $plan_id
     * @return bool
     */
    public function deactivate($plan_id)
    {
        return $this->update($plan_id, ['is_active' => 0]);
    }

    /**
     * Activate a plan
     * 
     * @param int $plan_id
     * @return bool
     */
    public function activate($plan_id)
    {
        return $this->update($plan_id, ['is_active' => 1]);
    }
}
