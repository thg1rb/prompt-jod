<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/auth/redirect', function () {
        return Socialite::driver('google')->redirect();
    });

    Route::get('/auth/callback', function () {
        $googleUser = Socialite::driver('google')->user();

        if (! $googleUser->user['email_verified'] ?? true) {
            return redirect('/login')->with('error', 'Google account email must be verified.');
        }

        $user = User::updateOrCreate([
            'google_id' => $googleUser->id,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    });
});

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletMemberController;
use App\Http\Controllers\WebhookController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/filter', [DashboardController::class, 'filter'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.filter');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/terms', function () {
        return view('legal.terms');
    })->name('terms');
    Route::get('/privacy', function () {
        return view('legal.privacy');
    })->name('privacy');

    // Wallet Routes
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/create', [WalletController::class, 'create'])->name('wallets.create');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::patch('/wallets/reorder', [WalletController::class, 'reorder'])->name('wallets.reorder');
    Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');
    Route::get('/wallets/{wallet}/adjustments', [WalletController::class, 'adjustments'])->name('wallets.adjustments');
    Route::get('/wallets/{wallet}/edit', [WalletController::class, 'edit'])->name('wallets.edit');
    Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');

    // Balance Adjustment
    Route::post('/wallets/{wallet}/adjust-balance', [WalletController::class, 'adjustBalance'])->name('wallets.adjust-balance');

    // Set Default
    Route::post('/wallets/{wallet}/set-default', [WalletController::class, 'setDefault'])->name('wallets.set-default');

    // Wallet Member Routes
    Route::get('/wallets/{wallet}/members', [WalletMemberController::class, 'members'])->name('wallets.members');
    Route::get('/wallets/{wallet}/invitations', [WalletMemberController::class, 'invitations'])->name('wallets.invitations');
    Route::post('/wallets/{wallet}/invitations', [WalletMemberController::class, 'createInvitation'])->name('wallets.invitations.create');
    Route::delete('/wallets/{wallet}/members/{user}', [WalletMemberController::class, 'removeMember'])->name('wallets.members.remove');

    // Invitation Routes
    Route::get('/invitations/{token}', [WalletMemberController::class, 'showInvitation'])->name('invitations.accept');
    Route::post('/invitations/{token}/accept', [WalletMemberController::class, 'acceptInvitation'])->name('invitations.accept.store');

    // Category Routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Transaction Routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/data', [TransactionController::class, 'data'])->name('transactions.data');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/verify-slip', [TransactionController::class, 'verifySlip'])
        ->middleware('throttle:10,1')
        ->name('transactions.verify-slip');
    Route::get('/transactions/search-names', [TransactionController::class, 'searchNames'])->name('transactions.search-names');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Subscription Routes
    Route::get('/subscription', fn () => redirect()->route('profile.edit'))->name('subscription.index');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::delete('/subscription', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
    Route::patch('/subscription/card', [SubscriptionController::class, 'updateCard'])->name('subscription.update-card');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
    Route::get('/subscription/payments', [SubscriptionController::class, 'payments'])->name('subscription.payments');
});

// Webhook Routes (no auth required)
Route::post('/webhooks/omise', [WebhookController::class, 'handle'])->name('webhooks.omise');

require __DIR__.'/auth.php';

Route::fallback(function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});
