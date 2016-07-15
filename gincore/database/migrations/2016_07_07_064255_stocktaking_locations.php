<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StocktakingLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stocktaking_locations')) {
            Schema::create('stocktaking_locations', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('stocktaking_id')->integer(10)->unsigned();
                $table->integer('location_id')->integer(10)->unsigned();
                $table->index('location_id');
                $table->index('stocktaking_id');
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
        Schema::dropIfExists('stocktaking_locations');
    }
}
