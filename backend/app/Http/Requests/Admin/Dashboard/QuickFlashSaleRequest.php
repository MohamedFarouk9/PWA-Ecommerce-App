<?php

namespace App\Http\Requests\Admin\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class QuickFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => Str::upper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $this->input('code'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'code'            => ['required', 'string', 'min:3', 'max:32', 'unique:coupons,code'],
            'discount_amount' => ['required', 'numeric', 'min:1', 'max:80'],
            'min_spend'       => ['nullable', 'numeric', 'min:0'],
            'duration_hours'  => ['required', 'integer', 'min:1', 'max:720'],
            'usage_limit'     => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'That coupon code already exists. Please choose a different campaign code.',
        ];
    }
}
