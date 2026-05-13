<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case Paused = 'paused';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'ใช้งานอยู่',
            self::Trialing => 'ทดลองใช้',
            self::PastDue => 'ค้างชำระ',
            self::Canceled => 'ยกเลิกแล้ว',
            self::Expired => 'หมดอายุ',
            self::Paused => 'ระงับชั่วคราว',
        };
    }
}
