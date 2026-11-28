<?php

namespace Modules\Dashboard\Actions;

use App\Models\Coupon;
use Modules\Dashboard\Repositories\Contracts\DashboardRepositoryInterface;

class LaunchFlashSaleAction
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(array $validated): Coupon
    {
        return $this->dashboardRepository->createFlashSaleCoupon($validated);
    }
}
