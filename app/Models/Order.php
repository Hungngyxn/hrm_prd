<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'extra_id',
        'sku',
        'shop_name',
        'quantity',
        'cost',
        'total',
        'profit',
        'bonus',
    ];

    public function shop()
    {
        return $this->belongsTo(SellerHasShop::class, 'shop_name', 'shop_name');
    }
}
