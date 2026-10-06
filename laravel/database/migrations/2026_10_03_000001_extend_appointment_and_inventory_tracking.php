<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->json('working_days')->nullable();
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('arrival_status', 20)->default('NOT_ARRIVED');
            $table->timestamp('arrival_time')->nullable();
            $table->foreignId('arrived_marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['employee_id', 'appointment_date', 'start_time', 'end_time'], 'appointments_employee_schedule_index');
        });

        Schema::table('business_settings', function (Blueprint $table): void {
            $table->string('appointment_reminder_minutes')->default('30,15,0');
            $table->unsignedSmallInteger('late_threshold_minutes')->default(10);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
        });

        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->string('action', 20)->nullable();
            $table->integer('previous_stock')->nullable();
            $table->integer('new_stock')->nullable();
            $table->index(['created_at', 'action'], 'inventory_transactions_action_date_index');
        });

        Schema::create('appointment_notification_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('event_key', 40);
            $table->timestamps();
            $table->unique(['appointment_id', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_notification_events');

        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->dropIndex('inventory_transactions_action_date_index');
            $table->dropColumn(['action', 'previous_stock', 'new_stock']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('appointment_id');
        });

        Schema::table('business_settings', function (Blueprint $table): void {
            $table->dropColumn(['appointment_reminder_minutes', 'late_threshold_minutes']);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex('appointments_employee_schedule_index');
            $table->dropConstrainedForeignId('arrived_marked_by');
            $table->dropColumn(['arrival_status', 'arrival_time']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['working_days', 'shift_start_time', 'shift_end_time']);
        });
    }
};