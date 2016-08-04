<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceTypeToOrderGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders_goods', 'price_type')) {
            Schema::table('orders_goods', function ($table) {
                $table->integer('price_type')->integer(1)->unsigned()->default(3);
                $table->index('price_type');
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
        if (!Schema::hasColumn('orders_goods', 'price_type')) {
            Schema::table('orders_goods', function ($table) {
                $table->dropColumn('price_type');
            });
        }
    }
}
