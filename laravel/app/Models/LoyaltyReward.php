<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    protected $fillable = [
        'customer_id',
        'reward_title',
        'discount_percentage',
        'status',
        'issued_date',
        'redeemed_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
