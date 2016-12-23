<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleIndexToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $keyExists = DB::select(
        DB::raw(
            'SHOW KEYS
            FROM restore4_categories
            WHERE Key_name=\'categories_title_index\''
            )
        );
        if (!$keyExists) {
            Schema::table('categories', function ($table) {
                $table->index('title');
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
        //
    }
}
