<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriorityToTemplateVars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('template_vars', 'priority')) {
            Schema::table('template_vars', function ($table) {
                $table->integer('priority')->unsigned()->default(0);
                $table->index('priority');
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
        if (!Schema::hasColumn('template_vars', 'priority')) {
            Schema::table('template_vars', function ($table) {
                $table->dropColumn('priority');
            });
        }
    }
}
