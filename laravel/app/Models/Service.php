<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'category',
        'description',
        'photo_path',
        'price',
        'duration_minutes',
        'status',
    ];

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function ratings()
    {
        return $this->hasMany(ServiceRating::class);
    }
}
