<?php

namespace App\Models;

use App\Models\Concerns\SyncsInputConsumption;
use Carbon\CarbonInterface;
use Database\Factories\DailyReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    /** @use HasFactory<DailyReportFactory> */
    use HasFactory, SyncsInputConsumption;

    protected $fillable = [
        'report_date',
        'birds_count',
        'deaths_count',
        'sick_count',
        'feed_quantity',
        'feed_input_id',
        'water_quantity',
        'birds_weight',
        'temperature',
        'observations',
        'feed_consumed',
        'water_consumed',
    ];

    protected $casts = [
        'report_date' => 'date',
        'feed_quantity' => 'decimal:2',
        'water_quantity' => 'decimal:2',
        'temperature' => 'decimal:2',
        'birds_weight' => 'decimal:2',
        'feed_consumed' => 'decimal:2',
        'water_consumed' => 'decimal:2',
    ];

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function feedInput(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'feed_input_id');
    }

    protected function consumedInputId(): ?int
    {
        return $this->feed_input_id;
    }

    /**
     * La quantité servie est celle qui sort du magasin.
     */
    protected function consumedQuantity(): ?float
    {
        return $this->feed_quantity !== null ? (float) $this->feed_quantity : null;
    }

    protected function consumptionDate(): CarbonInterface
    {
        return $this->report_date;
    }

    protected function consumptionLabel(): string
    {
        return 'Aliment servi — rapport du '.$this->report_date->format('d/m/Y');
    }
}
