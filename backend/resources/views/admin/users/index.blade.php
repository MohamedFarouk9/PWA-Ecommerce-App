@extends('admin.layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Header -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Customers</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            Customer Management
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="bx bx-user me-1"></i> {{ $stats['total'] ?? $users->count() }} Registered Customers
                </span>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <!-- Stats Overview Cards -->
        <div class="row row-cols-1 row-cols-md-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-primary border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bx bx-group fs-2 text-primary"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total'] ?? $users->count() }}</div>
                            <div class="text-muted small">Total Customers</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-success border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bx bx-user-check fs-2 text-success"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['active'] ?? 0 }}</div>
                            <div class="text-muted small">Active (Ordered)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-info border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bx bx-user-plus fs-2 text-info"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['new_this_month'] ?? 0 }}</div>
                            <div class="text-muted small">New This Month</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-warning border-4 mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bx bx-dollar-circle fs-2 text-warning"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold">${{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
                            <div class="text-muted small">Customer Revenue</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table Card -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0">All Customers</h5>
                    <span class="badge bg-primary ms-2">
                        {{ $users->count() }} Total
                    </span>
                </div>

                <div class="table-responsive">
                    <table id="usersTable"
                           class="table table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $user->image }}" alt="{{ $user->name }}"
                                                 class="rounded-circle border" width="40" height="40"
                                                 style="object-fit: cover;">
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div>{{ $user->phone ?? 'N/A' }}</div>
                                        <small class="text-muted">
                                            {{ collect([$user->city, $user->country])->filter()->implode(', ') ?: 'Location N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-primary">Customer</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($user->status === 'banned')
                                            <span class="badge bg-danger">Banned</span>
                                        @elseif($user->status === 'inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="fw-bold">{{ $user->orders_count }}</span>
                                        <small class="text-muted">orders</small>
                                    </td>

                                    <td>
                                        <span class="fw-bold text-success">${{ number_format($user->total_spent ?? 0, 2) }}</span>
                                    </td>

                                    <td>
                                        <small title="{{ $user->created_at?->format('Y-m-d H:i') }}">
                                            {{ $user->created_at?->diffForHumans() ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.users.show', $user->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="View Profile">
                                                <i class="bx bx-show"></i>
                                            </a>

                                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit Details">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.toggle-status', $user->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $user->status === 'banned' ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                            title="{{ $user->status === 'banned' ? 'Activate Customer' : 'Ban Customer' }}">
                                                        <i class="bx {{ $user->status === 'banned' ? 'bx-check-circle' : 'bx-block' }}"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                                      method="POST"
                                                      class="d-inline delete-file-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-user-x fs-1 d-block mb-2"></i>
                                        No customers found.
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
        $('#usersTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            paging: true,
            language: {
                search: "Search customers:",
                lengthMenu: "Show _MENU_ customers",
                info: "Showing _START_ to _END_ of _TOTAL_ customers",
                emptyTable: "No customers available",
                zeroRecords: "No matching customers found"
            }
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')
@endpush
