<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new user
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $password): bool
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }

    /**
     * Store password reset token
     */
    public function storeResetToken(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => hash_hmac('sha256', $token, config('app.key')),
                'created_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Check if email is valid for password reset
     */
    public function isValidEmail(string $email): bool
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->exists();
    }

    /**
     * Check if token is valid
     */
    public function isValidToken(string $email, string $token): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        return hash_equals($record->token, hash_hmac('sha256', $token, config('app.key')));
    }

    /**
     * Delete password reset token
     */
    public function deleteResetToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    /**
     * Reset user password
     */
    public function resetPassword(int $userId, string $password): void
    {
        $user = $this->model->findOrFail($userId);
        $user->update(['password' => Hash::make($password)]);
    }
}
