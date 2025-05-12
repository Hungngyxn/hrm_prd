<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerHasShop;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerHasShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SellerHasShop::with('seller');

        // Nếu là User, chỉ xem Shop của mình
        if ($user->role->name === 'User') {
            $query->where('user_id', $user->id);
        }

        // Nếu có filter theo seller
        if ($request->filled('user_id') && $user->role->name !== 'User') {
            $query->where('user_id', $request->user_id);
        }

        // Nếu có search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', '%' . $search . '%')
                    ->orWhere('shop_code', 'like', '%' . $search . '%');
            });
        }

        $shops = $query->paginate(10)->appends($request->only(['search', 'user_id']));

        $sellers = [];
        if ($user->role->name !== 'User') {
            $sellers = User::whereHas('role')->get();
        }

        return view('pages.shop.index', compact('shops', 'sellers'));
    }

    public function create()
    {
        $sellers = User::whereHas('role')->get();
        return view('pages.shop.create', compact('sellers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_code' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $shop = SellerHasShop::create([
                'user_id' => $request->seller_id,
                'shop_name' => trim($request->shop_name),
                'shop_code' => $request->shop_code,
            ]);

            Order::where('shop_name', 'like', $request->shop_name . '%')
                ->update(['shop_name' => $shop->shop_name]);

            DB::commit();
            return redirect()->route('shop.index')->with('success', 'Shop created successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->route('shop.index')->with('error', 'Failed to create shop. Maybe duplicated shop name or code.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('shop.index')->with('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function edit(SellerHasShop $shop)
    {
        $this->authorizeShopAccess($shop);
        return view('pages.shop.edit', compact('shop'));
    }

    public function update(Request $request, SellerHasShop $shop)
    {
        $this->authorizeShopAccess($shop);

        DB::beginTransaction();
        try {
            $shop->update($request->only('user_id'));
            DB::commit();
            return redirect()->route('shop.index')->with('success', 'Shop updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('shop.index')->with('error', 'Error updating shop: ' . $e->getMessage());
        }
    }

    public function destroy(SellerHasShop $shop)
    {
        $this->authorizeShopAccess($shop);

        DB::beginTransaction();
        try {
            Order::where('shop_name', $shop->shop_name)
                ->update(['shop_name' => $shop->shop_name . ' - Chưa được add']);

            $shop->delete();

            DB::commit();
            return redirect()->route('shop.index')->with('success', 'Shop deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('shop.index')->with('error', 'Error deleting shop: ' . $e->getMessage());
        }
    }

    private function authorizeShopAccess(SellerHasShop $shop)
    {
        $user = auth()->user();
        if ($user->role->name === 'Seller' && $shop->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to shop.');
        }
    }

    public function checkSeller(Request $request)
    {
        $shopCode = $request->input('shop_code');

        $shop = SellerHasShop::where('shop_code', $shopCode)->first();

        if ($shop) {
            return response()->json([
                'success' => true,
                'shop_name' => $shop->shop_name,
                'seller_name' => optional($shop->seller)->name ?? 'Unknown'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found.'
            ]);
        }
    }
}
