<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_code',
        'full_name',
        'phone',
        'email',
        'position',
        'status',
        'schedule_notes',
        'working_days',
        'shift_start_time',
        'shift_end_time',
    ];

    protected $casts = [
        'working_days' => 'array',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
