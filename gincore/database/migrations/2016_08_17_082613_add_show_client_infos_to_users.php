<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowClientInfosToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'show_client_info')) {
            Schema::table('users', function ($table) {
                $table->integer('show_client_info')->unsigned()->default(1);
                $table->index('show_client_info');
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
        if (!Schema::hasColumn('users', 'show_client_info')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('show_client_info');
            });
        }
    }
}
