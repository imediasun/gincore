<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemToWarehouses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('warehouses', 'is_system')) {
            Schema::table('warehouses', function ($table) {
                $table->tinyInteger('is_system')->default(0);
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
        if (Schema::hasColumn('warehouses', 'is_system')) {
            Schema::table('warehouses', function ($table) {
                $table->dropColumn('is_system');
            });
        }
    }
}
