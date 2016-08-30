<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PurchaseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_invoices')) {
            Schema::create('purchase_invoices', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id')->integer(10)->unsigned();
                $table->integer('supplier_id')->integer(10)->unsigned();
                $table->integer('warehouse_id')->integer(10)->unsigned();
                $table->integer('location_id')->integer(10)->unsigned();
                $table->integer('type')->integer(2)->unsigned();
                $table->integer('state')->integer(2)->unsigned();
                $table->string('comment')->default('');
                $table->timestamp('date');
                $table->timestamp('purchase_date');
                $table->index('user_id');
                $table->index('supplier_id');
                $table->index('warehouse_id');
                $table->index('location_id');
                $table->index('type');
                $table->index('state');
            });
        }
        if (!Schema::hasTable('purchase_invoice_goods')) {
            Schema::create('purchase_invoice_goods', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('invoice_id')->integer(10)->unsigned();
                $table->integer('good_id')->integer(10)->unsigned()->nullable();
                $table->integer('price')->integer(10)->unsigned();
                $table->integer('quantity')->integer(10)->unsigned();
                $table->string('not_found')->default('');
                $table->index('invoice_id');
                $table->index('good_id');
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
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_invoice_goods');
    }
}
