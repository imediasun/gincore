<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWriteOffPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('users_permissions')->where('link', '=', 'write-off-items')->count() == 0) {
            DB::table('users_permissions')->insert(
                array(
                    'link' => 'write-off-items',
                    'name' => 'Списание изделия',
                    'group_id' => 1,
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
