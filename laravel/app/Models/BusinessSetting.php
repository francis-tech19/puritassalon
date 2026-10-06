<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'salon_name',
        'opening_time',
        'closing_time',
        'weekend_opening_time',
        'weekend_closing_time',
        'contact_phone',
        'contact_phone_secondary',
        'contact_email',
        'address',
        'appointment_reminder_minutes',
        'late_threshold_minutes',
    ];
}
