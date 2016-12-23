<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;



class AddForeignCategorygoodsGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbcfg = require __DIR__.'/../../../db_config.php';

        if (file_exists(__DIR__ . '/../../../db_config-local.php')) {
            $dbcfg = array_merge($dbcfg, require(__DIR__ . '/../../../db_config-local.php'));
        }

        \Illuminate\Support\Facades\DB::statement('DELETE cg FROM `'.$dbcfg['_prefix'].'category_goods` cg LEFT JOIN `'.$dbcfg['_prefix'].'goods` g ON g.id=cg.goods_id WHERE g.id IS NULL;');
    
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `'.$dbcfg['_prefix'].'category_goods` ADD CONSTRAINT `'.$dbcfg['_prefix'].'category_goods_ibfk_2` FOREIGN KEY (`goods_id`) REFERENCES `'.$dbcfg['_prefix'].'goods` (`id`) ON DELETE CASCADE;');
        } catch (Exception $e) {

        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
