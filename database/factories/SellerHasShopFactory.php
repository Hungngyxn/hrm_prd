<?php

namespace Database\Factories;

use App\Models\SellerHasShop;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerHasShopFactory extends Factory
{
    protected $model = SellerHasShop::class;

    public function definition()
    {
        return [
            
            'shop_name' => $this->faker->company,
            'shop_code' => strtoupper($this->faker->unique()->lexify('SHOP????')),
        ];
    }
    
}
