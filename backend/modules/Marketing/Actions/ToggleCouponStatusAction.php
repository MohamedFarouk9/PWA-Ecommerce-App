<?php

namespace Modules\Marketing\Actions;

use Modules\Marketing\Models\Coupon;

class ToggleCouponStatusAction
{
    public function execute(int $couponId): Coupon
    {
        $coupon = Coupon::findOrFail($couponId);
        $coupon->update(['is_active' => !$coupon->is_active]);
        return $coupon;
    }
}
