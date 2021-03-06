<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCashlessToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'cashless')) {
            Schema::table('orders', function ($table) {
                $table->boolean('cashless')->default(0);
                $table->index('cashless');
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
        if (Schema::hasColumn('orders', 'cashless')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('cashless');
            });
        }
    }
}
