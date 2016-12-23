<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneFieldToWarehouseGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('warehouses_groups', 'phone')) {
            Schema::table('warehouses_groups', function ($table) {
                $table->string('phone', 255)->nullable();
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
        if (Schema::hasColumn('warehouses_groups', 'phone')) {
            Schema::table('warehouses_groups', function ($table) {
                $table->dropColumn('phone');
            });
        }
    }
}
