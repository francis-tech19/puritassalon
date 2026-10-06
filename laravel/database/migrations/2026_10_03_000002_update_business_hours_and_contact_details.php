<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->time('weekend_opening_time')->default('09:00:00');
            $table->time('weekend_closing_time')->default('17:00:00');
            $table->string('contact_phone_secondary', 20)->nullable();
        });

        DB::table('business_settings')
            ->where('address', '123 Katipunan Avenue, Quezon City, Metro Manila')
            ->where('contact_phone', '0917-123-4567')
            ->where('contact_email', 'puritasbeautylounge@gmail.com')
            ->update([
                'address' => 'Poblacion Public Market, San Juan, Batangas',
                'opening_time' => '10:00:00',
                'closing_time' => '16:00:00',
                'weekend_opening_time' => '09:00:00',
                'weekend_closing_time' => '17:00:00',
                'contact_phone' => '09611556557',
                'contact_phone_secondary' => '09192001649',
                'contact_email' => 'dcsisters@yahoo.com',
            ]);
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'weekend_opening_time',
                'weekend_closing_time',
                'contact_phone_secondary',
            ]);
        });
    }
};