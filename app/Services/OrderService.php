<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Sku;

class OrderService
{
    protected $sku;
    protected $quantity;
    protected $total;

    public function __construct(Sku $sku, int $quantity, float $total)
    {
        $this->sku = $sku;
        $this->quantity = $quantity;
        $this->total = $total;
    }

    public function calculate(): array
    {
        $cost = $this->sku->cost * $this->quantity;
        $profit = $this->total - $cost;
        $bonus = $profit * ($this->sku->bonus_percentage / 100);

        return [
            'cost' => round($cost, 2),
            'profit' => round($profit, 2),
            'bonus' => round($bonus, 2),
        ];
    }
}
