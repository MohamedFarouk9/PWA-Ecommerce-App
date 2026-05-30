@extends('admin.layouts.app')
@section('title', 'Order Tracking - ' . $order->order_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Order Tracking - #{{ $order->order_number }}</h4>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Tracking Progress</h5>
                    </div>
                    <div class="card-body">
                        <!-- Tracking Steps -->
                        <div class="tracking-steps">
                            @foreach($timeline as $key => $step)
                            <div class="step @if($step['completed']) active @endif">
                                <div class="step-indicator">
                                    <i class="bx bx-{{ $step['icon'] }}"></i>
                                </div>
                                <div class="step-content">
                                    <h6>{{ $step['label'] }}</h6>
                                    @if($step['date'])
                                        <p class="text-muted">{{ $step['date']->format('M d, Y h:i A') }}</p>
                                    @else
                                        <p class="text-muted">Pending</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Order Details Card -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                                <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                                <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                                <p><strong>Total Amount:</strong> <strong>${{ number_format($order->total_amount, 2) }}</strong></p>
                                <p><strong>Status:</strong>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-info">Processing</span>
                                            @break
                                        @case('shipped')
                                            <span class="badge bg-primary">Shipped</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge bg-success">Delivered</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Shipping Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-map me-2"></i>Shipping Address</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $order->shipping_address }}</p>
                    </div>
                </div>

                <!-- Order Items Summary -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-package me-2"></i>Items</h6>
                    </div>
                    <div class="card-body">
                        @forelse($order->products as $product)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $product->name }}</span>
                            <strong>{{ $product->pivot->quantity }}x</strong>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No items in this order</p>
                        @endforelse
                    </div>
                </div>

                <!-- Update Status -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-refresh me-2"></i>Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select name="status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tracking-steps {
        position: relative;
    }

    .tracking-steps::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .step {
        display: flex;
        margin-bottom: 30px;
        position: relative;
    }

    .step-indicator {
        width: 40px;
        height: 40px;
        background: #f0f0f0;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        flex-shrink: 0;
        color: #6c757d;
    }

    .step.active .step-indicator {
        background: #198754;
        border-color: #198754;
        color: white;
    }

    .step-content h6 {
        margin: 5px 0;
        font-weight: 600;
        color: #333;
    }

    .step-content p {
        margin: 0;
        font-size: 0.875rem;
    }

    .step.active .step-content h6 {
        color: #198754;
    }
</style>
@endsection
