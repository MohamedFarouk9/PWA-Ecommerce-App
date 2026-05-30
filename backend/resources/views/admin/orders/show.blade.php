@extends('admin.layouts.app')
@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Order Details</h4>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            <!-- Order Information -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="mb-2">Order Status</h6>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge bg-warning" style="font-size: 14px;">Pending</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-info" style="font-size: 14px;">Processing</span>
                                        @break
                                    @case('shipped')
                                        <span class="badge bg-primary" style="font-size: 14px;">Shipped</span>
                                        @break
                                    @case('delivered')
                                        <span class="badge bg-success" style="font-size: 14px;">Delivered</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger" style="font-size: 14px;">Cancelled</span>
                                        @break
                                @endswitch
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Payment Status</h6>
                                @if($order->payment_status === 'paid')
                                    <span class="badge bg-success" style="font-size: 14px;">Paid</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="badge bg-warning" style="font-size: 14px;">Pending</span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="badge bg-danger" style="font-size: 14px;">Failed</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 14px;">Refunded</span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                                <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                                <p><strong>Total Amount:</strong> <strong>${{ number_format($order->total_amount, 2) }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($order->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->pivot->quantity }}</td>
                                        <td>${{ number_format($product->pivot->price, 2) }}</td>
                                        <td>${{ number_format($product->pivot->total, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No items in this order</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $order->notes ?? 'No notes added.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Shipping Address -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $order->shipping_address }}</p>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Billing Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $order->billing_address }}</p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="small text-muted mb-0">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @if($order->shipped_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Shipped</h6>
                                    <p class="small text-muted mb-0">{{ $order->shipped_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @endif
                            @if($order->delivered_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Delivered</h6>
                                    <p class="small text-muted mb-0">{{ $order->delivered_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3">
                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bx bx-edit me-2"></i>Edit Order
                    </a>
                    <a href="{{ route('admin.orders.tracking', $order->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="bx bx-map me-2"></i>Track Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-item {
        display: flex;
        margin-bottom: 20px;
        position: relative;
    }

    .timeline-marker {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 15px;
        margin-top: 5px;
        flex-shrink: 0;
    }

    .timeline-content h6 {
        margin: 0;
        font-size: 0.9rem;
    }
</style>
@endsection
