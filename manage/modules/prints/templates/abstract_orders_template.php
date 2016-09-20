<?php

require_once __DIR__ . '/abstract_template.php';

/**
 * Class AbstractOrdersTemplate
 *
 * @property MOrdersGoods OrdersGoods
 */
abstract class AbstractOrdersTemplate extends AbstractTemplate
{
    /**
     * @param $product
     * @return string
     */
    public function calculateHash($product)
    {
        $string = $product['goods_id'] . $product['price'] . $product['discount'] . $product['url'] . $product['item_id'] . $product['warranty'] . $product['discount_type'];
        return md5($string);
    }

    /**
     * @param $products
     * @return array
     */
    public function productsGroup($products)
    {
        $result = array();
        foreach ($products as $product) {
            $hash = $this->calculateHash($product);
            if (empty($result[$hash])) {
                $result[$hash] = $product;
                $result[$hash]['id'] = $hash;
                $result[$hash]['count'] = 0;
            }
            $result[$hash]['count'] += $product['count'];
        }
        return $result;
    }

    /**
     * @param $order
     * @param $goods
     * @return array
     */
    protected function getVariables($order, $goods)
    {
        $arr = array();
        $view = new View($this->all_configs);
        $summ = $order['sum'];
        $products_html = $view->renderFile('prints/waybill_products', array(
            'order' => $order,
            'goods' => $this->productsGroup($goods),
            'amount' => $summ / 100
        ));
        $qty_all = count($goods);
        $qty_products = 0;
        $qty_services = 0;

        $sum_in_words = $this->amountAsWord($summ / 100);
        $str_date = $this->dateAsWord();


        $sum_with_discount = $order['sum'] - $order['discount'];
        $sum_for_paid = $sum_with_discount - $order['sum_paid'];

        $client = $this->all_configs['db']->query('SELECT * FROM {clients} WHERE id=?i',
            array($order['user_id']))->row();
        if ($order['type'] == 0) {
            $services_cost = array();
            $products = $products_cost = $services = '';
            $sum_by_products_and_services = $sum_by_products = $sum_by_services = 0;
            if ($goods) {
                foreach ($goods as $product) {
                    $sum_by_products_and_services += $product['price'];
                    if ($product['type'] == 0) {
                        $products .= h($product['title']) . '<br/>';
                        $products_cost .= ($product['price'] / 100) . ' ' . viewCurrency() . '<br />';
                        $sum_by_products += $product['price'];
                        $qty_products += 1;
                    }
                    if ($product['type'] == 1) {
                        $services .= h($product['title']) . '<br/>';
                        $services_cost[] = ($product['price'] / 100) . ' ' . viewCurrency();;
                        $sum_by_services += $product['price'];
                        $qty_services += 1;
                    }
                }
            }
            $src = $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=Z-' . $order['id'];
            $barcode = '<img src="' . $src . '" alt="S/N" title="S/N" />';
            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                'sum' => array('value' => $summ / 100, 'name' => l('Сумма за ремонт')),
                'prepay' => array('value' => $order['prepay'] / 100, 'name' => l('Предоплата')),
                'discount' => array(
                    'value' => $order['discount'] > 0 ? ($order['discount'] / 100) . viewCurrency() : '',
                    'name' => l('Скидка на заказ')
                ),
                'sum_with_discount' => array(
                    'value' => $sum_with_discount / 100,
                    'name' => l('Сумма за ремонт с учетом скидки')
                ),
                'qty_all' => array('value' => $qty_all, 'name' => l('Количество наименований')),
                'qty_products' => array('value' => $qty_products, 'name' => l('Количество запчастей')),
                'qty_services' => array('value' => $qty_services, 'name' => l('Количество услуг')),
                'sum_in_words' => array('value' => $sum_in_words, 'name' => l('Сумма за ремонт прописью')),
                'sum_paid' => array(
                    'value' => $order['sum_paid'] > 0 ? $order['sum_paid'] / 100 : '',
                    'name' => l('Оплачено')
                ),
                'sum_paid_in_words' => array(
                    'value' => $order['sum_paid'] > 0 ? $this->amountAsWord($order['sum_paid'] / 100) : '',
                    'name' => l('Оплачено прописью')
                ),
                'sum_for_paid' => array(
                    'value' => $sum_for_paid > 0 ? $sum_for_paid / 100 : '',
                    'name' => l('К оплате')
                ),
                'sum_for_paid_in_words' => array(
                    'value' => $sum_for_paid > 0 ? $this->amountAsWord($sum_for_paid / 100) : '',
                    'name' => l('К оплате прописью')
                ),
                'address' => array('value' => h($order['accept_address']), 'name' => l('Адрес')),
                'currency' => array('value' => viewCurrency(), 'name' => l('Валюта')),
                'phone' => array('value' => h($order['phone']), 'name' => l('Телефон клиента')),
                'fio' => array('value' => h($order['fio']), 'name' => l('ФИО или название клиента')),
                'order_data' => array(
                    'value' => date('d/m/Y', strtotime($order['date_add'])),
                    'name' => l('Дата создания заказа')
                ),
                'now' => array('value' => $str_date, 'name' => l('Текущая дата')),
                'warranty' => array(
                    'value' => $order['warranty'] > 0 ? $order['warranty'] . ' ' . l('мес') . '' : l('Без гарантии'),
                    'name' => l('Гарантия')
                ),
                'product' => array(
                    'value' => h($order['title']) . ' ' . h($order['note']),
                    'name' => l('Устройство')
                ),
                'products_and_services' => array('value' => $products_html, 'name' => l('Товары и услуги')),
                'color' => array(
                    'value' => h($this->all_configs['configs']['devices-colors'][$order['color']]),
                    'name' => l('Цвет')
                ),
                'serial' => array('value' => h($order['serial']), 'name' => l('Серийный номер')),
                'company' => array(
                    'value' => h($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
                'order' => array('value' => $order['id'], 'name' => l('Номер заказа')),
                'defect' => array('value' => h($order['defect']), 'name' => l('Неисправность')),
                'engineer' => array('value' => h($order['engineer']), 'name' => l('Инженер')),
                'accepter' => array('value' => h($order['a_fio']), 'name' => l('Приемщик')),
                'comment' => array('value' => h($order['comment']), 'name' => l('Внешний вид')),
                'warehouse' => array('value' => h($order['wh_title']), 'name' => l('Название склада')),
                'warehouse_accept' => array(
                    'value' => h($order['wa_title']),
                    'name' => l('Название склада приема')
                ),
                'wh_address' => array(
                    'value' => h($order['print_address']),
                    'name' => l('Адрес склада')
                ),
                'wh_phone' => array(
                    'value' => h($order['print_phone']),
                    'name' => l('Телефон склада')
                ),
                'products' => array('value' => $products, 'name' => l('Установленные запчасти')),
                'products_cost' => array('value' => $products_cost, 'name' => l('Установленные запчасти')),
                'services' => array('value' => $services, 'name' => l('Услуги')),
                'services_cost' => array(
                    'value' => implode(' ' . viewCurrency() . '<br />', $services_cost),
                    'name' => l('Стоимость услуг')
                ),
                'repair' => array('value' => '', 'name' => l('Вид ремонта')),
                'complect' => array('value' => '', 'name' => l('Комплектация')),
                'domain' => array('value' => $this->all_configs['settings']['site_name'], 'name' => l('Домен сайта')),
                'barcode' => array('value' => $barcode, 'name' => l('Штрихкод')),
                'sum_by_products_and_services' => array(
                    'value' => $sum_by_products_and_services / 100,
                    'name' => l('Сумма за запчасти и услуги')
                ),
                'sum_by_products' => array('value' => $sum_by_products / 100, 'name' => l('Сумма за запчасти')),
                'sum_by_services' => array('value' => $sum_by_services / 100, 'name' => l('Сумма за услуги')),
                'client_reg_data_1' => array(
                    'value' => isset($client['reg_data_1']) ? $client['reg_data_1'] : '',
                    'name' => l('Клиент (Регистрационные данные 1)')
                ),
                'client_reg_data_2' => array(
                    'value' => isset($client['reg_data_2']) ? $client['reg_data_2'] : '',
                    'name' => l('Клиент (Регистрационные данные 2)')
                ),
                'client_legal_address' => array(
                    'value' => isset($client['legal_address']) ? $client['legal_address'] : '',
                    'name' => l('Клиент (Юридический адрес)')
                ),
                'client_residential_address' => array(
                    'value' => isset($client['residential_address']) ? $client['residential_address'] : '',
                    'name' => l('Клиент (Фактический адрес)')
                ),
                'client_note' => array(
                    'value' => isset($client['note']) ? $client['note'] : '',
                    'name' => l('Клиент (примечания)')
                ),
                'tech_report'=> array(
                    'value' => isset($order['engineer_comment']) ? $order['engineer_comment'] : '',
                    'name' => l('Заключение инженера')
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
        }

        if ($order['type'] == 3) {
            $discount = 0;
            if (!empty($goods)) {
                foreach ($goods as $good) {
                    $discount += discount($good);
                }
            }
            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                'sum' => array('value' => $summ / 100, 'name' => l('Сумма за ремонт')),
                'qty_all' => array('value' => $qty_all, 'name' => l('Количество наименований')),
                'products_and_services' => array('value' => $products_html, 'name' => l('Товары и услуги')),
                'discount' => array(
                    'value' => ($discount > 0 ? $discount . viewCurrency() : ''),
                    'name' => l('Скидка на заказ')
                ),
                'sum_with_discount' => array(
                    'value' => $sum_with_discount / 100,
                    'name' => l('Сумма заказа с учетом скидки')
                ),
                'sum_paid' => array(
                    'value' => $order['sum_paid'] > 0 ? $order['sum_paid'] / 100 : '',
                    'name' => l('Оплачено')
                ),
                'sum_paid_in_words' => array(
                    'value' => $order['sum_paid'] > 0 ? $this->amountAsWord($order['sum_paid'] / 100) : '',
                    'name' => l('Оплачено прописью')
                ),
                'product' => array(
                    'value' => h($order['g_title']) . ' ' . h($order['note']),
                    'name' => l('Устройство')
                ),
                'serial' => array(
                    'value' => suppliers_order_generate_serial($order),
                    'name' => l('Серийный номер')
                ),
                'company' => array(
                    'value' => h($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
                'address' => array('value' => h($order['accept_address']), 'name' => 'Адрес'),
                'wh_phone' => array(
                    'value' => h($order['print_phone']),
                    'name' => l('Телефон склада')
                ),
                'now' => array('value' => $str_date, 'name' => l('Текущая дата')),
                'currency' => array('value' => viewCurrency(), 'name' => l('Валюта')),
                'sum_in_words' => array('value' => $sum_in_words, 'name' => l('Сумма за ремонт прописью')),
                'order' => array('value' => $order['id'], 'name' => l('Номер заказа')),
                'order_data' => array(
                    'value' => date('d/m/Y', $order['date_add']),
                    'name' => l('Дата создания заказа')
                ),
                'client_reg_data_1' => array(
                    'value' => isset($client['reg_data_1']) ? $client['reg_data_1'] : '',
                    'name' => l('Клиент (Регистрационные данные 1)')
                ),
                'client_reg_data_2' => array(
                    'value' => isset($client['reg_data_2']) ? $client['reg_data_2'] : '',
                    'name' => l('Клиент (Регистрационные данные 2)')
                ),
                'client_legal_address' => array(
                    'value' => isset($client['legal_address']) ? $client['legal_address'] : '',
                    'name' => l('Клиент (Юридический адрес)')
                ),
                'client_residential_address' => array(
                    'value' => isset($client['residential_address']) ? $client['residential_address'] : '',
                    'name' => l('Клиент (Фактический адрес)')
                ),
                'client_note' => array(
                    'value' => isset($client['note']) ? $client['note'] : '',
                    'name' => l('Клиент (примечания)')
                ),
            );
        }
        return $arr;
    }

    /**
     * @param $order
     * @param $arr
     * @return mixed
     */
    protected function addUsersFieldsValues($order, $arr)
    {
        $usersFieldsValues = $this->getUsersFieldsValues($order['id']);
        if (!empty($usersFieldsValues)) {
            foreach ($usersFieldsValues as $name => $field) {
                if (!empty($field['value'])) {
                    $arr[$name] = array(
                        'value' => h($field['value']),
                        'name' => h($name)
                    );
                }
            }
        }
        return $arr;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    private function getUsersFieldsValues($orderId)
    {
        return db()->query('
            SELECT ouf.*, uf.*, uf.id as uf_id, ouf.id as ouf_id 
            FROM {users_fields} uf 
            LEFT JOIN {orders_users_fields} ouf ON uf.id=ouf.users_field_id AND  ouf.order_id=? 
            WHERE uf.deleted=0',
            array($orderId))->assoc('name');
    }
}
