<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     */
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

    /**
     * Show form to create a new coupon
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store newly created coupon
     */
    public function store(StoreCouponRequest $request)
    {
        $validated = $request->validated();

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active');
        $validated['min_spend'] = $validated['min_spend'] ?? 0;
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

    /**
     * Show form to edit coupon
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update coupon details
     */
    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validated();

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active');
        $validated['min_spend'] = $validated['min_spend'] ?? 0;
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

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        $state = $coupon->is_active ? 'activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Coupon {$state}."]);
        }

        return back()->with('success', "Coupon {$coupon->code} {$state}.");
    }

    /**
     * Delete coupon
     */
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
