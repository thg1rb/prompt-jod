<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionUpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'omise_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'omise_token.required' => 'กรุณาระบุข้อมูลบัตรเครดิต',
        ];
    }
}
