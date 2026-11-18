@extends('admin.layouts.app')

@section('title', 'Create Role')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Create Role</h4>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Roles
                </a>
            </div>
            <div class="card">
                <div class="card-body">
                <form class="ajax-form" method="POST" action="{{ route('admin.roles.store') }}" data-redirect="{{ route('admin.roles.index') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            @foreach($permissionGroups as $group => $groupPermissions)
                                <div class="col-md-3 mb-2">
                                    <strong class="d-block mb-1 text-uppercase small">{{ $group }}</strong>
                                    @foreach($groupPermissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-grid d-sm-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
@endsection


