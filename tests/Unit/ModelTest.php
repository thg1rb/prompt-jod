<?php

namespace Tests\Unit;

use App\Enums\SubscriptionStatus;
use App\Enums\WalletAccess;
use App\Enums\WalletType;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function transaction_is_adjustment(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->adjustment()->create();

        expect($transaction->isAdjustment())->toBeTrue();
        expect($transaction->isExpense())->toBeFalse();
        expect($transaction->isIncome())->toBeFalse();
    }

    #[Test]
    public function transaction_signed_amount_for_adjustment(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->adjustment()->create(['amount' => 100]);

        expect($transaction->signed_amount)->toBe(100.0);
    }

    #[Test]
    public function transaction_scope_expense_filters_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->count(3)->for($user)->for($wallet)->expense()->create();
        Transaction::factory()->count(2)->for($user)->for($wallet)->income()->create();

        expect(Transaction::expense()->count())->toBe(3);
    }

    #[Test]
    public function transaction_scope_income_filters_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->count(3)->for($user)->for($wallet)->expense()->create();
        Transaction::factory()->count(2)->for($user)->for($wallet)->income()->create();

        expect(Transaction::income()->count())->toBe(2);
    }

    #[Test]
    public function transaction_scope_adjustment_filters_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->count(2)->for($user)->for($wallet)->adjustment()->create();
        Transaction::factory()->count(3)->for($user)->for($wallet)->expense()->create();

        expect(Transaction::adjustment()->count())->toBe(2);
    }

    #[Test]
    public function transaction_scope_by_date_range(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->for($user)->for($wallet)->create(['transacted_at' => '2025-01-15 10:00:00']);
        Transaction::factory()->for($user)->for($wallet)->create(['transacted_at' => '2025-02-15 10:00:00']);
        Transaction::factory()->for($user)->for($wallet)->create(['transacted_at' => '2025-03-15 10:00:00']);

        $results = Transaction::byDateRange('2025-01-01', '2025-02-28')->count();

        expect($results)->toBe(2);
    }

    #[Test]
    public function transaction_scope_latest_orders_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $t1 = Transaction::factory()->for($user)->for($wallet)->create();
        $t2 = Transaction::factory()->for($user)->for($wallet)->create();
        $t3 = Transaction::factory()->for($user)->for($wallet)->create();

        $latest = Transaction::orderBy('id', 'desc')->first();

        expect($latest->id)->toBe($t3->id);
    }

    #[Test]
    public function transaction_scope_oldest_orders_correctly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $t1 = Transaction::factory()->for($user)->for($wallet)->create();
        $t2 = Transaction::factory()->for($user)->for($wallet)->create();
        $t3 = Transaction::factory()->for($user)->for($wallet)->create();

        $oldest = Transaction::orderBy('id', 'asc')->first();

        expect($oldest->id)->toBe($t1->id);
    }

    #[Test]
    public function transaction_creator_relation(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        expect($transaction->creator->id)->toBe($user->id);
    }

    #[Test]
    public function wallet_is_owner_with_null_user(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        expect($wallet->isOwner(null))->toBeFalse();
    }

    #[Test]
    public function wallet_has_member_with_null_user(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        expect($wallet->hasMember(null))->toBeFalse();
    }

    #[Test]
    public function wallet_has_access_with_null_user(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        expect($wallet->hasAccess(null))->toBeFalse();
    }

    #[Test]
    public function wallet_get_member_count_attribute(): void
    {
        $owner = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member1)->accepted()->create();
        WalletMember::factory()->for($wallet)->for($member2)->accepted()->create();

        expect($wallet->member_count)->toBe(2);
    }

    #[Test]
    public function wallet_scope_active(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['is_active' => true]);
        Wallet::factory()->for($user)->create(['is_active' => false]);
        Wallet::factory()->for($user)->create(['is_active' => true]);

        expect(Wallet::active()->count())->toBe(2);
    }

    #[Test]
    public function wallet_scope_default(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['is_default' => true]);
        Wallet::factory()->for($user)->create(['is_default' => false]);

        expect(Wallet::default()->count())->toBe(1);
    }

    #[Test]
    public function wallet_scope_of_type(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['type' => WalletType::Bank]);
        Wallet::factory()->for($user)->create(['type' => WalletType::EWallet]);
        Wallet::factory()->for($user)->create(['type' => WalletType::Bank]);

        expect(Wallet::ofType(WalletType::Bank)->count())->toBe(2);
    }

    #[Test]
    public function wallet_scope_shared(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['access_type' => WalletAccess::Shared]);
        Wallet::factory()->for($user)->create(['access_type' => WalletAccess::Personal]);
        Wallet::factory()->for($user)->create(['access_type' => WalletAccess::Shared]);

        expect(Wallet::shared()->count())->toBe(2);
    }

    #[Test]
    public function wallet_scope_personal(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['access_type' => WalletAccess::Personal]);
        Wallet::factory()->for($user)->create(['access_type' => WalletAccess::Shared]);

        expect(Wallet::personal()->count())->toBe(1);
    }

    #[Test]
    public function wallet_member_is_accepted(): void
    {
        $member = WalletMember::factory()->accepted()->create();

        expect($member->isAccepted())->toBeTrue();
        expect($member->isPending())->toBeFalse();
    }

    #[Test]
    public function wallet_member_is_pending(): void
    {
        $member = WalletMember::factory()->pending()->create();

        expect($member->isPending())->toBeTrue();
        expect($member->isAccepted())->toBeFalse();
    }

    #[Test]
    public function wallet_member_is_token_valid_with_future_token(): void
    {
        $member = WalletMember::factory()->create([
            'token_expires_at' => now()->addHours(24),
        ]);

        expect($member->isTokenValid())->toBeTrue();
    }

    #[Test]
    public function wallet_member_is_token_valid_with_expired_token(): void
    {
        $member = WalletMember::factory()->create([
            'token_expires_at' => now()->subHours(1),
        ]);

        expect($member->isTokenValid())->toBeFalse();
    }

    #[Test]
    public function wallet_member_accept_sets_accepted_at(): void
    {
        $member = WalletMember::factory()->pending()->create();

        expect($member->accepted_at)->toBeNull();
        $member->accept();
        expect($member->accepted_at)->not->toBeNull();
    }

    #[Test]
    public function wallet_member_invited_by_relation(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $memberRecord = WalletMember::factory()->for(
            Wallet::factory()->for($owner)->create()
        )->for($member)->create(['invited_by' => $owner->id]);

        expect($memberRecord->invitedBy->id)->toBe($owner->id);
    }

    #[Test]
    public function subscription_mark_as_canceled(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Active]);

        $subscription->markAsCanceled();

        expect($subscription->status)->toBe(SubscriptionStatus::Canceled);
        expect($subscription->canceled_at)->not->toBeNull();
    }

    #[Test]
    public function subscription_mark_as_past_due(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => 'active']);

        $subscription->markAsPastDue();

        expect($subscription->status->value)->toBe('past_due');
    }

    #[Test]
    public function subscription_mark_as_active(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->pastDue()->create();

        $subscription->markAsActive();

        expect($subscription->status)->toBe(SubscriptionStatus::Active);
    }

    #[Test]
    public function subscription_get_status_label_attribute(): void
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Active]);

        expect($subscription->status_label)->toBe('ใช้งานอยู่');
    }

    #[Test]
    public function subscription_scope_active(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Subscription::factory()->for($user1)->create(['status' => SubscriptionStatus::Active]);
        Subscription::factory()->for($user2)->create(['status' => SubscriptionStatus::Canceled]);

        expect(Subscription::active()->count())->toBe(1);
    }

    #[Test]
    public function subscription_scope_past_due(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Subscription::factory()->for($user1)->create(['status' => SubscriptionStatus::PastDue]);
        Subscription::factory()->for($user2)->create(['status' => SubscriptionStatus::Active]);

        expect(Subscription::pastDue()->count())->toBe(1);
    }
}
