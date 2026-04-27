<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        $defaultCategories = [
            [
                'name' => 'Food',
                'description' => 'Restaurants, cafes, groceries, and food delivery',
                'color' => '#ef4444', // red-500
                'icon' => 'food',
                'sort_order' => 1,
            ],
            [
                'name' => 'Shopping',
                'description' => 'Clothing, electronics, and retail purchases',
                'color' => '#f97316', // orange-500
                'icon' => 'shopping-bag',
                'sort_order' => 2,
            ],
            [
                'name' => 'Transport',
                'description' => 'Fuel, public transport, ride-sharing, and parking',
                'color' => '#eab308', // yellow-500
                'icon' => 'car',
                'sort_order' => 3,
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet, and phone bills',
                'color' => '#22c55e', // green-500
                'icon' => 'bolt',
                'sort_order' => 4,
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Movies, games, streaming, and hobbies',
                'color' => '#14b8a6', // teal-500
                'icon' => 'film',
                'sort_order' => 5,
            ],
            [
                'name' => 'Health',
                'description' => 'Medical, pharmacy, fitness, and wellness',
                'color' => '#3b82f6', // blue-500
                'icon' => 'heart',
                'sort_order' => 6,
            ],
            [
                'name' => 'Finance',
                'description' => 'Insurance, investments, and banking fees',
                'color' => '#8b5cf6', // violet-500
                'icon' => 'bank',
                'sort_order' => 7,
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous expenses',
                'color' => '#6b7280', // gray-500
                'icon' => 'dots-horizontal',
                'sort_order' => 99,
            ],
        ];

        // Create system categories for all existing users
        foreach (Category::withTrashed()->get()->pluck('user_id')->unique() as $userId) {
            foreach ($defaultCategories as $category) {
                // Check if category already exists for this user
                $existing = Category::withTrashed()
                    ->where('user_id', $userId)
                    ->where('name', $category['name'])
                    ->first();

                if (!$existing) {
                    Category::create([
                        'user_id' => $userId,
                        'name' => $category['name'],
                        'description' => $category['description'],
                        'color' => $category['color'],
                        'icon' => $category['icon'],
                        'is_active' => true,
                        'is_system' => true,
                        'sort_order' => $category['sort_order'],
                    ]);
                }
            }
        }

        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
    }
}
