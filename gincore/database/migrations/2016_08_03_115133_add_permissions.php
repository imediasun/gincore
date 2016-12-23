<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!DB::table('users_permissions')->select('id')->where('link', '=', 'show-client-section')->count()) {
            $id = DB::table('users_permissions')->insertGetId(array(
                'name' => 'Доступ к разделу "Клиенты"',
                'link' => 'show-client-section',
                'child' => 0,
                'group_id' => 4
            ));
            DB::table('users_role_permission')->insert(array(
                array(
                    'role_id' => 1,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 5,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 11,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 10,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 4,
                    'permission_id' => $id,
                ),
            ));
        }
        if (!DB::table('users_permissions')->select('id')->where('link', '=', 'export-clients-and-orders')->count()) {
            $id = DB::table('users_permissions')->insertGetId(array(
                'name' => 'Доступ к экспорту базы клиентов и заказов"',
                'link' => 'export-clients-and-orders',
                'child' => 0,
                'group_id' => 4
            ));
            DB::table('users_role_permission')->insert(array(
                array(
                    'role_id' => 10,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 11,
                    'permission_id' => $id,
                ),
                array(
                    'role_id' => 1,
                    'permission_id' => $id,
                ),
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
