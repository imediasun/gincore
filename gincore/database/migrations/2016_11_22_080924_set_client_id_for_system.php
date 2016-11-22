<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetClientIdForSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!DB::table('settings')->select('id')->where('name', '=', 'client_id-for-quick-sale')->count()) {
            $value = DB::table('clients')->select('id')->where('phone', '=', '000000000002')->value('id');
            DB::table('settings')->insert(
                array(
                    'section' => 1,
                    'name' => 'client_id-for-quick-sale',
                    'value' => empty($value) ? 0 : $value,
                    'title' => 'Клиент используемый для быстрых продаж',
                    'ro' => 0,
                    'description' => 'Клиент используемый для быстрых продаж',
                )
            );
        }
        if (!DB::table('settings')->select('id')->where('name', '=', 'client_id-for-write-off')->count()) {
            $value = DB::table('clients')->select('id')->where('phone', '=', '000000000000')->value('id');
            DB::table('settings')->insert(
                array(
                    'section' => 1,
                    'name' => 'client_id-for-write-off',
                    'value' => empty($value) ? 0 : $value,
                    'title' => 'Клиент используемый для списаний',
                    'ro' => 0,
                    'description' => 'Клиент используемый для списаний',
                )
            );
        }
        if (!DB::table('settings')->select('id')->where('name', '=', 'client_id-for-supply')->count()) {
            $value = DB::table('clients')->select('id')->where('phone', '=', '000000000001')->value('id');
            DB::table('settings')->insert(
                array(
                    'section' => 1,
                    'name' => 'client_id-for-supply',
                    'value' => empty($value) ? 0 : $value,
                    'title' => 'Клиент используемый для поставок',
                    'ro' => 0,
                    'description' => 'Клиент используемый для поставок',
                )
            );
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
