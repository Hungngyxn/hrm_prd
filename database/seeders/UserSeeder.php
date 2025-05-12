<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Tắt kiểm tra khóa ngoại
        // Xóa dữ liệu cũ nếu cần
        User::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Tạo tài khoản admin
        User::factory()->administrator()->create();
    }
}
