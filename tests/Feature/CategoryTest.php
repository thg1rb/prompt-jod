<?php

use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('index view renders', function () {
    $response = $this->get(route('categories.index'));

    $response->assertSuccessful();
    $response->assertViewIs('categories');
});

test('data endpoint returns user custom categories with fixedCategory relation', function () {
    Category::factory()->forUser($this->user)->count(3)->create();

    $response = $this->getJson(route('categories.data'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'categories' => [
            '*' => [
                'id',
                'fixed_category_id',
                'name',
                'user_id',
                'wallet_id',
                'icon',
                'fixed_category',
            ],
        ],
    ]);

    $categories = $response->json('categories');
    expect($categories)->toHaveCount(3);

    collect($categories)->each(function (array $category) {
        expect($category['fixed_category'])->not->toBeNull();
        expect($category['user_id'])->toBe($this->user->id);
        expect($category['wallet_id'])->toBeNull();
    });
});

test('data endpoint only returns authenticated users categories', function () {
    $otherUser = User::factory()->create();
    Category::factory()->forUser($this->user)->count(2)->create();
    Category::factory()->forUser($otherUser)->count(3)->create();

    $response = $this->getJson(route('categories.data'));
    $categories = $response->json('categories');

    expect($categories)->toHaveCount(2);
});

test('fixed categories endpoint returns all fixed categories', function () {
    $fixedCategories = FixedCategory::factory()->count(3)->create();

    $response = $this->getJson(route('categories.fixed'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'fixed_categories' => [
            '*' => ['id', 'name', 'color', 'icon', 'sort_order', 'type'],
        ],
    ]);

    $returned = $response->json('fixed_categories');
    expect($returned)->toHaveCount($fixedCategories->count());
});

test('fixed categories are ordered by sort_order', function () {
    FixedCategory::factory()->create(['sort_order' => 10, 'name' => 'Zebra']);
    FixedCategory::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
    FixedCategory::factory()->create(['sort_order' => 5, 'name' => 'Beta']);

    $response = $this->getJson(route('categories.fixed'));
    $categories = $response->json('fixed_categories');

    expect($categories[0]['name'])->toBe('Alpha');
    expect($categories[1]['name'])->toBe('Beta');
    expect($categories[2]['name'])->toBe('Zebra');
});

test('premium user can store personal category', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->postJson(route('categories.store'), [
        'fixed_category_id' => $fixedCategory->id,
        'name' => 'Food',
        'icon' => '🍔',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'บันทึกหมวดหมู่เรียบร้อย',
    ]);

    $category = Category::where('name', 'Food')->first();
    expect($category)->not->toBeNull();
    expect($category->user_id)->toBe($this->user->id);
    expect($category->wallet_id)->toBeNull();
    expect($category->fixed_category_id)->toBe($fixedCategory->id);

    $response->assertJsonStructure(['category' => ['id', 'fixed_category']]);
});

test('store requires fixed_category_id', function () {
    Subscription::factory()->forUser($this->user)->active()->create();

    $response = $this->postJson(route('categories.store'), [
        'name' => 'Food',
        'icon' => '🍔',
    ]);

    $response->assertUnprocessable();
    expect($response->json('errors'))->toHaveKey('fixed_category_id');
});

test('store requires valid fixed_category_id', function () {
    Subscription::factory()->forUser($this->user)->active()->create();

    $response = $this->postJson(route('categories.store'), [
        'fixed_category_id' => 'non-existent-uuid',
        'name' => 'Food',
        'icon' => '🍔',
    ]);

    $response->assertUnprocessable();
    expect($response->json('errors'))->toHaveKey('fixed_category_id');
});

test('store requires name', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->postJson(route('categories.store'), [
        'fixed_category_id' => $fixedCategory->id,
        'icon' => '🍔',
    ]);

    $response->assertUnprocessable();
    expect($response->json('errors'))->toHaveKey('name');
});

test('store icon max 4 characters', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->postJson(route('categories.store'), [
        'fixed_category_id' => $fixedCategory->id,
        'name' => 'Test',
        'icon' => '🍔🍕🍕🍕🍔',
    ]);

    $response->assertUnprocessable();
    expect($response->json('errors'))->toHaveKey('icon');
});

test('premium user can update personal category', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $category = Category::factory()->forUser($this->user)->create();
    $newFixedCategory = FixedCategory::factory()->create();

    $response = $this->putJson(route('categories.update', $category), [
        'fixed_category_id' => $newFixedCategory->id,
        'name' => 'Updated Category',
        'icon' => '🍕',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'แก้ไขหมวดหมู่เรียบร้อย',
    ]);

    $category->refresh();
    expect($category->name)->toBe('Updated Category');
    expect($category->icon)->toBe('🍕');
    expect($category->fixed_category_id)->toBe($newFixedCategory->id);
});

test('non-owner cannot update category', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->forUser($otherUser)->create();
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->putJson(route('categories.update', $category), [
        'fixed_category_id' => $fixedCategory->id,
        'name' => 'Hacked',
        'icon' => '🍕',
    ]);

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบหมวดหมู่',
    ]);

    $category->refresh();
    expect($category->name)->not->toBe('Hacked');
});

test('premium user can destroy personal category', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $category = Category::factory()->forUser($this->user)->create();

    $response = $this->deleteJson(route('categories.destroy', $category));

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'ลบหมวดหมู่เรียบร้อย',
    ]);

    expect(Category::withTrashed()->find($category->id)->deleted_at)->not->toBeNull();
});

test('non-owner cannot destroy category', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->forUser($otherUser)->create();

    $response = $this->deleteJson(route('categories.destroy', $category));

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบหมวดหมู่',
    ]);

    expect(Category::find($category->id))->not->toBeNull();
});

test('cannot destroy category with transactions', function () {
    Subscription::factory()->forUser($this->user)->active()->create();
    $category = Category::factory()->forUser($this->user)->create();
    $wallet = Wallet::factory()->forUser($this->user)->create();

    $category->transactions()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'type' => 'expense',
        'amount' => 100,
        'sender' => 'Test',
        'recipient' => 'Test',
        'transacted_at' => now(),
    ]);

    $response = $this->deleteJson(route('categories.destroy', $category));

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถลบหมวดหมู่ที่มีธุรกรรมได้',
    ]);
});

test('free user cannot store category', function () {
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->postJson(route('categories.store'), [
        'fixed_category_id' => $fixedCategory->id,
        'name' => 'Food',
        'icon' => '🍔',
    ]);

    $response->assertForbidden();
    $response->assertJson([
        'success' => false,
        'requires_subscription' => true,
    ]);
});

test('free user cannot update category', function () {
    $category = Category::factory()->forUser($this->user)->create();
    $fixedCategory = FixedCategory::factory()->create();

    $response = $this->putJson(route('categories.update', $category), [
        'fixed_category_id' => $fixedCategory->id,
        'name' => 'Updated',
        'icon' => '🍕',
    ]);

    $response->assertForbidden();
    $response->assertJson([
        'success' => false,
        'requires_subscription' => true,
    ]);
});

test('free user cannot destroy category', function () {
    $category = Category::factory()->forUser($this->user)->create();

    $response = $this->deleteJson(route('categories.destroy', $category));

    $response->assertForbidden();
    $response->assertJson([
        'success' => false,
        'requires_subscription' => true,
    ]);
});

test('new user gets 8 default categories matching fixed_categories', function () {
    $defaultNames = [
        'อาหาร & เครื่องดื่ม',
        'ช้อปปิ้ง',
        'เดินทาง',
        'ค่าสาธารณูปโภค',
        'บันเทิง',
        'สุขภาพ',
        'การเงิน',
        'อื่น ๆ',
    ];

    $fixedCategories = collect();
    foreach ($defaultNames as $i => $name) {
        $fixedCategories->push(FixedCategory::factory()->create([
            'name' => $name,
            'sort_order' => $i + 1,
        ]));
    }

    $user = User::factory()->create();

    expect(Category::where('user_id', $user->id)->count())->toBe(8);

    foreach ($defaultNames as $name) {
        $category = Category::where('user_id', $user->id)->where('name', $name)->first();
        expect($category)->not->toBeNull("Expected default category '{$name}' not found for user");
        expect($category->fixed_category_id)->not->toBeNull();
    }
});

test('category belongs to fixedCategory', function () {
    $fixedCategory = FixedCategory::factory()->create(['name' => 'Travel', 'color' => '#FF0000']);
    $category = Category::factory()->forUser($this->user)->create([
        'fixed_category_id' => $fixedCategory->id,
    ]);

    $category->load('fixedCategory');

    expect($category->fixedCategory->id)->toBe($fixedCategory->id);
    expect($category->fixedCategory->name)->toBe('Travel');
});

test('category color accessor delegates to fixedCategory', function () {
    $fixedCategory = FixedCategory::factory()->create(['color' => '#EC4899']);
    $category = Category::factory()->forUser($this->user)->create([
        'fixed_category_id' => $fixedCategory->id,
    ]);

    expect($category->color)->toBe('#EC4899');
});

test('fixedCategory has many custom categories', function () {
    $fixedCategory = FixedCategory::factory()->create();
    Category::factory()->forUser($this->user)->count(3)->create([
        'fixed_category_id' => $fixedCategory->id,
    ]);

    expect($fixedCategory->fresh()->customCategories)->toHaveCount(3);
});

test('personal category has user_id set and wallet_id null', function () {
    $category = Category::factory()->forUser($this->user)->create();

    expect($category->user_id)->toBe($this->user->id);
    expect($category->wallet_id)->toBeNull();
});

test('wallet category has wallet_id set and user_id null', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forWallet($wallet)->create();

    expect($category->wallet_id)->toBe($wallet->id);
    expect($category->user_id)->toBeNull();
});

test('free user sees categories index', function () {
    Category::factory()->forUser($this->user)->count(3)->create();

    $response = $this->get(route('categories.index'));

    $response->assertSuccessful();
});
