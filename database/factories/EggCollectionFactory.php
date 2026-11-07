<?php

namespace Database\Factories;

use App\Models\EggCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EggCollection>
 */
class EggCollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_date' => fake()->unique()->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
            'collected_count' => fake()->numberBetween(300, 420),
            'broken_count' => 0,
        ];
    }
}
