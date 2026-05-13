<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'omise_charge_id' => fake()->uuid(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'THB',
            'status' => 'pending',
            'omise_response' => null,
            'paid_at' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'successful',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forSubscription(Subscription $subscription): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
        ]);
    }
}
