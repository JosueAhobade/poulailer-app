<?php

namespace App\Models;

use App\Models\Concerns\SyncsInputConsumption;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use SyncsInputConsumption;

    protected $fillable = [
        'daily_report_id',
        'input_id',
        'vaccine_name',
        'disease',
        'administration_method',
        'dosage',
        'quantity_used',
        'rappel',
        'birds_count',
        'batch_number',
        'notes',
    ];

    protected $casts = [
        'rappel' => 'boolean',
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
        return 'Vaccination « '.$this->vaccine_name.' » — rapport du '.$this->consumptionDate()->format('d/m/Y');
    }
}
