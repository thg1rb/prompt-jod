<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Wallet $wallet): bool
    {
        return $wallet->hasAccess($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function delete(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function restore(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function forceDelete(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function adjustBalance(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function setDefault(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function reorder(User $user): bool
    {
        return true;
    }

    public function manageMembers(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function createInvitation(User $user, Wallet $wallet): bool
    {
        return $wallet->isOwner($user);
    }

    public function addTransaction(User $user, Wallet $wallet): bool
    {
        return $wallet->hasAccess($user);
    }
}
