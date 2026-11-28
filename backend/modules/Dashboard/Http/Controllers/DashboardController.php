<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Actions\ExportSalesReportAction;
use Modules\Dashboard\Actions\LaunchFlashSaleAction;
use Modules\Dashboard\Http\Requests\QuickFlashSaleRequest;
use Modules\Dashboard\Http\Requests\UploadFileRequest;
use Modules\Dashboard\Http\Requests\UploadImageRequest;
use Modules\Dashboard\Repositories\Contracts\DashboardRepositoryInterface;
use Modules\Dashboard\Services\DashboardAnalyticsService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
        private readonly DashboardAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $payload = $this->analyticsService->getOverviewPayload((int) $request->get('range', 30));

        return view('admin.dashboard', $payload);
    }

    public function stats(Request $request)
    {
        $days = in_array((int) $request->get('range', 30), [7, 30, 90], true)
            ? (int) $request->get('range', 30)
            : 30;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stats'         => $this->dashboardRepository->getDashboardStats(),
                'chart_data'    => $this->dashboardRepository->getRevenueTimeline($days),
                'recent_orders' => $this->dashboardRepository->getRecentOrders(),
                'top_products'  => $this->dashboardRepository->getTopProducts(),
            ],
        ]);
    }

    public function exportSalesCsv(Request $request, ExportSalesReportAction $action)
    {
        return $action->execute((int) $request->get('range', 30));
    }

    public function createQuickFlashSale(QuickFlashSaleRequest $request, LaunchFlashSaleAction $action)
    {
        $validated = $request->validated();
        $coupon    = $action->execute($validated);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Flash Sale coupon {$coupon->code} ({$coupon->discount_amount}% OFF) is now LIVE for {$validated['duration_hours']} hours!");
    }

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
