<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slip extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ocr_status',
        'ocr_raw_text',
        'transaction_ref',
        'sender',
        'recipient',
        'bank',
        'amount',
        'transferred_at',
        'verification_status',
        'is_duplicate',
        'duplicate_of',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transferred_at' => 'datetime',
            'is_duplicate' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the slip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction that belongs to this slip.
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Get the duplicate slip (self-reference).
     */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Slip::class, 'duplicate_of');
    }

    /**
     * Get duplicate slips that reference this slip.
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(Slip::class, 'duplicate_of');
    }

    /**
     * Scope a query to only include pending slips.
     */
    public function scopePending($query)
    {
        return $query->where('ocr_status', 'pending');
    }

    /**
     * Scope a query to only include processing slips.
     */
    public function scopeProcessing($query)
    {
        return $query->where('ocr_status', 'processing');
    }

    /**
     * Scope a query to only include done slips.
     */
    public function scopeDone($query)
    {
        return $query->where('ocr_status', 'done');
    }

    /**
     * Scope a query to only include failed slips.
     */
    public function scopeFailed($query)
    {
        return $query->where('ocr_status', 'failed');
    }

    /**
     * Scope a query to only include verified slips.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope a query to only include mismatched slips.
     */
    public function scopeMismatch($query)
    {
        return $query->where('verification_status', 'mismatch');
    }

    /**
     * Scope a query to only include unverified slips.
     */
    public function scopeUnverified($query)
    {
        return $query->where('verification_status', 'unverified');
    }

    /**
     * Scope a query to only include duplicate slips.
     */
    public function scopeDuplicate($query)
    {
        return $query->where('is_duplicate', true);
    }

    /**
     * Check if the slip is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if the slip has a mismatch.
     */
    public function hasMismatch(): bool
    {
        return $this->verification_status === 'mismatch';
    }

    /**
     * Check if the slip is a duplicate.
     */
    public function isDuplicate(): bool
    {
        return $this->is_duplicate;
    }
}
