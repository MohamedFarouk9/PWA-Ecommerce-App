<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class MyFatoorahGateway implements PaymentGatewayInterface
{
    public function charge(array $data): array
    {
        Log::info('Processing MyFatoorah payment charge', [
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
        ]);

        return [
            'success' => true,
            'transaction_id' => 'mf_' . uniqid(),
            'gateway' => 'myfatoorah',
            'status' => 'completed',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'KWD',
            ],
        ];
    }

    public function handleWebhook(array $payload): array
    {
        Log::info('MyFatoorah webhook received', ['payload' => $payload]);

        return [
            'status' => 'processed',
            'gateway' => 'myfatoorah',
            'success' => ($payload['Event'] ?? '') === 'TransactionsStatusChanged',
            'transaction_id' => $payload['Data']['InvoiceId'] ?? null,
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        Log::info('Processing MyFatoorah refund', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return [
            'success' => true,
            'refund_id' => 'ref_mf_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
        ];
    }

    public function getGatewayName(): string
    {
        return 'myfatoorah';
    }

    public function validateData(array $data): bool
    {
        return isset($data['amount'], $data['currency']) && (float) $data['amount'] > 0;
    }
}

