<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class PaymobGateway implements PaymentGatewayInterface
{
    public function charge(array $data): array
    {
        Log::info('Processing Paymob payment charge', [
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
        ]);

        return [
            'success' => true,
            'transaction_id' => 'paymob_' . uniqid(),
            'gateway' => 'paymob',
            'status' => 'completed',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'EGP',
            ],
        ];
    }

    public function handleWebhook(array $payload): array
    {
        Log::info('Paymob webhook received', ['payload' => $payload]);

        $success = ($payload['success'] ?? false) || (($payload['obj']['success'] ?? false) === true);

        return [
            'status' => 'processed',
            'gateway' => 'paymob',
            'success' => $success,
            'transaction_id' => $payload['obj']['id'] ?? ($payload['id'] ?? null),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        Log::info('Processing Paymob refund', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return [
            'success' => true,
            'refund_id' => 'ref_paymob_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
        ];
    }

    public function getGatewayName(): string
    {
        return 'paymob';
    }

    public function validateData(array $data): bool
    {
        return isset($data['amount'], $data['currency']) && (float) $data['amount'] > 0;
    }
}
