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
    ];

    protected $casts = [
        'report_date' => 'date',
        'feed_quantity' => 'decimal:2',
        'water_quantity' => 'decimal:2',
        'temperature' => 'decimal:2',
        'birds_weight' => 'decimal:2',
    ];
}
