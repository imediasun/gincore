<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedToContractorsCategoriesLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('contractors_categories_links', 'deleted')) {
            Schema::table('contractors_categories_links', function ($table) {
                $table->integer('deleted')->integer(1)->unsigned()->default(3);
                $table->index('deleted');
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
        if (!Schema::hasColumn('contractors_categories_links', 'deleted')) {
            Schema::table('contractors_categories_links', function ($table) {
                $table->dropColumn('deleted');
            });
        }
    }
}
