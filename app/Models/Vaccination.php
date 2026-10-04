<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
     protected $fillable = [
        'daily_report_id',
        'vaccine_name',
        'disease',
        'administration_method',
        'dosage',
        'rappel',
        'birds_count',
        'batch_number',
        'notes',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }
}
