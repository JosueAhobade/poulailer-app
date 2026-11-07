<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EggSaleItem extends Model
{
    protected $fillable = [
        'egg_sale_id',
        'egg_collection_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(EggSale::class, 'egg_sale_id');
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(EggCollection::class, 'egg_collection_id');
    }
}
