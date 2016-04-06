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
        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('order_id')->integer(10)->unsigned();
                $table->integer('client_id')->integer(10)->unsigned();
                $table->integer('engineer')->integer(2)->unsigned()->default(0);
                $table->integer('manager')->integer(2)->unsigned()->default(0);
                $table->integer('acceptor')->integer(2)->unsigned()->default(0);
                $table->text('comment')->default('');
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('users', 'rating')) {
            Schema::table('users', function ($table) {
                $table->float('rating')->unsigned()->default(10);
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
        Schema::dropIfExists('feedback');
        if (Schema::hasColumn('users', 'rating')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('rating');
            });
        }
    }
}
