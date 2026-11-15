<?php
namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    protected $model;

    public function __construct(Cart $model)
    {
        $this->model = $model;
    }

    /**
     * Get or query cart for user or session
     */
    public function forUserOrSession(?int $userId, ?string $sessionId)
    {
        $query = Cart::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query;
    }

    /**
     * Get all cart items for user or session
     */
    public function getItems(?int $userId, ?string $sessionId)
    {
        return $this->forUserOrSession($userId, $sessionId)->with('product')->get();
    }

    /**
     * Add item to cart, incrementing quantity if already present
     */
    public function addItem(array $data)
    {
        $userId = $data['user_id'] ?? null;
        $sessionId = $data['session_id'] ?? null;
        $productId = $data['product_id'] ?? null;

        // Merge existing item if present for this user/session
        $existing = $this->forUserOrSession($userId, $sessionId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity'] ?? 1);
            return $existing->fresh();
        }

        return $this->model->create($data);
    }

    /**
     * Find cart item strictly owned by user or session (prevents IDOR)
     */
    public function findOwned(int $id, ?int $userId, ?string $sessionId)
    {
        return $this->forUserOrSession($userId, $sessionId)
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Update cart item (quantity, etc) with ownership check
     */
    public function updateItem(int $id, array $data, ?int $userId = null, ?string $sessionId = null)
    {
        $item = ($userId || $sessionId) ? $this->findOwned($id, $userId, $sessionId) : $this->find($id);
        $item->update($data);
        return $item;
    }

    /**
     * Remove item from cart with ownership check
     */
    public function removeItem(int $id, ?int $userId = null, ?string $sessionId = null)
    {
        $item = ($userId || $sessionId) ? $this->findOwned($id, $userId, $sessionId) : $this->find($id);
        $item->delete();
        return true;
    }

    /**
     * Clear all cart items for user or session
     */
    public function clear(?int $userId, ?string $sessionId)
    {
        return $this->forUserOrSession($userId, $sessionId)->delete();
    }

    /**
     * Find single cart item by id
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get cart total price for user or session
     */
    public function getTotal(?int $userId, ?string $sessionId)
    {
        return $this->forUserOrSession($userId, $sessionId)
            ->with('product')
            ->get()
            ->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
    }
     /**
     * Get cart count for user or session
     */
    public function getCount(?int $userId, ?string $sessionId)
    {
        return $this->forUserOrSession($userId, $sessionId)->count();
    }

}
