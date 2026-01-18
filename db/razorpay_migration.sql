-- Razorpay Subscriptions Integration Migration
-- Run this SQL to add subscription and payment tables
-- Date: January 18, 2026
-- 
-- This implements Razorpay's Subscriptions API for recurring UPI payments
-- NOT one-time orders - subscriptions auto-renew monthly/yearly

-- --------------------------------------------------------
-- Table: subscription_plans - available subscription plans
-- Stores local plans that link to Razorpay Plan IDs
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `plan_id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_code` VARCHAR(50) NOT NULL,
  `razorpay_plan_id` VARCHAR(100) DEFAULT NULL COMMENT 'Razorpay Plan ID (plan_xxxxx) - must create in Razorpay first',
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL COMMENT 'Amount per billing cycle in INR',
  `currency` VARCHAR(10) DEFAULT 'INR',
  `billing_period` ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly' COMMENT 'Razorpay billing period',
  `billing_interval` INT(11) DEFAULT 1 COMMENT 'Billing interval (1 = every month, 3 = every 3 months)',
  `duration_days` INT(11) NOT NULL DEFAULT 30 COMMENT 'Days per billing cycle (30 for monthly, 365 for yearly)',
  `total_billing_cycles` INT(11) DEFAULT NULL COMMENT 'NULL = infinite recurring',
  `features` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`plan_id`),
  UNIQUE KEY `idx_plan_code` (`plan_code`),
  UNIQUE KEY `idx_razorpay_plan_id` (`razorpay_plan_id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default plans
-- NOTE: razorpay_plan_id must be created in Razorpay Dashboard first or via API
-- These are placeholder values - update with actual Razorpay Plan IDs after creation
INSERT INTO `subscription_plans` (`plan_code`, `razorpay_plan_id`, `name`, `description`, `amount`, `currency`, `billing_period`, `billing_interval`, `duration_days`, `total_billing_cycles`, `features`, `is_active`, `sort_order`) VALUES
('monthly_basic', NULL, 'Basic Monthly', 'Basic monthly subscription - ₹30/month via UPI autopay', 30.00, 'INR', 'monthly', 1, 30, NULL, '["Access to premium posters","Standard quality downloads","Email support","Auto-renews monthly"]', 1, 1),
('monthly_premium', NULL, 'Premium Monthly', 'Premium monthly subscription - ₹99/month via UPI autopay', 99.00, 'INR', 'monthly', 1, 30, NULL, '["Access to all premium posters","High quality downloads without watermark","Unlimited downloads","Priority customer support","Early access to new designs","Auto-renews monthly"]', 1, 2),
('yearly_premium', NULL, 'Premium Yearly', 'Premium yearly subscription - ₹999/year via UPI autopay (Save 20%)', 999.00, 'INR', 'yearly', 1, 365, NULL, '["Access to all premium posters","High quality downloads without watermark","Unlimited downloads","Priority customer support","Early access to new designs","20% discount","Auto-renews yearly"]', 1, 3);

-- --------------------------------------------------------
-- Table: subscriptions - tracks user subscription status
-- Links to Razorpay Subscription for recurring billing
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `plan_id` INT(11) NOT NULL,
  `razorpay_subscription_id` VARCHAR(100) DEFAULT NULL COMMENT 'Razorpay Subscription ID (sub_xxxxx)',
  `razorpay_short_url` VARCHAR(255) DEFAULT NULL COMMENT 'Razorpay hosted payment page URL',
  `status` ENUM('created', 'authenticated', 'active', 'pending', 'halted', 'paused', 'cancelled', 'completed', 'expired') DEFAULT 'created',
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 30.00,
  `currency` VARCHAR(10) DEFAULT 'INR',
  `billing_period` ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly',
  `charge_at` DATETIME DEFAULT NULL COMMENT 'Next charge date from Razorpay',
  `offer_id` VARCHAR(100) DEFAULT NULL COMMENT 'Razorpay offer ID if any',
  `current_period_start` DATETIME DEFAULT NULL,
  `current_period_end` DATETIME DEFAULT NULL,
  `paid_count` INT(11) DEFAULT 0 COMMENT 'Number of successful charges',
  `auth_attempts` INT(11) DEFAULT 0 COMMENT 'UPI mandate auth attempts',
  `cancelled_at` DATETIME DEFAULT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`subscription_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `idx_razorpay_subscription_id` (`razorpay_subscription_id`),
  KEY `idx_current_period_end` (`current_period_end`),
  KEY `idx_charge_at` (`charge_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: payments - tracks all payment transactions (recurring charges)
-- Each subscription.charged event creates a new payment record
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `subscription_id` INT(11) DEFAULT NULL,
  `razorpay_payment_id` VARCHAR(100) DEFAULT NULL COMMENT 'Razorpay Payment ID (pay_xxxxx)',
  `razorpay_invoice_id` VARCHAR(100) DEFAULT NULL COMMENT 'Razorpay Invoice ID if any',
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'INR',
  `method` VARCHAR(50) DEFAULT NULL COMMENT 'upi, card, netbanking, wallet',
  `status` ENUM('created', 'authorized', 'captured', 'failed', 'refunded') DEFAULT 'created',
  `is_recurring` TINYINT(1) DEFAULT 0 COMMENT '1 if this is an auto-debit charge',
  `billing_cycle` INT(11) DEFAULT 1 COMMENT 'Which billing cycle this payment is for',
  `error_code` VARCHAR(100) DEFAULT NULL,
  `error_description` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_subscription_id` (`subscription_id`),
  UNIQUE KEY `idx_razorpay_payment_id` (`razorpay_payment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_recurring` (`is_recurring`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: webhook_logs - for debugging and audit
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `webhook_logs` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(100) NOT NULL,
  `razorpay_subscription_id` VARCHAR(100) DEFAULT NULL COMMENT 'For easier filtering',
  `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `signature` VARCHAR(255) DEFAULT NULL,
  `is_valid` TINYINT(1) DEFAULT 0,
  `processed` TINYINT(1) DEFAULT 0,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_razorpay_subscription_id` (`razorpay_subscription_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Alter users table - add subscription columns
-- --------------------------------------------------------

-- Note: If columns already exist, these statements will fail. That's expected.
-- You can run them only once or manually check if columns exist before running.

ALTER TABLE `users` 
ADD COLUMN `subscription_status` ENUM('none', 'active', 'pending', 'halted', 'paused', 'expired', 'cancelled') DEFAULT 'none' AFTER `subscription_type`,
ADD COLUMN `subscription_expires_at` DATETIME DEFAULT NULL AFTER `subscription_status`,
ADD COLUMN `razorpay_customer_id` VARCHAR(100) DEFAULT NULL AFTER `subscription_expires_at`;

-- Add indexes for subscription fields
ALTER TABLE `users` ADD INDEX `idx_subscription_status` (`subscription_status`);
ALTER TABLE `users` ADD INDEX `idx_subscription_expires_at` (`subscription_expires_at`);
