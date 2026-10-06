-- ====================================================================
-- SALON MANAGEMENT SYSTEM FOR PURITA'S BEAUTY LOUNGE
-- Sample Seed Data
-- ====================================================================

USE `purita_salon_db`;

-- 1. Roles
INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'OWNER'),
(2, 'STAFF'),
(3, 'ADMIN');

-- 2. Employees
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `phone`, `email`, `position`, `status`, `schedule_notes`) VALUES
(1, 'EMP-001', 'Purita Santos', '0917-111-2233', 'purita@beauty.com', 'Salon Owner & Master Stylist', 'ACTIVE', 'Mon - Sat (9:00 AM - 6:00 PM)'),
(2, 'EMP-002', 'Maria Clara Dela Cruz', '0918-222-3344', 'maria@beauty.com', 'Senior Hair Stylist', 'ACTIVE', 'Tue - Sun (9:00 AM - 7:00 PM)'),
(3, 'EMP-003', 'Angela Reyes', '0919-333-4455', 'angela@beauty.com', 'Nail & Facial Technician', 'ACTIVE', 'Mon - Fri (10:00 AM - 6:00 PM)'),
(4, 'EMP-004', 'Jenny Mercado', '0920-444-5566', 'jenny@beauty.com', 'Junior Stylist', 'ACTIVE', 'Wed - Mon (9:00 AM - 5:00 PM)');

-- 3. Users (Passwords hashed for 'password123': $2b$10$Z1x3C5v7B9n1M2K3L4P5O6I7U8Y9T0R1e2W3Q4E5R6T7Y8U9I0O1P)
-- Note: The backend authentication engine also supports auto-migrating/seeding default users if database is fresh.
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`, `employee_id`, `is_active`) VALUES
(1, 'owner', 'owner@purita.com', '$2a$10$7R0Z4Q6Qz.6OaX9Z.6z8ue4v.rZ4kY4cO.5Z.2g4J8Z.6Z8ue4v.', 1, 1, 1),
(2, 'staff1', 'staff@purita.com', '$2a$10$7R0Z4Q6Qz.6OaX9Z.6z8ue4v.rZ4kY4cO.5Z.2g4J8Z.6Z8ue4v.', 2, 2, 1),
(3, 'admin', 'admin@purita.com', '$2a$10$7R0Z4Q6Qz.6OaX9Z.6z8ue4v.rZ4kY4cO.5Z.2g4J8Z.6Z8ue4v.', 3, 1, 1);

-- 4. Customers
INSERT INTO `customers` (`id`, `customer_code`, `full_name`, `phone`, `email`, `address`, `notes`, `status`, `visit_count`, `total_spent`) VALUES
(1, 'CUST-001', 'Ana Maria Gonzales', '0917-555-0101', 'ana.gonzales@gmail.com', 'Quezon City, Metro Manila', 'Prefers organic hair color treatments', 'ACTIVE', 6, 4500.00),
(2, 'CUST-002', 'Bea Alonzo', '0918-555-0202', 'bea.a@yahoo.com', 'Makati City', 'Regular customer for re-bonding', 'ACTIVE', 4, 3800.00),
(3, 'CUST-003', 'Carla Abellana', '0919-555-0303', 'carla.a@gmail.com', 'Pasig City', 'Sensitive skin, test polish first', 'ACTIVE', 2, 1200.00),
(4, 'CUST-004', 'Donna Cruz', '0920-555-0404', 'donna.cruz@outlook.com', 'Mandaluyong City', 'Loves gel manicures', 'ACTIVE', 5, 2900.00),
(5, 'CUST-005', 'Elena Adarna', '0921-555-0505', 'elena.a@gmail.com', 'San Juan City', 'Walk-in customer', 'ACTIVE', 1, 450.00);

-- 5. Services
INSERT INTO `services` (`id`, `service_name`, `category`, `description`, `price`, `duration_minutes`, `status`) VALUES
(1, 'Haircut & Blowdry (Women)', 'Hair Care', 'Precision haircut with washing and blowdry styling', 450.00, 45, 'ACTIVE'),
(2, 'Haircut (Men)', 'Hair Care', 'Classic and modern men haircuts with hot towel finish', 300.00, 30, 'ACTIVE'),
(3, 'Full Hair Coloring', 'Hair Care', 'Premium hair color with scalp protection and wash', 1500.00, 90, 'ACTIVE'),
(4, 'Keratin Hair Rebonding', 'Hair Care', 'Intense hair smoothing and straightening treatment', 2500.00, 150, 'ACTIVE'),
(5, 'Classic Manicure', 'Nail Care', 'Nail shaping, cuticle care, and polish application', 250.00, 30, 'ACTIVE'),
(6, 'Classic Pedicure', 'Nail Care', 'Foot soak, scrub, nail shaping, and polish', 350.00, 45, 'ACTIVE'),
(7, 'Gel Manicure & Pedicure Combo', 'Nail Care', 'Long-lasting gel polish manicure and pedicure package', 900.00, 75, 'ACTIVE'),
(8, 'Facial Deep Cleansing', 'Facial & Skin', 'Deep pore cleansing, exfoliation, and soothing facial mask', 800.00, 60, 'ACTIVE');

-- 6. Inventory Items
INSERT INTO `inventory` (`id`, `item_code`, `item_name`, `category`, `quantity`, `unit`, `min_stock_level`, `supplier`, `status`) VALUES
(1, 'INV-001', 'Organic Hair Color Cream (Dark Brown)', 'Hair Supplies', 12, 'tubes', 5, 'L’Oreal Philippines', 'IN_STOCK'),
(2, 'INV-002', 'Keratin Straightening Solution 1000ml', 'Hair Supplies', 3, 'bottles', 4, 'Schwarzkopf Distributor', 'LOW_STOCK'),
(3, 'INV-003', 'Clarifying Shampoo 500ml', 'Hair Supplies', 8, 'bottles', 3, 'Wella Professional', 'IN_STOCK'),
(4, 'INV-004', 'Nail Polish Remover 500ml', 'Nail Supplies', 2, 'bottles', 3, 'Beauty Supplies Inc', 'LOW_STOCK'),
(5, 'INV-005', 'Premium Red Gel Nail Polish', 'Nail Supplies', 15, 'bottles', 5, 'OPI Philippines', 'IN_STOCK'),
(6, 'INV-006', 'Disposable Towels (Pack of 100)', 'General Salon', 1, 'packs', 2, 'Manila Hygiene Co', 'LOW_STOCK');

-- 7. Inventory Transactions
INSERT INTO `inventory_transactions` (`id`, `inventory_id`, `transaction_type`, `quantity_change`, `notes`, `recorded_by`) VALUES
(1, 1, 'STOCK_IN', 20, 'Initial monthly stock delivery', 1),
(2, 2, 'STOCK_IN', 5, 'Initial stock purchase', 1),
(3, 4, 'STOCK_OUT', -1, 'Daily consumption in salon station', 2);

-- 8. Business Settings
INSERT INTO `business_settings` (`id`, `salon_name`, `opening_time`, `closing_time`, `contact_phone`, `contact_email`, `address`) VALUES
(1, 'Purita’s Beauty Lounge', '09:00:00', '19:00:00', '0917-123-4567', 'puritasbeautylounge@gmail.com', '123 Katipunan Avenue, Quezon City, Metro Manila');

-- 9. Loyalty Settings
INSERT INTO `loyalty_settings` (`id`, `visits_required_for_reward`, `reward_description`, `discount_percentage`, `is_active`) VALUES
(1, 5, '10% Discount on Next Service for 5 Completed Visits', 10.00, 1);

-- 10. Loyalty Rewards (Sample issued reward)
INSERT INTO `loyalty_rewards` (`id`, `customer_id`, `reward_title`, `discount_percentage`, `status`) VALUES
(1, 1, '10% Loyalty Reward (5 Visits Reached)', 10.00, 'AVAILABLE');

-- 11. Appointments (Recent & Upcoming)
INSERT INTO `appointments` (`id`, `appointment_code`, `customer_id`, `employee_id`, `appointment_date`, `start_time`, `end_time`, `status`, `total_amount`, `notes`, `created_by`) VALUES
(1, 'APT-20260801-01', 1, 2, CURRENT_DATE(), '09:30:00', '11:00:00', 'COMPLETED', 1500.00, 'Hair color retouch', 1),
(2, 'APT-20260801-02', 2, 2, CURRENT_DATE(), '11:30:00', '14:00:00', 'CONFIRMED', 2500.00, 'Keratin Rebonding appointment', 1),
(3, 'APT-20260801-03', 3, 3, CURRENT_DATE(), '14:30:00', '15:45:00', 'PENDING', 900.00, 'Gel manicure & pedicure', 2),
(4, 'APT-20260802-01', 4, 1, DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), '10:00:00', '10:45:00', 'CONFIRMED', 450.00, 'Haircut & blowdry', 1);

-- 12. Appointment Services
INSERT INTO `appointment_services` (`id`, `appointment_id`, `service_id`, `price`) VALUES
(1, 1, 3, 1500.00),
(2, 2, 4, 2500.00),
(3, 3, 7, 900.00),
(4, 4, 1, 450.00);

-- 13. Sales (POS Transactions)
INSERT INTO `sales` (`id`, `invoice_number`, `appointment_id`, `customer_id`, `subtotal`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `recorded_by`, `created_at`) VALUES
(1, 'INV-20260801-001', 1, 1, 1500.00, 0.00, 1500.00, 'CASH', 'PAID', 1, NOW()),
(2, 'INV-20260801-002', NULL, 4, 900.00, 0.00, 900.00, 'GCASH_MANUAL', 'PAID', 2, NOW());

-- 14. Sale Items
INSERT INTO `sale_items` (`id`, `sale_id`, `item_type`, `item_id`, `item_name`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 'SERVICE', 3, 'Full Hair Coloring', 1, 1500.00, 1500.00),
(2, 2, 'SERVICE', 7, 'Gel Manicure & Pedicure Combo', 1, 900.00, 900.00);

-- 15. Expenses
INSERT INTO `expenses` (`id`, `expense_code`, `category`, `title`, `amount`, `expense_date`, `notes`, `recorded_by`) VALUES
(1, 'EXP-202608-01', 'SUPPLIES', 'Purchase of Hair Dyes & Hydrogen Peroxide', 3200.00, CURRENT_DATE(), 'Purchased from L’Oreal Supplier', 1),
(2, 'EXP-202608-02', 'UTILITIES', 'Meralco Electric Bill - August', 5400.00, CURRENT_DATE(), 'Paid online', 1);

-- 16. In-App Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Low Stock Alert', 'Low Stock Alert: Keratin Straightening Solution 1000ml needs restocking.', 'LOW_STOCK', 0, NOW()),
(2, 1, 'Upcoming Appointment', 'Upcoming Appointment: Bea Alonzo at 11:30 AM with Maria Clara Dela Cruz.', 'APPOINTMENT_REMINDER', 0, NOW()),
(3, 2, 'New Appointment Scheduled', 'New appointment scheduled for Carla Abellana today at 2:30 PM.', 'STATUS_UPDATE', 1, NOW());

-- 17. Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `details`, `ip_address`) VALUES
(1, 1, 'LOGIN', 'AUTHENTICATION', 'Owner logged into the system successfully', '127.0.0.1'),
(2, 1, 'CREATE_APPOINTMENT', 'APPOINTMENTS', 'Created appointment APT-20260801-01 for Ana Maria Gonzales', '127.0.0.1'),
(3, 2, 'RECORD_SALE', 'SALES', 'Recorded sale INV-20260801-002 of ₱900.00 via GCASH_MANUAL', '127.0.0.1');
