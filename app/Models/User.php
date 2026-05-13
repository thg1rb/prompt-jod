<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'google_token', 'google_refresh_token'])]
#[Hidden(['password', 'remember_token', 'google_token', 'google_refresh_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected static function booted(): void
    {
        static::observe(UserObserver::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_token' => 'encrypted',
            'google_refresh_token' => 'encrypted',
        ];
    }

    /**
     * Find or create user by Google ID.
     */
    public static function findByGoogleId(string $googleId): ?self
    {
        return static::where('google_id', $googleId)->first();
    }

    /**
     * Find user by email and link Google account.
     */
    public function linkGoogleAccount(string $googleId, string $token, ?string $refreshToken = null): void
    {
        $this->google_id = $googleId;
        $this->google_token = $token;
        $this->google_refresh_token = $refreshToken;
        $this->save();
    }

    /**
     * Get all wallets for the user.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get all categories for the user.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get all transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all budgets for the user.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get all budget alerts for the user.
     */
    public function budgetAlerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class);
    }

    /**
     * Get all balance adjustments for the user.
     */
    public function balanceAdjustments(): HasMany
    {
        return $this->hasMany(BalanceAdjustment::class);
    }

    /**
     * Get the user's subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get all payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function needsSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription) {
            return true;
        }

        return $subscription->status === SubscriptionStatus::Expired;
    }
}
