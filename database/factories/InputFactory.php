<?php

namespace Database\Factories;

use App\Enums\InputCategory;
use App\Models\Input;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Input>
 */
class InputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Provende '.fake()->unique()->word(),
            'category' => InputCategory::Feed,
            'unit' => 'kg',
            'alert_threshold' => null,
        ];
    }

    public function feed(): static
    {
        return $this->state(fn () => [
            'name' => 'Provende '.fake()->unique()->word(),
            'category' => InputCategory::Feed,
            'unit' => 'kg',
        ]);
    }

    public function vaccine(): static
    {
        return $this->state(fn () => [
            'name' => 'Vaccin '.fake()->unique()->word(),
            'category' => InputCategory::Vaccine,
            'unit' => 'doses',
        ]);
    }

    public function medicine(): static
    {
        return $this->state(fn () => [
            'name' => 'Médicament '.fake()->unique()->word(),
            'category' => InputCategory::Medicine,
            'unit' => 'g',
        ]);
    }

    public function withAlertThreshold(float $threshold): static
    {
        return $this->state(fn () => ['alert_threshold' => $threshold]);
    }
}
