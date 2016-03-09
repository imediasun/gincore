<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoodsExtended extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('goods_extended')) {
            Schema::create('goods_extended', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('goods_id')->unsigned();
                $table->string('market_yandex_id');
                $table->string('hotline_url')->default('');
                $table->boolean('hotline_flag')->default('0');
                $table->foreign('goods_id')->references('id')->on('goods');
                $table->index('hotline_flag');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_extended');
    }
}
