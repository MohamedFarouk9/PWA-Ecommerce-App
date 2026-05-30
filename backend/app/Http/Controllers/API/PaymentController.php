<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentRepositoryInterface $paymentRepository) {}

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'gateway' => 'required|string',
        ]);

        $result = $this->paymentRepository->charge($validated);
        return response()->json($result);
    }

    public function handleWebhook(Request $request)
    {
        return $this->paymentRepository->webhook($request->all());
    }
}
