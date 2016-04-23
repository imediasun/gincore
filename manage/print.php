<?php

/**
 * Че вообще происходит:
 * 
 * шаблоны печатных документов берутся из таблицы core_template_vars
 *   - для рестора в ней шаблоны разделены по городам (есть переключалка городов)
 *   - для жинкора шаблоны тянутся с города kiev (без переключалки по городам)
 * 
 * при распаковке новой системы жинкор в core_template_vars город kiev прописываются шаблоны 
 * согласно выбранного языка с таблицы core_admin_translates
 * 
 * итого: если менять чето в шаблонах, нужно менять в core_template_vars для всех городов - это для рестора
 *                                      плюс менять в core_admin_translates - для распаковщика жинкора   
 */

include 'inc_config.php';
include 'inc_func.php';
include 'inc_settings.php';
global $all_configs, $manage_lang;

$langs = get_langs();

//if ($all_configs['configs']['manage-print-default-service-restore']) {
    $cur_lang = isset($_GET['lang']) ? trim($_GET['lang']) : $langs['def_lang'];
//} else {
//    $cur_lang = $manage_lang;
//}

$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$print_html = $variables = '';
$editor = false;

if (!$all_configs['oRole']->is_active()) {
    header('Location: ' . $all_configs['prefix']);
    exit;
}

if (isset($_GET['ajax']) && $all_configs['oRole']->hasPrivilege('site-administration')) {
    $return = array('state' => false, 'msg' => 'Произошла ошибка');

    if ($_GET['ajax'] == 'editor' && isset($_GET['act'])) {
        $save_act = trim($_GET['act']);
        if (in_array($save_act, array('check','warranty','invoice','act', 'invoicing')) && isset($_POST['html'])) {
            // remove empty tags
            $value = preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/", '', trim($_POST['html']));
            $return['state'] = true;
            $var_id = $all_configs['db']->query("SELECT id FROM {template_vars} WHERE var = 'print_template_".$save_act."'")->el();
            $all_configs['db']->query("INSERT INTO {template_vars_strings}(var_id,text,lang) "
                                    . "VALUES(?i,?,?) ON DUPLICATE KEY UPDATE text = VALUES(text)", 
                                            array($var_id, $value, $cur_lang));
        }
    }

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($return);
    exit;
}

function get_template($act){
    global $all_configs, $cur_lang;
    return $all_configs['db']->query("SELECT text FROM {template_vars_strings} as s "
                                    ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
                                    ."WHERE s.lang = ? AND t.var = ?", 
                               array($cur_lang, 'print_template_'.$act), 'el');
}

function generate_template($arr, $act)
{
    global $all_configs, $cur_lang, $manage_lang, $variables;
    $print_html = get_template($act);
    
    foreach ($arr as $k=>$v) {
        $variables .= '<p><b>{{' . $k . '}}</b> - ' . $v['name'] . '</p>';
    }

    // адрес и телефон по-умолчанию
    if(isset($all_configs['configs']['manage-print-default-service-restore']) && 
             $all_configs['configs']['manage-print-default-service-restore']){
        $address = 'г.Киев ул. Межигорская 63';
        $phone = 'тел./факс: (044)393-47-42';
    }else{
        $address = '';
        $phone = '';
    }
    if(empty($arr['wh_address']['value'])){
        $arr['wh_address']['value'] = $address;
    }
    if(empty($arr['wh_phone']['value'])){
        $arr['wh_phone']['value'] = $phone;
    }
    
    $print_html = preg_replace_callback(
        "/\{\{([a-zA-Z0-9_\-]{0,100})\}\}/",
        function ($m) use ($arr, &$variables) {
            $value = '';
            if (isset($arr[$m[1]])) {
                $value = $arr[$m[1]]['value'];
            }
            return '<span data-key="' . $m[1] . '" class="template">' . $value . '</span>';
        },
        $print_html
    );

    return $print_html;
}

if (isset($_GET['barcode']) && isset($_GET['bartype'])) {
    barcode_generate($_GET['barcode'], $_GET['bartype']);
    exit;
}

if (isset($_GET['object_id']) && !empty($_GET['object_id'])) {

    $objects = array_filter(explode(',', $_GET['object_id']));

    foreach ($objects as $object) {

        if ($object == 0) continue;

        // этикетка
        if ($act == 'label') {
            $product = $all_configs['db']->query('SELECT g.barcode, g.title, i.serial, i.id as item_id,
                  o.number, o.parent_id, o.id, o.num
                FROM {goods} as g, {warehouses_goods_items} as i, {contractors_suppliers_orders} as o
                WHERE i.goods_id=g.id AND i.id=?i AND o.id=i.supplier_order_id', array($object))->row();

            if ($product) {
                $print_html .= '<div class="label-box">';

                $src = $all_configs['prefix'] . 'print.php?bartype=sn&barcode=' . suppliers_order_generate_serial($product);
                $print_html .= '<div class="label-box-code"><img src="' . $src . '" alt="S/N" title="S/N" /></div>';

                $print_html .= '<div class="label-box-title">' . htmlspecialchars($product['title']) . '</div>';

                $num = $all_configs['suppliers_orders']->supplier_order_number($product, null, false);
                $print_html .= '<div class="label-box-order">' . $num . '</div>';

                $print_html .= '</div>';
            }
        }

        // склад локация
        if ($act == 'location') {
            $location = $all_configs['db']->query('SELECT w.title, l.location, l.id
                FROM {warehouses} as w, {warehouses_locations} as l
                WHERE l.id=?i AND l.wh_id=w.id', array($object))->row();

            if ($location) {
                $print_html .= '<div class="label-box">';

                $src = $all_configs['prefix'] . 'print.php?bartype=sn&barcode=L-' . $location['id'];
                $print_html .= '<div class="label-box-code"><img src="' . $src . '" alt="S/N" title="S/N" /></div>';

                $print_html .= '<div style="font-size: 1.4em;" class="label-box-title">' .htmlspecialchars($location['location']) . '</div>';

                $print_html .= '</div>';
            }
        }

        // гарантийный талон
        if ($act == 'warranty') {

            $order = $all_configs['db']->query('SELECT o.*, e.fio as engineer, w.title as wh_title, aw.title as aw_title,
                                                aw.print_address,aw.print_phone 
                                                FROM {orders} as o
                                                LEFT JOIN {users} as e ON e.id=o.engineer 
                                                LEFT JOIN {warehouses} as w ON w.id=o.wh_id 
                                                LEFT JOIN {warehouses} as aw ON aw.id=o.accept_wh_id 
                                                WHERE o.id=?i',
                array($object))->row();

            if ($order) {
                $products = $products_cost = $services = '';
                $services_cost = array();


                // товары и услуги
                $goods = $all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                    array($object))->assoc();
                if ($goods) {
                    foreach ($goods as $product) {
                        if ($product['type'] == 0) {
                            $products .= htmlspecialchars($product['title']) . '<br/>';
                            $products_cost .= ($product['price'] / 100) . ' '.viewCurrency().'<br />';
                        }
                        if ($product['type'] == 1) {
                            $services .= htmlspecialchars($product['title']) . '<br/>';
                            $services_cost[] = ($product['price'] / 100);
                        }
                    }
                }

                $editor = true;
                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'date' => array('value' => date("d/m/Y", strtotime($order['date_add'])), 'name' => 'Дата создания заказа на ремонт'),
                    'now' => array('value' => date("d/m/Y", time()), 'name' => 'Текущая дата'),
                    'warranty' => array('value' => $order['warranty'] > 0 ? $order['warranty'] . ' '. l('мес') . '' : 'Без гарантии', 'name' => 'Гарантия'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'defect' => array('value' => htmlspecialchars($order['defect']), 'name' => 'Неисправность'),
                    'engineer' => array('value' => htmlspecialchars($order['engineer']), 'name' => 'Инженер'),
                    'comment' => array('value' => htmlspecialchars($order['comment']), 'name' => 'Внешний вид'),
                    'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                    'sum_paid' => array('value' => $order['sum_paid'] / 100, 'name' => 'Оплаченная сумма'),
                    'products' => array('value' => $products, 'name' => 'Установленные запчасти'),
                    'products_cost' => array('value' => $products_cost, 'name' => 'Установленные запчасти'),
                    'services' => array('value' => $services, 'name' => 'Услуги'),
                    'services_cost' => array('value' => implode(' '.viewCurrency().'<br />', $services_cost), 'name' => 'Стоимость услуг'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'warehouse' =>  array('value' => htmlspecialchars($order['wh_title']), 'name' => 'Название склада'),
                    'warehouse_accept' =>  array('value' => htmlspecialchars($order['aw_title']), 'name' => 'Название склада приема'),
                    'wh_address' =>  array('value' => htmlspecialchars($order['print_address']), 'name' => 'Адрес склада'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                );

                $print_html = generate_template($arr, 'warranty');
            }
        }

        // чек
        if ($act == 'invoice') {
            $type = $all_configs['db']->query("SELECT type FROM {orders} WHERE id = ?i", array($object), 'el');
            $products_rows = array();
            $summ = 0;
            if($type == 0) {
                $order = $all_configs['db']->query(
                    'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                            wa.print_phone, wa.title as wa_title, wag.address as accept_address
                    FROM {orders} as o
                    LEFT JOIN {users} as a ON a.id=o.accepter
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id=?i', array($object))->row();
            }

            if($type == 3) {
                $order = $all_configs['db']->query(
                    "SELECT o.*, g.title as g_title, g.item_id, wag.address as accept_address,wa.print_phone FROM {orders} as o
                    LEFT JOIN {orders_goods} as g ON g.order_id = o.id
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id = ?i", array($object))->row();
            }


            if ($order) {

                // товары и услуги
                $goods = $all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                    array($object))->assoc();
                if ($goods) {
                    foreach ($goods as $product) {
                        $products_rows[] = array(
                            'title' => htmlspecialchars($product['title']),
                            'price_view' => ($product['price'] / 100).' '.viewCurrency()
                        );
                    }
                }
                $summ = $order['sum'];
                
                $products_html_parts = array();
                $num = 1;
                foreach($products_rows as $prod){
                    $products_html_parts[] = '
                        '.$num.'</td>
                        <td>'.$prod['title'].'</td>
                        <td>1</td>
                        <td>'.$prod['price_view'].'</td>
                        <td>'.$prod['price_view'].'
                    ';
                    $num ++;
                }
                $qty_all = $num-1;
                $products_html = implode('</td></tr><tr><td>', $products_html_parts);

                $editor = true;
                        include './classes/php_rutils/struct/TimeParams.php';
                        include './classes/php_rutils/Dt.php';
                        include './classes/php_rutils/Numeral.php';
                        include './classes/php_rutils/RUtils.php';
                        $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($summ / 100, false, 
                                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['gender'],
                                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['words']);
                        $params = new \php_rutils\struct\TimeParams();
                        $params->date = null;
                        $params->format = 'd F Y';
                        $params->monthInflected = true;
                        $str_date = \php_rutils\RUtils::dt()->ruStrFTime($params);

                
                if($order['type'] == 0) {
                    $arr = array(
                        'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                        'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                        'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                        'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                        'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                        'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                        'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                        'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                        'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                        'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                        'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                        'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                        'color' => array('value' => $order['color']?htmlspecialchars($all_configs['configs']['devices-colors'][$order['color']]):'', 'name' => 'Устройство'),
                        'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                        'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                        'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                        'order_data' => array('value' => date('d/m/Y', $order['date_add']), 'name' => 'Дата создания заказа'),
                    );
                }

                if($order['type'] == 3) {

                    $arr = array(
                        'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                        'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                        'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                        'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                        'product' => array('value' => htmlspecialchars($order['g_title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                        'serial' => array('value' => suppliers_order_generate_serial($order), 'name' => 'Серийный номер'),
                        'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                        'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                        'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                        'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                        'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                        'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                    );
                }


                $print_html = generate_template($arr, 'invoice');
            }
        }

        // акт
        if ($act == 'act') {
            $order = $all_configs['db']->query(
                'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();
            if ($order) {
                $editor = true;
                
                 // товары и услуги
                $products_rows = array();
                $summ = $sum_by_products_and_services = $sum_by_products = $sum_by_services = 0;

                $products = $products_cost = $services = '';
                $services_cost = array();
                $goods = $all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                    array($object))->assoc();
                if ($goods) {
                    foreach ($goods as $product) {
                        $products_rows[] = array(
                            'title' => htmlspecialchars($product['title']),
                            'price_view' => ($product['price'] / 100).' '.viewCurrency()
                        );
                        $sum_by_products_and_services += $product['price'];
                        if ($product['type'] == 0) {
                            $products .= htmlspecialchars($product['title']) . '<br/>';
                            $products_cost .= ($product['price'] / 100) . ' '.viewCurrency().'<br />';
                            $sum_by_products += $product['price'];
                        }
                        if ($product['type'] == 1) {
                            $services .= htmlspecialchars($product['title']) . '<br/>';
                            $services_cost[] = ($product['price'] / 100). ' '.viewCurrency();
                            $sum_by_services += $product['price'];
                        }
                    }
                }
                $summ = $order['sum'];
                
                $products_html_parts = array();
                $num = 1;
                foreach($products_rows as $prod){
                    $products_html_parts[] = '
                        '.$num.'</td>
                        <td>'.$prod['title'].'</td>
                        <td>1</td>
                        <td>'.$prod['price_view'].'</td>
                        <td>'.$prod['price_view'].'
                    ';
                    $num ++;
                }
                $qty_all = $num-1;
                $products_html = implode('</td></tr><tr><td>', $products_html_parts);
                
                include './classes/php_rutils/struct/TimeParams.php';
                include './classes/php_rutils/Dt.php';
                include './classes/php_rutils/Numeral.php';
                include './classes/php_rutils/RUtils.php';
                $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($order['sum'] / 100, false, 
                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['gender'],
                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['words']);
                $params = new \php_rutils\struct\TimeParams();
                $params->date = null;
                $params->format = 'd F Y';
                $params->monthInflected = true;
                $str_date = \php_rutils\RUtils::dt()->ruStrFTime($params);
                
                $arr = array(
                    'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                    'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                    'sum_by_products_and_services' => array('value' => $sum_by_products_and_services / 100, 'name' => 'Сумма за запчасти и услуги'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'color' => array('value' => $order['color']?htmlspecialchars($all_configs['configs']['devices-colors'][$order['color']]):'', 'name' => 'Устройство'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                    'products' => array('value' => $products, 'name' => 'Установленные запчасти'),
                    'products_cost' => array('value' => $products_cost, 'name' => 'Установленные запчасти'),
                    'sum_by_products' => array('value' => $sum_by_products/ 100, 'name' => 'Сумма за запчасти'),
                    'services' => array('value' => $services, 'name' => 'Услуги'),
                    'services_cost' => array('value' => implode(' '.viewCurrency().'<br />', $services_cost), 'name' => 'Стоимость услуг'),
                    'sum_by_services' => array('value' => $sum_by_services / 100, 'name' => 'Сумма за услуги'),
                    'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги (вставляется внутрь таблицы)'),
                );
                $print_html = generate_template($arr, 'act');
            }
        }
        
        // квитанция
        if ($act == 'check') {

            $order = $all_configs['db']->query(
                'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();

            if ($order) {
                $editor = true;

                $src = $all_configs['prefix'] . 'print.php?bartype=sn&barcode=Z-' . $order['id'];
                $barcode = '<img src="' . $src . '" alt="S/N" title="S/N" />';

                $arr = array(
                    'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'comment' => array('value' => htmlspecialchars($order['comment']), 'name' => 'Внешний вид'),
                    'defect' => array('value' => htmlspecialchars($order['defect']), 'name' => 'Неисправность'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'prepay' => array('value' => $order['prepay'] / 100, 'name' => 'Предоплата'),
                    'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                    'repair' => array('value' => '', 'name' => 'Вид ремонта'),
                    'complect' => array('value' => '', 'name' => 'Комплектация'),
                    'date' => array('value' => date("d/m/Y", strtotime($order['date_add'])), 'name' => 'Дата создания заказа на ремонт'),
                    'accepter' => array('value' => htmlspecialchars($order['a_fio']), 'name' => 'Приемщик'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'warehouse' =>  array('value' => htmlspecialchars($order['wh_title']), 'name' => 'Название склада'),
                    'warehouse_accept' =>  array('value' => htmlspecialchars($order['wa_title']), 'name' => 'Название склада приема'),
                    'barcode' => array('value' => $barcode, 'name' => 'Штрихкод'),
                    'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                    'wh_address' =>  array('value' => htmlspecialchars($order['print_address']), 'name' => 'Адрес склада'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'domain' => array('value' => $_SERVER['HTTP_HOST'], 'name' => 'Домен сайта'),
                    'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                    'order_data' => array('value' => date('d/m/Y', strtotime($order['date_add'])), 'name' => 'Дата создания заказа'),
                );
                $arr['repair']['value'] = $order['repair'] == 0 ? 'Платный' : $arr['repair']['value'];
                $arr['repair']['value'] = $order['repair'] == 1 ? 'Гарантийный' : $arr['repair']['value'];
                $arr['repair']['value'] = $order['repair'] == 2 ? 'Доработка' : $arr['repair']['value'];

                $arr['complect']['value'] .= $order['battery'] == 1 ? l('Аккумулятор') . '<br />' : '';
                $arr['complect']['value'] .= $order['charger'] == 1 ? l('Зарядное устройств кабель') . '<br />' : '';
                $arr['complect']['value'] .= $order['cover'] == 1 ? l('Задняя крышка') . '<br />' : '';
                $arr['complect']['value'] .= $order['box'] == 1 ? l('Коробка').'</br>' : '';
                $arr['complect']['value'] .= $order['equipment'] ? $order['equipment'] : '';

                $print_html = generate_template($arr, 'check');
            }
        }
        
        // Счет на оплату
        // чек
        if ($act == 'invoicing') {
            $type = $all_configs['db']->query("SELECT type FROM {orders} WHERE id = ?i", array($object), 'el');
            $products_rows = array();
            $summ = 0;
            if($type == 0) {
                $order = $all_configs['db']->query(
                    'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                            wa.print_phone, wa.title as wa_title, wag.address as accept_address
                    FROM {orders} as o
                    LEFT JOIN {users} as a ON a.id=o.accepter
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id=?i', array($object))->row();
            }

            if($type == 3) {
                $order = $all_configs['db']->query(
                    "SELECT o.*, g.title as g_title, g.item_id, wag.address as accept_address,wa.print_phone FROM {orders} as o
                    LEFT JOIN {orders_goods} as g ON g.order_id = o.id
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id = ?i", array($object))->row();
            }


            if ($order) {

                // товары и услуги
                $goods = $all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                    array($object))->assoc();
                if ($goods) {
                    foreach ($goods as $product) {
                        $products_rows[] = array(
                            'title' => htmlspecialchars($product['title']),
                            'price_view' => ($product['price'] / 100).' '.viewCurrency()
                        );
                    }
                }
                $summ = $order['sum'];
                
                $products_html_parts = array();
                $num = 1;
                foreach($products_rows as $prod){
                    $products_html_parts[] = '
                        '.$num.'</td>
                        <td>'.$prod['title'].'</td>
                        <td>1</td>
                        <td>'.$prod['price_view'].'</td>
                        <td>'.$prod['price_view'].'
                    ';
                    $num ++;
                }
                $qty_all = $num-1;
                $products_html = implode('</td></tr><tr><td>', $products_html_parts);

                $editor = true;
                        include './classes/php_rutils/struct/TimeParams.php';
                        include './classes/php_rutils/Dt.php';
                        include './classes/php_rutils/Numeral.php';
                        include './classes/php_rutils/RUtils.php';
                        $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($summ / 100, false, 
                                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['gender'],
                                                                                 $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['words']);
                        $params = new \php_rutils\struct\TimeParams();
                        $params->date = null;
                        $params->format = 'd F Y';
                        $params->monthInflected = true;
                        $str_date = \php_rutils\RUtils::dt()->ruStrFTime($params);

                
                if($order['type'] == 0) {
                    $arr = array(
                        'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                        'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                        'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                        'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                        'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                        'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                        'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                        'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                        'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                        'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                        'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                        'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                        'color' => array('value' => $order['color']?htmlspecialchars($all_configs['configs']['devices-colors'][$order['color']]):'', 'name' => 'Устройство'),
                        'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                        'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                        'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                        'order_data' => array('value' => date('d/m/Y', $order['date_add']), 'name' => 'Дата создания заказа'),
                    );
                }

                if($order['type'] == 3) {

                    $arr = array(
                        'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                        'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                        'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                        'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                        'product' => array('value' => htmlspecialchars($order['g_title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                        'serial' => array('value' => suppliers_order_generate_serial($order), 'name' => 'Серийный номер'),
                        'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                        'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                        'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                        'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                        'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                        'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                        'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                        'order_data' => array('value' => date('d/m/Y', $order['date_add']), 'name' => 'Дата создания заказа'),
                    );
                }


                $print_html = generate_template($arr, 'invoicing');
            }
        }
    }
    if ($print_html && $editor == true && $all_configs['oRole']->hasPrivilege('site-administration')) {
        $tpl = get_template($act);
        $print_html = 
                      '<div class="well unprint">' .
                      '<button class="btn btn-small btn-primary" id="editRedactor"><i class="icon-edit"></i> Редактировать</button> ' .
                      '<button class="btn btn-small btn-success" id="saveRedactor"><i class="icon-ok"></i> Сохранить</button> ' .
                      '<button class="btn btn-small btn-" id="print"><i class="icon-print"></i> Печать</button>' .
                      '<br><br><h4><p class="text-success">Допустимые переменные</p></h4>' .
                      $variables . '</div>' .
                      '<div style="display:none" id="print_tempalte">'.$tpl.'</div>'.
                      '<div id="redactor">' . $print_html . '</div>';
    }
    if($print_html){
        $l_sel = '';
        if(!empty($all_configs['configs']['manage-print-city-select']) && in_array($act, array('check', 'warranty', 'act', 'invoice'))){
            $langs_select = '';
            foreach($langs['langs'] as $l){
                $langs_select .= '<option'.($cur_lang == $l['url'] ? ' selected' : '').' value="'.$l['url'].'">'.$l['name'].'</option>';
            }
            $l_sel = '<div style="margin:0" class="well unprint"><form style="margin:0" method="get" action="'.$all_configs['prefix'].'print.php">'
                          .'<input type="hidden" name="act" value="'.$_GET['act'].'">'.
                           '<input type="hidden" name="object_id" value="'.$_GET['object_id'].'">'.
                           '<select id="lang_change" name="lang">'.$langs_select.'</select>'.
                      '</form></div>';
        }
        if($act == 'location'){
            $print_html .= '
                <div class="printer_preview unprint">
                    <div class="row" style="text-align: center">
                        <button class="btn btn-primary" onclick="javascript:window.print()"><i class="cursor-pointer fa fa-print"></i> Печать</button>
                    </div>
                    <p><i class="fa fa-info-circle"></i>Формат этикеток настроен под печать на термопринтере HPRT LPQ58</p>
                    <img src="'.$all_configs['prefix'].'img/hprt_lpq58.jpg">
                </div>
            ';
        }
        $print_html = $l_sel.$print_html;
    }
}


if ($print_html) {?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Печать</title>

        <script type="text/javascript" src="<?=$all_configs['prefix'];?>js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="<?=$all_configs['prefix'];?>js/bootstrap.js"></script>
        <script type="text/javascript" src="<?=$all_configs['prefix'];?>js/summernote.js"></script>

        <?=isset($_GET['act']) && in_array($_GET['act'], array('label', 'location')) ? '' : '
            <link rel="stylesheet" href="' . $all_configs['prefix'] . 'css/summernote.css" />
        ' ?>
            <link rel="stylesheet" href="<?= $all_configs['prefix'] ?>css/bootstrap.min.css" />
            <link rel="stylesheet" href="<?= $all_configs['prefix'] ?>css/font-awesome.css">

        <style>
            /* print begin */
            .printer_preview{
                position: absolute;
                right: 20px;
                width: 300px;
                top: 20px;
            }
            .printer_preview p{
                margin-top: 20px;
                line-height: 20px;
                font-size: 16px;
                text-align: center;
            }
            .printer_preview p > i.fa {
                color: indianred; font-size: 1.3em; margin-right: 10px
            }
            .printer_preview img{
                width: 100%;
            }
            @media print {
                .label-box {
                    page-break-before: always;
                    page-break-inside: avoid;
                }
                /*.label-box:first-child {
                    page-break-before: avoid;
                }*/
            }

            @media print {
                .unprint {
                    display : none;
                }
            }
            /* print end */

            body, html {
                font-size: 11px;
                line-height: 12px;
                margin: 0;
                padding: 0;
            }

            li {
                line-height: 14px;
            }

            th, td {
                padding: 2px 4px !important;
            }

            p {
                margin: 0 0 5px;
            }
            /* normalize end */

            /* redactor begin */
            #redactor .template_key, .note-editor .template_value {
                display: none;
            }
            /* redactor end */

            /* label begin */
            .label-box {
                /*height: 2.5cm;*/
                overflow: hidden;
                /*width: 4cm;*/
                display: block;
                margin: 3px 0 1px 2px;
            }

            .label-box-title {
                margin-bottom: 3px;
                max-height: 36px;
                overflow: hidden;
            }

            .label-box-code {
                text-align: center;
            }

            .label-box-order {
                text-align: center;
            }

            /* label end */
        </style>

        <script type="text/javascript">

            $(function () {
                $('#lang_change').change(function(){
                    window.location = $(this).parent().attr('action')+'?'+$(this).parent().serialize();
                });
                
                $('#saveRedactor').prop('disabled', true);
                $('#editRedactor').click(function() {
                    $(this).prop('disabled', true);
                    $('#redactor').hide();
                    $('#print_tempalte').show().summernote({
                        focus: true,
                        lang: 'ru-RU',
                        oninit: function(a) {
                            $('#saveRedactor').prop('disabled', false);
                            $('#print').prop('disabled', true);
                        }
                    });
                });

                $('#saveRedactor').click(function() {
                    var _this = this;
                    $(_this).prop('disabled', true);
                    // save content if you need
                    $.ajax({
                        type: 'POST',
                        url: window.location.search + '&ajax=editor',
                        data: {html: $('#print_tempalte').code()},
                        cache: false,
                        success: function(msg) {
                            if (msg) {
                                if (msg['state'] == false && msg['msg']) {
                                    alert(msg['msg']);
                                }
                                if (msg['state'] == true) {
                                    // destroy editor
                                    //$('#redactor').destroy();
                                    window.location.reload();
                                }
                            }
                            $(_this).prop('disabled', false);
                            $('#print').prop('disabled', false);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(xhr.responseText, 1);
                        }
                    });
                });

                $('#print').click(function () {
                    window.print();
                });
            });

        </script>

    </head>
    <body><?=$print_html;?></body>
    </html>
    <?php
} else {
    header('Location: ' . $all_configs['prefix']);
    exit;
}

function barcode_generate($barcode, $type)
{
    require_once('classes/BCG/BCGFontFile.php');
    require_once('classes/BCG/BCGColor.php');
    require_once('classes/BCG/BCGDrawing.php');

    $font = new BCGFontFile('classes/BCG/font/Arial.ttf', 10);
    $color_black = new BCGColor(0, 0, 0);
    $color_white = new BCGColor(255, 255, 255);


    // Barcode Part
    if ($type == 'sn') {
        require_once('classes/BCG/BCGcode128.barcode.php');
        $code = new BCGcode128();

        $code->setScale(1);
        $code->setThickness(35);

    } elseif ($type == 'ean') {
        require_once('classes/BCG/BCGean13.barcode.php');
        $code = new BCGean13();

        $code->setScale(1.5);
        $code->setThickness(35);

    } else {
        require_once('classes/BCG/BCGcodabar.barcode.php');
        $code = new BCGcodabar();
    }

    $code->setForegroundColor($color_black);
    $code->setBackgroundColor($color_white);
    $code->setFont($font);

    header('Content-Type: image/png');

    try {
        $code->parse($barcode);
        // Drawing Part
        $drawing = new BCGDrawing('', $color_white);
        $drawing->setBarcode($code);
        $drawing->draw();
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    } catch(Exception $e) {
        $im = imagecreate(1, 1);
        $background_color = imagecolorallocate($im, 255, 255, 255);
        imagepng($im);
        imagedestroy($im);
    }

}