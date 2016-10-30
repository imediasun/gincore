<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasterToOrdersGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders_goods', 'engineer')) {
            Schema::table('orders_goods', function ($table) {
                $table->integer('engineer')->unsigned();
                $table->index('engineer');
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
        if (Schema::hasColumn('orders_goods', 'engineer')) {
            Schema::table('orders_goods', function ($table) {
                $table->dropColumn('engineer');
            });
        }
    }
}
