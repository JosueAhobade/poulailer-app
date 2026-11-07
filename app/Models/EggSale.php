<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EggSale extends Model
{
    public const EGGS_PER_TRAY = 30;

    protected $fillable = [
        'sale_date',
        'customer',
        'quantity',
        'unit_price',
        'total_amount',
        'is_paid',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(EggSaleItem::class);
    }
}
