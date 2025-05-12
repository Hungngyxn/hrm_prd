<?php

namespace App\Imports;

use App\Http\Controllers\SkuController;
use App\Models\Order;
use App\Models\Sku;
use App\Services\SkuService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SkuImport implements ToCollection, WithHeadingRow
{
    public $created = [];
    public $updated = [];
    public $skipped = [];
    protected $skuService;

    public function __construct()
    {
        $this->skuService = new SkuService();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $skuCode = trim($row['sku'] ?? '');
            $cost = isset($row['base_cost']) ? floatval(str_replace(',', '.', $row['base_cost'])) : null;
            $skuName = $row['mat_hang'] ?? null;
            $quantity = $row['so_luong_ton_kho'] ?? null;
            $bonusStr = $row['tier'] ?? null;
            

            preg_match('/\((\d+(\.\d+)?)%/', $bonusStr, $matches);
            $bonus_percentage = isset($matches[1]) ? floatval($matches[1]) : 0;

            if (empty($skuCode)) {
                $this->skipped[] = $skuCode ?: '[SKU không hợp lệ]';
                continue;
            }

            $sku = Sku::where('sku', $skuCode)->first();

            if ($sku) {
                $sku->update([
                    'cost' => $cost,
                    'name' => $skuName,
                    'quantity' => $quantity,
                    'bonus_percentage' => $bonus_percentage,
                ]);
                $this->skuService->updateOrdersBySku($sku);
                $this->updated[] = $skuCode;
            } else {
                $newSku = Sku::create([
                    'sku' => $skuCode,
                    'cost' => $cost,
                    'name' => $skuName,
                    'quantity' => $quantity,
                    'bonus_percentage' => $bonus_percentage,
                ]);
                $this->skuService->updateOrdersBySku($newSku);
                $this->created[] = $skuCode;
            }
        }
    }
}

