<?php

namespace Modules\Customers\Services;

use App\Models\User;
use Carbon\Carbon;
use Modules\Sales\Models\Order;

class CustomerStatsService
{
    public function getOverviewStats(): array
    {
        return [
            'total'          => User::where('role', 'user')->count(),
            'active'         => User::where('role', 'user')->has('orders')->count(),
            'new_this_month' => User::where('role', 'user')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
            'total_revenue'  => (float) Order::where('payment_status', 'paid')->sum('total_amount'),
        ];
    }

    public function getCustomerProfileMetrics(User $user): array
    {
        return [
            'orderCount'      => $user->orders()->count(),
            'reviewCount'     => $user->reviews()->count(),
            'totalSpent'      => (float) $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'completedOrders' => $user->orders()->whereIn('status', ['delivered', 'completed'])->count(),
            'pendingOrders'   => $user->orders()->where('status', 'pending')->count(),
        ];
    }
}
