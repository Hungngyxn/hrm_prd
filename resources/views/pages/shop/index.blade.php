@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'shop'])

@section('_content')
    <div class="container-fluid mt-2 px-4">
        <div class="row">
            <div class="col-12">
                <h4 class="font-weight-bold">Shop Management</h4>
                <hr>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <div class="bg-light text-dark card p-4 shadow-sm rounded">

                    {{-- Toolbar --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <a href="{{ route('shop.create') }}" class="btn btn-outline-dark px-3 py-2">
                            <i class="fas fa-plus me-2"></i> Add New Shop
                        </a>

                        {{-- Filter Form --}}
                        <form method="GET" action="{{ route('shop.index') }}" id="filterForm"
                            class="d-flex align-items-center gap-2">
                            @if (count($sellers) > 0)
                                <select name="user_id_filter" class="form-select px-4 py-2 select2">
                                    <option value="">-- All Sellers --</option>
                                    @foreach ($sellers as $seller)
                                        <option value="{{ $seller->id }}" {{ request('user_id') == $seller->id ? 'selected' : '' }}>
                                            {{ $seller->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <input type="text" name="search" value="{{ request('search') }}" class="form-control px-4 py-2"
                                placeholder="Search ..." style="min-width: 250px;">

                            <button type="submit" class="btn btn-outline-secondary px-4" id="btnsearch">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="resetFilters()">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Alerts --}}
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Shop Name</th>
                                    <th>Shop Code</th>
                                    <th>Seller</th>
                                    @if (Auth::user()->role->name !== 'User')
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shops as $shop)
                                    <tr>
                                        <td>{{ $loop->iteration + $shops->firstItem() - 1 }}</td>
                                        <td>{{ $shop->shop_name }}</td>
                                        <td>{{ $shop->shop_code }}</td>
                                        <td>{{ optional($shop->seller)->name ?? '-' }}</td>
                                        @if (Auth::user()->role->name !== 'User')
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="openEditModal({{ $shop->id }}, '{{ addslashes($shop->shop_name) }}', '{{ $shop->shop_code }}', '{{ $shop->user_id }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <form action="{{ route('shop.destroy', $shop->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this shop?')">
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
                                        <td colspan="5" class="text-center">No shops found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start">
                        {{ $shops->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="editShopModal" tabindex="-1" aria-labelledby="editShopModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="editShopForm" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editShopModalLabel">Edit Shop</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="editShopId" name="id">

                        <div class="form-group mb-3">
                            <label>Shop Name</label>
                            <input type="text" name="shop_name" id="editShopName" class="form-control" required readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label>Shop Code</label>
                            <input type="text" name="shop_code" id="editShopCode" class="form-control" required readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label>Seller</label>
                            <select name="user_id" id="editSellerId" class="form-select select2">
                                <option value="">-- Select Seller --</option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Scripts --}}
    @push('scripts')
        <script src="{{ asset('js/shop.js') }}"></script>
    @endpush
@endsection