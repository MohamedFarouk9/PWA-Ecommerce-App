<?php

namespace App\Repositories\Contracts;

interface AuthRepositoryInterface
{
    public function create(array $data);
    public function findByEmail(string $email);
    public function verifyCredentials(string $email, string $password): bool;
    public function storeResetToken(string $email, string $token): void;
    public function isValidEmail(string $email): bool;
    public function isValidToken(string $email, string $token): bool;
    public function deleteResetToken(string $email): void;
    public function resetPassword(int $userId, string $password): void;
}
