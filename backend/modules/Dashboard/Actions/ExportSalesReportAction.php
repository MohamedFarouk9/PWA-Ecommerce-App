<?php

namespace Modules\Dashboard\Actions;

use Modules\Dashboard\Repositories\Contracts\DashboardRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportSalesReportAction
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(int $days = 30): StreamedResponse
    {
        $safeDays = in_array($days, [7, 30, 90, 365], true) ? $days : 30;
        $filename = 'shoppulse_sales_report_' . $safeDays . 'd_' . now()->format('Y-m-d') . '.csv';
        $orders   = $this->dashboardRepository->getOrdersForExport($safeDays);

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
}
