<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultCategoryToGodos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('goods', 'category_for_margin')) {
            Schema::table('goods', function ($table) {
                $table->integer('category_for_margin')->unsigned()->default(0);
                $table->index('category_for_margin');
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
        if (Schema::hasColumn('goods', 'category_for_margin')) {
            Schema::table('goods', function ($table) {
                $table->dropColumn('category_for_margin');
            });
        }
    }
}
