<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnIdPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!DB::table('users_permissions')->select('id')->where('link', '=', 'edit_return_id')->count()) {
            $id = DB::table('users_permissions')->insertGetId(array(
                'name' => 'Возврат денежных средств клиентам"',
                'link' => 'edit_return_id',
                'child' => 0,
                'group_id' => 6
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
