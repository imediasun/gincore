<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupSomeSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->whereIn('name', [
            'ga-profile-id',
            'ga-service-account-email',
            'ga-private-key',
        ])->update(['section' => 2]);

        DB::table('settings')->whereIn('name', [
            'order_warranties',
            'default_order_warranty',
        ])->update(['section' => 3]);

        DB::table('settings')->whereIn('name', [
            'turbosms-password',
            'turbosms-login',
            'turbosms-from',
            'sms-provider',
        ])->update(['section' => 4]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
