<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletMember extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'invited_by',
        'token',
        'token_expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_at');
    }

    public function scopeValidToken(Builder $query): Builder
    {
        return $query->where('token_expires_at', '>', now());
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function isTokenValid(): bool
    {
        return $this->token_expires_at->isFuture();
    }

    public function accept(): void
    {
        $this->update(['accepted_at' => now()]);
    }
}
