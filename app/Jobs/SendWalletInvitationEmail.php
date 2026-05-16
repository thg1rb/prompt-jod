<?php

namespace App\Jobs;

use App\Mail\WalletInvitationMail;
use App\Models\Wallet;
use App\Models\WalletMember;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWalletInvitationEmail implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [1, 5, 10];

    public function __construct(
        public Wallet $wallet,
        public WalletMember $invitation,
        public string $email
    ) {}

    public function uniqueId(): string
    {
        return $this->email.':'.$this->wallet->id;
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(): void
    {
        $acceptUrl = route('invitations.accept', ['token' => $this->invitation->token]);

        Mail::to($this->email)->send(new WalletInvitationMail(
            $this->wallet,
            $this->invitation,
            $acceptUrl
        ));
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to send wallet invitation email', [
            'email' => $this->email,
            'wallet_id' => $this->wallet->id,
            'invitation_id' => $this->invitation->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
