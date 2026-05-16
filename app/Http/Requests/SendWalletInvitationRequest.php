<?php

namespace App\Http\Requests;

use App\Models\WalletMember;
use Illuminate\Foundation\Http\FormRequest;

class SendWalletInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $wallet = $this->route('wallet');

        return $wallet->isOwner(auth()->user());
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $wallet = $this->route('wallet');
                    $exists = WalletMember::where('wallet_id', $wallet->id)
                        ->where('email', $value)
                        ->whereNull('accepted_at')
                        ->validToken()
                        ->exists();

                    if ($exists) {
                        $fail('มีการเชิญอีเมลนี้ไปแล้ว กรุณารอหรือสร้างใหม่');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'กรุณาระบุอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.max' => 'อีเมลต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
