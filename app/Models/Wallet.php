<?php

namespace App\Models;

use App\Enums\WalletAccess;
use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'access_type',
        'bank_name',
        'account_number',
        'balance',
        'is_active',
        'is_default',
        'notes',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletType::class,
            'access_type' => WalletAccess::class,
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the balance adjustments for the wallet.
     */
    public function balanceAdjustments(): HasMany
    {
        return $this->hasMany(BalanceAdjustment::class);
    }

    /**
     * Get the members for the wallet.
     */
    public function members(): HasMany
    {
        return $this->hasMany(WalletMember::class);
    }

    /**
     * Get the accepted members for the wallet.
     */
    public function acceptedMembers(): HasMany
    {
        return $this->members()->accepted();
    }

    /**
     * Get the pending invitations for the wallet.
     */
    public function invitations(): HasMany
    {
        return $this->members()->pending();
    }

    /**
     * Check if the given user is the owner of the wallet.
     */
    public function isOwner(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }

    /**
     * Check if the given user is a member of the wallet.
     */
    public function hasMember(?User $user): bool
    {
        return $user && $this->members()->where('user_id', $user->id)->accepted()->exists();
    }

    /**
     * Check if the given user can access the wallet (owner or member).
     */
    public function hasAccess(?User $user): bool
    {
        return $this->isOwner($user) || $this->hasMember($user);
    }

    /**
     * Get the member count for the wallet.
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->accepted()->count();
    }

    /**
     * Scope a query to only include active wallets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default wallets.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include wallets of a specific type.
     */
    public function scopeOfType($query, WalletType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include shared wallets.
     */
    public function scopeShared($query)
    {
        return $query->where('access_type', WalletAccess::Shared);
    }

    /**
     * Scope a query to only include personal wallets.
     */
    public function scopePersonal($query)
    {
        return $query->where('access_type', WalletAccess::Personal);
    }
}
