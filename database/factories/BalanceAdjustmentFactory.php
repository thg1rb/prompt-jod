<?php

namespace Database\Factories;

use App\Models\BalanceAdjustment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class BalanceAdjustmentFactory extends Factory
{
    protected $model = BalanceAdjustment::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'user_id' => User::factory(),
            'previous_balance' => fake()->randomFloat(2, 0, 1000000),
            'new_balance' => fake()->randomFloat(2, 0, 1000000),
            'adjustment_amount' => fake()->randomFloat(2, -100000, 100000),
            'reason' => fake()->randomElement(['opening_balance', 'manual_adjustment', 'transaction_expense', 'transaction_income', 'transaction_adjustment', 'revert_transaction_expense', 'revert_transaction_income', 'revert_transaction_adjustment']),
            'notes' => fake()->sentence(),
            'adjusted_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function openingBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'opening_balance',
            'previous_balance' => 0,
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'manual_adjustment',
        ]);
    }

    public function increase(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_amount' => fake()->randomFloat(2, 1, 100000),
        ]);
    }

    public function decrease(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_amount' => fake()->randomFloat(2, -100000, -1),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjusted_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjusted_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'wallet_id' => Wallet::factory()->forUser($user),
        ]);
    }

    public function forWallet(Wallet $wallet): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
        ]);
    }
}
