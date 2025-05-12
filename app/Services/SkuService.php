<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Sku;

class SkuService
{
    /**
     * Cập nhật đơn hàng theo SKU.
     */
    public function updateOrdersBySku(Sku $sku): void
    {
        $orders = Order::where('sku', $sku->sku)->get();

        foreach ($orders as $order) {
            $order->cost = $sku->cost * $order->quantity;
            $order->profit = $order->total - $order->cost;
            $order->bonus = $order->profit * ($sku->bonus_percentage / 100);
            $order->save();
        }
    }
}
