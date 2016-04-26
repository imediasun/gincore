<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $manager = DB::select(DB::raw("SELECT count(*) as cnt
                FROM restore4_settings WHERE `name` = 'need_send_login_log'"));
        if (empty($manager[0]) || $manager[0]->cnt == 0) {
            DB::insert(DB::raw("
                INSERT INTO restore4_settings (name, value, description, ro, title)
                VALUES 
                ('need_send_login_log', '0', 'Отправлять ежедневные логи входа на email', 0, 'Отправлять ежедневные логи входа на email'),
                ('email_for_send_login_log', '', 'email на который будут отправлять логи входов в систему', 0, 'email на который будут отправлять логи входов в систему')
            "));
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
