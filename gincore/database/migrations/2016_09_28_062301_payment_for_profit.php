<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentForProfit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('goods', 'fixed_payment')) {
            Schema::table('goods', function ($table) {
                $table->float('fixed_payment')->unsigned()->default(0);
                $table->integer('percent_from_profit')->unsigned()->default(0);
            });
        }
        if (!Schema::hasColumn('categories', 'fixed_payment')) {
            Schema::table('categories', function ($table) {
                $table->float('fixed_payment')->unsigned()->default(0);
                $table->integer('percent_from_profit')->unsigned()->default(0);
            });
        }
        if (!Schema::hasColumn('users', 'use_fixed_payment')) {
            Schema::table('users', function ($table) {
                $table->boolean('use_fixed_payment')->default(0);
                $table->boolean('use_percent_from_profit')->default(0);
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
        if (Schema::hasColumn('goods', 'fixed_payment')) {
            Schema::table('goods', function ($table) {
                $table->dropColumn('fixed_payment');
                $table->dropColumn('percent_from_profit');
            });
        }
        if (Schema::hasColumn('categories', 'fixed_payment')) {
            Schema::table('categories', function ($table) {
                $table->dropColumn('fixed_payment');
                $table->dropColumn('percent_from_profit');
            });
        }
        if (Schema::hasColumn('users', 'use_fixed_payment')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('use_fixed_payment');
                $table->dropColumn('use_percent_from_profit');
            });
        }
    }
}
