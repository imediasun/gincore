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
        \Illuminate\Support\Facades\DB::statement('DELETE cg FROM `restore4_category_goods` cg LEFT JOIN `restore4_goods` g ON g.id=cg.goods_id WHERE g.id IS NULL;');
    
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `restore4_category_goods` ADD CONSTRAINT `restore4_category_goods_ibfk_2` FOREIGN KEY (`goods_id`) REFERENCES `restore4_goods` (`id`) ON DELETE CASCADE;');
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
