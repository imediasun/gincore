<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountTypeFieldToOrdersGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders_goods', 'discount_type')) {
            Schema::table('orders_goods', function ($table) {
                $table->integer('discount_type')->default(1);
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
        if (Schema::hasColumn('orders_goods', 'discount_type')) {
            Schema::table('orders_goods', function ($table) {
                $table->dropColumn('discount_type');
            });
        }
    }
}
