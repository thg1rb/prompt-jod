<?php

use App\Enums\WalletAccess;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    Auth::login($this->owner);
});

describe('Wallet Ownership and Access', function () {
    test('wallet owner has correct methods', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();

        expect($wallet->isOwner($this->owner))->toBeTrue();
        expect($wallet->isOwner($this->member))->toBeFalse();
        expect($wallet->hasAccess($this->owner))->toBeTrue();
        expect($wallet->hasAccess($this->member))->toBeFalse();
    });

    test('wallet member can access wallet after joining', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $member = WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();

        expect($wallet->hasMember($this->member))->toBeTrue();
        expect($wallet->hasAccess($this->member))->toBeTrue();
    });

    test('pending member cannot access wallet', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        WalletMember::factory()->forWallet($wallet)->pending()->forUser($this->member)->create();

        expect($wallet->hasMember($this->member))->toBeFalse();
        expect($wallet->hasAccess($this->member))->toBeFalse();
    });
});

describe('Invitation System', function () {
    test('owner can generate invitation', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();

        $response = $this->postJson(route('wallets.invitations.create', $wallet));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'invitation' => ['id', 'token', 'url', 'expires_at'],
        ]);
    });

    test('non-owner cannot generate invitation', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        Auth::login($this->member);

        $response = $this->postJson(route('wallets.invitations.create', $wallet));

        $response->assertStatus(403);
    });

    test('member cannot see members list', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();
        Auth::login($this->member);

        $response = $this->getJson(route('wallets.members', $wallet));

        $response->assertStatus(403);
    });

    test('owner can see members list', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();

        $response = $this->getJson(route('wallets.members', $wallet));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'members');
    });

    test('owner can remove member', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $memberRecord = WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();

        $response = $this->deleteJson(route('wallets.members.remove', [$wallet, $this->member->id]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wallet_members', ['id' => $memberRecord->id]);
    });

    test('owner cannot remove self', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->owner)->create();

        $response = $this->deleteJson(route('wallets.members.remove', [$wallet, $this->owner->id]));

        $response->assertStatus(400);
    });
});

describe('Invitation Flow', function () {
    test('user can view invitation page with valid token', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $token = Str::random(64);
        WalletMember::factory()->create([
            'wallet_id' => $wallet->id,
            'user_id' => $this->owner->id,
            'invited_by' => $this->owner->id,
            'token' => $token,
            'token_expires_at' => now()->addHours(24),
        ]);

        $response = $this->get(route('invitations.accept', $token));

        $response->assertStatus(200);
        $response->assertViewHas(['wallet', 'owner', 'token']);
    });

    test('user cannot view invitation page with expired token', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $token = Str::random(64);
        WalletMember::factory()->create([
            'wallet_id' => $wallet->id,
            'user_id' => $this->owner->id,
            'invited_by' => $this->owner->id,
            'token' => $token,
            'token_expires_at' => now()->subHour(),
        ]);

        $response = $this->get(route('invitations.accept', $token));

        $response->assertRedirect();
    });

    test('logged in user can accept invitation', function () {
        Subscription::factory()->forUser($this->owner)->active()->create();
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $token = Str::random(64);
        WalletMember::factory()->create([
            'wallet_id' => $wallet->id,
            'user_id' => $this->owner->id,
            'invited_by' => $this->owner->id,
            'token' => $token,
            'token_expires_at' => now()->addHours(24),
            'accepted_at' => null,
        ]);

        $response = $this->post(route('invitations.accept.store', $token));

        $response->assertRedirect(route('wallets.show', $wallet));
        $this->assertDatabaseHas('wallet_members', [
            'wallet_id' => $wallet->id,
            'user_id' => $this->owner->id,
        ]);
    });
});

describe('Transaction Creation by Member', function () {
    test('wallet member can create transaction', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $category = Category::factory()->forUser($this->member)->create();
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();
        Auth::login($this->member);

        $data = [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100.00,
            'sender' => 'Test Sender',
            'recipient' => 'Test Recipient',
            'transacted_at' => now()->format('Y-m-d\TH:i'),
        ];

        $response = $this->postJson(route('transactions.store'), $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'created_by' => $this->member->id,
        ]);
    });

    test('wallet member cannot create transaction in non-shared wallet', function () {
        $otherWallet = Wallet::factory()->forUser($this->owner)->create();
        $category = Category::factory()->forUser($this->member)->create();
        Auth::login($this->member);

        $data = [
            'wallet_id' => $otherWallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100.00,
            'transacted_at' => now()->format('Y-m-d\TH:i'),
        ];

        $response = $this->postJson(route('transactions.store'), $data);

        $response->assertStatus(422);
    });
});

describe('Member Removal Deletes Transactions', function () {
    test('removing member deletes their transactions', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();
        $category = Category::factory()->forUser($this->owner)->create();
        $memberRecord = WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();

        Auth::login($this->member);
        $transaction = Transaction::factory()->forWallet($wallet)->forUser($this->member)->create([
            'created_by' => $this->member->id,
        ]);

        Auth::login($this->owner);
        $memberRecord->delete();

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    });
});

describe('Wallet Adjustments Access', function () {
    test('owner can access wallet adjustments page', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create();

        $response = $this->get(route('wallets.adjustments', $wallet));

        $response->assertStatus(200);
    });

    test('member can access shared wallet adjustments page', function () {
        Subscription::factory()->forUser($this->member)->active()->create();
        $wallet = Wallet::factory()->forUser($this->owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($this->member)->create();

        Auth::login($this->member);

        $response = $this->get(route('wallets.adjustments', $wallet));

        $response->assertStatus(200);
    });

    test('non-member cannot access wallet adjustments page', function () {
        $wallet = Wallet::factory()->forUser($this->owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);

        Auth::login($this->member);

        $response = $this->get(route('wallets.adjustments', $wallet));

        $response->assertStatus(403);
    });
});
