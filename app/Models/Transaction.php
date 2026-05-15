<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TransactionObserver::class])]
class Transaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'created_by',
        'wallet_id',
        'category_id',
        'transaction_ref',
        'type',
        'amount',
        'sender',
        'recipient',
        'note',
        'transacted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'transacted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created the transaction (may differ from user_id for shared wallets).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    /**
     * Scope a query to only include income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    /**
     * Scope a query to only include adjustment transactions.
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', TransactionType::Adjustment);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transacted_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to order by transaction date (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('transacted_at');
    }

    /**
     * Scope a query to order by transaction date (oldest first).
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('transacted_at');
    }

    /**
     * Check if the transaction is an expense.
     */
    public function isExpense(): bool
    {
        return $this->type === TransactionType::Expense;
    }

    /**
     * Check if the transaction is an income.
     */
    public function isIncome(): bool
    {
        return $this->type === TransactionType::Income;
    }

    /**
     * Check if the transaction is an adjustment.
     */
    public function isAdjustment(): bool
    {
        return $this->type === TransactionType::Adjustment;
    }

    /**
     * Get the signed amount (negative for expenses, positive for income/adjustments).
     */
    public function getSignedAmountAttribute(): float
    {
        return $this->isExpense() ? -$this->amount : $this->amount;
    }
}
