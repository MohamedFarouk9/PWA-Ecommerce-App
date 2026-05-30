<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\ForgetPasswordMail;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller {

    public function __construct(private AuthRepositoryInterface $authRepository) {}

    /**
     * User Registration
     */
    public function register(RegisterRequest $request) {
        $user = $this->authRepository->create($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }

    /**
     * User Login
     */
    public function login(LoginRequest $request) {
        if (!$this->authRepository->verifyCredentials($request->email, $request->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $this->authRepository->findByEmail($request->email);
        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'message' => 'Login successfull',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    /**
     * Send Password Reset Link
     */
    public function sendResetLinkEmail(Request $request) {
        $request->validate(['email' => 'required|email']);

        $user = $this->authRepository->findByEmail($request->email);
        if (!$user) {
            return response()->json(['message' => 'No user found with that email address.'], 404);
        }

        $token = Str::random(40);
        $this->authRepository->storeResetToken($request->email, $token);
        $this->sendPasswordResetEmail($request->email, $token);

        return response()->json(['message' => 'Password reset link has been sent to your email.'], 200);
    }

    private function sendPasswordResetEmail(string $email, string $token): void {
        try {
            Mail::to($email)->send(new ForgetPasswordMail($token, $email));
        } catch (Exception $exception) {
            Log::error('Failed to send password reset email', ['email' => $email, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * Reset Password
     */
    public function passwordReset(PasswordResetRequest $request) {
        if (!$this->authRepository->isValidEmail($request->email)) {
            return response()->json(['message' => 'No password reset request found for this email'], 400);
        }

        if (!$this->authRepository->isValidToken($request->email, $request->token)) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        $user = $this->authRepository->findByEmail($request->email);
        $this->authRepository->resetPassword($user->id, $request->password);
        $this->authRepository->deleteResetToken($request->email);

        Log::info('Password reset successful for user: ' . $user->email);

        return response()->json(['message' => 'Password reset successfully'], 200);
    }

    public function userProfile(Request $request) {
        return response()->json([
           'user' => auth()->guard('api')->user(),
        ], 200);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
