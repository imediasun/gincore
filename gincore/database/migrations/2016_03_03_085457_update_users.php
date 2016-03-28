<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'deleted')) {
            Schema::table('users', function ($table) {
                $table->boolean('deleted')->default(false);
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
        if (Schema::hasColumn('users', 'deleted')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('deleted');
            });
        }
    }
}
