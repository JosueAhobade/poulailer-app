<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $fillable = [
        'daily_report_id',
        'name',
        'dosage',
        'administration_method',
        'reason',
        'birds_count',
        'notes',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}
