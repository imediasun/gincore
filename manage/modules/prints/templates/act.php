<?php

require_once __DIR__ . '/abstract_orders_template.php';

// акт 
class act extends AbstractOrdersTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $order = $this->all_configs['db']->query(
            'SELECT o.*, a.fio as a_fio, e.fio as engineer, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {users} as e ON e.id=o.engineer 
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();
        if ($order) {
            $this->editor = true;
            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.*, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();

            // товары и услуги
            $products_rows = array();

            if ($goods) {
                foreach ($goods as $product) {
                    $products_rows[] = array(
                        'title' => htmlspecialchars($product['title']),
                        'price_view' => ($product['price'] / 100) . ' ' . viewCurrency()
                    );
                }
            }

            $products_html_parts = array();
            $num = 1;
            foreach ($products_rows as $prod) {
                $products_html_parts[] = '
                        ' . $num . '</td>
                        <td>' . $prod['title'] . '</td>
                        <td>1</td>
                        <td>' . $prod['price_view'] . '</td>
                        <td>' . $prod['price_view'] . '
                    ';
                $num++;
            }
            $products_html = implode('</td></tr><tr><td>', $products_html_parts);


            $arr = $this->getVariables($order, $goods);
            $arr ['products_and_services'] = array(
                    'value' => $products_html,
                    'name' => l('Товары и услуги (вставляется внутрь таблицы)')
            );
            
            $print_html = $this->generate_template($this->addUsersFieldsValues($order, $arr), 'act');
        }
        return $print_html;
    }
}
