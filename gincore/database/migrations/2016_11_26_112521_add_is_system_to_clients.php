<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSystemToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'is_system')) {
            Schema::table('clients', function ($table) {
                $table->tinyInteger('is_system')->default(0);
                $table->index('is_system');
            });
            DB::table('clients')->whereIn('phone', [
                '000000000000',
                '000000000001',
                '000000000002',
            ])->update(['is_system' => 1]);
            DB::table('settings')->whereIn('name', [
                'client_id-for-quick-sale',
                'client_id-for-write-off',
                'client_id-for-supply',
            ])->delete();

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('clients', 'is_system')) {
            Schema::table('clients', function ($table) {
                $table->dropColumn('is_system');
            });
        }
    }
}
