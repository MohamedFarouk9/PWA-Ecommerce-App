@extends('admin.layouts.app')
@section('title', 'Notification Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Notifications</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $notification->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Notifications
                </a>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">{{ $notification->title }}</h5>
                <h6 class="card-subtitle mb-3 text-muted">
                    Sent to: <strong>{{ $notification->user->name ?? 'User #' . $notification->user_id }}</strong> ({{ $notification->user->email ?? 'N/A' }}) &bull; {{ $notification->created_at ? $notification->created_at->format('M d, Y h:i A') : 'N/A' }}
                </h6>
                <div class="mb-3">
                    <span class="badge {{ $notification->type === 'order' ? 'bg-success' : 'bg-info' }}">
                        {{ ucfirst($notification->type ?? 'general') }}
                    </span>
                    @if($notification->read_at)
                        <span class="badge bg-light text-secondary border ms-1">Read at {{ $notification->read_at }}</span>
                    @else
                        <span class="badge bg-warning text-dark ms-1">Unread</span>
                    @endif
                </div>

                <div class="p-3 bg-light rounded mb-4">
                    <p class="mb-0 fs-6">{{ $notification->message }}</p>
                </div>

                @if(!empty($notification->data))
                    <div class="mb-3">
                        <h6 class="fw-bold">Payload Data:</h6>
                        <pre class="bg-dark text-white p-3 rounded" style="max-height: 250px;"><code>{{ is_array($notification->data) ? json_encode($notification->data, JSON_PRETTY_PRINT) : $notification->data }}</code></pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
