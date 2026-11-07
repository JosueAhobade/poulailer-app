<?php

namespace Database\Factories;

use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReport>
 */
class DailyReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_date' => fake()->unique()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'birds_count' => 455,
            'deaths_count' => 0,
            'sick_count' => 0,
            'feed_quantity' => fake()->randomFloat(2, 30, 60),
            'water_quantity' => fake()->randomFloat(2, 60, 120),
        ];
    }
}
