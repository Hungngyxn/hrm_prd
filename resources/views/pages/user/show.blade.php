@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'user'])

@section('_content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center text-black">
                <h5 class="mb-0">User Detail</h5>
                <a href="{{ route('user.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <input type="text" class="form-control" value="{{ optional($user->role)->name }}" readonly>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    @if (collect($accesses)->where('menu_id', 4)->first()->status == 2)
                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </form>

                        <a href="{{ route('user.edit', $user->id) }}" class="btn btn-info">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection