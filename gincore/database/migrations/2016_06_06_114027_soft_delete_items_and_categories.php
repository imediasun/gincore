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
        if (!Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function ($table) {
                $table->integer('type')->default(1);
            });

        }
        DB::table('categories')->insert(array(
            'title' => 'Trash Bin',
            'parent_id' => 0,
            'avail' => 0,
            'url' => 'trash-bin',
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
            'type' => 0b101
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function ($table) {
                $table->dropColumn('type');
            });
        }
    }
}
