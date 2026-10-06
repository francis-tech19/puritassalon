<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'inventory_id',
        'transaction_type',
        'quantity_change',
        'action',
        'previous_stock',
        'new_stock',
        'notes',
        'recorded_by',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
