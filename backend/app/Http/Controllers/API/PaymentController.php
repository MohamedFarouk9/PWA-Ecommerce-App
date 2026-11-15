<?php

namespace App\Http\Controllers\API;

use App\Events\Notifications\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Process payment charge for an order.
     * Price and customer are authoritative from the DB to prevent price-tampering.
     */
    public function charge(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required',
            'gateway' => 'required|string|in:stripe,paymob,myfatoorah',
            'idempotency_key' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'retries' => 'nullable|integer|min:1|max:5',
        ]);

        // Find authoritative order record
        $orderQuery = Order::query();
        if (is_numeric($validated['order_id'])) {
            $orderQuery->where('id', $validated['order_id'])->orWhere('order_number', $validated['order_id']);
        } else {
            $orderQuery->where('order_number', $validated['order_id']);
        }
        $order = $orderQuery->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Ownership enforcement
        $currentUserId = auth('api')->check() ? auth('api')->id() : null;
        if ($currentUserId && (int) $order->user_id !== (int) $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized order payment attempt.',
            ], 403);
        }

        // Prevent duplicate charges on already settled orders
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Order has already been paid and settled.',
                'order_id' => $order->id,
                'status' => 'paid',
            ], 200);
        }

        // Authoritative payment payload derived strictly from verified database state
        $chargeData = [
            'amount' => (float) $order->total_amount,
            'currency' => 'USD',
            'gateway' => $validated['gateway'],
            'order_id' => (string) $order->id,
            'customer_id' => (string) $order->user_id,
            'metadata' => array_merge($validated['metadata'] ?? [], [
                'order_number' => $order->order_number,
            ]),
            'retries' => $validated['retries'] ?? 3,
        ];

        // Retrieve or generate deterministic idempotency key
        $idempotencyKey = $validated['idempotency_key']
            ?? $this->paymentService->generateIdempotencyKey($chargeData);

        // Process payment through resilient handler pipeline
        $result = $this->paymentService->processPayment($chargeData, $idempotencyKey);

        if ($result->success) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_method' => $result->gateway,
            ]);

            // Clear settled cart
            Cart::where('user_id', $order->user_id)->delete();

            // Fire event for RabbitMQ async notification workers
            try {
                event(new OrderCreated($order->load('user')));
            } catch (\Throwable $e) {
                \Log::warning('OrderCreated notification event dispatch failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => $result->success,
            'transaction_id' => $result->transactionId,
            'gateway' => $result->gateway,
            'status' => $result->status,
            'error' => $result->errorMessage,
            'idempotency_key' => $idempotencyKey,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ],
        ], $result->success ? 200 : 400);
    }

    public function refund(Request $request)
    {
        $validated = $request->validate([
            'gateway' => 'required|string|in:stripe,paymob,myfatoorah',
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $result = $this->paymentService->refund(
            $validated['gateway'],
            $validated['transaction_id'],
            $validated['amount']
        );

        return response()->json($result);
    }

    public function webhook(Request $request)
    {
        $gateway = $request->query('gateway', 'stripe');
        $result = $this->paymentService->handleWebhook($gateway, $request->all());
        return response()->json($result);
    }
}
