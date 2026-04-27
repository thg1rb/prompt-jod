<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceAdjustment extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'user_id',
        'previous_balance',
        'new_balance',
        'adjustment_amount',
        'reason',
        'notes',
        'adjusted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_balance' => 'decimal:2',
            'new_balance' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'adjusted_at' => 'datetime',
        ];
    }

    /**
     * Get the wallet that owns the adjustment.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that owns the adjustment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to order by adjusted_at (newest first).
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('adjusted_at');
    }

    /**
     * Scope a query to filter by wallet.
     */
    public function scopeForWallet($query, $walletId)
    {
        return $query->where('wallet_id', $walletId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('adjusted_at', [$startDate, $endDate]);
    }

    /**
     * Check if the adjustment is an increase (positive amount).
     */
    public function isIncrease(): bool
    {
        return $this->adjustment_amount > 0;
    }

    /**
     * Check if the adjustment is a decrease (negative amount).
     */
    public function isDecrease(): bool
    {
        return $this->adjustment_amount < 0;
    }

    /**
     * Get a human-readable description of the adjustment.
     */
    public function getDescriptionAttribute(): string
    {
        $type = $this->isIncrease() ? 'Increased' : 'Decreased';
        $reason = $this->reason ? " ({$this->reason})" : '';

        return "{$type} balance by " . abs($this->adjustment_amount) . $reason;
    }
}
