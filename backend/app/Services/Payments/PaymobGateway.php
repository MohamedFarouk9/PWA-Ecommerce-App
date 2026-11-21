<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $secretKey;
    private string $publicKey;
    private int $integrationId;
    private string $hmacSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.paymob.base_url', 'https://accept.paymob.com'), '/');
        $this->secretKey = (string) (config('services.paymob.secret_key') ?? '');
        $this->publicKey = (string) (config('services.paymob.public_key') ?? '');
        $this->integrationId = (int) config('services.paymob.integration_id', 0);
        $this->hmacSecret = (string) (config('services.paymob.hmac_secret') ?? '');
    }

    public function charge(array $data): array
    {
        Log::info('Processing Paymob payment charge', [
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'mode' => $this->isLiveConfigured() ? 'live_api' : 'sandbox',
        ]);

        if ($this->isLiveConfigured()) {
            return $this->chargeViaPaymobIntention($data);
        }

        return $this->chargeSandbox($data);
    }

    private function chargeViaPaymobIntention(array $data): array
    {
        $amountCents = (int) round(((float) $data['amount']) * 100);
        $billing = $data['metadata']['billing'] ?? [];
        $fullName = trim((string) ($billing['full_name'] ?? 'Customer User'));
        $nameParts = explode(' ', $fullName, 2);
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

        $response = Http::timeout(15)->withHeaders([
            'Authorization' => "Token {$this->secretKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/intention/", [
            'amount' => $amountCents,
            'currency' => strtoupper($data['currency'] ?? 'EGP'),
            'payment_methods' => [$this->integrationId],
            'billing_data' => [
                'first_name' => $nameParts[0] ?: 'Customer',
                'last_name' => $nameParts[1] ?? 'User',
                'email' => $data['metadata']['customer_email'] ?? 'customer@example.com',
                'phone_number' => $billing['phone'] ?? '+201000000000',
                'country' => 'EG',
                'city' => 'Cairo',
                'street' => $billing['address'] ?? 'N/A',
            ],
            'special_reference' => (string) ($data['order_id'] ?? uniqid()),
            'redirection_url' => "{$frontendUrl}/orders?payment=paymob_return",
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'retryable' => $response->serverError() || $response->status() === 429,
                'gateway' => 'paymob',
                'status' => 'failed',
                'mode' => 'live_api',
                'error' => $response->json('detail', 'Paymob intention creation failed.'),
            ];
        }

        $clientSecret = $response->json('client_secret');
        $checkoutUrl = "{$this->baseUrl}/unifiedcheckout/?publicKey={$this->publicKey}&clientSecret={$clientSecret}";

        return [
            'success' => true,
            'transaction_id' => (string) ($response->json('id') ?? uniqid('paymob_')),
            'payment_url' => $checkoutUrl,
            'gateway' => 'paymob',
            'status' => 'pending',
            'mode' => 'live_api',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => strtoupper($data['currency'] ?? 'EGP'),
                'payment_url' => $checkoutUrl,
            ],
        ];
    }

    private function chargeSandbox(array $data): array
    {
        $cardLast4 = (string) ($data['metadata']['card_last4'] ?? '8769');
        $scenario = strtolower((string) ($data['metadata']['test_card_scenario'] ?? ''));

        if ($cardLast4 === '9995' || $scenario === 'decline') {
            return [
                'success' => false,
                'retryable' => false,
                'gateway' => 'paymob',
                'status' => 'failed',
                'mode' => 'sandbox',
                'error' => 'Paymob transaction declined by issuing bank.',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'paymob_sandbox_' . bin2hex(random_bytes(8)),
            'gateway' => 'paymob',
            'status' => 'completed',
            'mode' => 'sandbox',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'EGP',
                'card_last4' => $cardLast4,
            ],
        ];
    }

    public function verifyWebhookSignature(string $rawBody, array $headers): bool
    {
        if (empty($this->hmacSecret)) {
            return app()->environment('local', 'testing');
        }

        $payload = json_decode($rawBody, true) ?? [];
        $obj = $payload['obj'] ?? [];
        $receivedHmac = $payload['hmac'] ?? ($headers['x-paymob-hmac'][0] ?? null);

        if (!$receivedHmac || empty($obj)) {
            return false;
        }

        // Paymob canonical HMAC concatenation order
        $fields = [
            $obj['amount_cents'] ?? '',
            $obj['created_at'] ?? '',
            $obj['currency'] ?? '',
            ($obj['error_occured'] ?? false) ? 'true' : 'false',
            ($obj['has_parent_transaction'] ?? false) ? 'true' : 'false',
            $obj['id'] ?? '',
            $obj['integration_id'] ?? '',
            ($obj['is_3d_secure'] ?? false) ? 'true' : 'false',
            ($obj['is_auth'] ?? false) ? 'true' : 'false',
            ($obj['is_capture'] ?? false) ? 'true' : 'false',
            ($obj['is_refunded'] ?? false) ? 'true' : 'false',
            ($obj['is_standalone_payment'] ?? false) ? 'true' : 'false',
            ($obj['is_voided'] ?? false) ? 'true' : 'false',
            $obj['order']['id'] ?? '',
            $obj['owner'] ?? '',
            ($obj['pending'] ?? false) ? 'true' : 'false',
            $obj['source_data']['pan'] ?? '',
            $obj['source_data']['sub_type'] ?? '',
            $obj['source_data']['type'] ?? '',
            ($obj['success'] ?? false) ? 'true' : 'false',
        ];

        $calculated = hash_hmac('sha512', implode('', $fields), $this->hmacSecret);
        return hash_equals($calculated, (string) $receivedHmac);
    }

    public function handleWebhook(array $payload): array
    {
        Log::info('Paymob webhook received', ['order_id' => $payload['obj']['order']['merchant_order_id'] ?? null]);

        $success = ($payload['success'] ?? false) || (($payload['obj']['success'] ?? false) === true);

        return [
            'status' => 'processed',
            'gateway' => 'paymob',
            'success' => $success,
            'order_id' => $payload['obj']['order']['merchant_order_id'] ?? ($payload['order_id'] ?? null),
            'transaction_id' => (string) ($payload['obj']['id'] ?? ($payload['id'] ?? '')),
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        Log::info('Processing Paymob refund', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        if ($this->isLiveConfigured() && !str_starts_with($transactionId, 'paymob_sandbox_')) {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => "Token {$this->secretKey}",
            ])->post("{$this->baseUrl}/api/acceptance/void_refund/refund", [
                'transaction_id' => $transactionId,
                'amount_cents' => (int) round($amount * 100),
            ]);

            return [
                'success' => $response->successful(),
                'refund_id' => (string) ($response->json('id') ?? ''),
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => $response->successful() ? 'refunded' : 'failed',
            ];
        }

        return [
            'success' => true,
            'refund_id' => 'ref_paymob_sandbox_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
            'mode' => 'sandbox',
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

    private function isLiveConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->publicKey) && $this->integrationId > 0;
    }
}
