<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemToContractorsCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('contractors_categories', 'is_system')) {
            Schema::table('contractors_categories', function ($table) {
                $table->boolean('is_system')->default('1');
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
        if (Schema::hasColumn('contractors_categories', 'is_system')) {
            Schema::table('contractors_categories', function ($table) {
                $table->dropColumn('is_system');
            });
        }
    }
}
