<?php

require_once __DIR__ . '/abstract_import_handler.php';

/**
 * Class import_posting_items
 *
 * @property MPurchaseInvoiceGoods PurchaseInvoiceGoods
 * @property MPurchaseInvoices     PurchaseInvoices
 *
 */
class import_posting_items extends abstract_import_handler
{
    protected $userId;
    public $uses = array(
        'PurchaseInvoices',
        'PurchaseInvoiceGoods'
    );

    /**
     * @inheritdoc
     */
    public function __construct($all_configs, $provider, $import_settings)
    {
        parent::__construct($all_configs, $provider, $import_settings);
        $this->userId = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    }

    /**
     * @param $rows
     * @return array
     */
    public function run($rows)
    {
        if (!$this->all_configs['oRole']->hasPrivilege('logistics')) {
            return array(
                'state' => false,
                'message' => l('Не хватает прав')
            );
        }
        $results = array();
        if (!empty($rows)) {
            $error = 0;
            $invoiceId = $this->createInvoice($this->import_settings);
            foreach ($rows as $row) {
                try {
                    $data = $this->getItemData($row);
                    $result = $this->addItem($invoiceId, $data);
                    if(empty($data['id'])) {
                       $result = array(
                           'state' => false,
                           'title' => $data['title'],
                           'message' => l('Отсутствует соответствие в номенклатуре')
                       );
                        $error = 1;
                    }
                    $results[] = $result;
                } catch (ExceptionWithMsg $e) {
                    $results[] = array(
                        'state' => false,
                        'id' => $this->provider->title($row),
                        'message' => l('Ошибка при добавлении товара в накладную')
                    );
                }
            }
            if($error == 0) {
                try {
                    $invoice = $this->PurchaseInvoices->getByPk($invoiceId);
                    $mod_id = $this->all_configs['configs']['warehouses-manage-page'];
                    $parentOrderId = $this->PurchaseInvoices->createOrderFromInvoice($invoice, $mod_id);
                    $this->debitOrderFromInvoice($invoiceId, $parentOrderId, $mod_id);
                    $this->PurchaseInvoices->update(array(
                        'state' => PURCHASE_INVOICE_STATE_CAPITALIZED
                    ), array('id' => $invoiceId));
                } catch (ExceptionWithMsg $e) {
                }
            }
        }
        $this->flushLog();
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results, $error)
        );
    }

    /**
     * @param $row
     * @return bool
     */
    public function check_format($row)
    {
        if (empty($this->import_settings['location']) || empty($this->import_settings['contractor'])) {
            return false;
        }
        return parent::check_format($row);
    }

    /**
     * @param $row
     * @return string
     */
    public function get_result_row($row)
    {
        return "<td>{$row['title']}</td><td>{$row['message']}</td>";
    }

    /**
     * @param $import_settings
     * @return bool|int
     */
    private function createInvoice($import_settings)
    {
        return $this->PurchaseInvoices->insert(array(
            'user_id' => $this->getUserId(),
            'supplier_id' => $import_settings['contractor'],
            'warehouse_id' => $import_settings['warehouse'],
            'location_id' => $import_settings['location'],
            'date' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * @param $row
     * @return array
     */
    private function getItemData($row)
    {
        $id = $this->provider->get_item_id($row);
        $title = $this->provider->title($row);
        return array(
            'good_id' => $id ? $id : '',
            'not_found' => ($id === false) ? $title : '',
            'price' => $this->provider->price($row),
            'quantity' => $this->provider->quantity($row),
            'title' => $title
        );

    }

    /**
     * @param $invoice
     * @param $data
     * @return array
     */
    private function addItem($invoice, $data)
    {
        $data['invoice_id'] = $invoice;
        $id = $this->PurchaseInvoiceGoods->insert($data);
        return $id ? array(
            'state' => true,
            'title' => $data['title'],
            'message' => l('Товар добавлен в накладную')
        ) : array(
            'state' => false,
            'title' => $data['title'],
            'message' => l('Ошибка при добавлении товара в накладную')
        );
    }

    /**
     * @return string
     */
    public function getImportForm()
    {
        $contractors = db()->query('SELECT id, title FROM {contractors}')->vars();
        $warehouses = db()->query('
            SELECT w.id, w.title 
            FROM {warehouses} as w
        ')->vars();
        return $this->view->renderFile('import/forms/posting_items', array(
            'contractors' => $contractors,
            'warehouses' => $warehouses
        ));
    }


    /**
     * @param $invoiceId
     * @param $parentOrderId
     * @param $mod_id
     * @return array
     * @throws ExceptionWithMsg
     */
    private function debitOrderFromInvoice($invoiceId, $parentOrderId, $mod_id)
    {
        $orders = $this->all_configs['db']->query('
            SELECT * 
            FROM {contractors_suppliers_orders} 
            WHERE id=?i OR parent_id=?i
        ', array($parentOrderId, $parentOrderId))->assoc('id');
        if (empty($orders)) {
            throw new ExceptionWithMsg(l('Договора с поставщиком не найдены'));
        }
        $goods = $this->all_configs['db']->query(' SELECT * FROM {purchase_invoice_goods} WHERE invoice_id=?i AND NOT good_id=0',
            array($invoiceId))->assoc('good_id');
        $result = array();
        if (!empty($goods)) {
            foreach ($orders as $order) {
                $data = array(
                    'order_id' => $order['id'],
                    'serial' => '',
                    'auto' => 'on',
                );
                $result[] = $this->all_configs['suppliers_orders']->debit_supplier_order($data, $mod_id);
            }
        }
        return $result;
    }
}
