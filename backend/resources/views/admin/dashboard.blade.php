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

    $ordersUp     = ($stats['orders_change_type'] ?? 'increase') === 'increase';
    $revenueUp    = ($stats['revenue_change_type'] ?? 'increase') === 'increase';
    $customersUp  = ($stats['customers_change_type'] ?? 'increase') === 'increase';
    $publishedPct = $totalProducts > 0 ? round(($activeProducts / $totalProducts) * 100) : 0;

    $catColors   = ['#4f46e5', '#10b981', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];
    $totalCatRev = $categoryRevenue->sum('revenue');
@endphp

<div class="page-wrapper">
    <div class="page-content">

        {{-- Flash Feedback Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
                <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
                <i class='bx bx-error-circle me-2'></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 1. Executive Hero Bar & Growth Actions --}}
        <div class="adm-hero-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="adm-hero-title">Welcome back, {{ $adminName }}</h4>
                <p class="text-muted small mb-0">
                    <i class='bx bx-calendar me-1'></i>{{ now()->format('l, F j, Y') }} &bull;
                    Real-time store revenue, marketing campaigns, and fulfillment intelligence.
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group" aria-label="Chart Range">
                    @foreach([7 => '7D', 30 => '30D', 90 => '90D'] as $d => $lbl)
                        <a href="{{ route('admin.dashboard', ['range' => $d]) }}"
                           class="btn {{ ($rangeDays ?? 30) === $d ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>

                <button type="button" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:10px;font-size:0.85rem;" data-bs-toggle="modal" data-bs-target="#quickFlashSaleModal">
                    <i class='bx bxs-zap fs-6'></i> Launch Flash Sale
                </button>
                <a href="{{ route('admin.dashboard.export-sales', ['range' => $rangeDays ?? 30]) }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1" style="border-radius:10px;font-size:0.85rem;">
                    <i class='bx bx-download fs-6'></i> Export CSV
                </a>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class='bx bx-plus-circle fs-6'></i> Add Product
                </a>
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}" target="_blank" rel="noopener" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class='bx bx-link-external fs-6'></i> Storefront
                </a>
            </div>
        </div>

        {{-- 2. KPI Cards & Quick Metrics --}}
        @include('admin.dashboard.partials.kpi-cards')

        {{-- 3. Revenue Timeline Chart + Category Revenue Breakdown --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $rangeDays ?? 30 }}-Day Revenue Overview</h6>
                            <small class="text-muted">Daily paid order revenue across the last {{ $rangeDays ?? 30 }} days</small>
                        </div>
                        <span class="adm-badge adm-badge-primary">
                            <i class='bx bx-line-chart'></i>
                            ${{ number_format(array_sum($chartData['data'] ?? []), 2) }} ({{ $rangeDays ?? 30 }}d)
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

        {{-- 4. Marketing Suite, Fulfillment Funnel & Payment Gateway Mix --}}
        @include('admin.dashboard.partials.marketing-suite')

        {{-- 5. Recent Orders & Top Selling Products --}}
        @include('admin.dashboard.partials.orders-and-products')

        {{-- 6. Low Stock Alerts, Customer Reviews & Store Intelligence --}}
        @include('admin.dashboard.partials.inventory-and-reviews')

    </div>
</div>

{{-- 7. 1-Click Quick Flash Sale Modal --}}
@include('admin.dashboard.partials.flash-sale-modal')
@endsection

@push('scripts')
<script>
(function () {
    // 1. Revenue Area Chart (Chart.js v2 compatible)
    var revCanvas = document.getElementById('revenueTimelineChart');
    if (revCanvas && typeof Chart !== 'undefined') {
        var ctx = revCanvas.getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels'] ?? []),
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: @json($chartData['data'] ?? []),
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
                    xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 10, fontColor: '#64748b' } }],
                    yAxes: [{
                        gridLines: { color: 'rgba(148, 163, 184, 0.16)', drawBorder: false },
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#64748b',
                            callback: function (value) { return '$' + Number(value).toLocaleString(); }
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
