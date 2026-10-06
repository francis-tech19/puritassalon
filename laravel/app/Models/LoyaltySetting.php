<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    protected $fillable = [
        'visits_required_for_reward',
        'reward_description',
        'discount_percentage',
        'is_active',
    ];
}
