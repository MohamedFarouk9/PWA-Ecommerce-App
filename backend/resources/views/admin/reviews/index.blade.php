@extends('admin.layouts.app')

@section('title', 'Product Reviews Moderation')

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
                            Reviews Moderation
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="ms-auto d-flex gap-2">
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                    <i class="bx bxs-star me-1"></i> Average Rating: {{ $stats['avg_rating'] }} / 5.0
                </span>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <!-- Review Stats -->
        <div class="row row-cols-1 row-cols-md-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-primary border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bx bx-comment-detail fs-2 text-primary"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                            <div class="text-muted small">Total Reviews</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-success border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bx bx-check-circle fs-2 text-success"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['approved'] }}</div>
                            <div class="text-muted small">Approved</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-warning border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bx bx-time fs-2 text-warning"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['pending'] }}</div>
                            <div class="text-muted small">Pending Moderation</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-danger border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bx bx-x-circle fs-2 text-danger"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['rejected'] }}</div>
                            <div class="text-muted small">Rejected / Spam</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Table Card -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0">Customer Product Reviews</h5>
                    <span class="badge bg-primary ms-2">{{ $reviews->count() }} Total</span>
                </div>

                <div class="table-responsive">
                    <table id="reviewsTable"
                           class="table table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Review Comment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($reviews as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>

                                    <td>
                                        @if($item->product)
                                            <a href="{{ route('admin.products.show', $item->product_id) }}" class="fw-bold text-primary">
                                                {{ Str::limit($item->product->name, 35) }}
                                            </a>
                                        @else
                                            <span class="text-muted">Product #{{ $item->product_id }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-bold">{{ $item->user->name ?? 'Anonymous' }}</div>
                                        <small class="text-muted">{{ $item->user->email ?? 'N/A' }}</small>
                                    </td>

                                    <td>
                                        <div class="text-nowrap">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bx bxs-star {{ $i <= $item->rating ? 'text-warning' : 'text-secondary opacity-25' }}"></i>
                                            @endfor
                                            <span class="ms-1 fw-bold">({{ $item->rating }})</span>
                                        </div>
                                    </td>

                                    <td style="max-width: 300px;">
                                        <div class="text-truncate">
                                            {{ $item->comment }}
                                        </div>
                                    </td>

                                    <td>
                                        @if($item->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($item->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>

                                    <td>
                                        <small title="{{ $item->created_at?->format('Y-m-d H:i') }}">
                                            {{ $item->created_at?->diffForHumans() ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.reviews.show', $item->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="View Full Review">
                                                <i class="bx bx-show"></i>
                                            </a>

                                            @if($item->status !== 'approved')
                                                <form action="{{ route('admin.reviews.approve', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Approve Review">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($item->status !== 'rejected')
                                                <form action="{{ route('admin.reviews.reject', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="Reject / Hide Review">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.reviews.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline delete-file-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete Review">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bx bx-comment-x fs-1 d-block mb-2"></i>
                                        No reviews found.
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
        $('#reviewsTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            paging: true,
            language: {
                search: "Search reviews:",
                lengthMenu: "Show _MENU_ reviews",
                info: "Showing _START_ to _END_ of _TOTAL_ reviews",
                emptyTable: "No reviews available",
                zeroRecords: "No matching reviews found"
            }
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')
@endpush
