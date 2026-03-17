<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment <?= $status === 'success' ? 'Successful' : 'Failed' ?> - BannerBox</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: <?= $status === 'success' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .result-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            text-align: center;
            overflow: hidden;
        }
        
        .result-icon {
            padding: 40px 30px 20px;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            background: <?= $status === 'success' ? '#dcfce7' : '#fef2f2' ?>;
        }
        
        .result-body {
            padding: 0 30px 30px;
        }
        
        .result-title {
            font-size: 24px;
            font-weight: 700;
            color: <?= $status === 'success' ? '#059669' : '#dc2626' ?>;
            margin-bottom: 12px;
        }
        
        .result-message {
            color: #64748b;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .subscription-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #64748b;
            font-size: 14px;
        }
        
        .detail-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 14px;
        }
        
        .action-button {
            display: inline-block;
            background: <?= $status === 'success' ? 'linear-gradient(135deg, #3B82F6 0%, #2563EB 100%)' : 'linear-gradient(135deg, #64748b 0%, #475569 100%)' ?>;
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }
        
        .secondary-link {
            display: block;
            margin-top: 16px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
        
        .secondary-link:hover {
            color: #3B82F6;
        }
        
        .redirect-notice {
            background: #f1f5f9;
            padding: 16px;
            font-size: 13px;
            color: #64748b;
        }
        
        .countdown {
            font-weight: 600;
            color: #3B82F6;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-icon">
            <div class="icon-circle">
                <?= $status === 'success' ? '✓' : '✗' ?>
            </div>
        </div>
        
        <div class="result-body">
            <h1 class="result-title">
                <?= $status === 'success' ? 'Payment Successful!' : 'Payment Failed' ?>
            </h1>
            
            <p class="result-message">
                <?php if ($status === 'success'): ?>
                    Your subscription has been activated. You now have access to all premium features.
                <?php else: ?>
                    <?= htmlspecialchars($message ?? 'Something went wrong with your payment. Please try again.') ?>
                <?php endif; ?>
            </p>
            
            <?php if ($status === 'success' && !empty($subscription)): ?>
            <div class="subscription-details">
                <div class="detail-row">
                    <span class="detail-label">Plan</span>
                    <span class="detail-value"><?= htmlspecialchars($subscription['plan_name'] ?? 'Premium') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value">₹<?= number_format($subscription['amount'] ?? 0, 0) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color: #10b981;">Active</span>
                </div>
                <?php if (!empty($subscription['expires_at'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Next Renewal</span>
                    <span class="detail-value"><?= date('M d, Y', strtotime($subscription['expires_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <a href="<?= $redirect_url ?>" class="action-button">
                <?= $status === 'success' ? 'Go to Dashboard' : 'Try Again' ?>
            </a>
            
            <?php if ($status === 'failed'): ?>
            <a href="<?= base_url('api/subscription/plans') ?>" class="secondary-link">
                View Plans
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($auto_redirect)): ?>
        <div class="redirect-notice">
            Redirecting in <span class="countdown" id="countdown"><?= $redirect_delay ?? 5 ?></span> seconds...
        </div>
        
        <script>
            let seconds = <?= $redirect_delay ?? 5 ?>;
            const countdown = document.getElementById('countdown');
            const redirectUrl = '<?= $redirect_url ?>';
            
            const timer = setInterval(function() {
                seconds--;
                countdown.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
