<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webhook Log Model
 * 
 * Handles logging of all webhook events for debugging and audit
 * 
 * @author BannerBox
 * @version 1.0.0
 */
class Webhook_log_model extends CI_Model {
    
    private $table = 'webhook_logs';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new webhook log entry
     * 
     * @param array $data Log data
     * @return int|bool Log ID or false on failure
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Update log entry
     * 
     * @param int $id Log ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $this->db->where('log_id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Get log by ID
     * 
     * @param int $id Log ID
     * @return array|null Log record
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['log_id' => $id])->row_array();
    }
    
    /**
     * Get recent webhook logs
     * 
     * @param int $limit Number of logs to fetch
     * @return array List of logs
     */
    public function get_recent($limit = 50) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get logs by event type
     * 
     * @param string $event_type Event type
     * @param int $limit Number of logs to fetch
     * @return array List of logs
     */
    public function get_by_event_type($event_type, $limit = 50) {
        $this->db->where('event_type', $event_type);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get failed/invalid webhook logs
     * 
     * @param int $limit Number of logs to fetch
     * @return array List of failed logs
     */
    public function get_failed($limit = 50) {
        $this->db->where('is_valid', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get unprocessed webhook logs
     * 
     * @param int $limit Number of logs to fetch
     * @return array List of unprocessed logs
     */
    public function get_unprocessed($limit = 50) {
        $this->db->where('processed', 0);
        $this->db->where('is_valid', 1);
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Mark log as processed
     * 
     * @param int $id Log ID
     * @return bool Success status
     */
    public function mark_processed($id) {
        return $this->update($id, ['processed' => 1]);
    }
    
    /**
     * Get webhook statistics
     * 
     * @return array Statistics
     */
    public function get_stats() {
        $stats = [];
        
        // Total webhooks received
        $stats['total'] = $this->db->count_all($this->table);
        
        // Valid webhooks
        $this->db->where('is_valid', 1);
        $stats['valid'] = $this->db->count_all_results($this->table);
        
        // Invalid webhooks
        $this->db->where('is_valid', 0);
        $stats['invalid'] = $this->db->count_all_results($this->table);
        
        // Processed webhooks
        $this->db->where('processed', 1);
        $stats['processed'] = $this->db->count_all_results($this->table);
        
        // Webhooks today
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $stats['today'] = $this->db->count_all_results($this->table);
        
        return $stats;
    }
    
    /**
     * Clean old logs
     * 
     * Delete logs older than specified days
     * 
     * @param int $days_old Days to keep
     * @return int Number of deleted records
     */
    public function clean_old_logs($days_old = 30) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days_old} days"));
        
        $this->db->where('created_at <', $cutoff);
        $this->db->delete($this->table);
        
        return $this->db->affected_rows();
    }
    
    /**
     * Delete log entry
     * 
     * @param int $id Log ID
     * @return bool Success status
     */
    public function delete($id) {
        $this->db->where('log_id', $id);
        return $this->db->delete($this->table);
    }
}
