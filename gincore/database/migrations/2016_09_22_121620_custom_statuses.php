<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('status')) {
            Schema::create('status', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('status_id')->unsigned();
                $table->integer('order_type')->unsigned();
                $table->string('name');
                $table->string('color');
                $table->string('from')->default('');
                $table->boolean('system')->default(0);
                $table->boolean('use_in_manager')->default(1);
                $table->boolean('active')->default(1);
                $table->index('status_id');
                $table->index('order_type')->default(0);
                $table->index('system');
                $table->index('active');
                $table->index('use_in_manager');
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
        Schema::dropIfExists('status');
    }
}
