<?php

require_once __DIR__ . '/abstract_template.php';

class wh_purchase_invoice extends AbstractTemplate
{

    /**
     * @param        $object
     * @param string $template
     * @return mixed|string
     */
    public function draw_one($object, $template = '')
    {
        $print_html = '';
        $invoice = $this->all_configs['db']->query(
            'SELECT pi.*, u.fio as fio, l.location as location, w.title as warehouse,
                        w.print_phone as wh_print_phone, wag.address as accept_address,
                        co.title as supplier
                FROM {purchase_invoices} as pi
                LEFT JOIN {users} as u ON u.id=pi.user_id
                LEFT JOIN {warehouses} as w ON w.id=pi.warehouse_id
                LEFT JOIN {warehouses_groups} as wag ON w.group_id=w.id
                LEFT JOIN {warehouses_locations} as l ON l.id=pi.location_id
                LEFT JOIN {contractors} as co ON co.id=pi.supplier_id
                WHERE pi.id=?i', array($object))->row();
        if ($invoice) {
            $this->editor = true;
            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT pig.*, g.type, g.title
                      FROM {purchase_invoice_goods} as pig
                      LEFT JOIN {goods} as g ON g.id=pig.good_id
                      WHERE pig.invoice_id=?i',
                array($object))->assoc();

            $print_html = $this->generate_template($this->getVariables($invoice, $goods), 'wh_purchase_invoice');
        }
        return $print_html;
    }

    /**
     * @param $invoice
     * @param $goods
     * @return array
     */
    public function getVariables($invoice, $goods)
    {
        $view = new View($this->all_configs);
        $amount = $this->getAmount($goods);
        $invoiceGoods = $view->renderFile('prints/wh_purchase_invoice_goods', array(
            'order' => $invoice,
            'goods' => $goods,
            'amount' => $amount
        ));
        $arr = array(
            'id' => array('value' => intval($invoice['id']), 'name' => l('ID приходной накладной')),
            'supplier' => array('value' => h($invoice['supplier']), 'name' => l('Поставщик')),
            'warehouse' => array('value' => h($invoice['warehouse']), 'name' => l('Склад')),
            'location' => array('value' => h($invoice['location']), 'name' => l('Локация')),
            'wh_print_phone' => array('value' => h($invoice['wh_print_phone']), 'name' => l('Телефон склада')),
            'wh_address' => array('value' => h($invoice['accept_address']), 'name' => l('Адрес склада')),
            'acceptor' => array('value' => h($invoice['fio']), 'name' => l('Приемщик')),
            'type' => array('value' => $invoice['type'] == 2 ? l('') : l(''), 'name' => l('Тип поставки')),
            'description' => array('value' => h($invoice['description']), 'name' => l('Комментарий')),
            'date' => array('value' => $invoice['date'], 'name' => l('Дата накладной')),
            'state' => array('value' => intval($invoice['state']), 'name' => l('Состояние')),
            'goods' => array('value' => $invoiceGoods, 'name' => l('Товары в накладной (таблица)')),
            'amount' => array('value' => $amount, 'name' => l('Сумма накладной')),
            'currency' => array('value' => viewCurrencySuppliers(), 'name' => l('Валюта')),
            'amount_as_word' => array('value' => $this->amountAsWord($amount), 'name' => l('Сумма накладной прописью')),
        );
        return $arr;
    }

    /**
     * @param $goods
     * @return int
     */
    private function getAmount($goods)
    {
        $amount = 0;
        if (!empty($goods)) {
            foreach ($goods as $good) {
                $amount += $good['price'] / 100 * $good['quantity'];
            }
        }
        return $amount;
    }
}