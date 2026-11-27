{{-- Marketing & Growth Suite (Abandoned Carts + VIP Customers + Active Campaigns) --}}
<div class="row g-3 mb-4">
    {{-- Abandoned Cart Recovery Radar --}}
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">Abandoned Cart Radar</h6>
                    <small class="text-muted">Unconverted revenue sitting in carts</small>
                </div>
                <span class="adm-badge adm-badge-warning">
                    <i class='bx bx-cart'></i> {{ $abandonedCartStats['abandoned_carts'] ?? 0 }} carts
                </span>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-baseline justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Recoverable Revenue</div>
                            <div class="fs-4 fw-bold text-warning">${{ number_format($abandonedCartStats['recoverable_revenue'] ?? 0, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Items Left</div>
                            <div class="fw-bold">{{ number_format($abandonedCartStats['items_in_carts'] ?? 0) }} pcs</div>
                        </div>
                    </div>

                    <div class="small fw-semibold text-muted text-uppercase mb-2" style="font-size: 0.7rem;">Top Items Left Behind</div>
                    @forelse($abandonedCartStats['top_products'] ?? [] as $item)
                        <div class="d-flex align-items-center justify-content-between py-1">
                            <span class="small text-truncate" style="max-width: 65%;">{{ $item->product_name }}</span>
                            <span class="small fw-semibold">${{ number_format($item->total_value, 0) }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No abandoned carts right now — great checkout conversion!</p>
                    @endforelse
                </div>

                <div class="mt-3 pt-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#quickFlashSaleModal">
                        <i class='bx bxs-coupon me-1'></i> Launch Recovery Discount Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- VIP Customers & Repeat Loyalty (CLV) --}}
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">VIP Customers &amp; Loyalty</h6>
                    <small class="text-muted">Top buyers by Lifetime Value (CLV)</small>
                </div>
                <span class="adm-badge adm-badge-success">{{ $retentionMetrics['repeat_rate'] ?? 0 }}% repeat</span>
            </div>
            <div class="card-body p-0">
                @forelse($vipCustomers ?? [] as $vip)
                    <div class="d-flex align-items-center px-3 py-2 border-bottom gap-2">
                        <span class="adm-avatar-circle" style="width:32px;height:32px;font-size:0.75rem;">
                            {{ strtoupper(substr($vip->name, 0, 2)) }}
                        </span>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="{{ route('admin.users.show', $vip->id) }}" class="fw-semibold small text-reset text-decoration-none d-block text-truncate">
                                {{ $vip->name }}
                            </a>
                            <div class="text-muted" style="font-size: 11px;">{{ $vip->orders_count }} paid orders</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="fw-bold text-success small">${{ number_format($vip->lifetime_value, 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted small">No paid customer orders yet.</div>
                @endforelse
            </div>
            <div class="card-footer py-2 d-flex justify-content-between align-items-center small text-muted">
                <span>{{ $retentionMetrics['repeat_buyers'] ?? 0 }} / {{ $retentionMetrics['total_buyers'] ?? 0 }} repeat buyers</span>
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none fw-semibold">All Customers &rarr;</a>
            </div>
        </div>
    </div>

    {{-- Active Marketing Campaigns (Coupons) --}}
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">Active Campaigns &amp; Coupons</h6>
                    <small class="text-muted">Live promotional codes driving sales</small>
                </div>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">Manage</a>
            </div>
            <div class="card-body p-0">
                @forelse($activeCoupons ?? [] as $coupon)
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <div class="overflow-hidden">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $coupon->code }}</span>
                            <div class="text-muted mt-1" style="font-size: 11px;">
                                {{ $coupon->discount_type === 'percentage' ? $coupon->discount_amount . '% OFF' : '$' . $coupon->discount_amount . ' OFF' }}
                                @if($coupon->expires_at)
                                    &bull; Ends {{ $coupon->expires_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold small">{{ $coupon->used_count }}{{ $coupon->usage_limit ? '/' . $coupon->usage_limit : '' }}</div>
                            <div class="text-muted" style="font-size: 10px;">used</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 px-3">
                        <i class='bx bx-purchase-tag-alt fs-2 text-muted d-block mb-1'></i>
                        <p class="text-muted small mb-2">No active coupons running right now.</p>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#quickFlashSaleModal">
                            Create Your First Flash Sale
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Order Fulfillment Funnel & Payment Gateway Revenue Mix --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">Order Fulfillment Funnel</h6>
                    <small class="text-muted">Live order lifecycle &amp; delivery health</small>
                </div>
                <span class="adm-badge adm-badge-success">{{ $orderFunnel['fulfillment_rate'] ?? 0 }}% delivered</span>
            </div>
            <div class="card-body">
                @php
                    $funnelTotal = max(1, ($orderFunnel['pending'] ?? 0) + ($orderFunnel['processing'] ?? 0) + ($orderFunnel['shipped'] ?? 0) + ($orderFunnel['delivered'] ?? 0) + ($orderFunnel['cancelled'] ?? 0));
                    $funnelSteps = [
                        ['label' => 'Pending',    'count' => $orderFunnel['pending'] ?? 0,    'color' => 'warning'],
                        ['label' => 'Processing', 'count' => $orderFunnel['processing'] ?? 0, 'color' => 'primary'],
                        ['label' => 'Shipped',    'count' => $orderFunnel['shipped'] ?? 0,    'color' => 'info'],
                        ['label' => 'Delivered',  'count' => $orderFunnel['delivered'] ?? 0,  'color' => 'success'],
                        ['label' => 'Cancelled',  'count' => $orderFunnel['cancelled'] ?? 0,  'color' => 'danger'],
                    ];
                @endphp
                @foreach($funnelSteps as $step)
                    @php $w = round(($step['count'] / $funnelTotal) * 100, 1); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold">{{ $step['label'] }}</span>
                            <span class="text-muted">{{ number_format($step['count']) }} ({{ $w }}%)</span>
                        </div>
                        <div class="progress" style="height: 7px; border-radius: 999px;">
                            <div class="progress-bar bg-{{ $step['color'] }}" style="width: {{ $w }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">Payment Gateway Revenue Mix</h6>
                    <small class="text-muted">Paid checkout performance by gateway/method</small>
                </div>
                <i class='bx bx-credit-card fs-5 text-primary'></i>
            </div>
            <div class="card-body">
                @php $totalPayRev = max(1, ($paymentBreakdown ?? collect())->sum('revenue')); @endphp
                @forelse($paymentBreakdown ?? [] as $pm)
                    @php $pmPct = round(($pm->revenue / $totalPayRev) * 100, 1); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-uppercase">{{ str_replace('_', ' ', $pm->method) }}</span>
                            <span>
                                <strong>${{ number_format($pm->revenue, 0) }}</strong>
                                <span class="text-muted">({{ $pm->orders_count }} orders &bull; {{ $pmPct }}%)</span>
                            </span>
                        </div>
                        <div class="progress" style="height: 7px; border-radius: 999px;">
                            <div class="progress-bar" style="width: {{ $pmPct }}%; background: linear-gradient(90deg, #4f46e5, #0ea5e9);"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 text-center py-4">No paid transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
