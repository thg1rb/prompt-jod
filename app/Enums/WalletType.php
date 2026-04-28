<?php

namespace App\Enums;

enum WalletType: string
{
    case Bank = 'bank';
    case EWallet = 'ewallet';
    case Cash = 'cash';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bank => 'บัญชีธนาคาร',
            self::EWallet => 'เว็บเวล็ต',
            self::Cash => 'เงินสด',
        };
    }
}
