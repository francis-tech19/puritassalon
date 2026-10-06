<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 20)->unique();
            $table->string('full_name', 100);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['ACTIVE', 'ARCHIVED'])->default('ACTIVE');
            $table->integer('visit_count')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name', 100);
            $table->string('category', 50)->default('Hair Care');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // 3. Inventory
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 20)->unique();
            $table->string('item_name', 100);
            $table->string('category', 50)->default('Hair Supplies');
            $table->integer('quantity')->default(0);
            $table->string('unit', 20)->default('pcs');
            $table->integer('min_stock_level')->default(5);
            $table->string('supplier', 100)->nullable();
            $table->enum('status', ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'])->default('IN_STOCK');
            $table->timestamps();
        });

        // 4. Inventory Transactions
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');
            $table->enum('transaction_type', ['STOCK_IN', 'STOCK_OUT', 'ADJUSTMENT']);
            $table->integer('quantity_change');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['PENDING', 'CONFIRMED', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Appointment Services
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('price_at_booking', 10, 2);
            $table->timestamps();
        });

        // 7. Sales Transactions
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 30)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('payment_method', ['CASH', 'GCASH', 'MAYA', 'CARD', 'OTHER'])->default('CASH');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('final_amount', 10, 2);
            $table->enum('status', ['COMPLETED', 'REFUNDED', 'VOIDED'])->default('COMPLETED');
            $table->timestamps();
        });

        // 8. Sale Items
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->enum('item_type', ['SERVICE', 'PRODUCT']);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name', 100);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        // 9. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_category', 50)->default('Utilities');
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Loyalty Settings
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('visits_required_for_reward')->default(5);
            $table->string('reward_description', 255)->default('10% Discount on Next Service');
            $table->decimal('discount_percentage', 5, 2)->default(10.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 11. Loyalty Rewards
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('reward_title', 255);
            $table->decimal('discount_percentage', 5, 2)->default(10.00);
            $table->enum('status', ['AVAILABLE', 'REDEEMED', 'EXPIRED'])->default('AVAILABLE');
            $table->timestamp('issued_date')->useCurrent();
            $table->timestamp('redeemed_date')->nullable();
            $table->timestamps();
        });

        // 12. Business Settings
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('salon_name', 100)->default("Purita's Beauty Lounge");
            $table->time('opening_time')->default('09:00:00');
            $table->time('closing_time')->default('19:00:00');
            $table->string('contact_phone', 20)->default('0917-123-4567');
            $table->string('contact_email', 100)->default('puritasbeautylounge@gmail.com');
            $table->string('address', 255)->default('123 Katipunan Avenue, Quezon City, Metro Manila');
            $table->timestamps();
        });

        // 13. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('message');
            $table->enum('type', ['APPOINTMENT', 'INVENTORY', 'SYSTEM', 'LOYALTY', 'SALES'])->default('SYSTEM');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 14. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('module', 50);
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_settings');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('appointment_services');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('services');
        Schema::dropIfExists('customers');
    }
};
