<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        FixedCategory::orderBy('sort_order')->each(function (FixedCategory $fixedCategory) use ($user) {
            Category::create([
                'fixed_category_id' => $fixedCategory->id,
                'user_id' => $user->id,
                'name' => $fixedCategory->name,
                'icon' => $fixedCategory->icon,
            ]);
        });
    }
}
