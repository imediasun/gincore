<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $manager = DB::select(DB::raw("SELECT count(*) as cnt
                FROM restore4_clients WHERE `phone` = '000000000002'"));
        if (empty($manager[0]) || $manager[0]->cnt == 0) {
            DB::insert(DB::raw("
                INSERT INTO restore4_clients (phone,pass,fio,date_add,person)
                VALUES 
                ('000000000002','-','Клиент',NOW(),1)
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
