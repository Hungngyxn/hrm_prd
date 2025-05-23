<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = ['dashboard', 'order','shop' , 'user','role', 'sku', 'account'];

        foreach($menus as $menu) {
            Menu::factory()->create(['name' => $menu]);
        }
    }
}
