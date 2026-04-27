<?php

namespace App\Enums;

enum WalletType: string
{
    case Bank = 'bank';
    case EWallet = 'ewallet';
    case Cash = 'cash';
}
