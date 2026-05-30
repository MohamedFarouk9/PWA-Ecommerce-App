<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AdminUserRepository implements AdminUserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get user profile
     */
    public function getProfile(int $userId)
    {
        return $this->model->find($userId);
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data)
    {
        $user = $this->model->findOrFail($userId);
        $user->update($data);
        return $user;
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword)
    {
        $user = $this->model->findOrFail($userId);
        $user->update(['password' => Hash::make($newPassword)]);
        return $user;
    }

    /**
     * Verify user password
     */
    public function verifyPassword(int $userId, string $password): bool
    {
        $user = $this->model->find($userId);

        if (!$user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create new user
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(int $userId): bool
    {
        $user = $this->model->find($userId);
        return $user && $user->role === 'admin';
    }
}
