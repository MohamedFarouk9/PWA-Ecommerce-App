<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Cart;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\Contracts\Admin\AdminDashboardRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminDashboardRepository implements AdminDashboardRepositoryInterface
{
    public function getDashboardStats(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $prevMonthStart = $lastMonth->copy()->subMonth();

        $totalProducts  = Product::count();
        $activeProducts = Product::whereHas('status', fn ($q) => $q->where('name', 'published'))->count();
        $totalOrders    = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $totalRevenue   = (float) Order::where('payment_status', 'paid')->sum('total_amount');
        $pendingOrders  = Order::where('status', 'pending')->count();
        $totalReviews   = ProductReview::count();
        $totalContacts  = Contact::count();

        // Month-over-month growth rates
        $ordersThisMonth = Order::where('created_at', '>=', $lastMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$prevMonthStart, $lastMonth])->count();
        $ordersChange    = $ordersLastMonth > 0 ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1) : 0;

        $revenueThisMonth = (float) Order::where('payment_status', 'paid')->where('created_at', '>=', $lastMonth)->sum('total_amount');
        $revenueLastMonth = (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$prevMonthStart, $lastMonth])->sum('total_amount');
        $revenueChange    = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0;

        $customersThisMonth = User::where('role', 'user')->where('created_at', '>=', $lastMonth)->count();
        $customersLastMonth = User::where('role', 'user')->whereBetween('created_at', [$prevMonthStart, $lastMonth])->count();
        $customersChange    = $customersLastMonth > 0 ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1) : 0;

        return [
            'total_products'        => $totalProducts,
            'active_products'       => $activeProducts,
            'total_orders'          => $totalOrders,
            'pending_orders'        => $pendingOrders,
            'total_customers'       => $totalCustomers,
            'total_revenue'         => $totalRevenue,
            'total_reviews'         => $totalReviews,
            'total_contacts'        => $totalContacts,
            'orders_change'         => abs($ordersChange),
            'orders_change_type'    => $ordersChange >= 0 ? 'increase' : 'decrease',
            'revenue_change'        => abs($revenueChange),
            'revenue_change_type'   => $revenueChange >= 0 ? 'increase' : 'decrease',
            'customers_change'      => abs($customersChange),
            'customers_change_type' => $customersChange >= 0 ? 'increase' : 'decrease',
        ];
    }

    public function getRevenueTimeline(int $days = 30): array
    {
        $labels = [];
        $data   = [];

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $dailyTotals = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i);
            $key      = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $data[]   = round((float) ($dailyTotals[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return Order::with('user:id,name,email')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return (object) [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'customer_name'  => $order->user->name ?? 'Guest User',
                    'customer_email' => $order->user->email ?? 'N/A',
                    'status'         => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? 'unpaid',
                    'total'          => (float) ($order->total_amount ?? 0),
                    'created_at'     => $order->created_at ? $order->created_at->format('d M Y') : 'N/A',
                ];
            });
    }

    public function getRecentReviews(int $limit = 5): Collection
    {
        return ProductReview::with(['product:id,name', 'user:id,name'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($review) {
                return (object) [
                    'rating'        => (int) ($review->rating ?? 5),
                    'comment'       => $review->comment ?? '',
                    'product_title' => $review->product->name ?? 'Product #' . $review->product_id,
                    'customer_name' => $review->user->name ?? 'Anonymous Customer',
                    'created_at'    => $review->created_at ? $review->created_at->diffForHumans() : 'Recently',
                ];
            });
    }

    public function getTopProducts(int $limit = 5): Collection
    {
        return Product::select('products.id', 'products.name', 'products.price', 'products.quantity as stock')
            ->selectRaw('COALESCE(COUNT(order_items.id), 0) as sales_count')
            ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as total_revenue')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.payment_status', '=', 'paid');
            })
            ->groupBy('products.id', 'products.name', 'products.price', 'products.quantity')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    public function getLowStockProducts(int $limit = 8): Collection
    {
        return Product::select('products.id', 'products.name', 'products.price', 'products.quantity', 'products.product_category_id')
            ->with(['category:id,name'])
            ->where('products.quantity', '<=', 10)
            ->where('products.quantity', '>', 0)
            ->orderBy('products.quantity', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getTrendingProducts(int $days = 7, int $limit = 6): Collection
    {
        return Product::select('products.id', 'products.name', 'products.price', 'products.quantity')
            ->selectRaw('COUNT(order_items.id) as units_sold')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as week_revenue')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', function ($join) use ($days) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.payment_status', '=', 'paid')
                    ->where('orders.created_at', '>=', Carbon::now()->subDays($days));
            })
            ->groupBy('products.id', 'products.name', 'products.price', 'products.quantity')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get();
    }

    public function getDeadStockProducts(int $days = 30, int $limit = 6): Collection
    {
        $recentlySold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', Carbon::now()->subDays($days))
            ->pluck('order_items.product_id');

        return Product::select('products.id', 'products.name', 'products.price', 'products.quantity', 'products.product_category_id')
            ->with('category:id,name')
            ->whereNotIn('products.id', $recentlySold)
            ->where('products.quantity', '>', 0)
            ->orderByDesc('products.quantity')
            ->limit($limit)
            ->get();
    }

    public function getCategoryRevenue(int $limit = 8): Collection
    {
        return DB::table('product_categories')
            ->select(
                'product_categories.name as category',
                DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as revenue'),
                DB::raw('COUNT(DISTINCT order_items.product_id) as product_count')
            )
            ->leftJoin('products', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.payment_status', '=', 'paid');
            })
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function getOrderFunnel(): array
    {
        $counts = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total     = max(1, (int) $counts->sum());
        $delivered = (int) ($counts['delivered'] ?? 0);
        $cancelled = (int) ($counts['cancelled'] ?? 0);

        return [
            'pending'          => (int) ($counts['pending'] ?? 0),
            'processing'       => (int) ($counts['processing'] ?? 0),
            'shipped'          => (int) ($counts['shipped'] ?? 0),
            'delivered'        => $delivered,
            'cancelled'        => $cancelled,
            'fulfillment_rate' => round(($delivered / $total) * 100, 1),
            'cancel_rate'      => round(($cancelled / $total) * 100, 1),
        ];
    }

    public function getPaymentMethodBreakdown(): Collection
    {
        return Order::where('payment_status', 'paid')
            ->select(
                DB::raw("COALESCE(NULLIF(payment_method, ''), 'other') as method"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('method')
            ->orderByDesc('revenue')
            ->get();
    }

    public function getVipCustomers(int $limit = 5): Collection
    {
        return User::select('users.id', 'users.name', 'users.email')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as lifetime_value')
            ->join('orders', function ($join) {
                $join->on('orders.user_id', '=', 'users.id')
                    ->where('orders.payment_status', '=', 'paid');
            })
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('lifetime_value')
            ->limit($limit)
            ->get();
    }

    public function getCustomerRetentionMetrics(): array
    {
        $buyers = Order::where('payment_status', 'paid')
            ->select('user_id', DB::raw('COUNT(*) as order_cnt'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get();

        $totalBuyers  = $buyers->count();
        $repeatBuyers = $buyers->where('order_cnt', '>=', 2)->count();

        return [
            'total_buyers'  => $totalBuyers,
            'repeat_buyers' => $repeatBuyers,
            'repeat_rate'   => $totalBuyers > 0 ? round(($repeatBuyers / $totalBuyers) * 100, 1) : 0,
        ];
    }

    public function getAbandonedCartStats(): array
    {
        $cartItems = Cart::with('product:id,name,price')->get();
        $recoverableRevenue = $cartItems->sum(
            fn ($c) => (float) ($c->price ?? $c->product?->price ?? 0) * (int) ($c->quantity ?? 1)
        );
        $uniqueCarts = $cartItems
            ->map(fn ($c) => $c->user_id ? 'u:' . $c->user_id : 's:' . $c->session_id)
            ->unique()
            ->count();

        $topAbandoned = $cartItems->groupBy('product_id')
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'product_name' => $first->product?->name ?? ('Product #' . $first->product_id),
                    'cart_count'   => $group->count(),
                    'total_value'  => $group->sum(
                        fn ($c) => (float) ($c->price ?? $c->product?->price ?? 0) * (int) ($c->quantity ?? 1)
                    ),
                ];
            })
            ->sortByDesc('total_value')
            ->take(3)
            ->values();

        return [
            'abandoned_carts'     => $uniqueCarts,
            'items_in_carts'      => (int) $cartItems->sum('quantity'),
            'recoverable_revenue' => round($recoverableRevenue, 2),
            'top_products'        => $topAbandoned,
        ];
    }

    public function getActiveCoupons(int $limit = 5): Collection
    {
        return Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('used_count')
            ->limit($limit)
            ->get();
    }

    public function getOrdersForExport(int $days = 30): Collection
    {
        return Order::with('user:id,name,email')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->latest()
            ->get();
    }

    public function createFlashSaleCoupon(array $validated): Coupon
    {
        $code = Str::upper(preg_replace('/[^A-Za-z0-9_-]/', '', $validated['code']));

        return Coupon::create([
            'code'            => $code,
            'description'     => "Flash Sale Campaign ({$validated['discount_amount']}% OFF) launched from Admin Dashboard",
            'discount_type'   => 'percentage',
            'discount_amount' => $validated['discount_amount'],
            'min_spend'       => $validated['min_spend'] ?? 0,
            'max_discount'    => 250,
            'usage_limit'     => $validated['usage_limit'] ?? 200,
            'usage_per_user'  => 1,
            'used_count'      => 0,
            'starts_at'       => now(),
            'expires_at'      => now()->addHours((int) $validated['duration_hours']),
            'is_active'       => true,
        ]);
    }

    public function globalSearch(string $query, int $limit = 10): array
    {
        $searchTerm = "%{$query}%";

        $products = Product::with('category:id,name')
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('brand', 'like', $searchTerm)
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->limit($limit)
            ->get();

        $orders = Order::with('user:id,name,email')
            ->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                    ->orWhere('coupon_code', 'like', $searchTerm)
                    ->orWhere('payment_method', 'like', $searchTerm)
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    });
            })
            ->latest()
            ->limit($limit)
            ->get();

        $customers = User::where('role', 'user')
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            })
            ->limit($limit)
            ->get();

        return compact('products', 'orders', 'customers');
    }
}
