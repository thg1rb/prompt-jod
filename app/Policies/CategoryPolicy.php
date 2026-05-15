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
        if ($category->user_id && $category->user_id === $user->id) {
            return true;
        }

        if ($category->wallet_id && $category->wallet->hasAccess($user)) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isPremium();
    }

    public function update(User $user, Category $category): bool
    {
        if (! $user->isPremium()) {
            return false;
        }

        if ($category->user_id && $category->user_id === $user->id) {
            return true;
        }

        if ($category->wallet_id && $category->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Category $category): bool
    {
        if (! $user->isPremium()) {
            return false;
        }

        if ($category->user_id && $category->user_id === $user->id) {
            return true;
        }

        if ($category->wallet_id && $category->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $this->delete($user, $category);
    }
}
