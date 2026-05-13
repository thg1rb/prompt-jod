<?php

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\OmiseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\mock;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);

    Queue::fake();

    $this->omise = mock(OmiseService::class);
    app()->instance(OmiseService::class, $this->omise);
});

test('subscription page redirects to profile', function () {
    $response = $this->get('/subscription');

    $response->assertRedirect(route('profile.edit'));
});

test('user can create monthly subscription', function () {
    $this->omise->shouldReceive('createCustomer')
        ->once()
        ->with($this->user->email, "Subscription for user {$this->user->id}", 'tokn_test_123')
        ->andReturn([
            'success' => true,
            'customer_id' => 'cust_test_123',
            'card_id' => 'card_test_123',
            'default_card_id' => 'card_test_123',
        ]);

    $this->omise->shouldReceive('createChargeSchedule')
        ->once()
        ->with('cust_test_123', 'card_test_123', 9900, Mockery::type('int'), 'PromptJod Premium Subscription')
        ->andReturn([
            'success' => true,
            'schedule_id' => 'schd_test_123',
            'status' => 'active',
        ]);

    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
        'plan' => 'monthly',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'สมัครสมาชิกสำเร็จ',
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $this->user->id,
        'omise_customer_id' => 'cust_test_123',
        'omise_card_id' => 'card_test_123',
        'omise_schedule_id' => 'schd_test_123',
        'status' => 'active',
        'plan' => 'monthly',
        'amount' => 99.00,
    ]);
});

test('user can create yearly subscription', function () {
    $this->omise->shouldReceive('createCustomer')
        ->once()
        ->andReturn([
            'success' => true,
            'customer_id' => 'cust_test_123',
            'card_id' => 'card_test_123',
            'default_card_id' => 'card_test_123',
        ]);

    $this->omise->shouldReceive('createChargeSchedule')
        ->once()
        ->with('cust_test_123', 'card_test_123', 99900, Mockery::type('int'), 'PromptJod Premium Subscription')
        ->andReturn([
            'success' => true,
            'schedule_id' => 'schd_test_123',
            'status' => 'active',
        ]);

    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
        'plan' => 'yearly',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'สมัครสมาชิกสำเร็จ',
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $this->user->id,
        'plan' => 'yearly',
        'amount' => 999.00,
    ]);
});

test('user cannot create duplicate subscription', function () {
    Subscription::factory()->forUser($this->user)->create();

    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
        'plan' => 'monthly',
    ]);

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'คุณมีการสมัครสมาชิกอยู่แล้ว',
    ]);
});

test('subscription creation fails when customer creation fails', function () {
    $this->omise->shouldReceive('createCustomer')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Invalid token',
        ]);

    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_invalid',
        'plan' => 'monthly',
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถสร้างข้อมูลลูกค้าได้',
    ]);

    $this->assertDatabaseMissing('subscriptions', ['user_id' => $this->user->id]);
});

test('subscription creation fails when schedule creation fails', function () {
    $this->omise->shouldReceive('createCustomer')
        ->once()
        ->andReturn([
            'success' => true,
            'customer_id' => 'cust_test_123',
            'card_id' => 'card_test_123',
            'default_card_id' => 'card_test_123',
        ]);

    $this->omise->shouldReceive('createChargeSchedule')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Schedule creation failed',
        ]);

    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
        'plan' => 'monthly',
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถสร้างตารางการชำระเงินได้',
    ]);

    $this->assertDatabaseMissing('subscriptions', ['user_id' => $this->user->id]);
});

test('subscription store requires omise_token', function () {
    $response = $this->postJson(route('subscription.store'), [
        'plan' => 'monthly',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['omise_token']);
});

test('subscription store requires plan', function () {
    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['plan']);
});

test('subscription store rejects invalid plan', function () {
    $response = $this->postJson(route('subscription.store'), [
        'omise_token' => 'tokn_test_123',
        'plan' => 'weekly',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['plan']);
});

test('user can cancel subscription', function () {
    $subscription = Subscription::factory()->forUser($this->user)->create([
        'omise_schedule_id' => 'schd_test_123',
    ]);

    $this->omise->shouldReceive('destroySchedule')
        ->once()
        ->with('schd_test_123')
        ->andReturn([
            'success' => true,
            'deleted' => true,
        ]);

    $response = $this->deleteJson(route('subscription.destroy'));

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'ยกเลิกการสมัครสมาชิกสำเร็จ',
    ]);

    $subscription->refresh();
    expect($subscription->status->value)->toBe('canceled');
});

test('user cannot cancel non-existent subscription', function () {
    $response = $this->deleteJson(route('subscription.destroy'));

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
    ]);
});

test('cancellation fails when Omise schedule destruction fails', function () {
    $subscription = Subscription::factory()->forUser($this->user)->create([
        'omise_schedule_id' => 'schd_test_123',
    ]);

    $this->omise->shouldReceive('destroySchedule')
        ->once()
        ->with('schd_test_123')
        ->andReturn([
            'success' => false,
            'error' => 'Schedule not found',
        ]);

    $response = $this->deleteJson(route('subscription.destroy'));

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถยกเลิกการสมัครสมาชิกได้',
    ]);

    $subscription->refresh();
    expect($subscription->status->value)->not->toBe('canceled');
});

test('user can update subscription card', function () {
    $subscription = Subscription::factory()->forUser($this->user)->create([
        'omise_customer_id' => 'cust_test_123',
    ]);

    $this->omise->shouldReceive('updateCustomerCard')
        ->once()
        ->with('cust_test_123', 'tokn_new_123')
        ->andReturn([
            'success' => true,
            'card_id' => 'card_new_123',
            'default_card_id' => 'card_new_123',
        ]);

    $response = $this->patchJson(route('subscription.update-card'), [
        'omise_token' => 'tokn_new_123',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'อัปเดตบัตรเครดิตสำเร็จ',
    ]);

    $subscription->refresh();
    expect($subscription->omise_card_id)->toBe('card_new_123');
});

test('user cannot update card without subscription', function () {
    $response = $this->patchJson(route('subscription.update-card'), [
        'omise_token' => 'tokn_new_123',
    ]);

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
    ]);
});

test('card update requires omise_token', function () {
    Subscription::factory()->forUser($this->user)->create([
        'omise_customer_id' => 'cust_test_123',
    ]);

    $response = $this->patchJson(route('subscription.update-card'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['omise_token']);
});

test('card update fails when Omise update fails', function () {
    Subscription::factory()->forUser($this->user)->create([
        'omise_customer_id' => 'cust_test_123',
    ]);

    $this->omise->shouldReceive('updateCustomerCard')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Card update failed',
        ]);

    $response = $this->patchJson(route('subscription.update-card'), [
        'omise_token' => 'tokn_invalid',
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถอัปเดตบัตรเครดิตได้',
    ]);
});

test('user can resume canceled subscription', function () {
    $subscription = Subscription::factory()->forUser($this->user)->canceled()->create([
        'omise_customer_id' => 'cust_test_123',
        'omise_card_id' => 'card_test_123',
        'plan' => 'monthly',
    ]);

    $this->omise->shouldReceive('createChargeSchedule')
        ->once()
        ->with('cust_test_123', 'card_test_123', 9900, Mockery::type('int'), 'PromptJod Premium Subscription')
        ->andReturn([
            'success' => true,
            'schedule_id' => 'schd_new_123',
            'status' => 'active',
        ]);

    $response = $this->postJson(route('subscription.resume'));

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'เริ่มใช้งานการสมัครสมาชิกสำเร็จ',
    ]);

    $subscription->refresh();
    expect($subscription->status->value)->toBe('active');
    expect($subscription->omise_schedule_id)->toBe('schd_new_123');
});

test('user cannot resume active subscription', function () {
    Subscription::factory()->forUser($this->user)->active()->create();

    $response = $this->postJson(route('subscription.resume'));

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'การสมัครสมาชิกของคุณยังคงใช้งานอยู่',
    ]);
});

test('user cannot resume non-existent subscription', function () {
    $response = $this->postJson(route('subscription.resume'));

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
    ]);
});

test('resume fails when schedule creation fails', function () {
    $subscription = Subscription::factory()->forUser($this->user)->canceled()->create([
        'omise_customer_id' => 'cust_test_123',
        'omise_card_id' => 'card_test_123',
    ]);

    $this->omise->shouldReceive('createChargeSchedule')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Schedule creation failed',
        ]);

    $response = $this->postJson(route('subscription.resume'));

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'ไม่สามารถสร้างตารางการชำระเงินได้',
    ]);

    $subscription->refresh();
    expect($subscription->status->value)->toBe('canceled');
});

test('user can get payments data', function () {
    $subscription = Subscription::factory()->forUser($this->user)->create();
    Payment::factory()->forUser($this->user)->forSubscription($subscription)->count(5)->create();

    $response = $this->getJson(route('subscription.payments'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'subscription_id',
                'amount',
                'status',
                'paid_at',
            ],
        ],
    ]);
});

test('payments are paginated', function () {
    $subscription = Subscription::factory()->forUser($this->user)->create();
    Payment::factory()->forUser($this->user)->forSubscription($subscription)->count(25)->create();

    $response = $this->getJson(route('subscription.payments', ['per_page' => 10]));

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(10);
});

test('user cannot see other users payments', function () {
    $otherUser = User::factory()->create();
    $otherSubscription = Subscription::factory()->forUser($otherUser)->create();
    Payment::factory()->forUser($otherUser)->forSubscription($otherSubscription)->count(5)->create();

    $subscription = Subscription::factory()->forUser($this->user)->create();
    Payment::factory()->forUser($this->user)->forSubscription($subscription)->count(3)->create();

    $response = $this->getJson(route('subscription.payments'));

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
});

test('subscription factory creates active subscription', function () {
    $subscription = Subscription::factory()->active()->create();

    expect($subscription->status->value)->toBe('active');
});

test('subscription factory creates canceled subscription', function () {
    $subscription = Subscription::factory()->canceled()->create();

    expect($subscription->status->value)->toBe('canceled');
});

test('subscription factory creates past_due subscription', function () {
    $subscription = Subscription::factory()->pastDue()->create();

    expect($subscription->status->value)->toBe('past_due');
});

test('subscription is active when status is active', function () {
    $subscription = Subscription::factory()->active()->create();

    expect($subscription->isActive())->toBeTrue();
    expect($subscription->isPastDue())->toBeFalse();
});

test('subscription is past due when status is past_due', function () {
    $subscription = Subscription::factory()->pastDue()->create();

    expect($subscription->isPastDue())->toBeTrue();
    expect($subscription->isActive())->toBeFalse();
});

test('user needs subscription when they have none', function () {
    expect($this->user->needsSubscription())->toBeTrue();
});

test('user needs subscription when subscription is expired', function () {
    Subscription::factory()->forUser($this->user)->expired()->create();

    expect($this->user->needsSubscription())->toBeTrue();
});

test('user does not need subscription when active', function () {
    Subscription::factory()->forUser($this->user)->active()->create();

    expect($this->user->needsSubscription())->toBeFalse();
});

test('user does not need subscription when canceled', function () {
    Subscription::factory()->forUser($this->user)->canceled()->create();

    expect($this->user->needsSubscription())->toBeFalse();
});
