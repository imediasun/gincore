<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BlockUserByTariff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'blocked_by_tariff')) {
            Schema::table('users', function ($table) {
                $table->boolean('blocked_by_tariff')->default('0');
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
        if (Schema::hasColumn('users', 'blocked_by_tariff')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('blocked_by_tariff');
            });
        }
    }
}
