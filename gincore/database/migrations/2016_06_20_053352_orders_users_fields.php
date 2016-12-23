<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrdersUsersFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('orders_users_fields')) {
            Schema::create('orders_users_fields', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('order_id')->integer(10)->unsigned();
                $table->integer('users_field_id')->integer(10)->unsigned();
                $table->text('value')->default('');
                $table->index('order_id');
                $table->index('users_field_id');
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
        Schema::dropIfExists('orders_users_fields');
    }
}
