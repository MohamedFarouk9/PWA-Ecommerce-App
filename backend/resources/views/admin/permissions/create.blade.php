@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Create Permission</h4>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Permissions
                </a>
            </div>
            <div class="card">
                <div class="card-body">
                <form class="ajax-form" method="POST" action="{{ route('admin.permissions.store') }}" data-redirect="{{ route('admin.permissions.index') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. products.view" required>
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-grid d-sm-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save
                        </button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
@endsection


