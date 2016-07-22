<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HomeMasterRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('home_master_requests')) {
            Schema::create('home_master_requests', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('order_id')->integer(10)->unsigned();
                $table->timestamp('date');
                $table->string('address')->default('');
                $table->index('order_id');
            });
            if (!Schema::hasColumn('orders', 'home_master_request')) {
                Schema::table('orders', function ($table) {
                    $table->integer('home_master_request')->integer(10)->unsigned();
                    $table->index('home_master_request');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_master_requests');
        if (!Schema::hasColumn('orders', 'home_master_request')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('home_master_request');
            });
        }
    }
}
