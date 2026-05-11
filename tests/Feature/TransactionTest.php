<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
    Storage::fake('public');
});

test('user can view transactions index', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.index'));

    $response->assertStatus(200);
    $response->assertViewIs('transactions');
    $response->assertViewHas('transactions');
    $response->assertViewHas('categories');
    $response->assertViewHas('wallets');
});

test('user can get transactions data', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->count(5)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.data'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'transactions' => [
            '*' => [
                'id',
                'description',
                'amount',
                'type',
                'category_id',
                'category',
                'category_icon',
                'wallet_id',
                'wallet',
                'transacted_at',
            ],
        ],
    ]);
});

test('user can filter transactions by category', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category1 = Category::factory()->forUser($this->user)->create();
    $category2 = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category1->id,
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category2->id,
    ]);

    $response = $this->get(route('transactions.data', ['category' => $category1->id]));
    $data = $response->json('transactions');

    expect($data)->toHaveCount(1);
    expect($data[0]['category_id'])->toBe($category1->id);
});

test('user can filter transactions by wallet', function () {
    $wallet1 = Wallet::factory()->forUser($this->user)->create();
    $wallet2 = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet1->id,
        'category_id' => $category->id,
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet2->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.data', ['wallet' => $wallet1->id]));
    $data = $response->json('transactions');

    expect($data)->toHaveCount(1);
    expect($data[0]['wallet_id'])->toBe($wallet1->id);
});

test('user can search transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Coffee Shop',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Gas Station',
    ]);

    $response = $this->get(route('transactions.data', ['q' => 'coffee']));
    $data = $response->json('transactions');

    expect($data)->toHaveCount(1);
});

test('search returns matching transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Coffee Shop Bangna',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => '7-Eleven',
    ]);

    $response = $this->get(route('transactions.data', ['q' => 'coffee']));
    $data = $response->json('transactions');

    expect($data)->toHaveCount(1);
    expect($data[0]['recipient'])->toBe('Coffee Shop Bangna');
});

test('user can create transaction page', function () {
    $response = $this->get(route('transactions.create'));

    $response->assertStatus(200);
    $response->assertViewIs('transactions.create');
    $response->assertViewHas('wallets');
    $response->assertViewHas('categories');
});

test('user can store transaction', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    $data = [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100.50,
        'sender' => 'John Doe',
        'recipient' => 'Coffee Shop',
        'note' => 'Morning coffee',
        'transacted_at' => now()->toDateString(),
        'transaction_ref' => 'REF123',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'บันทึกธุรกรรมเรียบร้อย',
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 100.50,
    ]);
});

test('user cannot store transaction with other users wallet', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create();
    $category = Category::factory()->forUser($this->user)->create();

    $data = [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100.50,
        'sender' => 'John Doe',
        'recipient' => 'Test',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors('wallet_id');
});

test('user cannot store transaction with other users category', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->forUser($otherUser)->create();

    $data = [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100.50,
        'sender' => 'John Doe',
        'recipient' => 'Test',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors('category_id');
});

test('user can view transaction details', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $transaction = Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.show', $transaction));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'transaction' => [
            'id',
            'wallet_id',
            'category_id',
            'type',
            'amount',
            'sender',
            'recipient',
            'note',
            'transacted_at',
        ],
    ]);
});

test('user cannot view other users transaction', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create();
    $category = Category::factory()->forUser($otherUser)->create();
    $transaction = Transaction::factory()->forUser($otherUser)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.show', $transaction));

    $response->assertStatus(404);
});

test('user can update transaction', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $transaction = Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $data = [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 200.00,
        'sender' => 'Updated Sender',
        'recipient' => 'Updated Recipient',
        'note' => 'Updated note',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'แก้ไขธุรกรรมเรียบร้อย',
    ]);

    $transaction->refresh();
    expect((float) $transaction->amount)->toBe(200.00);
    expect($transaction->type->value)->toBe('income');
});

test('user can delete transaction', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $transaction = Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->delete(route('transactions.destroy', $transaction));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'ลบธุรกรรมเรียบร้อย',
    ]);

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('user cannot delete other users transaction', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($otherUser)->create();
    $category = Category::factory()->forUser($otherUser)->create();
    $transaction = Transaction::factory()->forUser($otherUser)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->delete(route('transactions.destroy', $transaction));

    $response->assertStatus(404);
});

test('transaction factory creates expense', function () {
    $transaction = Transaction::factory()->expense()->create();

    expect($transaction->type->value)->toBe('expense');
});

test('transaction factory creates income', function () {
    $transaction = Transaction::factory()->income()->create();

    expect($transaction->type->value)->toBe('income');
});

test('transaction factory creates adjustment', function () {
    $transaction = Transaction::factory()->adjustment()->create();

    expect($transaction->type->value)->toBe('adjustment');
});

test('transaction is expense when type is expense', function () {
    $transaction = Transaction::factory()->expense()->create();

    expect($transaction->isExpense())->toBeTrue();
    expect($transaction->isIncome())->toBeFalse();
});

test('transaction gets signed amount correctly', function () {
    $expense = Transaction::factory()->expense()->create(['amount' => 100]);
    $income = Transaction::factory()->income()->create(['amount' => 100]);

    expect($expense->getSignedAmountAttribute())->toBe(-100.0);
    expect($income->getSignedAmountAttribute())->toBe(100.0);
});

test('user can search sender names', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'John Doe',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'John Smith',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'Jane Doe',
    ]);

    $response = $this->get(route('transactions.search-names', ['q' => 'John', 'field' => 'sender']));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'names' => [],
    ]);

    $names = $response->json('names');
    expect($names)->toContain('John Doe');
    expect($names)->toContain('John Smith');
    expect($names)->not->toContain('Jane Doe');
});

test('user can search recipient names', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Coffee Shop',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Coffee Corner',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Tea House',
    ]);

    $response = $this->get(route('transactions.search-names', ['q' => 'Coffee', 'field' => 'recipient']));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'names' => [],
    ]);

    $names = $response->json('names');
    expect($names)->toContain('Coffee Shop');
    expect($names)->toContain('Coffee Corner');
    expect($names)->not->toContain('Tea House');
});

test('search names requires valid field parameter', function () {
    $response = $this->getJson(route('transactions.search-names', ['q' => 'test', 'field' => 'invalid']));

    $response->assertUnprocessable();
});

test('search names returns recent names on empty query', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'First Sender',
        'transacted_at' => now()->subDays(3),
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'Second Sender',
        'transacted_at' => now()->subDays(2),
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'Third Sender',
        'transacted_at' => now()->subDay(),
    ]);

    $response = $this->getJson(route('transactions.search-names', ['q' => '', 'field' => 'sender']));

    $response->assertStatus(200);
    $names = $response->json('names');

    expect($names)->toHaveCount(3);
    expect($names[0])->toBe('Third Sender');
    expect($names[1])->toBe('Second Sender');
    expect($names[2])->toBe('First Sender');
});

test('search names returns only user transactions', function () {
    $otherUser = User::factory()->create();
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $otherWallet = Wallet::factory()->forUser($otherUser)->create();
    $category = Category::factory()->forUser($this->user)->create();

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'sender' => 'My Sender',
    ]);
    Transaction::factory()->forUser($otherUser)->create([
        'wallet_id' => $otherWallet->id,
        'category_id' => Category::factory()->forUser($otherUser)->create()->id,
        'sender' => 'Other Sender',
    ]);

    $response = $this->get(route('transactions.search-names', ['q' => 'Sender', 'field' => 'sender']));

    $names = $response->json('names');
    expect($names)->toContain('My Sender');
    expect($names)->not->toContain('Other Sender');
});
