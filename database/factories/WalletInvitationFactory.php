<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletInvitation>
 */
class WalletInvitationFactory extends Factory
{
    protected $model = WalletInvitation::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'invited_by' => fn () => User::factory(),
            'token' => fn () => Str::random(64),
            'expires_at' => now()->addHours(24),
        ];
    }
}
