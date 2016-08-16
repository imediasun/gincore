<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForToTemplateVars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('template_vars', 'for_view')) {
            Schema::table('template_vars', function ($table) {
                $table->string('for_view')->default('');
                $table->index('for_view');
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
        if (!Schema::hasColumn('template_vars', 'for_view')) {
            Schema::table('template_vars', function ($table) {
                $table->dropColumn('for_view');
            });
        }
    }
}
