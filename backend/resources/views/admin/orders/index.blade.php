@extends('admin.layouts.app')
@section('title', 'Orders')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Orders</h4>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">Create Order</a>
            </div>

            <!-- Order Statistics -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>{{ $stats['total_orders'] }}</h5>
                            <p class="text-muted mb-0">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>${{ number_format($stats['total_revenue'], 2) }}</h5>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>{{ $stats['pending_orders'] }}</h5>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>{{ $stats['completed_orders'] }}</h5>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>{{ $stats['cancelled_orders'] }}</h5>
                            <p class="text-muted mb-0">Cancelled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>${{ number_format($stats['average_order_value'], 2) }}</h5>
                            <p class="text-muted mb-0">Avg Value</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTables CSS -->
            <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
            <link href="{{ asset('admin/assets/plugins/datatable/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" />

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user->name ?? 'N/A' }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
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
                                </td>
                                <td>
                                    @if($order->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($order->payment_status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($order->payment_status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-secondary">Refunded</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex order-actions gap-2">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.tracking', $order->id) }}" class="btn btn-sm btn-primary" title="Track Order">
                                            <i class="bx bx-map"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline delete-file-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                lengthChange: true,
                buttons: ['copy', 'excel', 'pdf', 'print']
            });
        });
    </script>

    @include('admin.components.ajax-delete-file-handler')
@endpush
