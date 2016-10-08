<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixSettingsVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->whereIn('name', [
            'email-to-receive-new-comments',
            'widget-order-feedback-bg-color',
            'widget-order-feedback-fg-color',
            'widget-order-state-bg-color',
            'widget-order-state-fg-color',
            'site-for-add-rating',
        ])->update(['ro' => 1]);


        DB::table('settings')->whereIn('name', [
            'sms-provider',
        ])->update(['section' => 4]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
