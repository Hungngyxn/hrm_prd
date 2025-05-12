@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'order'])

@section('_content')
    <div class="container-fluid mt-2 px-4">
        <div class="row">
            <div class="col-12">
                <h4 class="font-weight-bold">Orders</h4>
                <hr>
            </div>
        </div>
        <div class="card">
            <div class="col-12 mb-3">
                <div class="bg-light text-dark card p-4 shadow-sm rounded">
                    {{-- Filter + Search --}}
                    <form method="GET" action="{{ route('orders.index') }}" id="filterForm"
                        class="d-flex align-items-center gap-2">
                        @if (count($sellers) > 0)
                            <select name="user_id" class="form-select px-3 py-2 select2">
                                <option value="">-- All Sellers --</option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ request('user_id') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <select name="shop_name" class="form-select px-3 py-2 select2">
                            <option value="">-- All Shops --</option>
                            @foreach ($shopNames as $shopName)
                                <option value="{{ $shopName }}" {{ request('shop_name') == $shopName ? 'selected' : '' }}>
                                    {{ $shopName }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group flatpickr position-relative">
                            <input type="text" class="form-control ps-5 datepicker" name="date_start"
                                value="{{ request('date_start') }}" placeholder="Start Date">
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>
                        <div class="input-group flatpickr position-relative">
                            <input type="text" class="form-control ps-5 datepicker" name="date_end"
                                value="{{ request('date_end') }}" placeholder="End Date">
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control px-3 py-2"
                                placeholder="Search ....">
                        </div>
                        <button class="btn btn-outline-secondary px-4" type="submit" id="btnsearch">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="resetFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>

                    {{-- Toolbar --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap pt-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Import --}}
                            <div class="btn-group">
                                <button class="btn btn-outline-dark btn-md dropdown-toggle px-4 py-2" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-import me-2"></i> Import
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('orders.create') }}">
                                            <i class="fas fa-keyboard me-1"></i> Import Manual
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('orders.import') }}" method="POST"
                                            enctype="multipart/form-data" class="dropdown-item p-0 m-0 border-0"
                                            id="importForm">
                                            @csrf
                                            <label class="dropdown-item d-block" style="cursor: pointer">
                                                <i class="fas fa-file-excel me-1"></i> Import Excel
                                                <input type="file" name="file" accept=".xlsx,.xls"
                                                    onchange="handleImport(this)" style="display: none;">
                                            </label>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                            {{-- Export --}}
                            <div class="btn-group">
                                <button class="btn btn-outline-dark btn-md dropdown-toggle px-4 py-2" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item" type="button" onclick="submitExportSelected()">
                                            <i class="fas fa-check-square me-1"></i> Export Selected
                                        </button></li>
                                    <li><button class="dropdown-item" type="button" onclick="submitExport('current')">
                                            <i class="fas fa-clone me-1"></i> Export Current Page
                                        </button></li>
                                    <li><button class="dropdown-item" type="button" onclick="submitExport('all')">
                                            <i class="fas fa-globe me-1"></i> Export All
                                        </button></li>
                                </ul>
                            </div>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('orders.delete') }}" id="deleteForm">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="order_ids[]" id="deleteOrderIds">
                                <button type="submit" class="btn btn-outline-danger btn-md px-4 py-2">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Alerts --}}
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Spinner --}}
                    <div id="importSpinner" class="text-center my-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Đang xử lý file Excel, vui lòng chờ...</p>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table text-uppercase">
                                <tr>
                                    <th><input type="checkbox" id="selectAllTable"></th>
                                    <th>#</th>
                                    <th>Extra ID</th>
                                    <th>SKU</th>
                                    <th>Shop</th>
                                    <th>Quantity</th>
                                    <th>Cost</th>
                                    <th>Total</th>
                                    <th>Profit</th>
                                    <th>Bonus</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td><input type="checkbox" class="table-checkbox" value="{{ $order->id }}"></td>
                                        <th scope="row">{{ $loop->iteration + $orders->firstItem() - 1 }}</th>
                                        <td>{{ $order->extra_id }}</td>
                                        <td>{{ $order->sku }}</td>
                                        <td>
                                            @if (Str::contains($order->shop_name, 'Chưa được add'))
                                                <span class="badge bg-danger">{{ $order->shop_name }}</span>
                                                <a href="{{ route('shop.create') }}" class="btn btn-sm btn-outline-primary ms-2">
                                                    + Add Shop
                                                </a>
                                            @else
                                                {{ $order->shop_name }}
                                            @endif
                                        </td>
                                        <td>{{ $order->quantity }}</td>
                                        <td>{{ $order->cost }}</td>
                                        <td>{{ $order->total }}</td>
                                        <td>{{ $order->profit }}</td>
                                        <td>{{ $order->bonus }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editModal" type="button"
                                                onclick="openEditModal({{ $order->id }}, {{ addslashes($order->extra_id) }}, '{{ addslashes($order->sku) }}', {{ $order->quantity }}, {{ $order->total }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this shop?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-start">Tổng số đơn hàng: {{ $orderCount }}</td>
                                    <td colspan="3" class="text-end">Tổng toàn bộ đơn hàng:</td>
                                    <td>{{ number_format($totalCost, 2) }}</td>
                                    <td>{{ number_format($totalRevenue, 2) }}</td>
                                    <td>{{ number_format($totalProfit, 2) }}</td>
                                    <td>{{ number_format($totalBonus, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{ $orders->appends(request()->query())->links() }}

                    {{-- Export Hidden Form --}}
                    <form id="exportForm" method="POST" action="{{ route('orders.export') }}">
                        @csrf
                        <input type="hidden" name="mode" id="exportMode">
                        <div id="currentPageIds">
                            @foreach ($orders as $order)
                                <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                            @endforeach
                        </div>
                    </form>

                    {{-- Modal Edit --}}
                    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" id="editOrderForm" class="modal-content">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="edit_id">

                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="editModalLabel">Chỉnh sửa SKU đơn hàng</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body px-4">
                                        <div class="mb-3">
                                            <label for="edit_extra_id" class="form-label">Extra ID</label>
                                            <input type="text" name="extra_id" id="edit_extra_id" class="form-control" required readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit_sku" class="form-label">SKU</label>
                                            <input type="text" name="sku" id="edit_sku" class="form-control" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit_quantity" class="form-label">Quantity</label>
                                            <input type="text" name="quantity" id="edit_quantity" class="form-control"
                                                required readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit_total" class="form-label">Total</label>
                                            <input type="text" name="total" id="edit_total" class="form-control" required
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="modal-footer px-4">
                                        <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal xác nhận Export --}}
                    <div class="modal fade" id="exportConfirmModal" tabindex="-1" aria-labelledby="exportConfirmModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-sm">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title">Xác nhận Export</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">Bạn có chắc chắn muốn export dữ liệu?</div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success" form="exportForm" data-bs-dismiss="modal">
                                        <i class="fas fa-file-excel me-1"></i> Xác nhận Export
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/order.js') }}"></script>
    @endpush
@endsection