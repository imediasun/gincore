<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorCodeToGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('goods', 'vendor_code')) {
            Schema::table('goods', function ($table) {
                $table->string('vendor_code')->default('');
                $table->index('vendor_code');
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
        if (!Schema::hasColumn('goods', 'vendor_code')) {
            Schema::table('goods', function ($table) {
                $table->dropColumn('vendor_code');
            });
        }
    }
}
