# Subscription API

Recurring subscription management with Razorpay Subscriptions API and UPI Autopay.

## Overview

The Subscription API handles premium recurring subscriptions via Razorpay's Subscriptions functionality. Users subscribe to plans and are automatically charged monthly/yearly via UPI mandate (autopay).

**Key Features:**
- ✅ **Auto-Renewal:** Razorpay automatically charges users each billing cycle
- ✅ **UPI Autopay:** Users authorize recurring UPI payments
- ✅ **No Manual Renewal:** Webhook extends subscription on each charge
- ✅ **Pause/Resume:** Users can pause and resume subscriptions

**Authentication:** These endpoints use **JWT token only** (no API key required). The `plans` endpoint is public.

## Base URL

```
/api/subscription
```

## Subscription Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    RAZORPAY RECURRING SUBSCRIPTION FLOW                      │
└─────────────────────────────────────────────────────────────────────────────┘

SETUP (One-time per plan - Admin):
1. Create Razorpay Plan
   └──> POST /api/subscription/create-plan { plan_id: 1 }
   └──> Stores razorpay_plan_id in database

USER SUBSCRIPTION:
1. User views available plans
   └──> GET /api/subscription/plans

2. User initiates subscription
   └──> POST /api/subscription/subscribe { plan_id: 1 }
   └──> Creates Razorpay subscription
   └──> Returns short_url for UPI mandate

3. User completes UPI mandate (external)
   ├──> Option A: Redirect to short_url
   │    └──> User authorizes recurring payment on Razorpay page
   │
   └──> Option B: Inline checkout with checkout_options
        └──> Use Razorpay.js in your React app

4. Razorpay sends webhook
   └──> POST /api/webhook/razorpay (subscription.authenticated)
   └──> First payment captured, subscription activated

5. Subscription active!
   └──> GET /api/subscription/status

MONTHLY/YEARLY AUTO-RENEWAL:
6. Razorpay auto-charges user on renewal date
   └──> POST /api/webhook/razorpay (subscription.charged)
   └──> Server extends current_period_end
   └──> User continues with premium access

7. Repeat step 6 every billing cycle until cancelled
```

## Endpoints

---

### Get Subscription Plans

Get all available subscription plans.

```http
GET /api/subscription/plans
```

#### Headers

None required (public endpoint)

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "plans": [
      {
        "id": 1,
        "code": "monthly_basic",
        "name": "Basic Monthly",
        "description": "Basic monthly subscription - ₹30/month via UPI autopay",
        "amount": 30,
        "amount_display": "₹30",
        "currency": "INR",
        "billing_period": "monthly",
        "billing_interval": 1,
        "duration_days": 30,
        "features": [
          "Access to premium posters",
          "Standard quality downloads",
          "Auto-renews monthly via UPI"
        ],
        "is_recurring": true,
        "auto_renews": "Monthly"
      },
      {
        "id": 2,
        "code": "yearly_premium",
        "name": "Premium Yearly",
        "description": "Premium yearly subscription - ₹299/year via UPI autopay",
        "amount": 299,
        "amount_display": "₹299",
        "currency": "INR",
        "billing_period": "yearly",
        "billing_interval": 1,
        "duration_days": 365,
        "features": [
          "Access to all premium posters",
          "High quality downloads",
          "Auto-renews yearly via UPI"
        ],
        "is_recurring": true,
        "auto_renews": "Yearly"
      }
    ],
    "note": "All plans auto-renew via UPI autopay. Cancel anytime."
  }
}
```

---

### Create Subscription (Subscribe)

Create a new Razorpay subscription for recurring payments.

```http
POST /api/subscription/subscribe
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `plan_id` | integer | Yes | ID of the subscription plan |

#### Example Request

```json
{
  "plan_id": 1
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription created. Complete payment to activate.",
  "data": {
    "subscription_id": 1,
    "razorpay_subscription_id": "sub_Oq4s7qLhDJbABC",
    "short_url": "https://rzp.io/i/aBcDeFgHi",
    "status": "created",
    "plan": {
      "id": 1,
      "name": "Basic Monthly",
      "amount": 30,
      "billing_period": "monthly"
    },
    "checkout_options": {
      "key": "rzp_test_xxxxx",
      "subscription_id": "sub_Oq4s7qLhDJbABC",
      "name": "BannerBox",
      "description": "Basic Monthly - ₹30/monthly",
      "prefill": {
        "name": "John Doe",
        "email": "john@example.com"
      },
      "theme": {
        "color": "#3B82F6"
      }
    },
    "key_id": "rzp_test_xxxxx",
    "instructions": {
      "redirect": "Redirect user to short_url to complete UPI mandate authorization",
      "inline": "Or use checkout_options with Razorpay.js for inline checkout"
    }
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | plan_id is required |
| 400 | You already have an active subscription |
| 400 | Plan not configured for Razorpay subscriptions |
| 401 | Authorization header missing |
| 404 | Plan not found or inactive |
| 404 | User not found |
| 500 | Failed to create subscription |

#### Usage Options

**Option 1: Redirect to short_url (Recommended for Mobile)**
```javascript
// Redirect user to Razorpay hosted page
window.location.href = response.data.short_url;
// User completes UPI autopay mandate on Razorpay page
// Webhook handles activation automatically
```

**Option 2: Inline Checkout (Recommended for Web)**
```javascript
// Use checkout_options with Razorpay.js
const razorpay = new window.Razorpay(response.data.checkout_options);
razorpay.open();
```

---

### Authenticate Subscription

Verify subscription after UPI mandate is completed (optional - webhook handles this automatically).

```http
POST /api/subscription/authenticate
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `razorpay_subscription_id` | string | Yes | Subscription ID from subscribe response |
| `razorpay_payment_id` | string | Yes | Payment ID from Razorpay callback |
| `razorpay_signature` | string | Yes | Signature from Razorpay callback |

#### Example Request

```json
{
  "razorpay_subscription_id": "sub_Oq4s7qLhDJbABC",
  "razorpay_payment_id": "pay_Oq4tXyZ123ABC",
  "razorpay_signature": "e5e4f7c44db96b8a..."
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription activated successfully! Auto-renews monthly.",
  "data": {
    "subscription_status": "active",
    "subscription_type": "premium",
    "current_period_start": "2026-01-18 12:00:00",
    "current_period_end": "2026-02-18 12:00:00",
    "next_charge_at": "2026-02-18 12:00:00",
    "paid_count": 1,
    "plan": {
      "id": 1,
      "name": "Basic Monthly",
      "billing_period": "monthly"
    }
  }
}
```

#### Note

This endpoint is optional. The webhook endpoint (`subscription.authenticated`) automatically activates subscriptions when users complete the UPI mandate.

---

### Get Subscription Status

Get current subscription status for the authenticated user.

```http
GET /api/subscription/status
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "subscription_type": "premium",
    "subscription_status": "active",
    "is_active": true,
    "is_recurring": true,
    "current_period_start": "2026-01-18 12:00:00",
    "current_period_end": "2026-02-18 12:00:00",
    "next_charge_at": "2026-02-18 12:00:00",
    "days_remaining": 30,
    "paid_count": 2,
    "plan": {
      "id": 1,
      "code": "monthly_basic",
      "name": "Basic Monthly",
      "amount": 30,
      "billing_period": "monthly",
      "duration_days": 30
    },
    "razorpay_subscription_id": "sub_Oq4s7qLhDJbABC"
  }
}
```

#### Status Values

| subscription_status | Description |
|---------------------|-------------|
| `none` | Never subscribed |
| `created` | Subscription created, awaiting first payment |
| `authenticated` | First payment successful, mandate active |
| `active` | Currently subscribed and active |
| `paused` | Temporarily paused (no charges) |
| `halted` | Payment failures, needs attention |
| `cancelled` | Cancelled (access until period end) |
| `expired` | Subscription ended |
| `completed` | All billing cycles completed |

---

### Cancel Subscription

Cancel the user's recurring subscription.

```http
POST /api/subscription/cancel
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `cancel_at_cycle_end` | boolean | No | Default: true. If true, access continues until period ends. If false, cancels immediately. |

#### Example Request

```json
{
  "cancel_at_cycle_end": true
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription cancelled successfully",
  "data": {
    "subscription_status": "cancelled",
    "access_until": "2026-02-18 12:00:00",
    "cancel_at_cycle_end": true,
    "note": "You will continue to have access until February 18, 2026. No further charges will be made."
  }
}
```

#### Notes

- Cancellation stops all future recurring charges
- User retains access until `current_period_end`
- User can resubscribe anytime after cancellation

---

### Pause Subscription

Temporarily pause the subscription. No charges will be made while paused.

```http
POST /api/subscription/pause
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription paused successfully",
  "data": {
    "subscription_status": "paused",
    "note": "No charges will be made until you resume. Use /resume to reactivate."
  }
}
```

---

### Resume Subscription

Resume a paused subscription.

```http
POST /api/subscription/resume
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription resumed successfully",
  "data": {
    "subscription_status": "active",
    "next_charge_at": "2026-02-18 12:00:00"
  }
}
```

---

### Sync Subscription

Sync subscription status from Razorpay (useful if webhooks were missed).

```http
POST /api/subscription/sync
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Subscription synced successfully",
  "data": {
    "subscription_status": "active",
    "current_period_start": "2026-01-18 12:00:00",
    "current_period_end": "2026-02-18 12:00:00",
    "next_charge_at": "2026-02-18 12:00:00",
    "paid_count": 2
  }
}
```

---

### Get Payment History

Get the user's payment and subscription history.

```http
GET /api/subscription/history
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "payments": [
      {
        "payment_id": 1,
        "razorpay_payment_id": "pay_Oq4tXyZ123ABC",
        "amount": 30,
        "amount_display": "₹30",
        "method": "upi",
        "status": "captured",
        "is_recurring": false,
        "billing_cycle": 1,
        "date": "January 18, 2026 12:00 PM"
      },
      {
        "payment_id": 2,
        "razorpay_payment_id": "pay_Pq5uYzA456DEF",
        "amount": 30,
        "amount_display": "₹30",
        "method": "upi",
        "status": "captured",
        "is_recurring": true,
        "billing_cycle": 2,
        "date": "February 18, 2026 12:00 PM"
      }
    ],
    "subscriptions": [
      {
        "subscription_id": 1,
        "razorpay_subscription_id": "sub_Oq4s7qLhDJbABC",
        "plan": {
          "id": 1,
          "name": "Basic Monthly",
          "code": "monthly_basic",
          "billing_period": "monthly"
        },
        "amount": 30,
        "status": "active",
        "paid_count": 2,
        "period_start": "February 18, 2026",
        "period_end": "March 18, 2026",
        "created_at": "January 18, 2026"
      }
    ],
    "summary": {
      "total_payments": 2,
      "successful_payments": 2,
      "recurring_payments": 1,
      "total_spent": 60,
      "total_spent_display": "₹60"
    }
  }
}
```

---

### Create Razorpay Plan (Admin)

Create a Razorpay Plan from a local plan. Must be done before users can subscribe.

```http
POST /api/subscription/create-plan
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `Authorization` | `Bearer <jwt_token>` | Yes (admin) |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `plan_id` | integer | Yes | Local plan ID |

#### Example Request

```json
{
  "plan_id": 1
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Razorpay plan created successfully",
  "data": {
    "plan_id": 1,
    "razorpay_plan_id": "plan_Oq4r8sT9UVWXYZ",
    "razorpay_plan": {
      "id": "plan_Oq4r8sT9UVWXYZ",
      "entity": "plan",
      "interval": 1,
      "period": "monthly",
      "item": {
        "id": "item_Oq4r8sT9ABCDE",
        "name": "Basic Monthly",
        "amount": 3000,
        "currency": "INR"
      }
    }
  }
}
```

#### Admin Setup Flow

1. Add plan to `subscription_plans` table with `billing_period` = 'monthly' or 'yearly'
2. Call this endpoint to create corresponding Razorpay plan
3. Now users can subscribe to this plan

---

## Webhooks

Razorpay sends webhook events for subscription lifecycle changes.

### Configure Webhooks in Razorpay Dashboard

1. Go to **Dashboard → Settings → Webhooks**
2. Add webhook URL: `https://your-api.com/api/webhook/razorpay`
3. Select events:
   - `subscription.authenticated`
   - `subscription.activated`
   - `subscription.charged` ⭐ (handles recurring payments)
   - `subscription.pending`
   - `subscription.halted`
   - `subscription.cancelled`
   - `subscription.paused`
   - `subscription.resumed`
   - `subscription.completed`
4. Copy webhook secret to `config.php`

### Key Webhook Events

| Event | Description | Action |
|-------|-------------|--------|
| `subscription.authenticated` | First payment successful, UPI mandate created | Activate subscription |
| `subscription.charged` | **Recurring payment successful** | Extend `current_period_end` |
| `subscription.cancelled` | Subscription cancelled | Mark as cancelled |
| `subscription.halted` | Multiple payment failures | Mark as halted, notify user |

### Recurring Payment Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         MONTHLY AUTO-RENEWAL                                 │
└─────────────────────────────────────────────────────────────────────────────┘

Day 1: User subscribes
├── current_period_start: Jan 18, 2026
├── current_period_end: Feb 18, 2026
└── paid_count: 1

Day 31: Razorpay auto-charges UPI
├── Webhook: subscription.charged
├── Server extends period:
│   ├── current_period_start: Feb 18, 2026
│   ├── current_period_end: Mar 18, 2026
│   └── paid_count: 2
└── User continues with premium access

Day 62: Razorpay auto-charges again
├── Webhook: subscription.charged
├── current_period_end: Apr 18, 2026
├── paid_count: 3
└── Continues indefinitely until cancelled
```

---

## Frontend Integration

### React Integration Example (Recurring Subscriptions)

```jsx
import React, { useState, useEffect } from 'react';

const SubscriptionPage = () => {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState(null);

  // Fetch plans and current status on mount
  useEffect(() => {
    // Get plans
    fetch('/api/subscription/plans')
      .then(res => res.json())
      .then(data => setPlans(data.data.plans));
    
    // Get current subscription status
    fetch('/api/subscription/status', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
      .then(res => res.json())
      .then(data => setStatus(data.data));
  }, []);

  const handleSubscribe = async (planId) => {
    setLoading(true);
    
    try {
      // 1. Create subscription
      const response = await fetch('/api/subscription/subscribe', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ plan_id: planId })
      });
      
      const data = await response.json();
      
      if (!data.success) {
        alert(data.message);
        return;
      }

      // 2. Option A: Redirect to Razorpay page (simpler)
      // window.location.href = data.data.short_url;
      
      // 2. Option B: Inline checkout
      const options = {
        ...data.data.checkout_options,
        handler: async function(response) {
          // 3. Verify subscription (optional - webhook handles this)
          const verifyResponse = await fetch('/api/subscription/authenticate', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              razorpay_subscription_id: response.razorpay_subscription_id,
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_signature: response.razorpay_signature
            })
          });
          
          const verifyData = await verifyResponse.json();
          
          if (verifyData.success) {
            alert('Subscription activated! Auto-renews monthly.');
            window.location.reload();
          } else {
            alert('Verification failed. Please wait for webhook.');
          }
        }
      };

      const razorpay = new window.Razorpay(options);
      razorpay.open();
      
    } catch (error) {
      console.error('Subscription error:', error);
      alert('Something went wrong');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async () => {
    if (!confirm('Are you sure you want to cancel? You will retain access until the current period ends.')) {
      return;
    }
    
    const response = await fetch('/api/subscription/cancel', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ cancel_at_cycle_end: true })
    });
    
    const data = await response.json();
    alert(data.message);
    window.location.reload();
  };

  return (
    <div>
      {/* Current Status */}
      {status?.is_active && (
        <div className="current-subscription">
          <h2>Your Subscription</h2>
          <p>Plan: {status.plan.name}</p>
          <p>Status: {status.subscription_status}</p>
          <p>Next renewal: {status.next_charge_at}</p>
          <p>Payments made: {status.paid_count}</p>
          <button onClick={handleCancel}>Cancel Subscription</button>
        </div>
      )}
      
      {/* Plans */}
      <h1>Choose a Plan</h1>
      <p>All plans auto-renew via UPI autopay. Cancel anytime.</p>
      {plans.map(plan => (
        <div key={plan.id} className="plan-card">
          <h2>{plan.name}</h2>
          <p className="price">{plan.amount_display}/{plan.billing_period}</p>
          <p className="auto-renew">Auto-renews {plan.auto_renews}</p>
          <ul>
            {plan.features.map((feature, i) => (
              <li key={i}>{feature}</li>
            ))}
          </ul>
          <button 
            onClick={() => handleSubscribe(plan.id)}
            disabled={loading || status?.is_active}
          >
            {status?.is_active ? 'Already Subscribed' : 'Subscribe Now'}
          </button>
        </div>
      ))}
    </div>
  );
};

export default SubscriptionPage;
```

### Loading Razorpay Script

Add to your `index.html`:

```html
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
```

---

## Database Schema

### subscription_plans

| Column | Type | Description |
|--------|------|-------------|
| plan_id | INT | Primary key |
| plan_code | VARCHAR(50) | Unique plan identifier |
| name | VARCHAR(100) | Display name |
| description | TEXT | Plan description |
| amount | DECIMAL(10,2) | Price in INR |
| currency | VARCHAR(10) | Currency code |
| billing_period | ENUM | 'monthly', 'yearly', 'daily', 'weekly' |
| billing_interval | INT | Interval count (1 = every month) |
| duration_days | INT | Period duration in days |
| total_billing_cycles | INT | NULL = infinite, or specific count |
| features | TEXT | JSON array of features |
| razorpay_plan_id | VARCHAR(100) | Razorpay Plan ID |
| is_active | TINYINT | 1 = active, 0 = inactive |

### subscriptions

| Column | Type | Description |
|--------|------|-------------|
| subscription_id | INT | Primary key |
| user_id | INT | User ID |
| plan_id | INT | Plan ID |
| razorpay_subscription_id | VARCHAR(100) | Razorpay Subscription ID |
| razorpay_short_url | VARCHAR(255) | Payment URL |
| status | ENUM | created, authenticated, active, paused, halted, cancelled, expired, completed |
| current_period_start | DATETIME | Period start date |
| current_period_end | DATETIME | Period end date |
| charge_at | DATETIME | Next charge timestamp |
| paid_count | INT | Number of successful payments |
| ended_at | DATETIME | When subscription ended |

### payments

| Column | Type | Description |
|--------|------|-------------|
| payment_id | INT | Primary key |
| user_id | INT | User ID |
| subscription_id | INT | Subscription ID |
| razorpay_payment_id | VARCHAR(100) | Razorpay Payment ID |
| razorpay_subscription_id | VARCHAR(100) | Razorpay Subscription ID |
| amount | DECIMAL(10,2) | Amount in INR |
| method | VARCHAR(50) | Payment method (upi, card, etc.) |
| status | ENUM | created, authorized, captured, failed, refunded |
| is_recurring | TINYINT | 0 = first payment, 1 = renewal |
| billing_cycle | INT | Which billing cycle this payment is for |

---

## FAQ

### How does auto-renewal work?

1. When a user subscribes, they authorize a UPI mandate (recurring payment permission)
2. Razorpay stores this mandate and automatically charges the user on each renewal date
3. Our server receives a `subscription.charged` webhook and extends the subscription period
4. The user's access continues seamlessly without any action required

### What happens if a payment fails?

1. Razorpay retries failed payments according to their retry schedule
2. After multiple failures, subscription status changes to `halted`
3. User is notified and can update payment method
4. You can also use `sync` endpoint to refresh status

### How do I test subscriptions?

1. Use Razorpay test mode credentials
2. Use test UPI IDs like `success@razorpay` or `failure@razorpay`
3. Webhook events will still fire in test mode

### Can users change plans?

Currently, users need to cancel existing subscription and create new one. Plan switching can be added in a future update.
