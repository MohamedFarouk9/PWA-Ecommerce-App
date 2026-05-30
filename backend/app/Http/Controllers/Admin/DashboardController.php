<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_reviews' => ProductReview::count(),
            'total_contacts' => Contact::count(),
            'active_products' => Product::where('status', 'published')->count(),
            'pending_contacts' => Contact::whereNull('read_at')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Search functionality
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('admin.dashboard');
        }

        $searchTerm = "%{$query}%";

        // Search in products
        $products = Product::where('title', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(5)
            ->get();

        // Search in orders
        $orders = Order::where('order_number', 'like', $searchTerm)
            ->orWhere('customer_name', 'like', $searchTerm)
            ->limit(5)
            ->get();

        // Search in customers
        $customers = User::where('name', 'like', $searchTerm)
            ->orWhere('email', 'like', $searchTerm)
            ->limit(5)
            ->get();

        return view('admin.search.results', compact('query', 'products', 'orders', 'customers'));
    }

    /**
     * Upload image.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $path = $image->storeAs('admin/uploads', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'filename' => $filename
        ]);
    }

    /**
     * Upload file.
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
     * Get dashboard statistics.
     */
    private function getDashboardStats()
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Total counts
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('is_admin', false)->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');

        // Monthly changes
        $productsThisMonth = Product::where('created_at', '>=', $lastMonth)->count();
        $productsLastMonth = Product::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        $productsChange = $productsLastMonth > 0 ? round((($productsThisMonth - $productsLastMonth) / $productsLastMonth) * 100, 1) : 0;

        $ordersThisMonth = Order::where('created_at', '>=', $lastMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        $ordersChange = $ordersLastMonth > 0 ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1) : 0;

        $customersThisMonth = User::where('is_admin', false)->where('created_at', '>=', $lastMonth)->count();
        $customersLastMonth = User::where('is_admin', false)->whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        $customersChange = $customersLastMonth > 0 ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1) : 0;

        $revenueThisMonth = Order::where('status', 'completed')->where('created_at', '>=', $lastMonth)->sum('total');
        $revenueLastMonth = Order::where('status', 'completed')->whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->sum('total');
        $revenueChange = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0;

        return [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'total_revenue' => $totalRevenue,
            'products_change' => abs($productsChange),
            'products_change_type' => $productsChange >= 0 ? 'increase' : 'decrease',
            'orders_change' => abs($ordersChange),
            'orders_change_type' => $ordersChange >= 0 ? 'increase' : 'decrease',
            'customers_change' => abs($customersChange),
            'customers_change_type' => $customersChange >= 0 ? 'increase' : 'decrease',
            'revenue_change' => abs($revenueChange),
            'revenue_change_type' => $revenueChange >= 0 ? 'increase' : 'decrease',
        ];
    }

    /**
     * Get chart data for sales overview.
     */
    private function getChartData()
    {
        $days = 30;
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $dailyRevenue = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');

            $data[] = $dailyRevenue;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get recent orders.
     */
    private function getRecentOrders()
    {
        return Order::with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return (object) [
                    'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                    'customer_name' => $order->user->name ?? 'Guest',
                    'status' => $order->status,
                    'total' => $order->total,
                    'created_at' => $order->created_at
                ];
            });
    }

    /**
     * Get recent reviews.
     */
    private function getRecentReviews()
    {
        return ProductReview::with('product', 'user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return (object) [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'product_title' => $review->product->title ?? 'Unknown Product',
                    'customer_name' => $review->user->name ?? 'Anonymous',
                    'created_at' => $review->created_at
                ];
            });
    }

    /**
     * Get top products.
     */
    private function getTopProducts()
    {
        return Product::select('products.*')
            ->selectRaw('COUNT(order_items.id) as sales_count')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as total_revenue')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
    }
}
