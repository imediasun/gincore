<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsProviderToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!DB::table('settings')->select('id')->where('name', '=', 'sms-provider')->count()) {
            DB::table('settings')->insert(array(
                'section' => 1,
                'description' => 'Укажите провайдера: turbosms или smsru',
                'name' => 'sms-provider',
                'value' => '',
                'title' => 'Смс провайдер',
                'ro' => 0
            ));
        }
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
