<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Customers\Actions\ToggleCustomerStatusAction;
use Modules\Customers\Http\Requests\UpdateUserRequest;
use Modules\Customers\Services\CustomerStatsService;

class UserController extends Controller
{
    public function __construct(private CustomerStatsService $statsService) {}

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
        $stats = $this->statsService->getOverviewStats();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $users]);
        }

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show($id, Request $request)
    {
        $user = User::with([
            'orders'  => fn ($q) => $q->withCount('items')->latest()->limit(20),
            'reviews' => fn ($q) => $q->with('product:id,name')->latest()->limit(10),
        ])->findOrFail($id);

        $orders  = $user->orders;
        $reviews = $user->reviews;
        $metrics = $this->statsService->getCustomerProfileMetrics($user);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $user]);
        }

        return view('admin.users.show', array_merge(compact('user', 'orders', 'reviews'), $metrics));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user      = User::findOrFail($id);
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Customer updated successfully.',
                'redirect' => route('admin.users.index'),
                'data'     => $user,
            ]);
        }

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Customer updated successfully.');
    }

    public function toggleStatus(Request $request, $id, ToggleCustomerStatusAction $action)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $newStatus = $action->execute($user);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Customer status updated to {$newStatus}.",
                'data'    => $user,
            ]);
        }

        return back()->with('success', "Customer {$user->name} is now {$newStatus}.");
    }

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
