<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fixed_category_id' => ['required', 'uuid', 'exists:fixed_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:4'],
        ];
    }
}
