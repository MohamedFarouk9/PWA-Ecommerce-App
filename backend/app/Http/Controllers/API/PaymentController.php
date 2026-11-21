<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Payments\ChargePaymentRequest;
use App\Http\Requests\Api\Payments\RefundPaymentRequest;
use App\Models\IdempotentTransaction;
use App\Models\Order;
use App\Services\Payments\OrderSettlementService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderSettlementService $settlementService,
    ) {}

    /**
     * Process an idempotent payment charge for an authenticated user's order.
     */
    public function charge(ChargePaymentRequest $request): JsonResponse
    {
        $order = $this->findOrder($request->validated('order_id'));

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ((int) $order->user_id !== (int) auth('api')->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized order payment attempt.'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Order has already been paid and settled.',
                'order_id' => $order->id,
                'status' => 'paid',
            ]);
        }

        $baseChargeData = [
            'amount' => (float) $order->total_amount,
            'currency' => 'USD',
            'gateway' => $request->validated('gateway'),
            'order_id' => (string) $order->id,
            'customer_id' => (string) $order->user_id,
        ];

        $idempotencyKey = $request->validated('idempotency_key')
            ?? $this->paymentService->generateIdempotencyKey($baseChargeData);

        $chargeData = array_merge($baseChargeData, [
            'metadata' => array_merge($request->safeMetadata(), [
                'order_number' => $order->order_number,
                'idempotency_key' => $idempotencyKey,
            ]),
            'retries' => $request->validated('retries') ?? 3,
        ]);

        $result = $this->paymentService->processPayment($chargeData, $idempotencyKey);

        // Settle immediately ONLY when confirmed completed (never on pending redirect / 3DS)
        if ($result->success && $result->status === 'completed') {
            $this->settlementService->settle($order, $result->gateway);
        }

        $freshOrder = $order->fresh();

        return response()->json([
            'success' => $result->success,
            'transaction_id' => $result->transactionId,
            'gateway' => $result->gateway,
            'status' => $result->status,
            'mode' => $result->metadata['mode'] ?? 'sandbox',
            'payment_url' => $result->metadata['payment_url'] ?? null,
            'client_secret' => $result->metadata['client_secret'] ?? null,
            'error' => $result->errorMessage,
            'idempotency_key' => $idempotencyKey,
            'order' => [
                'id' => $freshOrder->id,
                'order_number' => $freshOrder->order_number,
                'total_amount' => $freshOrder->total_amount,
                'payment_status' => $freshOrder->payment_status,
                'status' => $freshOrder->status,
            ],
        ], $result->success ? 200 : 400);
    }

    /**
     * Admin-only refund endpoint with refund ceiling validation.
     */
    public function refund(RefundPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $txRecord = IdempotentTransaction::where('transaction_id', $validated['transaction_id'])->first();
        $orderId = $validated['order_id'] ?? $txRecord?->order_id;
        $order = $orderId ? Order::find($orderId) : null;

        if ($order) {
            if ($order->payment_status === 'refunded') {
                return response()->json(['success' => false, 'message' => 'This order has already been refunded.'], 422);
            }
            if ((float) $validated['amount'] > (float) $order->total_amount) {
                return response()->json(['success' => false, 'message' => 'Refund amount cannot exceed the original order total.'], 422);
            }
        }

        $result = $this->paymentService->refund(
            $validated['gateway'],
            $validated['transaction_id'],
            (float) $validated['amount']
        );

        if (!empty($result['success']) && $order) {
            $this->settlementService->markRefunded($order);
        }

        return response()->json($result, !empty($result['success']) ? 200 : 400);
    }

    /**
     * Cryptographically verified webhook endpoint for Stripe, Paymob, and MyFatoorah.
     */
    public function webhook(Request $request): JsonResponse
    {
        $gateway = strtolower((string) $request->query('gateway', 'stripe'));
        if (!in_array($gateway, ['stripe', 'paymob', 'myfatoorah'], true)) {
            return response()->json(['error' => 'Unsupported gateway.'], 400);
        }

        if (!$this->paymentService->verifyWebhookSignature($gateway, $request->getContent(), $request->headers->all())) {
            Log::warning('Rejected payment webhook with invalid signature', [
                'gateway' => $gateway,
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $result = $this->paymentService->handleWebhook($gateway, $request->all());

        if (!empty($result['success']) && !empty($result['order_id'])) {
            $order = Order::find($result['order_id']);
            if ($order && $order->payment_status !== 'paid') {
                $this->settlementService->settle($order, $gateway);
            }
        }

        return response()->json($result);
    }

    private function findOrder(string|int $orderIdentifier): ?Order
    {
        return Order::query()
            ->when(
                is_numeric($orderIdentifier),
                fn ($q) => $q->where('id', $orderIdentifier)->orWhere('order_number', $orderIdentifier),
                fn ($q) => $q->where('order_number', $orderIdentifier)
            )
            ->first();
    }
}
