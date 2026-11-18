<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'            => 'required|string|max:50|unique:coupons,code',
            'description'     => 'nullable|string|max:255',
            'discount_type'   => 'required|in:percentage,fixed',
            'discount_amount' => 'required|numeric|min:0.01',
            'min_spend'       => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
            'usage_per_user'  => 'nullable|integer|min:1',
            'starts_at'       => 'nullable|date',
            'expires_at'      => 'nullable|date|after_or_equal:starts_at',
            'is_active'       => 'nullable|boolean',
        ];
    }
}
