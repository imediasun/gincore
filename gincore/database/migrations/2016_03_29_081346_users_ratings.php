<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersRatings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users_ratings')) {
            Schema::create('users_ratings', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id')->integer(10)->unsigned();
                $table->integer('order_id')->integer(10)->unsigned();
                $table->integer('client_id')->integer(10)->unsigned();
                $table->integer('rating')->integer(2)->unsigned()->default(0);
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('users', 'rating')) {
            Schema::table('users', function ($table) {
                $table->integer('rating')->integer(2)->unsigned()->default(10);
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
        Schema::dropIfExists('users_ratings');
        if (Schema::hasColumn('users', 'rating')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('rating');
            });
        }
    }
}
