<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndividualFieldToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'note')) {
            Schema::table('clients', function ($table) {
                $table->string('note')->default('');
            });
        }
        if (!Schema::hasColumn('clients', 'reg_data_1')) {
            Schema::table('clients', function ($table) {
                $table->string('reg_data_1')->default('');
            });
        }
        if (!Schema::hasColumn('clients', 'reg_data_2')) {
            Schema::table('clients', function ($table) {
                $table->string('reg_data_2')->default('');
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
        if (!Schema::hasColumn('clients', 'note')) {
            Schema::table('clients', function ($table) {
                $table->string('note')->default('');
            });
        }
        if (!Schema::hasColumn('clients', 'reg_data_1')) {
            Schema::table('clients', function ($table) {
                $table->string('reg_data_1')->default('');
            });
        }
        if (!Schema::hasColumn('clients', 'reg_data_2')) {
            Schema::table('clients', function ($table) {
                $table->string('reg_data_2')->default('');
            });
        }
    }
}
