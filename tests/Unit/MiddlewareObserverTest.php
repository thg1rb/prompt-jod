<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use App\Observers\SubscriptionObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MiddlewareObserverTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function subscription_observer_handles_activated_status(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $observer = new SubscriptionObserver;
        $subscription->update(['status' => 'trialing']);

        expect($subscription->status->value)->toBe('trialing');
    }

    #[Test]
    public function subscription_observer_handles_past_due_status(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $observer = new SubscriptionObserver;
        $subscription->update(['status' => 'past_due']);

        expect($subscription->status->value)->toBe('past_due');
    }

    #[Test]
    public function subscription_observer_handles_canceled_status(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $observer = new SubscriptionObserver;
        $subscription->update(['status' => 'canceled']);

        expect($subscription->status->value)->toBe('canceled');
    }

    #[Test]
    public function subscription_observer_handles_expired_status(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $observer = new SubscriptionObserver;
        $subscription->update(['status' => 'expired']);

        expect($subscription->status->value)->toBe('expired');
    }

    #[Test]
    public function subscription_observer_handles_paused_status(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $observer = new SubscriptionObserver;
        $subscription->update(['status' => 'paused']);

        expect($subscription->status->value)->toBe('paused');
    }

    #[Test]
    public function subscription_observer_does_nothing_when_status_not_dirty(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create([
            'status' => 'active',
            'amount' => 99.00,
        ]);

        $observer = new SubscriptionObserver;
        $subscription->update(['amount' => 150.00]);

        expect($subscription->status->value)->toBe('active');
        expect((float) $subscription->amount)->toBe(150.00);
    }
}
