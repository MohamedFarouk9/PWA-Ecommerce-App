@extends('admin.layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- DataTables CSS -->
        <link rel="stylesheet"
              href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">

        <link rel="stylesheet"
              href="{{ asset('admin/assets/plugins/datatable/css/buttons.bootstrap5.min.css') }}">

        <!-- Breadcrumb -->
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
        </div>

        @include('admin.components.flash-messages')

        <!-- Statistics -->
        <div class="row row-cols-1 row-cols-md-4 mb-4">

            <div class="col">
                <div class="card radius-10 border-start border-primary border-4">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bx bx-group fs-2 text-primary"></i>
                        </div>

                        <div>
                            <div class="fs-4 fw-bold">
                                {{ $stats['total'] ?? 0 }}
                            </div>

                            <div class="text-muted small">
                                Total Customers
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-success border-4">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bx bx-user-check fs-2 text-success"></i>
                        </div>

                        <div>
                            <div class="fs-4 fw-bold">
                                {{ $stats['active'] ?? 0 }}
                            </div>

                            <div class="text-muted small">
                                Active Customers
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-info border-4">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bx bx-user-plus fs-2 text-info"></i>
                        </div>

                        <div>
                            <div class="fs-4 fw-bold">
                                {{ $stats['new_this_month'] ?? 0 }}
                            </div>

                            <div class="text-muted small">
                                New This Month
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-warning border-4">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bx bx-dollar-circle fs-2 text-warning"></i>
                        </div>

                        <div>
                            <div class="fs-4 fw-bold">
                                ${{ number_format($stats['total_revenue'] ?? 0, 2) }}
                            </div>

                            <div class="text-muted small">
                                Total Customer Revenue
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Customers Table -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-group me-2 text-primary"></i>
                        All Customers
                    </h5>
                </div>

                <hr>

                <div class="table-responsive">
                    <table id="customersTable"
                           class="table table-hover table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        {{ $user->id }}
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $user->image }}"
                                                 alt="{{ $user->name }}"
                                                 class="rounded-circle"
                                                 width="40"
                                                 height="40"
                                                 style="object-fit: cover;">

                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $user->name }}
                                                </div>

                                                <div class="text-muted small">
                                                    ID #{{ $user->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div>
                                            {{ $user->email }}
                                        </div>

                                        @if($user->phone)
                                            <div class="text-muted small">
                                                {{ $user->phone }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">
                                                Admin
                                            </span>
                                        @else
                                            <span class="badge bg-primary">
                                                Customer
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="fw-bold">
                                            {{ $user->orders_count ?? 0 }}
                                        </span>

                                        <span class="text-muted small">
                                            orders
                                        </span>
                                    </td>

                                    <td>
                                        <span class="fw-semibold text-success">
                                            ${{ number_format($user->total_spent ?? 0, 2) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($user->created_at)
                                            <div class="small">
                                                {{ $user->created_at->format('d M Y') }}
                                            </div>

                                            <div class="text-muted"
                                                 style="font-size: 11px;">
                                                {{ $user->created_at->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                N/A
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">

                                            <a href="{{ route('admin.users.show', $user->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Profile">
                                                <i class="bx bx-show"></i>
                                            </a>

                                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                            @if($user->id !== auth()->id())
                                                <form method="POST"
                                                      action="{{ route('admin.users.destroy', $user->id) }}"
                                                      class="delete-file-form"
                                                      onsubmit="return confirm('Delete this customer? This cannot be undone.')">

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
                                    <td colspan="8"
                                        class="text-center py-5 text-muted">
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

<!-- DataTables JavaScript -->
<script src="{{ asset('admin/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>

<script src="{{ asset('admin/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $('#customersTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            paging: true,
            info: true,

            language: {
                search: 'Search customers:',
                lengthMenu: 'Show _MENU_ customers',
                info: 'Showing _START_ to _END_ of _TOTAL_ customers',
                zeroRecords: 'No matching customers found',
                emptyTable: 'No customers available'
            },

            columnDefs: [
                {
                    orderable: false,
                    targets: [7]
                }
            ]
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')

@endpush