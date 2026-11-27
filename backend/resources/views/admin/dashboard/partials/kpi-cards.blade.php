{{-- Primary 4 KPI Stat Cards --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <div class="adm-kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <div class="adm-kpi-label mb-1">Total Revenue</div>
                    <div class="adm-kpi-value">${{ number_format($totalRevenue, 2) }}</div>
                </div>
                <span class="adm-kpi-icon emerald"><i class='bx bx-dollar-circle'></i></span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="adm-trend-pill {{ $revenueUp ? 'up' : 'down' }}">
                    <i class='bx bx-{{ $revenueUp ? 'trending-up' : 'trending-down' }}'></i>
                    {{ $revenueUp ? '+' : '-' }}{{ $stats['revenue_change'] ?? 0 }}%
                </span>
                <span class="text-muted small">vs last 30 days</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="adm-kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <div class="adm-kpi-label mb-1">Total Orders</div>
                    <div class="adm-kpi-value">{{ number_format($totalOrders) }}</div>
                </div>
                <span class="adm-kpi-icon indigo"><i class='bx bx-cart-alt'></i></span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="adm-trend-pill {{ $ordersUp ? 'up' : 'down' }}">
                    <i class='bx bx-{{ $ordersUp ? 'trending-up' : 'trending-down' }}'></i>
                    {{ $ordersUp ? '+' : '-' }}{{ $stats['orders_change'] ?? 0 }}%
                </span>
                <span class="text-muted small">{{ number_format($pendingOrders) }} pending</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="adm-kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <div class="adm-kpi-label mb-1">Total Customers</div>
                    <div class="adm-kpi-value">{{ number_format($totalCustomers) }}</div>
                </div>
                <span class="adm-kpi-icon sky"><i class='bx bx-group'></i></span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="adm-trend-pill {{ $customersUp ? 'up' : 'down' }}">
                    <i class='bx bx-{{ $customersUp ? 'trending-up' : 'trending-down' }}'></i>
                    {{ $customersUp ? '+' : '-' }}{{ $stats['customers_change'] ?? 0 }}%
                </span>
                <span class="text-muted small">registered buyers</span>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="adm-kpi-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <div class="adm-kpi-label mb-1">Catalog Products</div>
                    <div class="adm-kpi-value">{{ number_format($totalProducts) }}</div>
                </div>
                <span class="adm-kpi-icon amber"><i class='bx bx-package'></i></span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="adm-trend-pill up">
                    <i class='bx bx-check-circle'></i> {{ $publishedPct }}% live
                </span>
                <span class="text-muted small">{{ number_format($activeProducts) }} published</span>
            </div>
        </div>
    </div>
</div>

{{-- Secondary Quick Metrics Strip --}}
<div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
    <div class="col">
        <div class="card mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <span class="adm-kpi-icon indigo" style="width:40px;height:40px;font-size:1.15rem;"><i class='bx bx-receipt'></i></span>
                <div>
                    <div class="text-muted small">Avg. Order Value</div>
                    <div class="fw-bold fs-6">${{ number_format($avgOrderValue, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <span class="adm-kpi-icon amber" style="width:40px;height:40px;font-size:1.15rem;"><i class='bx bx-time-five'></i></span>
                <div>
                    <div class="text-muted small">Pending Fulfillment</div>
                    <div class="fw-bold fs-6">{{ number_format($pendingOrders) }} orders</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <span class="adm-kpi-icon emerald" style="width:40px;height:40px;font-size:1.15rem;"><i class='bx bx-star'></i></span>
                <div>
                    <div class="text-muted small">Customer Reviews</div>
                    <div class="fw-bold fs-6">{{ number_format($totalReviews) }} total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <span class="adm-kpi-icon rose" style="width:40px;height:40px;font-size:1.15rem;"><i class='bx bx-envelope'></i></span>
                <div>
                    <div class="text-muted small">Support Inquiries</div>
                    <div class="fw-bold fs-6">{{ number_format($totalContacts) }} messages</div>
                </div>
            </div>
        </div>
    </div>
</div>
