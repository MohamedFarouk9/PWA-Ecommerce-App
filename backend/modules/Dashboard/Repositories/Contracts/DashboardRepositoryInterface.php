<?php

namespace Modules\Dashboard\Repositories\Contracts;

use App\Models\Coupon;
use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getDashboardStats(): array;

    public function getRevenueTimeline(int $days = 30): array;

    public function getRecentOrders(int $limit = 10): Collection;

    public function getRecentReviews(int $limit = 5): Collection;

    public function getTopProducts(int $limit = 5): Collection;

    public function getLowStockProducts(int $limit = 8): Collection;

    public function getTrendingProducts(int $days = 7, int $limit = 6): Collection;

    public function getDeadStockProducts(int $days = 30, int $limit = 6): Collection;

    public function getCategoryRevenue(int $limit = 8): Collection;

    public function getOrderFunnel(): array;

    public function getPaymentMethodBreakdown(): Collection;

    public function getVipCustomers(int $limit = 5): Collection;

    public function getCustomerRetentionMetrics(): array;

    public function getAbandonedCartStats(): array;

    public function getActiveCoupons(int $limit = 5): Collection;

    public function getOrdersForExport(int $days = 30): Collection;

    public function createFlashSaleCoupon(array $validated): Coupon;

    public function globalSearch(string $query, int $limit = 10): array;
}
