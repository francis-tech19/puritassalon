<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'appointment_code',
        'customer_id',
        'employee_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'total_amount',
        'notes',
        'created_by',
        'no_show',
        'decline_reason',
        'responded_by',
        'responded_at',
        'arrival_status',
        'arrival_time',
        'arrived_marked_by',
    ];

    protected $casts = [
        'no_show' => 'boolean',
        'responded_at' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services')->withPivot('price_at_booking');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function arrivalMarker()
    {
        return $this->belongsTo(User::class, 'arrived_marked_by');
    }
}
