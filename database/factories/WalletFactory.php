<?php

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'type' => WalletType::from(fake()->randomElement(['bank', 'ewallet', 'cash'])),
            'bank_name' => fake()->randomElement(['KBANK', 'SCB', 'KTB', 'BBL']),
            'account_number' => fake()->numerify('###-#-######-#'),
            'balance' => fake()->randomFloat(2, 0, 1000000),
            'is_active' => true,
            'is_default' => false,
            'notes' => fake()->sentence(),
        ];
    }

    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletType::Bank,
            'bank_name' => fake()->randomElement(['KBANK', 'SCB', 'KTB', 'BBL']),
            'account_number' => fake()->numerify('###-#-######-#'),
        ]);
    }

    public function ewallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletType::EWallet,
            'bank_name' => null,
            'account_number' => fake()->numerify('########'),
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletType::Cash,
            'bank_name' => null,
            'account_number' => null,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
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
