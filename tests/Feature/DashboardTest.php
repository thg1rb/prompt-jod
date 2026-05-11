<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('user can view dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard');
    $response->assertViewHas('dashboardData');
    $response->assertViewHas('currentRange');
});

test('dashboard returns correct data structure', function () {
    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data)->toHaveKeys([
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

test('dashboard filters by date range', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 100,
        'type' => 'expense',
        'transacted_at' => now()->subDays(10),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 200,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 50,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $response = $this->get(route('dashboard.filter', ['range' => 'today']));
    $data = $response->json();

    expect((float) $data['totalExpenses'])->toBe(250.0);
    expect($data['filteredCount'])->toBe(2);
});

test('dashboard calculates total expenses correctly', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 100,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 200,
        'type' => 'income',
        'transacted_at' => now(),
    ]);

    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data['totalExpenses'])->toBe(100.0);
});

test('dashboard shows top category', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category1 = Category::factory()->forUser($this->user)->create(['name' => 'Food']);
    $category2 = Category::factory()->forUser($this->user)->create(['name' => 'Transport']);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category1->id,
        'amount' => 500,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category2->id,
        'amount' => 200,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data['topCategory']['name'])->toBe('Food');
});

test('dashboard shows seven day spending', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    foreach (range(6, 0) as $i) {
        Transaction::factory()->forUser($this->user)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 100,
            'type' => 'expense',
            'transacted_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data['sevenDaySpending'])->toHaveCount(7);
    expect(array_sum(array_column($data['sevenDaySpending'], 'amount')))->toBe(700.0);
});

test('dashboard shows recent transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->count(5)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data['recentTransactions'])->toHaveCount(5);
});

test('dashboard category data is sorted by amount descending', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category1 = Category::factory()->forUser($this->user)->create(['name' => 'C']);
    $category2 = Category::factory()->forUser($this->user)->create(['name' => 'B']);
    $category3 = Category::factory()->forUser($this->user)->create(['name' => 'A']);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category1->id,
        'amount' => 100,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category2->id,
        'amount' => 300,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category3->id,
        'amount' => 200,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $response = $this->get(route('dashboard'));
    $data = $response->viewData('dashboardData');

    expect($data['categoryData'][0]['name'])->toBe('B');
    expect($data['categoryData'][1]['name'])->toBe('A');
    expect($data['categoryData'][2]['name'])->toBe('C');
});
