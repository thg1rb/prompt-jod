<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,ewallet,cash'],
            'bank_name' => ['required_if:type,bank', 'nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'is_default' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'กรุณาระบุชื่อบัญชี',
            'type.required' => 'กรุณาระบุประเภทบัญชี',
            'type.in' => 'ประเภทบัญชีไม่ถูกต้อง',
            'bank_name.required_if' => 'กรุณาระบุชื่อธนาคาร',
        ];
    }
}
