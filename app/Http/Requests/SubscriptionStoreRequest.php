<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'omise_token' => ['required', 'string'],
            'plan' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
        ];
    }

    public function messages(): array
    {
        return [
            'omise_token.required' => 'กรุณาระบุข้อมูลบัตรเครดิต',
            'plan.required' => 'กรุณาเลือกแพ็กเกจ',
            'plan.in' => 'แพ็กเกจไม่ถูกต้อง',
        ];
    }
}
