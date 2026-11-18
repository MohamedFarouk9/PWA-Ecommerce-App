<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Validate and apply a coupon code
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal');
        $user = auth('api')->user();

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code. Please check and try again.',
            ], 404);
        }

        $result = $coupon->validateFor($user, $subtotal);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success'         => true,
            'message'         => $result['message'],
            'discount_amount' => $result['discount_amount'],
            'new_total'       => $result['new_total'],
            'coupon'          => $result['coupon'],
        ]);
    }
}
