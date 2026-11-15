<?php

namespace App\Services\Payments;

use App\DTOs\PaymentResultDTO;
use App\Models\IdempotentTransaction;
use Illuminate\Support\Facades\Hash;


class IdempotencyHandler
{
    /**
     * Generate deterministic idempotency key from order and customer data
     */
    public static function generate(array $data): string
    {
        $orderId = $data['order_id'] ?? '';
        $customerId = $data['customer_id'] ?? '';
        $gateway = $data['gateway'] ?? '';
        return hash('sha256', "order:{$orderId}:customer:{$customerId}:gw:{$gateway}");
    }

    /**
     * Check if request was already processed
     * Returns cached result if found
     */
    public function checkDuplicate(string $idempotencyKey): ?PaymentResultDTO
    {
        $record = IdempotentTransaction::byKey($idempotencyKey)->recent()->first();

        if (!$record) {
            return null; // No previous request found
        }

        // Found a previous request
        if ($record->status === 'completed') {
            return PaymentResultDTO::success(
                $record->transaction_id ?? '',
                $record->gateway,
                $record->response ?? []
            );
        }

        if ($record->status === 'failed') {
            return PaymentResultDTO::failed(
                $record->gateway,
                'This payment request previously failed. ' .
                    ($record->response['error'] ?? 'Unknown error')
            );
        }

        // Status is 'pending' - still processing
        return PaymentResultDTO::failed(
            $record->gateway,
            'This payment request is already being processed. Please wait.'
        );
    }


    /**
     * Store request to track idempotency
     */

    public function store(
        string $idempotencyKey,
        string $gateway,
        string $orderId,
        string $customerId,
        array $requestData,
        string $status = 'pending'
    ): IdempotentTransaction {
        return idempotentTransaction::create([
            'idempotency_key' => $idempotencyKey,
            'gateway' => $gateway,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'request_hash' => $this->hashRequest($requestData),
            'status' => $status,
            'processed_at' => $status === 'pending' ? null : now(),
        ]);
    }


    /**
     * Update with completion result
     */
    public function markCompleted(string $idempotencyKey, PaymentResultDTO $result): void
    {
        $record = IdempotentTransaction::byKey($idempotencyKey)->first();

        if (!$record) {
            // This should never happen - we should have a pending record
            throw new \Exception("Idempotent record not found for key: $idempotencyKey");
        }

        $record->update([
            'transaction_id' => $result->transactionId,
            'status' => $result->success ? 'completed' : 'failed',
            'response' => [
                'gateway' => $result->gateway,
                'status' => $result->status,
                'error_message' => $result->errorMessage,
                'metadata' => $result->metadata,
            ],
            'processed_at' => now(),
        ]);
    }

    /**
     * Hash request data for integrity check (deterministic sha256)
     */
    private function hashRequest(array $data): string
    {
        $normalized = [
            'amount' => (string) ($data['amount'] ?? 0),
            'currency' => strtoupper((string) ($data['currency'] ?? 'USD')),
            'order_id' => (string) ($data['order_id'] ?? ''),
            'customer_id' => (string) ($data['customer_id'] ?? ''),
        ];

        return hash('sha256', json_encode($normalized));
    }

    /**
     * Validate request hasn't been tampered with
     */
    public function validateRequestIntegrity(string $idempotencyKey, array $requestData): bool
    {
        $record = IdempotentTransaction::byKey($idempotencyKey)->first();

        if (!$record || empty($record->request_hash)) {
            return true;
        }

        $incomingHash = $this->hashRequest($requestData);
        return hash_equals($record->request_hash, $incomingHash);
    }
}
