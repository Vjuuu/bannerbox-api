<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - BannerBox</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .payment-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .plan-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .plan-name {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .plan-price {
            font-size: 32px;
            font-weight: 700;
            color: #3B82F6;
        }
        
        .plan-price span {
            font-size: 16px;
            font-weight: 400;
            color: #64748b;
        }
        
        .plan-features {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        
        .plan-features li {
            list-style: none;
            padding: 6px 0;
            color: #475569;
            font-size: 14px;
        }
        
        .plan-features li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .pay-button {
            width: 100%;
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }
        
        .pay-button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .secure-note {
            text-align: center;
            margin-top: 16px;
            color: #64748b;
            font-size: 12px;
        }
        
        .secure-note img {
            height: 16px;
            vertical-align: middle;
            margin-right: 4px;
        }
        
        .auto-renew-note {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #92400e;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .loading-text {
            color: white;
            margin-top: 16px;
            font-size: 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Processing payment...</div>
    </div>
    
    <div class="payment-container">
        <div class="payment-header">
            <h1>BannerBox Premium</h1>
            <p>Complete your subscription payment</p>
        </div>
        
        <div class="payment-body">
            <div class="error-message" id="errorMessage"></div>
            
            <div class="plan-details">
                <div class="plan-name"><?= htmlspecialchars($plan['name'] ?? 'Premium Plan') ?></div>
                <div class="plan-price">
                    ₹<?= number_format($plan['amount'] ?? 0, 0) ?>
                    <span>/<?= ($plan['billing_period'] ?? 'monthly') === 'monthly' ? 'month' : 'year' ?></span>
                </div>
                <ul class="plan-features">
                    <?php 
                    $features = [];
                    if (isset($plan['features'])) {
                        if (is_string($plan['features'])) {
                            $features = json_decode($plan['features'], true) ?: [];
                        } elseif (is_array($plan['features'])) {
                            $features = $plan['features'];
                        }
                    }
                    ?>
                    <?php if (!empty($features)): ?>
                        <?php foreach ($features as $feature): ?>
                            <li><?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Access to premium posters</li>
                        <li>High quality downloads</li>
                        <li>Cancel anytime</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="auto-renew-note">
                ⚡ This subscription auto-renews <?= ($plan['billing_period'] ?? 'monthly') === 'monthly' ? 'monthly' : 'yearly' ?> via UPI autopay. You can cancel anytime.
            </div>
            
            <button class="pay-button" id="payButton" onclick="openRazorpay()">
                Pay ₹<?= number_format($plan['amount'] ?? 0, 0) ?> & Subscribe
            </button>
            
            <p class="secure-note">
                🔒 Secured by Razorpay. Your payment details are safe.
            </p>
        </div>
    </div>
    
    <script>
        // Subscription and checkout data from PHP
        const subscriptionData = <?= json_encode($subscription_data) ?>;
        const checkoutOptions = <?= json_encode($checkout_options) ?>;
        const callbackUrl = '<?= $callback_url ?>';
        const frontendSuccessUrl = '<?= $frontend_success_url ?>';
        const frontendFailureUrl = '<?= $frontend_failure_url ?>';
        
        function showError(message) {
            const errorEl = document.getElementById('errorMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
        
        function showLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('active', show);
            document.getElementById('payButton').disabled = show;
        }
        
        function openRazorpay() {
            showLoading(true);
            
            const options = {
                key: checkoutOptions.key,
                subscription_id: checkoutOptions.subscription_id,
                name: checkoutOptions.name,
                description: checkoutOptions.description,
                image: checkoutOptions.image || '',
                prefill: checkoutOptions.prefill,
                notes: checkoutOptions.notes,
                theme: checkoutOptions.theme,
                modal: {
                    ondismiss: function() {
                        showLoading(false);
                    },
                    confirm_close: true,
                    escape: false
                },
                handler: function(response) {
                    // Payment successful - redirect to callback
                    showLoading(true);
                    document.getElementById('loadingOverlay').querySelector('.loading-text').textContent = 'Verifying payment...';
                    
                    // Build callback URL with payment details
                    const params = new URLSearchParams({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_subscription_id: response.razorpay_subscription_id,
                        razorpay_signature: response.razorpay_signature
                    });
                    
                    window.location.href = callbackUrl + '?' + params.toString();
                }
            };
            
            try {
                const rzp = new Razorpay(options);
                
                rzp.on('payment.failed', function(response) {
                    showLoading(false);
                    showError('Payment failed: ' + (response.error.description || 'Unknown error'));
                    
                    // Optionally redirect to failure URL
                    setTimeout(function() {
                        const params = new URLSearchParams({
                            status: 'failed',
                            message: response.error.description || 'Payment failed',
                            subscription_id: subscriptionData.razorpay_subscription_id
                        });
                        window.location.href = frontendFailureUrl + '?' + params.toString();
                    }, 3000);
                });
                
                rzp.open();
            } catch (error) {
                showLoading(false);
                showError('Failed to initialize payment: ' + error.message);
            }
        }
        
        // Auto-open Razorpay if autoOpen flag is set
        <?php if (!empty($auto_open)): ?>
        window.onload = function() {
            setTimeout(openRazorpay, 500);
        };
        <?php endif; ?>
    </script>
</body>
</html>
