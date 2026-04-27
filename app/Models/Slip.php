<?php

namespace App\Models;

use App\Enums\SlipStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slip extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'wallet_id',
        'category_id',
        'transaction_id',
        'status',
        'verification_status',
        'duplicate_of',
        'transaction_id_field',
        'amount',
        'transacted_at',
        'sender',
        'sender_bank',
        'recipient',
        'recipient_bank',
        'raw_ocr_data',
        'image_path',
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
            'status' => SlipStatus::class,
            'verification_status' => VerificationStatus::class,
            'amount' => 'decimal:2',
            'transacted_at' => 'datetime',
            'raw_ocr_data' => 'array',
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
     * Get the wallet that owns the slip.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the category that owns the slip.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the transaction that owns the slip.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
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
    public function duplicates()
    {
        return $this->hasMany(Slip::class, 'duplicate_of');
    }

    /**
     * Scope a query to only include pending slips.
     */
    public function scopePending($query)
    {
        return $query->where('status', SlipStatus::Pending);
    }

    /**
     * Scope a query to only include processing slips.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', SlipStatus::Processing);
    }

    /**
     * Scope a query to only include done slips.
     */
    public function scopeDone($query)
    {
        return $query->where('status', SlipStatus::Done);
    }

    /**
     * Scope a query to only include failed slips.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', SlipStatus::Failed);
    }

    /**
     * Scope a query to only include verified slips.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', VerificationStatus::Verified);
    }

    /**
     * Scope a query to only include mismatched slips.
     */
    public function scopeMismatch($query)
    {
        return $query->where('verification_status', VerificationStatus::Mismatch);
    }

    /**
     * Scope a query to only include unverified slips.
     */
    public function scopeUnverified($query)
    {
        return $query->where('verification_status', VerificationStatus::Unverified);
    }

    /**
     * Scope a query to only include duplicate slips.
     */
    public function scopeDuplicate($query)
    {
        return $query->whereNotNull('duplicate_of');
    }

    /**
     * Check if the slip is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    /**
     * Check if the slip has a mismatch.
     */
    public function hasMismatch(): bool
    {
        return $this->verification_status === VerificationStatus::Mismatch;
    }

    /**
     * Check if the slip is a duplicate.
     */
    public function isDuplicate(): bool
    {
        return $this->duplicate_of !== null;
    }
}
