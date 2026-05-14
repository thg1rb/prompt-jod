<?php

namespace Tests\Unit;

use App\Enums\SubscriptionStatus;
use App\Enums\WalletAccess;
use App\Enums\WalletType;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnumTest extends TestCase
{
    #[Test]
    public function wallet_access_get_label(): void
    {
        expect(WalletAccess::Personal->getLabel())->toBe('ส่วนตัว');
        expect(WalletAccess::Shared->getLabel())->toBe('แชร์กับเพื่อน');
    }

    #[Test]
    public function wallet_type_get_label(): void
    {
        expect(WalletType::Bank->getLabel())->toBe('บัญชีธนาคาร');
        expect(WalletType::EWallet->getLabel())->toBe('เว็บเวล็ต');
        expect(WalletType::Cash->getLabel())->toBe('เงินสด');
    }

    #[Test]
    public function subscription_status_get_label(): void
    {
        expect(SubscriptionStatus::Active->getLabel())->toBe('ใช้งานอยู่');
        expect(SubscriptionStatus::Trialing->getLabel())->toBe('ทดลองใช้');
        expect(SubscriptionStatus::PastDue->getLabel())->toBe('ค้างชำระ');
        expect(SubscriptionStatus::Canceled->getLabel())->toBe('ยกเลิกแล้ว');
        expect(SubscriptionStatus::Expired->getLabel())->toBe('หมดอายุ');
        expect(SubscriptionStatus::Paused->getLabel())->toBe('ระงับชั่วคราว');
    }
}
