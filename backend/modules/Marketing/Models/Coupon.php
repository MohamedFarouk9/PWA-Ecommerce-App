<?php

namespace Modules\Marketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_amount',
        'min_spend',
        'max_discount',
        'usage_limit',
        'usage_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'min_spend'       => 'decimal:2',
        'max_discount'    => 'decimal:2',
        'usage_limit'     => 'integer',
        'usage_per_user'  => 'integer',
        'used_count'      => 'integer',
        'is_active'       => 'boolean',
        'starts_at'       => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function validateFor(?User $user, float $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'This coupon has not started yet.'];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'This coupon usage limit has been reached.'];
        }

        if ($this->min_spend > 0 && $subtotal < $this->min_spend) {
            return [
                'valid'   => false,
                'message' => 'Minimum order amount of $' . number_format($this->min_spend, 2) . ' required to use this coupon.',
            ];
        }

        if ($user && $this->usage_per_user > 0) {
            $userUsage = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsage >= $this->usage_per_user) {
                return ['valid' => false, 'message' => 'You have already reached the maximum usage for this coupon.'];
            }
        }

        $discount = $this->calculateDiscount($subtotal);

        return [
            'valid'           => true,
            'message'         => 'Coupon applied successfully!',
            'discount_amount' => $discount,
            'new_total'       => max(0, $subtotal - $discount),
            'coupon'          => [
                'id'              => $this->id,
                'code'            => $this->code,
                'discount_type'   => $this->discount_type,
                'discount_amount' => (float) $this->discount_amount,
            ],
        ];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = round(($subtotal * (float) $this->discount_amount) / 100, 2);
            if ($this->max_discount && $discount > (float) $this->max_discount) {
                $discount = (float) $this->max_discount;
            }
        } else {
            $discount = min($subtotal, (float) $this->discount_amount);
        }

        return round($discount, 2);
    }
}
