<?php

require_once __DIR__ . '/abstract_import_handler.php';

/**
 * Class import_posting_items
 *
 * @property MPurchaseInvoiceGoods PurchaseInvoiceGoods
 * @property MPurchaseInvoices PurchaseInvoices
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
            /** @todo remove */
            $goods = db()->query('SELECT * FROM {goods}')->assoc('title');
            $invoiceId = $this->createInvoice($this->import_settings);
            foreach ($rows as $row) {
                $title = $this->provider->get_title($row);
                if (!empty($title) && isset($goods[$title])) {
                    $data = $this->getItemData($goods[$title], $row);

                    if (!empty($data)) {
                        $results[] = $this->addItem($invoiceId, $data);
                    } else {
                        $results[] = array(
                            'state' => true,
                            'id' => $title,
                            'message' => l('Ошибка при добавлении товара в накладную')
                        );
                    }
                }
            }
            /** @todo remove */
        }
        $this->flushLog();
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
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
        $location = db()->query('SELECT id, wh_id FROM {warehouses_locations} WHERE id=?i',
            array($import_settings['location']))->row();
        return $this->PurchaseInvoices->insert(array(
            'user_id' => $this->getUserId(),
            'supplier_id' => $import_settings['contractor'],
            'warehouse_id' => $location['wh_id'],
            'location_id' => $location['id'],
            'date' => date('Y-m-d H:i:s')
        ));
    }

    private function getItemData($title, $row)
    {
    }

    private function addItem($invoice, $data)
    {
    }

    /**
     * @return string
     */
    public function getImportForm()
    {
        $contractors = db()->query('SELECT id, title FROM {contractors}')->vars();
        $locations = db()->query('
            SELECT l.id, CONCAT(w.title,"(", l.location, ")") as title 
            FROM {warehouses_locations} as l 
            JOIN {warehouses} as w ON w.id=l.wh_id
        ')->vars();
        return $this->view->renderFile('import/forms/posting_items', array(
            'contractors' => $contractors,
            'locations' => $locations
        ));
    }
}
