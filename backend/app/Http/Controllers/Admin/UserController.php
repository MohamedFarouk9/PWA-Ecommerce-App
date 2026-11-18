<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with stats
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->get();

        // Dashboard stats
        $stats = [
            'total'          => User::where('role', 'user')->count(),
            'active'         => User::where('role', 'user')->has('orders')->count(),
            'new_this_month' => User::where('role', 'user')
                                    ->where('created_at', '>=', Carbon::now()->startOfMonth())
                                    ->count(),
            'total_revenue'  => (float) Order::where('payment_status', 'paid')->sum('total_amount'),
        ];

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $users]);
        }

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display the specified user's full profile
     */
    public function show($id, Request $request)
    {
        $user = User::with([
            'orders' => fn($q) => $q->withCount('items')->latest()->limit(20),
            'reviews' => fn($q) => $q->with('product:id,name')->latest()->limit(10),
        ])->findOrFail($id);

        $orders     = $user->orders;
        $reviews    = $user->reviews;
        $orderCount = $user->orders()->count();
        $reviewCount = $user->reviews()->count();
        $totalSpent = (float) $user->orders()->where('payment_status', 'paid')->sum('total_amount');
        $completedOrders = $user->orders()->whereIn('status', ['delivered', 'completed'])->count();
        $pendingOrders   = $user->orders()->where('status', 'pending')->count();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $user]);
        }

        return view('admin.users.show', compact(
            'user', 'orders', 'reviews',
            'orderCount', 'reviewCount',
            'totalSpent', 'completedOrders', 'pendingOrders'
        ));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user details
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'        => 'sometimes|string|in:admin,user',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'password'    => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Customer updated', 'data' => $user]);
        }

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'You cannot delete your own account'], 403);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Customer deleted']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Customer deleted successfully.');
    }
}
