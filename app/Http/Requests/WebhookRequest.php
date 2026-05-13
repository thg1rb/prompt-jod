<?php

namespace App\Http\Requests;

use App\Services\OmiseService;
use Illuminate\Foundation\Http\FormRequest;

class WebhookRequest extends FormRequest
{
    public function __construct(private OmiseService $omise)
    {
        $this->omise = $omise;
    }

    public function authorize(): bool
    {
        $payload = $this->getContent();
        $signature = $this->header('Omise-Signature');
        $timestamp = $this->header('Omise-Signature-Timestamp');

        return $this->omise->verifyWebhookSignature($payload, $signature, $timestamp);
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'data' => ['required', 'array'],
        ];
    }
}
