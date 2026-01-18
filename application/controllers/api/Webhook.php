<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webhook Controller
 * 
 * Handles incoming webhook events from Razorpay for RECURRING SUBSCRIPTIONS
 * 
 * IMPORTANT: This endpoint does NOT require JWT authentication
 * It uses Razorpay's webhook signature for verification
 * 
 * Endpoint: POST /api/webhook/razorpay
 * 
 * Key Subscription Events:
 * - subscription.authenticated: First payment successful, mandate created
 * - subscription.activated: Subscription is now active
 * - subscription.charged: RECURRING payment successful - extends subscription period
 * - subscription.pending: Charge pending
 * - subscription.halted: Multiple payment failures
 * - subscription.cancelled: Subscription cancelled
 * - subscription.paused: Subscription paused
 * - subscription.resumed: Subscription resumed
 * - subscription.completed: All billing cycles completed
 * 
 * @author BannerBox
 * @version 2.0.0 - Recurring Subscriptions
 */
class Webhook extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Subscription_model', 'Payment_model', 'User_model', 'Webhook_log_model', 'Plan_model']);
        $this->load->library('razorpay_library');
    }
    
    /**
     * Handle Razorpay webhook events
     * 
     * POST /api/webhook/razorpay
     */
    public function razorpay() {
        // Get raw payload - important for signature verification
        $payload = file_get_contents('php://input');
        
        // Get signature from header
        $signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) 
            ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] 
            : '';
        
        // Initialize log data
        $log_data = [
            'event_type' => 'unknown',
            'payload' => $payload,
            'signature' => $signature,
            'is_valid' => 0,
            'processed' => 0,
            'error_message' => null
        ];
        
        // Log webhook receipt
        log_message('info', 'Razorpay webhook received');
        
        // Verify webhook signature
        if (empty($signature)) {
            $log_data['error_message'] = 'Missing signature header';
            $this->Webhook_log_model->create($log_data);
            
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing signature']);
            return;
        }
        
        $is_valid = $this->razorpay_library->verify_webhook_signature($payload, $signature);
        
        if (!$is_valid) {
            $log_data['error_message'] = 'Invalid webhook signature';
            $this->Webhook_log_model->create($log_data);
            
            log_message('error', 'Razorpay webhook signature verification failed');
            
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
            return;
        }
        
        $log_data['is_valid'] = 1;
        
        // Parse the payload
        $event = json_decode($payload, true);
        
        if (!$event || !isset($event['event'])) {
            $log_data['error_message'] = 'Invalid payload format';
            $this->Webhook_log_model->create($log_data);
            
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
            return;
        }
        
        $event_type = $event['event'];
        $log_data['event_type'] = $event_type;
        
        log_message('info', 'Processing Razorpay webhook event: ' . $event_type);
        
        // Process the event
        try {
            switch ($event_type) {
                case 'payment.captured':
                    $this->handle_payment_captured($event['payload']['payment']['entity']);
                    break;
                    
                case 'payment.failed':
                    $this->handle_payment_failed($event['payload']['payment']['entity']);
                    break;
                    
                case 'payment.authorized':
                    // Payment authorized but not captured - usually auto-captured
                    log_message('info', 'Payment authorized: ' . ($event['payload']['payment']['entity']['id'] ?? 'unknown'));
                    break;
                    
                case 'order.paid':
                    $this->handle_order_paid($event['payload']['order']['entity'], $event['payload']['payment']['entity'] ?? null);
                    break;
                    
                case 'subscription.activated':
                    $this->handle_subscription_activated($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.charged':
                    $this->handle_subscription_charged(
                        $event['payload']['subscription']['entity'],
                        $event['payload']['payment']['entity'] ?? null
                    );
                    break;
                    
                case 'subscription.pending':
                    $this->handle_subscription_pending($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.halted':
                    $this->handle_subscription_halted($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.cancelled':
                    $this->handle_subscription_cancelled($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.completed':
                    $this->handle_subscription_completed($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.paused':
                    $this->handle_subscription_paused($event['payload']['subscription']['entity']);
                    break;
                    
                case 'subscription.resumed':
                    $this->handle_subscription_resumed($event['payload']['subscription']['entity']);
                    break;
                    
                case 'refund.created':
                    $this->handle_refund_created($event['payload']['refund']['entity']);
                    break;
                    
                default:
                    log_message('info', 'Unhandled webhook event type: ' . $event_type);
                    $log_data['error_message'] = 'Unhandled event type: ' . $event_type;
            }
            
            $log_data['processed'] = 1;
            
        } catch (Exception $e) {
            log_message('error', 'Webhook processing error: ' . $e->getMessage());
            $log_data['error_message'] = $e->getMessage();
        }
        
        // Save the webhook log
        $this->Webhook_log_model->create($log_data);
        
        // Always return 200 to acknowledge receipt
        // Razorpay will retry if it doesn't receive 200
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'event' => $event_type]);
    }
    
    /**
     * Handle payment.captured event
     * 
     * This is fired when a payment is successfully captured
     */
    private function handle_payment_captured($payment) {
        $order_id = $payment['order_id'] ?? null;
        $payment_id = $payment['id'];
        
        log_message('info', 'Processing payment.captured for order: ' . $order_id);
        
        if (!$order_id) {
            log_message('error', 'payment.captured event missing order_id');
            return;
        }
        
        // Find our payment record
        $payment_record = $this->Payment_model->get_by_order_id($order_id);
        
        if (!$payment_record) {
            log_message('error', 'Payment record not found for order: ' . $order_id);
            throw new Exception('Payment record not found for order: ' . $order_id);
        }
        
        // Check if already processed
        if ($payment_record['status'] === 'captured') {
            log_message('info', 'Payment already captured, skipping: ' . $order_id);
            return;
        }
        
        // Update payment status
        $this->Payment_model->update($payment_record['payment_id'], [
            'razorpay_payment_id' => $payment_id,
            'method' => $payment['method'] ?? 'unknown',
            'status' => 'captured'
        ]);
        
        // Activate subscription if not already active
        if ($payment_record['subscription_id']) {
            $subscription = $this->Subscription_model->get_by_id($payment_record['subscription_id']);
            
            if ($subscription && $subscription['status'] !== 'active') {
                $subscription_duration = $this->config->item('subscription_duration_days');
                $start_date = date('Y-m-d H:i:s');
                $end_date = date('Y-m-d H:i:s', strtotime("+{$subscription_duration} days"));
                
                $this->Subscription_model->update($payment_record['subscription_id'], [
                    'status' => 'active',
                    'current_period_start' => $start_date,
                    'current_period_end' => $end_date
                ]);
                
                $this->User_model->update($payment_record['user_id'], [
                    'subscription_type' => 'premium',
                    'subscription_status' => 'active',
                    'subscription_expires_at' => $end_date
                ]);
                
                log_message('info', 'Subscription activated via webhook for user: ' . $payment_record['user_id']);
            }
        }
    }
    
    /**
     * Handle payment.failed event
     */
    private function handle_payment_failed($payment) {
        $order_id = $payment['order_id'] ?? null;
        
        log_message('info', 'Processing payment.failed for order: ' . $order_id);
        
        if (!$order_id) {
            return;
        }
        
        $payment_record = $this->Payment_model->get_by_order_id($order_id);
        
        if ($payment_record) {
            $this->Payment_model->update($payment_record['payment_id'], [
                'razorpay_payment_id' => $payment['id'],
                'status' => 'failed',
                'error_code' => $payment['error_code'] ?? null,
                'error_description' => $payment['error_description'] ?? null
            ]);
            
            log_message('info', 'Payment marked as failed for order: ' . $order_id);
        }
    }
    
    /**
     * Handle order.paid event
     */
    private function handle_order_paid($order, $payment = null) {
        $order_id = $order['id'];
        
        log_message('info', 'Processing order.paid for order: ' . $order_id);
        
        // Similar to payment.captured - ensure subscription is activated
        $payment_record = $this->Payment_model->get_by_order_id($order_id);
        
        if ($payment_record && $payment_record['status'] !== 'captured') {
            $this->Payment_model->update($payment_record['payment_id'], [
                'razorpay_payment_id' => $payment['id'] ?? null,
                'method' => $payment['method'] ?? 'unknown',
                'status' => 'captured'
            ]);
            
            if ($payment_record['subscription_id']) {
                $subscription = $this->Subscription_model->get_by_id($payment_record['subscription_id']);
                
                if ($subscription && $subscription['status'] !== 'active') {
                    $subscription_duration = $this->config->item('subscription_duration_days');
                    $start_date = date('Y-m-d H:i:s');
                    $end_date = date('Y-m-d H:i:s', strtotime("+{$subscription_duration} days"));
                    
                    $this->Subscription_model->update($payment_record['subscription_id'], [
                        'status' => 'active',
                        'current_period_start' => $start_date,
                        'current_period_end' => $end_date
                    ]);
                    
                    $this->User_model->update($payment_record['user_id'], [
                        'subscription_type' => 'premium',
                        'subscription_status' => 'active',
                        'subscription_expires_at' => $end_date
                    ]);
                }
            }
        }
    }
    
    /**
     * Handle subscription.activated event
     * 
     * Fired when a Razorpay subscription becomes active (after authentication)
     */
    private function handle_subscription_activated($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.activated: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            // Get plan details for duration
            $plan = $this->Plan_model->get_by_id($sub_record['plan_id']);
            $duration_days = $plan ? $plan['duration_days'] : 30;
            
            $start = isset($subscription['current_start']) 
                ? date('Y-m-d H:i:s', $subscription['current_start']) 
                : date('Y-m-d H:i:s');
            $end = isset($subscription['current_end']) 
                ? date('Y-m-d H:i:s', $subscription['current_end']) 
                : date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
            $charge_at = isset($subscription['charge_at']) 
                ? date('Y-m-d H:i:s', $subscription['charge_at']) 
                : null;
            
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'active',
                'current_period_start' => $start,
                'current_period_end' => $end,
                'charge_at' => $charge_at,
                'paid_count' => $subscription['paid_count'] ?? 1
            ]);
            
            $this->User_model->update($sub_record['user_id'], [
                'subscription_type' => 'premium',
                'subscription_status' => 'active',
                'subscription_expires_at' => $end
            ]);
            
            log_message('info', 'Subscription activated for user: ' . $sub_record['user_id'] . ' until ' . $end);
        }
    }
    
    /**
     * Handle subscription.charged event
     * 
     * CRITICAL: This is fired for RECURRING payments (auto-debit)
     * This extends the subscription period by another billing cycle
     */
    private function handle_subscription_charged($subscription, $payment = null) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.charged (RECURRING): ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if (!$sub_record) {
            log_message('error', 'Subscription not found for recurring charge: ' . $razorpay_sub_id);
            return;
        }
        
        // Get plan details for duration
        $plan = $this->Plan_model->get_by_id($sub_record['plan_id']);
        $duration_days = $plan ? $plan['duration_days'] : 30;
        
        // Calculate new period dates from Razorpay response
        $start = isset($subscription['current_start']) 
            ? date('Y-m-d H:i:s', $subscription['current_start']) 
            : date('Y-m-d H:i:s');
        $end = isset($subscription['current_end']) 
            ? date('Y-m-d H:i:s', $subscription['current_end']) 
            : date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
        $charge_at = isset($subscription['charge_at']) 
            ? date('Y-m-d H:i:s', $subscription['charge_at']) 
            : null;
        
        $paid_count = $subscription['paid_count'] ?? ($sub_record['paid_count'] + 1);
        
        // Create payment record for this recurring charge
        if ($payment) {
            // Check if payment already recorded (idempotency)
            $existing_payment = $this->Payment_model->get_by_razorpay_payment_id($payment['id']);
            
            if (!$existing_payment) {
                $this->Payment_model->create([
                    'user_id' => $sub_record['user_id'],
                    'subscription_id' => $sub_record['subscription_id'],
                    'razorpay_payment_id' => $payment['id'],
                    'amount' => ($payment['amount'] ?? $sub_record['amount'] * 100) / 100,
                    'currency' => $payment['currency'] ?? 'INR',
                    'method' => $payment['method'] ?? 'upi',
                    'status' => 'captured',
                    'is_recurring' => 1,
                    'billing_cycle' => $paid_count
                ]);
                
                log_message('info', 'Recorded recurring payment #' . $paid_count . ' for user: ' . $sub_record['user_id']);
            }
        }
        
        // Update subscription with extended period
        $this->Subscription_model->update($sub_record['subscription_id'], [
            'status' => 'active',
            'current_period_start' => $start,
            'current_period_end' => $end,
            'charge_at' => $charge_at,
            'paid_count' => $paid_count
        ]);
        
        // Ensure user has premium access
        $this->User_model->update($sub_record['user_id'], [
            'subscription_type' => 'premium',
            'subscription_status' => 'active',
            'subscription_expires_at' => $end
        ]);
        
        log_message('info', 'Subscription renewed (cycle #' . $paid_count . ') for user: ' . $sub_record['user_id'] . ' until ' . $end);
    }
    
    /**
     * Handle subscription.pending event
     */
    private function handle_subscription_pending($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.pending: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'pending'
            ]);
        }
    }
    
    /**
     * Handle subscription.halted event
     * 
     * Fired when payment fails multiple times
     */
    private function handle_subscription_halted($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.halted: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'halted'
            ]);
            
            // Downgrade user to basic
            $this->User_model->update($sub_record['user_id'], [
                'subscription_type' => 'basic',
                'subscription_status' => 'expired'
            ]);
            
            log_message('info', 'Subscription halted for user: ' . $sub_record['user_id']);
        }
    }
    
    /**
     * Handle subscription.cancelled event
     */
    private function handle_subscription_cancelled($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.cancelled: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->User_model->update($sub_record['user_id'], [
                'subscription_status' => 'cancelled'
            ]);
        }
    }
    
    /**
     * Handle subscription.completed event
     */
    private function handle_subscription_completed($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.completed: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'completed'
            ]);
            
            // Downgrade user to basic
            $this->User_model->update($sub_record['user_id'], [
                'subscription_type' => 'basic',
                'subscription_status' => 'expired'
            ]);
        }
    }
    
    /**
     * Handle subscription.paused event
     */
    private function handle_subscription_paused($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.paused: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'halted' // Using halted for paused state
            ]);
        }
    }
    
    /**
     * Handle subscription.resumed event
     */
    private function handle_subscription_resumed($subscription) {
        $razorpay_sub_id = $subscription['id'];
        
        log_message('info', 'Processing subscription.resumed: ' . $razorpay_sub_id);
        
        $sub_record = $this->Subscription_model->get_by_razorpay_id($razorpay_sub_id);
        
        if ($sub_record) {
            $this->Subscription_model->update($sub_record['subscription_id'], [
                'status' => 'active'
            ]);
            
            $this->User_model->update($sub_record['user_id'], [
                'subscription_status' => 'active'
            ]);
        }
    }
    
    /**
     * Handle refund.created event
     */
    private function handle_refund_created($refund) {
        $payment_id = $refund['payment_id'] ?? null;
        
        log_message('info', 'Processing refund.created for payment: ' . $payment_id);
        
        if ($payment_id) {
            $payment_record = $this->Payment_model->get_by_payment_id($payment_id);
            
            if ($payment_record) {
                $this->Payment_model->update($payment_record['payment_id'], [
                    'status' => 'refunded',
                    'notes' => json_encode([
                        'refund_id' => $refund['id'],
                        'refund_amount' => $refund['amount'] / 100,
                        'refund_status' => $refund['status']
                    ])
                ]);
                
                // If full refund, cancel subscription
                if ($payment_record['subscription_id']) {
                    $this->Subscription_model->update($payment_record['subscription_id'], [
                        'status' => 'cancelled',
                        'cancelled_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    $this->User_model->update($payment_record['user_id'], [
                        'subscription_type' => 'basic',
                        'subscription_status' => 'cancelled'
                    ]);
                }
                
                log_message('info', 'Refund processed for user: ' . $payment_record['user_id']);
            }
        }
    }
}
