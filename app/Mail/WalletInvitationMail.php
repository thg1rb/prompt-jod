<?php

namespace App\Mail;

use App\Models\Wallet;
use App\Models\WalletMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Wallet $wallet,
        public WalletMember $invitation,
        public string $acceptUrl
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'เชิญเข้าร่วมกระเป๋าเงิน '.$this->wallet->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.wallet-invitation',
            with: [
                'walletName' => $this->wallet->name,
                'ownerName' => $this->wallet->user->name,
                'acceptUrl' => $this->acceptUrl,
                'expiresAt' => $this->invitation->token_expires_at->format('d/m/Y H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
