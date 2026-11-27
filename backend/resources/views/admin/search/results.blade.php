@extends('admin.layouts.app')
@section('title', 'Search Results')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <div class="adm-hero-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="adm-hero-title">Search Results for &ldquo;{{ $query }}&rdquo;</h4>
                <p class="text-muted small mb-0">
                    Found {{ $products->count() }} products, {{ $orders->count() }} orders, and {{ $customers->count() }} customers.
                </p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>
        </div>

        {{-- Matching Products --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class='bx bx-package me-2 text-primary'></i>Products ({{ $products->count() }})</h6>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">All Products</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="fw-semibold">{{ $product->name }}</td>
                                    <td class="text-muted">{{ $product->brand ?? '—' }}</td>
                                    <td><span class="adm-badge adm-badge-muted">{{ $product->category->name ?? 'Uncategorized' }}</span></td>
                                    <td class="text-end fw-bold">${{ number_format((float) $product->price, 2) }}</td>
                                    <td class="text-end">{{ $product->quantity }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2"><i class='bx bx-show'></i></a>
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2"><i class='bx bx-edit'></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No matching products found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Matching Orders --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class='bx bx-shopping-bag me-2 text-success'></i>Orders ({{ $orders->count() }})</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">All Orders</a>
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
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $order->order_number ?? 'ORD-' . $order->id }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $order->user->name ?? 'Guest' }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $order->user->email ?? '' }}</div>
                                    </td>
                                    <td><span class="adm-badge adm-badge-info">{{ ucfirst($order->status) }}</span></td>
                                    <td><span class="adm-badge adm-badge-success">{{ ucfirst($order->payment_status) }}</span></td>
                                    <td class="text-end fw-bold">${{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2"><i class='bx bx-show'></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No matching orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Matching Customers --}}
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class='bx bx-group me-2 text-info'></i>Customers ({{ $customers->count() }})</h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">All Customers</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td class="fw-semibold">{{ $customer->name }}</td>
                                    <td class="text-muted">{{ $customer->email }}</td>
                                    <td class="text-muted">{{ $customer->phone ?? '—' }}</td>
                                    <td><span class="adm-badge {{ $customer->status === 'active' ? 'adm-badge-success' : 'adm-badge-muted' }}">{{ ucfirst($customer->status ?? 'active') }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.users.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2"><i class='bx bx-show'></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No matching customers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
