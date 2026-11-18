<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount('orders');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user
     */
    public function show($id, Request $request)
    {
        $user = User::with(['orders' => function ($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $user,
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user details
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|string|in:admin,user',
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'You cannot delete your own account'], 403);
            }
            return back()->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }
}
