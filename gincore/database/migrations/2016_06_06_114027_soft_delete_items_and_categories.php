<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SoftDeleteItemsAndCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('categories', 'deleted')) {
            Schema::table('categories', function ($table) {
                $table->integer('deleted')->default(0);
            });
        }
        DB::table('categories')->insert(array(
            'title' => 'Recycle Bin',
            'parent_id' => 0,
            'avail' => 0,
            'url' => 'recycle-bin',
            'content' => '',
            'page_content' => '',
            'page_title' => '',
            'page_description' => '',
            'page_keywords' => '',
            'date_add' => date('Y-m-d H:i:s'),
            'warehouses_suppliers' => '',
            'information' => '',
            'rating' => 0,
            'votes' => 0,
            'deleted' => 0
        ));
        if (!Schema::hasColumn('goods', 'deleted')) {
            Schema::table('goods', function ($table) {
                $table->integer('deleted')->default(0);
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
        if (Schema::hasColumn('categories', 'deleted')) {
            Schema::table('categories', function ($table) {
                $table->dropColumn('deleted');
            });
        }
        if (Schema::hasColumn('goods', 'deleted')) {
            Schema::table('goods', function ($table) {
                $table->dropColumn('deleted');
            });
        }
    }
}
