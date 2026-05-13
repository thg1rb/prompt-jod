<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityRateLimitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function rate_limiter_tracks_attempts_correctly(): void
    {
        $key = 'test_rate_limit_key';

        RateLimiter::hit($key, 60);

        expect(RateLimiter::attempts($key))->toBe(1);

        RateLimiter::hit($key, 60);
        RateLimiter::hit($key, 60);

        expect(RateLimiter::attempts($key))->toBe(3);
    }

    #[Test]
    public function rate_limiter_detects_when_limit_exceeded(): void
    {
        $key = 'test_rate_limit_exceed';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $isLimited = RateLimiter::tooManyAttempts($key, 5);

        expect($isLimited)->toBeTrue();
    }

    #[Test]
    public function rate_limiter_clears_after_cooldown(): void
    {
        $key = 'test_rate_limit_clear';

        RateLimiter::hit($key, 60);

        expect(RateLimiter::attempts($key))->toBeGreaterThan(0);

        RateLimiter::clear($key);

        expect(RateLimiter::attempts($key))->toBe(0);
    }

    #[Test]
    public function rate_limiter_available_in_seconds(): void
    {
        $key = 'test_rate_limit_available';

        RateLimiter::hit($key, 60);

        $availableIn = RateLimiter::availableIn($key);

        expect($availableIn)->toBeGreaterThan(0);
        expect($availableIn)->toBeLessThanOrEqual(60);
    }
}
