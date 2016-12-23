<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOverEmailToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'send_over_email')) {
            Schema::table('users', function ($table) {
                $table->boolean('send_over_email')->default('0');
                $table->boolean('send_over_sms')->default('0');
                $table->index('send_over_email');
                $table->index('send_over_sms');
            });
        }
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'send_over_email')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('send_over_email');
                $table->dropColumn('send_over_sms');
            });
        }
    }
}
