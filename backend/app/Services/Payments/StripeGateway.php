<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    private string $secret;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secret = (string) (config('services.stripe.secret') ?? '');
        $this->webhookSecret = (string) (config('services.stripe.webhook_secret') ?? '');
    }

    public function charge(array $data): array
    {
        Log::info('Processing Stripe payment charge', [
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'usd',
            'mode' => $this->isLiveConfigured() ? 'live_api' : 'sandbox',
        ]);

        // Live/Test Stripe API Mode when STRIPE_SECRET_KEY is configured
        if ($this->isLiveConfigured()) {
            return $this->chargeViaStripeApi($data);
        }

        // Resilient Interactive Sandbox Mode (no Stripe key in .env yet)
        return $this->chargeSandbox($data);
    }

    private function chargeViaStripeApi(array $data): array
    {
        $amountCents = (int) round(((float) $data['amount']) * 100);
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

        $payload = [
            'amount' => $amountCents,
            'currency' => strtolower($data['currency'] ?? 'usd'),
            'automatic_payment_methods[enabled]' => 'true',
            'metadata[order_id]' => (string) ($data['order_id'] ?? ''),
            'metadata[order_number]' => (string) ($data['metadata']['order_number'] ?? ''),
            'metadata[customer_id]' => (string) ($data['customer_id'] ?? ''),
        ];

        if (!empty($data['metadata']['customer_email'])) {
            $payload['receipt_email'] = $data['metadata']['customer_email'];
        }

        // Support tokenized PaymentMethod from Stripe Elements or test token (e.g. pm_card_visa)
        if (!empty($data['metadata']['payment_method_id'])) {
            $payload['payment_method'] = $data['metadata']['payment_method_id'];
            $payload['confirm'] = 'true';
            $payload['return_url'] = "{$frontendUrl}/orders?payment=stripe_return";
        }

        $idempotencyKey = $data['metadata']['idempotency_key'] ?? ('st_' . uniqid('', true));

        $response = Http::withBasicAuth($this->secret, '')
            ->asForm()
            ->timeout(15)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post("{$this->baseUrl}/payment_intents", $payload);

        if (!$response->successful()) {
            $errorType = $response->json('error.type');
            $isTransient = $response->serverError() || $response->status() === 429 || $errorType === 'api_connection_error';

            return [
                'success' => false,
                'retryable' => $isTransient,
                'gateway' => 'stripe',
                'status' => 'failed',
                'mode' => 'live_api',
                'error' => $response->json('error.message', 'Stripe payment failed.'),
            ];
        }

        $intent = $response->json();
        $isCompleted = ($intent['status'] ?? '') === 'succeeded';

        return [
            'success' => in_array($intent['status'] ?? '', ['succeeded', 'requires_action', 'requires_payment_method', 'processing'], true),
            'transaction_id' => (string) ($intent['id'] ?? ''),
            'client_secret' => $intent['client_secret'] ?? null,
            'gateway' => 'stripe',
            'status' => $isCompleted ? 'completed' : 'pending',
            'mode' => 'live_api',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => strtolower($data['currency'] ?? 'usd'),
                'stripe_status' => $intent['status'] ?? null,
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
                'gateway' => 'stripe',
                'status' => 'failed',
                'mode' => 'sandbox',
                'error' => 'Your card was declined (insufficient funds). Try test card 4242 4242 4242 4242.',
            ];
        }

        if ($cardLast4 === '0002' || $scenario === 'expired') {
            return [
                'success' => false,
                'retryable' => false,
                'gateway' => 'stripe',
                'status' => 'failed',
                'mode' => 'sandbox',
                'error' => 'Your card has expired. Please use a valid future expiry date.',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'pi_sandbox_' . bin2hex(random_bytes(10)),
            'gateway' => 'stripe',
            'status' => 'completed',
            'mode' => 'sandbox',
            'metadata' => [
                'order_id' => $data['order_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'currency' => strtolower($data['currency'] ?? 'usd'),
                'card_last4' => $cardLast4,
                'card_brand' => $data['metadata']['card_brand'] ?? 'visa',
            ],
        ];
    }

    public function verifyWebhookSignature(string $rawBody, array $headers): bool
    {
        if (empty($this->webhookSecret)) {
            return app()->environment('local', 'testing');
        }

        $sigHeader = $this->getHeader($headers, 'stripe-signature');
        if (!$sigHeader) {
            return false;
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $sigHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            if ($kv[0] === 't') {
                $timestamp = (int) $kv[1];
            } elseif ($kv[0] === 'v1') {
                $signatures[] = $kv[1];
            }
        }

        // Reject missing or replayed webhooks older than 5 minutes
        if (!$timestamp || empty($signatures) || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $this->webhookSecret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }

        return false;
    }

    public function handleWebhook(array $payload): array
    {
        Log::info('Stripe webhook received', ['type' => $payload['type'] ?? 'unknown']);

        $eventType = $payload['type'] ?? '';
        $isSuccess = in_array($eventType, ['payment_intent.succeeded', 'charge.succeeded'], true);

        return [
            'status' => 'processed',
            'gateway' => 'stripe',
            'event' => $eventType,
            'success' => $isSuccess,
            'order_id' => $payload['data']['object']['metadata']['order_id'] ?? null,
            'transaction_id' => $payload['data']['object']['id'] ?? null,
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        Log::info('Processing Stripe refund', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        if ($this->isLiveConfigured() && !str_starts_with($transactionId, 'pi_sandbox_')) {
            $paramKey = str_starts_with($transactionId, 'pi_') ? 'payment_intent' : 'charge';
            $response = Http::withBasicAuth($this->secret, '')
                ->asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/refunds", [
                    $paramKey => $transactionId,
                    'amount' => (int) round($amount * 100),
                ]);

            return [
                'success' => $response->successful(),
                'refund_id' => $response->json('id'),
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => $response->successful() ? 'refunded' : 'failed',
                'error' => $response->json('error.message'),
            ];
        }

        return [
            'success' => true,
            'refund_id' => 're_sandbox_' . bin2hex(random_bytes(10)),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
            'mode' => 'sandbox',
        ];
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function validateData(array $data): bool
    {
        return isset($data['amount'], $data['currency']) && (float) $data['amount'] > 0;
    }

    private function isLiveConfigured(): bool
    {
        return !empty($this->secret) && str_starts_with($this->secret, 'sk_');
    }

    private function getHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $val) {
            if (strtolower($key) === strtolower($name)) {
                return is_array($val) ? ($val[0] ?? null) : (string) $val;
            }
        }
        return null;
    }
}
