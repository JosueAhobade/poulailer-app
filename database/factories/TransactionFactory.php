<?php

namespace Database\Factories;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TransactionType::Expense,
            'category' => TransactionCategory::Labor,
            'transaction_date' => now()->toDateString(),
            'label' => fake()->sentence(3),
            'amount' => fake()->numberBetween(5, 100) * 1000,
        ];
    }

    public function income(TransactionCategory $category = TransactionCategory::BirdSale): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Income,
            'category' => $category,
        ]);
    }

    public function expense(TransactionCategory $category = TransactionCategory::Labor): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Expense,
            'category' => $category,
        ]);
    }
}
