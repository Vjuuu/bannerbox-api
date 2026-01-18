<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Razorpay Library
 * 
 * Handles Razorpay Subscriptions API for recurring UPI payments
 * 
 * Flow:
 * 1. Create a Plan in Razorpay (one-time, reuse for all users)
 * 2. Create a Subscription linking user to plan
 * 3. User authenticates via UPI mandate (autopay)
 * 4. Razorpay auto-charges monthly/yearly
 * 5. Webhooks update subscription status on each charge
 * 
 * @author BannerBox
 * @version 2.0.0 - Subscriptions API
 */
class Razorpay_library {
    
    private $CI;
    private $key_id;
    private $key_secret;
    private $api_url = 'https://api.razorpay.com/v1';
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->key_id = $this->CI->config->item('razorpay_key_id');
        $this->key_secret = $this->CI->config->item('razorpay_key_secret');
        
        // Validate credentials are configured
        if (empty($this->key_id) || $this->key_id === 'YOUR_RAZORPAY_KEY_ID') {
            log_message('error', 'Razorpay Key ID not configured in config.php');
        }
        if (empty($this->key_secret) || $this->key_secret === 'YOUR_RAZORPAY_KEY_SECRET') {
            log_message('error', 'Razorpay Key Secret not configured in config.php');
        }
    }
    
    // =========================================================================
    // PLANS API - Create recurring billing plans
    // =========================================================================
    
    /**
     * Create a Razorpay Plan
     * 
     * Plans define the billing amount and period. Create once, reuse for all subscribers.
     * 
     * @param string $name Plan name (e.g., "Premium Monthly")
     * @param int $amount Amount in paise (3000 = ₹30)
     * @param string $period Billing period: daily, weekly, monthly, yearly
     * @param int $interval Billing interval (1 = every period, 2 = every 2 periods)
     * @param string $description Optional description
     * @return array Plan details with 'id' or error
     */
    public function create_plan($name, $amount, $period = 'monthly', $interval = 1, $description = null) {
        $data = [
            'period' => $period,
            'interval' => $interval,
            'item' => [
                'name' => $name,
                'amount' => $amount, // Amount in paise
                'currency' => 'INR',
                'description' => $description ?? 'BannerBox ' . ucfirst($period) . ' Subscription'
            ]
        ];
        
        return $this->make_request('/plans', 'POST', $data);
    }
    
    /**
     * Fetch a Plan by ID
     * 
     * @param string $plan_id Razorpay Plan ID (plan_xxxxx)
     * @return array Plan details
     */
    public function fetch_plan($plan_id) {
        return $this->make_request('/plans/' . $plan_id, 'GET');
    }
    
    /**
     * List all Plans
     * 
     * @param int $count Number of plans to fetch (default 10, max 100)
     * @param int $skip Number of plans to skip
     * @return array List of plans
     */
    public function list_plans($count = 10, $skip = 0) {
        return $this->make_request('/plans?count=' . $count . '&skip=' . $skip, 'GET');
    }
    
    // =========================================================================
    // SUBSCRIPTIONS API - Manage recurring subscriptions
    // =========================================================================
    
    /**
     * Create a Subscription
     * 
     * Creates a new subscription for a user. Returns a short_url for payment.
     * 
     * @param string $plan_id Razorpay Plan ID (plan_xxxxx)
     * @param int $total_count Total billing cycles (null = infinite)
     * @param string $customer_id Razorpay Customer ID (optional, will create if not provided)
     * @param array $customer_details Array with 'name', 'email', 'contact' (if customer_id not provided)
     * @param array $notes Additional notes (user_id, etc.)
     * @param int $start_at Unix timestamp to start subscription (null = immediate)
     * @param int $expire_by Unix timestamp by which subscription must be authenticated
     * @return array Subscription details with 'id', 'short_url', 'status'
     */
    public function create_subscription($plan_id, $total_count = null, $customer_id = null, 
                                         $customer_details = [], $notes = [], 
                                         $start_at = null, $expire_by = null) {
        $data = [
            'plan_id' => $plan_id,
            'customer_notify' => 1, // Razorpay sends payment reminders
            'notes' => $notes
        ];
        
        // Total billing cycles - REQUIRED by Razorpay (either total_count or end_at)
        // If not specified, set to large number (120 = 10 years of monthly)
        if ($total_count !== null && $total_count > 0) {
            $data['total_count'] = $total_count;
        } else {
            // Default to 120 billing cycles (10 years for monthly, or large number for annual)
            $data['total_count'] = 120;
        }
        
        // Customer handling - either use existing customer_id or create new customer
        if (!empty($customer_id)) {
            // Use existing Razorpay customer
            $data['customer_id'] = $customer_id;
        } elseif (!empty($customer_details)) {
            // Create customer first, then use their ID
            $customer_name = $customer_details['name'] ?? '';
            $customer_email = $customer_details['email'] ?? '';
            $customer_contact = $customer_details['contact'] ?? '';
            
            if ($customer_email) {
                $customer_response = $this->create_customer($customer_name, $customer_email, $customer_contact);
                
                if (isset($customer_response['id'])) {
                    $data['customer_id'] = $customer_response['id'];
                }
            }
        }
        // If neither customer_id nor customer_details provided, Razorpay will collect details during checkout
        
        // Schedule subscription start (null = starts immediately after auth)
        if ($start_at !== null) {
            $data['start_at'] = $start_at;
        }
        
        // Expire subscription if not authenticated by this time
        if ($expire_by !== null) {
            $data['expire_by'] = $expire_by;
        } else {
            // Default: expire in 24 hours if not authenticated
            $data['expire_by'] = time() + (24 * 60 * 60);
        }
        
        return $this->make_request('/subscriptions', 'POST', $data);
    }
    
    /**
     * Fetch a Subscription by ID
     * 
     * @param string $subscription_id Razorpay Subscription ID (sub_xxxxx)
     * @return array Subscription details
     */
    public function fetch_subscription($subscription_id) {
        return $this->make_request('/subscriptions/' . $subscription_id, 'GET');
    }
    
    /**
     * List all Subscriptions
     * 
     * @param array $filters Optional filters: plan_id, status, count, skip
     * @return array List of subscriptions
     */
    public function list_subscriptions($filters = []) {
        $query = http_build_query($filters);
        return $this->make_request('/subscriptions?' . $query, 'GET');
    }
    
    /**
     * Cancel a Subscription
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param bool $cancel_at_cycle_end If true, access continues until cycle end
     * @return array Updated subscription details
     */
    public function cancel_subscription($subscription_id, $cancel_at_cycle_end = true) {
        return $this->make_request(
            '/subscriptions/' . $subscription_id . '/cancel',
            'POST',
            ['cancel_at_cycle_end' => $cancel_at_cycle_end ? 1 : 0]
        );
    }
    
    /**
     * Pause a Subscription
     * 
     * Pauses the subscription - no charges will be made until resumed
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param string $pause_at 'now' or 'cycle_end'
     * @return array Updated subscription details
     */
    public function pause_subscription($subscription_id, $pause_at = 'now') {
        return $this->make_request(
            '/subscriptions/' . $subscription_id . '/pause',
            'POST',
            ['pause_at' => $pause_at]
        );
    }
    
    /**
     * Resume a Paused Subscription
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param string $resume_at 'now' or 'cycle_end'
     * @return array Updated subscription details
     */
    public function resume_subscription($subscription_id, $resume_at = 'now') {
        return $this->make_request(
            '/subscriptions/' . $subscription_id . '/resume',
            'POST',
            ['resume_at' => $resume_at]
        );
    }
    
    /**
     * Update a Subscription
     * 
     * Can update plan_id (upgrade/downgrade), quantity, schedule changes, etc.
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param array $updates Fields to update
     * @return array Updated subscription details
     */
    public function update_subscription($subscription_id, $updates) {
        return $this->make_request(
            '/subscriptions/' . $subscription_id,
            'PATCH',
            $updates
        );
    }
    
    /**
     * Get Pending Updates for a Subscription
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @return array Pending updates if any
     */
    public function fetch_pending_updates($subscription_id) {
        return $this->make_request(
            '/subscriptions/' . $subscription_id . '/retrieve_scheduled_changes',
            'GET'
        );
    }
    
    /**
     * Cancel Pending Updates
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @return array Updated subscription details
     */
    public function cancel_pending_updates($subscription_id) {
        return $this->make_request(
            '/subscriptions/' . $subscription_id . '/cancel_scheduled_changes',
            'POST'
        );
    }
    
    // =========================================================================
    // INVOICES API - For subscription invoices
    // =========================================================================
    
    /**
     * Fetch all Invoices for a Subscription
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @return array List of invoices
     */
    public function fetch_subscription_invoices($subscription_id) {
        return $this->make_request('/invoices?subscription_id=' . $subscription_id, 'GET');
    }
    
    // =========================================================================
    // CUSTOMERS API
    // =========================================================================
    
    /**
     * Create a Customer
     * 
     * @param string $name Customer name
     * @param string $email Customer email
     * @param string $contact Customer phone
     * @return array Customer details with 'id'
     */
    public function create_customer($name, $email, $contact = null) {
        $data = [
            'name' => $name,
            'email' => $email,
            'contact' => $contact,
            'fail_existing' => 0 // Return existing customer if already exists
        ];
        
        return $this->make_request('/customers', 'POST', $data);
    }
    
    /**
     * Fetch Customer by ID
     * 
     * @param string $customer_id Razorpay Customer ID
     * @return array Customer details
     */
    public function fetch_customer($customer_id) {
        return $this->make_request('/customers/' . $customer_id, 'GET');
    }
    
    // =========================================================================
    // PAYMENTS API
    // =========================================================================
    
    /**
     * Fetch Payment Details
     * 
     * @param string $payment_id Razorpay Payment ID (pay_xxxxx)
     * @return array Payment details
     */
    public function fetch_payment($payment_id) {
        return $this->make_request('/payments/' . $payment_id, 'GET');
    }
    
    /**
     * Create Refund for a Payment
     * 
     * @param string $payment_id Razorpay Payment ID
     * @param int $amount Amount to refund in paise (null = full refund)
     * @param array $notes Additional notes
     * @return array Refund details
     */
    public function create_refund($payment_id, $amount = null, $notes = []) {
        $data = ['notes' => $notes];
        
        if ($amount !== null) {
            $data['amount'] = $amount;
        }
        
        return $this->make_request('/payments/' . $payment_id . '/refund', 'POST', $data);
    }
    
    // =========================================================================
    // VERIFICATION
    // =========================================================================
    
    /**
     * Verify Subscription Authentication
     * 
     * Verifies the subscription auth response is genuine
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param string $payment_id Razorpay Payment ID
     * @param string $signature Signature from Razorpay response
     * @return bool True if signature is valid
     */
    public function verify_subscription_signature($subscription_id, $payment_id, $signature) {
        $generated_signature = hash_hmac(
            'sha256',
            $payment_id . '|' . $subscription_id,
            $this->key_secret
        );
        
        return hash_equals($generated_signature, $signature);
    }
    
    /**
     * Verify Webhook Signature
     * 
     * Verifies that the webhook payload is authentic
     * 
     * @param string $payload Raw request body
     * @param string $signature Signature from X-Razorpay-Signature header
     * @return bool True if signature is valid
     */
    public function verify_webhook_signature($payload, $signature) {
        $webhook_secret = $this->CI->config->item('razorpay_webhook_secret');
        $generated_signature = hash_hmac('sha256', $payload, $webhook_secret);
        
        return hash_equals($generated_signature, $signature);
    }
    
    // =========================================================================
    // CHECKOUT HELPERS
    // =========================================================================
    
    /**
     * Get Subscription Checkout Options
     * 
     * Returns configuration for Razorpay checkout to authenticate a subscription
     * 
     * @param string $subscription_id Razorpay Subscription ID
     * @param array $user User details
     * @return array Checkout configuration for frontend
     */
    public function get_subscription_checkout_options($subscription_id, $user) {
        return [
            'key' => $this->key_id,
            'subscription_id' => $subscription_id,
            'name' => 'BannerBox',
            'description' => 'Premium Subscription - Auto-renews monthly/yearly',
            'image' => base_url('uploads/logos/bannerbox-logo.png'),
            'prefill' => [
                'name' => $user['full_name'] ?? $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'contact' => $user['phone'] ?? ''
            ],
            'notes' => [
                'user_id' => (string)($user['user_id'] ?? $user['id'] ?? '')
            ],
            'theme' => [
                'color' => '#3B82F6'
            ],
            'modal' => [
                'confirm_close' => true,
                'escape' => false,
                'animation' => true
            ],
            // Prefer UPI for autopay
            'config' => [
                'display' => [
                    'blocks' => [
                        'banks' => [
                            'name' => 'Pay via UPI Autopay',
                            'instruments' => [
                                ['method' => 'upi', 'flows' => ['collect', 'intent']]
                            ]
                        ]
                    ],
                    'sequence' => ['block.banks'],
                    'preferences' => [
                        'show_default_blocks' => true
                    ]
                ]
            ],
            // Handler functions (frontend should implement these)
            'handler' => [
                'onPaymentSuccess' => 'handleSubscriptionSuccess',
                'onPaymentError' => 'handleSubscriptionError'
            ]
        ];
    }
    
    /**
     * Get Key ID (for frontend)
     * 
     * @return string Razorpay Key ID
     */
    public function get_key_id() {
        return $this->key_id;
    }
    
    // =========================================================================
    // HTTP CLIENT
    // =========================================================================
    
    /**
     * Make HTTP Request to Razorpay API
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param array $data Request data
     * @return array Response data or error
     */
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = $this->api_url . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key_id . ':' . $this->key_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Disable SSL verification for local development
        // In production, always verify SSL certificates
        $is_local = (
            strpos(base_url(), 'localhost') !== false || 
            strpos(base_url(), '.test') !== false ||
            strpos(base_url(), '127.0.0.1') !== false ||
            ENVIRONMENT === 'development'
        );
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$is_local);
        if ($is_local) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log the request for debugging
        log_message('debug', 'Razorpay API Request: ' . $method . ' ' . $endpoint);
        log_message('debug', 'Razorpay API Response Code: ' . $http_code);
        
        if ($error) {
            log_message('error', 'Razorpay API Error: ' . $error);
            return [
                'error' => true,
                'message' => 'Connection error: ' . $error
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = $decoded['error']['description'] ?? 'Unknown error';
            $error_code = $decoded['error']['code'] ?? null;
            
            // Better error messages for common issues
            if ($http_code == 401 || $http_code == 400) {
                if (strpos($error_message, 'authentication') !== false || strpos($error_message, 'API key') !== false) {
                    $error_message = 'Razorpay API credentials are invalid or not configured. Please check your config.php file and add valid API keys from https://dashboard.razorpay.com/app/keys';
                }
            }
            
            log_message('error', 'Razorpay API Error: ' . $error_message . ' (Code: ' . $error_code . ', HTTP: ' . $http_code . ')');
            log_message('debug', 'Razorpay Response: ' . $response);
            
            return [
                'error' => true,
                'message' => $error_message,
                'code' => $error_code,
                'http_code' => $http_code
            ];
        }
        
        return $decoded;
    }
}
