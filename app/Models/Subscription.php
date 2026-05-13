<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'omise_customer_id',
        'omise_card_id',
        'omise_schedule_id',
        'omise_default_card_id',
        'status',
        'plan',
        'amount',
        'currency',
        'billing_day',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'trial_ends_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'amount' => 'decimal:2',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    /**
     * Scope a query to only include past due subscriptions.
     */
    public function scopePastDue($query)
    {
        return $query->where('status', SubscriptionStatus::PastDue);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    /**
     * Check if the subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === SubscriptionStatus::PastDue;
    }

    /**
     * Mark the subscription as canceled.
     */
    public function markAsCanceled(): void
    {
        $this->update([
            'status' => SubscriptionStatus::Canceled,
            'canceled_at' => now(),
        ]);
    }

    /**
     * Mark the subscription as past due.
     */
    public function markAsPastDue(): void
    {
        $this->update([
            'status' => SubscriptionStatus::PastDue,
        ]);
    }

    /**
     * Mark the subscription as active.
     */
    public function markAsActive(): void
    {
        $this->update([
            'status' => SubscriptionStatus::Active,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->getLabel();
    }
}
