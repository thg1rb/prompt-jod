<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/callback', function () {
    $googleUser = Socialite::driver('google')->user();

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

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;

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

    // Wallet Routes
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/create', [WalletController::class, 'create'])->name('wallets.create');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');
    Route::get('/wallets/{wallet}/edit', [WalletController::class, 'edit'])->name('wallets.edit');
    Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');

    // Balance Adjustment
    Route::post('/wallets/{wallet}/adjust-balance', [WalletController::class, 'adjustBalance'])->name('wallets.adjust-balance');
    Route::get('/wallets/{wallet}/adjustments', [WalletController::class, 'adjustments'])->name('wallets.adjustments');

    // Set Default
    Route::post('/wallets/{wallet}/set-default', [WalletController::class, 'setDefault'])->name('wallets.set-default');
});

require __DIR__.'/auth.php';
