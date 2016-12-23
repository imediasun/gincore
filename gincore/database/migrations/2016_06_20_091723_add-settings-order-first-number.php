<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsOrderFirstNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!DB::table('settings')->select('id')->where('name', '=', 'order-first-number')->count()) {
            DB::table('settings')->insert(array(
                'section' => 1,
                'description' => 'Укажите последний номер заказа, который у вас был ранее',
                'name' => 'order-first-number',
                'value' => 0,
                'title' => 'Начало нумарации заказов',
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
