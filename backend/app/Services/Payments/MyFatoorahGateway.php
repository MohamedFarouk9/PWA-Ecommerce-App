<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $apiKey;
    private string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.myfatoorah.base_url', 'https://apitest.myfatoorah.com'), '/');
        $this->apiKey = (string) (config('services.myfatoorah.api_key') ?? '');
        $this->webhookSecret = (string) (config('services.myfatoorah.webhook_secret') ?? '');
    }

    public function charge(array $data): array
    {
        Log::info('Processing MyFatoorah payment charge', [
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'mode' => $this->isLiveConfigured() ? 'live_api' : 'sandbox',
        ]);

        if ($this->isLiveConfigured()) {
            return $this->chargeViaMyFatoorahApi($data);
        }

        return $this->chargeSandbox($data);
    }

    private function chargeViaMyFatoorahApi(array $data): array
    {
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');
        $billing = $data['metadata']['billing'] ?? [];

        $response = Http::timeout(15)
            ->withToken($this->apiKey)
            ->post("{$this->baseUrl}/v2/SendPayment", [
                'NotificationOption' => 'LNK',
                'InvoiceValue' => (float) $data['amount'],
                'DisplayCurrencyIso' => strtoupper($data['currency'] ?? 'USD'),
                'CustomerName' => $billing['full_name'] ?? 'Customer',
                'CustomerEmail' => $data['metadata']['customer_email'] ?? 'customer@example.com',
                'CustomerReference' => (string) ($data['order_id'] ?? ''),
                'CallBackUrl' => "{$frontendUrl}/orders?payment=myfatoorah_success",
                'ErrorUrl' => "{$frontendUrl}/payment?payment=failed",
            ]);

        if (!$response->successful() || !$response->json('IsSuccess')) {
            return [
                'success' => false,
                'retryable' => $response->serverError() || $response->status() === 429,
                'gateway' => 'myfatoorah',
                'status' => 'failed',
                'mode' => 'live_api',
                'error' => $response->json('Message', 'MyFatoorah invoice creation failed.'),
            ];
        }

        $invoiceUrl = $response->json('Data.InvoiceURL');

        return [
            'success' => true,
            'transaction_id' => (string) $response->json('Data.InvoiceId'),
            'payment_url' => $invoiceUrl,
            'gateway' => 'myfatoorah',
            'status' => 'pending',
            'mode' => 'live_api',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'payment_url' => $invoiceUrl,
            ],
        ];
    }

    private function chargeSandbox(array $data): array
    {
        $cardLast4 = (string) ($data['metadata']['card_last4'] ?? '4242');
        $scenario = strtolower((string) ($data['metadata']['test_card_scenario'] ?? ''));

        if ($cardLast4 === '9995' || $scenario === 'decline') {
            return [
                'success' => false,
                'retryable' => false,
                'gateway' => 'myfatoorah',
                'status' => 'failed',
                'mode' => 'sandbox',
                'error' => 'MyFatoorah card authorization declined.',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'mf_sandbox_' . bin2hex(random_bytes(8)),
            'gateway' => 'myfatoorah',
            'status' => 'completed',
            'mode' => 'sandbox',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
                'card_last4' => $cardLast4,
            ],
        ];
    }

    public function verifyWebhookSignature(string $rawBody, array $headers): bool
    {
        if (empty($this->webhookSecret)) {
            return app()->environment('local', 'testing');
        }

        $receivedSig = null;
        foreach ($headers as $key => $val) {
            if (strtolower($key) === 'myfatoorah-signature') {
                $receivedSig = is_array($val) ? ($val[0] ?? null) : (string) $val;
                break;
            }
        }

        if (!$receivedSig) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $rawBody, $this->webhookSecret, true));
        return hash_equals($expected, $receivedSig);
    }

    public function handleWebhook(array $payload): array
    {
        Log::info('MyFatoorah webhook received', ['event' => $payload['Event'] ?? 'unknown']);

        $data = $payload['Data'] ?? [];
        $isSuccess = ($payload['Event'] ?? '') === 'TransactionsStatusChanged'
            && strtoupper((string) ($data['TransactionStatus'] ?? '')) === 'SUCCESS';

        return [
            'status' => 'processed',
            'gateway' => 'myfatoorah',
            'success' => $isSuccess,
            'order_id' => $data['CustomerReference'] ?? null,
            'transaction_id' => (string) ($data['InvoiceId'] ?? ''),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        Log::info('Processing MyFatoorah refund', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        if ($this->isLiveConfigured() && !str_starts_with($transactionId, 'mf_sandbox_')) {
            $response = Http::timeout(15)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/v2/MakeRefund", [
                    'KeyType' => 'InvoiceId',
                    'Key' => $transactionId,
                    'RefundChargeOnCustomer' => false,
                    'ServiceChargeOnCustomer' => false,
                    'Amount' => $amount,
                    'Comment' => 'Order Refund',
                ]);

            return [
                'success' => (bool) $response->json('IsSuccess'),
                'refund_id' => (string) ($response->json('Data.RefundReference') ?? ''),
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => $response->json('IsSuccess') ? 'refunded' : 'failed',
            ];
        }

        return [
            'success' => true,
            'refund_id' => 'ref_mf_sandbox_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
            'mode' => 'sandbox',
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

    private function isLiveConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
