<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerHasShopTable extends Migration
{
    public function up()
    {
        Schema::create('seller_has_shop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shop_name')->unique();
            $table->string('shop_code')->unique();
            $table->integer('bank');
            $table->decimal('on_hold', 10, 2);
            $table->decimal('payout', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seller_has_shop');
    }
}
