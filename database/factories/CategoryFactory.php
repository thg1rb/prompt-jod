<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private static int $counter = 0;

    public function definition(): array
    {
        return [
            'fixed_category_id' => FixedCategory::factory(),
            'user_id' => User::factory(),
            'name' => 'category_'.(self::$counter++).'_'.fake()->unique()->word(),
            'icon' => fake()->randomElement(['📌', '🍜', '🛍️', '🚗', '💡', '🎬', '🏥', '🏦']),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forWallet(Wallet $wallet): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
            'user_id' => null,
        ]);
    }

    public function defaultForUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'name' => fn () => FixedCategory::find($attributes['fixed_category_id'])?->name ?? 'category_'.(self::$counter++).'_'.fake()->unique()->word(),
            'icon' => fn () => FixedCategory::find($attributes['fixed_category_id'])?->icon ?? '📌',
        ]);
    }
}
