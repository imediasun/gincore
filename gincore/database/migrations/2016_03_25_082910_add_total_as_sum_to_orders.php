<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalAsSumToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'total_as_sum')) {
            Schema::table('orders', function ($table) {
                $table->boolean('total_as_sum')->default(0);
                $table->index('total_as_sum');
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
        if (Schema::hasColumn('orders', 'total_as_sum')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('total_as_sum');
            });
        }
    }
}
