@extends('admin.layouts.app')
@section('title', 'Edit Order - ' . $order->order_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Edit Order</h4>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Order Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                        <option value="">Select Payment Status</option>
                                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shipping Address</label>
                                <textarea name="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" rows="3">{{ $order->shipping_address }}</textarea>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Billing Address</label>
                                <textarea name="billing_address" class="form-control @error('billing_address') is-invalid @enderror" rows="3">{{ $order->billing_address }}</textarea>
                                @error('billing_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ $order->notes }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Order</button>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Order Summary</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                        <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                        <hr>
                        <p><strong>Total Amount:</strong></p>
                        <h4 class="text-primary">${{ number_format($order->total_amount, 2) }}</h4>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Order Items ({{ $order->products->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($order->products as $product)
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <div>
                                <p class="mb-0"><strong>{{ $product->name }}</strong></p>
                                <small class="text-muted">Qty: {{ $product->pivot->quantity }}</small>
                            </div>
                            <p class="mb-0">${{ number_format($product->pivot->total, 2) }}</p>
                        </div>
                        @empty
                        <p class="text-muted">No items in this order</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
