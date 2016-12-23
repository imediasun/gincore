<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManagersToGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $manager = DB::select(DB::raw("SELECT u.id
                FROM restore4_users as u, restore4_users_permissions as p, restore4_users_role_permission as l
                WHERE p.link IN ('site-administration') AND l.permission_id=p.id AND u.role=l.role_id LIMIT 1"));
        if (!empty($manager[0]) && !empty($manager[0]->id)) {
            DB::insert(DB::raw("
                INSERT INTO restore4_users_goods_manager (goods_id, user_id) 
                SELECT id, {$manager[0]->id} FROM restore4_goods
                WHERE NOT id in (SELECT goods_id FROM restore4_users_goods_manager GROUP BY goods_id)
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
    }
}
