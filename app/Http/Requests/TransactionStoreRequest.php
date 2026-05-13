<?php

namespace App\Http\Requests;

use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'wallet_id' => ['required', 'uuid', function ($attribute, $value, $fail) {
                $wallet = Wallet::find($value);
                if (! $wallet) {
                    $fail('กระเป๋าเงินไม่ถูกต้อง');

                    return;
                }
                if (! $wallet->hasAccess(auth()->user())) {
                    $fail('กระเป๋าเงินไม่ถูกต้อง');
                }
            }],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'type' => ['required', 'in:expense,income,adjustment'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'sender' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'transacted_at' => ['required', 'date', 'before_or_equal:now'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'wallet_id.required' => 'กรุณาเลือกกระเป๋าเงิน',
            'wallet_id.exists' => 'กระเป๋าเงินไม่ถูกต้อง',
            'category_id.required' => 'กรุณาเลือกหมวดหมู่',
            'category_id.exists' => 'หมวดหมู่ไม่ถูกต้อง',
            'type.required' => 'กรุณาระบุประเภทธุรกรรม',
            'amount.required' => 'กรุณาระบุจำนวนเงิน',
            'amount.numeric' => 'จำนวนเงินต้องเป็นตัวเลข',
            'amount.min' => 'จำนวนเงินต้องไม่ต่ำกว่า 0.01',
            'transacted_at.required' => 'กรุณาระบุวันที่ทำรายการ',
            'transacted_at.before_or_equal' => 'วันที่ทำรายการต้องไม่เกินวันนี้',
        ];
    }
}
