<?php

namespace App\Http\Requests\Api\Payments;

use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    /**
     * Strictly restrict refund operations to administrators.
     */
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user && (
            $user->role === 'admin'
            || (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'super_admin']))
        );
    }

    public function rules(): array
    {
        return [
            'gateway' => ['required', 'string', 'in:stripe,paymob,myfatoorah'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ];
    }
}
