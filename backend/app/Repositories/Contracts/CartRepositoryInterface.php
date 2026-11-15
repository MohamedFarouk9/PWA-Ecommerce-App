<?php

namespace App\Repositories\Contracts;

interface CartRepositoryInterface
{
    public function forUserOrSession(?int $userId, ?string $sessionId);
    public function getItems(?int $userId, ?string $sessionId);
    public function addItem(array $data);
    public function updateItem(int $id, array $data, ?int $userId = null, ?string $sessionId = null);
    public function removeItem(int $id, ?int $userId = null, ?string $sessionId = null);
    public function clear(?int $userId, ?string $sessionId);
    public function find(int $id);
    public function findOwned(int $id, ?int $userId, ?string $sessionId);
}
