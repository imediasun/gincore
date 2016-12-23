<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleToSmsTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sms_templates', 'var')) {
            Schema::table('sms_templates', function ($table) {
                $table->string('var')->default('');
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
        if (Schema::hasColumn('sms_templates', 'var')) {
            Schema::table('sms_templates', function ($table) {
                $table->dropColumn('var');
            });
        }
    }
}
