@extends('admin.layouts.app')
@section('title', 'Product Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Product Details</h4>
                <p class="text-muted mb-0">View and manage product information</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i>Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Products
                </a>
            </div>
        </div>

        <!-- Product Overview Card -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($product->images->count())
                                <img src="{{ $product->images->first()->url }}" alt="Product Image"
                                     class="img-fluid rounded shadow" style="max-width: 250px; max-height: 250px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded shadow d-flex align-items-center justify-content-center"
                                     style="width: 250px; height: 250px; margin: 0 auto;">
                                    <div class="text-muted text-center">
                                        <i class="bx bx-image fs-1"></i>
                                        <p class="mb-0 mt-2">No Image</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if($product->images->count())
                            <div class="row g-2">
                                @foreach($product->images as $img)
                                    <div class="col-3">
                                        <img src="{{ $img->url }}" alt="Product Image {{ $loop->iteration }}"
                                             class="img-fluid rounded shadow" style="width: 100%; height: 100px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <div class="mb-3">
                            <span class="badge bg-{{ $product->status ? 'success' : 'danger' }} fs-6 px-3 py-2">
                                <i class="bx bx-{{ $product->status ? 'check-circle' : 'x-circle' }} me-1"></i>
                                {{ $product->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Price</h6>
                                <h4 class="text-primary mb-0">${{ number_format($product->price, 2) }}</h4>
                            </div>
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Stock</h6>
                                <h4 class="text-info mb-0">{{ $product->quantity ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Product Name</label>
                                    <p class="mb-0 fs-6">{{ $product->name }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Category</label>
                                    <p class="mb-0">
                                        @if($product->category)
                                            <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                        @else
                                            <span class="text-muted">No Category</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Created</label>
                                    <p class="mb-0">{{ $product->created_at->diffForHumans()}}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Last Updated</label>
                                    <p class="mb-0">{{ $product->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-muted">Description</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">{{ $product->description ?: 'No description available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tags and Sections Row -->
        @if($product->tags->count() > 0 || $product->sections->count() > 0)
        <div class="row mt-4">
            @if($product->tags->count() > 0)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-tag me-2"></i>Product Tags</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->tags as $tag)
                                <span class="badge bg-primary px-3 py-2">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($product->sections->count() > 0)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-category me-2"></i>Product Sections</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->sections as $section)
                                <span class="badge bg-info px-3 py-2">{{ $section->label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Product Variants -->
        @if($product->productVariants->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-layer me-2"></i>Product Variants</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="bx bx-cog me-1"></i>Attribute</th>
                                        <th><i class="bx bx-text me-1"></i>Value</th>
                                        <th><i class="bx bx-dollar me-1"></i>Additional Price</th>
                                        <th><i class="bx bx-package me-1"></i>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->productVariants as $variant)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $variant->productAttribute->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $variant->value }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-semibold">
                                                ${{ number_format($variant->additional_price ?? 0, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ $variant->quantity ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Empty State for Variants -->
        @if($product->productVariants->count() == 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-layer text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-3">No Product Variants</h6>
                        <p class="text-muted mb-0">This product doesn't have any variants configured.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
