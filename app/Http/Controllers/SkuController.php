<?php

namespace App\Http\Controllers;

use App\Imports\SkuImport;
use App\Models\Order;
use App\Models\Sku;
use Illuminate\Http\Request;
use App\Services\SkuService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SkuController extends Controller
{
    public function index(Request $request)
    {
        $query = Sku::query();
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%$search%");
            });
        }
        $skus = $query->paginate(10)->appends($request->only(['search']));

        return view('pages.sku.index', compact('skus'));
    }

    public function create()
    {
        return view('pages.sku.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|max:255',
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'bonus_percentage' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $skuData = $request->only('sku', 'cost', 'quantity', 'bonus_percentage');

            $sku = Sku::where('sku', $skuData['sku'])->first();

            if ($sku) {
                $sku->update($skuData);
            } else {
                $sku = Sku::create($skuData);
            }

            app(SkuService::class)->updateOrdersBySku($sku);

            DB::commit();
            return redirect()->route('sku.index')->with('success', 'SKU saved and related orders updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sku.index')->with('error', 'Failed to save SKU: ' . $e->getMessage());
        }
    }

    public function edit(Sku $sku)
    {
        return view('pages.sku.edit', compact('sku'));
    }

    public function update(Request $request, Sku $sku)
    {
        $request->validate([
            'sku' => 'required|max:255|unique:skus,sku,' . $sku->id,
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'bonus_percentage' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sku->update($request->only('sku', 'cost', 'quantity', 'bonus_percentage'));

            app(SkuService::class)->updateOrdersBySku($sku);

            DB::commit();
            return redirect()->route('sku.index')->with('success', 'SKU và các đơn hàng liên quan đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sku.index')->with('error', 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }

    public function destroy(Sku $sku)
    {
        $sku->delete();
        return redirect()->route('sku.index')->with('success', 'SKU deleted successfully.');
    }

    // Import SKU từ file Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = new SkuImport();

        try {
            Excel::import($import, filePath: $request->file('file'));

            $messages = [];

            if (count($import->created)) {
                $messages[] = '✅ Đã tạo mới: ' . implode(', ', $import->created);
            }

            if (count($import->updated)) {
                $messages[] = '🔁 Đã cập nhật: ' . implode(', ', $import->updated);
            }

            if (count($import->skipped)) {
                $messages[] = '⚠️ Bỏ qua: ' . implode(', ', $import->skipped);
            }

            return redirect()->route('sku.index')->with('status', implode('<br>', $messages));
        } catch (\Exception $e) {
            return redirect()->route('sku.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
