<?php

require_once __DIR__ . '/abstract_template.php';

// квитанция
class check extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';

        $order = $this->all_configs['db']->query(
            'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();

        if ($order) {
            $this->editor = true;

            $src = $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=Z-' . $order['id'];
            $barcode = '<img src="' . $src . '" alt="S/N" title="S/N" />';

            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                'comment' => array('value' => htmlspecialchars($order['comment']), 'name' => 'Внешний вид'),
                'defect' => array('value' => htmlspecialchars($order['defect']), 'name' => 'Неисправность'),
                'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                'prepay' => array('value' => $order['prepay'] / 100, 'name' => 'Предоплата'),
                'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                'repair' => array('value' => '', 'name' => 'Вид ремонта'),
                'complect' => array('value' => '', 'name' => 'Комплектация'),
                'date' => array(
                    'value' => date("d/m/Y", strtotime($order['date_add'])),
                    'name' => 'Дата создания заказа на ремонт'
                ),
                'accepter' => array('value' => htmlspecialchars($order['a_fio']), 'name' => 'Приемщик'),
                'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                'product' => array(
                    'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                    'name' => 'Устройство'
                ),
                'warehouse' => array('value' => htmlspecialchars($order['wh_title']), 'name' => 'Название склада'),
                'warehouse_accept' => array(
                    'value' => htmlspecialchars($order['wa_title']),
                    'name' => 'Название склада приема'
                ),
                'barcode' => array('value' => $barcode, 'name' => 'Штрихкод'),
                'address' => array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                'wh_address' => array(
                    'value' => htmlspecialchars($order['print_address']),
                    'name' => 'Адрес склада'
                ),
                'wh_phone' => array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => 'Название компании'
                ),
                'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                'domain' => array('value' => $_SERVER['HTTP_HOST'], 'name' => 'Домен сайта'),
                'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                'order_data' => array(
                    'value' => date('d/m/Y', strtotime($order['date_add'])),
                    'name' => 'Дата создания заказа'
                ),
            );
            $arr['repair']['value'] = $order['repair'] == 0 ? 'Платный' : $arr['repair']['value'];
            $arr['repair']['value'] = $order['repair'] == 1 ? 'Гарантийный' : $arr['repair']['value'];
            $arr['repair']['value'] = $order['repair'] == 2 ? 'Доработка' : $arr['repair']['value'];

            $arr['complect']['value'] .= $order['battery'] == 1 ? l('Аккумулятор') . '<br />' : '';
            $arr['complect']['value'] .= $order['charger'] == 1 ? l('Зарядное устройств кабель') . '<br />' : '';
            $arr['complect']['value'] .= $order['cover'] == 1 ? l('Задняя крышка') . '<br />' : '';
            $arr['complect']['value'] .= $order['box'] == 1 ? l('Коробка') . '</br>' : '';
            $arr['complect']['value'] .= $order['equipment'] ? $order['equipment'] : '';

            $print_html = $this->generate_template($arr, 'check');
        }
        return $print_html;
    }
}
