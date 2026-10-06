# Database Documentation - Purita's Beauty Lounge Salon Management System

## Setup Instructions

1. Ensure MySQL Server 8.0 or higher is installed and running.
2. Log into MySQL shell or your GUI database client (e.g. MySQL Workbench, phpMyAdmin, DBeaver, TablePlus):
   ```bash
   mysql -u root -p
   ```
3. Import `schema.sql` to build the database and normalized table structures:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
4. Import `seed.sql` to populate sample salon data:
   ```bash
   mysql -u root -p purita_salon_db < database/seed.sql
   ```

## Database Tables Summary (18 Tables)
- `roles`: User roles (`OWNER`, `STAFF`).
- `employees`: Salon staff members & schedules.
- `users`: User login credentials (bcrypt password hash).
- `customers`: Customer records, visit counts & lifetime spending.
- `services`: Salon services, prices & duration.
- `appointments`: Booking entries with start/end time & status.
- `appointment_services`: Multi-service junction table.
- `inventory`: Hair & beauty products stock tracking with low-stock thresholds.
- `inventory_transactions`: Audit trail for stock additions and usages.
- `sales`: Completed POS billing transactions & payment methods (`CASH`, `GCASH_MANUAL`, `OTHER_MANUAL`).
- `sale_items`: Service and product itemized sales details.
- `expenses`: Salon operational expenses & category tracking.
- `loyalty_settings`: Configurable visit target & reward discount settings.
- `loyalty_rewards`: Issued customer loyalty rewards.
- `notifications`: In-app fallback notifications.
- `fcm_tokens`: Device web push notification tokens.
- `audit_logs`: Activity and security audit trail.
- `business_settings`: Operating hours & salon metadata.
