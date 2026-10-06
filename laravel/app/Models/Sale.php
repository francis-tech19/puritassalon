<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_code',
        'customer_id',
        'employee_id',
        'payment_method',
        'total_amount',
        'discount_percentage',
        'discount_amount',
        'final_amount',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
