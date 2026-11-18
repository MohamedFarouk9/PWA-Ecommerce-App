<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'           => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status'   => 'required|in:pending,paid,failed,refunded',
            'notes'            => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:500',
            'billing_address'  => 'nullable|string|max:500',
        ];
    }
}
