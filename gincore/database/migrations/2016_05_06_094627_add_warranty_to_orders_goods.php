<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarrantyToOrdersGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders_goods', 'warranty')) {
            Schema::table('orders_goods', function ($table) {
                $table->integer('warranty')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('orders_goods', 'warranty')) {
            Schema::table('orders_goods', function ($table) {
                $table->dropColumn('warranty');
            });
        }
    }
}
