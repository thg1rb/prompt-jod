<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isPremium();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isPremium() && $category->user_id === $user->id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isPremium() && $category->user_id === $user->id;
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->isPremium() && $category->user_id === $user->id;
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->isPremium() && $category->user_id === $user->id;
    }
}
