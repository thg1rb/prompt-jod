<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryRuleFactory extends Factory
{
    protected $model = CategoryRule::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'keyword' => fake()->word(),
            'priority' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'case_sensitive' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function caseSensitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_sensitive' => true,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->numberBetween(8, 10),
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->numberBetween(1, 3),
        ]);
    }
}
