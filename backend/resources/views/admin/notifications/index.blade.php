@extends('admin.layouts.app')

@section('title', 'Admin Notifications')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Header -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Notifications</div>

            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            System & Customer Notifications
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="ms-auto">
                @if($unreadCount > 0)
                    <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                        @csrf

                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-check-double me-1"></i>
                            Mark All as Read ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @include('admin.components.flash-messages')

        <!-- Notifications Card -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0">All Notifications</h5>

                    <span class="badge bg-primary ms-2">
                        {{ $notifications->count() }} Total
                    </span>
                </div>

                <div class="table-responsive">
                    <table id="notificationsTable"
                           class="table table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Recipient</th>
                                <th>Type</th>
                                <th>Title & Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($notifications as $item)
                                <tr class="{{ is_null($item->read_at) ? 'table-light fw-semibold' : '' }}">

                                    <td>
                                        {{ $item->id }}
                                    </td>

                                    <td>
                                        <div>
                                            {{ $item->user->name ?? 'User #' . $item->user_id }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $item->user->email ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge
                                            {{
                                                $item->type === 'order'
                                                    ? 'bg-success'
                                                    : ($item->type === 'promotional'
                                                        ? 'bg-info'
                                                        : 'bg-secondary')
                                            }}">
                                            {{ ucfirst($item->type ?? 'general') }}
                                        </span>
                                    </td>

                                    <td style="max-width: 320px;">
                                        <div class="text-truncate fw-bold">
                                            {{ $item->title }}
                                        </div>

                                        <small class="text-muted text-truncate d-block">
                                            {{ $item->message }}
                                        </small>
                                    </td>

                                    <td>
                                        @if($item->read_at)
                                            <span class="badge bg-light text-secondary border">
                                                Read
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                Unread
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <small>
                                            {{ $item->created_at?->diffForHumans() ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">

                                            <a href="{{ route('admin.notifications.show', $item->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="View Details">
                                                <i class="bx bx-show"></i>
                                            </a>

                                            @if(is_null($item->read_at))
                                                <form action="{{ route('admin.notifications.mark-read', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Mark as Read">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.notifications.destroy', $item->id) }}"
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

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="text-center py-4 text-muted">
                                        <i class="bx bx-bell-off fs-1 d-block mb-2"></i>
                                        No notifications found.
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
        $('#notificationsTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            paging: true,
            language: {
                search: "Search notifications:",
                lengthMenu: "Show _MENU_ notifications",
                info: "Showing _START_ to _END_ of _TOTAL_ notifications",
                emptyTable: "No notifications available",
                zeroRecords: "No matching notifications found"
            }
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')

@endpush