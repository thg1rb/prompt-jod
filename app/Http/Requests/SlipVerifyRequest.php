<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlipVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'กรุณาอัพโหลดรูปสลิป',
            'image.image' => 'ไฟล์ต้องเป็นรูปภาพ',
            'image.mimes' => 'รูปภาพต้องเป็นไฟล์ JPEG, PNG หรือ JPG',
            'image.max' => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
        ];
    }
}
