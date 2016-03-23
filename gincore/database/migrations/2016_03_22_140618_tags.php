<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Tags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title');
                $table->string('color')->default('#bbbbbb');
                $table->boolean('avail')->default('1');
            });
            Schema::table('clients', function ($table) {
                $table->integer('tag_id')->integer(10)->unsigned()->default(null);
                $table->index('tag_id');
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
        Schema::dropIfExists('tags');
        Schema::table('clients', function ($table) {
            $table->dropColumn('tag_id');
        });
    }
}
