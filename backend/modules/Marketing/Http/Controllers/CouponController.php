<?php

namespace Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Marketing\Actions\ToggleCouponStatusAction;
use Modules\Marketing\Http\Requests\Coupon\StoreCouponRequest;
use Modules\Marketing\Http\Requests\Coupon\UpdateCouponRequest;
use Modules\Marketing\Models\Coupon;
use Modules\Marketing\Models\CouponUsage;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::withCount('usages')->latest()->get();

        $stats = [
            'total'          => Coupon::count(),
            'active'         => Coupon::where('is_active', true)->count(),
            'total_used'     => (int) Coupon::sum('used_count'),
            'total_discount' => (float) CouponUsage::sum('discount_amount'),
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'success',
                'stats'   => $stats,
                'coupons' => $coupons,
            ]);
        }

        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(StoreCouponRequest $request)
    {
        $validated                   = $request->validated();
        $validated['code']           = strtoupper(trim($validated['code']));
        $validated['is_active']      = $request->has('is_active');
        $validated['min_spend']      = $validated['min_spend'] ?? 0;
        $validated['usage_per_user'] = $validated['usage_per_user'] ?? 1;

        $coupon = Coupon::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => "Coupon {$coupon->code} created successfully.",
                'redirect' => route('admin.coupons.index'),
                'coupon'   => $coupon,
            ], 201);
        }

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$coupon->code} created successfully.");
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon                      = Coupon::findOrFail($id);
        $validated                   = $request->validated();
        $validated['code']           = strtoupper(trim($validated['code']));
        $validated['is_active']      = $request->has('is_active');
        $validated['min_spend']      = $validated['min_spend'] ?? 0;
        $validated['usage_per_user'] = $validated['usage_per_user'] ?? 1;

        $coupon->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => "Coupon {$coupon->code} updated successfully.",
                'redirect' => route('admin.coupons.index'),
                'coupon'   => $coupon,
            ]);
        }

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$coupon->code} updated successfully.");
    }

    public function toggleStatus(Request $request, $id, ToggleCouponStatusAction $action)
    {
        $coupon = $action->execute((int) $id);
        $state  = $coupon->is_active ? 'activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Coupon {$state}."]);
        }

        return back()->with('success', "Coupon {$coupon->code} {$state}.");
    }

    public function destroy(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Coupon deleted.']);
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }
}
