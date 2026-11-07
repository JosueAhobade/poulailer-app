<?php

namespace App\Models;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'transaction_date',
        'label',
        'amount',
        'notes',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'category' => TransactionCategory::class,
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
