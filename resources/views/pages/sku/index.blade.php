@extends('layouts.admin', ['active' => 'sku'])

@section('_content')
    <div class="container-fluid mt-2 px-4">
        <div class="row">
            <div class="col-12">
                <h4 class="font-weight-bold">SKU Management</h4>
                <hr>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card bg-light shadow-sm p-4">

                    {{-- Toolbar --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if (Auth::user()->role->name !== 'User')
                                {{-- Add --}}
                                <div class="btn-group">
                                    <button class="btn btn-outline-dark dropdown-toggle px-4 py-2" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-plus me-2"></i> Add SKU
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('sku.create') }}">
                                                <i class="fas fa-keyboard me-1"></i> Add Manual
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('sku.import') }}" method="POST" enctype="multipart/form-data"
                                                class="dropdown-item p-0 m-0 border-0">
                                                @csrf
                                                <label class="dropdown-item d-block" style="cursor: pointer">
                                                    <i class="fas fa-file-excel me-1"></i> Import Excel
                                                    <input type="file" name="file" accept=".xlsx,.xls"
                                                        onchange="this.form.submit()" style="display: none;">
                                                </label>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        {{-- Search --}}
                        <form method="GET" action="{{ route('sku.index') }}" id="filterForm"
                            class="d-flex align-items-center gap-2">
                            <input type="text" name="search" class="form-control px-3 py-2" placeholder="Search SKU..."
                                value="{{ request('search') }}" style="min-width: 250px;">
                            <button type="submit" class="btn btn-outline-secondary px-4" id="btnsearch">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="resetFilters()">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Alerts --}}
                    @if (session('status'))
                        <div class="alert alert-info">
                            {!! session('status') !!}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th>SKU</th>
                                    <th>Mặt Hàng</th>
                                    <th>Base Cost</th>
                                    <th>Quantity</th>
                                    <th>Bonus</th>
                                    @if (Auth::user()->role->name !== 'User')
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($skus as $sku)
                                    <tr>
                                        <td>{{ $sku->sku }}</td>
                                        <td>{{ $sku->name }}</td>
                                        <td>{{ number_format($sku->cost, 1) }}</td>
                                        <td>{{ number_format($sku->quantity) }}</td>
                                        <td>{{ number_format($sku->bonus_percentage) }} %</td>
                                        @if (collect($accesses)->where('menu_id', 6)->first()->status == 2)
                                            <td>
                                                <a href="{{ route('sku.edit', $sku->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('sku.destroy', $sku->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this SKU?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No SKUs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-start">
                        {{ $skus->appends(['search' => request('search')])->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function resetFilters() {
            const form = document.getElementById('filterForm');
            if (form.querySelector('input[name="search"]')) {
                form.querySelector('input[name="search"]').value = '';
            }
            document.getElementById('btnsearch').click();
        }
    </script>
@endsection