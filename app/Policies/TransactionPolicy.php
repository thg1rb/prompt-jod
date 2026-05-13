<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($transaction->user_id === $user->id) {
            return true;
        }

        return $transaction->wallet && $transaction->wallet->isOwner($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        if ($transaction->created_by === $user->id) {
            return true;
        }

        if ($transaction->wallet && $transaction->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        if ($transaction->created_by === $user->id) {
            return true;
        }

        if ($transaction->wallet && $transaction->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        if ($transaction->created_by === $user->id) {
            return true;
        }

        if ($transaction->wallet && $transaction->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        if ($transaction->created_by === $user->id) {
            return true;
        }

        if ($transaction->wallet && $transaction->wallet->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function verifySlip(User $user): bool
    {
        return true;
    }

    public function export(User $user): bool
    {
        return true;
    }

    public function searchNames(User $user): bool
    {
        return true;
    }
}
