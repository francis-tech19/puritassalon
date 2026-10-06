<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'full_name',
        'phone',
        'email',
        'address',
        'notes',
        'status',
        'visit_count',
        'total_spent',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyRewards()
    {
        return $this->hasMany(LoyaltyReward::class);
    }
}
