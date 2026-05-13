<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'access_type' => ['required', 'in:personal,shared'],
            'bank_name' => ['required_if:type,bank', 'nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
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
            'opening_balance.numeric' => 'ยอดเงินเริ่มต้นต้องเป็นตัวเลข',
            'opening_balance.min' => 'ยอดเงินเริ่มต้นต้องไม่ต่ำกว่า 0',
        ];
    }
}
