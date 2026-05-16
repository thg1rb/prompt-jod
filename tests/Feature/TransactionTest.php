<?php

use App\Enums\WalletAccess;
use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMember;
use App\Services\EasySlipService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
    Storage::fake('public');

    if (FixedCategory::count() === 0) {
        $categories = [
            ['name' => 'อาหาร & เครื่องดื่ม', 'color' => '#EC4899', 'icon' => '🍜', 'sort_order' => 1, 'type' => 'expense'],
            ['name' => 'ช้อปปิ้ง', 'color' => '#F59E0B', 'icon' => '🛍️', 'sort_order' => 2, 'type' => 'expense'],
            ['name' => 'เดินทาง', 'color' => '#EC4899', 'icon' => '🚗', 'sort_order' => 3, 'type' => 'expense'],
            ['name' => 'ค่าสาธารณูปโภค', 'color' => '#EF4444', 'icon' => '💡', 'sort_order' => 4, 'type' => 'expense'],
            ['name' => 'บันเทิง', 'color' => '#F59E0B', 'icon' => '🎬', 'sort_order' => 5, 'type' => 'expense'],
            ['name' => 'สุขภาพ', 'color' => '#F59E0B', 'icon' => '🏥', 'sort_order' => 6, 'type' => 'expense'],
            ['name' => 'การเงิน', 'color' => '#10B981', 'icon' => '🏦', 'sort_order' => 7, 'type' => 'expense'],
            ['name' => 'อื่น ๆ', 'color' => '#3B82F6', 'icon' => '📌', 'sort_order' => 99, 'type' => 'expense'],
        ];
        foreach ($categories as $cat) {
            FixedCategory::create($cat);
        }
    }
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

test('transaction show returns created_by field', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $transaction = Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('transactions.show', $transaction));

    $response->assertStatus(200);
    $response->assertJsonPath('transaction.created_by', $this->user->id);
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

test('user can export transactions', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('transactions.export'));

    $response->assertStatus(200);
    $response->assertJsonStructure(['rows']);
    $rows = $response->json('rows');
    expect(count($rows))->toBeGreaterThan(1);
});

test('export respects category filter', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $cat1 = Category::factory()->forUser($this->user)->create();
    $cat2 = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $cat1->id,
        'note' => 'Cat1Note',
        'transacted_at' => '2024-01-01 10:00:00',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $cat2->id,
        'note' => 'Cat2Note',
        'transacted_at' => '2024-01-02 10:00:00',
    ]);

    $response = $this->get(route('transactions.export', ['category' => $cat1->id]));
    $rows = $response->json('rows');

    expect(count($rows))->toBe(2);
    expect($rows[1][0])->toBe('01/01/2024 10:00');
});

test('export respects wallet filter', function () {
    $wallet1 = Wallet::factory()->forUser($this->user)->create();
    $wallet2 = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet1->id,
        'category_id' => $category->id,
        'transacted_at' => '2024-01-01 10:00:00',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet2->id,
        'category_id' => $category->id,
        'transacted_at' => '2024-01-02 10:00:00',
    ]);

    $response = $this->get(route('transactions.export', ['wallet' => $wallet1->id]));
    $rows = $response->json('rows');

    expect(count($rows))->toBe(2);
    expect($rows[1][0])->toBe('01/01/2024 10:00');
});

test('export respects search query', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => 'Coffee Shop Bangkok',
        'transacted_at' => '2024-01-01 10:00:00',
    ]);
    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'recipient' => '7-Eleven',
        'transacted_at' => '2024-01-02 10:00:00',
    ]);

    $response = $this->get(route('transactions.export', ['q' => 'coffee']));
    $rows = $response->json('rows');

    expect(count($rows))->toBe(2);
    expect($rows[1][0])->toBe('01/01/2024 10:00');
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

describe('Shared Wallet Visibility', function () {
    test('member can see transactions created by other members in shared wallet', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        $ownerTx = Transaction::factory()->forUser($owner)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $owner->id,
        ]);
        $memberTx = Transaction::factory()->forUser($member)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
        ]);

        Auth::login($member);

        $response = $this->get(route('transactions.index'));
        $transactions = $response->viewData('transactions');

        $txIds = collect($transactions)->pluck('id')->all();
        expect($txIds)->toContain($ownerTx->id);
        expect($txIds)->toContain($memberTx->id);
    });

    test('member can see other members transactions via data endpoint', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        Transaction::factory()->forUser($owner)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $owner->id,
            'recipient' => 'Owner Transaction',
        ]);
        Transaction::factory()->forUser($member)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
            'recipient' => 'Member Transaction',
        ]);

        Auth::login($member);

        $response = $this->getJson(route('transactions.data'));
        $txIds = collect($response->json('transactions'))->pluck('id')->all();

        expect(count($txIds))->toBe(2);
    });

    test('owner can see transactions created by member in shared wallet', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $wallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($wallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        $memberTx = Transaction::factory()->forUser($member)->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
        ]);

        Auth::login($owner);

        $response = $this->get(route('transactions.index'));
        $transactions = $response->viewData('transactions');

        $txIds = collect($transactions)->pluck('id')->all();
        expect($txIds)->toContain($memberTx->id);
    });
});

test('free user cannot verify slip', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $image = UploadedFile::fake()->image('slip.jpg');

    $response = $this->post(route('transactions.verify-slip'), ['image' => $image]);

    $response->assertStatus(403);
    $response->assertJson(['success' => false]);
});

test('premium user can verify slip', function () {
    $user = User::factory()->create();
    Subscription::factory()->active()->create(['user_id' => $user->id]);
    $user->refresh();
    Auth::login($user);

    $mockSlip = UploadedFile::fake()->image('slip.jpg');

    $mockService = Mockery::mock(EasySlipService::class);
    $mockService->shouldReceive('verifyBankSlip')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'amount' => 500.00,
                'date' => '2024-01-15 10:30:00',
                'sender_name' => 'John Doe',
                'sender_bank' => 'SCB',
                'receiver_name' => 'Coffee Shop',
                'transaction_ref' => 'REF123',
                'ref1' => '123456',
                'ref2' => '789012',
            ],
        ]);

    $this->app->instance(EasySlipService::class, $mockService);

    $response = $this->post(route('transactions.verify-slip'), ['image' => $mockSlip]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'slip' => [
            'amount' => 500.00,
            'sender_name' => 'John Doe',
        ],
    ]);
});

test('verify slip returns 400 on api failure', function () {
    $user = User::factory()->create();
    Subscription::factory()->active()->create(['user_id' => $user->id]);
    $user->refresh();
    Auth::login($user);

    $mockSlip = UploadedFile::fake()->image('slip.jpg');

    $mockService = Mockery::mock(EasySlipService::class);
    $mockService->shouldReceive('verifyBankSlip')
        ->once()
        ->andReturn(['success' => false, 'error' => 'Invalid slip image']);

    $this->app->instance(EasySlipService::class, $mockService);

    $response = $this->post(route('transactions.verify-slip'), ['image' => $mockSlip]);

    $response->assertStatus(400);
    $response->assertJson(['success' => false]);
});

test('free user cannot store transaction in shared wallet', function () {
    $owner = User::factory()->create();
    $freeUser = User::factory()->create();

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($freeUser)->create();

    $freeCategory = Category::factory()->forUser($freeUser)->create();

    Auth::login($freeUser);

    $data = [
        'wallet_id' => $sharedWallet->id,
        'category_id' => $freeCategory->id,
        'type' => 'expense',
        'amount' => 100,
        'recipient' => 'Test',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertStatus(403);
    $response->assertJson(['requires_subscription' => true]);
});

test('premium member can store transaction in shared wallet', function () {
    $owner = User::factory()->create();
    $premiumUser = User::factory()->create();

    $subscription = Subscription::factory()->active()->create(['user_id' => $premiumUser->id]);

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($premiumUser)->create();

    $category = Category::factory()->forUser($premiumUser)->create();

    Auth::login($premiumUser);

    $data = [
        'wallet_id' => $sharedWallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'recipient' => 'Test Shared Wallet',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('owner can store transaction in shared wallet regardless of subscription', function () {
    $owner = User::factory()->create();
    Auth::login($owner);

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);

    $sharedWallet->setupSharedWalletCategories();
    $category = $sharedWallet->customCategories()->first();

    expect($category)->not->toBeNull();

    $data = [
        'wallet_id' => $sharedWallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'recipient' => 'Owner Transaction',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('free user cannot show transaction in shared wallet', function () {
    $owner = User::factory()->create();
    $freeUser = User::factory()->create();

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($freeUser)->create();

    $category = Category::factory()->forUser($owner)->create();
    $transaction = Transaction::factory()->forUser($owner)->create([
        'wallet_id' => $sharedWallet->id,
        'category_id' => $category->id,
    ]);

    Auth::login($freeUser);

    $response = $this->getJson(route('transactions.show', $transaction));

    $response->assertStatus(403);
    $response->assertJson(['requires_subscription' => true]);
});

test('free user cannot update transaction in shared wallet', function () {
    $owner = User::factory()->create();
    $freeUser = User::factory()->create();

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($freeUser)->create();

    $ownerCategory = Category::factory()->forUser($owner)->create();
    $freeCategory = Category::factory()->forUser($freeUser)->create();
    $transaction = Transaction::factory()->forUser($owner)->create([
        'wallet_id' => $sharedWallet->id,
        'category_id' => $ownerCategory->id,
    ]);

    Auth::login($freeUser);

    $data = [
        'wallet_id' => $sharedWallet->id,
        'category_id' => $freeCategory->id,
        'type' => 'expense',
        'amount' => 200,
        'recipient' => 'Updated',
        'transacted_at' => now()->toDateString(),
    ];

    $response = $this->putJson(route('transactions.update', $transaction), $data);

    $response->assertStatus(403);
    $response->assertJson(['requires_subscription' => true]);
});

test('free user cannot delete transaction in shared wallet', function () {
    $owner = User::factory()->create();
    $freeUser = User::factory()->create();

    $sharedWallet = Wallet::factory()->forUser($owner)->create([
        'access_type' => WalletAccess::Shared,
    ]);
    WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($freeUser)->create();

    $category = Category::factory()->forUser($owner)->create();
    $transaction = Transaction::factory()->forUser($owner)->create([
        'wallet_id' => $sharedWallet->id,
        'category_id' => $category->id,
    ]);

    Auth::login($freeUser);

    $response = $this->deleteJson(route('transactions.destroy', $transaction));

    $response->assertStatus(403);
    $response->assertJson(['requires_subscription' => true]);
});

describe('Wallet Type Filter', function () {
    test('user can filter transactions by wallet type personal', function () {
        $personalWallet = Wallet::factory()->forUser($this->user)->create([
            'access_type' => WalletAccess::Personal,
        ]);
        $sharedWallet = Wallet::factory()->forUser($this->user)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        $category = Category::factory()->forUser($this->user)->create();

        Transaction::factory()->forUser($this->user)->create([
            'wallet_id' => $personalWallet->id,
            'category_id' => $category->id,
        ]);
        Transaction::factory()->forUser($this->user)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('transactions.data', ['wallet_type' => 'personal']));
        $data = $response->json('transactions');

        expect($data)->toHaveCount(1);
        expect($data[0]['wallet_id'])->toBe($personalWallet->id);
    });

    test('user can filter transactions by wallet type shared', function () {
        $personalWallet = Wallet::factory()->forUser($this->user)->create([
            'access_type' => WalletAccess::Personal,
        ]);
        $sharedWallet = Wallet::factory()->forUser($this->user)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        $category = Category::factory()->forUser($this->user)->create();

        Transaction::factory()->forUser($this->user)->create([
            'wallet_id' => $personalWallet->id,
            'category_id' => $category->id,
        ]);
        Transaction::factory()->forUser($this->user)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('transactions.data', ['wallet_type' => 'shared']));
        $data = $response->json('transactions');

        expect($data)->toHaveCount(1);
        expect($data[0]['wallet_id'])->toBe($sharedWallet->id);
    });

    test('shared wallet member can filter to show only own transactions', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $sharedWallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        Transaction::factory()->forUser($owner)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $owner->id,
        ]);
        Transaction::factory()->forUser($member)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
        ]);

        Auth::login($member);

        $response = $this->get(route('transactions.data', [
            'wallet_type' => 'shared',
            'transaction_filter' => 'shared',
        ]));
        $data = $response->json('transactions');

        expect($data)->toHaveCount(1);
        expect($data[0]['created_by'])->toBe($member->id);
    });

    test('shared wallet owner can filter to show only own transactions', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $sharedWallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        Transaction::factory()->forUser($owner)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $owner->id,
        ]);
        Transaction::factory()->forUser($member)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
        ]);

        Auth::login($owner);

        $response = $this->get(route('transactions.data', [
            'wallet_type' => 'shared',
            'transaction_filter' => 'shared',
        ]));
        $data = $response->json('transactions');

        expect($data)->toHaveCount(1);
        expect($data[0]['created_by'])->toBe($owner->id);
    });

    test('shared wallet shows all transactions by default when wallet_type is shared', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $sharedWallet = Wallet::factory()->forUser($owner)->create([
            'access_type' => WalletAccess::Shared,
        ]);
        WalletMember::factory()->forWallet($sharedWallet)->accepted()->forUser($member)->create();

        $category = Category::factory()->forUser($owner)->create();
        Transaction::factory()->forUser($owner)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $owner->id,
        ]);
        Transaction::factory()->forUser($member)->create([
            'wallet_id' => $sharedWallet->id,
            'category_id' => $category->id,
            'created_by' => $member->id,
        ]);

        Auth::login($member);

        $response = $this->get(route('transactions.data', ['wallet_type' => 'shared']));
        $data = $response->json('transactions');

        expect($data)->toHaveCount(2);
    });
});
