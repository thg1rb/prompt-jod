<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\BalanceAdjustment;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        DB::transaction(fn () => $this->updateWalletBalance($transaction));
    }

    public function updated(Transaction $transaction): void
    {
        DB::transaction(fn () => $this->updateWalletBalance($transaction));
    }

    public function deleted(Transaction $transaction): void
    {
        DB::transaction(fn () => $this->revertWalletBalance($transaction));
    }

    public function restored(Transaction $transaction): void
    {
        DB::transaction(fn () => $this->updateWalletBalance($transaction));
    }

    protected function updateWalletBalance(Transaction $transaction): void
    {
        $wallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
        if (! $wallet) {
            return;
        }

        $previousBalance = $wallet->balance;

        $amount = match ($transaction->type) {
            TransactionType::Expense => -$transaction->amount,
            TransactionType::Income, TransactionType::Adjustment => $transaction->amount,
        };

        $newBalance = $previousBalance + $amount;
        $adjustmentAmount = $newBalance - $previousBalance;

        if (abs($adjustmentAmount) > 0.01) {
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $transaction->user_id,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => 'transaction_'.$transaction->type->value,
                'notes' => 'ปรับยอดจากธุรกรรม #'.$transaction->id,
                'adjusted_at' => now(),
            ]);

            $wallet->balance = $newBalance;
            $wallet->saveQuietly();
        }
    }

    protected function revertWalletBalance(Transaction $transaction): void
    {
        $wallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
        if (! $wallet) {
            return;
        }

        $previousBalance = $wallet->balance;

        $amount = match ($transaction->type) {
            TransactionType::Expense => $transaction->amount,
            TransactionType::Income, TransactionType::Adjustment => -$transaction->amount,
        };

        $newBalance = $previousBalance + $amount;
        $adjustmentAmount = $newBalance - $previousBalance;

        if (abs($adjustmentAmount) > 0.01) {
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $transaction->user_id,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => 'revert_transaction_'.$transaction->type->value,
                'notes' => 'ยกเลิกจากธุรกรรมที่ถูกลบ #'.$transaction->id,
                'adjusted_at' => now(),
            ]);

            $wallet->balance = $newBalance;
            $wallet->saveQuietly();
        }
    }
}
