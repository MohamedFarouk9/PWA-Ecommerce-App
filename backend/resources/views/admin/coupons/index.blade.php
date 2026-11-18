@extends('admin.layouts.app')

@section('title', 'Coupons & Discounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Header -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">eCommerce</div>

            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            Coupons & Discounts
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="ms-auto">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Create New Coupon
                </a>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <!-- Coupon Stats Cards -->
        <div class="row row-cols-1 row-cols-md-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-primary border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bx bx-purchase-tag-alt fs-2 text-primary"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                            <div class="text-muted small">Total Coupons</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-success border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bx bx-check-shield fs-2 text-success"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['active'] }}</div>
                            <div class="text-muted small">Active Coupons</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-info border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bx bx-check-double fs-2 text-info"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total_used'] }}</div>
                            <div class="text-muted small">Times Redeemed</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-warning border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bx bx-dollar fs-2 text-warning"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">${{ number_format($stats['total_discount'], 2) }}</div>
                            <div class="text-muted small">Total Discount Given</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coupons Table Card -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0">All Discount Codes</h5>
                    <span class="badge bg-primary ms-2">{{ $coupons->count() }} Total</span>
                </div>

                <div class="table-responsive">
                    <table id="couponsTable"
                           class="table table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Min. Spend</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($coupons as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>

                                    <td>
                                        <span class="badge bg-dark fs-6 px-2 py-1 font-monospace">{{ $item->code }}</span>
                                        @if($item->description)
                                            <small class="text-muted d-block mt-1">{{ $item->description }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->discount_type === 'percentage')
                                            <span class="fw-bold text-success">{{ number_format($item->discount_amount, 0) }}% OFF</span>
                                            @if($item->max_discount)
                                                <small class="text-muted d-block">Up to ${{ number_format($item->max_discount, 2) }}</small>
                                            @endif
                                        @else
                                            <span class="fw-bold text-success">${{ number_format($item->discount_amount, 2) }} OFF</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->min_spend > 0)
                                            <span>${{ number_format($item->min_spend, 2) }}</span>
                                        @else
                                            <span class="text-muted">No min</span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ $item->used_count }}</strong>
                                        @if($item->usage_limit)
                                            <span class="text-muted">/ {{ $item->usage_limit }}</span>
                                        @else
                                            <span class="text-muted">/ &infin;</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$item->starts_at && !$item->expires_at)
                                            <span class="badge bg-light text-dark border">Always Valid</span>
                                        @else
                                            <small class="d-block">
                                                From: {{ $item->starts_at ? $item->starts_at->format('M d, Y') : 'Immediate' }}
                                            </small>
                                            <small class="d-block text-{{ $item->expires_at && $item->expires_at->isPast() ? 'danger' : 'muted' }}">
                                                To: {{ $item->expires_at ? $item->expires_at->format('M d, Y') : 'No expiry' }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$item->is_active)
                                            <span class="badge bg-secondary">Inactive</span>
                                        @elseif($item->expires_at && $item->expires_at->isPast())
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($item->usage_limit && $item->used_count >= $item->usage_limit)
                                            <span class="badge bg-warning text-dark">Exhausted</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.coupons.edit', $item->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit Coupon">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                            <form action="{{ route('admin.coupons.toggle-status', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                        title="{{ $item->is_active ? 'Deactivate Coupon' : 'Activate Coupon' }}">
                                                    <i class="bx {{ $item->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.coupons.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline delete-file-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete Coupon">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bx bx-purchase-tag-alt fs-1 d-block mb-2"></i>
                                        No coupons found. Click "Create New Coupon" to add one!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet"
      href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">

<!-- DataTables JavaScript -->
<script src="{{ asset('admin/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $('#couponsTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            paging: true,
            language: {
                search: "Search coupons:",
                lengthMenu: "Show _MENU_ coupons",
                info: "Showing _START_ to _END_ of _TOTAL_ coupons",
                emptyTable: "No coupons available",
                zeroRecords: "No matching coupons found"
            }
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')
@endpush
