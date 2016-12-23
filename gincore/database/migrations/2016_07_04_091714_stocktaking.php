<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Stocktaking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stocktaking')) {
            Schema::create('stocktaking', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('warehouse_id')->integer(10)->unsigned();
                $table->text('checked_serials');
                $table->timestamp('created_at');
                $table->timestamp('saved_at')->nullable();
                $table->tinyInteger('history')->unsigned()->default(0);
                $table->index('warehouse_id');
                $table->index('created_at');
                $table->index('saved_at');
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
        Schema::dropIfExists('stocktaking');
    }
}
