<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function delete(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function restore(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function forceDelete(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function adjustBalance(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function setDefault(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function reorder(User $user): bool
    {
        return true;
    }
}
