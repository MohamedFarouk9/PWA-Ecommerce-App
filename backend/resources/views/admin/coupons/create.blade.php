@extends('admin.layouts.app')

@section('title', 'Create New Coupon')

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
                            <a href="{{ route('admin.coupons.index') }}">Coupons</a>
                        </li>
                        <li class="breadcrumb-item active">Create Coupon</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Coupons
                </a>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <div class="row justify-content-center">
            <div class="col-12 col-xl-9">
                <div class="card radius-10">
                    <div class="card-header bg-transparent fw-bold fs-6 py-3">
                        <i class="bx bx-purchase-tag-alt me-1 text-primary"></i> Create Promotional Coupon
                    </div>
                    <div class="card-body p-4">

                        <form class="ajax-form" action="{{ route('admin.coupons.store') }}" method="POST" data-redirect="{{ route('admin.coupons.index') }}">
                            @csrf

                            <div class="row g-3">

                                <!-- Coupon Code -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Coupon Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-tag"></i></span>
                                        <input type="text" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror"
                                               placeholder="e.g. SAVE20 or FIRST10" value="{{ old('code') }}" required>
                                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <small class="text-muted">Unique code customers enter at checkout.</small>
                                </div>

                                <!-- Description -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Description / Campaign</label>
                                    <input type="text" name="description" class="form-control"
                                           placeholder="e.g. 20% off for Summer Sale" value="{{ old('description') }}">
                                </div>

                                <!-- Discount Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                                    <select name="discount_type" id="discountTypeSelect" class="form-select" required>
                                        <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage Discount (%)</option>
                                        <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount Discount ($)</option>
                                    </select>
                                </div>

                                <!-- Discount Amount -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="discountPrefix">%</span>
                                        <input type="number" step="0.01" min="0.01" name="discount_amount"
                                               class="form-control @error('discount_amount') is-invalid @enderror"
                                               placeholder="e.g. 20" value="{{ old('discount_amount') }}" required>
                                        @error('discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <!-- Minimum Spend -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Minimum Order Spend ($)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" name="min_spend" class="form-control"
                                               placeholder="0.00 (leave 0 for no minimum)" value="{{ old('min_spend', '0.00') }}">
                                    </div>
                                    <small class="text-muted">Cart subtotal required before coupon applies.</small>
                                </div>

                                <!-- Maximum Discount Cap (Percentage only) -->
                                <div class="col-md-6" id="maxDiscountGroup">
                                    <label class="form-label fw-bold">Maximum Discount Cap ($)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" name="max_discount" class="form-control"
                                               placeholder="Leave blank for no limit" value="{{ old('max_discount') }}">
                                    </div>
                                    <small class="text-muted">Optional cap on percentage discounts.</small>
                                </div>

                                <!-- Usage Limit (Global) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Total Usage Limit</label>
                                    <input type="number" min="1" name="usage_limit" class="form-control"
                                           placeholder="Leave blank for unlimited" value="{{ old('usage_limit') }}">
                                    <small class="text-muted">Total number of times this coupon can be redeemed.</small>
                                </div>

                                <!-- Usage Per User -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Usage Limit Per Customer</label>
                                    <input type="number" min="1" name="usage_per_user" class="form-control"
                                           placeholder="1" value="{{ old('usage_per_user', '1') }}">
                                    <small class="text-muted">How many times each individual user can use it.</small>
                                </div>

                                <!-- Start Date -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Start Date & Time</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                                    <small class="text-muted">Leave empty to activate immediately.</small>
                                </div>

                                <!-- Expiry Date -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Expiry Date & Time</label>
                                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                                    <small class="text-muted">Leave empty for no expiration.</small>
                                </div>

                                <!-- Active Checkbox -->
                                <div class="col-12 mt-3">
                                    <div class="form-check form-switch fs-6">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="isActiveCheck">Coupon is Active</label>
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-check me-1"></i> Create Coupon
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('discountTypeSelect').addEventListener('change', function() {
        var prefix = document.getElementById('discountPrefix');
        var maxGroup = document.getElementById('maxDiscountGroup');
        if (this.value === 'percentage') {
            prefix.innerText = '%';
            maxGroup.style.display = 'block';
        } else {
            prefix.innerText = '$';
            maxGroup.style.display = 'none';
        }
    });
</script>
@endpush
