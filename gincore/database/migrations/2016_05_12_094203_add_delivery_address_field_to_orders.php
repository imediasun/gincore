<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryAddressFieldToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'delivery_to')) {
            Schema::table('orders', function ($table) {
                $table->string('delivery_to')->default('');
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
        if (Schema::hasColumn('orders', 'delivery_to')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('delivery_to');
            });
        }
    }
}
