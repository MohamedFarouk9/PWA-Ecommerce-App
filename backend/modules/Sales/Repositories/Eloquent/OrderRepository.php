<?php

namespace Modules\Sales\Repositories\Eloquent;

use Modules\Sales\Models\Order;
use Modules\Sales\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function getPaginated($perPage = 15)
    {
        return Order::with('user', 'products')
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getAll()
    {
        return Order::with('user', 'products')
            ->latest('created_at')
            ->get();
    }

    public function find($id)
    {
        return Order::find($id);
    }

    public function getWithDetails($id)
    {
        return Order::with(['user', 'products'])
            ->findOrFail($id);
    }

    public function getByStatus($status, $perPage = 15)
    {
        return Order::where('status', $status)
            ->with('user', 'products')
            ->latest('created_at')
            ->get();
    }

    public function getByUser($userId, $perPage = 15)
    {
        return Order::where('user_id', $userId)
            ->with('products')
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function updateStatus($id, $status)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        return $order;
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }

    public function getItems($id)
    {
        return Order::findOrFail($id)->products;
    }

    public function getPendingCount()
    {
        return Order::whereIn('status', ['pending', 'processing'])->count();
    }

    public function getStatistics()
    {
        return [
            'total_orders'        => Order::count(),
            'total_revenue'       => Order::where('status', 'delivered')->sum('total_amount'),
            'pending_orders'      => Order::whereIn('status', ['pending', 'processing'])->count(),
            'completed_orders'    => Order::where('status', 'delivered')->count(),
            'cancelled_orders'    => Order::where('status', 'cancelled')->count(),
            'average_order_value' => Order::avg('total_amount'),
        ];
    }

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
