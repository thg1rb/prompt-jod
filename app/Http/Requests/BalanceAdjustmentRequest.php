<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BalanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'new_balance' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['required', 'string', 'max:500'],
            'adjusted_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_balance.required' => 'กรุณาระบุยอดเงินใหม่',
            'new_balance.numeric' => 'ยอดเงินต้องเป็นตัวเลข',
            'new_balance.min' => 'ยอดเงินต้องไม่ต่ำกว่า 0',
            'notes.required' => 'กรุณาระบุเหตุผลการปรับ',
            'adjusted_at.date' => 'วันที่ไม่ถูกต้อง',
        ];
    }
}
