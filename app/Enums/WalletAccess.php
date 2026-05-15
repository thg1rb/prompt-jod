<?php

namespace App\Enums;

enum WalletAccess: string
{
    case Personal = 'personal';
    case Shared = 'shared';

    public function getLabel(): string
    {
        return match ($this) {
            self::Personal => 'ส่วนตัว',
            self::Shared => 'แชร์กับเพื่อน',
        };
    }

    public function isShared(): bool
    {
        return $this === self::Shared;
    }

    public function isPersonal(): bool
    {
        return $this === self::Personal;
    }
}
