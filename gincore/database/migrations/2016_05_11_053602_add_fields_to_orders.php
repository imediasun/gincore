<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'delivery_by')) {
            Schema::table('orders', function ($table) {
                $table->integer('delivery_by')->default(0);
            });
        }
        if (!Schema::hasColumn('orders', 'sale_type')) {
            Schema::table('orders', function ($table) {
                $table->integer('sale_type')->default(0);
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
        if (Schema::hasColumn('orders', 'delivery_by')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('delivery_by');
            });
        }
        if (Schema::hasColumn('orders', 'sale_type')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('sale_type');
            });
        }
    }
}
