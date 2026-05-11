<?php

namespace Database\Factories;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetAlertFactory extends Factory
{
    protected $model = BudgetAlert::class;

    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'user_id' => User::factory(),
            'alert_type' => AlertType::from(fake()->randomElement(['warning_80', 'warning_100', 'exceeded'])),
            'status' => AlertStatus::from(fake()->randomElement(['sent', 'read', 'dismissed'])),
            'threshold_percent' => fake()->randomFloat(2, 70, 100),
            'amount_spent' => fake()->randomFloat(2, 0, 50000),
            'amount_remaining' => fake()->randomFloat(2, -10000, 50000),
            'message' => fake()->sentence(),
            'read_at' => fake()->optional()->dateTime(),
            'dismissed_at' => fake()->optional()->dateTime(),
        ];
    }

    public function warning80(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => AlertType::Warning80,
            'threshold_percent' => 80,
        ]);
    }

    public function warning100(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => AlertType::Warning100,
            'threshold_percent' => 100,
        ]);
    }

    public function exceeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => AlertType::Exceeded,
            'threshold_percent' => 100,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertStatus::Sent,
            'read_at' => null,
            'dismissed_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertStatus::Read,
            'read_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertStatus::Dismissed,
            'dismissed_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AlertStatus::Sent,
        ]);
    }

    public function undismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'dismissed_at' => null,
        ]);
    }
}
