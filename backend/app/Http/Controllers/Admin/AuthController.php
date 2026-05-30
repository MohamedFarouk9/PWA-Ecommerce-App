<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\RegisterRequest;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AdminUserRepositoryInterface $userRepository) {}

    /**
     * Show the admin login page
     */
    public function loginPage()
    {
        return view('admin.auth.login');
    }

    /**
     * Admin Login
     */
    public function loginAdmin(LoginRequest $request)
    {
        $user = $this->userRepository->findByEmail($request->email);

        if (!$user || !$this->userRepository->verifyPassword($user->id, $request->password)) {
            return redirect()->back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        if (!$this->userRepository->isAdmin($user->id)) {
            return redirect()->back()->withErrors(['email' => 'Not an admin account'])->withInput();
        }

        Auth::login($user);
        return redirect()->route('admin.dashboard');
    }

    /**
     * Admin Registration
     */
    public function registerAdmin(RegisterRequest $request)
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $data['role'] = 'admin';

        $newAdmin = $this->userRepository->create($data);

        return response()->json([
            'message' => 'Admin registered successfully',
            'user' => $newAdmin,
        ], 201);
    }

    /**
     * Admin Profile Update
     */
    public function updateAdminProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->only(['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code']);
        $updated = $this->userRepository->updateProfile($user->id, $data);

        return response()->json(['message' => 'Profile updated', 'user' => $updated], 200);
    }

    /**
     * Admin Change Password
     */
    public function changeAdminPassword(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!$this->userRepository->verifyPassword($user->id, $request->current_password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $this->userRepository->updatePassword($user->id, $request->password);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
