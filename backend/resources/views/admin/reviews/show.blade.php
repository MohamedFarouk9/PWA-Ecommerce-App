@extends('admin.layouts.app')

@section('title', 'Review Details — #' . $review->id)

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.reviews.index') }}">Reviews</a>
                        </li>
                        <li class="breadcrumb-item active">#{{ $review->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Reviews
                </a>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            <!-- Left: Review Card -->
            <div class="col-12 col-xl-8">
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">Customer Review #{{ $review->id }}</h5>
                            <small class="text-muted">Submitted {{ $review->created_at->format('M d, Y h:i A') }} ({{ $review->created_at->diffForHumans() }})</small>
                        </div>
                        <div class="ms-auto">
                            @if($review->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($review->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Rating -->
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <div class="fs-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bx bxs-star {{ $i <= $review->rating ? 'text-warning' : 'text-secondary opacity-25' }}"></i>
                                @endfor
                            </div>
                            <span class="fs-5 fw-bold ms-2">{{ $review->rating }} out of 5 stars</span>
                        </div>

                        <!-- Review Text -->
                        <div class="p-4 bg-light rounded mb-4" style="font-size: 1.1rem; line-height: 1.8; white-space: pre-wrap;">{{ $review->comment }}</div>

                        <!-- Moderation Controls -->
                        <div class="card border mb-0">
                            <div class="card-header bg-transparent fw-bold">
                                <i class="bx bx-shield-quarter me-1"></i> Moderation Actions
                            </div>
                            <div class="card-body d-flex gap-2">
                                @if($review->status !== 'approved')
                                    <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="bx bx-check me-1"></i> Approve & Publish Review
                                        </button>
                                    </form>
                                @endif

                                @if($review->status !== 'rejected')
                                    <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-x me-1"></i> Reject & Hide Review
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST"
                                      onsubmit="return confirm('Permanently delete this review?');" class="ms-auto">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bx bx-trash me-1"></i> Delete Review
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Product & Customer info -->
            <div class="col-12 col-xl-4">
                <!-- Product Card -->
                <div class="card radius-10 mb-3">
                    <div class="card-header bg-transparent fw-bold">
                        <i class="bx bx-package me-1"></i> Product Information
                    </div>
                    <div class="card-body">
                        @if($review->product)
                            <h6>{{ $review->product->name }}</h6>
                            <p class="text-muted small mb-2">Price: ${{ number_format($review->product->price, 2) }}</p>
                            <a href="{{ route('admin.products.show', $review->product_id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-show me-1"></i> View Product in Admin
                            </a>
                        @else
                            <p class="text-muted mb-0">Product #{{ $review->product_id }} (Not found or deleted)</p>
                        @endif
                    </div>
                </div>

                <!-- Customer Card -->
                <div class="card radius-10">
                    <div class="card-header bg-transparent fw-bold">
                        <i class="bx bx-user me-1"></i> Customer Information
                    </div>
                    <div class="card-body">
                        @if($review->user)
                            <h6>{{ $review->user->name }}</h6>
                            <p class="text-muted small mb-2">{{ $review->user->email }}</p>
                            <a href="{{ route('admin.users.show', $review->user_id) }}" class="btn btn-outline-info btn-sm">
                                <i class="bx bx-user me-1"></i> View Customer Profile
                            </a>
                        @else
                            <p class="text-muted mb-0">Anonymous / Deleted Customer</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
