<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class EasySlipService
{
    private HttpClient $http;

    private string $apiKey;

    private string $apiUrl;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
        $this->apiKey = config('services.easyslip.api_key');
        $this->apiUrl = config('services.easyslip.api_url');
    }

    public function verifyBankSlip(UploadedFile $image): array
    {
        try {
            $response = $this->http->asMultipart()
                ->timeout(30)
                ->connectTimeout(10)
                ->retry([100, 200, 500])
                ->withToken($this->apiKey, 'Bearer')
                ->attach('image', file_get_contents($image->getPathname()), $image->getClientOriginalName())
                ->post($this->apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                if (! $this->validateResponse($data)) {
                    Log::warning('EasySlip API returned invalid response structure');

                    return [
                        'success' => false,
                        'error' => 'Invalid response from verification service',
                    ];
                }

                return [
                    'success' => true,
                    'data' => $this->parseResponse($data),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to verify slip'),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('EasySlip API Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Unable to connect to verification service',
            ];
        }
    }

    private function validateResponse(array $data): bool
    {
        if (! isset($data['data'])) {
            return false;
        }

        if (! is_array($data['data'])) {
            return false;
        }

        return true;
    }

    private function parseResponse(array $data): array
    {
        $rawSlip = $data['data']['rawSlip'] ?? [];

        $date = null;
        if (isset($rawSlip['date']) && is_string($rawSlip['date'])) {
            try {
                $date = Carbon::parse($rawSlip['date'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to parse slip date: '.$rawSlip['date']);
            }
        }

        $amount = null;
        if (isset($data['data']['amountInSlip'])) {
            $amount = (float) $data['data']['amountInSlip'];
        } elseif (isset($rawSlip['amount']['amount'])) {
            $amount = (float) $rawSlip['amount']['amount'];
        }

        $senderName = null;
        if (isset($rawSlip['sender']['account']['name']['th'])) {
            $senderName = is_string($rawSlip['sender']['account']['name']['th'])
                ? trim($rawSlip['sender']['account']['name']['th'])
                : null;
        }

        $senderBank = null;
        if (isset($rawSlip['sender']['bank']['short'])) {
            $senderBank = is_string($rawSlip['sender']['bank']['short'])
                ? trim($rawSlip['sender']['bank']['short'])
                : null;
        }

        $receiverName = null;
        if (isset($rawSlip['receiver']['account']['name']['th'])) {
            $receiverName = is_string($rawSlip['receiver']['account']['name']['th'])
                ? trim($rawSlip['receiver']['account']['name']['th'])
                : null;
        }

        return [
            'amount' => $amount,
            'date' => $date,
            'sender_name' => $senderName,
            'sender_bank' => $senderBank,
            'receiver_name' => $receiverName,
            'transaction_ref' => is_string($rawSlip['transRef'] ?? null) ? trim($rawSlip['transRef']) : null,
            'slip_payload' => is_string($rawSlip['payload'] ?? null) ? trim($rawSlip['payload']) : null,
            'ref1' => is_string($rawSlip['ref1'] ?? null) ? trim($rawSlip['ref1']) : null,
            'ref2' => is_string($rawSlip['ref2'] ?? null) ? trim($rawSlip['ref2']) : null,
            'raw_data' => $data,
        ];
    }
}
