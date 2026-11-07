<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'input_id',
        'type',
        'quantity',
        'movement_date',
        'unit_price',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'movement_date' => 'date',
    ];

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSynced(): bool
    {
        return $this->source_type !== null;
    }

    /**
     * Rapport journalier lié à ce mouvement, quelle que soit la source.
     */
    public function relatedReport(): ?DailyReport
    {
        return match (true) {
            $this->source instanceof DailyReport => $this->source,
            $this->source instanceof Treatment,
            $this->source instanceof Vaccination => $this->source->dailyReport,
            default => null,
        };
    }
}
