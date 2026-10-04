<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'report_date',
        'birds_count',
        'deaths_count',
        'sick_count',
        'feed_quantity',
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

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }
}
