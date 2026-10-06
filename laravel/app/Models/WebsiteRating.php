<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteRating extends Model
{
    protected $fillable = ['user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}