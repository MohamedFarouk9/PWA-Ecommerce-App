<?php

namespace Modules\Dashboard\Services;

use Modules\Dashboard\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardAnalyticsService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function getOverviewPayload(int $rangeDays = 30): array
    {
        $days = in_array($rangeDays, [7, 30, 90], true) ? $rangeDays : 30;

        return [
            'rangeDays'          => $days,
            'stats'              => $this->dashboardRepository->getDashboardStats(),
            'chartData'          => $this->dashboardRepository->getRevenueTimeline($days),
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
        ];
    }
}
