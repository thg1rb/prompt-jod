<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Wallet;
use App\Policies\CategoryPolicy;
use App\Policies\WalletPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function wallet_policy_blocks_access_to_other_users_wallet(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $wallet = Wallet::factory()->for($user1)->create();

        $policy = new WalletPolicy;

        expect($policy->view($user2, $wallet))->toBeFalse();
        expect($policy->update($user2, $wallet))->toBeFalse();
        expect($policy->delete($user2, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_allows_access_to_own_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->view($user, $wallet))->toBeTrue();
        expect($policy->update($user, $wallet))->toBeTrue();
        expect($policy->delete($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function category_policy_blocks_update_for_free_user(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $policy = new CategoryPolicy;

        expect($policy->update($user, $category))->toBeFalse();
        expect($policy->delete($user, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_allows_update_for_premium_user_on_own_category(): void
    {
        $user = User::factory()->has(Subscription::factory()->active())->create();
        $category = Category::factory()->for($user)->create();

        $policy = new CategoryPolicy;

        expect($policy->update($user, $category))->toBeTrue();
        expect($policy->delete($user, $category))->toBeTrue();
    }

    #[Test]
    public function category_policy_blocks_access_to_other_users_categories(): void
    {
        $user1 = User::factory()->has(Subscription::factory()->active())->create();
        $user2 = User::factory()->has(Subscription::factory()->active())->create();

        $category = Category::factory()->for($user1)->create();

        $policy = new CategoryPolicy;

        expect($policy->update($user2, $category))->toBeFalse();
        expect($policy->delete($user2, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_allows_viewing_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $policy = new CategoryPolicy;

        expect($policy->view($user, $category))->toBeTrue();
    }
}
