<?php

namespace App\Observers;

use App\Models\Budget;
use App\Models\Transaction;

class TransactionBudgetObserver
{
    /**
     * Handle the Transaction "created" event for budget updates.
     */
    public function created(Transaction $transaction): void
    {
        // Only check for expense transactions
        if (!$transaction->isExpense()) {
            return;
        }

        // Find relevant budgets for this transaction
        $budgets = Budget::active()
            ->where('user_id', $transaction->user_id)
            ->where(function ($query) use ($transaction) {
                // Check if category matches or if budget has no specific category
                $query->where('category_id', $transaction->category_id)
                      ->orWhereNull('category_id');
            })
            ->where('year', $transaction->transacted_at->year)
            ->where(function ($query) use ($transaction) {
                // Match monthly budgets by month or yearly budgets
                $query->where(function ($q) use ($transaction) {
                    $q->where('period', 'monthly')
                      ->where('month', $transaction->transacted_at->month);
                })->orWhere('period', 'yearly');
            })
            ->get();

        foreach ($budgets as $budget) {
            // Update budget status
            $budget->updateStatus();

            // Check if budget observer is enabled and check thresholds
            if ($budget->alert_enabled) {
                // Use the BudgetObserver's logic via a static method or call directly
                app(\App\Observers\BudgetObserver::class)->checkBudgetThresholds($budget);
            }
        }
    }
}
