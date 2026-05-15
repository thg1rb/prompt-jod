<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'custom_categories';

    protected $fillable = [
        'fixed_category_id',
        'name',
        'user_id',
        'wallet_id',
        'icon',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function fixedCategory(): BelongsTo
    {
        return $this->belongsTo(FixedCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getColorAttribute(): string
    {
        return $this->fixedCategory?->color ?? '#64748b';
    }

    public function scopePersonal($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeForWallet($query, string $walletId)
    {
        return $query->where('wallet_id', $walletId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function belongsToWallet(Wallet $wallet): bool
    {
        return $this->wallet_id === $wallet->id;
    }
}
