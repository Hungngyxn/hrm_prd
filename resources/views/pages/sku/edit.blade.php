@extends('layouts.admin', ['active' => 'sku'])

@section('_content')
<div class="container mt-4">
    <h3>Edit SKU</h3>

    <form action="{{ route('sku.update', $sku->id) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-bold">SKU</label>
            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                   value="{{ old('sku', $sku->sku) }}" required>

            @error('sku')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Cost</label>
            <input name="cost" class="form-control @error('cost') is-invalid @enderror"
                   value="{{ old('cost', $sku->cost) }}" required>

            @error('cost')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Quantity</label>
            <input name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                   value="{{ old('quantity', $sku->quantity) }}" required>

            @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Bonus</label>
            <input name="bonus_percentage" class="form-control @error('bonus_percentage') is-invalid @enderror"
                   value="{{ old('bonus_percentage', $sku->bonus_percentage) }}" required>

            @error('bonus_percentage')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
            <a href="{{ route('sku.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
