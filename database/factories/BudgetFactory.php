<?php

namespace Database\Factories;

use App\Enums\BudgetPeriod;
use App\Enums\BudgetStatus;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'period' => BudgetPeriod::from(fake()->randomElement(['monthly', 'yearly'])),
            'status' => BudgetStatus::from(fake()->randomElement(['active', 'warning', 'exceeded'])),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'year' => fake()->numberBetween(2023, 2026),
            'month' => fake()->numberBetween(1, 12),
            'is_active' => true,
            'alert_enabled' => true,
            'alert_threshold' => fake()->randomFloat(2, 70, 90),
            'notes' => fake()->sentence(),
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => BudgetPeriod::Monthly,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => BudgetPeriod::Yearly,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BudgetStatus::Active,
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BudgetStatus::Warning,
        ]);
    }

    public function exceeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BudgetStatus::Exceeded,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'category_id' => Category::factory()->forUser($user),
        ]);
    }
}
