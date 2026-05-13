<?php

namespace Tests\Unit;

use App\Services\OmiseService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityOmiseWebhookTest extends TestCase
{
    #[Test]
    public function webhook_verification_rejects_requests_when_secret_is_empty(): void
    {
        $omiseService = new OmiseService;

        $result = $omiseService->verifyWebhookSignature(
            '{"key":"charge.create"}',
            'dummy_signature',
            (string) time()
        );

        expect($result)->toBeFalse();
    }

    #[Test]
    public function webhook_verification_rejects_invalid_signatures(): void
    {
        config(['services.omise.webhook_secret' => 'test_secret']);

        $omiseService = new OmiseService;
        $timestamp = (string) time();
        $payload = '{"key":"charge.create"}';

        $result = $omiseService->verifyWebhookSignature(
            $payload,
            'invalid_signature',
            $timestamp
        );

        expect($result)->toBeFalse();
    }

    #[Test]
    public function webhook_verification_accepts_valid_signatures(): void
    {
        $secret = base64_encode('test_webhook_secret');
        config(['services.omise.webhook_secret' => $secret]);

        $omiseService = new OmiseService;
        $timestamp = (string) time();
        $payload = '{"key":"charge.create"}';

        $signedPayload = $timestamp.'.'.$payload;
        $validSignature = hash_hmac('sha256', $signedPayload, base64_decode($secret));

        $result = $omiseService->verifyWebhookSignature(
            $payload,
            $validSignature,
            $timestamp
        );

        expect($result)->toBeTrue();
    }
}
