<?php

namespace App\Models;

use App\Enums\BudgetPeriod;
use App\Enums\BudgetStatus;
use App\Observers\BudgetObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([BudgetObserver::class])]
class Budget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'period',
        'status',
        'amount',
        'year',
        'month',
        'is_active',
        'alert_enabled',
        'alert_threshold',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period' => BudgetPeriod::class,
            'status' => BudgetStatus::class,
            'amount' => 'decimal:2',
            'year' => 'integer',
            'month' => 'integer',
            'is_active' => 'boolean',
            'alert_enabled' => 'boolean',
            'alert_threshold' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the budget.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the alerts for the budget.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class);
    }

    /**
     * Get the transactions for this budget's category within the budget period.
     */
    public function transactions()
    {
        $query = $this->category->transactions();

        if ($this->period === BudgetPeriod::Monthly) {
            $query->whereYear('transacted_at', $this->year)
                  ->whereMonth('transacted_at', $this->month);
        } else {
            $query->whereYear('transacted_at', $this->year);
        }

        return $query->expense();
    }

    /**
     * Scope a query to only include active budgets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope a query to filter by period.
     */
    public function scopeOfPeriod($query, BudgetPeriod $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Get the percentage of budget used.
     */
    public function getPercentageUsedAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        $spent = $this->transactions()->sum('amount');
        return min(100, round(($spent / $this->amount) * 100, 2));
    }

    /**
     * Get the remaining budget amount.
     */
    public function getRemainingAmountAttribute(): float
    {
        $spent = $this->transactions()->sum('amount');
        return max(0, $this->amount - $spent);
    }

    /**
     * Get the spent amount.
     */
    public function getSpentAmountAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    /**
     * Check if the budget is exceeded.
     */
    public function isExceeded(): bool
    {
        return $this->status === BudgetStatus::Exceeded;
    }

    /**
     * Check if the budget is in warning state.
     */
    public function isWarning(): bool
    {
        return $this->status === BudgetStatus::Warning;
    }

    /**
     * Update the budget status based on usage.
     */
    public function updateStatus(): void
    {
        $percentageUsed = $this->percentage_used;

        if ($percentageUsed >= 100) {
            $this->status = BudgetStatus::Exceeded;
        } elseif ($percentageUsed >= $this->alert_threshold) {
            $this->status = BudgetStatus::Warning;
        } else {
            $this->status = BudgetStatus::Active;
        }

        $this->save();
    }
}
