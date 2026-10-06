<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('employee_id')->constrained('customers')->nullOnDelete();
            $table->string('verification_code', 10)->nullable()->after('email_verified_at');
            $table->timestamp('verification_expires_at')->nullable()->after('verification_code');
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->text('decline_reason')->nullable()->after('notes');
            $table->foreignId('responded_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable()->after('responded_by');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropForeign(['responded_by']);
            $table->dropColumn(['decline_reason', 'responded_by', 'responded_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'verification_code', 'verification_expires_at']);
        });
    }
};
