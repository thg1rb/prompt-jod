<?php

use App\Models\BalanceAdjustment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('user can view wallets index', function () {
    Wallet::factory()->forUser($this->user)->count(3)->create();

    $response = $this->get(route('wallets.index'));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.index');
    $response->assertViewHas('wallets');
    $response->assertViewHas('totalBalance');
});

test('user can create wallet page', function () {
    $response = $this->get(route('wallets.create'));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.create');
});

test('user can store wallet', function () {
    $data = [
        'name' => 'Main Wallet',
        'type' => 'bank',
        'access_type' => 'personal',
        'bank_name' => 'KBANK',
        'account_number' => '123-4-56789-0',
        'opening_balance' => 1000.00,
        'is_default' => true,
        'notes' => 'Test wallet',
    ];

    $response = $this->post(route('wallets.store'), $data);

    $response->assertRedirect(route('wallets.index'));
    $this->assertDatabaseHas('wallets', [
        'user_id' => $this->user->id,
        'name' => 'Main Wallet',
        'balance' => 1000.00,
    ]);

    $wallet = Wallet::where('name', 'Main Wallet')->first();
    expect($wallet->is_default)->toBeTrue();

    $this->assertDatabaseHas('balance_adjustments', [
        'wallet_id' => $wallet->id,
        'reason' => 'opening_balance',
        'adjustment_amount' => 1000.00,
    ]);
});

test('user can view wallet details', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $response = $this->get(route('wallets.show', $wallet));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.show');
    $response->assertViewHas('wallet');
});

test('user cannot view other users wallet', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create();

    $response = $this->get(route('wallets.show', $wallet));

    $response->assertStatus(403);
});

test('user can view wallet adjustments', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->count(3)->create();

    $response = $this->get(route('wallets.adjustments', $wallet));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.adjustments');
    $response->assertViewHas('adjustments');
});

test('user can edit wallet page', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $response = $this->get(route('wallets.edit', $wallet));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.edit');
});

test('user cannot edit other users wallet', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create();

    $response = $this->get(route('wallets.edit', $wallet));

    $response->assertStatus(403);
});

test('user can update wallet', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $data = [
        'name' => 'Updated Wallet',
        'type' => 'ewallet',
        'notes' => 'Updated notes',
    ];

    $response = $this->put(route('wallets.update', $wallet), $data);

    $response->assertRedirect(route('wallets.index'));
    $this->assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'name' => 'Updated Wallet',
        'notes' => 'Updated notes',
    ]);
});

test('user can delete wallet without transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $response = $this->delete(route('wallets.destroy', $wallet));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'ลบบัญชีเรียบร้อยแล้ว',
    ]);
    $this->assertSoftDeleted('wallets', ['id' => $wallet->id]);
});

test('user cannot delete wallet with transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $wallet->transactions()->create([
        'user_id' => $this->user->id,
        'category_id' => null,
        'type' => 'expense',
        'amount' => 100,
        'sender' => 'Test',
        'recipient' => 'Test',
        'transacted_at' => now(),
    ]);

    $response = $this->delete(route('wallets.destroy', $wallet));

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถลบกระเป๋าเงินที่มีธุรกรรมได้',
    ]);
    $this->assertDatabaseHas('wallets', ['id' => $wallet->id]);
});

test('user can adjust wallet balance', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create(['balance' => 1000.00]);

    $data = [
        'new_balance' => 1500.00,
        'notes' => 'Adjustment reason',
    ];

    $response = $this->post(route('wallets.adjust-balance', $wallet), $data);

    $response->assertRedirect(route('wallets.show', $wallet));

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(1500.0);

    $this->assertDatabaseHas('balance_adjustments', [
        'wallet_id' => $wallet->id,
        'previous_balance' => 1000.00,
        'new_balance' => 1500.00,
        'adjustment_amount' => 500.00,
        'reason' => 'manual_adjustment',
    ]);
});

test('user cannot adjust other users wallet balance', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create(['balance' => 1000.00]);

    $data = [
        'new_balance' => 1500.00,
        'notes' => 'Adjustment reason',
    ];

    $response = $this->post(route('wallets.adjust-balance', $wallet), $data);

    $response->assertStatus(403);
});

test('user can set default wallet', function () {
    $wallet1 = Wallet::factory()->forUser($this->user)->default()->create();
    $wallet2 = Wallet::factory()->forUser($this->user)->create();

    $response = $this->post(route('wallets.set-default', $wallet2));

    $response->assertRedirect(route('wallets.index'));

    $wallet1->refresh();
    $wallet2->refresh();

    expect($wallet1->is_default)->toBeFalse();
    expect($wallet2->is_default)->toBeTrue();
});

test('wallet factory creates bank type correctly', function () {
    $wallet = Wallet::factory()->bank()->create();

    expect($wallet->type->value)->toBe('bank');
    expect($wallet->bank_name)->not->toBeNull();
    expect($wallet->account_number)->not->toBeNull();
});

test('wallet factory creates ewallet type correctly', function () {
    $wallet = Wallet::factory()->ewallet()->create();

    expect($wallet->type->value)->toBe('ewallet');
    expect($wallet->bank_name)->toBeNull();
});

test('wallet factory creates cash type correctly', function () {
    $wallet = Wallet::factory()->cash()->create();

    expect($wallet->type->value)->toBe('cash');
    expect($wallet->bank_name)->toBeNull();
    expect($wallet->account_number)->toBeNull();
});

test('user can reorder wallets', function () {
    $wallet1 = Wallet::factory()->forUser($this->user)->create(['sort_order' => 1]);
    $wallet2 = Wallet::factory()->forUser($this->user)->create(['sort_order' => 2]);
    $wallet3 = Wallet::factory()->forUser($this->user)->create(['sort_order' => 3]);

    $response = $this->patchJson(route('wallets.reorder'), [
        'ids' => [$wallet3->id, $wallet1->id, $wallet2->id],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    expect($wallet3->fresh()->sort_order)->toBe(1);
    expect($wallet1->fresh()->sort_order)->toBe(2);
    expect($wallet2->fresh()->sort_order)->toBe(3);
});

test('reorder validates ids are required', function () {
    $response = $this->patchJson(route('wallets.reorder'), []);

    $response->assertUnprocessable();
});

test('reorder rejects non-uuid ids', function () {
    $response = $this->patchJson(route('wallets.reorder'), [
        'ids' => ['not-a-uuid'],
    ]);

    $response->assertUnprocessable();
});

test('user cannot reorder wallets belonging to another user', function () {
    $otherUser = User::factory()->create();
    $otherWallet = Wallet::factory()->forUser($otherUser)->create();

    $response = $this->patchJson(route('wallets.reorder'), [
        'ids' => [$otherWallet->id],
    ]);

    $response->assertForbidden();
});

test('wallets are ordered by sort_order on index', function () {
    Wallet::factory()->forUser($this->user)->create(['name' => 'Alpha', 'sort_order' => 3]);
    Wallet::factory()->forUser($this->user)->create(['name' => 'Bravo', 'sort_order' => 1]);
    Wallet::factory()->forUser($this->user)->create(['name' => 'Charlie', 'sort_order' => 2]);

    $response = $this->get(route('wallets.index'));

    $response->assertSuccessful();
    $wallets = $response->viewData('wallets');

    expect($wallets->get(0)->name)->toBe('Bravo');
    expect($wallets->get(1)->name)->toBe('Charlie');
    expect($wallets->get(2)->name)->toBe('Alpha');
});

test('free user cannot create wallet when at limit', function () {
    Wallet::factory()->forUser($this->user)->count(5)->create();

    $data = [
        'name' => 'Extra Wallet',
        'type' => 'bank',
        'access_type' => 'personal',
        'bank_name' => 'SCB',
    ];

    $response = $this->postJson(route('wallets.store'), $data);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'requires_subscription' => true,
    ]);
});

test('free user cannot create shared wallet', function () {
    $data = [
        'name' => 'Shared Wallet',
        'type' => 'bank',
        'access_type' => 'shared',
        'bank_name' => 'SCB',
    ];

    $response = $this->post(route('wallets.store'), $data);

    $response->assertRedirect(route('wallets.index'));
    $wallet = Wallet::where('name', 'Shared Wallet')->first();
    expect($wallet)->not->toBeNull();
    expect($wallet->access_type->value)->toBe('personal');
});

test('free user cannot access shared wallet details', function () {
    $owner = User::factory()->create();
    $wallet = Wallet::factory()->forUser($owner)->create(['access_type' => 'shared']);

    $response = $this->getJson(route('wallets.show', $wallet));

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึงกระเป๋าเงินนี้',
    ]);
});

test('free user can access owned wallet details', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $response = $this->get(route('wallets.show', $wallet));

    $response->assertStatus(200);
});

test('wallet access type is forced to personal for free user', function () {
    $data = [
        'name' => 'Test Wallet',
        'type' => 'bank',
        'access_type' => 'shared',
        'bank_name' => 'SCB',
    ];

    $response = $this->post(route('wallets.store'), $data);

    $response->assertRedirect(route('wallets.index'));

    $wallet = Wallet::where('name', 'Test Wallet')->first();
    expect($wallet)->not->toBeNull();
    expect($wallet->access_type->value)->toBe('personal');
});
