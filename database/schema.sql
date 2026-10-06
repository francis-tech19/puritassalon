-- ====================================================================
-- SALON MANAGEMENT SYSTEM FOR PURITA'S BEAUTY LOUNGE
-- MySQL Database Schema
-- Version: 1.0.0
-- Compatible with MySQL 8.0+
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `purita_salon_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `purita_salon_db`;

-- Set Foreign Key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist (Clean Setup)
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `fcm_tokens`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `loyalty_rewards`;
DROP TABLE IF EXISTS `loyalty_settings`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `inventory_transactions`;
DROP TABLE IF EXISTS `inventory`;
DROP TABLE IF EXISTS `appointment_services`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `business_settings`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. ROLES TABLE
-- --------------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. EMPLOYEES TABLE
-- --------------------------------------------------------------------
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_code` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `position` VARCHAR(50) DEFAULT 'Senior Stylist',
  `status` ENUM('ACTIVE', 'INACTIVE', 'ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `schedule_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. USERS TABLE (System Login Accounts for Owner & Staff)
-- --------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `employee_id` INT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. CUSTOMERS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_code` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('ACTIVE', 'ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `visit_count` INT NOT NULL DEFAULT 0,
  `total_spent` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. SERVICES TABLE
-- --------------------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Hair Care',
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 30,
  `status` ENUM('ACTIVE', 'INACTIVE', 'ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. APPOINTMENTS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_code` VARCHAR(20) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `employee_id` INT NOT NULL,
  `appointment_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `status` ENUM('PENDING', 'CONFIRMED', 'COMPLETED', 'CANCELLED', 'NO_SHOW') NOT NULL DEFAULT 'PENDING',
  `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_apt_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_apt_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_apt_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_apt_date_employee` (`appointment_date`, `employee_id`),
  INDEX `idx_apt_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. APPOINTMENT_SERVICES JUNCTION TABLE
-- --------------------------------------------------------------------
CREATE TABLE `appointment_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  CONSTRAINT `fk_as_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_as_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. INVENTORY TABLE
-- --------------------------------------------------------------------
CREATE TABLE `inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_code` VARCHAR(20) NOT NULL UNIQUE,
  `item_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Hair Supplies',
  `quantity` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
  `min_stock_level` INT NOT NULL DEFAULT 5,
  `supplier` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK', 'ARCHIVED') NOT NULL DEFAULT 'IN_STOCK',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 9. INVENTORY_TRANSACTIONS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `inventory_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inventory_id` INT NOT NULL,
  `transaction_type` ENUM('STOCK_IN', 'STOCK_OUT', 'ADJUSTMENT', 'SERVICE_USAGE') NOT NULL,
  `quantity_change` INT NOT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  `recorded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_it_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_it_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 10. SALES TABLE (POS Transactions)
-- --------------------------------------------------------------------
CREATE TABLE `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(30) NOT NULL UNIQUE,
  `appointment_id` INT DEFAULT NULL,
  `customer_id` INT NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('CASH', 'GCASH_MANUAL', 'OTHER_MANUAL') NOT NULL DEFAULT 'CASH',
  `payment_status` ENUM('PAID', 'REFUNDED') NOT NULL DEFAULT 'PAID',
  `recorded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_sales_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 11. SALE_ITEMS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `sale_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `item_type` ENUM('SERVICE', 'PRODUCT') NOT NULL,
  `item_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  CONSTRAINT `fk_si_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 12. EXPENSES TABLE
-- --------------------------------------------------------------------
CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expense_code` VARCHAR(20) NOT NULL UNIQUE,
  `category` ENUM('SUPPLIES', 'UTILITIES', 'MAINTENANCE', 'INVENTORY_PURCHASE', 'RENT', 'OTHER') NOT NULL DEFAULT 'SUPPLIES',
  `title` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `recorded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_expenses_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_expense_date` (`expense_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 13. LOYALTY_SETTINGS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `loyalty_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `visits_required_for_reward` INT NOT NULL DEFAULT 5,
  `reward_description` VARCHAR(255) NOT NULL DEFAULT '10% Discount on Next Hair/Nail Service',
  `discount_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 14. LOYALTY_REWARDS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `loyalty_rewards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `reward_title` VARCHAR(100) NOT NULL,
  `discount_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
  `status` ENUM('AVAILABLE', 'REDEEMED', 'EXPIRED') NOT NULL DEFAULT 'AVAILABLE',
  `earned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `redeemed_date` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_lr_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 15. NOTIFICATIONS TABLE (In-App Notifications)
-- --------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('APPOINTMENT_REMINDER', 'LOW_STOCK', 'SYSTEM', 'STATUS_UPDATE') NOT NULL DEFAULT 'SYSTEM',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 16. FCM_TOKENS TABLE (Firebase Cloud Messaging Tokens)
-- --------------------------------------------------------------------
CREATE TABLE `fcm_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `fcm_token` VARCHAR(255) NOT NULL UNIQUE,
  `device_info` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_fcm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 17. AUDIT_LOGS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_audit_module` (`module`),
  INDEX `idx_audit_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 18. BUSINESS_SETTINGS TABLE
-- --------------------------------------------------------------------
CREATE TABLE `business_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `salon_name` VARCHAR(100) NOT NULL DEFAULT "Purita's Beauty Lounge",
  `opening_time` TIME NOT NULL DEFAULT '09:00:00',
  `closing_time` TIME NOT NULL DEFAULT '19:00:00',
  `contact_phone` VARCHAR(20) DEFAULT '0917-123-4567',
  `contact_email` VARCHAR(100) DEFAULT 'puritasbeautylounge@gmail.com',
  `address` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
