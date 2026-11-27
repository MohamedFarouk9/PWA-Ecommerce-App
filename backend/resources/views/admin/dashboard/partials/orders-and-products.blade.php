{{-- Recent Orders Table + Top Selling Products --}}
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
