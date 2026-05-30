<?php

namespace App\Repositories\Contracts;

interface AdminUserRepositoryInterface
{
    public function getProfile(int $userId);
    public function updateProfile(int $userId, array $data);
    public function updatePassword(int $userId, string $newPassword);
    public function verifyPassword(int $userId, string $password): bool;
    public function findByEmail(string $email);
    public function create(array $data);
    public function isAdmin(int $userId): bool;
}
