<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\SellerHasShop;
use App\Models\Sku;
use App\Models\User;
use App\Services\OrderService;
use DB;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\Request;
use App\Imports\OrderImport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
	public function index(Request $request)
	{
		$query = Order::query();
		$user = auth()->user();
		$role = $user->role->name;
		$sellers = [];
		$shopNames = [];

		// Lọc theo vai trò
		if ($role === 'User') {
			$shopNames = $user->shops()->pluck('shop_name');
			$query->whereIn('shop_name', $shopNames);
		} else {
			$shopNames = SellerHasShop::pluck('shop_name')->unique();
			$sellers = User::select('id', 'name')->distinct('name')->get();
		}

		// Lọc theo user_id → lấy shop_name của user đó
		if ($request->filled('user_id')) {
			$shopByUser = SellerHasShop::where('user_id', $request->user_id)->pluck('shop_name');
			$query->whereIn('shop_name', $shopByUser);
		}

		// Lọc theo shop_name cụ thể
		if ($request->filled('shop_name')) {
			$query->where('shop_name', $request->shop_name);
		}

		// Lọc theo từ khóa
		if ($search = $request->input('search')) {
			$query->where(function ($q) use ($search) {
				$q->where('sku', 'like', "%$search%")
					->orWhere('extra_id', 'like', "%$search%");
			});
		}

		// Lọc theo ngày bắt đầu
		if ($request->filled('date_start')) {
			$query->whereDate('created_at', '>=', $request->date_start);
		}

		// Lọc theo ngày kết thúc
		if ($request->filled('date_end')) {
			$query->whereDate('created_at', '<=', $request->date_end);
		}

		// Thống kê tổng
		$totalCost = $query->sum('cost');
		$totalRevenue = $query->sum('total');
		$totalProfit = $query->sum('profit');
		$totalBonus = $query->sum('bonus');
		$orderCount = $query->count();

		// Phân trang
		$orders = $query->paginate(15)->appends($request->only([
			'search',
			'user_id',
			'shop_name',
			'date_start',
			'date_end'
		]));

		return view('pages.order.index', compact(
			'orders',
			'totalCost',
			'totalRevenue',
			'orderCount',
			'sellers',
			'shopNames',
			'totalProfit',
			'totalBonus'
		));
	}

	public function create()
	{
		$shops = SellerHasShop::all();
		$skus = Sku::all();

		return view('pages.order.create', compact('shops', 'skus'));
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'extra_id' => 'required|string|max:255',
			'sku' => 'required|string|max:255',
			'shop_name' => 'required|string|max:255',
			'quantity' => 'required|numeric|min:0',
			'total' => 'required|numeric|min:0',
		]);

		DB::beginTransaction();

		try {
			$sku = Sku::where('sku', $validated['sku'])->firstOrFail();
			if ($sku->quantity < $validated['quantity']) {
				return redirect()->back()->with('error', 'Số lượng kho không đủ để tạo đơn hàng với ' . $sku->sku);
			}

			$service = new OrderService($sku, $validated['quantity'], $validated['total']);
			$calc = $service->calculate();

			$order = new Order(array_merge($validated, $calc));
			$order->save();

			$sku->decrement('quantity', $validated['quantity']);

			DB::commit();

			return redirect()->route('orders.index')->with('status', 'Order created successfully!');
		} catch (\Exception $e) {
			DB::rollBack();

			return redirect()->back()->with('error', 'Lỗi khi tạo đơn hàng: ' . $e->getMessage());
		}
	}

	public function edit(Order $order)
	{
		return view('pages.order.edit', compact('order'));
	}

	public function update(Request $request, Order $order)
	{
		$validated = $request->validate([
			'extra_id'=> 'required|string|max:255',
			'sku' => 'required|string|max:100',
			'quantity' => 'required|numeric|min:0',
			'total' => 'required|numeric|min:0',
		]);

		DB::beginTransaction();

		try {
			$sku = Sku::where('sku', $validated['sku'])->firstOrFail();

			$service = new OrderService($sku, $validated['quantity'], $validated['total']);
			$calc = $service->calculate();

			$order->sku = $validated['sku'];
			$order->quantity = $validated['quantity'];
			$order->total = $validated['total'];
			$order->cost = $calc['cost'];
			$order->profit = $calc['profit'];
			$order->bonus = $calc['bonus'];

			$order->save();

			DB::commit();

			return redirect()->back()->with('status', 'Order '.$validated['extra_id']. ' updated successfully!');
		} catch (\Exception $e) {
			DB::rollBack();

			return redirect()->back()->with('error', 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage());
		}
	}

	public function destroy(Order $order)
	{
		DB::beginTransaction();

		try {
			$order->delete();
			DB::commit();

			return redirect()->back()->with('success', 'Đơn hàng đã được xóa.');
		} catch (\Exception $e) {
			DB::rollBack();

			return redirect()->back()->with('error', 'Lỗi khi xóa đơn hàng: ' . $e->getMessage());
		}
	}

	public function delete(Request $request)
	{
		$orderIds = $request->input('order_ids');

		if (!$orderIds || !is_array($orderIds)) {
			return redirect()->back()->with('error', 'Không có đơn hàng nào được chọn.');
		}

		try {
			DB::beginTransaction();

			// Xoá các đơn hàng theo danh sách ID
			Order::whereIn('id', $orderIds)->delete();

			DB::commit();
			return redirect()->back()->with('success', 'Xóa đơn hàng thành công.');
		} catch (\Exception $e) {
			DB::rollBack();
			return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xóa: ' . $e->getMessage());
		}
	}

	public function importForm()
	{
		return view('orders.import');
	}

	public function import(Request $request)
	{
		$request->validate([
			'file' => 'required|mimes:xlsx,xls'
		]);
		$orders = new OrderImport();

		try {
			Excel::import($orders, $request->file('file'));

			if (count($orders->skipped) > 0) {
				return redirect()->route('orders.index')->with(
					'error',
					'Imported with some skipped codes: ' . implode(', ', $orders->skipped)
				);
			}

			return redirect()->route('orders.index')->with('status', 'Imported successfully!');
		} catch (\Exception $e) {
			return redirect()->route('orders.index')->with('error', 'Có lỗi xảy ra, vui lòng kiểm tra lại file và thử lại.');
		}
	}
	public function export(Request $request)
	{
		$mode = $request->input('mode');
		$ids = $request->input('order_ids');

		if ($mode === 'all') {
			$orders = Order::all();
		} elseif ($mode === 'current' && is_array($ids)) {
			$orders = Order::whereIn('id', $ids)->get();
		} elseif (is_array($ids)) {
			// Export selected
			$orders = Order::whereIn('id', $ids)->get();
		} else {
			return back()->with('error', 'Không có dữ liệu để export.');
		}

		return Excel::download(new OrdersExport($orders), 'orders-' . time() . '.xlsx');
	}
}

