<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Order;
use App\Repositories\Contracts\Admin\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get all orders with pagination
     *
     * @param int $perPage
     * @return mixed
     */
    public function getPaginated($perPage = 15)
    {
        return Order::with('user', 'products')
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all orders
     *
     * @return mixed
     */
    public function getAll()
    {
        return Order::with('user', 'products')
            ->latest('created_at')
            ->get();
    }

    /**
     * Find order by ID
     *
     * @param int $id
     * @return mixed
     */
    public function find($id)
    {
        return Order::find($id);
    }

    /**
     * Get order with related data
     *
     * @param int $id
     * @return mixed
     */
    public function getWithDetails($id)
    {
        return Order::with(['user', 'products'])
            ->findOrFail($id);
    }

    /**
     * Get orders by status
     *
     * @param string $status
     * @param int $perPage
     * @return mixed
     */
    public function getByStatus($status, $perPage = 15)
    {
        return Order::where('status', $status)
            ->with('user', 'products')
            ->latest('created_at')
            ->get();
    }

    /**
     * Get orders by user
     *
     * @param int $userId
     * @param int $perPage
     * @return mixed
     */
    public function getByUser($userId, $perPage = 15)
    {
        return Order::where('user_id', $userId)
            ->with('products')
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Update order status
     *
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updateStatus($id, $status)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        return $order;
    }

    /**
     * Update order
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }

    /**
     * Get order items
     *
     * @param int $id
     * @return mixed
     */
    public function getItems($id)
    {
        return Order::findOrFail($id)->products;
    }

    /**
     * Get pending orders count
     *
     * @return int
     */
    public function getPendingCount()
    {
        return Order::whereIn('status', ['pending', 'processing'])
            ->count();
    }

    /**
     * Get orders statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'delivered')
                ->sum('total_amount'),
            'pending_orders' => Order::whereIn('status', ['pending', 'processing'])
                ->count(),
            'completed_orders' => Order::where('status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')
                ->count(),
            'average_order_value' => Order::avg('total_amount'),
        ];
    }

    /**
     * Search orders
     *
     * @param string $query
     * @param int $perPage
     * @return mixed
     */
    public function search($query, $perPage = 15)
    {
        return Order::where('order_number', 'like', "%{$query}%")
            ->orWhere('notes', 'like', "%{$query}%")
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('user', 'products')
            ->latest('created_at')
            ->get();
    }
}
