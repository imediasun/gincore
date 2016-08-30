<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdToPurchaseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchase_invoices', 'supplier_order_id')) {
            Schema::table('purchase_invoices', function ($table) {
                $table->integer('supplier_order_id')->integer(10)->unsigned();
                $table->index('supplier_order_id');
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
        if (!Schema::hasColumn('purchase_invoices', 'supplier_order_id')) {
            Schema::table('purchase_invoices', function ($table) {
                $table->dropColumn('supplier_order_id');
            });
        }
    }
}
