<?php

namespace App\Observers;

use App\Models\WalletMember;

class WalletMemberObserver
{
    public function deleting(WalletMember $walletMember): void
    {
        $walletMember->wallet->transactions()
            ->where('created_by', $walletMember->user_id)
            ->delete();
    }
}
