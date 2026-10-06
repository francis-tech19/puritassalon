<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRating extends Model
{
    protected $fillable = ['customer_id', 'service_id', 'appointment_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}