<?php

use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

test('user can create monthly budget', function () {
    $category = Category::factory()->forUser($this->user)->create();

    $budget = Budget::create([
        'user_id' => $this->user->id,
        'category_id' => $category->id,
        'period' => 'monthly',
        'amount' => 5000.00,
        'year' => 2024,
        'month' => 1,
        'is_active' => true,
        'alert_enabled' => true,
        'alert_threshold' => 80,
    ]);

    expect($budget->user_id)->toBe($this->user->id);
    expect($budget->period->value)->toBe('monthly');
    expect($budget->is_active)->toBeTrue();
});

test('user can create yearly budget', function () {
    $category = Category::factory()->forUser($this->user)->create();

    $budget = Budget::create([
        'user_id' => $this->user->id,
        'category_id' => $category->id,
        'period' => 'yearly',
        'amount' => 60000.00,
        'year' => 2024,
        'month' => null,
    ]);

    expect($budget->period->value)->toBe('yearly');
});

test('budget calculates percentage used correctly', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $budget = Budget::factory()->forUser($this->user)->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'year' => now()->year,
        'month' => now()->month,
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 400.00,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    expect($budget->percentage_used)->toBe(40.0);
    expect($budget->remaining_amount)->toBe(600.00);
    expect($budget->spent_amount)->toBe(400.00);
});

test('budget status updates when exceeded', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $budget = Budget::factory()->forUser($this->user)->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'year' => now()->year,
        'month' => now()->month,
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 1200.00,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $budget->updateStatus();
    expect($budget->isExceeded())->toBeTrue();
});

test('budget status updates to warning at threshold', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $budget = Budget::factory()->forUser($this->user)->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'year' => now()->year,
        'month' => now()->month,
        'alert_threshold' => 85,
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 850.00,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $budget->updateStatus();
    expect($budget->isWarning())->toBeTrue();
});

test('budget alerts are created when thresholds are met', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $budget = Budget::factory()->forUser($this->user)->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'year' => now()->year,
        'month' => now()->month,
        'alert_enabled' => true,
        'alert_threshold' => 80,
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 850.00,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $budget->updateStatus();

    expect($budget->alerts()->exists())->toBeTrue();
});

test('budget does not create duplicate alerts', function () {
    $wallet = Wallet::factory()->forUser($this->user)->create();
    $category = Category::factory()->forUser($this->user)->create();
    $budget = Budget::factory()->forUser($this->user)->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'year' => now()->year,
        'month' => now()->month,
        'alert_enabled' => true,
        'alert_threshold' => 80,
    ]);

    Transaction::factory()->forUser($this->user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 850.00,
        'type' => 'expense',
        'transacted_at' => now(),
    ]);

    $budget->updateStatus();
    $alertCount = $budget->alerts()->count();

    $budget->updateStatus();
    expect($budget->alerts()->count())->toBe($alertCount);
});

test('budget alerts can be marked as read', function () {
    $alert = BudgetAlert::factory()->create();

    $alert->markAsRead();

    expect($alert->isRead())->toBeTrue();
    expect($alert->read_at)->not->toBeNull();
});

test('budget alerts can be dismissed', function () {
    $alert = BudgetAlert::factory()->create();

    $alert->dismiss();

    expect($alert->isDismissed())->toBeTrue();
    expect($alert->dismissed_at)->not->toBeNull();
});

test('budget factory creates monthly budget', function () {
    $budget = Budget::factory()->monthly()->create();

    expect($budget->period->value)->toBe('monthly');
});

test('budget factory creates yearly budget', function () {
    $budget = Budget::factory()->yearly()->create();

    expect($budget->period->value)->toBe('yearly');
});

test('budget factory creates active budget', function () {
    $budget = Budget::factory()->active()->create();

    expect($budget->status->value)->toBe('active');
});

test('budget factory creates warning budget', function () {
    Budget::withoutEvents(function () {
        $budget = Budget::factory()->warning()->create();
        expect($budget->status->value)->toBe('warning');
    });
});

test('budget factory creates exceeded budget', function () {
    Budget::withoutEvents(function () {
        $budget = Budget::factory()->exceeded()->create();
        expect($budget->status->value)->toBe('exceeded');
    });
});

test('budget alert factory creates 80% warning', function () {
    $alert = BudgetAlert::factory()->warning80()->create();

    expect($alert->alert_type->value)->toBe('warning_80');
});

test('budget alert factory creates 100% warning', function () {
    $alert = BudgetAlert::factory()->warning100()->create();

    expect($alert->alert_type->value)->toBe('warning_100');
});

test('budget alert factory creates exceeded alert', function () {
    $alert = BudgetAlert::factory()->exceeded()->create();

    expect($alert->alert_type->value)->toBe('exceeded');
});

test('budget alert factory creates sent alert', function () {
    $alert = BudgetAlert::factory()->sent()->create();

    expect($alert->status->value)->toBe('sent');
    expect($alert->read_at)->toBeNull();
});

test('budget alert factory creates read alert', function () {
    $alert = BudgetAlert::factory()->read()->create();

    expect($alert->status->value)->toBe('read');
    expect($alert->read_at)->not->toBeNull();
});

test('budget alert factory creates dismissed alert', function () {
    $alert = BudgetAlert::factory()->dismissed()->create();

    expect($alert->status->value)->toBe('dismissed');
    expect($alert->dismissed_at)->not->toBeNull();
});
