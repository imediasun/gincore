<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersSmsCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'sms_code')) {
            Schema::table('clients', function ($table) {
                $table->string('sms_code', 10)->default('');
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
        if (Schema::hasColumn('clients', 'sms_code')) {
            Schema::table('clients', function ($table) {
                $table->dropColumn('sms_code');
            });
        }
    }
}
