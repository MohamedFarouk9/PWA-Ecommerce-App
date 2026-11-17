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
                    <!-- Image Management -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bx bx-images me-2"></i>Product Images</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="openImageManager" data-bs-toggle="modal" data-bs-target="#imageManagerModal">
                                <i class="bx bx-edit me-1"></i>Manage Images
                            </button>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($product->images->count())
                                    <div class="row g-2" id="imagePreviewGrid">
                                        @foreach($product->images as $img)
                                            <div class="col-4">
                                                <div class="position-relative">
                                                    <img src="{{ $img->url }}" alt="Product Image {{ $loop->iteration }}"
                                                         class="img-fluid rounded shadow" style="width: 100%; height: 100px; object-fit: cover;">
                                                    @if($img->is_cover)
                                                        <span class="position-absolute top-0 start-0 m-1 badge bg-success">
                                                            <i class="bx bx-check me-1"></i>Cover
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-light rounded shadow d-flex align-items-center justify-content-center"
                                         style="width: 200px; height: 120px; margin: 0 auto;">
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

                    <!-- Image Manager Modal -->
                    <div class="modal fade" id="imageManagerModal" tabindex="-1" aria-labelledby="imageManagerModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="imageManagerModalLabel">
                                        <i class="bx bx-images me-2"></i>Image Manager - {{ $product->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="imageManagerContainer">
                                        <!-- Image manager will be loaded here via AJAX -->
                                    </div>
                                </div>
                            </div>
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

       // ===== IMAGE MANAGER FUNCTIONALITY =====
        @php
            $imageData = $product->images->map(function ($img) {
                return [
                    'id'       => $img->id,
                    'path'     => $img->path,
                    'position' => $img->position,
                    'is_cover' => $img->is_cover,
                    'url'      => $img->url,
                ];
            })->values()->toArray();
        @endphp

        let productId = @json($product->id);
        let images = @json($imageData);

        // Load Image Manager Modal Content
        $('#imageManagerModal').on('show.bs.modal', function () {
            loadImageManager();
        });

        function loadImageManager() {
            $.ajax({
                url: `/api/admin/products/${productId}/images`,
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                success: function(response) {
                    renderImageManager(response.data.images);
                },
                error: function(xhr) {
                    // Fallback to local data if API fails
                    renderImageManager(images);
                }
            });
        }

        function renderImageManager(imageData) {
            images = imageData;
            let html = `
                <div class="image-manager">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Drag to reorder • Click star to set cover</h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" id="uploadNewImages" data-bs-toggle="modal" data-bs-target="#uploadImagesModal">
                                <i class="bx bx-upload me-1"></i>Upload Images
                            </button>
                        </div>
                    </div>
                    <div class="image-grid" id="imageGrid">
            `;

            if (images.length === 0) {
                html += `
                    <div class="col-12 text-center py-5">
                        <i class="bx bx-image" style="font-size: 48px; color: #ccc;"></i>
                        <h5 class="text-muted mt-2">No images yet</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadImagesModal">
                            <i class="bx bx-upload me-1"></i>Upload First Image
                        </button>
                    </div>
                `;
            } else {
                images.forEach((image, index) => {
                    html += `
                        <div class="image-item" data-image-id="${image.id}" draggable="true">
                            <div class="image-thumbnail">
                                <img src="${image.urls.thumbnail}" alt="Image ${index + 1}" loading="lazy">
                                ${image.is_cover ? '<span class="cover-badge"><i class="bx bx-check"></i> Cover</span>' : ''}
                                <span class="position-indicator">#${index + 1}</span>
                            </div>
                            <div class="image-actions">
                                <button class="btn-action ${image.is_cover ? 'active' : ''}"
                                        onclick="setCover(${image.id})"
                                        ${image.is_cover ? 'disabled' : ''}
                                        title="Set as Cover">
                                    <i class="bx bx-star"></i>
                                </button>
                                <a href="${image.urls.original}" target="_blank" class="btn-action" title="View Full Size">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="${image.urls.original}" download class="btn-action" title="Download">
                                    <i class="bx bx-download"></i>
                                </a>
                                <button class="btn-action danger" onclick="deleteImage(${image.id})" title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                            <div class="drag-handle" title="Drag to reorder">
                                <i class="bx bx-move"></i>
                            </div>
                        </div>
                    `;
                });
            }

            html += `
                    </div>
                </div>

                <!-- Upload Images Modal -->
                <div class="modal fade" id="uploadImagesModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Upload New Images</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="upload-area" id="dropZone">
                                    <input type="file" id="uploadFiles" multiple accept="image/*" style="display: none;">
                                    <i class="bx bx-upload" style="font-size: 48px; color: #007bff;"></i>
                                    <h5 class="mt-3">Drag & drop images here or click to browse</h5>
                                    <p class="text-muted">Max 10 images • 5MB each • JPEG, PNG, WebP</p>
                                </div>
                                <div id="selectedFilesList" class="mt-3" style="display: none;"></div>
                                <div id="uploadProgress" class="mt-3" style="display: none;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Uploading...</span>
                                        <span id="uploadPercent">0%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="confirmUpload" disabled>
                                    <i class="bx bx-upload me-1"></i>Upload <span id="fileCount">0</span> Image(s)
                                </button>
                            </div>
                        </div>
                    </div>
                `;

            $('#imageManagerContainer').html(html);
            initDragAndDrop();
            initUploadModal();
        }

        // Drag and Drop Reordering
        function initDragAndDrop() {
            const grid = document.getElementById('imageGrid');
            let draggedItem = null;

            grid.addEventListener('dragstart', (e) => {
                const item = e.target.closest('.image-item');
                if (item) {
                    draggedItem = item;
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                }
            });

            grid.addEventListener('dragend', (e) => {
                const item = e.target.closest('.image-item');
                if (item) {
                    item.classList.remove('dragging');
                }
                draggedItem = null;
            });

            grid.addEventListener('dragover', (e) => {
                e.preventDefault();
                const item = e.target.closest('.image-item');
                if (item && item !== draggedItem) {
                    const rect = item.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;

                    if (e.clientY < midY) {
                        item.style.borderTop = '3px solid #007bff';
                        item.style.borderBottom = 'none';
                    } else {
                        item.style.borderBottom = '3px solid #007bff';
                        item.style.borderTop = 'none';
                    }
                }
            });

            grid.addEventListener('dragleave', (e) => {
                const item = e.target.closest('.image-item');
                if (item) {
                    item.style.borderTop = 'none';
                    item.style.borderBottom = 'none';
                }
            });

            grid.addEventListener('drop', (e) => {
                e.preventDefault();
                const target = e.target.closest('.image-item');
                if (target && draggedItem && target !== draggedItem) {
                    target.style.borderTop = 'none';
                    target.style.borderBottom = 'none';

                    const rect = target.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;

                    if (e.clientY < midY) {
                        grid.insertBefore(draggedItem, target);
                    } else {
                        grid.insertBefore(draggedItem, target.nextSibling);
                    }

                    // Update positions
                    saveImageOrder();
                }
            });
        }

        function saveImageOrder() {
            const items = document.querySelectorAll('#imageGrid .image-item');
            const imageIds = Array.from(items).map(item => item.dataset.imageId);

            $.ajax({
                url: `/api/admin/products/${productId}/images/reorder`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({ image_ids: imageIds }),
                success: function(response) {
                    // Update position indicators
                    items.forEach((item, index) => {
                        item.querySelector('.position-indicator').textContent = '#' + (index + 1);
                    });
                    showToast('Image order updated', 'success');
                },
                error: function() {
                    showToast('Failed to reorder images', 'error');
                    loadImageManager(); // Reload to reset
                }
            });
        }

        // Set Cover Image
        window.setCover = function(imageId) {
            $.ajax({
                url: `/api/admin/products/${productId}/images/${imageId}/cover`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                success: function() {
                    loadImageManager();
                    showToast('Cover image updated', 'success');
                },
                error: function() {
                    showToast('Failed to set cover image', 'error');
                }
            });
        };

        // Delete Image
        window.deleteImage = function(imageId) {
            if (!confirm('Delete this image? This cannot be undone.')) return;

            $.ajax({
                url: `/api/admin/products/${productId}/images/${imageId}`,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                success: function() {
                    loadImageManager();
                    showToast('Image deleted', 'success');
                },
                error: function() {
                    showToast('Failed to delete image', 'error');
                }
            });
        };

        // Upload Modal
        function initUploadModal() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('uploadFiles');
            const selectedFilesList = document.getElementById('selectedFilesList');
            const confirmBtn = document.getElementById('confirmUpload');
            let selectedFiles = [];

            // Click to select
            dropZone.addEventListener('click', () => fileInput.click());

            // Drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                handleFiles(dt.files);
            });

            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });

            function handleFiles(files) {
                selectedFiles = [];
                Array.from(files).forEach(file => {
                    if (!file.type.startsWith('image/')) {
                        showToast(`${file.name}: Not an image file`, 'error');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        showToast(`${file.name}: Exceeds 5MB limit`, 'error');
                        return;
                    }
                    if (selectedFiles.length >= 10) {
                        showToast('Maximum 10 images allowed', 'error');
                        return;
                    }
                    selectedFiles.push(file);
                });
                updateFileList();
            }

            function updateFileList() {
                if (selectedFiles.length === 0) {
                    selectedFilesList.style.display = 'none';
                    confirmBtn.disabled = true;
                    return;
                }

                selectedFilesList.style.display = 'block';
                confirmBtn.disabled = false;
                document.getElementById('fileCount').textContent = selectedFiles.length;

                selectedFilesList.innerHTML = `
                    <h6>Selected (${selectedFiles.length}):</h6>
                    <ul class="list-unstyled">
                        ${selectedFiles.map((file, i) => `
                            <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span>${file.name}</span>
                                <span class="badge bg-secondary">${(file.size / 1024).toFixed(1)} KB</span>
                            </li>
                        `).join('')}
                    </ul>
                `;
            }

            // Upload handler
            confirmBtn.addEventListener('click', async () => {
                if (selectedFiles.length === 0) return;

                const formData = new FormData();
                selectedFiles.forEach(file => formData.append('images', file));

                const progressContainer = document.getElementById('uploadProgress');
                const progressBar = document.getElementById('progressBar');
                const uploadPercent = document.getElementById('uploadPercent');

                progressContainer.style.display = 'block';
                confirmBtn.disabled = true;

                try {
                    // Use XHR for progress tracking
                    await new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();

                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                progressBar.style.width = percent + '%';
                                uploadPercent.textContent = percent + '%';
                            }
                        });

                        xhr.addEventListener('load', () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve(JSON.parse(xhr.responseText));
                            } else {
                                reject(new Error('Upload failed'));
                            }
                        });

                        xhr.addEventListener('error', () => reject(new Error('Upload failed')));

                        xhr.open('POST', `/api/admin/products/${productId}/images`);
                        xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.getItem('api_token'));
                        xhr.send(formData);
                    });

                    selectedFiles = [];
                    fileInput.value = '';
                    updateFileList();
                    progressContainer.style.display = 'none';
                    loadImageManager();

                    // Close upload modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadImagesModal'));
                    modal?.hide();

                    showToast('Images uploaded successfully', 'success');
                } catch (err) {
                    showToast('Upload failed: ' + err.message, 'error');
                    confirmBtn.disabled = false;
                    progressContainer.style.display = 'none';
                }
            });
        }

        // Toast helper
        function showToast(message, type = 'info') {
            // Use existing toast system or create simple one
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(container);
            }
            container.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    });
</script>
@include('admin.components.ajax-form-handler')
@endpush
