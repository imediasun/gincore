<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Brands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title');
            });

            DB::table('brands')->insert(array(
                array(
                    'title' => 'LQ',
                ),
                array(
                    'title' => 'Apple',
                ),
                array(
                    'title' => 'Samsung',
                ),
            ));
        }
        if (!Schema::hasColumn('orders', 'brand_id')) {
            Schema::table('orders', function ($table) {
                $table->integer('brand_id')->integer(10)->unsigned();
                $table->index('brand_id');
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
        Schema::dropIfExists('brands');
        if (Schema::hasColumn('orders', 'brand_id')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('brand_id');
            });
        }
    }
}
