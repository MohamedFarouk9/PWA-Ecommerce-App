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
                        <span class="badge bg-light text-secondary border ms-1">Read at {{ $notification->read_at->format('M d, Y h:i A') }}</span>
                    @else
                        <span class="badge bg-warning text-dark ms-1">Unread</span>
                    @endif
                </div>

                <div class="p-3 bg-light rounded mb-4">
                    <p class="mb-0 fs-6">{{ $notification->message }}</p>
                </div>

                @php
                    $payload = !empty($notification->data)
                        ? (is_array($notification->data)
                            ? $notification->data
                            : json_decode($notification->data, true))
                        : null;
                @endphp
                @if(!empty($payload) && is_array($payload))
                <div class="mb-3">
                    <h6 class="fw-bold">Additional Details</h6>
                    <table class="table table-sm table-bordered table-striped w-auto mb-0">
                        <tbody>
                            @foreach($payload as $key => $value)
                            <tr>
                                <th class="text-muted bg-light" style="min-width:150px; white-space:nowrap;">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </th>
                                <td>
                                    @if(is_array($value))
                                        <ul class="mb-0 ps-3">
                                            @foreach($value as $k => $v)
                                                <li>@if(!is_int($k))<strong>{{ ucwords(str_replace('_',' ',$k)) }}:</strong> @endif{{ $v }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
