@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $adminName      = auth()->user()->name ?? 'Admin';
    $totalOrders    = (int) ($stats['total_orders'] ?? 0);
    $totalRevenue   = (float) ($stats['total_revenue'] ?? 0);
    $totalCustomers = (int) ($stats['total_customers'] ?? 0);
    $totalProducts  = (int) ($stats['total_products'] ?? 0);
    $activeProducts = (int) ($stats['active_products'] ?? 0);
    $pendingOrders  = (int) ($stats['pending_orders'] ?? 0);
    $totalReviews   = (int) ($stats['total_reviews'] ?? 0);
    $totalContacts  = (int) ($stats['total_contacts'] ?? 0);
    $avgOrderValue  = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

    $ordersUp    = ($stats['orders_change_type'] ?? 'increase') === 'increase';
    $revenueUp   = ($stats['revenue_change_type'] ?? 'increase') === 'increase';
    $customersUp = ($stats['customers_change_type'] ?? 'increase') === 'increase';
    $publishedPct = $totalProducts > 0 ? round(($activeProducts / $totalProducts) * 100) : 0;

    $catColors   = ['#4f46e5', '#10b981', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];
    $totalCatRev = $categoryRevenue->sum('revenue');
@endphp

<div class="page-wrapper">
    <div class="page-content">

        {{-- 1. Executive Welcome Hero Bar --}}
        <div class="adm-hero-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="adm-hero-title">Welcome back, {{ $adminName }}</h4>
                <p class="text-muted small mb-0">
                    <i class='bx bx-calendar me-1'></i>{{ now()->format('l, F j, Y') }} &bull;
                    Real-time overview of your store performance, inventory, and orders.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class='bx bx-plus-circle fs-6'></i> Add Product
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class='bx bx-shopping-bag fs-6'></i> Manage Orders
                    @if($pendingOrders > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingOrders }}</span>
                    @endif
                </a>
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}" target="_blank" rel="noopener" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class='bx bx-link-external fs-6'></i> Storefront
                </a>
            </div>
        </div>

        {{-- 2. Primary 4 KPI Stat Cards --}}
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
            {{-- Total Revenue --}}
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

            {{-- Total Orders --}}
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

            {{-- Total Customers --}}
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

            {{-- Active Catalog --}}
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

        {{-- 3. Secondary Quick Metrics Strip --}}
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

        {{-- 4. Revenue Timeline Chart + Category Revenue Breakdown --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold">30-Day Revenue Overview</h6>
                            <small class="text-muted">Daily paid order revenue across the last 30 days</small>
                        </div>
                        <span class="adm-badge adm-badge-primary">
                            <i class='bx bx-line-chart'></i>
                            ${{ number_format(array_sum($chartData['data'] ?? []), 2) }} (30d)
                        </span>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;">
                            <canvas id="revenueTimelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Revenue by Category</h6>
                            <small class="text-muted">Top earning product categories</small>
                        </div>
                        <i class='bx bx-pie-chart-alt-2 fs-5 text-primary'></i>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-center mb-3" style="position: relative; height: 185px;">
                            <canvas id="categoryRevenueChart"></canvas>
                        </div>
                        <div class="mt-2">
                            @forelse($categoryRevenue->take(5) as $i => $cat)
                                @php $pct = $totalCatRev > 0 ? round(($cat->revenue / $totalCatRev) * 100, 1) : 0; @endphp
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <span class="rounded-circle flex-shrink-0" style="width:10px;height:10px;background:{{ $catColors[$i % count($catColors)] }};"></span>
                                        <span class="small fw-medium text-truncate" style="max-width: 140px;">{{ $cat->category }}</span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="small fw-bold">${{ number_format($cat->revenue, 0) }}</span>
                                        <span class="text-muted ms-1" style="font-size: 11px;">({{ $pct }}%)</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small text-center mb-0">No category revenue recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Recent Orders Table + Top Selling Products --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Recent Orders</h6>
                            <small class="text-muted">Latest 10 customer transactions</small>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th class="text-end">Total</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        @php
                                            $statusClass = match($order->status) {
                                                'delivered'  => 'adm-badge-success',
                                                'shipped'    => 'adm-badge-info',
                                                'processing' => 'adm-badge-primary',
                                                'cancelled'  => 'adm-badge-danger',
                                                default      => 'adm-badge-warning',
                                            };
                                            $payClass = match($order->payment_status) {
                                                'paid'     => 'adm-badge-success',
                                                'refunded' => 'adm-badge-danger',
                                                default    => 'adm-badge-warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold text-primary">{{ $order->order_number }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $order->customer_name }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $order->customer_email }}</div>
                                            </td>
                                            <td><span class="adm-badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span></td>
                                            <td><span class="adm-badge {{ $payClass }}">{{ ucfirst($order->payment_status) }}</span></td>
                                            <td class="text-end fw-bold">${{ number_format($order->total, 2) }}</td>
                                            <td class="text-muted small">{{ $order->created_at }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Order">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No orders placed yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Top Selling Products</h6>
                            <small class="text-muted">Ranked by paid revenue</small>
                        </div>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">Catalog</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($topProducts as $idx => $product)
                            <div class="d-flex align-items-center px-3 py-3 border-bottom gap-3">
                                <span class="adm-avatar-circle" style="width:34px;height:34px;font-size:0.78rem;">#{{ $idx + 1 }}</span>
                                <div class="flex-grow-1 overflow-hidden">
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="fw-semibold text-reset text-decoration-none d-block text-truncate">
                                        {{ $product->name }}
                                    </a>
                                    <div class="text-muted small">
                                        {{ number_format($product->sales_count) }} orders &bull;
                                        <span class="{{ $product->stock <= 10 ? 'text-danger fw-semibold' : '' }}">{{ $product->stock }} in stock</span>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="fw-bold text-success">${{ number_format($product->total_revenue, 0) }}</div>
                                    <div class="text-muted" style="font-size:11px;">${{ number_format($product->price, 2) }}/ea</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted small">No sales data yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. Low Stock Alerts + Recent Customer Reviews --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-6">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Low Stock Alerts</h6>
                            <small class="text-muted">Products with 10 or fewer units remaining</small>
                        </div>
                        <span class="adm-badge adm-badge-danger">{{ $lowStockProducts->count() }} items</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($lowStockProducts as $product)
                            @php
                                $lvl = $product->quantity <= 3 ? 'danger' : ($product->quantity <= 7 ? 'warning' : 'info');
                            @endphp
                            <div class="d-flex align-items-center px-3 py-2 border-bottom gap-3">
                                <div class="adm-kpi-icon {{ $lvl === 'danger' ? 'rose' : ($lvl === 'warning' ? 'amber' : 'sky') }}" style="width:38px;height:38px;font-size:1.1rem;">
                                    <i class='bx bx-error-circle'></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate">{{ $product->name }}</div>
                                    <div class="progress mt-1" style="height: 5px; max-width: 180px;">
                                        <div class="progress-bar bg-{{ $lvl }}" style="width: {{ min($product->quantity * 10, 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <span class="adm-badge adm-badge-{{ $lvl }}">{{ $product->quantity }} left</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class='bx bx-check-shield fs-1 text-success d-block mb-1'></i>
                                All products are well stocked!
                            </div>
                        @endforelse
                    </div>
                    @if($lowStockProducts->count() > 0)
                        <div class="card-footer text-center py-2">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-danger">Restock Inventory</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Latest Customer Reviews</h6>
                            <small class="text-muted">Recent verified feedback across your catalog</small>
                        </div>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">All Reviews</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentReviews as $review)
                            <div class="px-3 py-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="fw-semibold small text-truncate" style="max-width: 65%;">{{ $review->product_title }}</div>
                                    <div class="text-warning small flex-shrink-0">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class='bx {{ $s <= $review->rating ? 'bxs-star' : 'bx-star text-muted' }}'></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-muted small mb-1">{{ Str::limit($review->comment, 110) }}</p>
                                <div class="text-muted" style="font-size: 11px;">
                                    By <strong>{{ $review->customer_name }}</strong> &bull; {{ $review->created_at }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted small">No customer reviews yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. Smart Store Intelligence Banner --}}
        @if($lowStockProducts->count() > 0 || $trendingProducts->count() > 0 || $deadStockProducts->count() > 0)
        <div class="card adm-insight-banner mb-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h6 class="fw-bold text-white mb-0">
                        <i class='bx bx-bulb me-2'></i>Smart Store Intelligence &amp; Actionable Insights
                    </h6>
                    <span class="badge bg-white bg-opacity-25 text-white">Auto-Computed from Live Orders</span>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    @if($lowStockProducts->count() > 0)
                        <div class="col">
                            <div class="adm-insight-tile">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class='bx bx-refresh fs-5'></i>
                                    <strong>Restock Priority</strong>
                                </div>
                                <p class="small opacity-75 mb-2">{{ $lowStockProducts->count() }} SKUs have &le; 10 units left.</p>
                                <div class="small opacity-90">
                                    @foreach($lowStockProducts->take(3) as $p)
                                        <div class="text-truncate">&bull; {{ $p->name }} ({{ $p->quantity }} left)</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($trendingProducts->count() > 0)
                        <div class="col">
                            <div class="adm-insight-tile">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class='bx bx-trending-up fs-5'></i>
                                    <strong>7-Day Trending Products</strong>
                                </div>
                                <p class="small opacity-75 mb-2">Fastest-moving products this week — ideal for homepage sliders.</p>
                                <div class="small opacity-90">
                                    @foreach($trendingProducts->take(3) as $p)
                                        <div class="text-truncate">&bull; {{ $p->name }} ({{ $p->units_sold }} sold)</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($deadStockProducts->count() > 0)
                        <div class="col">
                            <div class="adm-insight-tile">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class='bx bx-purchase-tag fs-5'></i>
                                    <strong>Promote Idle Inventory</strong>
                                </div>
                                <p class="small opacity-75 mb-2">{{ $deadStockProducts->count() }} in-stock products had no orders in 30 days.</p>
                                <div class="small opacity-90">
                                    @foreach($deadStockProducts->take(3) as $p)
                                        <div class="text-truncate">&bull; {{ $p->name }} ({{ $p->quantity }} units)</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // 1. 30-Day Revenue Area Chart (Chart.js v2 compatible)
    var revCanvas = document.getElementById('revenueTimelineChart');
    if (revCanvas && typeof Chart !== 'undefined') {
        var ctx = revCanvas.getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

        var labels = @json($chartData['labels'] ?? []);
        var values = @json($chartData['data'] ?? []);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: values,
                    borderColor: '#4f46e5',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointRadius: 2.5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    fill: true,
                    lineTension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (tooltipItem) {
                            return ' $' + Number(tooltipItem.yLabel || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { maxTicksLimit: 10, fontColor: '#64748b' }
                    }],
                    yAxes: [{
                        gridLines: { color: 'rgba(148, 163, 184, 0.16)', drawBorder: false },
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#64748b',
                            callback: function (value) {
                                return '$' + Number(value).toLocaleString();
                            }
                        }
                    }]
                }
            }
        });
    }

    // 2. Category Revenue Doughnut Chart (Chart.js v2 compatible)
    var catCanvas = document.getElementById('categoryRevenueChart');
    if (catCanvas && typeof Chart !== 'undefined') {
        var catLabels = @json($categoryRevenue->pluck('category'));
        var catValues = @json($categoryRevenue->pluck('revenue'));
        var catColors = ['#4f46e5', '#10b981', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

        new Chart(catCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: catColors.slice(0, catLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 68,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var lbl = data.labels[tooltipItem.index] || '';
                            var val = data.datasets[0].data[tooltipItem.index] || 0;
                            return ' ' + lbl + ': $' + Number(val).toLocaleString();
                        }
                    }
                }
            }
        });
    }
})();
</script>
@endpush
