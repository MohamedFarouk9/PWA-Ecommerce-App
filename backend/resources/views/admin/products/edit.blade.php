@extends('admin.layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Product</h4>
                <p class="text-muted mb-0">Update product information and settings</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info">
                    <i class="bx bx-show me-1"></i>View Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Products
                </a>
            </div>
        </div>

        <form id="productForm" class="ajax-form" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" data-redirect="{{ route('admin.products.index') }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <!-- Basic Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="4">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', $product->price) }}" step="0.01" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                           value="{{ old('quantity', $product->quantity) }}" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="product_category_id" class="form-select @error('product_category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                    {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="archived" {{ old('status', $product->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags and Sections -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-tag me-2"></i>Product Tags</h6>
                                </div>
                                <div class="card-body">
                                    <select name="tags[]" class="form-select" multiple>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}"
                                                    {{ $product->tags->contains($tag->id) ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple tags</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-category me-2"></i>Product Sections</h6>
                                </div>
                                <div class="card-body">
                                    <select name="sections[]" class="form-select" multiple>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}"
                                                    {{ $product->sections->contains($section->id) ? 'selected' : '' }}>
                                                {{ $section->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple sections</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bx bx-layer me-2"></i>Product Variants</h6>
                            <button type="button" id="add-variant" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-plus me-1"></i>Add Variant
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" id="variants-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 28%">Attribute</th>
                                            <th style="width: 28%">Value</th>
                                            <th style="width: 18%">Additional Price</th>
                                            <th style="width: 18%">Quantity</th>
                                            <th style="width: 8%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Existing variants and new rows will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block mt-2">Leave variants empty if the product has no variations.</small>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Image Upload -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-image me-2"></i>Product Image</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($product->images->count())
                                    <div class="row g-2">
                                        @foreach($product->images as $img)
                                            <div class="col-4">
                                                <img src="{{ $img->url }}" alt="Product Image {{ $loop->iteration }}"
                                                     class="img-fluid rounded shadow" style="width: 100%; height: 120px; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-light rounded shadow d-flex align-items-center justify-content-center"
                                         style="width: 200px; height: 200px; margin: 0 auto;">
                                        <div class="text-muted text-center">
                                            <i class="bx bx-image fs-1"></i>
                                            <p class="mb-0 mt-2">No Images</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="images[]" id="images" class="form-control @error('images') is-invalid @enderror"
                                   accept="image/*" multiple>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">Upload new images to replace the current product gallery.</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Recommended: 400x400px or larger</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-save me-2"></i>Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Update Product
                                </button>
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        $("#images").change(function(e){
            var file = e.target.files[0];
            if (!file) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        });

        // Variants functionality
        const attributes = @json($attributes ?? []);
        const existingVariants = @json($product->productVariants ?? []);
        const tbody = document.querySelector('#variants-table tbody');
        const addBtn = document.getElementById('add-variant');
        let variantIndex = 0;

        function renderAttributeOptions() {
            return attributes.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
        }

        function addVariantRow(variant = null) {
            const tr = document.createElement('tr');
            const isEdit = variant !== null;
            const selectedAttribute = isEdit ? variant.product_attribute_id : '';
            const selectedValue = isEdit ? variant.value : '';
            const selectedPrice = isEdit ? (variant.additional_price || '') : '';
            const selectedQuantity = isEdit ? (variant.quantity || '') : '';

            tr.innerHTML = `
                <td>
                    <select name="variants[${variantIndex}][product_attribute_id]" class="form-select">
                        <option value="">Select attribute</option>
                        ${renderAttributeOptions().replace(`value="${selectedAttribute}"`, `value="${selectedAttribute}" selected`)}
                    </select>
                </td>
                <td>
                    <input type="text" name="variants[${variantIndex}][value]" class="form-control"
                           placeholder="e.g. Red, XL" value="${selectedValue}" />
                </td>
                <td>
                    <input type="number" step="0.01" name="variants[${variantIndex}][additional_price]"
                           class="form-control" placeholder="0.00" value="${selectedPrice}" />
                </td>
                <td>
                    <input type="number" name="variants[${variantIndex}][quantity]" class="form-control"
                           placeholder="e.g. 10" value="${selectedQuantity}" />
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            variantIndex++;
        }

        // Add existing variants
        existingVariants.forEach(variant => {
            addVariantRow(variant);
        });

        // Add new variant button
        addBtn?.addEventListener('click', () => addVariantRow());

        // Remove variant functionality
        tbody?.addEventListener('click', function(e){
            if(e.target.closest('.remove-variant')){
                const row = e.target.closest('tr');
                row?.parentNode?.removeChild(row);
                variantIndex--;
            }
        });

        // Hide variants section if no attributes available
        if (attributes.length === 0) {
            $('.card:contains("Product Variants")').hide();
        }
    });
</script>
@include('admin.components.ajax-form-handler')
@endpush
