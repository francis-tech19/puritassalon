<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'item_code',
        'item_name',
        'category',
        'quantity',
        'unit',
        'min_stock_level',
        'supplier',
        'status',
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public static function statusForQuantity(int $quantity): string
    {
        return $quantity === 0 || $quantity <= 8 ? ($quantity === 0 ? 'OUT_OF_STOCK' : 'LOW_STOCK') : 'IN_STOCK';
    }

    public static function alertLevelForQuantity(int $quantity): string
    {
        return match (true) {
            $quantity <= 0 => 'OUT_OF_STOCK',
            $quantity <= 3 => 'CRITICAL_STOCK',
            $quantity <= 8 => 'LOW_STOCK',
            default => 'GOOD_STOCK',
        };
    }
}
