<?php

namespace App\Models;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAlert extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'budget_id',
        'user_id',
        'alert_type',
        'status',
        'threshold_percent',
        'amount_spent',
        'amount_remaining',
        'message',
        'read_at',
        'dismissed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alert_type' => AlertType::class,
            'status' => AlertStatus::class,
            'threshold_percent' => 'decimal:2',
            'amount_spent' => 'decimal:2',
            'amount_remaining' => 'decimal:2',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * Get the budget that owns the alert.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the user that owns the alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include sent alerts.
     */
    public function scopeSent($query)
    {
        return $query->where('status', AlertStatus::Sent);
    }

    /**
     * Scope a query to only include read alerts.
     */
    public function scopeRead($query)
    {
        return $query->where('status', AlertStatus::Read);
    }

    /**
     * Scope a query to only include dismissed alerts.
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', AlertStatus::Dismissed);
    }

    /**
     * Scope a query to only include unread alerts.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include undismissed alerts.
     */
    public function scopeUndismissed($query)
    {
        return $query->whereNull('dismissed_at');
    }

    /**
     * Scope a query to filter by alert type.
     */
    public function scopeOfType($query, AlertType $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Mark the alert as read.
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update([
                'read_at' => now(),
                'status' => AlertStatus::Read,
            ]);
        }
    }

    /**
     * Dismiss the alert.
     */
    public function dismiss(): void
    {
        $this->update([
            'dismissed_at' => now(),
            'status' => AlertStatus::Dismissed,
        ]);
    }

    /**
     * Check if the alert is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if the alert is dismissed.
     */
    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    /**
     * Check if the alert is a warning at 80%.
     */
    public function isWarning80(): bool
    {
        return $this->alert_type === AlertType::Warning80;
    }

    /**
     * Check if the alert is a warning at 100%.
     */
    public function isWarning100(): bool
    {
        return $this->alert_type === AlertType::Warning100;
    }

    /**
     * Check if the alert is an exceeded alert.
     */
    public function isExceededAlert(): bool
    {
        return $this->alert_type === AlertType::Exceeded;
    }
}
