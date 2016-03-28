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
            DB::table('tags')->insert(
                array(
                    'id' => 1,
                    'title' => 'VIP',
                    'color' => '#3F48CC'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 2,
                    'title' => 'regular',
                    'color' => '#22B14C'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 3,
                    'title' => 'discount',
                    'color' => '#B5E61D'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 4,
                    'title' => 'blacklist',
                    'color' => '#000000'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 5,
                    'title' => '-5%',
                    'color' => '#C3C3C3'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 6,
                    'title' => '-10%',
                    'color' => '#C3C3C3'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 7,
                    'title' => '-20%',
                    'color' => '#C3C3C3'
                )
            );
            DB::table('tags')->insert(
                array(
                    'id' => 8,
                    'title' => '-30%',
                    'color' => '#C3C3C3'
                )
            );
            DB::table('users_permissions')->insert(
                array(
                    'name' => 'Добавление клиента в черный список',
                    'link' => 'add-client-to-blacklist',
                    'group_id' => 1
                )
            );
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
        DB::table('users_permissions')->where('link', '=', 'add-client-to-blacklist')->delete();
    }
}
