@extends('admin.layouts.app')

@section('title', 'Inquiry Details — ' . $contact->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.contacts.index') }}">Contact Messages</a>
                        </li>
                        <li class="breadcrumb-item active">#{{ $contact->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Inbox
                </a>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            <!-- Left: Message Content -->
            <div class="col-12 col-xl-8">
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">Message from {{ $contact->name }}</h5>
                            <small class="text-muted">Received {{ $contact->created_at->format('M d, Y h:i A') }} ({{ $contact->created_at->diffForHumans() }})</small>
                        </div>
                        <div class="ms-auto">
                            @if($contact->replied_at)
                                <span class="badge bg-success">Replied</span>
                            @else
                                <span class="badge bg-light text-secondary border">Read</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="p-4 bg-light rounded mb-4" style="font-size: 1.05rem; line-height: 1.7; white-space: pre-wrap;">{{ $contact->message }}</div>

                        @if($contact->reply_message)
                            <div class="border-start border-success border-4 ps-3 py-2 bg-light rounded mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <strong class="text-success"><i class="bx bx-reply me-1"></i>Admin Response</strong>
                                    <small class="text-muted ms-auto">{{ $contact->replied_at?->format('M d, Y h:i A') }}</small>
                                </div>
                                <div style="white-space: pre-wrap;">{{ $contact->reply_message }}</div>
                            </div>
                        @endif

                        <!-- Reply Form -->
                        <div class="card border mb-0">
                            <div class="card-header bg-transparent fw-bold">
                                <i class="bx bx-paper-plane me-1"></i> {{ $contact->reply_message ? 'Update Response / Note' : 'Respond / Add Internal Note' }}
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.contacts.reply', $contact->id) }}" method="POST" class="ajax-form">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="reply_message" rows="4" class="form-control"
                                                  placeholder="Type your response or internal follow-up notes here..." required>{{ old('reply_message', $contact->reply_message) }}</textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="mailto:{{ $contact->email }}?subject=Re: Your inquiry on our store"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-envelope me-1"></i> Open in Email Client
                                        </a>
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bx bx-check me-1"></i> Save Response
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right: Sender Details -->
            <div class="col-12 col-xl-4">
                <div class="card radius-10">
                    <div class="card-header bg-transparent fw-bold">
                        <i class="bx bx-user me-1"></i> Sender Information
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 100px;">Name</td>
                                    <td class="fw-bold">{{ $contact->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>
                                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Registered</td>
                                    <td>
                                        @if($contact->user)
                                            <a href="{{ route('admin.users.show', $contact->user_id) }}" class="badge bg-primary">
                                                View Account (#{{ $contact->user_id }})
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">Guest User</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>
                                        @if(!$contact->is_read)
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        @elseif($contact->replied_at)
                                            <span class="badge bg-success">Replied</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">Read</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Received</td>
                                    <td>{{ $contact->created_at->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.contacts.mark-read', $contact->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bx {{ $contact->is_read ? 'bx-envelope' : 'bx-envelope-open' }} me-1"></i>
                                    {{ $contact->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                                </button>
                            </form>

                            <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bx bx-trash me-1"></i> Delete Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
