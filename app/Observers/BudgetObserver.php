<?php

namespace App\Observers;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use App\Enums\BudgetStatus;
use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\Transaction;

class BudgetObserver
{
    /**
     * Handle the Budget "created" event.
     */
    public function created(Budget $budget): void
    {
        $this->checkBudgetThresholds($budget);
    }

    /**
     * Handle the Budget "updated" event.
     */
    public function updated(Budget $budget): void
    {
        $this->checkBudgetThresholds($budget);
    }

    /**
     * Handle the Transaction "created" event for budget updates.
     */
    public function created(Transaction $transaction): void
    {
        // Only check for expense transactions
        if ($transaction->type !== \App\Enums\TransactionType::Expense) {
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
            $this->checkBudgetThresholds($budget);
        }
    }

    /**
     * Check budget thresholds and create alerts if necessary.
     */
    protected function checkBudgetThresholds(Budget $budget): void
    {
        if (!$budget->alert_enabled) {
            return;
        }

        $percentageUsed = $budget->percentage_used;
        $amountSpent = $budget->spent_amount;
        $amountRemaining = $budget->remaining_amount;
        $threshold = $budget->alert_threshold;

        // Check for different threshold levels
        $this->createAlertIfNeeded($budget, AlertType::Warning80, 80, $percentageUsed, $amountSpent, $amountRemaining);
        $this->createAlertIfNeeded($budget, AlertType::Warning100, 100, $percentageUsed, $amountSpent, $amountRemaining);
        $this->createAlertIfNeeded($budget, AlertType::Exceeded, 100, $percentageUsed, $amountSpent, $amountRemaining, true);

        // Update budget status
        $budget->updateStatus();
    }

    /**
     * Create a budget alert if the threshold has been met and no alert exists.
     *
     * @param Budget $budget The budget to check
     * @param AlertType $alertType The type of alert to create
     * @param float $thresholdPercent The threshold percentage
     * @param float $currentPercent The current percentage used
     * @param float $amountSpent The amount spent
     * @param float $amountRemaining The amount remaining
     * @param bool $strict Whether to strictly match the threshold or use >=
     */
    protected function createAlertIfNeeded(
        Budget $budget,
        AlertType $alertType,
        float $thresholdPercent,
        float $currentPercent,
        float $amountSpent,
        float $amountRemaining,
        bool $strict = false
    ): void {
        // Check if threshold is met
        $thresholdMet = $strict
            ? $currentPercent >= $thresholdPercent
            : $currentPercent >= $thresholdPercent && $currentPercent < ($thresholdPercent + 10);

        if (!$thresholdMet) {
            return;
        }

        // Check if alert already exists for this threshold
        $existingAlert = BudgetAlert::where('budget_id', $budget->id)
            ->where('alert_type', $alertType)
            ->where('threshold_percent', $thresholdPercent)
            ->exists();

        if ($existingAlert) {
            return;
        }

        // Generate message based on alert type
        $message = match ($alertType) {
            AlertType::Warning80 => "Budget warning: You have used {$currentPercent}% of your {$budget->category->name} budget.",
            AlertType::Warning100 => "Budget alert: You have reached {$currentPercent}% of your {$budget->category->name} budget.",
            AlertType::Exceeded => "Budget exceeded: You have used {$currentPercent}% of your {$budget->category->name} budget! Limit was {$budget->amount}.",
        };

        // Create the alert
        BudgetAlert::create([
            'budget_id' => $budget->id,
            'user_id' => $budget->user_id,
            'alert_type' => $alertType,
            'status' => AlertStatus::Sent,
            'threshold_percent' => $thresholdPercent,
            'amount_spent' => $amountSpent,
            'amount_remaining' => $amountRemaining,
            'message' => $message,
        ]);
    }
}
