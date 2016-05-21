<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalaryToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'salary_from_repair')) {
            Schema::table('users', function ($table) {
                $table->integer('salary_from_repair')->default(0);
            });
        }
        if (!Schema::hasColumn('users', 'salary_from_sale')) {
            Schema::table('users', function ($table) {
                $table->integer('salary_from_sale')->default(0);
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
        if (Schema::hasColumn('users', 'salary_from_sale')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('salary_from_sale');
            });
        }
        if (Schema::hasColumn('users', 'salary_from_repair')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('salary_from_repair');
            });
        }
    }
}
