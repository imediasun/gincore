<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEngineerCommentToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'engineer_comment')) {
            Schema::table('orders', function ($table) {
                $table->string('engineer_comment')->default('');
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
        if (Schema::hasColumn('orders', 'engineer_comment')) {
            Schema::table('orders', function ($table) {
                $table->dropColumn('engineer_comment');
            });
        }
    }
}
