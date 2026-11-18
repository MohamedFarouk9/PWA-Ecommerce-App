<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard (Blade View)
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $chartData = $this->getChartData();
        $recentOrders = $this->getRecentOrders();
        $recentReviews = $this->getRecentReviews();
        $topProducts = $this->getTopProducts();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'recentOrders',
            'recentReviews',
            'topProducts'
        ));
    }

    /**
     * Get dashboard statistics (API endpoint for React Frontend & Ajax)
     */
    public function stats()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $this->getDashboardStats(),
                'chart_data' => $this->getChartData(),
                'recent_orders' => $this->getRecentOrders(),
                'top_products' => $this->getTopProducts(),
            ],
        ]);
    }

    /**
     * Global Admin Search
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('admin.dashboard');
        }

        $searchTerm = "%{$query}%";

        $products = Product::where('name', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(10)
            ->get();

        $orders = Order::where('order_number', 'like', $searchTerm)
            ->orWhere('payment_id', 'like', $searchTerm)
            ->orWhereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)->orWhere('email', 'like', $searchTerm);
            })
            ->limit(10)
            ->get();

        $customers = User::where('name', 'like', $searchTerm)
            ->orWhere('email', 'like', $searchTerm)
            ->limit(10)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => compact('query', 'products', 'orders', 'customers'),
            ]);
        }

        return view('admin.search.results', compact('query', 'products', 'orders', 'customers'));
    }

    /**
     * Upload image helper
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120'
        ]);

        $image = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('admin/uploads', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'filename' => $filename
        ]);
    }

    /**
     * Upload file helper
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240'
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('admin/files', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'filename' => $filename
        ]);
    }

    /**
     * Compute comprehensive dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $totalProducts = Product::count();
        $activeProducts = Product::whereHas('status', fn($q) => $q->where('name', 'published'))->count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalReviews = ProductReview::count();
        $totalContacts = Contact::count();

        // Month-over-month trends
        $ordersThisMonth = Order::where('created_at', '>=', $lastMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        $ordersChange = $ordersLastMonth > 0 ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1) : 0;

        $revenueThisMonth = (float) Order::where('payment_status', 'paid')->where('created_at', '>=', $lastMonth)->sum('total_amount');
        $revenueLastMonth = (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->sum('total_amount');
        $revenueChange = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0;

        $customersThisMonth = User::where('role', 'user')->where('created_at', '>=', $lastMonth)->count();
        $customersLastMonth = User::where('role', 'user')->whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        $customersChange = $customersLastMonth > 0 ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1) : 0;

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_customers' => $totalCustomers,
            'total_revenue' => $totalRevenue,
            'total_reviews' => $totalReviews,
            'total_contacts' => $totalContacts,
            'orders_change' => abs($ordersChange),
            'orders_change_type' => $ordersChange >= 0 ? 'increase' : 'decrease',
            'revenue_change' => abs($revenueChange),
            'revenue_change_type' => $revenueChange >= 0 ? 'increase' : 'decrease',
            'customers_change' => abs($customersChange),
            'customers_change_type' => $customersChange >= 0 ? 'increase' : 'decrease',
        ];
    }

    /**
     * Compute 30-day timeline revenue chart
     */
    private function getChartData(): array
    {
        $days = 30;
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $dailyRevenue = (float) Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $data[] = round($dailyRevenue, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Fetch recent orders with customer relationship
     */
    private function getRecentOrders()
    {
        return Order::with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return (object) [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'customer_name' => $order->user->name ?? 'Guest User',
                    'customer_email' => $order->user->email ?? 'N/A',
                    'status' => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? 'unpaid',
                    'total' => (float) ($order->total_amount ?? 0),
                    'created_at' => $order->created_at ? $order->created_at->format('d M Y') : 'N/A',
                ];
            });
    }

    /**
     * Fetch latest product reviews
     */
    private function getRecentReviews()
    {
        return ProductReview::with(['product', 'user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return (object) [
                    'rating' => (int) ($review->rating ?? 5),
                    'comment' => $review->comment ?? '',
                    'product_title' => $review->product->name ?? 'Product #' . $review->product_id,
                    'customer_name' => $review->user->name ?? 'Anonymous Customer',
                    'created_at' => $review->created_at ? $review->created_at->diffForHumans() : 'Recently',
                ];
            });
    }

    /**
     * Fetch top selling products
     */
    private function getTopProducts()
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
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
    }
}
