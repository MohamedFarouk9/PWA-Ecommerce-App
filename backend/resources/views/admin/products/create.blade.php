@extends('admin.layouts.app')
@section('title', 'Add Product')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Add New Product</h4>
                    <p class="text-muted mb-0">Create a new product and assign details</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary"><i
                        class="bx bx-arrow-back me-1"></i>Back to Products</a>
            </div>

            <form id="productForm" class="ajax-form" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" data-redirect="{{ route('admin.products.index') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="price" class="form-control" step="0.01"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="product_category_id" class="form-select">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="published">Published</option>
                                            <option value="draft" selected>Draft</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="bx bx-tag me-2"></i>Product Tags</h6>
                                    </div>
                                    <div class="card-body">
                                        <select name="tags[]" class="form-select" multiple>
                                            @foreach ($tags as $tag)
                                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
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
                                            @foreach ($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->label }}</option>
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
                                            <!-- rows injected by JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted d-block mt-2">Leave variants empty if the product has no
                                    variations.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-image me-2"></i>Product Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <img id="showImage" src="" alt="Preview" class="img-fluid rounded shadow"
                                        style="max-width: 200px; max-height: 200px; object-fit: cover; display: none;">
                                </div>
                                <input type="file" name="images[]" id="images" class="form-control"
                                    accept="image/*" multiple>
                                <small class="text-muted d-block mt-2">You may upload multiple product images.</small>
                                <small class="text-muted">Recommended: 400x400px or larger</small>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-save me-2"></i>Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i>Save Product
                                    </button>
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
        $(document).ready(function() {
            $("#images").change(function(e) {
                var file = e.target.files[0];
                if (!file) {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#showImage').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            });

            // Variants dynamic rows
            const attributes = @json($attributes ?? []);
            const tbody = document.querySelector('#variants-table tbody');
            const addBtn = document.getElementById("add-variant");

            function renderAttributeOptions() {
                return attributes.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
            }

            let variantIndex = 0;

            function addVariantRow() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>
                    <select name="variants[${variantIndex}][product_attribute_id]" class="form-select">
                        <option value="">Select attribute</option>
                        ${renderAttributeOptions()}
                    </select>
                </td>
                <td>
                    <input type="text" name="variants[${variantIndex}][value]" class="form-control" placeholder="e.g. Red, XL" />
                </td>
                <td>
                    <input type="number" step="0.01" name="variants[${variantIndex}][additional_price]" class="form-control" placeholder="0.00" />
                </td>
                <td>
                    <input type="number" name="variants[${variantIndex}][quantity]" class="form-control" placeholder="e.g. 10" />
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="bx bx-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
            variantIndex++;
            }


            addBtn?.addEventListener('click' , addVariantRow);
            tbody?.addEventListener('click' , function(e){
                if(e.target.closest('.remove-variant')){
                    const row = e.target.closest('tr');
                    row?.parentNode?.removeChild(row);
                    // Decrement index when removing a row
                    variantIndex--;
                }
            })
            // No default empty row - user must click "Add Variant" to create rows

            // If no attributes available, hide the variants section completely
            if (attributes.length === 0) {
                $('.card:contains("Product Variants")').hide();
            }
        });
    </script>
    @include('admin.components.ajax-form-handler')
@endpush
