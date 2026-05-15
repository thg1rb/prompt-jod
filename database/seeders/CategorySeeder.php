<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $fixedCategories = FixedCategory::orderBy('sort_order')->get();

        $users = User::all();

        foreach ($users as $user) {
            foreach ($fixedCategories as $fixedCategory) {
                $exists = Category::where('user_id', $user->id)->where('fixed_category_id', $fixedCategory->id)->exists();

                if (! $exists) {
                    Category::create([
                        'fixed_category_id' => $fixedCategory->id,
                        'user_id' => $user->id,
                        'name' => $fixedCategory->name,
                        'icon' => $fixedCategory->icon,
                    ]);
                }
            }
        }
    }
}
