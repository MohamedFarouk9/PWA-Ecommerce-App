<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'gateway' => 'required|string|in:stripe,paymob,myfatoorah',
            'order_id' => 'required|string',
            'customer_id' => 'required|string',
            'metadata' => 'nullable|array',
            'retries' => 'nullable|integer|min:1|max:5',
        ]);

        $result = $this->paymentService->processPayment($validated);

        return response()->json([
            'success' => $result->success,
            'transaction_id' => $result->transactionId,
            'gateway' => $result->gateway,
            'status' => $result->status,
            'error' => $result->errorMessage,
            'metadata' => $result->metadata,
        ], $result->success ? 200 : 400);
    }

    public function refund(Request $request)
    {
        $validated = $request->validate([
            'gateway' => 'required|string',
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
