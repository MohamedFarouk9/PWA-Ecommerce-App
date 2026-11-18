@extends('admin.layouts.app')
@section('title', 'Edit Customer — ' . $user->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Customers</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Customers</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <div class="row justify-content-center">
            <div class="col-12 col-xl-8">
                <div class="card radius-10">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-edit me-2 text-primary"></i>Edit Customer</h5>
                        <div class="ms-auto">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Profile picture preview --}}
                        <div class="text-center mb-4">
                            <img src="{{ $user->image }}" id="avatarPreview"
                                class="rounded-circle" width="90" height="90" style="object-fit:cover; border:3px solid #dee2e6;">
                            <div class="text-muted small mt-1">{{ $user->name }}</div>
                        </div>

                        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                            @csrf
                            @method('PATCH')

                            <div class="row g-3">

                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Phone --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $user->phone) }}">
                                </div>

                                {{-- Role --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Customer</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                    </select>
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ old('address', $user->address) }}">
                                </div>

                                {{-- City / State --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" name="city" class="form-control"
                                        value="{{ old('city', $user->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State</label>
                                    <input type="text" name="state" class="form-control"
                                        value="{{ old('state', $user->state) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control"
                                        value="{{ old('postal_code', $user->postal_code) }}">
                                </div>

                                {{-- Country --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Country</label>
                                    <input type="text" name="country" class="form-control"
                                        value="{{ old('country', $user->country) }}">
                                </div>

                                {{-- New Password --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Leave blank to keep current">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">Min 8 characters. Leave blank to keep the existing password.</div>
                                </div>

                            </div>

                            <hr class="mt-4">

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Save Changes
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
