<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'            => 'SAVE20',
                'description'     => '20% discount on all orders',
                'discount_type'   => 'percentage',
                'discount_amount' => 20.00,
                'min_spend'       => 20.00,
                'max_discount'    => 100.00,
                'usage_limit'     => 500,
                'usage_per_user'  => 3,
                'is_active'       => true,
            ],
            [
                'code'            => 'WELCOME10',
                'description'     => '10% discount for new customers',
                'discount_type'   => 'percentage',
                'discount_amount' => 10.00,
                'min_spend'       => 0.00,
                'max_discount'    => 50.00,
                'usage_limit'     => 1000,
                'usage_per_user'  => 1,
                'is_active'       => true,
            ],
            [
                'code'            => 'FLAT50',
                'description'     => 'Flat $50 off on orders over $150',
                'discount_type'   => 'fixed',
                'discount_amount' => 50.00,
                'min_spend'       => 150.00,
                'max_discount'    => null,
                'usage_limit'     => 200,
                'usage_per_user'  => 1,
                'is_active'       => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
