<?php
require_once __DIR__ . '/../Core/AModel.php';

class MPurchaseInvoices extends AModel
{
    public $table = 'purchase_invoices';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'user_id',
            'supplier_id',
            'warehouse_id',
            'location_id',
            'type',
            'state',
            'description',
            'date',
        );
    }
}
