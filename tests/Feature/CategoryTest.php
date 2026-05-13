<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('user can view categories index', function () {
    Category::factory()->forUser($this->user)->count(5)->create();

    $response = $this->get(route('categories.index'));

    $response->assertStatus(200);
    $response->assertViewIs('categories');
});

test('user can get categories data', function () {
    $testCategories = ['TestCategory1', 'TestCategory2', 'TestCategory3'];

    foreach ($testCategories as $i => $name) {
        Category::factory()->forUser($this->user)->create(['name' => $name]);
    }

    $response = $this->get(route('categories.data'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'categories' => [
            '*' => [
                'id',
                'name',
                'icon',
                'color',
                'is_active',
                'is_system',
            ],
        ],
    ]);
});

test('user can store category', function () {
    $data = [
        'name' => 'Food',
        'icon' => '🍔',
        'color' => '#FF5733',
    ];

    $response = $this->post(route('categories.store'), $data);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'บันทึกหมวดหมู่เรียบร้อย',
    ]);

    $category = Category::where('name', 'Food')->first();
    expect($category->user_id)->toBe($this->user->id);
});

test('user can update category', function () {
    $category = Category::factory()->forUser($this->user)->create();

    $data = [
        'name' => 'Updated Food',
        'icon' => '🍕',
        'color' => '#00FF00',
    ];

    $response = $this->put(route('categories.update', $category), $data);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'แก้ไขหมวดหมู่เรียบร้อย',
    ]);

    $category->refresh();
    expect($category->name)->toBe('Updated Food');
});

test('user cannot update system category', function () {
    $category = Category::factory()->forUser($this->user)->system()->create();

    $data = [
        'name' => 'Updated',
        'icon' => '🍕',
        'color' => '#00FF00',
    ];

    $response = $this->put(route('categories.update', $category), $data);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถแก้ไขหมวดหมู่ระบบได้',
    ]);
});

test('user cannot update other users category', function () {
    $otherUser = User::factory()->create();
    $category = Category::factory()->forUser($otherUser)->create();

    $data = [
        'name' => 'Updated',
        'icon' => '🍕',
        'color' => '#00FF00',
    ];

    $response = $this->put(route('categories.update', $category), $data);

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบหมวดหมู่',
    ]);
});

test('user can delete category', function () {
    $category = Category::factory()->forUser($this->user)->create();

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'ลบหมวดหมู่เรียบร้อย',
    ]);

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('user cannot delete system category', function () {
    $category = Category::factory()->forUser($this->user)->system()->create();

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถลบหมวดหมู่ระบบได้',
    ]);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('user cannot delete other users category', function () {
    $otherUser = User::factory()->create();
    $category = Category::factory()->forUser($otherUser)->create();

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบหมวดหมู่',
    ]);
});

test('user cannot delete category with transactions', function () {
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

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถลบหมวดหมู่ที่มีธุรกรรมได้',
    ]);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category factory creates system category', function () {
    $category = Category::factory()->system()->create();

    expect($category->is_system)->toBeTrue();
});

test('category factory creates custom category', function () {
    $category = Category::factory()->custom()->create();

    expect($category->is_system)->toBeFalse();
});

test('categories are ordered by sort_order then name', function () {
    Category::factory()->forUser($this->user)->create(['sort_order' => 2, 'name' => 'Zebra']);
    Category::factory()->forUser($this->user)->create(['sort_order' => 1, 'name' => 'Alpha']);
    Category::factory()->forUser($this->user)->create(['sort_order' => 1, 'name' => 'Beta']);

    $response = $this->get(route('categories.data'));
    $categories = $response->json('categories');

    $customCategories = array_filter($categories, fn ($c) => ! $c['is_system']);
    $customCategories = array_values($customCategories);

    expect($customCategories[0]['name'])->toBe('Alpha');
    expect($customCategories[1]['name'])->toBe('Beta');
    expect($customCategories[2]['name'])->toBe('Zebra');
});

test('new user gets default categories', function () {
    $user = User::factory()->create();

    $defaultCategories = [
        'อาหาร & เครื่องดื่ม',
        'ช้อปปิ้ง',
        'เดินทาง',
        'ค่าสาธารณูปโภค',
        'บันเทิง',
        'สุขภาพ',
        'การเงิน',
        'อื่น ๆ',
    ];

    foreach ($defaultCategories as $categoryName) {
        $category = Category::where('user_id', $user->id)
            ->where('name', $categoryName)
            ->first();

        expect($category)->not->toBeNull();
        expect($category->is_system)->toBeTrue();
        expect($category->is_active)->toBeTrue();
    }

    expect(Category::where('user_id', $user->id)->count())->toBe(8);
});
