<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['pin', 'cart', 'car', 'food', 'home', 'cash']),
            'is_active' => true,
            'is_system' => false,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
            'name' => fake()->randomElement(['Food', 'Shopping', 'Transport', 'Utilities', 'Entertainment']),
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
