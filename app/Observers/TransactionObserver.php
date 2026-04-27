<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\BalanceAdjustment;
use App\Models\Transaction;
use App\Models\Wallet;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        $this->updateWalletBalance($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $this->updateWalletBalance($transaction);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        $this->revertWalletBalance($transaction);
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        $this->updateWalletBalance($transaction);
    }

    /**
     * Update wallet balance based on transaction type and amount.
     */
    protected function updateWalletBalance(Transaction $transaction): void
    {
        $wallet = $transaction->wallet;
        if (!$wallet) {
            return;
        }

        $previousBalance = $wallet->balance;
        $amount = 0;

        match ($transaction->type) {
            TransactionType::Expense => $amount = -$transaction->amount,
            TransactionType::Income, TransactionType::Adjustment => $amount = $transaction->amount,
        };

        $newBalance = $previousBalance + $amount;
        $adjustmentAmount = $newBalance - $previousBalance;

        // Only create adjustment record if there's a significant change
        if (abs($adjustmentAmount) > 0.01) {
            // Create a balance adjustment record for audit trail
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $transaction->user_id,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => 'transaction_' . $transaction->type->value,
                'notes' => 'Auto-adjusted from transaction #' . $transaction->id,
                'adjusted_at' => now(),
            ]);

            // Update wallet balance
            $wallet->balance = $newBalance;
            $wallet->saveQuietly(); // Save without triggering observer
        }
    }

    /**
     * Revert wallet balance when transaction is deleted.
     */
    protected function revertWalletBalance(Transaction $transaction): void
    {
        $wallet = $transaction->wallet;
        if (!$wallet) {
            return;
        }

        $previousBalance = $wallet->balance;
        $amount = 0;

        match ($transaction->type) {
            TransactionType::Expense => $amount = $transaction->amount, // Reverse expense
            TransactionType::Income, TransactionType::Adjustment => $amount = -$transaction->amount, // Reverse income/adjustment
        };

        $newBalance = $previousBalance + $amount;
        $adjustmentAmount = $newBalance - $previousBalance;

        // Only create adjustment record if there's a significant change
        if (abs($adjustmentAmount) > 0.01) {
            // Create a balance adjustment record for audit trail
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $transaction->user_id,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => 'revert_transaction_' . $transaction->type->value,
                'notes' => 'Reverted from deleted transaction #' . $transaction->id,
                'adjusted_at' => now(),
            ]);

            // Update wallet balance
            $wallet->balance = $newBalance;
            $wallet->saveQuietly(); // Save without triggering observer
        }
    }
}
