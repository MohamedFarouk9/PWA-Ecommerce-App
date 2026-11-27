<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dashboard\QuickFlashSaleRequest;
use App\Http\Requests\Admin\Dashboard\UploadFileRequest;
use App\Http\Requests\Admin\Dashboard\UploadImageRequest;
use App\Repositories\Contracts\Admin\AdminDashboardRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardRepositoryInterface $dashboardRepository
    ) {}

    /**
     * Display the Admin Dashboard (Blade View)
     */
    public function index(Request $request)
    {
        $rangeDays = in_array((int) $request->get('range', 30), [7, 30, 90], true)
            ? (int) $request->get('range', 30)
            : 30;

        return view('admin.dashboard', [
            'rangeDays'          => $rangeDays,
            'stats'              => $this->dashboardRepository->getDashboardStats(),
            'chartData'          => $this->dashboardRepository->getRevenueTimeline($rangeDays),
            'recentOrders'       => $this->dashboardRepository->getRecentOrders(),
            'recentReviews'      => $this->dashboardRepository->getRecentReviews(),
            'topProducts'        => $this->dashboardRepository->getTopProducts(),
            'lowStockProducts'   => $this->dashboardRepository->getLowStockProducts(),
            'trendingProducts'   => $this->dashboardRepository->getTrendingProducts(),
            'deadStockProducts'  => $this->dashboardRepository->getDeadStockProducts(),
            'categoryRevenue'    => $this->dashboardRepository->getCategoryRevenue(),
            'orderFunnel'        => $this->dashboardRepository->getOrderFunnel(),
            'paymentBreakdown'   => $this->dashboardRepository->getPaymentMethodBreakdown(),
            'vipCustomers'       => $this->dashboardRepository->getVipCustomers(),
            'retentionMetrics'   => $this->dashboardRepository->getCustomerRetentionMetrics(),
            'abandonedCartStats' => $this->dashboardRepository->getAbandonedCartStats(),
            'activeCoupons'      => $this->dashboardRepository->getActiveCoupons(),
        ]);
    }

    /**
     * Dashboard statistics JSON endpoint (React / Ajax)
     */
    public function stats(Request $request)
    {
        $rangeDays = in_array((int) $request->get('range', 30), [7, 30, 90], true)
            ? (int) $request->get('range', 30)
            : 30;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stats'         => $this->dashboardRepository->getDashboardStats(),
                'chart_data'    => $this->dashboardRepository->getRevenueTimeline($rangeDays),
                'recent_orders' => $this->dashboardRepository->getRecentOrders(),
                'top_products'  => $this->dashboardRepository->getTopProducts(),
            ],
        ]);
    }

    /**
     * Stream an Excel-ready CSV Sales & Order Report
     */
    public function exportSalesCsv(Request $request)
    {
        $days = in_array((int) $request->get('range', 30), [7, 30, 90, 365], true)
            ? (int) $request->get('range', 30)
            : 30;

        $filename = 'shoppulse_sales_report_' . $days . 'd_' . now()->format('Y-m-d') . '.csv';
        $orders   = $this->dashboardRepository->getOrdersForExport($days);

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number', 'Date', 'Customer Name', 'Customer Email',
                'Order Status', 'Payment Status', 'Payment Method',
                'Coupon Code', 'Discount ($)', 'Total Amount ($)',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number ?? 'ORD-' . $order->id,
                    $order->created_at?->format('Y-m-d H:i:s'),
                    $order->user->name ?? 'Guest',
                    $order->user->email ?? 'N/A',
                    ucfirst($order->status ?? 'pending'),
                    ucfirst($order->payment_status ?? 'unpaid'),
                    strtoupper($order->payment_method ?? 'N/A'),
                    $order->coupon_code ?? '',
                    number_format((float) ($order->discount_amount ?? 0), 2, '.', ''),
                    number_format((float) ($order->total_amount ?? 0), 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * 1-Click Quick Flash-Sale Coupon Launcher
     */
    public function createQuickFlashSale(QuickFlashSaleRequest $request)
    {
        $validated = $request->validated();
        $coupon    = $this->dashboardRepository->createFlashSaleCoupon($validated);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Flash Sale coupon {$coupon->code} ({$coupon->discount_amount}% OFF) is now LIVE for {$validated['duration_hours']} hours!");
    }

    /**
     * Global Admin Search (Products, Orders, Customers)
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return redirect()->route('admin.dashboard');
        }

        $results = $this->dashboardRepository->globalSearch($query);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => array_merge(['query' => $query], $results),
            ]);
        }

        return view('admin.search.results', array_merge(['query' => $query], $results));
    }

    /**
     * Upload image helper
     */
    public function uploadImage(UploadImageRequest $request)
    {
        $image    = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path     = $image->storeAs('admin/uploads', $filename, 'public');

        return response()->json([
            'success'  => true,
            'url'      => asset('storage/' . $path),
            'filename' => $filename,
        ]);
    }

    /**
     * Upload file helper
     */
    public function uploadFile(UploadFileRequest $request)
    {
        $file     = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('admin/files', $filename, 'public');

        return response()->json([
            'success'  => true,
            'url'      => asset('storage/' . $path),
            'filename' => $filename,
        ]);
    }
}
