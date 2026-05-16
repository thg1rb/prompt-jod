<?php

use App\Enums\WalletAccess;
use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMember;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('dashboard view renders for authenticated user', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('dashboardData');
    $response->assertViewHas('currentRange', 'month');
});

test('filter endpoint returns correct data structure', function () {
    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'totalExpenses',
        'totalBalance',
        'averagePerTransaction',
        'topCategory',
        'categoryData',
        'sevenDaySpending',
        'recentTransactions',
        'filteredCount',
        'walletCount',
    ]);
});

test('category data groups by fixed_category_id with no duplicates', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create(['name' => 'Food', 'color' => '#ef4444', 'icon' => '🍔']);

    $cat1 = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $cat2 = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat1->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat2->id,
        'amount' => 200,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $categoryData = $response->json('categoryData');

    expect($categoryData)->toHaveCount(1);
    expect($categoryData[0]['id'])->toBe($fc->id);
    expect($categoryData[0]['name'])->toBe('Food');
    expect((float) $categoryData[0]['value'])->toBe(300.0);
    expect($categoryData[0]['color'])->toBe('#ef4444');
    expect($categoryData[0]['icon'])->toBe('🍔');
});

test('category data has correct keys from fixed_categories', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create(['name' => 'Transport', 'color' => '#3b82f6', 'icon' => '🚗']);
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 50,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $entry = $response->json('categoryData.0');

    expect($entry)->toHaveKeys(['id', 'name', 'value', 'color', 'icon']);
    expect($entry['id'])->toBe($fc->id);
    expect($entry['name'])->toBe('Transport');
    expect((float) $entry['value'])->toBe(50.0);
    expect($entry['color'])->toBe('#3b82f6');
    expect($entry['icon'])->toBe('🚗');
});

test('top category is the fixed category with highest spending', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fcFood = FixedCategory::factory()->create(['name' => 'Food', 'color' => '#ef4444', 'icon' => '🍔']);
    $fcTransport = FixedCategory::factory()->create(['name' => 'Transport', 'color' => '#3b82f6', 'icon' => '🚗']);

    $catFood = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fcFood->id]);
    $catTransport = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fcTransport->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $catFood->id,
        'amount' => 500,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $catTransport->id,
        'amount' => 200,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $topCategory = $response->json('topCategory');

    expect($topCategory)->not->toBeNull();
    expect($topCategory['name'])->toBe('Food');
    expect((float) $topCategory['value'])->toBe(500.0);
    expect($topCategory['color'])->toBe('#ef4444');
    expect($topCategory['icon'])->toBe('🍔');
});

test('today date range filtering returns only today expenses', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now()->subDays(10),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 200,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 50,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'today']));

    expect((float) $response->json('totalExpenses'))->toBe(250.0);
    expect($response->json('filteredCount'))->toBe(2);
});

test('week date range filtering works', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now()->startOfWeek()->subDay(),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 300,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'week']));

    expect((float) $response->json('totalExpenses'))->toBe(300.0);
    expect($response->json('filteredCount'))->toBe(1);
});

test('month date range filtering works', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 400,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now()->subMonths(2),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 150,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect((float) $response->json('totalExpenses'))->toBe(150.0);
});

test('all date range filtering includes everything', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 400,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now()->subMonths(6),
    ]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 150,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'all']));

    expect((float) $response->json('totalExpenses'))->toBe(550.0);
    expect($response->json('filteredCount'))->toBe(2);
});

test('personal wallet type filter only includes personal wallets', function () {
    $personalWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Personal,
    ]);
    $sharedWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    $fc = FixedCategory::factory()->create();
    $catPersonal = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $catShared = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($personalWallet)->expense()->create([
        'category_id' => $catPersonal->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($sharedWallet)->expense()->create([
        'category_id' => $catShared->id,
        'amount' => 500,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', [
        'range' => 'month',
        'wallet_type' => 'personal',
    ]));

    expect((float) $response->json('totalExpenses'))->toBe(100.0);
    expect($response->json('walletCount'))->toBe(1);
});

test('shared wallet type filter only includes shared wallets', function () {
    $personalWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Personal,
    ]);
    $sharedWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    $fc = FixedCategory::factory()->create();
    $catPersonal = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $catShared = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($personalWallet)->expense()->create([
        'category_id' => $catPersonal->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($sharedWallet)->expense()->create([
        'category_id' => $catShared->id,
        'amount' => 500,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', [
        'range' => 'month',
        'wallet_type' => 'shared',
    ]));

    expect((float) $response->json('totalExpenses'))->toBe(500.0);
    expect($response->json('walletCount'))->toBe(1);
});

test('all wallet type filter includes both personal and shared wallets', function () {
    $personalWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Personal,
    ]);
    $sharedWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    $fc = FixedCategory::factory()->create();
    $catPersonal = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $catShared = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($personalWallet)->expense()->create([
        'category_id' => $catPersonal->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($sharedWallet)->expense()->create([
        'category_id' => $catShared->id,
        'amount' => 500,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', [
        'range' => 'month',
        'wallet_type' => 'all',
    ]));

    expect((float) $response->json('totalExpenses'))->toBe(600.0);
    expect($response->json('walletCount'))->toBe(2);
});

test('seven day spending returns 7 entries with day and amount keys', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $sevenDaySpending = $response->json('sevenDaySpending');

    expect($sevenDaySpending)->toHaveCount(7);
    expect($sevenDaySpending[0])->toHaveKeys(['day', 'amount']);
    expect($sevenDaySpending[0]['amount'])->toBeNumeric();
});

test('seven day spending sums amounts per day correctly', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    foreach (range(6, 0) as $i) {
        Transaction::factory()->forWallet($wallet)->expense()->create([
            'category_id' => $cat->id,
            'amount' => 100,
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'transacted_at' => now()->subDays($i),
        ]);
    }

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $sevenDaySpending = $response->json('sevenDaySpending');

    expect($sevenDaySpending)->toHaveCount(7);
    expect((float) array_sum(array_column($sevenDaySpending, 'amount')))->toBe(700.0);
});

test('recent transactions include fixed category info', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create([
        'name' => 'Food',
        'color' => '#ef4444',
        'icon' => '🍔',
    ]);
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->create([
        'category_id' => $cat->id,
        'amount' => 150,
        'type' => 'expense',
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'recipient' => 'Test Recipient',
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $recent = $response->json('recentTransactions');

    expect($recent)->toHaveCount(1);
    expect($recent[0])->toHaveKeys([
        'id', 'recipient', 'amount', 'type', 'category',
        'category_color', 'icon', 'wallet', 'transacted_at',
    ]);
    expect($recent[0]['category'])->toBe('Food');
    expect($recent[0]['category_color'])->toBe('#ef4444');
    expect($recent[0]['icon'])->toBe('🍔');
});

test('recent transactions shows fallback when category relation missing', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();

    $transaction = Transaction::factory()->forWallet($wallet)->create([
        'category_id' => null,
        'amount' => 50,
        'type' => 'expense',
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'recipient' => 'Someone',
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $recent = $response->json('recentTransactions.0');

    expect($recent['category'])->toBe('อื่น ๆ');
    expect($recent['category_color'])->toBe('#64748b');
    expect($recent['icon'])->toBe('📌');
});

test('dashboard aggregates across all accessible wallets including shared', function () {
    $personalWallet = Wallet::factory()->forUser($this->user)->create([
        'access_type' => WalletAccess::Personal,
    ]);
    $otherUser = User::factory()->create();
    $sharedWallet = Wallet::factory()->forUser($otherUser)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($this->user)->create([
        'invited_by' => $otherUser->id,
    ]);

    $fc = FixedCategory::factory()->create();
    $catPersonal = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $catShared = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($personalWallet)->expense()->create([
        'category_id' => $catPersonal->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($sharedWallet)->expense()->create([
        'category_id' => $catShared->id,
        'amount' => 250,
        'user_id' => $otherUser->id,
        'created_by' => $otherUser->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect((float) $response->json('totalExpenses'))->toBe(100.0);
    expect($response->json('walletCount'))->toBe(2);
});

test('dashboard does not aggregate wallets from other users without membership', function () {
    $myWallet = Wallet::factory()->forUser($this->user)->create();
    $otherUser = User::factory()->create();
    $otherWallet = Wallet::factory()->forUser($otherUser)->create();

    $fc = FixedCategory::factory()->create();
    $catMine = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);
    $catOther = Category::factory()->forUser($otherUser)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($myWallet)->expense()->create([
        'category_id' => $catMine->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($otherWallet)->expense()->create([
        'category_id' => $catOther->id,
        'amount' => 999,
        'user_id' => $otherUser->id,
        'created_by' => $otherUser->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect((float) $response->json('totalExpenses'))->toBe(100.0);
    expect($response->json('walletCount'))->toBe(1);
});

test('empty state when no transactions exist', function () {
    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    $response->assertSuccessful();
    expect((float) $response->json('totalExpenses'))->toBe(0.0);
    expect($response->json('averagePerTransaction'))->toBe(0);
    expect($response->json('filteredCount'))->toBe(0);
    expect($response->json('topCategory'))->toBeNull();
    expect($response->json('categoryData'))->toBeEmpty();
    expect($response->json('recentTransactions'))->toBeEmpty();
});

test('total expenses only counts expense transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $cat->id,
        'amount' => 100,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forWallet($wallet)->income()->create([
        'category_id' => $cat->id,
        'amount' => 200,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect((float) $response->json('totalExpenses'))->toBe(100.0);
    expect($response->json('filteredCount'))->toBe(1);
});

test('category data is sorted by value descending', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fcC = FixedCategory::factory()->create(['name' => 'C']);
    $fcB = FixedCategory::factory()->create(['name' => 'B']);
    $fcA = FixedCategory::factory()->create(['name' => 'A']);

    $catC = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fcC->id]);
    $catB = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fcB->id]);
    $catA = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fcA->id]);

    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $catC->id, 'amount' => 100,
        'user_id' => $this->user->id, 'created_by' => $this->user->id, 'transacted_at' => now(),
    ]);
    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $catB->id, 'amount' => 300,
        'user_id' => $this->user->id, 'created_by' => $this->user->id, 'transacted_at' => now(),
    ]);
    Transaction::factory()->forWallet($wallet)->expense()->create([
        'category_id' => $catA->id, 'amount' => 200,
        'user_id' => $this->user->id, 'created_by' => $this->user->id, 'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));
    $categoryData = $response->json('categoryData');

    expect($categoryData[0]['name'])->toBe('B');
    expect($categoryData[1]['name'])->toBe('A');
    expect($categoryData[2]['name'])->toBe('C');
});

test('dashboard view data defaults to month range', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data)->toHaveKeys([
        'totalExpenses', 'totalBalance', 'averagePerTransaction',
        'topCategory', 'categoryData', 'sevenDaySpending',
        'recentTransactions', 'filteredCount', 'walletCount',
    ]);
    expect($response->viewData('currentRange'))->toBe('month');
});

test('filter validation rejects invalid range', function () {
    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'year']));

    $response->assertStatus(422);
});

test('filter validation rejects invalid wallet_type', function () {
    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', [
        'range' => 'month',
        'wallet_type' => 'invalid',
    ]));

    $response->assertStatus(422);
});

test('recent transactions limited to 5', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($wallet)->count(8)->create([
        'category_id' => $cat->id,
        'user_id' => $this->user->id,
        'created_by' => $this->user->id,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect($response->json('recentTransactions'))->toHaveCount(5);
});

test('pending shared wallet membership does not give access', function () {
    $myWallet = Wallet::factory()->forUser($this->user)->create();
    $otherUser = User::factory()->create();
    $sharedWallet = Wallet::factory()->forUser($otherUser)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->pending()->forUser($this->user)->create([
        'invited_by' => $otherUser->id,
    ]);

    $fc = FixedCategory::factory()->create();
    $cat = Category::factory()->forUser($this->user)->create(['fixed_category_id' => $fc->id]);

    Transaction::factory()->forWallet($myWallet)->expense()->create([
        'category_id' => $cat->id, 'amount' => 100,
        'user_id' => $this->user->id, 'created_by' => $this->user->id, 'transacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('dashboard.filter', ['range' => 'month']));

    expect($response->json('walletCount'))->toBe(1);
    expect((float) $response->json('totalExpenses'))->toBe(100.0);
});
