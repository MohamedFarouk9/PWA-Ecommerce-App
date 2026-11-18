@extends('admin.layouts.app')

@section('title', 'Contact Messages Inbox')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Header -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Support</div>

            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            Contact Messages Inbox
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="ms-auto d-flex gap-2">
                @if($unreadCount > 0)
                    <form action="{{ route('admin.contacts.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-check-double me-1"></i>
                            Mark All Read ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @include('admin.components.flash-messages')

        <!-- Contacts Card -->
        <div class="card radius-10">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0">Customer Inquiries</h5>
                    <span class="badge bg-primary ms-2">{{ $contacts->count() }} Total</span>
                    @if($unreadCount > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadCount }} Unread</span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table id="contactsTable"
                           class="table table-striped table-bordered align-middle"
                           style="width: 100%;">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sender</th>
                                <th>Message Snippet</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($contacts as $item)
                                <tr class="{{ !$item->is_read ? 'table-light fw-semibold' : '' }}">
                                    <td>{{ $item->id }}</td>

                                    <td>
                                        <div class="fw-bold">{{ $item->name }}</div>
                                        <small class="text-muted">{{ $item->email }}</small>
                                        @if($item->user)
                                            <span class="badge bg-light text-primary border ms-1">Customer #{{ $item->user_id }}</span>
                                        @endif
                                    </td>

                                    <td style="max-width: 360px;">
                                        <div class="text-truncate">
                                            {{ $item->message }}
                                        </div>
                                        @if($item->replied_at)
                                            <small class="text-success d-block">
                                                <i class="bx bx-reply me-1"></i>Replied {{ $item->replied_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$item->is_read)
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        @elseif($item->replied_at)
                                            <span class="badge bg-success">Replied</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">Read</span>
                                        @endif
                                    </td>

                                    <td>
                                        <small title="{{ $item->created_at?->format('Y-m-d H:i') }}">
                                            {{ $item->created_at?->diffForHumans() ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.contacts.show', $item->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="View & Reply">
                                                <i class="bx bx-show"></i>
                                            </a>

                                            <form action="{{ route('admin.contacts.mark-read', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm {{ $item->is_read ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                        title="{{ $item->is_read ? 'Mark as Unread' : 'Mark as Read' }}">
                                                    <i class="bx {{ $item->is_read ? 'bx-envelope' : 'bx-envelope-open' }}"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.contacts.destroy', $item->id) }}"
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
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bx bx-mail-send fs-1 d-block mb-2"></i>
                                        No messages found.
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
        $('#contactsTable').DataTable({
            pageLength: 20,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            paging: true,
            language: {
                search: "Search inquiries:",
                lengthMenu: "Show _MENU_ messages",
                info: "Showing _START_ to _END_ of _TOTAL_ messages",
                emptyTable: "No contact messages available",
                zeroRecords: "No matching messages found"
            }
        });
    });
</script>

@include('admin.components.ajax-delete-file-handler')
@endpush
