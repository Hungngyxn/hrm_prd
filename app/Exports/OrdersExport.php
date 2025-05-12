<?php
namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders->map(function ($item) {
            return [
                'extra_id' => $item->extra_id,
                'sku' => $item->sku,
                'shop_name'=> $item->shop_name,
                'quantity' => $item->quantity,
                'cost' => $item->cost,                
                'total' => $item->total,
                'profit' => $item->profit,
                'bonus' => $item->bonus,
                'created_at' => $item->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['extra_id', 'sku', 'shop_name', 'quantity', 'cost', 'total', 'profit', 'bonus', 'created_at'];
    }
}
