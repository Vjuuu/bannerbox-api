<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Subscription Controller
 * 
 * Handles Razorpay SUBSCRIPTIONS API for recurring UPI payments
 * NOT one-time orders - auto-renews monthly/yearly
 * 
 * Uses only JWT token authentication (no API key required)
 * 
 * Flow:
 * 1. GET /plans - User sees available plans
 * 2. POST /subscribe - Creates Razorpay subscription, returns payment link
 * 3. User completes UPI mandate auth on Razorpay page
 * 4. POST /authenticate - Verifies subscription auth (optional, webhook handles this)
 * 5. Webhooks handle: subscription.authenticated, subscription.charged, etc.
 * 6. GET /status - Check current subscription
 * 7. POST /cancel - Cancel recurring subscription
 * 
 * Endpoints:
 * - GET  /api/subscription/plans       - Get available plans
 * - POST /api/subscription/subscribe   - Create subscription (returns payment URL)
 * - POST /api/subscription/authenticate - Verify subscription auth
 * - GET  /api/subscription/status      - Get current subscription status
 * - POST /api/subscription/cancel      - Cancel subscription
 * - POST /api/subscription/pause       - Pause subscription
 * - POST /api/subscription/resume      - Resume paused subscription
 * - GET  /api/subscription/history     - Get payment history
 * - POST /api/subscription/sync        - Sync subscription from Razorpay
 * 
 * Admin Endpoints:
 * - POST /api/subscription/create-plan - Create Razorpay plan (admin only)
 * 
 * @author BannerBox
 * @version 3.0.0 - Razorpay Subscriptions API
 */
class Subscription extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Subscription_model', 'Payment_model', 'User_model', 'Plan_model']);
        $this->load->library(['jwt_library', 'razorpay_library']);
        $this->load->helper('url');
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }
    
    /**
     * Validate JWT Token
     * 
     * @return array User data from token
     */
    private function validate_jwt() {
        $auth_header = $this->input->get_request_header('Authorization', TRUE);
        
        if (!$auth_header) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authorization header missing']);
            exit;
        }
        
        $token = str_replace('Bearer ', '', $auth_header);
        $user_data = $this->jwt_library->get_user_from_token($token);
        
        if (!$user_data) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
        
        return $user_data;
    }
    
    /**
     * Get available subscription plans
     * 
     * GET /api/subscription/plans
     * 
     * Returns all active plans with pricing and features.
     * Public endpoint - no authentication required.
     */
    public function plans() {
        // Fetch plans from database
        $plans = $this->Plan_model->get_all_active();
        
        // Format plans for API response
        $formatted_plans = array_map(function($plan) {
            return [
                'id' => (int) $plan['plan_id'],
                'code' => $plan['plan_code'],
                'razorpay_plan_id' => $plan['razorpay_plan_id'],
                'name' => $plan['name'],
                'description' => $plan['description'],
                'amount' => (float) $plan['amount'],
                'amount_display' => '₹' . number_format($plan['amount'], 0),
                'currency' => $plan['currency'],
                'billing_period' => $plan['billing_period'],
                'billing_interval' => (int) $plan['billing_interval'],
                'duration_days' => (int) $plan['duration_days'],
                'features' => $plan['features'],
                'is_recurring' => true,
                'auto_renews' => $plan['billing_period'] === 'monthly' ? 'Monthly' : 'Yearly'
            ];
        }, $plans);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'plans' => $formatted_plans,
                'note' => 'All plans auto-renew via UPI autopay. Cancel anytime.'
            ]
        ]);
    }
    
    /**
     * Create a new subscription
     * 
     * POST /api/subscription/subscribe
     * Body: { "plan_id": 1 }
     * 
     * Creates a Razorpay subscription and returns payment URL for UPI mandate.
     * User must complete payment on Razorpay's hosted page.
     * 
     * Response includes:
     * - subscription_id: Our local subscription ID
     * - razorpay_subscription_id: Razorpay's subscription ID
     * - short_url: Razorpay hosted payment page URL (redirect user here)
     * - checkout_options: For inline checkout integration (optional)
     */
    public function subscribe() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Get JSON input
        $input = json_decode(trim(file_get_contents('php://input')), true);
        $plan_id = $input['plan_id'] ?? null;
        
        // Validate plan_id is provided
        if (!$plan_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'plan_id is required']);
            return;
        }
        
        // Get plan from database
        $plan = $this->Plan_model->get_active_by_id($plan_id);
        
        if (!$plan) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plan not found or inactive']);
            return;
        }
        
        // Check if plan has Razorpay plan ID configured
        if (empty($plan['razorpay_plan_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'This plan is not yet configured for payments. Please contact support.',
                'error_code' => 'PLAN_NOT_CONFIGURED'
            ]);
            return;
        }
        
        // Get user details
        $user = $this->User_model->get_by_id($user_data['user_id']);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Check if user already has active subscription
        $active_sub = $this->Subscription_model->get_active_by_user($user['user_id']);
        if ($active_sub && in_array($active_sub['status'], ['active', 'authenticated'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'You already have an active subscription. Cancel it first to subscribe to a different plan.',
                'data' => [
                    'subscription_status' => $active_sub['status'],
                    'current_period_end' => $active_sub['current_period_end'],
                    'razorpay_subscription_id' => $active_sub['razorpay_subscription_id']
                ]
            ]);
            return;
        }
        
        // Check for pending subscriptions (created but not authenticated)
        // $pending_sub = $this->Subscription_model->get_pending_by_user($user['user_id']);
        // if ($pending_sub) {
        //     // Return existing pending subscription instead of creating new one
        //     echo json_encode([
        //         'success' => true,
        //         'message' => 'You have a pending subscription. Complete the payment to activate.',
        //         'data' => [
        //             'subscription_id' => $pending_sub['subscription_id'],
        //             'razorpay_subscription_id' => $pending_sub['razorpay_subscription_id'],
        //             'short_url' => $pending_sub['razorpay_short_url'],
        //             'status' => 'pending_authentication',
        //             'plan' => [
        //                 'id' => $plan['plan_id'],
        //                 'name' => $plan['name'],
        //                 'amount' => $plan['amount'],
        //                 'billing_period' => $plan['billing_period']
        //             ]
        //         ]
        //     ]);
        //     return;
        // }
        
        // Create Razorpay subscription
        $customer_details = [
            'name' => $user['full_name'] ?? $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'contact' => $user['phone'] ?? ''
        ];
        
        $razorpay_subscription = $this->razorpay_library->create_subscription(
            $plan['razorpay_plan_id'],
            $plan['total_billing_cycles'], // null for infinite
            null, // customer_id (let library create new customer)
            $customer_details,
            [
                'user_id' => (string) $user['user_id'],
                'plan_id' => (string) $plan['plan_id'],
                'plan_code' => $plan['plan_code']
            ]
        );
        
        if (isset($razorpay_subscription['error'])) {
            log_message('error', 'Razorpay subscription creation failed: ' . $razorpay_subscription['message']);
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create subscription. Please try again.',
                'debug' => ENVIRONMENT === 'development' ? $razorpay_subscription['message'] : null
            ]);
            return;
        }
        
        // Create local subscription record
        $subscription_id = $this->Subscription_model->create([
            'user_id' => $user['user_id'],
            'plan_id' => $plan['plan_id'],
            'razorpay_subscription_id' => $razorpay_subscription['id'],
            'razorpay_short_url' => $razorpay_subscription['short_url'] ?? null,
            'status' => 'created',
            'amount' => $plan['amount'],
            'currency' => $plan['currency'],
            'billing_period' => $plan['billing_period'],
            'charge_at' => isset($razorpay_subscription['charge_at']) 
                ? date('Y-m-d H:i:s', $razorpay_subscription['charge_at']) 
                : null
        ]);
        
        // Update user subscription status to pending
        $this->User_model->update($user['user_id'], [
            'subscription_status' => 'pending'
        ]);
        
        // Get checkout options for inline checkout (optional, frontend can use short_url instead)
        $checkout_options = $this->razorpay_library->get_subscription_checkout_options(
            $razorpay_subscription['id'],
            $user
        );
        
        log_message('info', 'Subscription created for user ' . $user['user_id'] . ': ' . $razorpay_subscription['id']);
        
        // Build checkout URL for our hosted payment page
        $checkout_url = base_url('api/subscription/checkout/' . $subscription_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription created. Complete payment to activate.',
            'data' => [
                'subscription_id' => $subscription_id,
                'razorpay_subscription_id' => $razorpay_subscription['id'],
                'short_url' => $razorpay_subscription['short_url'], // Razorpay hosted page
                'checkout_url' => $checkout_url, // Our hosted page with redirect
                'status' => $razorpay_subscription['status'],
                'plan' => [
                    'id' => $plan['plan_id'],
                    'name' => $plan['name'],
                    'amount' => $plan['amount'],
                    'amount_display' => '₹' . number_format($plan['amount'], 0),
                    'billing_period' => $plan['billing_period']
                ],
                'checkout_options' => $checkout_options,
                'key_id' => $this->razorpay_library->get_key_id(),
                'instructions' => [
                    'checkout_url' => 'Redirect user to checkout_url for complete payment flow with redirect back',
                    'short_url' => 'Or use Razorpay hosted page (no redirect back)',
                    'inline' => 'Or use checkout_options with Razorpay.js in your frontend'
                ]
            ]
        ]);
    }
    
    /**
     * Hosted Checkout Page
     * 
     * GET /api/subscription/checkout/:subscription_id
     * 
     * Displays a payment page with Razorpay.js integration.
     * After payment, redirects to callback which then redirects to frontend.
     */
    public function checkout($subscription_id = null) {
        // Override JSON content type for HTML view
        header('Content-Type: text/html; charset=utf-8');
        
        if (!$subscription_id) {
            show_error('Invalid subscription', 400);
            return;
        }
        
        // Get subscription from database
        $subscription = $this->Subscription_model->get_by_id($subscription_id);
        
        if (!$subscription) {
            show_error('Subscription not found', 404);
            return;
        }
        
        // Check if already active
        if ($subscription['status'] === 'active') {
            redirect($this->config->item('frontend_url') . '/dashboard?message=already_subscribed');
            return;
        }
        
        // Get plan details
        $plan = $this->Plan_model->get_by_id($subscription['plan_id']);
        
        if (!$plan) {
            show_error('Plan not found', 404);
            return;
        }
        
        // Decode features if it's JSON string
        if (is_string($plan['features'])) {
            $plan['features'] = json_decode($plan['features'], true) ?: [];
        }
        
        // Get user details
        $user = $this->User_model->get_by_id($subscription['user_id']);
        
        // Get checkout options
        $checkout_options = $this->razorpay_library->get_subscription_checkout_options(
            $subscription['razorpay_subscription_id'],
            $user ?: []
        );
        
        // Prepare data for view
        $data = [
            'plan' => $plan,
            'subscription_data' => [
                'subscription_id' => $subscription['subscription_id'],
                'razorpay_subscription_id' => $subscription['razorpay_subscription_id']
            ],
            'checkout_options' => $checkout_options,
            'callback_url' => base_url('api/subscription/callback'),
            'frontend_success_url' => $this->config->item('payment_success_url') ?: $this->config->item('frontend_url') . '/payment-status',
            'frontend_failure_url' => $this->config->item('payment_failure_url') ?: $this->config->item('frontend_url') . '/payment-status',
            'auto_open' => true // Auto-open Razorpay checkout
        ];
        
        // Load checkout view
        $this->load->view('payment/checkout', $data);
    }
    
    /**
     * Payment Callback
     * 
     * GET /api/subscription/callback
     * 
     * Called after Razorpay payment completion. Verifies payment and redirects to frontend.
     */
    public function callback() {
        // Get payment details from query string (GET) or form data (POST)
        $razorpay_payment_id = $this->input->get_post('razorpay_payment_id');
        $razorpay_subscription_id = $this->input->get_post('razorpay_subscription_id');
        $razorpay_signature = $this->input->get_post('razorpay_signature');
        
        $frontend_url = $this->config->item('frontend_url') ?: 'http://localhost:3000';
        $success_url = $this->config->item('payment_success_url') ?: $frontend_url . '/payment-status';
        $failure_url = $this->config->item('payment_failure_url') ?: $frontend_url . '/payment-status';
        
        // If no payment details, redirect to failure
        if (!$razorpay_payment_id || !$razorpay_subscription_id || !$razorpay_signature) {
            $params = http_build_query([
                'status' => 'failed',
                'message' => 'Payment details missing'
            ]);
            redirect($failure_url . '?' . $params);
            return;
        }
        
        // Verify signature
        $is_valid = $this->razorpay_library->verify_subscription_signature(
            $razorpay_subscription_id,
            $razorpay_payment_id,
            $razorpay_signature
        );
        
        if (!$is_valid) {
            log_message('error', 'Payment signature verification failed for subscription: ' . $razorpay_subscription_id);
            $params = http_build_query([
                'status' => 'failed',
                'message' => 'Payment verification failed'
            ]);
            redirect($failure_url . '?' . $params);
            return;
        }
        
        // Get subscription from database
        $subscription = $this->Subscription_model->get_by_razorpay_id($razorpay_subscription_id);
        
        if (!$subscription) {
            $params = http_build_query([
                'status' => 'failed',
                'message' => 'Subscription not found'
            ]);
            redirect($failure_url . '?' . $params);
            return;
        }
        
        // Fetch subscription details from Razorpay
        $rzp_subscription = $this->razorpay_library->fetch_subscription($razorpay_subscription_id);
        
        if (isset($rzp_subscription['error'])) {
            log_message('error', 'Failed to fetch Razorpay subscription: ' . $rzp_subscription['message']);
        }
        
        // Get plan for duration calculation
        $plan = $this->Plan_model->get_by_id($subscription['plan_id']);
        $duration_days = $plan ? (int)$plan['duration_days'] : 30;
        
        // Calculate period dates
        $current_period_start = date('Y-m-d H:i:s');
        $current_period_end = date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
        
        // Use Razorpay dates if available
        if (!empty($rzp_subscription['current_start'])) {
            $current_period_start = date('Y-m-d H:i:s', $rzp_subscription['current_start']);
        }
        if (!empty($rzp_subscription['current_end'])) {
            $current_period_end = date('Y-m-d H:i:s', $rzp_subscription['current_end']);
        }
        
        // Update subscription status
        $this->Subscription_model->update($subscription['subscription_id'], [
            'status' => 'active',
            'current_period_start' => $current_period_start,
            'current_period_end' => $current_period_end,
            'paid_count' => 1,
            'charge_at' => !empty($rzp_subscription['charge_at']) 
                ? date('Y-m-d H:i:s', $rzp_subscription['charge_at']) 
                : $current_period_end
        ]);
        
        // Update user subscription status
        $this->User_model->update($subscription['user_id'], [
            'subscription_type' => 'premium',
            'subscription_status' => 'active',
            'subscription_expires_at' => $current_period_end
        ]);
        
        // Record payment
        $this->Payment_model->create([
            'user_id' => $subscription['user_id'],
            'subscription_id' => $subscription['subscription_id'],
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_subscription_id' => $razorpay_subscription_id,
            'amount' => $plan ? $plan['amount'] : $subscription['amount'],
            'currency' => 'INR',
            'method' => 'upi',
            'status' => 'captured',
            'is_recurring' => 0,
            'billing_cycle' => 1
        ]);
        
        log_message('info', 'Payment callback successful for subscription: ' . $razorpay_subscription_id);
        
        // Redirect to frontend with success parameters
        $params = http_build_query([
            'status' => 'success',
            'subscription_id' => $subscription['subscription_id'],
            'plan_id' => $plan ? $plan['plan_id'] : null,
            'plan_name' => $plan ? $plan['name'] : 'Premium',
            'plan_amount' => $plan ? $plan['amount'] : $subscription['amount'],
            'billing_period' => $plan ? $plan['billing_period'] : 'monthly',
            'expires_at' => $current_period_end,
            'payment_id' => $razorpay_payment_id
        ]);
        
        redirect($success_url . '?' . $params);
    }
    
    /**
     * Show payment result page
     */
    private function show_payment_result($status, $message, $redirect_url, $subscription_data = []) {
        // Override JSON content type for HTML view
        header('Content-Type: text/html; charset=utf-8');
        
        $data = [
            'status' => $status,
            'message' => $message,
            'redirect_url' => $redirect_url,
            'subscription' => $subscription_data,
            'auto_redirect' => true,
            'redirect_delay' => 5
        ];
        
        // Add status to redirect URL
        $separator = strpos($redirect_url, '?') !== false ? '&' : '?';
        $data['redirect_url'] = $redirect_url . $separator . 'status=' . $status;
        
        if ($status === 'success' && !empty($subscription_data['expires_at'])) {
            $data['redirect_url'] .= '&expires=' . urlencode($subscription_data['expires_at']);
        } elseif ($status === 'failed') {
            $data['redirect_url'] .= '&message=' . urlencode($message);
        }
        
        $this->load->view('payment/result', $data);
    }
    
    /**
     * Verify subscription authentication
     * 
     * POST /api/subscription/authenticate
     * Body: { 
     *   "razorpay_subscription_id": "sub_xxxxx",
     *   "razorpay_payment_id": "pay_xxxxx",
     *   "razorpay_signature": "xxxxx"
     * }
     * 
     * Called after user completes UPI mandate auth.
     * Note: Webhook subscription.authenticated also handles this automatically.
     */
    public function authenticate() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(trim(file_get_contents('php://input')), true);
        
        $razorpay_subscription_id = $input['razorpay_subscription_id'] ?? null;
        $razorpay_payment_id = $input['razorpay_payment_id'] ?? null;
        $razorpay_signature = $input['razorpay_signature'] ?? null;
        
        if (!$razorpay_subscription_id || !$razorpay_payment_id || !$razorpay_signature) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        // Get subscription record
        $subscription = $this->Subscription_model->get_by_razorpay_id($razorpay_subscription_id);
        
        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Subscription not found']);
            return;
        }
        
        // Verify the subscription belongs to this user
        if ($subscription['user_id'] != $user_data['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Check if already activated
        if ($subscription['status'] === 'active') {
            echo json_encode([
                'success' => true,
                'message' => 'Subscription already active',
                'data' => [
                    'subscription_status' => 'active',
                    'current_period_end' => $subscription['current_period_end']
                ]
            ]);
            return;
        }
        
        // Verify signature
        $is_valid = $this->razorpay_library->verify_subscription_signature(
            $razorpay_subscription_id,
            $razorpay_payment_id,
            $razorpay_signature
        );
        
        if (!$is_valid) {
            log_message('error', 'Subscription signature verification failed: ' . $razorpay_subscription_id);
            
            $this->Subscription_model->update($subscription['subscription_id'], [
                'auth_attempts' => $subscription['auth_attempts'] + 1
            ]);
            
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Signature verification failed']);
            return;
        }
        
        // Fetch subscription details from Razorpay
        $razorpay_sub = $this->razorpay_library->fetch_subscription($razorpay_subscription_id);
        
        if (isset($razorpay_sub['error'])) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to verify subscription with Razorpay']);
            return;
        }
        
        // Get plan details
        $plan = $this->Plan_model->get_by_id($subscription['plan_id']);
        $duration_days = $plan ? $plan['duration_days'] : 30;
        
        // Calculate period dates
        $current_start = isset($razorpay_sub['current_start']) 
            ? date('Y-m-d H:i:s', $razorpay_sub['current_start']) 
            : date('Y-m-d H:i:s');
        $current_end = isset($razorpay_sub['current_end']) 
            ? date('Y-m-d H:i:s', $razorpay_sub['current_end']) 
            : date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
        $charge_at = isset($razorpay_sub['charge_at']) 
            ? date('Y-m-d H:i:s', $razorpay_sub['charge_at']) 
            : null;
        
        // Update subscription status
        $this->Subscription_model->update($subscription['subscription_id'], [
            'status' => $razorpay_sub['status'] ?? 'authenticated',
            'current_period_start' => $current_start,
            'current_period_end' => $current_end,
            'charge_at' => $charge_at,
            'paid_count' => $razorpay_sub['paid_count'] ?? 1
        ]);
        
        // Record the first payment
        $this->Payment_model->create([
            'user_id' => $subscription['user_id'],
            'subscription_id' => $subscription['subscription_id'],
            'razorpay_payment_id' => $razorpay_payment_id,
            'amount' => $subscription['amount'],
            'currency' => $subscription['currency'],
            'method' => 'upi',
            'status' => 'captured',
            'is_recurring' => 0,
            'billing_cycle' => 1
        ]);
        
        // Update user
        $this->User_model->update($subscription['user_id'], [
            'subscription_type' => 'premium',
            'subscription_status' => 'active',
            'subscription_expires_at' => $current_end
        ]);
        
        log_message('info', 'Subscription authenticated for user ' . $subscription['user_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription activated successfully! Auto-renews ' . ($plan['billing_period'] ?? 'monthly') . '.',
            'data' => [
                'subscription_status' => 'active',
                'subscription_type' => 'premium',
                'current_period_start' => $current_start,
                'current_period_end' => $current_end,
                'next_charge_at' => $charge_at,
                'paid_count' => $razorpay_sub['paid_count'] ?? 1,
                'plan' => $plan ? [
                    'id' => $plan['plan_id'],
                    'name' => $plan['name'],
                    'billing_period' => $plan['billing_period']
                ] : null
            ]
        ]);
    }
    
    /**
     * Get current subscription status
     * 
     * GET /api/subscription/status
     */
    public function status() {
        $user_data = $this->validate_jwt();
        
        $user = $this->User_model->get_by_id($user_data['user_id']);
        
        // Get the user's latest subscription (any status)
        $subscription = $this->Subscription_model->get_active_by_user($user_data['user_id']);
        
        // If no active subscription, get the most recent one regardless of status
        if (!$subscription) {
            $all_subscriptions = $this->Subscription_model->get_by_user($user_data['user_id'], 1);
            $subscription = !empty($all_subscriptions) ? $all_subscriptions[0] : null;
        }
        
        // Get plan details if subscription exists
        $plan = null;
        if ($subscription && $subscription['plan_id']) {
            $plan = $this->Plan_model->get_by_id($subscription['plan_id']);
        }
        
        // Determine subscription state
        $is_active = false;
        $subscription_status = $user['subscription_status'] ?? 'none';
        $days_remaining = 0;
        
        if ($subscription && $subscription['current_period_end']) {
            $end_time = strtotime($subscription['current_period_end']);
            $is_active = $end_time > time() && in_array($subscription['status'], ['active', 'authenticated']);
            $days_remaining = $is_active ? ceil(($end_time - time()) / 86400) : 0;
            
            // Check if subscription has expired
            if (!$is_active && in_array($subscription['status'], ['active', 'authenticated'])) {
                // Subscription expired, update status
                $this->Subscription_model->update($subscription['subscription_id'], ['status' => 'expired']);
                $this->User_model->update($user_data['user_id'], [
                    'subscription_type' => 'basic',
                    'subscription_status' => 'expired'
                ]);
                $subscription_status = 'expired';
                $subscription['status'] = 'expired';
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'subscription_type' => $is_active ? 'premium' : ($user['subscription_type'] ?? 'basic'),
                'subscription_status' => $subscription ? $subscription['status'] : 'none',
                'is_active' => $is_active,
                'is_recurring' => $subscription && in_array($subscription['status'], ['active', 'authenticated']),
                'current_period_start' => $subscription['current_period_start'] ?? null,
                'current_period_end' => $subscription['current_period_end'] ?? null,
                'next_charge_at' => $subscription['charge_at'] ?? null,
                'days_remaining' => $days_remaining,
                'paid_count' => $subscription['paid_count'] ?? 0,
                'plan' => $plan ? [
                    'id' => $plan['plan_id'],
                    'code' => $plan['plan_code'],
                    'name' => $plan['name'],
                    'amount' => $plan['amount'],
                    'amount_display' => '₹' . number_format($plan['amount'], 0),
                    'billing_period' => $plan['billing_period'],
                    'duration_days' => $plan['duration_days']
                ] : null,
                'razorpay_subscription_id' => $subscription['razorpay_subscription_id'] ?? null,
                'short_url' => ($subscription && in_array($subscription['status'], ['created', 'pending'])) 
                    ? $subscription['razorpay_short_url'] 
                    : null
            ]
        ]);
    }
    
    /**
     * Cancel subscription
     * 
     * POST /api/subscription/cancel
     * Body: { "cancel_at_cycle_end": true } (optional, default: true)
     * 
     * If cancel_at_cycle_end is true, user keeps access until current period ends.
     * If false, cancels immediately.
     */
    public function cancel() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(trim(file_get_contents('php://input')), true);
        $cancel_at_cycle_end = $input['cancel_at_cycle_end'] ?? true;
        
        $subscription = $this->Subscription_model->get_active_by_user($user_data['user_id']);
        
        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No active subscription found']);
            return;
        }
        
        // Cancel in Razorpay
        if ($subscription['razorpay_subscription_id']) {
            $result = $this->razorpay_library->cancel_subscription(
                $subscription['razorpay_subscription_id'],
                $cancel_at_cycle_end
            );
            
            if (isset($result['error'])) {
                log_message('error', 'Razorpay cancel failed: ' . $result['message']);
                // Continue anyway - we'll mark as cancelled locally
            }
        }
        
        // Update local subscription
        $this->Subscription_model->update($subscription['subscription_id'], [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->User_model->update($user_data['user_id'], [
            'subscription_status' => 'cancelled'
        ]);
        
        log_message('info', 'Subscription cancelled by user ' . $user_data['user_id']);
        
        $access_until = $cancel_at_cycle_end 
            ? $subscription['current_period_end'] 
            : date('Y-m-d H:i:s');
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'data' => [
                'subscription_status' => 'cancelled',
                'access_until' => $access_until,
                'cancel_at_cycle_end' => $cancel_at_cycle_end,
                'note' => $cancel_at_cycle_end 
                    ? 'You will continue to have access until ' . date('F j, Y', strtotime($access_until))
                    : 'Your access has been revoked immediately'
            ]
        ]);
    }
    
    /**
     * Pause subscription
     * 
     * POST /api/subscription/pause
     */
    public function pause() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $subscription = $this->Subscription_model->get_active_by_user($user_data['user_id']);
        
        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No active subscription found']);
            return;
        }
        
        // Pause in Razorpay
        if ($subscription['razorpay_subscription_id']) {
            $result = $this->razorpay_library->pause_subscription($subscription['razorpay_subscription_id']);
            
            if (isset($result['error'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to pause subscription: ' . $result['message']
                ]);
                return;
            }
        }
        
        // Update local subscription
        $this->Subscription_model->update($subscription['subscription_id'], [
            'status' => 'paused'
        ]);
        
        $this->User_model->update($user_data['user_id'], [
            'subscription_status' => 'paused'
        ]);
        
        log_message('info', 'Subscription paused by user ' . $user_data['user_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription paused successfully',
            'data' => [
                'subscription_status' => 'paused',
                'note' => 'No charges will be made until you resume. Your access continues until the current period ends.'
            ]
        ]);
    }
    
    /**
     * Resume paused subscription
     * 
     * POST /api/subscription/resume
     */
    public function resume() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $subscription = $this->Subscription_model->get_by_user_and_status($user_data['user_id'], 'paused');
        
        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No paused subscription found']);
            return;
        }
        
        // Resume in Razorpay
        if ($subscription['razorpay_subscription_id']) {
            $result = $this->razorpay_library->resume_subscription($subscription['razorpay_subscription_id']);
            
            if (isset($result['error'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to resume subscription: ' . $result['message']
                ]);
                return;
            }
        }
        
        // Update local subscription
        $this->Subscription_model->update($subscription['subscription_id'], [
            'status' => 'active'
        ]);
        
        $this->User_model->update($user_data['user_id'], [
            'subscription_status' => 'active'
        ]);
        
        log_message('info', 'Subscription resumed by user ' . $user_data['user_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription resumed successfully',
            'data' => [
                'subscription_status' => 'active',
                'next_charge_at' => $subscription['charge_at']
            ]
        ]);
    }
    
    /**
     * Sync subscription status from Razorpay
     * 
     * POST /api/subscription/sync
     * 
     * Fetches latest subscription status from Razorpay and updates local records.
     * Useful if webhooks were missed.
     */
    public function sync() {
        $user_data = $this->validate_jwt();
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Get user's latest subscription
        $subscriptions = $this->Subscription_model->get_by_user($user_data['user_id'], 1);
        
        if (empty($subscriptions)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No subscription found']);
            return;
        }
        
        $subscription = $subscriptions[0];
        
        if (!$subscription['razorpay_subscription_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Subscription not linked to Razorpay']);
            return;
        }
        
        // Fetch from Razorpay
        $razorpay_sub = $this->razorpay_library->fetch_subscription($subscription['razorpay_subscription_id']);
        
        if (isset($razorpay_sub['error'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to fetch from Razorpay: ' . $razorpay_sub['message']
            ]);
            return;
        }
        
        // Get plan details
        $plan = $this->Plan_model->get_by_id($subscription['plan_id']);
        $duration_days = $plan ? $plan['duration_days'] : 30;
        
        // Update local subscription
        $update_data = [
            'status' => $razorpay_sub['status'],
            'paid_count' => $razorpay_sub['paid_count'] ?? $subscription['paid_count']
        ];
        
        if (isset($razorpay_sub['current_start'])) {
            $update_data['current_period_start'] = date('Y-m-d H:i:s', $razorpay_sub['current_start']);
        }
        if (isset($razorpay_sub['current_end'])) {
            $update_data['current_period_end'] = date('Y-m-d H:i:s', $razorpay_sub['current_end']);
        }
        if (isset($razorpay_sub['charge_at'])) {
            $update_data['charge_at'] = date('Y-m-d H:i:s', $razorpay_sub['charge_at']);
        }
        if (isset($razorpay_sub['ended_at'])) {
            $update_data['ended_at'] = date('Y-m-d H:i:s', $razorpay_sub['ended_at']);
        }
        
        $this->Subscription_model->update($subscription['subscription_id'], $update_data);
        
        // Update user status
        $is_active = in_array($razorpay_sub['status'], ['active', 'authenticated']);
        $this->User_model->update($user_data['user_id'], [
            'subscription_type' => $is_active ? 'premium' : 'basic',
            'subscription_status' => $razorpay_sub['status'],
            'subscription_expires_at' => $update_data['current_period_end'] ?? null
        ]);
        
        log_message('info', 'Subscription synced for user ' . $user_data['user_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription synced successfully',
            'data' => [
                'subscription_status' => $razorpay_sub['status'],
                'current_period_start' => $update_data['current_period_start'] ?? null,
                'current_period_end' => $update_data['current_period_end'] ?? null,
                'next_charge_at' => $update_data['charge_at'] ?? null,
                'paid_count' => $razorpay_sub['paid_count'] ?? 0
            ]
        ]);
    }
    
    /**
     * Get payment and subscription history
     * 
     * GET /api/subscription/history
     */
    public function history() {
        $user_data = $this->validate_jwt();
        
        $payments = $this->Payment_model->get_by_user($user_data['user_id']);
        $subscriptions = $this->Subscription_model->get_by_user($user_data['user_id']);
        
        // Get plan IDs used in subscriptions
        $plan_ids = array_unique(array_filter(array_column($subscriptions, 'plan_id')));
        $plans_map = [];
        foreach ($plan_ids as $plan_id) {
            $plan = $this->Plan_model->get_by_id($plan_id);
            if ($plan) {
                $plans_map[$plan_id] = $plan;
            }
        }
        
        // Format payment data
        $formatted_payments = array_map(function($payment) {
            return [
                'payment_id' => $payment['payment_id'],
                'razorpay_payment_id' => $payment['razorpay_payment_id'],
                'amount' => (float) $payment['amount'],
                'amount_display' => '₹' . number_format($payment['amount'], 0),
                'method' => $payment['method'],
                'status' => $payment['status'],
                'is_recurring' => (bool) ($payment['is_recurring'] ?? false),
                'billing_cycle' => $payment['billing_cycle'] ?? 1,
                'date' => date('F j, Y g:i A', strtotime($payment['created_at']))
            ];
        }, $payments);
        
        // Format subscription data
        $formatted_subscriptions = array_map(function($sub) use ($plans_map) {
            $plan = isset($plans_map[$sub['plan_id']]) ? $plans_map[$sub['plan_id']] : null;
            return [
                'subscription_id' => $sub['subscription_id'],
                'razorpay_subscription_id' => $sub['razorpay_subscription_id'],
                'plan' => $plan ? [
                    'id' => $plan['plan_id'],
                    'name' => $plan['name'],
                    'code' => $plan['plan_code'],
                    'billing_period' => $plan['billing_period']
                ] : null,
                'amount' => (float) $sub['amount'],
                'amount_display' => '₹' . number_format($sub['amount'], 0),
                'status' => $sub['status'],
                'paid_count' => $sub['paid_count'] ?? 0,
                'period_start' => $sub['current_period_start'] 
                    ? date('F j, Y', strtotime($sub['current_period_start'])) 
                    : null,
                'period_end' => $sub['current_period_end'] 
                    ? date('F j, Y', strtotime($sub['current_period_end'])) 
                    : null,
                'cancelled_at' => $sub['cancelled_at'] 
                    ? date('F j, Y', strtotime($sub['cancelled_at'])) 
                    : null,
                'created_at' => date('F j, Y', strtotime($sub['created_at']))
            ];
        }, $subscriptions);
        
        // Calculate totals
        $captured_payments = array_filter($payments, fn($p) => $p['status'] === 'captured');
        $total_spent = array_sum(array_column($captured_payments, 'amount'));
        $recurring_count = count(array_filter($payments, fn($p) => ($p['is_recurring'] ?? false)));
        
        echo json_encode([
            'success' => true,
            'data' => [
                'payments' => $formatted_payments,
                'subscriptions' => $formatted_subscriptions,
                'summary' => [
                    'total_payments' => count($payments),
                    'successful_payments' => count($captured_payments),
                    'recurring_payments' => $recurring_count,
                    'total_spent' => (float) $total_spent,
                    'total_spent_display' => '₹' . number_format($total_spent, 0)
                ]
            ]
        ]);
    }
    
    /**
     * Create Razorpay plan (Admin only)
     * 
     * POST /api/subscription/create-plan
     * Body: { "plan_id": 1 } - local plan ID to create in Razorpay
     * 
     * Creates a plan in Razorpay and updates the local plan with Razorpay Plan ID.
     */
    public function create_plan() {
        $user_data = $this->validate_jwt();
        
        // TODO: Add admin check here
        // if (!$this->User_model->is_admin($user_data['user_id'])) {
        //     http_response_code(403);
        //     echo json_encode(['success' => false, 'message' => 'Admin access required']);
        //     return;
        // }
        
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(trim(file_get_contents('php://input')), true);
        $plan_id = $input['plan_id'] ?? null;
        
        if (!$plan_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'plan_id is required']);
            return;
        }
        
        // Get local plan
        $plan = $this->Plan_model->get_by_id($plan_id);
        
        if (!$plan) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plan not found']);
            return;
        }
        
        // Check if already has Razorpay plan ID
        if (!empty($plan['razorpay_plan_id'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Plan already has Razorpay Plan ID',
                'data' => [
                    'razorpay_plan_id' => $plan['razorpay_plan_id']
                ]
            ]);
            return;
        }
        
        // Create plan in Razorpay
        $razorpay_plan = $this->razorpay_library->create_plan(
            $plan['name'],
            $plan['amount_in_paise'],
            $plan['billing_period'],
            $plan['billing_interval'],
            $plan['description']
        );
        
        if (isset($razorpay_plan['error'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create Razorpay plan: ' . $razorpay_plan['message']
            ]);
            return;
        }
        
        // Update local plan with Razorpay Plan ID
        $this->Plan_model->set_razorpay_plan_id($plan_id, $razorpay_plan['id']);
        
        log_message('info', 'Razorpay plan created: ' . $razorpay_plan['id'] . ' for local plan ' . $plan_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Razorpay plan created successfully',
            'data' => [
                'plan_id' => $plan_id,
                'razorpay_plan_id' => $razorpay_plan['id'],
                'razorpay_plan' => $razorpay_plan
            ]
        ]);
    }
}
