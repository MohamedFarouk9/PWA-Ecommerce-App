<?php

namespace App\Http\Requests\Api\Payments;

use Illuminate\Foundation\Http\FormRequest;

class ChargePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required'],
            'gateway' => ['required', 'string', 'in:stripe,paymob,myfatoorah'],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'retries' => ['nullable', 'integer', 'min:1', 'max:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.in' => 'Unsupported payment gateway. Choose stripe, paymob, or myfatoorah.',
        ];
    }

    /**
     * Return PCI-DSS sanitized metadata (strips any accidental raw PAN/CVC fields).
     */
    public function safeMetadata(): array
    {
        $metadata = $this->validated('metadata') ?? [];
        unset($metadata['card_number'], $metadata['pan'], $metadata['cvc'], $metadata['cvv']);

        return $metadata;
    }
}
