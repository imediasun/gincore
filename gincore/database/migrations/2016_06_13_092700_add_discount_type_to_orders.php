<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountTypeToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'discount_type')) {
            Schema::table('orders', function ($table) {
                $table->integer('discount_type')->default(0);
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
        if (Schema::hasColumn('orders', 'discount_type')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('discount_type');
            });
        }
    }
}
