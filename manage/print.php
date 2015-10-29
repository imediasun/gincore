<?php

include 'inc_config.php';
include 'inc_func.php';
include 'inc_settings.php';
global $all_configs;

$langs = get_langs();

$cur_lang = isset($_GET['lang']) ? trim($_GET['lang']) : $langs['def_lang'];

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
        if (in_array($save_act, array('check','warranty','invoice','act')) && isset($_POST['html'])) {
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

function generate_template($arr, $act)
{
    global $all_configs, $cur_lang, $variables;
    
    $print_html = $all_configs['db']->query("SELECT text FROM {template_vars_strings} as s "
                                           ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
                                           ."WHERE s.lang = ? AND t.var = ?", 
                                        array($cur_lang, 'print_template_'.$act), 'el');
    
//    $print_html = isset($all_configs['settings']['print_template_' . $act]) ? $all_configs['settings']['print_template_' . $act] : '<br>';

    foreach ($arr as $k=>$v) {
        $variables .= '<p><b>{{' . $k . '}}</b> - ' . $v['name'] . '</p>';
    }

    // адрес и телефон по-умолчанию
    $address = 'г.Киев ул. Межигорская 63';
    $phone = 'тел./факс: (044)393-47-42';
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
                //$variables .= '<p><b>{{' . $m[1] . '}}</b> - ' . $arr[$m[1]]['name'] . '</p>';
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

                //$src = $all_configs['prefix'] . 'print.php?bartype=ean&barcode=' . $product['barcode'];
                //$print_html .= '<div class="label-box-code"><img src="' . $src . '" alt="EAN" title="EAN" /></div>';

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

                $print_html .= '<div style="font-size: 1.4em;" class="label-box-title">' ./* htmlspecialchars($location['title']) . ' ' .*/ htmlspecialchars($location['location']) . '</div>';

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
                $products = $products_cost = $services = $services_cost = '';

                $services_cost = $order['sum'] / 100;

                // товары и услуги
                $goods = $all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                    array($object))->assoc();
                if ($goods) {
                    foreach ($goods as $product) {
                        if ($product['type'] == 0) {
                            $products .= htmlspecialchars($product['title']) . '<br/>';
                            $products_cost .= ($product['price'] / 100) . ' '.viewCurrency().'<br />';
                            $services_cost -= ($product['price'] / 100);
                        }
                        if ($product['type'] == 1) {
                            $services .= htmlspecialchars($product['title']) . '<br/>';
                            //$services_cost .= ($product['price'] / 100) . ''.viewCurrency().'<br />';
                        }
                    }
                }

                $editor = true;
                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'date' => array('value' => date("d/m/Y", strtotime($order['date_add'])), 'name' => 'Дата создания заказа на ремонт'),
                    'now' => array('value' => date("d/m/Y", time()), 'name' => 'Текущая дата'),
                    'warranty' => array('value' => $order['warranty'] > 0 ? $order['warranty'] . ' мес.' : 'Без гарантии', 'name' => 'Гарантия'),
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
                    'services_cost' => array('value' => $services_cost, 'name' => 'Стоимость услуг'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'warehouse' =>  array('value' => htmlspecialchars($order['wh_title']), 'name' => 'Название склада'),
                    'warehouse_accept' =>  array('value' => htmlspecialchars($order['aw_title']), 'name' => 'Название склада приема'),
                    'wh_address' =>  array('value' => htmlspecialchars($order['print_address']), 'name' => 'Адрес склада'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                );

                $print_html = generate_template($arr, 'warranty');
            }
        }

        // квитанция
        if ($act == 'invoice') {
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
                include './classes/php_rutils/struct/TimeParams.php';
                include './classes/php_rutils/Dt.php';
                include './classes/php_rutils/Numeral.php';
                include './classes/php_rutils/RUtils.php';
                $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($order['sum'] / 100, false, 
                                                                         $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['gender'],
                                                                         $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['words']);
                $params = new \php_rutils\struct\TimeParams();
                $params->date = null; //default value, 'now'
                $params->format = 'd F Y';
                $params->monthInflected = true;
                $arr = array(
                    'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                    'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                    'address' =>  array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                    'now' => array('value' => \php_rutils\RUtils::dt()->ruStrFTime($params), 'name' => 'Текущая дата'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'color' => array('value' => htmlspecialchars($all_configs['configs']['devices-colors'][$order['color']]), 'name' => 'Устройство'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                );
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
                include './classes/php_rutils/struct/TimeParams.php';
                include './classes/php_rutils/Dt.php';
                include './classes/php_rutils/Numeral.php';
                include './classes/php_rutils/RUtils.php';
                $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($order['sum'] / 100, false, 
                                                                         $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['gender'],
                                                                         $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']]['rutils']['words']);
                $params = new \php_rutils\struct\TimeParams();
                $params->date = null; //default value, 'now'
                $params->format = 'd F Y';
                $params->monthInflected = true;
                $arr = array(
                    'id'  => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'now' => array('value' => \php_rutils\RUtils::dt()->ruStrFTime($params), 'name' => 'Текущая дата'),
                    'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'product' => array('value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']), 'name' => 'Устройство'),
                    'color' => array('value' => htmlspecialchars($all_configs['configs']['devices-colors'][$order['color']]), 'name' => 'Устройство'),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'company' => array('value' => htmlspecialchars($all_configs['settings']['site_name']), 'name' => 'Название компании'),
                    'wh_phone' =>  array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                );
                $print_html = generate_template($arr, 'act');
            }
        }
        
        // чек
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
                );
                $arr['repair']['value'] = $order['repair'] == 0 ? 'Платный' : $arr['repair']['value'];
                $arr['repair']['value'] = $order['repair'] == 1 ? 'Гарантийный' : $arr['repair']['value'];
                $arr['repair']['value'] = $order['repair'] == 2 ? 'Доработка' : $arr['repair']['value'];

                $arr['complect']['value'] .= $order['battery'] == 1 ? 'Аккумулятор<br />' : '';
                $arr['complect']['value'] .= $order['charger'] == 1 ? 'Зарядное устройство/кабель<br />' : '';
                $arr['complect']['value'] .= $order['cover'] == 1 ? 'Задняя крышка<br />' : '';
                $arr['complect']['value'] .= $order['box'] == 1 ? ' Коробка' : '';

                $print_html = generate_template($arr, 'check');
            }
        }
    }
    if ($print_html && $editor == true && $all_configs['oRole']->hasPrivilege('site-administration')) {
        $print_html = '<div class="well unprint">' .
                      '<button class="btn btn-small btn-primary" id="editRedactor"><i class="icon-edit"></i> Редактировать</button> ' .
                      '<button class="btn btn-small btn-success" id="saveRedactor"><i class="icon-ok"></i> Сохранить</button> ' .
                      '<button class="btn btn-small btn-" id="print"><i class="icon-print"></i> Печать</button>' .
                      '<br><br><h4><p class="text-success">Допустимые переменные</p></h4>' .
                      $variables . '</div>' .
                      '<div id="redactor">' . $print_html . '</div>';
    }
    if($print_html){
        $l_sel = '';
        if(in_array($act, array('check', 'warranty'))){
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
                    <p>Формат этикеток настроен под печать на термопринтере HPRT LPQ58</p>
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

        <?=isset($_GET['act']) && in_array($_GET['act'], array('label', 'location')) ? '' :
            '<link rel="stylesheet" href="' . $all_configs['prefix'] . 'css/bootstrap.min.css" />
            <link rel="stylesheet" href="' . $all_configs['prefix'] . 'css/font-awesome.css">
            <link rel="stylesheet" href="' . $all_configs['prefix'] . 'css/summernote.css" />';?>

        <style>
            /* print begin */
            .printer_preview{
                position: absolute;
                right: 20px;
                width: 300px;
                top: 20px;
            }
            .printer_preview p{
                line-height: 20px;
                font-size: 16px;
                text-align: center;
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
                    $('#redactor').summernote({
                        focus: true,
                        lang: 'ru-RU',
                        oninit: function(a) {
                            $('#saveRedactor').prop('disabled', false);
                            $('#print').prop('disabled', true);
                            $('.note-editor .note-editable').find('.template').replaceWith(function () {
                                return '{{' + $(this).data('key') + '}}';
                            });
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
                        data: {html: $('#redactor').code()},
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
    require_once('BCG/BCGFontFile.php');
    require_once('BCG/BCGColor.php');
    require_once('BCG/BCGDrawing.php');

    $font = new BCGFontFile('BCG/font/Arial.ttf', 10);
    $color_black = new BCGColor(0, 0, 0);
    $color_white = new BCGColor(255, 255, 255);


    // Barcode Part
    if ($type == 'sn') {
        require_once('BCG/BCGcode128.barcode.php');
        $code = new BCGcode128();

        $code->setScale(1);
        $code->setThickness(35);

    } elseif ($type == 'ean') {
        require_once('BCG/BCGean13.barcode.php');
        $code = new BCGean13();

        $code->setScale(1.5);
        $code->setThickness(35);

    } else {
        require_once('BCG/BCGcodabar.barcode.php');
        $code = new BCGcodabar();
    }

//    $code->setScale(2);
//    $code->setThickness(30);

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
        //$text_color = imagecolorallocate($im, 0, 0, 0);
        //imagestring($im, 3, 1, 1, strtoupper($type), $text_color);
        imagepng($im);
        imagedestroy($im);
    }

}