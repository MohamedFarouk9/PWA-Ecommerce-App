<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Requests\UpdatePasswordRequest;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;

class AdminProfileController extends Controller
{
    public function __construct(private AdminUserRepositoryInterface $userRepository) {}

    public function AdminProfile()
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return view('admin.profile.profile')->with('user', $user);
    }

    public function updateAdminProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data    = $request->validated();
        $updated = $this->userRepository->updateProfile($user->id, $data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $updated,
        ], 200);
    }

    public function changePassword()
    {
        $user = auth()->user();
        return view('admin.profile.change_password', compact('user'));
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        if (!$user || !$this->userRepository->isAdmin($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        if (!$this->userRepository->verifyPassword($user->id, $validated['current_password'])) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $this->userRepository->updatePassword($user->id, $validated['password']);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
