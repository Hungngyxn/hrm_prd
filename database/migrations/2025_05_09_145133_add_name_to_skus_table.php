<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('skus', function (Blueprint $table) {
        $table->string('name')->nullable()->after('sku');
    });
}

public function down()
{
    Schema::table('skus', function (Blueprint $table) {
        $table->dropColumn('name');
    });
}

}
