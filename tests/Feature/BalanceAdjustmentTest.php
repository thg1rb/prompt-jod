<?php

use App\Models\BalanceAdjustment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('user can view balance adjustments', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->count(5)->create();

    $response = $this->get(route('wallets.adjustments', $wallet));

    $response->assertStatus(200);
    $response->assertViewIs('wallets.adjustments');
    $response->assertViewHas('adjustments');
    $response->assertViewHas('wallet');
});

test('balance adjustment factory creates opening balance', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $adjustment = BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->openingBalance()->create();

    expect($adjustment->reason)->toBe('opening_balance');
    expect((float) $adjustment->previous_balance)->toBe(0.0);
});

test('balance adjustment factory creates manual adjustment', function () {
    $adjustment = BalanceAdjustment::factory()->manual()->create();

    expect($adjustment->reason)->toBe('manual_adjustment');
});

test('balance adjustment factory creates increase', function () {
    $adjustment = BalanceAdjustment::factory()->increase()->create();

    expect($adjustment->isIncrease())->toBeTrue();
    expect($adjustment->isDecrease())->toBeFalse();
});

test('balance adjustment factory creates decrease', function () {
    $adjustment = BalanceAdjustment::factory()->decrease()->create();

    expect($adjustment->isDecrease())->toBeTrue();
    expect($adjustment->isIncrease())->toBeFalse();
});

test('balance adjustment generates correct description', function () {
    $increase = BalanceAdjustment::factory()->increase()->create();
    $decrease = BalanceAdjustment::factory()->decrease()->create();

    expect($increase->getDescriptionAttribute())->toContain('Increased');
    expect($decrease->getDescriptionAttribute())->toContain('Decreased');
});

test('balance adjustment scope orders by adjusted_at descending', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $old = BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->old()->create();
    $recent = BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->recent()->create();

    $adjustments = BalanceAdjustment::forWallet($wallet->id)->recent()->get();

    expect($adjustments->first()->id)->toBe($recent->id);
    expect($adjustments->last()->id)->toBe($old->id);
});

test('balance adjustment scope filters by wallet', function () {
    $wallet1 = Wallet::factory()->forUser($this->user)->create();
    $wallet2 = Wallet::factory()->forUser($this->user)->create();

    BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet1)->count(3)->create();
    BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet2)->count(2)->create();

    $adjustments = BalanceAdjustment::forWallet($wallet1->id)->get();

    expect($adjustments)->toHaveCount(3);
});

test('balance adjustment scope filters by date range', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $withinRange = BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->create([
        'adjusted_at' => now()->subDays(5),
    ]);

    BalanceAdjustment::factory()->forUser($this->user)->forWallet($wallet)->create([
        'adjusted_at' => now()->subDays(20),
    ]);

    $adjustments = BalanceAdjustment::forWallet($wallet->id)
        ->byDateRange(now()->subWeek(), now())
        ->get();

    expect($adjustments)->toHaveCount(1);
    expect($adjustments->first()->id)->toBe($withinRange->id);
});
