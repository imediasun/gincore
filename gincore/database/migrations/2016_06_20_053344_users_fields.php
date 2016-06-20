<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users_fields')) {
            Schema::create('users_fields', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->default('');
                $table->string('title')->default('');
                $table->integer('avail')->integer(10)->default(1)->unsigned();
                $table->integer('deleted')->integer(10)->default(0)->unsigned();
                $table->unique('name');
                $table->index('state');
                $table->index('deleted');
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
        Schema::dropIfExists('users_fields');
    }
}
