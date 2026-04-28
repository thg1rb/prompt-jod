<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletStoreRequest extends FormRequest
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
            'bank_name' => ['required_if:type,bank,ewallet', 'nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'is_default' => ['boolean'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'กรุณาระบุชื่อบัญชี',
            'type.required' => 'กรุณาระบุประเภทบัญชี',
            'type.in' => 'ประเภทบัญชีไม่ถูกต้อง',
            'bank_name.required_if' => 'กรุณาระบุชื่อธนาคาร/บริการ',
            'opening_balance.numeric' => 'ยอดเงินเริ่มต้นต้องเป็นตัวเลข',
            'opening_balance.min' => 'ยอดเงินเริ่มต้นต้องไม่ต่ำกว่า 0',
            'color.regex' => 'รูปแบบสีไม่ถูกต้อง (ต้องเป็น HEX code เช่น #6366f1)',
        ];
    }
}
