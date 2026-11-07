<?php

namespace App\Models;

use App\Models\Concerns\SyncsInputConsumption;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    use SyncsInputConsumption;

    protected $fillable = [
        'daily_report_id',
        'input_id',
        'name',
        'dosage',
        'quantity_used',
        'administration_method',
        'reason',
        'birds_count',
        'notes',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }

    protected function consumedInputId(): ?int
    {
        return $this->input_id;
    }

    protected function consumedQuantity(): ?float
    {
        return $this->quantity_used !== null ? (float) $this->quantity_used : null;
    }

    protected function consumptionDate(): CarbonInterface
    {
        return $this->dailyReport->report_date;
    }

    protected function consumptionLabel(): string
    {
        return 'Traitement « '.$this->name.' » — rapport du '.$this->consumptionDate()->format('d/m/Y');
    }
}
