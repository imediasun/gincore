<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowOnlyUsersOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'show_only_his_orders')) {
            Schema::table('users', function ($table) {
                $table->integer('show_only_his_orders')->default(0);
                $table->index('show_only_his_orders');
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
        if (Schema::hasColumn('users', 'show_only_his_orders')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('show_only_his_orders');
            });
        }
    }
}
