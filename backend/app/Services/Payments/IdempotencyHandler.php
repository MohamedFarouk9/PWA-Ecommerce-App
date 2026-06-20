<?php

namespace App\Services\Payments;

use App\DTOs\PaymentResultDTO;
use App\Models\IdempotentTransaction;
use Illuminate\Support\Facades\Hash;


class IdempotencyHandler
{
    /**
     * Generate idempotency key from data
     */
    public static function generate(array $data): string
    {
        // use order_id + gatway + timestamp to create unique key
        $key = "{$data['order_id']}-{$data['gateway']}-" . now()->timestamp;
        return hash('sha256', $key);
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
                $record->transaction_id,
                $record->gatway,
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
     * Hash request data for integrity check
     */
    private function hashRequest(array $data): string
    {
        $normalized = [
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'order_id' => $data['order_id'] ?? '',
            'customer_id' => $data['customer_id'] ?? '',
        ];

        return Hash::make(json_encode($normalized));
    }

    /**
     * Validate request hasn't been tampered with
     */

    public function validateRequestIntegrity(string $idempotencyKey, array $requestData): bool
    {
        $record = IdempotentTransaction::byKey($idempotencyKey)->first();

        if (!$record) {
            // No record found - treat as new request
            return true;
        }

        $newHash = $this->hashRequest($requestData);

        // Compare - if different, request was modified (suspicious!)
        return Hash::check(json_encode($requestData), $record->request_hash);
    }
}
