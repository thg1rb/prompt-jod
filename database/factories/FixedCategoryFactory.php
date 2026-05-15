<?php

namespace Database\Factories;

use App\Models\FixedCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedCategoryFactory extends Factory
{
    protected $model = FixedCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['📌', '🍜', '🛍️', '🚗', '💡', '🎬', '🏥', '🏦']),
            'sort_order' => fake()->numberBetween(1, 99),
            'type' => 'expense',
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }
}
