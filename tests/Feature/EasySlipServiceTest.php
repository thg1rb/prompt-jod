<?php

namespace Tests\Feature;

use App\Services\EasySlipService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;

describe('EasySlipService', function () {
    beforeEach(function () {
        config(['services.easyslip.api_key' => 'test-key']);
        config(['services.easyslip.api_url' => 'https://api.easyslip.com/v1/verify']);
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('verifyBankSlip', function () {
        test('returns success with valid response', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [
                            'date' => '2024-01-15 10:30:00',
                            'amount' => ['amount' => '500.00'],
                            'sender' => [
                                'account' => ['name' => ['th' => 'John Doe']],
                                'bank' => ['short' => 'SCB'],
                            ],
                            'receiver' => [
                                'account' => ['name' => ['th' => 'Coffee Shop']],
                            ],
                            'transRef' => 'REF123',
                            'ref1' => '123456',
                            'ref2' => '789012',
                            'payload' => 'PAYLOAD123',
                        ],
                        'amountInSlip' => '500.00',
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['amount'])->toBe(500.00);
            expect($result['data']['sender_name'])->toBe('John Doe');
            expect($result['data']['sender_bank'])->toBe('SCB');
            expect($result['data']['receiver_name'])->toBe('Coffee Shop');
            expect($result['data']['transaction_ref'])->toBe('REF123');
            expect($result['data']['ref1'])->toBe('123456');
            expect($result['data']['ref2'])->toBe('789012');
        });

        test('returns failure when API returns non-successful status', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response('', 500),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeFalse();
        });

        test('returns failure when response structure is invalid', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response(['data' => 'not an array'], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeFalse();
            expect($result['error'])->toBe('Invalid response from verification service');
        });

        test('returns failure when data key is missing', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeFalse();
            expect($result['error'])->toBe('Invalid response from verification service');
        });

        test('returns failure when exception is thrown', function () {
            Log::shouldReceive('error')
                ->once()
                ->with(Mockery::pattern('/EasySlip API Error: Connection refused/'));

            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => function () {
                    throw new \Exception('Connection refused');
                },
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeFalse();
            expect($result['error'])->toBe('Unable to connect to verification service');
        });
    });

    describe('parseResponse edge cases', function () {
        test('parses amount from rawSlip amount array', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [
                            'date' => '2024-01-15',
                            'amount' => ['amount' => '1234.56'],
                        ],
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['amount'])->toBe(1234.56);
        });

        test('parses sender name when thai name is not string', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [
                            'sender' => [
                                'account' => ['name' => ['th' => 12345]],
                            ],
                        ],
                        'amountInSlip' => '100',
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['sender_name'])->toBeNull();
        });

        test('trims whitespace from sender name', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [
                            'sender' => [
                                'account' => ['name' => ['th' => '  John Doe  ']],
                            ],
                        ],
                        'amountInSlip' => '100',
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['sender_name'])->toBe('John Doe');
        });

        test('returns null for optional fields when missing', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [],
                        'amountInSlip' => '100',
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['sender_name'])->toBeNull();
            expect($result['data']['sender_bank'])->toBeNull();
            expect($result['data']['receiver_name'])->toBeNull();
            expect($result['data']['transaction_ref'])->toBeNull();
            expect($result['data']['ref1'])->toBeNull();
            expect($result['data']['ref2'])->toBeNull();
        });

        test('trims transaction ref when present', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => [
                        'rawSlip' => [
                            'transRef' => '  REF456  ',
                            'ref1' => ' 111 ',
                            'ref2' => ' 222 ',
                        ],
                        'amountInSlip' => '50',
                    ],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
            expect($result['data']['transaction_ref'])->toBe('REF456');
            expect($result['data']['ref1'])->toBe('111');
            expect($result['data']['ref2'])->toBe('222');
        });
    });

    describe('validateResponse', function () {
        test('returns true when data is a non-empty array', function () {
            $image = UploadedFile::fake()->image('slip.jpg');

            Http::fake([
                'api.easyslip.com/*' => Http::response([
                    'data' => ['key' => 'value'],
                ], 200),
            ]);

            $service = app(EasySlipService::class);
            $result = $service->verifyBankSlip($image);

            expect($result['success'])->toBeTrue();
        });
    });
});
