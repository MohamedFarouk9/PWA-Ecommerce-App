<?php

namespace App\Http\Requests\Api\Orders;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:500'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:stripe,paymob,myfatoorah'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Return XSS-sanitized address and notes fields.
     */
    public function sanitizedFields(): array
    {
        $shipping = strip_tags((string) $this->validated('shipping_address'));
        $billing = strip_tags((string) ($this->validated('billing_address') ?? $shipping));
        $notes = $this->validated('notes') !== null
            ? strip_tags((string) $this->validated('notes'))
            : null;

        return [
            'shipping_address' => $shipping,
            'billing_address' => $billing,
            'notes' => $notes,
            'payment_method' => $this->validated('payment_method'),
            'coupon_code' => $this->validated('coupon_code'),
        ];
    }
}
