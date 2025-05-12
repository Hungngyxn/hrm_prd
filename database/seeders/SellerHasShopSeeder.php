<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SellerHasShop;

class SellerHasShopSeeder extends Seeder
{
    public function run(): void
    {
        SellerHasShop::factory()->count(15)->create();
    }
}
