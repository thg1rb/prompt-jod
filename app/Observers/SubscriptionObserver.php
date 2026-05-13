<?php

namespace App\Observers;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        if ($subscription->isDirty('status')) {
            $this->handleStatusChange($subscription);
        }
    }

    /**
     * Handle subscription status changes.
     */
    protected function handleStatusChange(Subscription $subscription): void
    {
        match ($subscription->status) {
            SubscriptionStatus::Active, SubscriptionStatus::Trialing => $this->handleActivated($subscription),
            SubscriptionStatus::PastDue => $this->handlePastDue($subscription),
            SubscriptionStatus::Canceled => $this->handleCanceled($subscription),
            SubscriptionStatus::Expired => $this->handleExpired($subscription),
            SubscriptionStatus::Paused => $this->handlePaused($subscription),
        };
    }

    /**
     * Handle subscription activation.
     */
    protected function handleActivated(Subscription $subscription): void
    {
        // Send notification, update user features, etc.
        logger()->info("Subscription activated for user {$subscription->user_id}");
    }

    /**
     * Handle subscription past due.
     */
    protected function handlePastDue(Subscription $subscription): void
    {
        // Send notification to user to update payment method
        logger()->warning("Subscription past due for user {$subscription->user_id}");
    }

    /**
     * Handle subscription cancellation.
     */
    protected function handleCanceled(Subscription $subscription): void
    {
        // Revoke user features, send notification
        logger()->info("Subscription canceled for user {$subscription->user_id}");
    }

    /**
     * Handle subscription expiration.
     */
    protected function handleExpired(Subscription $subscription): void
    {
        // Revoke user features, send notification
        logger()->info("Subscription expired for user {$subscription->user_id}");
    }

    /**
     * Handle subscription pause.
     */
    protected function handlePaused(Subscription $subscription): void
    {
        // Revoke user features temporarily, send notification
        logger()->info("Subscription paused for user {$subscription->user_id}");
    }
}
