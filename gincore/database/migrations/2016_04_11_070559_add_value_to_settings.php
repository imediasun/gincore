<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValueToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('settings')->where('name', '=', 'default_order_warranty')->count() == 0) {
            DB::table('settings')->insert(
                array(
                    'name' => 'default_order_warranty',
                    'value' => 0,
                    'title' => 'Гарантии по умолчанию',
                    'ro' => 0,
                    'description' => 'Укажите гарандию по умолчанию',
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
        DB::table('settings')->where('name', '=', 'default_order_warranty')->delete();
    }
}
