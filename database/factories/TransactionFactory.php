<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'category_id' => Category::factory(),
            'transaction_ref' => fake()->numerify('##########'),
            'type' => TransactionType::from(fake()->randomElement(['expense', 'income', 'adjustment'])),
            'amount' => fake()->randomFloat(2, 1, 10000),
            'sender' => fake()->name(),
            'recipient' => fake()->name(),
            'note' => fake()->sentence(),
            'transacted_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Expense,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Income,
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Adjustment,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'transacted_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'transacted_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }

    public function forWallet(Wallet $wallet): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'created_by' => $wallet->user_id,
        ]);
    }
}
