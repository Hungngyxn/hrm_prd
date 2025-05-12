@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'accounts'])

@section('_content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-center">
        <div class="card shadow-sm w-100" style="max-width: 80%;"> <!-- 80% so với container -->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Role Detail</h5>
                <a href="{{ route('roles.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Name:</label>
                        <div class="form-control-plaintext">{{ $role->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Is Superuser?:</label>
                        <div class="form-control-plaintext">{{ $role->is_super_user ? 'Yes' : 'No' }}</div>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3">Access Permissions</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Menu</th>
                                <th>Disabled</th>
                                <th>View</th>
                                <th>All</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accessesForEditing as $access)
                            <tr>
                                <td class="text-start">{{ Str::title($access->menu->name) }}</td>
                                <td>
                                    <input type="radio" disabled {{ $access->status == 0 ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="radio" disabled {{ $access->status == 1 ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="radio" disabled {{ $access->status == 2 ? 'checked' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (collect($accesses)->where('menu_id', 3)->first()->status == 2)
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('roles.edit', ['role' => $role->id]) }}" class="btn btn-info">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('roles.destroy', ['role' => $role->id]) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
