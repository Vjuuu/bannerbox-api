# Webhooks API

Webhook endpoints for receiving external service notifications.

## Overview

Webhooks allow external services to notify your API about events in real-time. Currently, the API supports webhooks from Razorpay for payment and subscription events.

**Important:** Webhook endpoints use signature verification instead of JWT authentication.

## Base URL

```
/api/webhook
```

---

## Razorpay Webhook

Receive payment and subscription event notifications from Razorpay.

```http
POST /api/webhook/razorpay
```

### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-Razorpay-Signature` | HMAC-SHA256 signature | Yes |
| `Content-Type` | `application/json` | Yes |

### Authentication

Razorpay webhooks are authenticated using HMAC-SHA256 signature verification:

1. Razorpay signs the request body with your webhook secret
2. The signature is sent in the `X-Razorpay-Signature` header
3. Your API verifies the signature before processing

### Supported Events

| Event | Description |
|-------|-------------|
| `payment.captured` | Payment was successfully captured |
| `payment.failed` | Payment attempt failed |
| `payment.authorized` | Payment was authorized (for 2-step payments) |
| `subscription.activated` | Subscription was activated |
| `subscription.charged` | Recurring payment was charged |
| `subscription.pending` | Subscription payment is pending |
| `subscription.halted` | Subscription was halted due to payment failure |
| `subscription.cancelled` | Subscription was cancelled |
| `subscription.completed` | Subscription completed all cycles |

### Request Body Examples

#### payment.captured

```json
{
  "event": "payment.captured",
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_XYZ789ABC",
        "entity": "payment",
        "amount": 3000,
        "currency": "INR",
        "status": "captured",
        "order_id": "order_ABC123XYZ",
        "method": "upi",
        "email": "user@example.com",
        "contact": "+919876543210",
        "notes": {
          "user_id": "1",
          "plan_id": "1"
        },
        "created_at": 1737187200
      }
    }
  },
  "created_at": 1737187200
}
```

#### payment.failed

```json
{
  "event": "payment.failed",
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_XYZ789ABC",
        "entity": "payment",
        "amount": 3000,
        "currency": "INR",
        "status": "failed",
        "order_id": "order_ABC123XYZ",
        "error_code": "BAD_REQUEST_ERROR",
        "error_description": "Payment failed due to insufficient funds",
        "error_reason": "insufficient_funds"
      }
    }
  }
}
```

#### subscription.activated

```json
{
  "event": "subscription.activated",
  "payload": {
    "subscription": {
      "entity": {
        "id": "sub_ABC123",
        "entity": "subscription",
        "plan_id": "plan_XYZ",
        "status": "active",
        "current_start": 1737187200,
        "current_end": 1739865600
      }
    }
  }
}
```

### Success Response (200)

```json
{
  "success": true,
  "message": "Webhook processed"
}
```

### Error Responses

| Code | Response |
|------|----------|
| 400 | `{ "success": false, "message": "Invalid webhook signature" }` |
| 400 | `{ "success": false, "message": "Invalid payload" }` |

---

## Setting Up Razorpay Webhooks

### Step 1: Configure Webhook in Razorpay Dashboard

1. Log in to [Razorpay Dashboard](https://dashboard.razorpay.com)
2. Go to **Settings** → **Webhooks**
3. Click **Add New Webhook**
4. Configure:
   - **Webhook URL:** `https://your-api.com/api/webhook/razorpay`
   - **Secret:** Generate a strong secret (save this!)
   - **Active Events:** Select the events you want to receive

### Step 2: Update Your Config

Add the webhook secret to your `config.php`:

```php
$config['razorpay_webhook_secret'] = 'your-webhook-secret-here';
```

### Step 3: Test the Webhook

1. In Razorpay Dashboard, go to the webhook you created
2. Click **Test Webhook**
3. Select an event type
4. Check your application logs

---

## Webhook Processing Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         WEBHOOK PROCESSING FLOW                              │
└─────────────────────────────────────────────────────────────────────────────┘

1. Razorpay sends webhook
   └──> POST /api/webhook/razorpay

2. Verify signature
   └──> Compare HMAC-SHA256(body, secret) with header signature
   └──> If invalid, return 400 and log attempt

3. Log webhook
   └──> Store in webhook_logs table for debugging

4. Parse event type
   └──> Extract event from payload

5. Process based on event:
   ├──> payment.captured
   │    └──> Update payment status
   │    └──> Activate subscription
   │    └──> Update user subscription_type
   │
   ├──> payment.failed
   │    └──> Update payment status
   │    └──> Log failure reason
   │
   ├──> subscription.cancelled
   │    └──> Update subscription status
   │    └──> Update user subscription_status
   │
   └──> subscription.charged (renewal)
        └──> Create new payment record
        └──> Extend subscription period

6. Return success response
   └──> { "success": true, "message": "Webhook processed" }
```

---

## Webhook Security

### Signature Verification

```php
// How signature verification works
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
$secret = $this->config->item('razorpay_webhook_secret');

$expected_signature = hash_hmac('sha256', $payload, $secret);

if (hash_equals($expected_signature, $signature)) {
    // Valid webhook
} else {
    // Invalid - reject
}
```

### Best Practices

1. **Always verify signatures** - Never process unverified webhooks
2. **Use HTTPS** - Webhook URL must use SSL/TLS
3. **Respond quickly** - Return 200 within 5 seconds
4. **Process asynchronously** - For heavy processing, queue the job
5. **Handle retries** - Razorpay retries failed webhooks
6. **Log everything** - Store webhook payloads for debugging
7. **Idempotency** - Handle duplicate webhooks gracefully

### Handling Duplicates

Razorpay may send the same webhook multiple times. Check if already processed:

```php
// Check if payment already processed
$existing = $this->Payment_model->get_by_razorpay_id($payment_id);
if ($existing && $existing['status'] === 'captured') {
    // Already processed, return success
    return;
}
```

---

## Webhook Logs

All webhooks are logged in the `webhook_logs` table for debugging:

### Table Schema

| Column | Type | Description |
|--------|------|-------------|
| log_id | INT | Primary key |
| event_type | VARCHAR(100) | Event name |
| payload | LONGTEXT | Full JSON payload |
| signature | VARCHAR(255) | Received signature |
| is_valid | TINYINT | 1 = valid signature, 0 = invalid |
| processed | TINYINT | 1 = processed, 0 = pending |
| error_message | TEXT | Error message if failed |
| created_at | DATETIME | Timestamp |

### Viewing Logs

Query recent webhooks:

```sql
SELECT * FROM webhook_logs 
ORDER BY created_at DESC 
LIMIT 50;
```

Failed webhooks:

```sql
SELECT * FROM webhook_logs 
WHERE is_valid = 0 OR processed = 0 
ORDER BY created_at DESC;
```

---

## Testing Webhooks Locally

### Using ngrok

1. Install ngrok: `npm install -g ngrok`
2. Start your local server
3. Expose it: `ngrok http 80`
4. Use the ngrok URL in Razorpay Dashboard

### Test Events

In Razorpay Dashboard Test Mode:
1. Create a test payment
2. The webhook will be triggered automatically
3. Check your logs

### Manual Testing with cURL

```bash
# Note: This won't have a valid signature
# Use only for testing endpoint availability

curl -X POST "http://localhost/api/webhook/razorpay" \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: test-signature" \
  -d '{
    "event": "payment.captured",
    "payload": {
      "payment": {
        "entity": {
          "id": "pay_test123",
          "order_id": "order_test456",
          "amount": 3000,
          "status": "captured"
        }
      }
    }
  }'
```

---

## Troubleshooting

### Webhook Not Received

1. Check webhook URL is correct and accessible
2. Verify HTTPS is working
3. Check firewall/security rules
4. Look at Razorpay Dashboard → Webhooks → Logs

### Signature Verification Failed

1. Verify webhook secret matches
2. Check for whitespace in secret
3. Ensure raw body is used for verification
4. Check if body is being modified by middleware

### Webhook Processing Failed

1. Check `webhook_logs` table for error messages
2. Verify database connections
3. Check for missing records (order_id not found)
4. Review application logs

### Common Error Codes

| Error | Cause | Solution |
|-------|-------|----------|
| 400 Invalid signature | Wrong secret or tampered payload | Verify webhook secret |
| 404 Order not found | Order was not created or expired | Check order creation flow |
| 500 Database error | Database connection issue | Check database connectivity |
