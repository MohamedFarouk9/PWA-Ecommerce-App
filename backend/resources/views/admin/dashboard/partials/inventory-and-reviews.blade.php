{{-- Low Stock Alerts + Recent Customer Reviews --}}
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

{{-- Smart Store Intelligence Banner --}}
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
