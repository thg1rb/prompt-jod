<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMember;
use App\Policies\BudgetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\WalletMemberPolicy;
use App\Policies\WalletPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function transaction_policy_view_any_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new TransactionPolicy;

        expect($policy->viewAny($user))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_view_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        $policy = new TransactionPolicy;

        expect($policy->view($user, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_view_allows_wallet_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id, 'user_id' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->view($owner, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_view_blocks_non_access(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->view($stranger, $transaction))->toBeFalse();
    }

    #[Test]
    public function transaction_policy_create_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new TransactionPolicy;

        expect($policy->create($user))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_update_allows_creator(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        $policy = new TransactionPolicy;

        expect($policy->update($user, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_update_allows_wallet_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id, 'user_id' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->update($owner, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_update_blocks_non_access(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->update($stranger, $transaction))->toBeFalse();
    }

    #[Test]
    public function transaction_policy_delete_allows_creator(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        $policy = new TransactionPolicy;

        expect($policy->delete($user, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_delete_allows_wallet_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id, 'user_id' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->delete($owner, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_delete_blocks_non_access(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->delete($stranger, $transaction))->toBeFalse();
    }

    #[Test]
    public function transaction_policy_restore_allows_creator(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        $policy = new TransactionPolicy;

        expect($policy->restore($user, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_restore_blocks_non_access(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->restore($stranger, $transaction))->toBeFalse();
    }

    #[Test]
    public function transaction_policy_force_delete_allows_creator(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($wallet)->create(['created_by' => $user->id]);

        $policy = new TransactionPolicy;

        expect($policy->forceDelete($user, $transaction))->toBeTrue();
    }

    #[Test]
    public function transaction_policy_force_delete_blocks_non_access(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        $transaction = Transaction::factory()->for($owner)->for($wallet)->create(['created_by' => $owner->id]);

        $policy = new TransactionPolicy;

        expect($policy->forceDelete($stranger, $transaction))->toBeFalse();
    }

    #[Test]
    public function transaction_policy_always_allows_verify_slip_export_search_names(): void
    {
        $user = User::factory()->create();
        $policy = new TransactionPolicy;

        expect($policy->verifySlip($user))->toBeTrue();
        expect($policy->export($user))->toBeTrue();
        expect($policy->searchNames($user))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_view_any_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new WalletMemberPolicy;

        expect($policy->viewAny($user))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_view_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->view($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_view_allows_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();

        $policy = new WalletMemberPolicy;

        expect($policy->view($member, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_view_blocks_non_member(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->view($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_create_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new WalletMemberPolicy;

        expect($policy->create($user))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_update_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->update($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_update_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->update($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_delete_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->delete($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_delete_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->delete($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_adjust_balance_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->adjustBalance($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_adjust_balance_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->adjustBalance($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_set_default_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->setDefault($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_set_default_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->setDefault($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_reorder_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new WalletMemberPolicy;

        expect($policy->reorder($user))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_manage_members_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->manageMembers($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_manage_members_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->manageMembers($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_create_invitation_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->createInvitation($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_create_invitation_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->createInvitation($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_member_policy_add_transaction_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->addTransaction($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_add_transaction_allows_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();

        $policy = new WalletMemberPolicy;

        expect($policy->addTransaction($member, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_member_policy_add_transaction_blocks_non_member(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletMemberPolicy;

        expect($policy->addTransaction($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function budget_policy_view_any_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new BudgetPolicy;

        expect($policy->viewAny($user))->toBeTrue();
    }

    #[Test]
    public function budget_policy_view_allows_owner(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $policy = new BudgetPolicy;

        expect($policy->view($user, $budget))->toBeTrue();
    }

    #[Test]
    public function budget_policy_view_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $budget = Budget::factory()->for($owner)->create();

        $policy = new BudgetPolicy;

        expect($policy->view($stranger, $budget))->toBeFalse();
    }

    #[Test]
    public function budget_policy_create_always_returns_true(): void
    {
        $user = User::factory()->create();
        $policy = new BudgetPolicy;

        expect($policy->create($user))->toBeTrue();
    }

    #[Test]
    public function budget_policy_update_allows_owner(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $policy = new BudgetPolicy;

        expect($policy->update($user, $budget))->toBeTrue();
    }

    #[Test]
    public function budget_policy_update_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $budget = Budget::factory()->for($owner)->create();

        $policy = new BudgetPolicy;

        expect($policy->update($stranger, $budget))->toBeFalse();
    }

    #[Test]
    public function budget_policy_delete_allows_owner(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $policy = new BudgetPolicy;

        expect($policy->delete($user, $budget))->toBeTrue();
    }

    #[Test]
    public function budget_policy_delete_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $budget = Budget::factory()->for($owner)->create();

        $policy = new BudgetPolicy;

        expect($policy->delete($stranger, $budget))->toBeFalse();
    }

    #[Test]
    public function budget_policy_restore_allows_owner(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $policy = new BudgetPolicy;

        expect($policy->restore($user, $budget))->toBeTrue();
    }

    #[Test]
    public function budget_policy_restore_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $budget = Budget::factory()->for($owner)->create();

        $policy = new BudgetPolicy;

        expect($policy->restore($stranger, $budget))->toBeFalse();
    }

    #[Test]
    public function budget_policy_force_delete_allows_owner(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $policy = new BudgetPolicy;

        expect($policy->forceDelete($user, $budget))->toBeTrue();
    }

    #[Test]
    public function budget_policy_force_delete_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $budget = Budget::factory()->for($owner)->create();

        $policy = new BudgetPolicy;

        expect($policy->forceDelete($stranger, $budget))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_restore_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->restore($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_restore_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->restore($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_force_delete_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->forceDelete($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_force_delete_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->forceDelete($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_adjust_balance_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->adjustBalance($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_adjust_balance_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->adjustBalance($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_set_default_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->setDefault($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_set_default_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->setDefault($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_manage_members_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->manageMembers($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_manage_members_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->manageMembers($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_add_transaction_allows_owner(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $policy = new WalletPolicy;

        expect($policy->addTransaction($user, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_add_transaction_allows_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();

        $policy = new WalletPolicy;

        expect($policy->addTransaction($member, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_add_transaction_blocks_non_member(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->addTransaction($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function wallet_policy_view_allows_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();
        WalletMember::factory()->for($wallet)->for($member)->accepted()->create();

        $policy = new WalletPolicy;

        expect($policy->view($member, $wallet))->toBeTrue();
    }

    #[Test]
    public function wallet_policy_view_blocks_non_member(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $wallet = Wallet::factory()->for($owner)->create();

        $policy = new WalletPolicy;

        expect($policy->view($stranger, $wallet))->toBeFalse();
    }

    #[Test]
    public function category_policy_restore_allows_non_system_owner(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->restore($user, $category))->toBeTrue();
    }

    #[Test]
    public function category_policy_restore_blocks_system(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['is_system' => true]);

        $policy = new CategoryPolicy;

        expect($policy->restore($user, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_restore_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $category = Category::factory()->for($owner)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->restore($stranger, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_force_delete_allows_non_system_owner(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->forceDelete($user, $category))->toBeTrue();
    }

    #[Test]
    public function category_policy_force_delete_blocks_system(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['is_system' => true]);

        $policy = new CategoryPolicy;

        expect($policy->forceDelete($user, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_force_delete_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $category = Category::factory()->for($owner)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->forceDelete($stranger, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_view_blocks_other_users_non_system_category(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $category = Category::factory()->for($owner)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->view($stranger, $category))->toBeFalse();
    }

    #[Test]
    public function category_policy_view_allows_own_non_system_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['is_system' => false]);

        $policy = new CategoryPolicy;

        expect($policy->view($user, $category))->toBeTrue();
    }
}
