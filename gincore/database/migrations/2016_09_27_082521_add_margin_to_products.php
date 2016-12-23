<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMarginToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('goods', 'automargin')) {
            Schema::table('goods', function ($table) {
                $table->boolean('use_minimum_balance')->default(0);
                $table->integer('minimum_balance')->unsigned()->default(0);
                $table->boolean('use_automargin')->default(0);
                $table->boolean('automargin_type')->default(0);
                $table->integer('automargin')->unsigned()->default(0);
                $table->boolean('wholesale_automargin_type')->default(0);
                $table->integer('wholesale_automargin')->unsigned()->default(0);
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
        if (Schema::hasColumn('goods', 'automargin')) {
            Schema::table('goods', function ($table) {
                $table->dropColumn('brand_id');
                $table->dropColumn('use_minimum_balance');
                $table->dropColumn('minimum_balance');
                $table->dropColumn('margin');
                $table->dropColumn('automargin');
                $table->dropColumn('margin');
                $table->dropColumn('margin_type');
                $table->dropColumn('wholesale_margin');
                $table->dropColumn('wholesale_margin_type');
            });
        }
    }
}
