<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct(private AdminUserRepositoryInterface $userRepository) {}

    /**
     * Admin profile
     */
    public function AdminProfile()
    {
        $user = auth()->user();
        
        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return view('admin.profile.profile')->with('user', $user);
    }

    /**
     * Admin Profile Update
     */
    public function updateAdminProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        
        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $updated = $this->userRepository->updateProfile($user->id, $data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $updated
        ], 200);
    }

    /**
     * Show change password form
     */
    public function changePassword()
    {
        $user = auth()->user();
        return view('admin.profile.change_password', compact('user'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
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

    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
