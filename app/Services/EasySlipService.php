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
                ->withToken($this->apiKey, 'Bearer')
                ->attach('image', file_get_contents($image->getPathname()), $image->getClientOriginalName())
                ->post($this->apiUrl);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $this->parseResponse($response->json()),
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

    private function parseResponse(array $data): array
    {
        $rawSlip = $data['data']['rawSlip'] ?? [];

        // Parse date from ISO 8601 format
        $date = null;
        if (isset($rawSlip['date'])) {
            try {
                $date = Carbon::parse($rawSlip['date'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Failed to parse date: '.$rawSlip['date']);
            }
        }

        return [
            'amount' => $data['data']['amountInSlip'] ?? $rawSlip['amount']['amount'] ?? null,
            'date' => $date,
            'sender_name' => $rawSlip['sender']['account']['name']['th'] ?? null,
            'sender_bank' => $rawSlip['sender']['bank']['short'] ?? null,
            'receiver_name' => $rawSlip['receiver']['account']['name']['th'] ?? null,
            'transaction_ref' => $rawSlip['transRef'] ?? null,
            'slip_payload' => $rawSlip['payload'] ?? null,
            'ref1' => $rawSlip['ref1'] ?? null,
            'ref2' => $rawSlip['ref2'] ?? null,
            'raw_data' => $data,
        ];
    }
}
