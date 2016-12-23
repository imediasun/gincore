<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HideSomeSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->whereIn('name', [
            'email_for_send_login_log',
            'warranties_left_days',
            'orders_comments_days',
            'unsold_items_days',
            'need_send_login_log',
            'cat-non-current-assets',
            'cat-non-all-ext',
        ])->update(['ro' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*
          DB::table('settings')->whereIn('name', [
            'email_for_send_login_log',
            'warranties_left_days',
            'orders_comments_days',
            'unsold_items_days',
            'need_send_login_log',
            'cat-non-current-assets',
            'cat-non-all-ext',
        ])->update(['ro' => 0]);
         */
    }
}
