<?php

class Cart
{
    public $db;
    public $config;
    public $id;
    public $row_id;
    public $cfg;

    public $path;
    public $prefix;

    public $region = 12;
    public $d_city = 30;
    public $city = 13;

    function __construct ($db, $prefix, $path, $cfg, $id = '')
    {
        $this->prefix = $prefix;
        $this->path = $path;

        //global $cfg;

        $this->cfg = $cfg;

        $this->configs = Configs::getInstance()->get();

        $this->db= $db;

        if ( isset($_SESSION) ) {
            if ( isset($_SESSION['user_id']) ) {
                $this->id = intval($_SESSION['user_id']);
                $this->row_id = 'user_id';
            } elseif (isset($_SESSION['guest_id'])) {
                $this->id = intval($_SESSION['guest_id']);
                $this->row_id = 'guest_id';
            }
        }
        if( intval($id) > 0) {
            $this->id=$id;
            $this->row_id = 'user_id';
        }

    }

    function show_phones($region, $city)
    {
        $phones_block = '';
        $phone_selected_title = '';
        $phone_select = '';

        $phones = $this->db->query('SELECT phone FROM {offices}
                WHERE region=?i AND city=?i AND phone<>"" AND avail_phone<>"" ORDER BY phone DESC',
            array($region, $city))->assoc();
        if (!$phones) {
            $phones = $this->db->query('SELECT phone FROM {offices}
                    WHERE region=?i AND city=?i AND phone<>"" AND avail_phone<>"" ORDER BY phone DESC',
                array($this->configs['default-region'], $this->configs['default-city']))->assoc();
        }

//echo '<pre>';print_r($phones);exit;
        if ( $phones ) {
            $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span>';

            //if ( count($phones) > 1 ) {
                foreach ( $phones as $phone ) {

                    $phone_code = substr(trim($phone['phone']), -10, 3);
                    $phone_1 = substr(trim($phone['phone']), -7, 3);
                    $phone_2 = substr(trim($phone['phone']), -4, 2);
                    $phone_3 = substr(trim($phone['phone']), -2, 2);

                    /*$phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">' . $phone_code . '</span>';
                    if ( $phone_code == '044')
                        $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">(' . $phone_code . ')</span>';
                    if ( $phone_code == '050' || $phone_code == '066' || $phone_code == '095' || $phone_code == '099')
                        $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/mts.png" alt="phone:"></span><span class="phone_code">' . $phone_code . '</span>';
                    if ( $phone_code == '063' || $phone_code == '093')
                        $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/life.png" alt="phone:" /></span><span class="phone_code">' . $phone_code . '</span>';
                    if ( $phone_code == '039' || $phone_code == '067' || $phone_code == '068' || $phone_code == '096' || $phone_code == '097' || $phone_code == '098')
                        $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kievstar.png" alt="phone:" /></span><span class="phone_code">' . $phone_code . '</span>';
                    $end_phone = $phone_img . $phone_1 . '-' . $phone_2 . '-' . $phone_3;
                    if ( $phone_code == '044')
                        $end_show_phone = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">(' . $phone_code . ')</span>' . $phone_1 . '-' . $phone_2 . '-' . $phone_3;
                    else
                        $end_show_phone = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">' . $phone_code . '</span>' . $phone_1 . '-' . $phone_2 . '-' . $phone_3;*/
                    //$phone_code = $phone_code == '044' ? '(' . $phone_code . ') ' : $phone_code . ' ';
                    //$end_phone = $phone_code . $phone_1 . '-' . $phone_2 . '-' . $phone_3;
                    $end_phone = '(' . $phone_code . ')' . $phone_1 . '-' . $phone_2 . '-' . $phone_3;

                    // блок выбора телефона
                    /*$is_active = $phone_code == '044';
                    if ($is_active) {
                        $phone_selected_title = $phone_img . $end_phone;
                        continue;
                    }*/
                    $phone_select .= '<li>' . $end_phone . '</li>';

                }
                //if (empty($phone_selected_title)) $phone_select .= '<li><a href="javascript:void(0)">' . $end_phone . '</a></li>';

                if (empty($phone_selected_title)) $phone_selected_title = $phone_img . $end_phone;
                $phones_block = '
                    <div class="region_divider"></div>
                    <div class="text-left nav_phone">

                        <div class="selected_phone my_select_btn" data-select="8">
                            <span><span class="phone_name">' . $phone_selected_title . '</span></span>▼&nbsp;&nbsp;
                        </div>
                        <div class="my_select_body" data-select="8">
                            <ul id="change_phone" class="phone_div">' . $phone_select . '
                            <li><a class="call-back on_load_popup" data-content="callback-popup" href="#">Заказать обратный звонок</a></li></ul>
                        </div>

                    </div>';
            /*} else {
                $phone_code = substr($phones[0]['phone'], 0, 3);
                $phone_1 = substr($phones[0]['phone'], 3, 3);
                $phone_2 = substr($phones[0]['phone'], 6, 2);
                $phone_3 = substr($phones[0]['phone'], 8, 2);

                $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">' . $phone_code . '</span>';
                if ( $phone_code == '044')
                    $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">(' . $phone_code . ')</span>';
                if ( $phone_code == '050' || $phone_code == '066' || $phone_code == '095' || $phone_code == '099')
                    $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:"></span><span class="phone_code">' . $phone_code . '</span>';
                if ( $phone_code == '063' || $phone_code == '093')
                    $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:" /></span><span class="phone_code">' . $phone_code . '</span>';
                if ( $phone_code == '039' || $phone_code == '067' || $phone_code == '068' || $phone_code == '096' || $phone_code == '097' || $phone_code == '098')
                    $phone_img = '<span class="phone_img"><img src="'. $this->prefix . 'images/kontakty.png" alt="phone:" /></span><span class="phone_code">' . $phone_code . '</span>';

                $phones_block = '
                    <div class="region_divider"></div>
                    <div class="text-left nav_phone">
                        ' . $phone_img . $phone_1 . '-' . $phone_2 . '-' . $phone_3 . '&nbsp;&nbsp;
                    </div>';
            }*/
        }

        return $phones_block;
    }

    // считаем цену товара с выбраной гарантией и количеством
    function item_price($model, $item_price = null, $warranty_price = null, $item_qty = null, $rel = null, $view = true, $course_key = null, $course_value = null, $one = false, $discount = 0) {

        $item_price = $model->get_prices($item_price, false, $course_key, $course_value, $one);
        $warranty_price = $model->get_prices($warranty_price, false, $course_key, $course_value, $one);

        $data = array( );

        foreach ( $item_price as $k=>$v ) {
            if ( $rel && array_key_exists($k, $rel) ) {
                $data[$k] = ($item_price[$k] + $warranty_price[$k]) * $item_qty + $rel[$k] * $item_qty - ($k != 'price' ? $discount * $item_qty : 0);
            } else {
                $data[$k] = ($item_price[$k] + $warranty_price[$k]) * $item_qty - ($k != 'price' ? $discount * $item_qty : 0);
            }
        }
//print_r($data);
        if ( $view ) {
            return $model->currency_view($data);
        } else {
            if ( $course_key && array_key_exists($course_key, $data) && $one == true ) {
                return $data[$course_key];
            } else {
                return $data;
            }
        }
    }

    function get_count_and_price ($order = null)
    {
        $model = new Model();

        $sc = $this->exist_cart(null, $order);

        $count = 0;
        $items_prices = $model->get_prices(0);
        $sum = $model->get_prices(0);

        if ( $sc ) {
            //$count = count($sc);
            foreach ( $sc as $v ) {
                $count += $v['count'];
                $warranty_cost = $this->warranties_cost($v['price'], $v['warranties'], $v['goods_id']);
                if ($order) {//print_r($v);
                    $sum_current = $this->item_price($model, $v['price'], $warranty_cost, $v['count'], null, false,
                        $order['course_key'], $order['course_value'], false, (isset($v['discount']) ? $v['discount'] : 0));
                } else {
                    $sum_current = $this->item_price($model, $v['price'], $warranty_cost, $v['count'], null, false);
                }

                //if ( !$sum ) {
                //    $sum = $sum_current;
                //} else {
                    foreach ( $sum_current as $k=>$val ) {
                        $sum[$k] += $val;
                    }
                //}
                $items_prices[$v['id']] = $model->currency_view($sum_current);
            }
        }
        return array(
            'sum'           =>  $sum,   // сумма товаров
            'count'         =>  $count, // количество товаров в корзине
            'items_prices'  =>  $items_prices, // для смены количества в корзине
        );
    }

    function ajax($data) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    function for_checkout_block_corporation($user_info)
    {
        $company_name = ($user_info && isset($user_info['company_name']) && !isset($_POST['company_name']))?trim($user_info['company_name']):(isset($_POST['company_name'])?trim($_POST['company_name']):'');
        $legal_address = ($user_info && isset($user_info['legal_address']) && !isset($_POST['legal_address']))?trim($user_info['legal_address']):(isset($_POST['legal_address'])?trim($_POST['legal_address']):'');
        $inn = ($user_info && isset($user_info['inn']) && !isset($_POST['inn']))?trim($user_info['inn']):(isset($_POST['inn'])?trim($_POST['inn']):'');
        $kpp = ($user_info && isset($user_info['kpp']) && !isset($_POST['kpp']))?trim($user_info['kpp']):(isset($_POST['kpp'])?trim($_POST['kpp']):'');

        $style = "display:none;";
        if ( ($user_info && $user_info['person'] == 2 && !isset($_POST['person'])) || (isset($_POST['person']) && $_POST['person'] == 'false') ) {
            $style = "";
        }
        return
            '<tr style="' . $style . '" class="checkout-corporation-info">' .
                '<td class="left">Название компании</td>' .
                '<td class="left"><input type="text" class="input" name="company_name" value="' . htmlspecialchars($company_name) . '" /></td>' .
            '</tr>' .
            '<tr style="' . $style . '"  class="checkout-corporation-info">' .
                '<td class="left">Юридический адрес</td>'.
                '<td class="left"><input type="text" class="input" name="legal_address" value="' . htmlspecialchars($legal_address) . '" /></td>' .
            '</tr>' .
            '<tr style="' . $style . '"  class="checkout-corporation-info">' .
                '<td class="left">ИНН</td>'.
                '<td class="left"><input type="text" class="input" name="inn" value="' . htmlspecialchars($inn) . '" /></td>' .
            '</tr>' .
            '<tr style="' . $style . '"  class="checkout-corporation-info">' .
                '<td class="left">КПП</td>'.
                '<td class="left"><input type="text" class="input" name="kpp" value="' . htmlspecialchars($kpp) . '" /></td>' .
            '</tr>';
    }

    function show_checkout_block ($model, $data, $user_info, $order = null, $edit = true, $show_btn = true, $oRole = null)
    {
        $d = ($edit == true) ? '' : 'disabled';

        $cs = $this->get_count_and_price($order);

        $shipping = $this->show_shipping_block($data, $cs['sum'], $model, false, $order, $edit);
        $payment = $this->show_payments_block($data, $shipping['shipping'], $model, $cs['sum'], $order, $edit);

        $fio = $data['fio'];
        $email = $data['email'];
        $phone = $data['phone'];
        $comment = $data['comment'];
        $address = $data['address'];
        $data['payment'] = $payment['payment'];
        $data['shipping'] = $shipping['shipping'];

        $region = $this->show_region_select($data['region'], $edit);
        $city = $this->show_city_select($data['city'], $data['region'], $edit);

        $style = '';
        if ( $shipping['shipping'] != 'courier' && $shipping['shipping'] != 'express' && $shipping['shipping'] != 'courier_today' )
            $style = 'display:none';
        $np_style = '';$np_style_mess = 'display:none';
        if ( $shipping['shipping'] != 'novaposhta_cash' && $shipping['shipping'] != 'novaposhta' ) {
            $np_style = 'display:none';
            $np_style_mess = '';
        }

        $office_style = '';
        if ( $shipping['shipping'] != 'pickup' )
            $office_style = 'display:none';

        $delivery = $this->delivery($data, $model);
        $sum = $this->get_all_price($delivery, $cs['sum']);

        $btn = '';
        $addresses_html = '';

        if ( $order === null ) {
            $addresses = $this->select_address ('address', 'input select-address');
            if ($addresses)
                $addresses_html = '<tr><td class="left">Ваши адреса</td><td class="left">' . $addresses . '</td></tr>';
        } else {
            if ( $order['user_id'] == 0 ) {
                if ( mb_strlen($order['email'], 'UTF-8') > 4 ) {
                    $user_id = $this->db->query('SELECT id FROM {clients} WHERE email=?', array($order['email']))->el();

                    if ( $user_id ) {
                        $btn = '<input ' . $d . ' type="button" data1="' . $user_id . '" data2="' . $order['id'] . '" class="btn client-bind" value="Привязать заказ клиенту" />';
                    }
                }
            }

            $user_info['inn'] = $order['company_inn'];
            $user_info['kpp'] = $order['company_kpp'];
            $user_info['company_name'] = $order['company_name'];
            $user_info['legal_address'] = $order['company_address'];

            $region = $this->show_region_select($order['region'], $edit);
            $city = $this->show_city_select($order['city'], $order['region'], $edit);

            if ( $order['user_id'] == 0 ) {
                if ( mb_strlen($order['email'], 'UTF-8') > 4 ) {
                    $user_id = $this->db->query('SELECT id FROM {clients} WHERE email=?', array($order['email']))->el();
                    if ( $user_id ) {
                        $btn = '<input ' . $d . ' type="button" data1="' . $user_id . '" data2="' . $order['id'] . '" class="btn client-bind" value="Привязать заказ клиенту" />';
                    }
                }
            }
            $address = $order['address'];
            $comment = $order['comment'];
        }

        $checked_person = '';$active_person = 'active';$active_corporation = '';//$show_rate = 'style="display:none"';
        if ( ($user_info && $user_info['person'] == 2 && !isset($_POST['person'])) || (isset($_POST['person']) && $_POST['person'] == 'false' ) ) {
            $checked_person = 'checked';
            $active_corporation = 'active';
            $active_person = '';
            //$show_rate = '';
        }

        return array(
            'data'  =>  $data,
            'html'  =>
                '<div style="margin:0px;' . (($user_info && $order == null) ? 'display:none;' : '') . '">' .
                    '<h2 class="account_title"><span class="sc_iso" id="sc_account_iso"></span>Тип плательщика:</h2>' .
                    '<table class="form-table payer-type"><tbody><tr>' .
                        '<td class="left"><label class="' . $active_person . '"><input ' . $d . ' checked onchange="checkout_change(1)" type="radio" value="true" name="person" /> Физическое лицо</label></td>'.
                        '<td class="left"><label class="' . $active_corporation . '"><input ' . $d . ' ' . $checked_person . ' onchange="checkout_change(1)" type="radio" id="legal-person" value="false" name="person" /> Юридическое лицо</label></td>' .
                    '</tr></tbody></table>' .
                '</div>' .

                '<h2 class="account_title"><span class="sc_iso" id="sc_customer_iso"></span>Заказчик</h2>' .
                '<table class="form-table account_table"><tbody>' .
                    '<tr><td class="left">Ф.И.О.</td>'.
                        '<td class="left"><input ' . $d . ' type="text" class="input" name="fio" value="' . htmlspecialchars($fio) . '" /></td></tr>' .
                    '<tr><td class="left">' . l('Номер телефона') . ' *</td>' .
                        '<td class="left"><input ' . $d . ' type="text" data-type="phone" data-required="true" data-trigger="change" class="input '
                . ((isset($_POST['phone']) && mb_strlen(trim($_POST['phone']),'UTF-8') == 0) ? 'input-error' : '')
                . '" onkeypress="return isNumberKey(event)" name="phone" value="' . htmlspecialchars($phone) . '" /></td></tr>' .
                    '<tr><td class="left">Электронная почта *' .
                        ((!isset($_SESSION['user_id']) && !$order)?'<br />(Ваш аккаунт будет создан автоматически)</td>':'') .
                            '<td class="left">' . $email . $btn . '</td></tr>' .
                    '<tr><td class="left">Область</td><td class="left">' . $region . '</td></tr>'.
                    '<tr><td class="left">Город</td><td class="left" class="city_select">' . $city . '</td></tr>' .
                    $this->for_checkout_block_corporation($user_info) .
                '</tbody></table>' .

                '<h2 class="account_title"><span class="sc_iso" id="sc_shipping_iso"></span>Способ доставки</h2>'.
                '<div id="show_shipping_block">' . $shipping['html'] . '</div>' .
                '<div class="sc-light-block" style="' . $np_style_mess . '" id="delivery_time_block">'.
                    (($order === null)?
                        '<p class="sc-light-block-head center">Сроки доставки: <span id="show_delivery_time_block">' . $delivery['msg'] . '</span<</p>'
                    :'') .
                    /*'<p class="sc-light-block-head">Стоимость доставки</p>' .
                    '<div id="show_currency_block">' . $delivery_cost . '</div>' .*/
                '</div>'.
                '<div id="address_block" style="' . $style . '">' .
                    '<h2 class="account_title"><span class="sc_iso" id="sc_address_iso"></span>Адрес</h2>' .
                    '<table class="form-table account_table"><tbody>' . $addresses_html .
                        '<tr><td class="left">Улица, дом, квартира</td><td class="left">' .
                        '<textarea ' . $d . ' id="tbody-address" class="input" placeholder="Введите адрес" name="address">' . htmlspecialchars($address) . '</textarea></td></tr>' .
                    '</tbody></table>'.
                '</div>' .

                '<div id="office_block" style="' . $office_style . '">' .
                    '<h2 class="account_title"><span class="sc_iso" id="sc_address_iso"></span>Отделение магазина</h2>' .
                    '<table class="form-table account_table"><tbody>' .
                    (($order !== null && isset($order['office']) && !empty($order['office'])) ?
                        '<tr><td class="left">Выбранное отделение</td><td class="left">' . $order['office'] . '</td></tr>' : ''
                    ) .
                    '<tr><td class="left">Выберите из списка</td><td id="offices" class="left">' .
                        (($order !== null) ?
                            $this->offices($order['region'], $order['city'], $data, $edit)
                        : $this->offices($_SESSION['region'], $_SESSION['city'], $data, $edit)
                        ) .
                    '</tbody></table>'.
                '</div>' .

                '<div id="np_address_block" style="' . $np_style . '">' .
                    '<h2 class="account_title"><span class="sc_iso" id="sc_np_iso"></span>Отделение новой почты</h2>' .
                    '<table class="form-table account_table"><tbody>' .
                    (($order !== null && isset($order['np_office']) && !empty($order['np_office'])) ?
                        '<tr><td class="left">Выбранное отделение</td><td class="left">' . $order['np_office'] . '</td></tr>' : ''
                    ) .
                    '<tr><td class="left">Выберите отделение</td><td id="np_select" class="left">' .
                    (($order !== null) ?
                        $this->np_delivery($order['region'], $order['city'], $data, $edit)
                        : $this->np_delivery( $_SESSION['region'], $_SESSION['city'], $data, $edit)
                    ) .
                    '</td></tr>' .
                    '</tbody></table>' .
                '</div>' .

                '<h2 class="account_title"><span class="sc_iso" id="sc_payment_iso"></span>Выбор способа оплаты</h2>' .
                '<div id="show_payment_block">' . $payment['html'] . '</div>' .
                '<table class="form-table account_table"><tbody>' .
                    '<tr><td class="left">Ваш комментарий</td>'.
                    '<td class="left"><textarea ' . ($show_btn == true ? '' : 'disabled') . ' class="input" name="comment">' . htmlspecialchars($comment) . '</textarea></td></tr>' .
                '</tbody></table>' .
                '<div class="cart_footer center font-big-size">' .
                    (($order === null) ?
                        '<a class="text-left" href=' . $this->prefix . $data['arrequest'] . '>&#8592; Вернутся в корзину</a>'
                    : '') .
                    (($show_btn == true) ?
                        '<input type="submit" name="checkout" class="green_btn btn-very-big" value="Подтвердить заказ" />'
                    : '') .
                '</div>'
        );
    }

    function show_block_cart($goods, $model)
    {
        //global $prefix, $path;

        $shc = '';
        $ids = array();
        foreach ($goods as $good) {
            $ids[$good['goods_id']] = $good['id'];
        }

        $desc_goods = $this->db->query('
            SELECT g.id, g.price, g.wait, g.warranties, g.related, g.qty_store as exist, g.no_warranties, g.title, g.url, i.image, i.title as image_title, g.foreign_warehouse
            FROM {goods} as g
            RIGHT JOIN (SELECT goods_id,type,title,image FROM {goods_images} ORDER BY prio)i ON i.goods_id=g.id AND i.type=1
            WHERE g.id IN (?l) GROUP BY i.goods_id', array(array_keys($ids)), 'assoc');

        if (count($desc_goods) > 0) {
            foreach ($goods as $good) {
                $my_select_id = 4;
                foreach ($desc_goods as $desc) {
                    $desc['cart_id'] = $good['id'];
                    $my_select_id++;
                    if ($desc['id'] == $good['goods_id']) {
                        $path_parts = full_pathinfo($desc['image']);
                        //                    $w = '';
                        $m = '';
                        if ($desc['no_warranties'] == 0) {
                            $warranties = unserialize($desc['warranties']);
                            if (!$warranties) {
                                $warranties = $this->configs['warranties'];
                            }
                            if (isset($good['warranties']) && intval($good['warranties']) > 0) {
                                if (array_key_exists(intval($good['warranties']), $this->configs['warranties'])) {
                                    foreach ($this->configs['warranties'][intval($good['warranties'])] as $to => $p1) {
                                        if (intval($desc['price']) <= intval($to) || $to == 'inf') {
                                            //                                        $w = $p1;
                                            $m .= intval($good['warranties']);
                                            break;
                                        }
                                    }
                                }
                            } else {
                                foreach ($warranties as $warranty => $on) {
                                    if (array_key_exists($warranty, $this->configs['warranties'])) {
                                        foreach ($this->configs['warranties'][$warranty] as $to => $p1) {
                                            if (intval($desc['price']) <= intval($to) || $to == 'inf') {
                                                //                                            $w = $p1;
                                                $m .= $warranty;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $exists_qty = $model->get_product_exists_qty($desc);

                        $item_cost = $desc['price'];
                        if (isset($good['count']) && intval($good['count']) > 0) {
                            $goods_count = intval($good['count']);
                            $c = $model->show_select_count($exists_qty, intval($good['count']), $good['id'], true, $my_select_id, 1);
                        } else {
                            $c = $model->show_select_count($exists_qty, null, $good['id'], true, $my_select_id);
                        }

                        $warr = $model->show_warranties($desc, $this, $m, true);
                        $warranty_cost = $warr['w'];
                        $warranties_html =
                            (!empty($warr['html']) ?
                                '<div class="cart_item_block">' .
                                '<div class="cart_item_block_title">Гарантия ('. l('мес') . ')</div>' .
                                '<ul class="cart_warranties_select">' . $warr['html'] . '</ul>' .
                                '</div>' : '');

                        // выбор сервисов

                        $services_html = '';
                        if ($this->configs['services_in_cart_enabled']) {
                            $s = unserialize($desc['related']);
                            $yet = array();
                            if (count($s) > 0) {
                                asort($s, SORT_NUMERIC);
                                $related = $model->get_goods($s);
                                $js_images = array();
                                $image_in_js_array = array(); // temp data
                                foreach ($related as $rel) {
                                    $is_wait = strtotime($rel['wait']) > 0;
                                    if ($rel['id'] == $desc['id'] || (!$rel['exist'] && !$rel['foreign_warehouse'] && !$is_wait))
                                        continue;
                                    $path_parts1 = full_pathinfo($rel['image']);
                                    $mimg = $this->configs['goods-images-path'] . $rel['id'] . '/' . htmlspecialchars($path_parts1['filename']) . $this->configs['medium-image'] . $path_parts1['extension'];

                                    if (!file_exists($this->path . $mimg)) {
                                        $mimg = 'images/iconka_300x300.png';
                                    }
                                    if (array_key_exists($rel['id'], $yet)) {
                                        if (!in_array($this->prefix . $mimg, $image_in_js_array)) {
                                            $image_in_js_array[$rel['id']] = $this->prefix . $mimg;
                                            $js_images[] = 'lightbox_images[' . $rel['id'] . '].push("' . $this->prefix . $mimg . '")';
                                        }
                                        continue;
                                    }
                                    $image_in_js_array[$rel['id']] = $this->prefix . $mimg;
                                    $js_images[] = 'lightbox_images[' . $rel['id'] . ']=[];lightbox_images[' . $rel['id'] . '].push("' . $this->prefix . $mimg . '")';

                                    $img = $this->configs['goods-images-path'] . $rel['id'] . '/' . htmlspecialchars($path_parts1['filename']) . $this->configs['small-image'] . $path_parts1['extension'];
                                    if (!file_exists($this->path . $img)) {
                                        $img = 'images/iconka_50x50.png';
                                    }
                                    $yet[$rel['id']] = $rel['id'];
                                    //$all[$rel['id']] = $rel['id'];
                                    if ($rel['type'] == 1) {
                                        $services_html .= '<li>
                                            <div class="text-left">
                                                <a class="lightbox" data-lightbox="' . $rel['id'] . '" title="' . htmlspecialchars($rel['title']) . '"
                                                        data-content="<div><a target=\'_blank\' class=\'prevent_default\' href=\'' . $this->prefix . htmlspecialchars($rel['url']) . '/' . $this->configs['product-page'] . '/' . $rel['id'] . '\'> ' . htmlspecialchars($rel['title']) . '</a>
                                                        </div>' . htmlspecialchars(tokenTruncate($rel['content'], 200)) . '"
                                                        href="' . $this->prefix . htmlspecialchars($rel['url']) . '/' . $this->configs['product-page'] . '/' . $rel['id'] . '">
                                                    <img alt=" " src="' . $this->prefix . $img . '" >
                                                </a>
                                            </div>
                                            <div class="rb_item">
                                                <div class="rbi_top">
                                                    <label>' .
                                            '<span>' .
                                            htmlspecialchars(tokenTruncate($rel['title'], 40)) .
                                            '</span>
                                        </label>
                                        <div class="red-title">' . $model->cur_currency($rel['price']) . '</div>
                                                </div>
                                                <input data-item_id="' . $desc['cart_id'] . '" data-service="' . $rel['id'] . '" type="button" value="Добавить в корзину" class="add_service_to_cart btn btn-small" />
                                            </div>
                                        </li>';
                                    }
                                }
                                $related_html_js = '<script type="application/javascript">var lightbox_images = [];' . implode(';', $js_images) . '</script>';
                            }
                        }

                        // вывод всего блока товара
                        $shadow_style = '';
                        $img = $this->configs['goods-images-path'] . $desc['id'] . '/' . htmlspecialchars($path_parts['filename']) . $this->configs['medium-image'] . $path_parts['extension'];
                        if (!file_exists($this->path . $img)) {
                            $img = 'images/iconka_300x300.png';
                            $shadow_style = 'style="display:none"';
                        }
                        $shc .= '
                            <div class="cart_item" id="cart_item' . $desc['cart_id'] . '" data-id="' . $desc['id'] . '" data-cart_id="' . $desc['cart_id'] . '">
                                <div class="cart_item_loader" id="cart_item_loader' . $desc['cart_id'] . '"></div>
                                <div class="cart_item_image">
                                    <img title="' . htmlspecialchars($desc['image_title']) . '"
                                        src="' . $this->prefix . $img . '" /><div ' . $shadow_style . ' class="cart_item_image_shadow"></div></td>
                                </div>
                                <div class="cart_item_price">
                                    Цена: <span id="item_price' . $desc['cart_id'] . '">' . $this->item_price($model, $item_cost, $warranty_cost, $goods_count) . '</span>
                                    <div class="remove_item_wrap">
                                            <div class="remove_item_wrap_inner">
                                                <div class="remove_item_confirm">
                                                    <input class="remove-item btn" data-rel="' . $m . '" data-id="' . $good['id'] . '"' .
                            ' type="button" name="remove" value="' . l('Удалить') . '" />
                            <input data-id="' . $good['goods_id'] . '" data-cart_id="' . $desc['cart_id'] . '" class="add-to-wishlist btn" type="button" value="Добавить в список желаний" />
                                                </div>
                                                <input class="remove-item-from-cart btn" type="button" value="Удалить товар из корзины" />
                                            </div>
                                        </div>
                                </div>
                                <div class="text-right sc_count">' . $c . ' ' . l('шт.') . '</div>
                                <div class="cart_item_body">
                                    <div class="cart_item_main_info' . (!$warranties_html && !$services_html ? ' min_height' : '') . '' . (!$warranties_html ? ' no_warranties' : '') . '">
                                        <h2><a href="' . $this->prefix . urlencode($desc['url']) . '/' . $this->configs['product-page'] . '/' . $desc['id'] . '">' . htmlspecialchars($desc['title']) . '</a></h2>' /*
                                        Количество:'.$c.'<br>
                                        <div class="cart_item_price">Цена: <span id="item_price'.$desc['cart_id'].'">'.$this->item_price($model, $item_cost, $warranty_cost, $goods_count).'</span></div>*/ . '
                                        ' . $warranties_html . '
                                    </div>
                                    ' . ($services_html ? '
                                        <div class="cart_item_block">
                                            <div class="cart_item_block_title">
                                                Сервисы и услуги
                                            </div>' . $related_html_js . '
                                            <ul class="cart_related">
                                                ' . $services_html . '
                                            </ul>
                                            <div class="clear_both"></div>
                                        </div>' : '') . '
                                    <input type="hidden" name="goods_id[]" value="' . $good['goods_id'] . '" />
                                    <input type="hidden" id="warranties_hidden' . $good['id'] . '" name="warranties[' . $good['id'] . '][]" value="' . $m . '" />

                                </div>
                                <div class="clear_both"></div>
                            </div>';
                        break;
                    }
                }
            }
        }
        //$cart->get_goods($sc);
        return $shc;
    }

    function add_items($goods, $model/*, $no = 0, $sp = null*/)
    {
        //global $path, $model;
        $data = array();
        $status = null;

        if ( intval($this->id) < 1 )
            return false;

        // создаем папку для картинок
        //if ($sp)
        //    $path = $sp;
        $dir = $this->path . $this->configs['images-path-sc'] . $this->id . '/';
        if ( !is_dir($dir) ) {
            if( mkdir($dir))  {
                chmod( $dir, 0777 );
            } else {
                return false;
            }
        }

        $count_goods = count($goods);

        if ( is_array($goods) && $count_goods > 0 ) {
            $return = array();

            foreach($goods as $id=>$good) {
                // если нету количества или не ясный товар удаляем его
                if ( intval($good['count']) < 1 || intval($good['goods_id']) < 0 ) {
                    unset($goods[$id]);
                    continue;
                }

                /* выставляем приоритеты:
                 *  - если передан $good['parent'](id записи товара в корзине), то новый приоритет = parent_prio + 1,
                 *    и обновляем всем остальным товарам у которых приоритет больше нового +1
                 *  - если парента нет, то новый приоритет = MAX(prio) + 1
                 */
                if(isset($good['parent'])){
                    $parent_prio = $this->db->query("SELECT prio FROM {shopping_cart}
                                        WHERE " . $this->row_id . "=?i AND id = ?i", array($this->id, $good['parent']), 'el');
                    if($parent_prio){
                        $prio = $parent_prio;
                        $this->db->query("UPDATE {shopping_cart}
                                    SET prio = prio+1 WHERE prio > ?i AND " . $this->row_id . "=?i", array($prio, $this->id));
                    }
                }
                if(!isset($prio)){
                    $prio = $this->db->query("SELECT max(prio) FROM {shopping_cart} WHERE " . $this->row_id . "=?i", array($this->id), 'el');
                }


                //проверяем наличие в базе такого товара
                $goodsid = $this->db->query('SELECT id, wait, warranties, qty_store as exist, foreign_warehouse, no_warranties
                                             FROM {goods} WHERE id=?i',
                                            array(intval($good['goods_id'])))->row();

                $is_wait = strtotime($goodsid['wait']) > 0;

                /*if ( !$model ) {
                    $model = new Model();
                }*/
                $exists_qty = $model->get_product_exists_qty($goodsid);

                if ( !$goodsid || ($exists_qty < 1 && $goodsid['foreign_warehouse'] != 1 && !$is_wait) ) { // если товара не существует удаляем
                    unset($goods[$good['goods_id']]);
                    $this->db->query('DELETE FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND goods_id=?i', array($this->id, $goodsid['id'])) ;
                    continue;
                }

                $prio ++;

                if ( isset($good['warranties']) && intval($good['warranties']) > 0  && array_key_exists(intval($good['warranties']), $this->configs['warranties']) && $goodsid['no_warranties'] == 0 ) {
                    $row = $this->db->query('SELECT count, id FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND goods_id=?i AND warranties=?i',
                        array($this->id, intval($good['goods_id']), intval($good['warranties'])))->row();
                    if ( $row ) {
                        //if ( $no == 0 ) {
                            $new_count = $row['count']+intval($good['count']);
                            if($new_count < 11 && $new_count <= $exists_qty){
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        /*}else{
                            $new_count = intval($good['count']);
                            if($new_count < 11 && $new_count <= $exists_qty){
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        }*/
                        $status = array('update', $row['id']);
                        continue;
                    } else {
                        $data = array($prio, $this->id, $good['goods_id'], intval($good['count']), intval($good['warranties']));
                    }
                } else if ( $goodsid['no_warranties'] == 0 ) {
                    $w = key($this->configs['warranties']);
                    if ( key(unserialize($goodsid['warranties'])) && key(unserialize($goodsid['warranties'])) > 0 )
                        $w = key(unserialize($goodsid['warranties']));

                    $row = $this->db->query('SELECT count,id FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND goods_id=?i AND warranties=?i',
                        array($this->id, intval($good['goods_id']), $w))->row();

                    if ( $row ) {
                        //if ( $no == 0 ) {
                            $new_count = $row['count']+intval($good['count']);
                            if($new_count < 11 && $new_count <= $exists_qty){
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        /*} else {
                            $new_count = intval($good['count']);
                            if($new_count < 11 && $new_count <= $exists_qty){
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        }*/
                        $status = array('update', $row['id']);
                        continue;
                    } else {
                        $data = array($prio, $this->id, $good['goods_id'], intval($good['count']), $w);
                    }
                } else if ( $goodsid['no_warranties'] == 1 ){

                    $row = $this->db->query('SELECT count,id FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND goods_id=?i AND warranties=?i',
                        array($this->id, intval($good['goods_id']), 0))->row();
                    if ( $row ) {
                        //if ( $no == 0 ) {
                            $new_count = $row['count']+intval($good['count']);
                            if ( $new_count < 11 && $new_count <= $exists_qty ) {
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        /*} else {
                            $new_count = intval($good['count']);
                            if ( $new_count < 11 && $new_count <= $exists_qty ) {
                                $this->db->query('UPDATE {shopping_cart} SET count=?i WHERE id=?i', array($new_count, $row['id']));
                            }
                        }*/
                        $status = array('update', $row['id']);
                        continue;
                    } else {
                        $data = array($prio, $this->id, $good['goods_id'], intval($good['count']), 0);
                    }
                }
                if ( $data[3] <= $exists_qty ) {
                    $new_id = $this->db->query('INSERT INTO {shopping_cart} (prio, ' . $this->row_id . ', goods_id, count, warranties) VALUES (?list)',
                        array($data), 'id');
                    $status = array('insert', $new_id);
                    $return[$good['goods_id']] = $status;
                } else {
                    continue;
                }
            }

            if ( $count_goods == 1 ) {
                $return = $status;
            }

            return $return;

        } else {
            return false;
        }

//        exit;
    }
/*
    function exist_order()
    {
        $order = $this->db->query('
            SELECT sc.*, os.date as order_date FROM {orders} as o

            RIGHT JOIN (
            SELECT * FROM {shopping_cart}
            )sc ON sc.order_id=o.id

            LEFT JOIN (
            SELECT order_id, status, date FROM {order_status}
            )os ON os.order_id=o.id AND os.status=1

            WHERE o.user_id=?i AND o.status=1',

            array($this->id))->assoc();

        if ($order)
            return $order;
        return false;

    }*/

    /*function delete_order()
    {
        $order_id = $this->db->query('SELECT id FROM {orders} WHERE ' . $this->row_id . '=?i AND status=1', array($this->id))->el();
        $this->db->query('DELETE FROM {orders} WHERE ' . $this->row_id . '=?i AND status=1', array($this->id));

        $this->db->query('DELETE FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND order_id=?i', array($this->id, $order_id));
        $this->db->query('DELETE FROM {order_status} WHERE order_id=?i ', array($order_id));
    }*/

    // меняем количество товара в корзине
    function change_qty($post)
    {
        if (isset($post['id']) && isset($post['qty'])) {
            $new_qty = (int)$post['qty'];
            if ($new_qty > 0 && $new_qty < 11) {
                $item = $this->db->query("SELECT id, goods_id, `count` FROM {shopping_cart} WHERE id=?i AND " . $this->row_id . "=?i", array(
                    $post['id'], $this->id
                ), 'row');
                //
                if ($item) {
                    $ar = $this->db->query("UPDATE {shopping_cart} SET count = ?i WHERE id = ?i", array($new_qty, $item['id']), 'ar');
                    if ($ar) {
                        $item['count'] = $new_qty;
                        return $item;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // меняем гарантию товару в корзине
    function change_warranty($post)
    {
        if (isset($post['w']) && isset($post['id'])) {
            $item = $this->db->query("SELECT id, goods_id, `count` FROM {shopping_cart} WHERE id=?i AND " . $this->row_id . "=?i", array(
                $post['id'], $this->id
            ), 'row');
            //
            if ($item) {
                if (isset($this->configs['warranties'][$post['w']])) {
                    $ar = $this->db->query("UPDATE {shopping_cart} SET warranties = ? WHERE id = ?i", array($post['w'], $item['id']), 'ar');
                    if ($ar) {
                        return $item;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function remove_product ($post)
    {
        $ar = false;

        if (isset($post['w'])) {
            $ar = $this->db->query('DELETE FROM {shopping_cart} WHERE id=?i AND ' . $this->row_id . '=?i AND warranties=?i',
                array(intval($post['id']), $this->id, intval($post['w'])))->ar();
        } else {
            $ar = $this->db->query('DELETE FROM {shopping_cart} WHERE id=?i AND ' . $this->row_id . '=?i',
                array(intval($post['id']), $this->id))->ar();
        }
        return $ar;
    }

    function update_order($data, $order = null)
    {
        $ar = null;
        $order_id = 0;
        $model = new Model();

        if ( isset($data['order_id']) && $data['order_id'] > 0 )
            $order_id = $data['order_id'];

        if ( $order_id == 0 ) return false;


        // обновляем товары
        if ( $order !== null ) {

            $query = '';
            $discount = null;
            // доступно ли редактирование
            if (array_key_exists($order['status'], $this->configs['order-status'])
                && $this->configs['order-status'][$order['status']]['edit'] == true) {
                // сносим все товары в заказе
                $this->db->query('DELETE FROM {orders_goods} WHERE order_id=?i', array($order['id']));

                if ( isset($data['goods_id']) && is_array($data['goods_id']) && count($data['goods_id']) > 0 ) {
                    $goods = array();
                    foreach ( $data['goods_id'] as $k=>$goods_id ) {
                        $count = (isset($data['count']) && isset($data['count'][$k]) && isset($data['count'][$k][$goods_id]) && $data['count'][$k][$goods_id] > 0)?$data['count'][$k][$goods_id]:0;
                        if ( $count == 0 )
                            continue;
                        $warranties = (isset($data['warranties']) && isset($data['warranties'][$k]) && isset($data['warranties'][$k][$goods_id]) && $data['warranties'][$k][$goods_id] > 0)?$data['warranties'][$k][$goods_id]:0;
                        // скидка
                        if (array_key_exists('discount', $data) && array_key_exists($k, $data['discount'])) {
                            $goods[] = array('goods_id'=>$goods_id, 'count'=>$count, 'warranties'=>$warranties, 'discount'=>$data['discount'][$k]);
                            $discount += ($data['discount'][$k] * $count);
                        } else {
                            $goods[] = array('goods_id'=>$goods_id, 'count'=>$count, 'warranties'=>$warranties);
                        }
                    }

                    $all_sum = $this->goods_for_order($goods, $model, $order_id, null, array('course_key'=>$data['course_key'], 'course_value'=>$data['course_value']), $order);

                    if ($order['user_id'] > 0)
                        $person = $this->db->query('SELECT person FROM {clients} WHERE id=?i', array($order['user_id']))->el();
                    else
                        $person = 1;


                    $array = array();
                    $array['region'] = (isset($data['region']) && array_key_exists($data['region'], $this->configs['cities']) &&
                        array_key_exists($data['region'], $this->configs['regions']))?$data['region']:$_SESSION['region'];
                    $array['city'] = (isset($data['city']) && array_key_exists($data['city'], $this->configs['cities'][$array['region']]))?$data['city']:$_SESSION['city'];
                    $array['email'] = $this->db->query('SELECT email FROM {clients} WHERE id=?i', array($this->id))->el();
                    $array['email'] = ($array['email'])?trim($array['email']):(isset($data['email'])?trim($data['email']):$order['email']);
                    $array['person'] = (isset($data['person']) && filter_var($data['person'], FILTER_VALIDATE_BOOLEAN) == true) ? 1 : 2;
                    $array['company_name'] = isset($data['company_name']) ? trim($data['company_name']) : null;
                    $array['legal_address'] = isset($data['legal_address']) ? trim($data['legal_address']) : null;
                    $array['inn'] = isset($data['inn']) ? trim($data['inn']) : null;
                    $array['kpp'] = isset($data['kpp']) ? trim($data['kpp']) : null;
                    $array['fio'] = (isset($data['fio']) && mb_strlen(trim($data['fio']), 'UTF-8') > 0) ? trim($data['fio']) : $order['fio'];
                    $array['payment'] = (isset($data['payment']) && mb_strlen(trim($data['payment']), 'UTF-8') > 0) ? trim($data['payment']) : $order['payment'];
                    $array['shipping'] = (isset($data['shipping']) && mb_strlen(trim($data['shipping']), 'UTF-8') > 0) ? trim($data['shipping']) : $order['shipping'];
                    $array['address'] = (isset($data['address']) && mb_strlen(trim($data['address']), 'UTF-8') > 0 && ($array['shipping'] == 'courier' || $array['shipping'] == 'express' || $array['shipping'] == 'courier_today')) ? trim($data['address']) : $order['address'];
                    $array['office'] = (isset($data['office']) && mb_strlen(trim($data['office']), 'UTF-8') > 0  && $array['shipping'] == 'pickup') ? trim($this->db->query('SELECT address FROM {offices} WHERE id=?i', array($data['office']))->el()) : $order['office'];
                    $array['np_office'] = (isset($data['np_office']) && mb_strlen(trim($data['np_office']), 'UTF-8') > 0 && ($array['shipping'] == 'novaposhta' || $array['shipping'] == 'novaposhta_cash')) ? trim($this->db->query('SELECT office FROM {nova_poschta} WHERE id=?i', array($data['np_office']))->el()) : $order['np_office'];
                    $array['comment'] = (isset($data['comment']) && mb_strlen(trim($data['comment']), 'UTF-8') > 0) ? trim($data['comment']) : $order['comment'];
                    $array['phone'] = (isset($data['phone']) && mb_strlen(trim($data['phone']), 'UTF-8') > 0) ? trim($data['phone']) : $order['phone'];

                    $array['course_key'] = $data['course_key'];
                    $array['course_value'] = $data['course_value'];
                    //$array['status'] = $data['status'];
                    //$array['status_id'] = $data['status_id'];

                    // цена доставки
                    $delivery = $this->delivery(
                        array(
                            'person'    =>   ($person == 2) ? 'corporation' : 'person',
                            'warehouse' =>   ($all_sum['foreign_warehouse'] == 1) ? 'supplier' : 'warehouse',
                            'payment'   =>   $array['payment'],
                            'shipping'  =>   $array['shipping'],
                            'city'      =>   $array['city'],
                        ), $model
                    );
                    $delivery_cost = $this->get_all_price($delivery, $all_sum['sum']);

                    $local_sum = $delivery_cost['all-sum'][$array['course_key']] * 100;
                    $d_sum = $delivery_cost['shipping-cost'][$array['course_key']] * 100;
                    $p_sum = $delivery_cost['payment-cost'][$array['course_key']] * 100;
//echo var_dump($discount);exit;
                    // скидка
                    if ($discount !== null) {
                        $query = $this->db->makeQuery('?query, discount=?i', array($query, $discount * 100));
                        $local_sum -= $discount * 100;
                    }
                    if ($array['person'] == 2) {
                        $query = $this->db->makeQuery('?query, company_name=?n, company_address=?n, company_inn=?n, company_kpp=?n',
                            array($query, $array['company_name'], $array['legal_address'], $array['inn'], $array['kpp']));
                    }
                    if ( $array['payment'] == 'installment' ) {
                        $inst = $this->installment_query($data, $model);
                        //`status_id`=?i, `status`=?i,
                        $ar = $this->db->query('UPDATE {orders} SET `payment`=?n, `shipping`=?n, `course_key`=?n,
                            `course_value`=?n, `person`=?n, `sum`=?, `delivery_cost`=?n, `payment_cost`=?n, ?q ?query WHERE id=?i',
                            array($array['payment'], $array['shipping'], $array['course_key'], $array['course_value'],
                                $array['person']/*, $array['status_id'], $array['status']*/, $local_sum, $d_sum, $p_sum,
                                $inst['query'], $query, $order_id), 'ar');
                    } else {//`status_id`=?i, `status`=?i,
                        $ar = $this->db->query('UPDATE {orders} SET `fio`=?n, `email`=?n, `region`=?i, `city`=?i, `payment`=?n,
                            `shipping`=?n, `address`=?n, `office`=?n, `np_office`=?n, `comment`=?n, `phone`=?n, `course_key`=?n,
                            `course_value`=?n, `person`=?n, `sum`=?, `delivery_cost`=?n, `payment_cost`=?n ?query WHERE id=?i',
                            array($array['fio'], $array['email'], $array['region'], $array['city'], $array['payment'],
                                $array['shipping'], $array['address'], $array['office'], $array['np_office'],
                                $array['comment'], $array['phone'], $array['course_key'], $array['course_value'],
                                $array['person'], /*$array['status_id'], $array['status'], */$local_sum,
                                $d_sum, $p_sum, $query, $order_id), 'ar');
                    }
                }
            }
            return $ar;

        } else {
            // обновление анкеты рассрочки с сайта
            $inst = $this->installment_query($data, $model);
            $this->db->query('UPDATE {orders} SET ?q WHERE id=?i', array($inst['query'], $order_id));

            // обновление данных клиента
            $this->update_user($inst['array']);
        }

        return true;
    }

    function update_user ($data)
    {
        if ( isset($_SESSION['user_id']) ) {

            $query_array = array();
            $array = array(
                'company_name' => null,
                'inn' => null,
                'kpp' => null,
                'fax' => null,
                //'phone' => null,{clients_phones}
                'ind' => null,
                'fio' => null,
                //'email' => null,
                //'pass' => null,
                //'person' => null, ?
                //'confirm' => null,
                //'address_id' => null,
                'legal_address' => null,
                'institution' => null,
                'birthday' => null,
                'job' => null,
                'works_phone' => null,
                'position' => null,
                'relationship' => null,
                'childrens' => null,
                'childrens_age' => null,
                'counts_people_apartment' => null,
                'education' => null,
                'mobile' => null,
                'identification_code' => null,
                'passport' => null,
                'issued_passport' => null,
                'when_passport_issued' => null,
                'registered_address' => null,
                'residential_address' => null,
                'payment' => null,
                'shipping' => null,
                'region' => null,
                'city' => null,
                'office_id' => null,
                'np_office_id' => null,
                'works_address' => null,
                'credits_package' => null,
            );

            foreach ($data as $k=>$v) {
                if ( array_key_exists($k, $array) && $v != null ) {
                    $query_array[$k] = trim($v);
                }
            }

            if ( isset($data['address']) && $data['address'] != null && isset($data['region']) && isset($data['city']) && $data['region'] != null && $data['city'] != null ) {
                //$query_array['address_id']
                $address = $this->db->query('SELECT id FROM {clients_delivery_addresses} WHERE user_id=?i AND content=? AND region=?i AND city=?i',
                    array($_SESSION['user_id'], trim($data['address']), $data['region'], $data['city']))->el();

                if (!$address) {
                    $this->db->query('INSERT INTO {clients_delivery_addresses} (user_id, content, region, city) VALUES (?i, ?, ?i, ?i)',
                        array($_SESSION['user_id'], trim($data['address']), $data['region'], $data['city']));
                }
            }

            //if ( $array['address'] != null )
            //    $user_update['address_id'] = $data['address'];
            //if ( $array['works_address'] != null )
            //    $array['works_address_id'] = $post['i_works_address'];

            $this->db->query('UPDATE {clients} SET ?set WHERE id=?i', array($query_array, $_SESSION['user_id']));
        }
    }

    function add_order($data, $goods = null, $redirect = false, $manager_flag = null, $mail = true, $from_guest = false)
    {
        $scg = array(); // все товары по заказам
        $parent = null; // для дополнительных заказов
        $discount = 0;

        // берем товары только с поста
        if (!$goods && isset($data['goods_id']) && is_array($data['goods_id']) && count($data['goods_id']) > 0) {
            $goods = $this->db->query('SELECT * FROM {shopping_cart} WHERE ' . $this->row_id . '=?i AND goods_id IN (?li)',
                array($this->id, array_values($data['goods_id'])))->assoc();
        }

        // новый заказ с админ.панели
        if (isset($data['goods_id']) && isset($data['clients']) && isset($data['count'])/* && isset($data['warranties'])*/) {
            $goods = array();
            foreach ($data['goods_id'] as $k => $goods_id) {
                $count = (isset($data['count']) && isset($data['count'][$k]) && isset($data['count'][$k][$goods_id]) && $data['count'][$k][$goods_id] > 0) ? $data['count'][$k][$goods_id] : 0;
                if ( $count == 0 )
                    continue;
                $warranties = (isset($data['warranties']) && isset($data['warranties'][$k]) && isset($data['warranties'][$k][$goods_id]) && $data['warranties'][$k][$goods_id] > 0)?$data['warranties'][$k][$goods_id]:0;

                if (array_key_exists('discount', $data) && array_key_exists($k, $data['discount'])) {
                    $goods[] = array('goods_id' => $goods_id, 'count' => $count, 'warranties' => $warranties, 'discount' => $data['discount'][$k]);
                    $discount += ($data['discount'][$k] * 100 * $count);
                } else {
                    $goods[] = array('goods_id' => $goods_id, 'count' => $count, 'warranties' => $warranties);
                }
            }
        }

        // берем товары с базы
        if (!$goods && (!$data || !isset($data['goods_id']))) {
            // принудительно из текущего гостя
            if ($from_guest && isset($_SESSION['guest_id'])) {
                $goods = $this->db->query('SELECT * FROM {shopping_cart} WHERE guest_id=?i',
                    array($_SESSION['guest_id']))->assoc();
            } else {
                $goods = $this->db->query('SELECT * FROM {shopping_cart} WHERE ' . $this->row_id . '=?i',
                    array($this->id))->assoc();
            }
        }

        // берем товары с $goods
        if ( !$goods )
            return array('status' => false, 'msg' => 'Нет ни одного товара');

        // распределяем товары по заказам
        foreach ( $goods as $product ) {
            $product_info = $this->db->query('SELECT qty_store as exist, foreign_warehouse FROM {goods} WHERE id=?i AND avail=1',
                array($product['goods_id']))->row();

            // на чужом складе
            if ( $product_info['foreign_warehouse'] == 1 )
                $scg[1][] = $product;

            // есть в наличии на нашем складе
            if ( $product_info['exist'] > 0 && $product_info['foreign_warehouse'] != 1 )
                $scg[0][] = $product;

            // товар в ожидании
            if ( $product_info['exist'] == 0 && $product_info['foreign_warehouse'] != 1 )
                $scg[2][] = $product;
        }
        ksort($scg, SORT_NUMERIC);
        $model = new Model();
        $course = $this->course();

        // если нет товаров в заказе
        if ( count($scg) < 1 )
            return array('status'=>false, 'msg'=>'Нет ни одного товара');

        $array = array();
        $array['region'] = (isset($_POST['region']) && array_key_exists($_POST['region'], $this->configs['cities']) &&
            array_key_exists($_POST['region'], $this->configs['regions'])) ? $_POST['region'] : array_key_exists('region', $_SESSION) ? $_SESSION['region'] : $this->region;
        $array['city'] = (isset($_POST['city']) && array_key_exists($_POST['city'], $this->configs['cities'][$array['region']])) ? $_POST['city'] :
            (array_key_exists('city', $_SESSION) && array_key_exists($_SESSION['city'], $this->configs['cities'][$array['region']]) ? $_SESSION['city'] :
                ($array['region'] == 12 ? $this->city : $this->d_city));
        $array['email'] = $this->db->query('SELECT email FROM {clients} WHERE id=?i', array($this->id))->el();
        $array['email'] = ($array['email'])?trim($array['email']):(isset($_POST['email'])?trim($_POST['email']):null);
        $array['person'] = (!isset($_POST['person']) || $_POST['person'] == 'true') ? 1 : 2;

        // данные для заказа если юр лицо
        $query = '';
        if ( $array['person'] == 2 ) {
            $query = $this->db->makeQuery(', company_name=?, company_address=?, company_inn=?, company_kpp=?', array(
                isset($_POST['company_name']) ? trim($_POST['company_name']) : null,
                isset($_POST['legal_address']) ? trim($_POST['legal_address']) : null,
                isset($_POST['inn']) ? trim($_POST['inn']) : null,
                isset($_POST['kpp']) ? trim($_POST['kpp']) : null,
            ));
        }

        $array['fio'] = (isset($data['fio']) && mb_strlen(trim($data['fio']), 'UTF-8') > 0) ? trim($data['fio']) : null;
        $array['phone'] = (isset($data['phone']) && mb_strlen(trim($data['phone']), 'UTF-8') > 0) ? trim($data['phone']) : null;
        if (($array['fio'] == null || $array['phone'] == null) && $this->row_id == 'user_id') {
            $client = $this->db->query('SELECT fio, phone FROM {clients} WHERE id=?i', array($this->id))->row();
            $array['fio'] = $array['fio'] == null && $client && mb_strlen(trim($client['fio']), 'UTF-8') > 0 ? $client['fio'] : $array['fio'];
            $array['phone'] = $array['phone'] == null && $client && mb_strlen(trim($client['phone']), 'UTF-8') > 0 ? $client['phone'] : $array['phone'];
        }
        $array['payment'] = (isset($data['payment']) && mb_strlen(trim($data['payment']), 'UTF-8') > 0) ? trim($data['payment']) : null;
        $array['shipping'] = (isset($data['shipping']) && mb_strlen(trim($data['shipping']), 'UTF-8') > 0) ? trim($data['shipping']) : null;
        $array['address'] = (isset($data['address']) && mb_strlen(trim($data['address']), 'UTF-8') > 0 && ($array['shipping'] == 'courier' || $array['shipping'] == 'express' || $array['shipping'] == 'courier_today')) ? trim($data['address']) : null;
        $array['office'] = (isset($data['office']) && mb_strlen(trim($data['office']), 'UTF-8') > 0  && $array['shipping'] == 'pickup') ? trim($this->db->query('SELECT address FROM {offices} WHERE id=?i', array($data['office']))->el()) : null;
        $array['np_office'] = (isset($data['np_office']) && mb_strlen(trim($data['np_office']), 'UTF-8') > 0 && ($array['shipping'] == 'novaposhta' || $array['shipping'] == 'novaposhta_cash')) ? trim($this->db->query('SELECT office FROM {nova_poschta} WHERE id=?i', array($data['np_office']))->el()) : null;
        $array['comment'] = (isset($data['comment']) && mb_strlen(trim($data['comment']), 'UTF-8') > 0) ? trim($data['comment']) : null;

        // ошибка
        if (empty($array['email']) && empty($array['phone'])) return array('status'=>false, 'msg'=>'Введите телефон или электронную почту');

        // обновляем инфу о заказе у клиента
        if ( $array['payment'] == 'installment') {
            $inst = $this->installment_query($data, $model);
            $user_update = $inst['array'] + $array;
        } else {
            $user_update = $array;
        }

        // ид отделения магазина для обновления информации клиента
        if ($array['office'] != null)
            $user_update['office_id'] = $data['office'];
        // ид отделения новой почты для обновления информации клиента
        if ($array['np_office'] != null)
            $user_update['np_office_id'] = $data['np_office'];

        $manager = null;
        if ($manager_flag) {
            $manager = $manager_flag;
        } else {
            // обновляем информацию клиента если не с админки
            $this->update_user($user_update);
        }

        // письмо на почту
        //$mailer = new Mailer($this->db, $this->prefix, $this->path, $this->configs);

        $mess_local_sum = null;
        $mess_d_sum = null;
        $mess_g_sum = null;
        $mess_mess = null;
        $mess_shipping = null;
        $mess_address = null;
        $mess_np_office = null;
        $mess_office = null;
        $mess_msg = null;
        $mess_goods = null;
        $mess_p_sum = null;

        foreach ( $scg as $oid=>$sp ) {
            // добавляем новый заказ
            if ( $array['payment'] == 'installment') {
                $order_id = $this->db->query('INSERT INTO {orders} SET ' . $this->row_id . '=?i, discount=?i, `payment`=?n, `shipping`=?n, `course_key`=?i,
                    `course_value`=?i, `parent`=?n, `person`=?n, `manager`=?n, ?q ?q',
                    array($this->id, $discount, $array['payment'], $array['shipping'], $course['course_key'], $course['course_value'],
                        $parent, $array['person'], $manager, $inst['query'], $query), 'id');
            } else {
                $order_id = $this->db->query('INSERT INTO {orders} SET ' . $this->row_id . '=?i, discount=?i, `fio`=?n, `email`=?n, `region`=?i, `city`=?i,
                        `payment`=?n, `shipping`=?n, `address`=?n, `office`=?n, `np_office`=?n, `comment`=?n, `phone`=?n, `course_key`=?n,
                        `course_value`=?n, `parent`=?n, `person`=?n, `manager`=?n ?q',
                    array($this->id, $discount, $array['fio'], $array['email'], $array['region'], $array['city'], $array['payment'], $array['shipping'],
                        $array['address'], $array['office'], $array['np_office'], $array['comment'], $array['phone'], $course['course_key'],
                        $course['course_value'], $parent, $array['person'], $manager, $query), 'id');
            }

            if ( !$order_id )
                return array('status'=>false, 'msg'=>'Произошла ошибка');

            // добавляем товары к заказу
            if ( !$parent )
                $all_sum = $this->goods_for_order ($sp, $model, $order_id, $mailer, $course, $manager_flag);
            else
                $all_sum = $this->goods_for_order ($sp, $model, $order_id, null, null, $manager_flag);

            // для следующих заказов чистим способ доставки, способ оплаты, цену доставки
            $payment = null;
            $shipping = null;

            // цена доставки
            $delivery = $this->delivery(
                array(
                    'person'    =>   ($array['person'] == 2) ? 'corporation' : 'person',
                    'warehouse' =>   ($all_sum['foreign_warehouse'] == 1) ? 'supplier' : 'warehouse',
                    'payment'   =>   $array['payment'],
                    'shipping'  =>   $array['shipping'],
                    'city'      =>   $array['city'],
                ), $model
            );
            $delivery_cost = $this->get_all_price($delivery, $all_sum['sum']);

            $local_sum = $delivery_cost['all-sum'][$course['course_key']] * 100 - intval($discount);
            $d_sum = $delivery_cost['shipping-cost'][$course['course_key']] * 100;
            $p_sum = $delivery_cost['payment-cost'][$course['course_key']] * 100;

            if (!$parent) {
                $parent = $order_id;
                $mess_local_sum = $local_sum;
                $mess_d_sum = $d_sum;
                $mess_g_sum = $local_sum - ($d_sum + $p_sum);
                $mess_msg = $delivery['msg'];
                $mess_shipping = $array['shipping'];
                $mess_address = $array['address'];
                $mess_np_office = $array['np_office'];
                $mess_office = $array['office'];
                $mess_goods = $all_sum['mess_goods_html'];
                $mess_p_sum = $p_sum;
            } /*else {
                //$sum = ($all_sum['sum'][$course['course_key']]*100/$course['course_value'])*100;
                $local_sum = $all_sum['sum'][$course['course_key']]*100;
                $d_sum = null;
                $p_sum = null;
            }*/

            // предзаказ
            $status = $this->configs['order-status-new'];
            if ( $oid == 2 )
                $status = $this->configs['order-status-preorder'];

            // статус заказа и сумма
            $order_status_id = $this->db->query('INSERT INTO {order_status} (`status`, `order_id`) VALUES (?i, ?i)', array($status, $order_id), 'id' );
            $this->db->query('UPDATE {orders} SET `status_id`=?i, `status`=?i, `sum`=?i, `delivery_cost`=?n, `payment_cost`=?n WHERE id=?i',
                    array($order_status_id, $status, $local_sum, $d_sum, $p_sum, $order_id));
        }

        // строим постоянную ссылку
        $order = $this->db->query('SELECT `id`, `date_add` FROM {orders} WHERE id=?i', array($parent))->row();
        $order_hash = $this->gen_order_hash($order);

        if ($mail == true) {
            $mailer->group('new-order', $array['email'], array(
                'order_id' => $parent,
                'order_hash' => $order_hash,
                'fio' => $array['fio'],
                'sum' => $model->show_price($mess_local_sum, null, null, true),
                'g_sum' => $model->show_price($mess_g_sum, null, null, true),
                'd_sum' => $model->show_price($mess_d_sum, null, null, true),
                'p_sum' => $model->show_price($mess_p_sum, null, null, true),
                'd_msg' => $mess_msg,
                'shipping' => $mess_shipping,
                'address' => $mess_address,
                'np_office' => $mess_np_office,
                'office' => $mess_office,
                'goods' => $mess_goods,
            ));
            $mailer->go();

            global $settings;
            //$mailer = new Mailer($this->db, $this->prefix, $this->path, $this->configs);
            //$mailer->group('Новый заказ', $settings['service_email'], array(), 'Новый заказ <a href="' . $this->configs['host'] . $this->prefix . 'manage/orders/create/' . $parent . '">Посмотреть</a>');
            //$mailer->go();
            $from_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'null';
            $url = 'http://' . $this->configs['host'] . $this->prefix . 'manage/orders/create/' . $parent;
            $admin_email_text = '<br><br> Посмотреть: <a href="' . $url . '">' . $url . '</a>';
            $admin_email_text .= '<br><br> Со страницы: ' . htmlspecialchars($from_page);
            send_mail($settings['email_service'], 'Новый заказ №' . $parent . ' на ' . ($mess_local_sum/100) . ' ' . l('грн') . '.', $admin_email_text);
        }

        // сообщение админам
        // тип сообщения
        if (!isset($data['mess-type']))
            $data['mess-type'] = 'mess-new-order';

        $content = 'Новый заказ <a href="' . $this->prefix . 'manage/orders/create/' . $parent . '">№';
        $content .= $parent . '</a>';
        if (count($scg) == 1 ) {
            $mailer->send_message($content, 'Новый заказ', $data['mess-type'], 1);
        } else {
            $mailer->send_message($content, 'Новый составной заказ', $data['mess-type'], 1);
        }

        // редирект на постоянную ссылку на заказ
        if ( $redirect ) {
            //if ( isset($_SESSION['user_id']) )
            //    header('Location: ' . $prefix . 'account/orders?order_id=' . $parent);
            //else
                header('Location: ' . $this->prefix . 'order?order_id=' . $parent . '&order_hash=' . $order_hash . '&thanks_message');
        }
        if (count($scg) > 1)
            return array('status' => true, 'msg'=>'Заказ успешно создан', 'id' => '', 'course_value' => $course['course_value']);

        if (count($scg) == 1)
            return array('status' => true, 'msg'=>'Заказ успешно создан', 'id' => $parent, 'course_value' => $course['course_value']);
    }

    function goods_for_order ($sp, $model, $order_id, $mailer = null, $course = null, $order = null)
    {
        $sum = array();
        $foreign_warehouse = 0;
        $mess_goods_html = '';
        if ( $sp > 0 ) {
            foreach ( $sp as $product ) {

                // достаем инфу товара
                $goods = $this->db->query('SELECT g.id as goods_id, g.article, g.date_add, i.image as attachment,
                            g.title, g.content, g.price, g.type, g.warranties, g.material, g.weight, g.size, g.secret_title, g.url,
                            g.code_1c, g.foreign_warehouse, m.user_id, g.qty_store
                        FROM {goods} as g
                        LEFT JOIN (SELECT `image`, `goods_id`, type FROM {goods_images} ORDER BY `prio`)i ON i.goods_id=g.id AND i.type=1
                        LEFT JOIN (SELECT `goods_id`, `user_id` FROM {users_goods_manager} GROUP BY goods_id)m ON m.goods_id=g.id
                        WHERE g.`id`=?i AND g.`avail`=1 GROUP BY i.goods_id', array($product['goods_id']))->row();

                if ( !$goods )
                    continue;

                // копируем изображение
                $path_parts = full_pathinfo($goods['attachment']);
                $img = '';
                $dir = $this->path . $this->configs['images-path-sc'] . $goods['goods_id'] . '/';
                if (array_key_exists('extension', $path_parts)) {
                    $img = $path_parts['filename'] . $this->configs['small-image'] . $path_parts['extension'];
                    if ( !is_dir($dir) ) {
                        if( mkdir($dir))  {
                            chmod( $dir, 0777 );
                        } else {
                            return false;
                        }
                    }
                    if ( file_exists($this->path . $this->configs['goods-images-path'] . $goods['goods_id'] . '/' . $img) )
                        copy($this->path . $this->configs['goods-images-path'] . $goods['goods_id'] . '/' . $img, $dir  . $img);
                }
                $count = (isset($product['count']))?$product['count']:1;
                $warranties = (isset($product['warranties']))?$product['warranties']:1;
                // цена гарантии товара
                $warranty_price = $this->warranties_cost($goods['price'], $warranties, $goods['goods_id']);

                // считаем сумму заказа
                if ( isset($product['parent'])) {
                    $cur_sum = $this->item_price($model, $goods['price'], 0, $product['count'], null, false, isset($course['course_key'])?$course['course_key']:null, isset($course['course_value'])?$course['course_value']:null);
                } else {
                    $cur_sum = $this->item_price($model, $goods['price'], $warranty_price, $product['count'], null, false, isset($course['course_key'])?$course['course_key']:null, isset($course['course_value'])?$course['course_value']:null);
                }

                $product_price = $model->get_prices($goods['price'], false, isset($course['course_key']) ? $course['course_key']:null, isset($course['course_value']) ? $course['course_value']:null, true);
                $warranty_price = $model->get_prices($warranty_price, false, isset($course['course_key']) ? $course['course_key']:null, isset($course['course_value']) ? $course['course_value']:null, true);

                // скидка
                if (isset($product['discount'])) {
                    // добавляем товар в заказ
                    $this->db->query('INSERT INTO {orders_goods} (`' . $this->row_id . '`, `goods_id`, `article`, `attachment`,
                                `title`, `content`, `price`, `type`, `count`, `warranties`, `warranties_cost`, `order_id`, `material`, `weight`,
                                `size`, `secret_title`, `url`, `code_1c`, `foreign_warehouse`, `manager_id`, discount)
                            VALUES (?n, ?i, ?, ?, ?, ?, ?, ?, ?i, ?i, ?, ?i, ?, ?, ?, ?, ?, ?, ?, ?n, ?i)',
                        array($this->id, $goods['goods_id'], $goods['article'], $img, $goods['title'], $goods['content'],
                            (($product_price * 100)-($product['discount'] * 100)), $goods['type'], $count, $warranties, $warranty_price * 100,
                            $order_id, $goods['material'], $goods['weight'], $goods['size'], $goods['secret_title'], $goods['url'],
                            $goods['code_1c'], $goods['foreign_warehouse'], $goods['user_id'], ($product['discount'] * 100)));
                } else {
                    // добавляем товар в заказ
                    $this->db->query('INSERT INTO {orders_goods} (`' . $this->row_id . '`, `goods_id`, `article`, `attachment`,
                            `title`, `content`, `price`, `type`, `count`, `warranties`, `warranties_cost`, `order_id`, `material`, `weight`,
                            `size`, `secret_title`, `url`, `code_1c`, `foreign_warehouse`, `manager_id`)
                        VALUES (?n, ?i, ?, ?, ?, ?, ?, ?, ?i, ?i, ?, ?i, ?, ?, ?, ?, ?, ?, ?, ?n)',
                        array($this->id, $goods['goods_id'], $goods['article'], $img, $goods['title'], $goods['content'],
                            $product_price * 100, $goods['type'], $count, $warranties, $warranty_price * 100,
                            $order_id, $goods['material'], $goods['weight'], $goods['size'], $goods['secret_title'], $goods['url'],
                            $goods['code_1c'], $goods['foreign_warehouse'], $goods['user_id']));
                }

                if (!$order) {
                    require_once('userhistory.class.php');
                    // в историю его
                    $users_history = new Users_History($this->cfg, $this->db, $this->configs, array('goods_id'=>intval($goods['goods_id']), 'order'=>1));
                    $users_history->insert_shopping_cart();

                    // удаляем с корзины
                    $this->db->query('DELETE FROM {shopping_cart} WHERE `' . $this->row_id . '`=?i AND `goods_id`=?i',
                        array($this->id, $goods['goods_id']));
                }

                // для письма
                if ($mailer != null) {
                    $img_hash = md5($img);
                    $mailer->AddEmbeddedImage($dir . $img, $img_hash);
                    $mess_goods_html .= "<tr>
                                            <td width='25%' valign='middle' style='padding: 10px;'><img src='cid:{$img_hash}' title=\"{$goods['title']}\" alt=\"{$goods['title']}\" /></td>
                                            <td width='40%' valtrign='middle' style='padding: 10px;'><a href='http://{$_SERVER['HTTP_HOST']}{$this->prefix}{$goods['url']}/{$this->configs['product-page']}/{$goods['goods_id']}'>{$goods['title']}</a></td>
                                            <td width='15%' valign='middle' style='padding: 10px;'>{$count} ' . l('шт.') . '</td>
                                            <td width='20%' valign='middle' style='padding: 10px;'>{$cur_sum[$course['course_key']]} грн.</td>
                                        </tr>";
                }

                //$sum += (array_key_exists($course['course_key'], $cur_sum))?$cur_sum[$course['course_key']]:0;
                foreach ( $cur_sum as $k=>$v ) {
                    if (array_key_exists($k, $sum))
                        $sum[$k] += $v;
                    else
                        $sum[$k] = $v;
                }

                if ($goods['foreign_warehouse'] == 1 && $goods['qty_store'] == 0)
                    $foreign_warehouse = 1;
            }
        }
        return array(
            'sum' => $sum, /*'cur_sum'=>$cur_sum,*/
            'foreign_warehouse' => $foreign_warehouse,
            'mess_goods_html' => $mess_goods_html
        );
    }

    function course ()
    {
        global $settings;

        if( !$settings || !isset($settings[$this->configs['default-course']]) )
            $settings[$this->configs['default-course']] = $this->db->query('SELECT value FROM {settings} WHERE name=?', array($this->configs['default-course']))->el();

        $course['course_value'] = $settings[$this->configs['default-course']];
        $course['course_key'] = $this->configs['default-course'];

        if (array_key_exists('tbl', $this->cfg) && isset($_COOKIE[$this->cfg['tbl'] . $this->configs['course']])) {

            if ( array_key_exists($_COOKIE[$this->cfg['tbl'] . $this->configs['course']], $settings)) {
                $course['course_value'] = $settings[$_COOKIE[$this->cfg['tbl'] . $this->configs['course']]];
                $course['course_key'] = $_COOKIE[$this->cfg['tbl'] . $this->configs['course']];
            }
        }

        return $course;

    }

    function gen_order_hash($order) {
        return md5($order['id'].$order['date_add'].'#seCurE_daTa');
    }

    function gen_order( $order_id, $user_id = null, $order_hash = null, $footer = false, $together = false) {
        global /*$prefix, */$arrequest;

        $user_query = '';
        if($user_id){
            $user_query = $this->db->makeQuery('o.user_id=?i AND', array($user_id));
        }

        // достаем описание заказа
        $order = $this->db->query('SELECT o.date_add, o.id, o.payment, o.shipping, o.office, o.sum, o.comment,
                o.note, o.address, o.status, o.parent, o.course_value, o.course_key, o.np_office, o.delivery_cost,
                o.payment_cost, (SELECT SUM(price+warranties_cost) FROM {orders_goods} WHERE order_id=o.id) as gsum
              FROM {orders} as o WHERE ?q o.id=?i ',
            array($user_query, $order_id), 'row');

        $orders_html = '';$event_for_installment = '';

        if ( $order ) {

            $hash = $this->gen_order_hash($order);

            if ( !is_null($order_hash) && $order_hash != $hash ) {

                return '<h3>Такой заказ не существует</h3>';

            } else {
                // обработчик формы в рассрочку
                if ( $order['payment'] == 'installment' && $order['status'] == $this->configs['order-status-new'] ) {
                    //$cart = new Cart($this->db);
                    $orders_html .= '
                        <div class="buy-installment sm_content">
                            <div class="top">
                                <div class="sm_close"></div>
                                <h2>Купить в рассрочку <span style="color:#DEA84E; font-size:14px; margin-left: 30px;">' . l('Услуга временно не доступна') . '</span></h2>
                            </div>
                            <div class="bottom" id="display-installment-form">'
                                //. $this->installment_form($order['id'])
                            . '</div>
                        </div>';

                    $event_for_installment = "

                        <script type=\"text/javascript\" src=\"{$this->prefix}extra/jquery.ui-1.10.2.js\"></script>
                        <script type=\"text/javascript\" src=\"{$this->prefix}extra/jquery.ui.datepicker-ru.js\"></script>

                        <script type=\"application/javascript\">

                            function update_order_installment (_this) {

                                jQuery('#installment_form').parsley( 'validate' );

                                if ( jQuery( '#installment_form' ).parsley('isValid') == true ) {
                                    server_request();

                                    var form = jQuery(_this).parents('.installment-form').serialize();

                                    jQuery.ajax({
                                        type: 'POST',
                                        url: prefix+'ajax.php',
                                        data: 'order-installment=1&' + form + '" . (isset($_GET['order_hash'])?'&order_hash=' . $_GET['order_hash']:'') . "',
                                        cache: false,
                                        success: function(msg){
                                            if ( msg['error'] ) {
                                                show_alert(msg['message'], 1);
                                            } else {
                                                show_alert(msg['message'] );
                                            }
                                        },
                                        error: function (xhr, ajaxOptions, thrownError) {
                                            show_alert(xhr.responseText, 1);
                                        }
                                    });
                                    return false;
                                }
                            }

                            function before_show_installment(order_id, _this) {
                                server_request();

                                jQuery('#for-installment-order_id').val(order_id);
                                var form = jQuery(_this).parents('.installment-form');

                                jQuery.ajax({
                                    type: 'POST',
                                    url: prefix+'ajax.php',
                                    data: 'get-order-info-installment=1&order_id=' + order_id + '" . (isset($_GET['order_hash'])?'&order_hash=' . $_GET['order_hash']:'') . "',
                                    cache: false,
                                    success: function(msg){
                                        if ( msg['error'] ) {
                                            show_alert(msg['message'], 1);
                                        } else {
                                            show_alert();
                                            jQuery('#display-installment-form').html(msg['form']);
                                            date_picker();
                                        }
                                    },
                                    error: function (xhr, ajaxOptions, thrownError) {
                                            show_alert(xhr.responseText, 1);
                                        }
                                });
                                return false;

                            }

                            function date_picker () {
                                var datepicker = jQuery( '.datepicker' );

                                if(datepicker.length) {
                                    datepicker.datepicker({
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: '1900:'
                                    });
                                }
                            }
                    </script>";
                }
                // показать окно "Спасибо" если статус заказа новый
                if ( $order['status'] == $this->configs['order-status-new'] && isset($_GET['thanks_message'])) {
                    $orders_html .=
                        '<div class="on_body_load sm1_content thanks_message" id="thanks_message">
                             <div class="top">
                                 <div class="sm1_close"></div>
                                 <h2>Спасибо, ваш заказ принят</h2>
                             </div>
                             <div class="bottom" style="max-height: 836px;">
                                 В скором времени с вами свяжется наш менеджер<br />
                                 для подтверждения заказа, уточнения времени<br />
                                 и места его получения.
                             </div>
                         </div>' .
                        '<script>
                            jQuery(function(){
                                jQuery("#thanks_message").show().center().css("z-index", "9998");';
                    // показывать анкету рассрочки если статус новый заказ
                    if ( $order['payment'] == 'installment' ) {
                        $orders_html .= '
                                before_show_installment(' . $order['id'] . ', this);
                                jQuery(".buy-installment").show();
                                jQuery("#blackout").show();';
                    }
                    $orders_html .= '
                            });
                        </script>';
                }
                // выводим заказ
                $orders_html .= $this->show_order ($order, $footer, $arrequest);

                if ( $together == true ) {
                    $orders_parent = $this->db->query('SELECT o.date_add, o.id, o.payment, o.shipping, o.office, o.status,
                        o.sum, o.address, o.comment, o.note, o.course_value, o.course_key, o.np_office, o.delivery_cost,
                        o.payment_cost, (SELECT SUM(price+warranties_cost) FROM {orders_goods} WHERE order_id=o.id) as gsum
                      FROM {orders} as o
                      WHERE ?q o.parent=?i', array($user_query, $order['id']))->assoc();

                    if ( $orders_parent ) {
                        foreach ( $orders_parent as $order_parent ) {
                            $orders_html .= '<br /><br /><br /><br />' . $this->show_order ($order_parent, $footer/*, $this->prefix*/, $arrequest);
                        }
                    }
                }

            }
        } else {
            $orders_html .= '<h3>' . l('Такой заказ не существует') . '</h3>';
        }

        return $orders_html . $event_for_installment;
    }

    function show_order ($order, $footer, $arrequest)
    {
        $model = new Model();
        $orders_html = '';

        $orders_html .= '<h2 class="account_title">Заказ №'.$order['id'].' <span>от '.date('d.m.Y H:i:s', strtotime($order['date_add'])).'</span></h2>';

        $orders_html .= '<h3>' . l('Данные заказа') . '</h3>';
        $orders_html .= '<table class="account_table">';

        //if (array_key_exists($order['status'],  $this->configs['order-status']))
        //    $orders_html .= '<tr><td class="td_name">'.l('Статус').'</td><td>' . $this->configs['order-status'][$order['status']]['name'] . '</td></tr>';

        $orders_html .= '<tr><td class="td_name">' . l('Стоимость товара') . '</td><td>' . $model->show_price($order['gsum'], null, null, true) . '</td></tr>';
        $orders_html .= '<tr><td class="td_name">' . l('Стоимость доставки') . '</td><td>' . $model->show_price($order['delivery_cost'], null, null, true) . '</td></tr>';
        $orders_html .= '<tr><td class="td_name">' . l('Комиссия банка') . '</td><td>' . $model->show_price($order['payment_cost'], null, null, true) . '</td></tr>';
        $orders_html .= '<tr><td class="td_name">' . l('Итого') . '</td><td>' . $model->show_price($order['sum'], null, null, true) . '</td></tr>';

        if ( isset($this->configs['payment-msg'][$order['payment']]) ) {
            $orders_html .=
                '<tr>' .
                '<td class="td_name">Способ оплаты</td>' .
                '<td>' . $this->configs['payment-msg'][$order['payment']]['name'];

            if ( $order['payment'] == 'installment' && ($order['status'] == $this->configs['order-status-new']/* || $order['status'] == $this->configs['order-status-preorder']*/) ) {
                $orders_html .= ', <a id="edit_installment" class="on_load_popup" onclick="before_show_installment(' . $order['id'] . ', this)" data-content="buy-installment" data-id="' . $order['id'] . '">редактировать анкету</a>';
            }
            // кнопка оплатить если вебмани и статус заказа новый
            if ($order['payment'] == 'webmoney' && $order['status'] == $this->configs['order-status-new']) {
                $orders_html .=
                    ' <input type="button" class="green_btn" value="Оплатить">';
            }
            $orders_html .=
                '</td></tr>';

            // образец счета
            if ( $order['payment'] == 'account' && $order['status'] == $this->configs['order-status-new'] ) {
                $orders_html .=
                    '<tr>' .
                    '<td class="td_name">Распечатать счет</td>' .
                    '<td><a href="' . $this->prefix . 'print.php?order_id=' . $order['id'] . '&order_hash=' . $this->gen_order_hash($order) . '" target="_blank">Распечатать</a>. Перед оплатой обязательно согласуйте с менеджером наличие товара и сроки поставки.</td></tr>';
            }
        }


        if (isset($this->configs['shipping-msg'][$order['shipping']]))
            $orders_html .= '<tr><td class="td_name">Способ доставки</td><td>' . $this->configs['shipping-msg'][$order['shipping']]['name'] . '</td></tr>';

        if ($order['address'])
            $orders_html .= '<tr><td class="td_name">Адрес</td><td>' . htmlspecialchars($order['address']) . '</td></tr>';

        if ($order['office'])
            $orders_html .= '<tr><td class="td_name">Отделение магазина</td><td>' . htmlspecialchars($order['office']) . '</td></tr>';

        if ($order['np_office'])
            $orders_html .= '<tr><td class="td_name">Отделение новой почты</td><td>' . htmlspecialchars($order['np_office']) . '</td></tr>';

        if ($order['comment'])
            $orders_html .= '<tr><td class="td_name">Ваш комментарий</td><td>' . htmlspecialchars($order['comment']) . '</td></tr>';

        if($order['note'])
            $orders_html .= '<tr><td class="td_name">Комментарий менеджера</td><td>' . htmlspecialchars($order['note']) . '</td></tr>';

        $orders_html .= '</table>';


        // достаем все товары в заказе
        $goods = $this->db->query('SELECT og.attachment, og.title, og.price, og.url, og.goods_id, og.`count`,
              og.warranties, og.warranties_cost, og.id, og.foreign_warehouse, g.no_warranties
            FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND g.id=og.goods_id', array($order['id']))->assoc();

        $orders_html .= '<h3>' . l('Товары') . '</h3>';
        if ( $goods && count($goods) > 0 ) {

            // выводим товары в заказе
            $orders_html .= $this->show_goods_in_order($goods, $order);

            // генерим футер - табы
            if ( $footer ) {
                // достаем историю заказа
                $orders_html_history = '';
                $history = $this->db->query('SELECT status, date FROM {order_status} WHERE order_id=?i ORDER BY date DESC', array($order['id']))->assoc();
                $orders_html_history .= '<table class="orders_table order_items_table width_70"><thead><tr><td>Статус</td><td class="otr_border">'.l('Дата').'</td></tr></thead><tbody>';
                foreach ( $history as $h ) {
                    if ( array_key_exists($h['status'], $this->configs['order-status']) ) {
                        $orders_html_history .= '<tr>
                                <td>' . $this->configs['order-status'][$h['status']]['name'] . '</td>
                                <td>' . date('d.m.y H:i', strtotime($h['date'])) . '</td></tr>';
                    }
                }
                $orders_html_history .= '</tbody></table>';

                // обработка сообщений
                $problem_msg = '';
                $return_msg = '';
                if ( isset($_POST['submit']) && isset($_GET['order_id']) && $_GET['order_id'] > 0 ) {
                    if ( isset($_GET['act']) && $_GET['act'] == 'problem' ) {
                        if ( !isset($_POST['content']) || mb_strlen(trim($_POST['content'])) < 1 ) {
                            $problem_msg = '<div class="message_error">Комментарий не может быть пустым</div>';
                        } else {
                            $content = 'Заказ <a href="' . $this->prefix . 'manage/orders/create/' . $_GET['order_id'] . '">№';
                            $content .= $_GET['order_id'] . '</a>. ' . htmlspecialchars(trim($_POST['content']));
                            //$messages = new Mailer($this->db, $this->prefix, $this->path, $this->configs);
                            $messages->send_message($content, 'Проблема с заказом', 'mess-new-order', 1);
                            $problem_msg = '<div class="message_info">Ваш комментарий успешно добавлен.</div>';
                        }
                    }
                    if ( isset($_GET['act']) && $_GET['act'] == 'return' ) {
                        if ( !isset($_POST['content']) || mb_strlen(trim($_POST['content'])) < 1 ) {
                            $return_msg = '<div class="message_error">Комментарий не может быть пустым</div>';
                        } else {
                            $content = 'Заказ <a href="' . $this->prefix . 'manage/orders/create/' . $_GET['order_id'] . '">№';
                            $content .= $_GET['order_id'] . '</a>. ' . htmlspecialchars(trim($_POST['content']));
                            //$messages = new Mailer($this->db, $this->prefix, $this->path, $this->configs);
                            $messages->send_message($content, 'Вернуть товар', 'mess-new-order', 1);
                            $return_msg = '<div class="message_info">Ваш комментарий успешно добавлен.</div>';
                        }
                    }
                }

                $orders_html .=
                    '<ul class="nav nav-tabs order_tabs">'.
                        '<li class="active"><a href="#order-status">Отследить статус заказа</a></li>'.
                        '<li><a href="#order-problem">Проблема с заказом</a></li>'.
                        //'<li><a href="#leave-message">Оставить отзыв</a></li>'.
                        '<li><a href="#return">Вернуть товар</a></li>'.
                    '</ul>'.
                    '<div class="tab-content order_tab_content">'.
                        '<div class="tab-pane active" id="order-status">'.
                            $orders_html_history .
                        '</div>'.
                        '<div class="tab-pane" id="order-problem">' .
                            $problem_msg .
                                '<form data-validate="parsley" action="'.$this->prefix.implode('/', $arrequest).'?order_id='.(int)$order['id'].'&act=problem#order-problem" method="post">'.
                                '<br>Комментарий<br><br>'.
                                '<textarea class="textarea" data-required="true" data-trigger="keyup" cols="60" name="content" rows="5"></textarea><br><br>'.
                                //'<input type="checkbox"> Уведомлять меня об ответах по почте<br><br>'.
                                '<input type="submit" value="Отправить" name="submit" class="green_btn">'.
                            '</form>'.
                        '</div>'.
                        //'<div class="tab-pane" id="leave-message">'.
                        //'</div>'.
                        '<div class="tab-pane" id="return">' .
                            $return_msg .
                            '<form data-validate="parsley" action="'.$this->prefix.implode('/', $arrequest).'?order_id='.(int)$order['id'].'&act=return#return" method="post">'.
                                '<br>Комментарий<br><br>' .
                                '<textarea class="textarea" data-required="true" data-trigger="keyup" cols="60" name="content" rows="5"></textarea><br><br>' .
                                //'<input type="checkbox"> Уведомлять меня об ответах по почте<br><br>' .
                                '<input type="submit" value="Отправить" name="submit" class="green_btn">' .
                            '</form>' .
                        '</div>' .
                    '</div>'
                ;
            }
        } else {
            $orders_html .= '<h3>У этого заказа нет товаров</h3>';
        }

        return $orders_html;
    }

    function show_goods_in_order($goods, $order, $admin_panel = '', $edit = true, $oRole = null)
    {
        $orders_html = '
            <table class="orders_table order_items_table ' . (($admin_panel > 0) ? 'table table-striped' : '') . '">' .
                '<thead>' .
                    '<tr>' .
                        '<td>' . l('Наименование') . '</td>' .
                        '<td>Цена товара</td>' .
                        (( $admin_panel > 0) ?
                            ($oRole && $oRole->hasPrivilege('client-order-discount') ? '<td class="td-sc-discount">Скидка</td>' : '')
                            : '' ) .
                        '<td>Количество</td>' .
                        '<td>' . l('Стоимость') . '</td>' .
                        (( $admin_panel > 0) ?
                            ($edit == true ? '<td>' . l('Удалить') . '</td>' : '')
                            : '' ) .
                    '</tr>'.
                '</thead>'.
            '<tbody ' . (($admin_panel > 0) ? 'id="goods-table"' : '') . '>';

        foreach ( $goods as $good ) {
            $orders_html .= $this->show_product_in_order($good, $order, $admin_panel, $edit, $oRole);
            if ( $admin_panel > 0 ) $admin_panel += 1;
        }
        $orders_html .= '</tbody></table>';

        return $orders_html;
    }

    function show_product_in_order($good, $order, $admin_panel, $edit = true, $oRole = null)
    {
        //global $path;
        $model = new Model();
        $hw = '';

        if ($admin_panel > 0 && $edit == true) {
            $count = '<select class="help-select" onchange="checkout_change()" name="count[' . $admin_panel . '][' . $good['goods_id'] . ']">';
            for ( $i=1; $i<=999; $i++ ) {
                if ( $i == $good['count'] )
                    $count .= '<option selected value="' . $i . '">' . $i . '</option>';
                else
                    $count .= '<option value="' . $i . '">' . $i . '</option>';
            }
            $count .= '</select>';
            //строи блок гарантии
            //if ( $good['warranties'] > 0 ) {
                $warr = $model->show_warranties($good, $this, $good['warranties'], false, false, true );
                if ( empty($warr['html']) )
                    $warranty_count = '<input type="hidden" name="warranties[' . $admin_panel . '][' . $good['goods_id'] . $admin_panel . ']">' . 0 . ' '. l('мес') . '';
                else
                    $warranty_count = '<select onchange="checkout_change()" class="help-select" name="warranties[' . $admin_panel . '][' . $good['goods_id'] . ']">' . $warr['html'] . '</select>';
                //<option value="">0</option>
            //} else {
            //    $warranty_count = '<input type="hidden" name="warranties[' . $good['goods_id'] . ']">' . $good['warranties'] . ' '. l('мес') . '';
            //}
            /*$price = $model->show_price($good['price'], $order['course_key']);
            //$cost = $model->show_price($good['price'], $order['course_key'], false, $good['count']);
            $warranties_cost = $model->show_price($good['warranties_cost'], $order['course_key']);*/
        } else {
            if ( !$order ) {
                $path_parts = full_pathinfo($good['attachment']);
                $img = $this->configs['goods-images-path'] . $good['goods_id'] . '/' . htmlspecialchars($path_parts['filename']) .
                    $this->configs['small-image'] . $path_parts['extension'];
                //$warranties_cost = $model->cur_currency($this->warranties_cost($good['price'], $good['warranties'], $good['goods_id']));
                //$price = $model->cur_currency($good['price']);
            } else {
                $img = $this->configs['images-path-sc'] . $good['goods_id'] . '/' . $good['attachment'];
                //$price = $model->show_price($good['price'], $order['course_key']);
                //$warranties_cost = $model->show_price($good['warranties_cost'], $order['course_key']);
            }
            if ( !file_exists($this->path . $img) ) {
                $img = 'images/iconka_50x50.png';
            } else {
                list($width, $height, $type, $attr) = getimagesize($this->path . $img);
                if ($width < $height)
                    $hw = 'height="50"';
                else
                    $hw = 'width="50"';
            }
            $warranty_count = $good['count'];
            $count = $good['count'];
        }
        if ($admin_panel > 0) {
            $count .= '<b> = </b>';
        }
        $price = $model->show_price($good['price'], null, null, true);
        $warranty = $good['warranties'];
        $cost = $model->show_price($good['price'], null, null, true, $good['count']);
        $warranty_cost = $model->show_price($good['warranties_cost'], null, null, true);
        $warranties_cost = $model->show_price($good['warranties_cost'], null, null, true, $good['count']);

        $warranties_html = '';
        if ($good['warranties'] > 0 && $this->configs['no-warranties'] == false/* || $admin_panel > 0 */) {
            $warranties_html = '
            <td class="order_table_war">' .
                (( $admin_panel == 0 ) ?
                    '<div class="warranty_ico">
                        <span>' . $warranty . '</span>
                        </div>'
                    : '' ) .
                (($good['no_warranties'] != 1 || $good['warranties'] > 0) ?
                    'Гарантийный пакет <span id="war-months-' . $good['goods_id'] . $admin_panel . '">' . $warranty . '</span> '. l('мес') . ' для ' . htmlspecialchars($good['title']) . '</td>'
                    : '' ) .
                '<td><label id="warranty-cost-' . $good['goods_id'] . $admin_panel . '">' . $warranty_cost . '</label></td>' .
                ($oRole && $oRole->hasPrivilege('client-order-discount') ? '<td class="td-sc-discount"></td>' : '') .
                '<td>' . $warranty_count . '</td>
                <td><label id="warranties-cost-' . $good['goods_id'] . $admin_panel . '">' . $warranties_cost . '</label></td>' .
                (($admin_panel > 0) ?
                    ($edit == true ? '<td></td>' : '')
                    : '' ) . '';
        }

        $good['discount'] = isset($good['discount']) ? $good['discount'] : 0;

        return
            '<tr><td class="order_table_name">
                    <a href="' . $this->prefix . urlencode($good['url']) . '/' . $this->configs['product-page'] . '/' . $good['goods_id'] . '">' .
                    ( ( $admin_panel == 0 ) ?
                        '<img ' . $hw . ' alt="' . htmlspecialchars($good['title']) . '" src ="' . $this->prefix . htmlspecialchars($img) . '" /><div>'
                    : '' ) . htmlspecialchars($good['title']) . '</div></a>' .
                 (isset($good['free_balance']) && isset($good['count_orders'])
                     ? ' <span title="Cвободный остаток/Актуальные заказы"><span>' . intval($good['free_balance']) . '</span>/<span class="class="' . ($good['count_orders'] > $good['free_balance'] ? 'text-error' : '') . '"">' . intval($good['count_orders']) : '</span>') .
                '</td>
                <td><label id="product-cost-' . $good['goods_id'] . $admin_panel . '">' . $price . '</label></td>' .
            ($oRole && $oRole->hasPrivilege('external-marketing') ?
                '<td class="td-sc-discount">' .
                ($edit == true && $oRole && $oRole->hasPrivilege('client-order-discount') ?
                    '<input onchange="checkout_change()" type="input" class="input-small" id="product-discount-' . $good['goods_id'] . $admin_panel . '" value="' . ($good['discount']/100) . '" name="discount[' . $admin_panel . ']" /> ' . l('грн') . '.'
                    : $model->show_price($good['discount'], null, null, true)) . '<b> X </b></td>' : '') .
                '<td>' . $count . '</td>
                <td><label id="products-cost-' . $good['goods_id'] . $admin_panel . '">' . $cost . '</label></td>' .
                (($admin_panel > 0 && $edit == true) ?
                    '<td><i class="icon-remove remove-product" onclick="remove_product(this)"></i></td>' : '' ) .
                '<input type="hidden" name="goods_id[' . $admin_panel . ']" value="' . $good['goods_id'] . '" />
            </tr><tr>' . $warranties_html . '</tr>';
    }

    function warranties_cost ($price, $month, $goods_id)
    {
        $cost = 0;
        $warr = $this->db->query('SELECT warranties, no_warranties FROM {goods} WHERE id=?i', array($goods_id))->row();

        if ($warr && $warr['no_warranties'] == 1) return 0;

        $w = (array)@unserialize($warr['warranties']);

        if (!$warr || count($w) == 0) {
            $warranties = $this->configs['warranties'];
        } else {
            $warranties = array();
            foreach ($this->configs['warranties'] as $m=>$v) {
                if (array_key_exists($m, $w)) {
                    if (count($warranties) > 0) {
                        $warranties[$m] = $v;
                    } else {
                        foreach ($v as $wk=>$wv) {
                            $warranties[$m][$wk] = 0;
                        }
                    }
                }
            }
        }

        if (!is_array($month) && array_key_exists($month, $warranties)) {
            foreach ($warranties[$month] as $to=>$p) {
                if ($to > $price || $to == 'inf')
                    return $p;
            }
        }

        return $cost;
    }

    function already_in_shopping_cart ($goods_id, $warranty = null)
    {
        $w = '';
        if(!is_null($warranty)){
            $w = $this->db->makeQuery(" AND warranties = ?i ", array($warranty));
        }
        $product = $this->db->query('SELECT id FROM {shopping_cart} WHERE goods_id=?i AND ' . $this->row_id . '=?i'.$w,
            array($goods_id, intval($this->id)))->el();

        if ( $product )
            return true;

        return false;
    }


    function exist_cart($goods_id = null, $order = null)
    {
        $goods = array();

        $goods_id_query = '';
        if ($goods_id) {
            $goods_id_query = $this->db->makeQuery(" AND sc.id = ?i ", array($goods_id));
        }

        // берем товары с готового заказ
        if ( $order && isset($order['id']) ) {
            $goods = $this->db->query('SELECT og.attachment, og.title, og.price/(o.course_value/100) as price, og.url, og.goods_id,
                  og.`count`, og.warranties, og.warranties_cost/(o.course_value/100) as warranties_cost, og.id, og.foreign_warehouse
                FROM {orders_goods} as og, {orders} as o
                WHERE og.order_id=?i AND og.order_id=o.id', array($order['id']))->assoc();
        } else // берем товары с поста (админка)
        if ( $order && isset($order['goods_id']) ) {
            foreach ($order['goods_id'] as $k=>$goods_id) {
                $count = (isset($order['count']) && isset($order['count'][$k]) && isset($order['count'][$k][$goods_id]) && $order['count'][$k][$goods_id] > 0)?$order['count'][$k][$goods_id]:0;
                if ( $count == 0 )
                    continue;
                $warranties = (isset($order['warranties']) && isset($order['warranties'][$k]) && isset($order['warranties'][$k][$goods_id]) && $order['warranties'][$k][$goods_id] > 0)?$order['warranties'][$k][$goods_id]:0;
                $product = $this->db->query('SELECT price, id, foreign_warehouse, title, url, qty_store as exist, wait FROM {goods} WHERE id=?i',
                    array($goods_id))->row();

                if ($product) {
                    $goods[] = array(
                        'goods_id'          =>  $goods_id,
                        'count'             =>  $count,
                        'warranties'        =>  $warranties,
                        'price'             =>  $product['price'],
                        'url'               =>  $product['url'],
                        'foreign_warehouse' =>  $product['foreign_warehouse'],
                        'title'             =>  $product['title'],
                        'id'                =>  $product['id'],
                        'exist'             =>  $product['exist'],
                        'wait'              =>  $product['wait'],
                        'discount'          =>  isset($order['discount']) && isset($order['discount'][$k]) ? $order['discount'][$k] : 0,
                    );
                }
            }
        } else {
            if ( intval($this->id) < 1 )
                return false;
            // берем товары с корзины
            $goods = $this->db->query('SELECT sc.goods_id, sc.prio, sc.id, sc.count, sc.warranties, g.price,
                        g.url, g.title, g.foreign_warehouse, g.qty_store as exist, g.wait
                    FROM {shopping_cart} as sc, {goods} as g
                    WHERE g.id=sc.goods_id AND g.avail=1 AND sc.?q=?i ?q ORDER BY sc.prio, sc.id DESC',
                array($this->row_id, intval($this->id), $goods_id_query))->assoc('goods_id');

            if ($goods) {
                $images = $this->db->query('SELECT DISTINCT i.goods_id, i.image as attachment FROM {goods_images} as i
                    WHERE i.goods_id IN (?li) AND i.type=1 ORDER BY i.prio', array(array_keys($goods)))->vars();

                foreach ($goods as $g_id=>$p) {
                    $goods[$g_id]['attachment'] = is_array($images) && array_key_exists($g_id, $images) ? $images[$g_id] : '';
                }
            }
        }

        if ( count($goods) > 0) {
            return $goods;
        } else {
            return false;
        }
    }

    function select_address ($name = 'address', $class = 'installment-select-address')
    {
        if ( isset($_SESSION['user_id']) ) {
            $addresses = $this->db->query('SELECT id, content, region, city FROM {clients_delivery_addresses} WHERE user_id=?i',
                array($_SESSION['user_id']))->assoc();

            if ( $addresses ) {
                $wa = '<select class="' . $class . '" name="select-' . $name . '"><option value="">Выберите из списка</option>';
                foreach ( $addresses as $address ) {
                    $region = '';
                    if ( array_key_exists($address['region'], $this->configs['regions']))
                        $region = htmlspecialchars($this->configs['regions'][$address['region']]);
                    $city = '';
                    if ( array_key_exists($address['region'], $this->configs['cities']) && array_key_exists($address['city'], $this->configs['cities'][$address['region']]) )
                        $city = htmlspecialchars($this->configs['cities'][$address['region']][$address['city']]);

                    $wa .= '<option value="' . $address['id'] . '">' . $region . '/' . $city. '/' . htmlspecialchars($address['content']) . '</option>';
                }
                $wa .= '</select>';

                return $wa;
            }
        }

        return '';
    }

    function installment_query($post, $model)
    {
        $array = array();
        $array['credits_package'] = (isset($post['i_credits_package']) && mb_strlen(trim($post['i_credits_package']), 'UTF-8') > 0) ? trim($post['i_credits_package']) : null;
        $array['email'] = (isset($post['i_email']) && mb_strlen(trim($post['i_email']), 'UTF-8') > 0) ? trim($post['i_email']) : null;
        $array['fio'] = (isset($post['i_fio']) && mb_strlen(trim($post['i_fio']), 'UTF-8') > 0) ? trim($post['i_fio']) : null;
        $array['institution'] = (isset($post['i_institution']) && mb_strlen(trim($post['i_institution']), 'UTF-8') > 0) ? trim($post['i_institution']) : null;
        $array['birthday'] = (isset($post['i_birthday']) && mb_strlen(trim($post['i_birthday']), 'UTF-8') > 0) ? trim($post['i_birthday']) : null;
        $array['job'] = (isset($post['i_job']) && mb_strlen(trim($post['i_job']), 'UTF-8') > 0) ? trim($post['i_job']) : null;
        $array['identification_code'] = (isset($post['i_identification_code']) && mb_strlen(trim($post['i_identification_code']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_identification_code'])) : null;
        $array['works_phone'] = (isset($post['i_works_phone']) && mb_strlen(trim($post['i_works_phone']), 'UTF-8') > 0) ? trim($post['i_works_phone']) : null;
        $array['passport'] = (isset($post['i_passport']) && mb_strlen(trim($post['i_passport']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_passport'])) : null;
        $array['works_address'] = (isset($post['i_works_address']) && mb_strlen(trim($post['i_works_address']), 'UTF-8') > 0) ? trim($post['i_works_address']) : null;
        $array['issued_passport'] = (isset($post['i_issued_passport']) && mb_strlen(trim($post['i_issued_passport']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_issued_passport'])) : null;
        $array['position'] = (isset($post['i_position']) && mb_strlen(trim($post['i_position']), 'UTF-8') > 0) ? trim($post['i_position']) : null;
        $array['when_passport_issued'] = (isset($post['i_when_passport_issued']) && mb_strlen(trim($post['i_when_passport_issued']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_when_passport_issued'])) : null;
        $array['relationship'] = (isset($post['i_relationship']) && mb_strlen(trim($post['i_relationship']), 'UTF-8') > 0) ? trim($post['i_relationship']) : null;
        $array['registered_address'] = (isset($post['i_registered_address']) && mb_strlen(trim($post['i_registered_address']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_registered_address'])) : null;
        $array['residential_address'] = (isset($post['i_residential_address']) && mb_strlen(trim($post['i_residential_address']), 'UTF-8') > 0) ? $model->urlsafe_b64encode(trim($post['i_residential_address'])) : null;
        $array['childrens'] = (isset($post['i_childrens']) && mb_strlen(trim($post['i_childrens']), 'UTF-8') > 0) ? trim($post['i_childrens']) : null;
        $array['address'] = (isset($post['i_address']) && mb_strlen(trim($post['i_address']), 'UTF-8') > 0) ? trim($post['i_address']) : null;
        $array['childrens_age'] = (isset($post['i_childrens_age']) && mb_strlen(trim($post['i_childrens_age']), 'UTF-8') > 0) ? trim($post['i_childrens_age']) : null;
        $array['ind'] = (isset($post['i_ind']) && mb_strlen(trim($post['i_ind']), 'UTF-8') > 0) ? trim($post['i_ind']) : null;
        $array['counts_people_apartment'] = (isset($post['i_counts_people_apartment']) && mb_strlen(trim($post['i_counts_people_apartment']), 'UTF-8') > 0) ? trim($post['i_counts_people_apartment']) : null;
        $array['mobile'] = (isset($post['i_mobile']) && mb_strlen(trim($post['i_mobile']), 'UTF-8') > 0) ? trim($post['i_mobile']) : null;
        $array['phone'] = (isset($post['i_phone']) && mb_strlen(trim($post['i_phone']), 'UTF-8') > 0) ? trim($post['i_phone']) : null;
        $array['education'] = (isset($post['i_education']) && mb_strlen(trim($post['i_education']), 'UTF-8') > 0) ? trim($post['i_education']) : null;
        $array['comment'] = (isset($post['i_comment']) && mb_strlen(trim($post['i_comment']), 'UTF-8') > 0) ? trim($post['i_comment']) : null;

        if ( array_key_exists($array['credits_package'], $this->configs['credits_package']) ) {
            $array['credits_cost'] = $this->configs['credits_package'][$array['credits_package']];
        } else {
            $array['credits_package'] = key($this->configs['credits_package']);
            $array['credits_cost'] = $this->configs['credits_package'][$array['credits_package']];
        }

        $query = $this->db->makeQuery('credits_package=?n, credits_cost=?n, email=?n, fio=?n, institution=?n, birthday=?n, job=?n,
                identification_code=?n, works_phone=?n, passport=?n, works_address=?n, issued_passport=?n, position=?n,
                when_passport_issued=?n, relationship=?n, registered_address=?n, residential_address=?n, childrens=?n, address=?n,
                childrens_age=?n, ind=?n, counts_people_apartment=?n, mobile=?n, phone=?n, education=?n, comment=?n',
            array($array['credits_package'], $array['credits_cost'], $array['email'], $array['fio'], $array['institution'],
                $array['birthday'], $array['job'], $array['identification_code'], $array['works_phone'], $array['passport'],
                $array['works_address'], $array['issued_passport'], $array['position'], $array['when_passport_issued'],
                $array['relationship'], $array['registered_address'], $array['residential_address'], $array['childrens'],
                $array['address'], $array['childrens_age'], $array['ind'], $array['counts_people_apartment'], $array['mobile'],
                $array['phone'], $array['education'], $array['comment']
            ));

        return array('query'=>$query, 'array'=>$array);
    }

    function installment_form ($order_id = null, $goods_id = null, $form = true, $edit = true)
    {
        $d = $edit == true ? '' : 'disabled';

        $model = new Model();

        $installment_form = '';

        $credits_packages = $this->configs['credits_package'];
        $select_cp = '<select ' . $d . ' class="input-small" data-required="true" name="i_credits_package"><option value="">' . l('Выберите') . '</option>';
        foreach ( $credits_packages as $credits_package=>$overpaid ) {
            $select_cp .= '<option value="' . $credits_package . '">' . $credits_package . '</option>';
        }
        $select_cp .= '</select>';
        $email = '';
        $fio = '';
        $institution = '';
        $birthday = '';
        $job = '';
        $works_phone = '';
        $position = '';
        $relationship = '';
        $childrens = '';
        $childrens_age = '';
        $index = '';
        $counts_people_apartment = '';
        $education = '';
        $phone = '';
        $mobile = '';
        $identification_code = '';
        $passport = '';
        $issued_passport = '';
        $when_passport_issued = '';
        $registered_address = '';
        $residential_address = '';
        $works_address = '';
        $comment = '';

        // информация с клинта
        if ( $order_id == null && isset($_SESSION['user_id']) ) {

            $user = $this->db->query('SELECT email, fio, institution, birthday, job, works_phone, position, relationship, childrens,
                    childrens_age, ind, counts_people_apartment, education, phone, mobile, identification_code, passport,
                  issued_passport, when_passport_issued, registered_address, residential_address, credits_package, works_address
                FROM {clients} WHERE id=?i', array($_SESSION['user_id']))->row();

            if ( $user ) {
                $select_cp = '<select ' . $d . ' class="input-small" data-required="true" name="i_credits_package"><option value="">' . l('Выберите') . '</option>';
                foreach ( $credits_packages as $credits_package=>$overpaid ) {
                    if ( $credits_package == $user['credits_package'])
                        $select_cp .= '<option selected value="' . $credits_package . '">' . $credits_package . '</option>';
                    else
                        $select_cp .= '<option value="' . $credits_package . '">' . $credits_package . '</option>';
                }
                $select_cp .= '</select>';

                $works_address = $user['works_address'];
                $email = trim($user['email']);
                $fio = trim($user['fio']);
                $institution = trim($user['institution']);
                $birthday = trim($user['birthday']);
                $job = trim($user['job']);
                $works_phone = trim($user['works_phone']);
                $position = trim($user['position']);
                $relationship = trim($user['relationship']);
                $childrens = ($user['childrens'] > 0)?trim($user['childrens']):'';
                $childrens_age = trim($user['childrens_age']);
                $index = ($user['ind']>0) ? trim($user['ind']) : '';
                $counts_people_apartment = ($user['counts_people_apartment']>0)?trim($user['counts_people_apartment']):'';
                $education = trim($user['education']);
                $phone = trim($user['phone']);
                $mobile = trim($user['mobile']);
                $identification_code = $model->urlsafe_b64decode(trim($user['identification_code']));
                $passport = $model->urlsafe_b64decode(trim($user['passport']));
                $issued_passport = $model->urlsafe_b64decode(trim($user['issued_passport']));
                $when_passport_issued = $model->urlsafe_b64decode(trim($user['when_passport_issued']));
                $registered_address = $model->urlsafe_b64decode(trim($user['registered_address']));
                $residential_address = $model->urlsafe_b64decode(trim($user['residential_address']));
            }
        }

        // информация с заказа
        if ( $order_id > 0 ) {

            $order = $this->db->query('SELECT email, fio, institution, birthday, job, works_phone, position, relationship, childrens,
                    childrens_age, ind, counts_people_apartment, education, phone, mobile, identification_code, passport, comment,
                    issued_passport, when_passport_issued, registered_address, residential_address, credits_package, works_address
                FROM {orders} WHERE id=?i', array($order_id))->row();

            if ( $order ) {
                $email = trim($order['email']);
                $fio = trim($order['fio']);
                $institution = trim($order['institution']);
                $birthday = trim($order['birthday']);
                $job = trim($order['job']);
                $works_phone = trim($order['works_phone']);
                $position = trim($order['position']);
                $relationship = trim($order['relationship']);
                $childrens = ($order['childrens'] > 0)?trim($order['childrens']):'';
                $childrens_age = trim($order['childrens_age']);
                $index = ($order['ind'] > 0)?trim($order['ind']):'';
                $counts_people_apartment = ($order['counts_people_apartment']>0)?trim($order['counts_people_apartment']):'';
                $education = trim($order['education']);
                $phone = trim($order['phone']);
                $mobile = trim($order['mobile']);
                $identification_code = $model->urlsafe_b64decode(trim($order['identification_code']));
                $passport = $model->urlsafe_b64decode(trim($order['passport']));
                $issued_passport = $model->urlsafe_b64decode(trim($order['issued_passport']));
                $when_passport_issued = $model->urlsafe_b64decode(trim($order['when_passport_issued']));
                $registered_address = $model->urlsafe_b64decode(trim($order['registered_address']));
                $residential_address = $model->urlsafe_b64decode(trim($order['residential_address']));
                $comment = trim($order['comment']);
                $works_address = trim($order['works_address']);
                $select_cp = '<select ' . $d . ' class="input-small" data-required="true" name="i_credits_package"><option value="">' . l('Выберите') . '</option>';
                foreach ( $credits_packages as $credits_package=>$overpaid ) {
                    if ( $credits_package == $order['credits_package'])
                        $select_cp .= '<option selected value="' . $credits_package . '">' . $credits_package . '</option>';
                    else
                        $select_cp .= '<option value="' . $credits_package . '">' . $credits_package . '</option>';
                }
                $select_cp .= '</select>';
            }
        }
        if ( $form == true )
            $installment_form .= '<form method="post" id="installment_form" class="installment-form" data-validate="parsley">';

        $disabled = '';
        if ( isset($_SESSION['email']) )
            $disabled = 'disabled';

        $installment_form .= '<table class="table_installment"><tbody><tr><td class="td_name">Пакет кредитования</td><td>' . $select_cp . ' платежа</td>
            <td class="td_name">Электронный адрес: </td><td><input ' . $d . ' class="input" data-trigger="change" data-required="true" data-type="email" type="text" ' . $disabled . ' name="i_email" value="' . htmlspecialchars($email) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Ф.И.О. клиента: </td><td><input ' . $d . ' class="input" type="text" name="i_fio" value="' . htmlspecialchars($fio) . '" /></td>
            <td class="td_name">Какое учебное учреждение закончил: </td><td><input ' . $d . ' class="input" type="text" name="i_institution" value="' . htmlspecialchars($institution) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Дата рождения: </td><td><input ' . $d . ' class="datepicker input" type="text" class="datepicker" name="i_birthday" value="' . htmlspecialchars($birthday) . '" /></td>
            <td class="td_name">Место работы: </td><td><input ' . $d . ' class="input" type="text" name="i_job" value="' . htmlspecialchars($job) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Идентификационный код: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($identification_code) . '" name="i_identification_code" /></td>
            <td class="td_name">Телефон работы: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($works_phone) . '" name="i_works_phone" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Паспорт номер: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($passport) . '" name="i_passport" /></td>
            <td class="td_name">Адрес работы: </td><td><input ' . $d . ' type="text" class="input installment-input-address" name="i_works_address" value="' . htmlspecialchars($works_address) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Кем выдан: </td><td><input ' . $d . ' class="input" type="text" name="i_issued_passport" value="' . htmlspecialchars($issued_passport) . '" /></td>
            <td class="td_name">Должность: </td><td><input ' . $d . ' class="input" type="text" name="i_position" value="' . htmlspecialchars($position) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Когда выдан: </td><td><input ' . $d . ' class="datepicker input" type="text" value="' . htmlspecialchars($when_passport_issued) . '" name="i_when_passport_issued" /></td>
            <td class="td_name">Семейное положение: </td><td><input ' . $d . ' class="input" type="text" name="i_relationship" value="' . htmlspecialchars($relationship) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Адрес реестрации: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($registered_address) . '" name="i_registered_address" /></td>
            <td class="td_name">Сколько детей: </td><td><input ' . $d . ' class="input" type="text" name="i_childrens" value="' . htmlspecialchars($childrens) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Адрес места проживания: </td><td><input ' . $d . ' type="text" value="' . htmlspecialchars($residential_address) . '" class="input installment-input-address" name="i_residential_address" /></td>
             <td class="td_name">Возраст детей: </td><td><input ' . $d . ' class="input" type="text" name="i_childrens_age" value="' . htmlspecialchars($childrens_age) . '" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Почтовый индекс: </td><td><input ' . $d . ' class="input" type="text" name="i_ind" value="' . htmlspecialchars($index) . '" /></td>
                 <td class="td_name">Сколько людей проживает в квартире: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($counts_people_apartment) . '" name="i_counts_people_apartment" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Мобильный телефон: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($mobile) . '" name="i_mobile" /></td>
                 <td class="td_name">Домашний телефон: </td><td><input ' . $d . ' class="input" type="text" data-type="phone" value="' . htmlspecialchars($phone) . '" name="i_phone" /></td></tr>';
        $installment_form .= '<tr><td class="td_name">Образование: </td><td><input ' . $d . ' class="input" type="text" value="' . htmlspecialchars($education) . '" name="i_education" /></td>
                 <td class="td_name">Комментарий</td><td ><textarea ' . $d . ' class="textarea" name="i_comment" rows="4" cols="18">' . htmlspecialchars($comment) . '</textarea></td></tr>';
        $installment_form .= '<tr><td colspan="4" class="td_horz_line"><div></div></td></tr>';

        //if ( $form == true )
        //    $installment_form .= '<tr><td class="right" colspan="4"><input type="button" onclick="update_order_installment(this)" class="green_btn" value="Отправить" /></td></tr>';

        if ( $goods_id > 0 ) {
            $installment_form .= '<input type="hidden" name="goods_id" value="' . $goods_id . '" />';
            $installment_form .= '<input type="hidden" id="for-installment-related" name="related" value="" />';
        }
        //if ( $order_id && $form == false )
        //if ( $order_id > 0 )
            $installment_form .= '<input type="hidden" id="for-installment-order_id" name="order_id" value="' . $order_id . '" />';

        $installment_form .= '</tbody></table>';

        if ( $form == true )
            $installment_form .= '</form>';

        return $installment_form;
    }


    // оформление корзины
    // выбор региона
    function show_region_select($region, $edit = true)
    {
        $d = (($edit == true) ? '' : 'disabled');

        $region_html = '<select ' . $d . ' class="input current-region" onchange="change_region(this.value)" name="region">';
        foreach($this->configs['regions'] as $cid => $vregion){
            if ($region == $cid)
                $region_html .= '<option selected value="' . $cid . '">' . $vregion . '</option>';
            else
                $region_html .= '<option value="' . $cid . '">' . $vregion . '</option>';
        }
        $region_html .= '</select>';

        return $region_html;
    }

// выбор города
    function show_city_select($city = null, $region = null, $edit = true)
    {
        if (!$region) $region = array_key_exists('region', $_SESSION) ? $_SESSION['region'] : $this->region;
        if (!$city) $city = array_key_exists('city', $_SESSION) ? $_SESSION['city'] : ($region == 12 ? $this->city : $this->d_city);

        $d = $edit == true ? '' : 'disabled';

        $city_html = '<select ' . $d . ' class="input" onchange="change_city(this.value)" id="city" name="city">';
        if(array_key_exists($region, $this->configs['cities'])) {
            foreach($this->configs['cities'][$region] as $cid => $city_name) {
                if ($city == $cid)
                    $city_html .= '<option selected value="' . $cid . '">' . $city_name . '</option>';
                else
                    $city_html .= '<option value="' . $cid . '">' . $city_name . '</option>';
            }
        }
        $city_html .= '</select>';

        return $city_html;
    }

    function checkout_processing_post ($goods, $info, $edit = true)
    {
        $d = $edit == true ? '' : 'disabled';

        $shipping   =   'pickup';
        $payment    =   'cash';
        $person     =   'person';
        $warehouse  =   'warehouse';
        $wait       =   false;

        $fio        =   '';
        $email      =   '<input ' . $d . ' type="text" data-trigger="change" data-required="true" data-type="email" value="" name="email" class="input" />';
        $phone      =   '';
        $comment    =   '';
        $office     =   '';
        $np_office  =   '';
        $address    =   '';
        if ( $info && isset($info['fio']) ) $fio = trim($info['fio']);
        if ( $info && isset($info['email']) ) $email = htmlspecialchars(trim($info['email']));
        if ( $info && isset($info['phone']) ) $phone = trim($info['phone']);
        if ( $info && isset($info['office_id']) ) $office = trim($info['office_id']);
        if ( $info && isset($info['np_office_id']) ) $np_office = trim($info['np_office_id']);
        if ( $info && isset($info['address_id']) ) $address = trim($info['address_id']);

        // for order
        //if ( $info && isset($info['np_office']) ) $np_office = trim($info['np_office']);
        if ( $info && isset($info['address']) ) $address = trim($info['address']);
        //if ( $info && isset($info['office']) ) $office = trim($info['office']);

        if ( isset($_POST['fio']) ) $fio = trim($_POST['fio']);
        if ( isset($_POST['email']) && !$info ) {
            if ( !filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) )
                $email = '<input ' . $d . ' type="text" value="' . htmlspecialchars(trim($_POST['email'])) . '" name="email" data-trigger="change" data-required="true" data-type="email" class="input input-error" />';
            else
                $email = '<input ' . $d . ' type="text" value="' . htmlspecialchars(trim($_POST['email'])) . '" name="email" data-trigger="change" data-required="true" data-type="email" class="input" />';
        }
        if ( isset($_POST['phone']) ) $phone = trim($_POST['phone']);
        if ( isset($_POST['comment']) ) $comment = trim($_POST['comment']);
        if ( isset($_POST['office']) ) $office = trim($_POST['office']);
        if ( isset($_POST['np_office']) ) $np_office = trim($_POST['np_office']);
        if ( isset($_POST['address']) ) $address = trim($_POST['address']);

        $city = '';
        if ( isset($_SESSION['city']) )
            $city = isset($_SESSION['city']) ? $_SESSION['city'] : 0;

        $region = '';
        if ( isset($_SESSION['region']) )
            $region = isset($_SESSION['region']) ? $_SESSION['region'] : 0;

        if ( isset($_POST['region']) && $_POST['region'] > 0 && array_key_exists($_POST['region'], $this->configs['regions']) && array_key_exists($_POST['region'], $this->configs['cities']) )
            $region = $_POST['region'];
        if ( isset($_POST['city']) && $_POST['city'] > 0 && array_key_exists($_POST['city'], $this->configs['cities'][$_POST['region']]) )
            $city = $_POST['city'];

        if ( isset($_POST['shipping']) && array_key_exists($_POST['shipping'], $this->configs['shipping-msg']) ) {
            $shipping = $_POST['shipping'];
        } else {
            //if ( $city != 13 )
            //    $shipping = 'novaposhta';
            /*foreach ( $this->configs['shipping-msg'] as $k=>$v ) {
                if ( $v['default'] && $v['default'] == 1 ) {
                    $shipping = $k;
                    break;
                }
            }*/
        }

        if ( isset($_POST['payment']) && array_key_exists($_POST['payment'], $this->configs['payment-msg']) ) {
            $payment = $_POST['payment'];
        } else {
            foreach ( $this->configs['payment-msg'] as $k=>$v ) {
                if ( $v['default'] && $v['default'] == 1 ) {
                    $payment = $k;
                    break;
                }
            }
        }

        if ( $info && isset($info['shipping']) && !empty($info['shipping']) ) $shipping = $info['shipping'];
        if ( $info && isset($info['payment']) && !empty($info['payment']) ) $payment = $info['payment'];


        //if ( $p == 2 && !isset($_POST['person']) )                      $person = 'corporation';
        //if ( isset($_POST['person']) && $_POST['person'] == 'true' )    $person = 'person';
        if ( isset($info['person']) && $info['person'] == 2 )           $person = 'corporation';
        if ( isset($_POST['person']) && $_POST['person'] == 'false' )   $person = 'corporation';

        if ( is_array($goods) && count($goods) > 0 ) {
            $for_wait = false;
            foreach($goods as $product) {
                if ($product['foreign_warehouse'] == 1) {
                    $warehouse = 'supplier';
                    //break;
                }
                if (array_key_exists('exist', $product) && $product['exist'] > 0) {
                    $for_wait = true;
                    $wait = false;
                }

                // для сообщения товар в ожидании
                if ($for_wait == false && array_key_exists('wait', $product) && array_key_exists('exist', $product)
                        && $product['exist'] == 0 && !empty($product['wait']) && $product['foreign_warehouse'] == 0)
                    $wait = true;
            }
        }

        return array(
            'payment'   =>  $payment,
            'person'    =>  $person,
            'shipping'  =>  $shipping,
            'region'    =>  $region,
            'city'      =>  $city,
            'warehouse' =>  $warehouse,
            'fio'       =>  $fio,
            'email'     =>  $email,
            'phone'     =>  $phone,
            'comment'   =>  $comment,
            'office'    =>  $office,
            'np_office' =>  $np_office,
            'address'   =>  $address,
            'wait'      =>  $wait,
        );
    }

    function show_payments_block($data, $shipping, $model, $sum, $order = null, $edit = true) {

        $shc_html = '';
        $payment_out = '';
        $person = $data['person'];
        $payment = null;

        $d = $edit == true ? '' : 'disabled';

        if (!empty($data['payment'])) {
            foreach ($this->configs['payment-msg'] as $k => $v) {
                if ($data['payment'] == $k && array_key_exists($person, $v) && array_key_exists($shipping, $v['shipping'])) {
                    $payment = $data['payment'];
                    break;
                }
            }
        }
        foreach ($this->configs['payment-msg'] as $k => $v) {

            if( array_key_exists($person, $v) && array_key_exists($shipping, $v['shipping']) ) {
                $delivery = $this->delivery(array(
                    'person'    =>  $data['person'],
                    'warehouse' =>  $data['warehouse'],
                    'payment'   =>  $k,
                    'shipping'  =>  $shipping,
                    'city'      =>  $data['city'],
                ), $model);
                $price = 0 . ' '. l('грн.');
                if ( count($sum) > 0 ) {
                    $prices = $this->get_all_price($delivery, $sum);
                    if ($order === null) {
                        $price = $model->currency_view($prices['payment-cost'], false, 2);
                    } else {
                        $price = $model->show_price($prices['payment-cost'][$data['course_key']]*100, null, null, true);
                    }
                }
                if ($k == 'installment') {
                    if(($payment != null && $payment == $k) || ($payment == null && empty($payment_out))) {
                    //if( $payment == $k || empty($payment_out) ) {
                        $payment_out = $k;
                        $shc_html .= '<label><input ' . $d . ' class="checkout-payment" onchange="checkout_change()" type="radio" name="payment" checked value="' . $k . '" /> ' . $v['name'] . '</label>';
                    } else {
                        $shc_html .= '<label><input ' . $d . ' class="checkout-payment" onchange="checkout_change()" type="radio" name="payment" value="' . $k . '" /> ' . $v['name'] . '</label>';
                    }
                } else {
                    if(($payment != null && $payment == $k) || ($payment == null && empty($payment_out))) {
                    //if( $payment == $k || empty($payment_out) ) {
                        $payment_out = $k;
                        $shc_html .= '<label><input ' . $d . ' class="checkout-payment" onchange="checkout_change()" type="radio" name="payment" checked value="' . $k . '" /> ' . $v['name'] . ' (' . $price . ')</label>';
                    } else {
                        $shc_html .= '<label><input ' . $d . ' class="checkout-payment" onchange="checkout_change()" type="radio" name="payment" value="' . $k . '" /> ' . $v['name'] . ' (' . $price . ')</label>';
                    }
                }
            }
        }

        return array('html' => $shc_html, 'payment' => $payment_out);
    }

    function show_shipping_block($data, $sum, $model, $product_page = false, $order = null, $edit = true)
    {
        $cur_region =   $data['region'];
        $cur_city   =   array_key_exists($data['region'], $this->configs['cities']) && array_key_exists($data['city'], $this->configs['cities'][$data['region']])
            ? $data['city'] : ($data['region'] == $this->region ? $this->city : $this->d_city);

        $o              = $this->db->query('SELECT address FROM {offices} WHERE region=?i AND city=?i AND avail=1 AND address<>"" AND address IS NOT NULL', array($cur_region, $cur_city))->el();
        $np             = $this->db->query('SELECT id FROM {nova_poschta} WHERE region=?i AND city=?i', array($cur_region, $cur_city))->el();
        $shipping_out   = '';
        $shc_html       = '';

        //if ( $order === null ) {

            $person         = $data['person'];
            $shipping       = $data['shipping'];
        //} else {
        //    $person         = ($order['person']==2)?'corporation':'person';
        //    $shipping       = $order['shipping'];
        //}

        $d = $edit == true ? '' : 'disabled';

        $show_ttn = false;

        foreach($this->configs['shipping-msg'] as $k => $v) {
            if ( array_key_exists($person, $v) ) {

                if ( isset($v['time']) && date("H", time()) > $v['time'] ) continue; // время

                if ( ($k == 'novaposhta' || $k == 'novaposhta_cash') && ($cur_city == 13 || !$np /*|| $np < 1*/) )
                    continue;

                if ( (($k == 'courier' || $k == 'courier_today' || $k == 'pickup' || $k == 'express') && (!$o /*|| $o < 1*/) ) || ($k == 'express' && $cur_city != 13 ) )
                    continue;

                $sub = '';
                if ( $k == 'novaposhta' )
                    $sub = '*<sup> Стоимость рассчитана на основании тарифа &laquo;Новой Почты&raquo; и может незначительно отличаться</sup>';
                $payment = $data['payment'];

                if (!$payment || empty($payment) || $product_page == true || !array_key_exists($k, $this->configs['payment-msg'][$payment]['shipping']) || !array_key_exists($data['person'], $this->configs['payment-msg'][$payment])) {
                    foreach ($this->configs['payment-msg'] as $key=>$value ) {
                        if (array_key_exists($k, $value['shipping']) && array_key_exists($data['person'], $value) && ((/*$key != 'transfer' && */$key != 'installment') || $product_page == false)) {
                            $payment = $key;
                            break;
                        }
                    }
                }
                $delivery = $this->delivery(
                    array(
                        'person'    =>  $data['person'],
                        'warehouse' =>  $data['warehouse'],
                        'payment'   =>  $payment,
                        'shipping'  =>  $k,
                        'city'      =>  $cur_city,
                        'wait'      =>  (array_key_exists('wait', $data) && $data['wait'] == true) ? true : false,
                        'none'      =>  (array_key_exists('none', $data) && $data['none'] == true) ? true : false,
                    ), $model
                );
                $delivery_info = $this->get_all_price($delivery, $sum);

                if ($delivery_info['ok'] == 0 && (!$order/* || $order['shipping'] != $k*/)) continue;

                $cost = $delivery_info['shipping-cost'];
                if ( $product_page == true ) {
                    if ($cost['price'] == 0)
                        $show_cost = 'Бесплатно';
                    else
                        $show_cost = /*'<span class="delivery-cost">' . */$model->currency_view($delivery_info['shipping-cost'], false, 2)/* . '</span>'*/;
                    if ( $k == 'pickup' )
                        $shc_html .= '<li><span class="text-bold">' . $v['name'] . ' (' . $show_cost . '): </span>' . $o . '. '  . $delivery['msg'] . '</li>';
                    else
                        $shc_html .= '<li><span class="text-bold">' . $v['name'] . ' (' . $show_cost . '): </span>' . $delivery['msg'] . '</li>';
                } else {
                    if ($order === null) {
                        $show_cost = $model->currency_view($delivery_info['shipping-cost'], false, 2);
                    } else {
                        $show_cost = $model->show_price($delivery_info['shipping-cost'][$data['course_key']]*100, null, null, true);
                    }
                    $m = '';
                    if ( $k == 'novaposhta' || $k == 'novaposhta_cash' ) {
                        $show_ttn = true;
                        $m = '<p class="sc-light-block-head">Сроки доставки: <span>' . $delivery['msg'] . '</span></p>';
                    }

                    if( $shipping == $k || (empty($shipping_out) /*&& $shipping == $k && $k != 'cash' && $k != 'novaposhta' /*&& !$order*/) ) {
                        $shipping_out = $k;
                        $shc_html .= '<label><input ' . $d . ' class="checkout-shipping" onchange="checkout_change()" type="radio" name="shipping" checked value="' . $k . '" /> '. $v['name'] .
                                '<p class="sc-light-block-head">Стоимость доставки: ' . $show_cost . $sub . '</p>' .
                                $m . //  '<p class="sc-light-block-head">Сроки доставки <span>' . $delivery['msg'] . '</span></p>' .
                            '</label>';
                    } else {
                        $shc_html .= '<label>
                                <input ' . $d . ' class="checkout-shipping" onchange="checkout_change()" type="radio" name="shipping" value="' . $k . '" /> ' . $v['name'] .
                                '<p class="sc-light-block-head">Стоимость доставки: ' . $show_cost . $sub . '</p>' .
                                $m .
                            '</label>';
                    }
                }
            }
        }

        // ттн нп
        if ($order && $show_ttn == true) {
            $ttns = $this->db->query('SELECT ttn FROM {np_ttn} WHERE order_id=?i',
                array(isset($order['order_id']) ? $order['order_id'] : $order['id']))->vars();
            if ($ttns) {
                $shc_html .= '<p>Номера декларации ' . implode(', ', $ttns) . '</p>';
            }
        }

        return array('html' => $shc_html, 'shipping' => $shipping_out);
    }

    function offices ($region, $city, $data, $edit = true)
    {
        $offices_html = '';

        $offices = $this->db->query('SELECT address, id FROM {offices} WHERE region=?i AND city=?i AND avail=1 AND address<>"" AND address IS NOT NULL', array($region, $city))->assoc();

        $d = $edit == true ? '' : 'disabled';

        if ( $offices ) {
            $offices_html = '<select ' . $d . ' class="input" name="office"><option value="">Выберите из списка</option>';
            foreach ( $offices as $office ) {
                if ( $data['office'] == $office['id'] )
                    $offices_html .= '<option selected value="' . $office['id'] . '">' . $office['address'] . '</option>';
                else
                    $offices_html .= '<option value="' . $office['id'] . '">' . $office['address'] . '</option>';
            }
            $offices_html .= '</select>';
        }

        return $offices_html;
    }

    function np_delivery( $region_id, $city_id, $data, $edit = true)
    {
        $np = $this->db->query('SELECT office, id FROM {nova_poschta} WHERE city=?i AND region=?i', array($city_id, $region_id))->assoc();

        if ( !$np || count($np) == 0 ) return false;

        $d = $edit == true ? '' : 'disabled';

        $np_html = '<select ' . $d . ' class="input" name="np_office"><option value="">' . l('Выберите') . '</option>';
        foreach ($np as $v) {
            if ( //($order && $order['shipping'] && ($order['shipping'] == 'novaposhta_cash' || $order['shipping'] == 'novaposhta') && isset($order['np_office']) && $v['office'] == $order['np_office'] && !isset($_POST['np_office'])) ||
                //(isset($_POST['np_office']) && $_POST['np_office'] == $v['id'])
                $data['np_office'] == $v['id'] /*|| $data['np_office'] == $v['office']*/ )
                $np_html .= '<option selected value="' . $v['id'] . '">' . $v['office'] . '</option>';
            else
                $np_html .= '<option value="' . $v['id'] . '">' . $v['office'] . '</option>';
        }
        $np_html .= '</select>';

        return $np_html;
    }

    function checkout_all_block ($model, $data, $order = null)
    {
        $cs = $this->get_count_and_price($order);
        $shipping = $this->show_shipping_block($data, $cs['sum'], $model, false, $order);
        $data['shipping'] = $shipping['shipping'];
        $payment = $this->show_payments_block($data, $shipping['shipping'], $model, $cs['sum'], $order);
        $data['payment'] = $payment['payment'];

        $delivery = $this->delivery($data, $model);
        $all_sum = $this->get_all_price($delivery, $cs['sum']);

        if ( $order ) {
            return array(
                'show_payment_block'        =>  $payment['html'],
                'show_shipping_block'       =>  $shipping['html'],
                'show_delivery_time_block'  =>  $delivery['msg'],
                'cart_goods_cost'           =>  $model->show_price($cs['sum'][$data['course_key']] * 100, null, null, 2),
                'cart_all_summ'             =>  $model->show_price($all_sum['all-sum'][$data['course_key']] * 100, null, null, 2),
                'cart_payment_cost'         =>  $model->show_price($all_sum['payment-cost'][$data['course_key']] * 100, null, null, 2),
                'cart_delivery_cost'        =>  $model->show_price($all_sum['shipping-cost'][$data['course_key']] * 100, null, null, 2),
                'city'                      =>  $this->show_city_select($data['city'], $data['region']),
                'payment'                   =>  $payment['payment'],
                'shipping'                  =>  $shipping['shipping'],
                'np_select'                 =>  $this->np_delivery($data['region'], $data['city'], $data),
                'offices'                   =>  $this->offices($data['region'], $data['city'], $data),
            );
        } else {
            return array(
                'show_payment_block'        =>  $payment['html'],
                'show_shipping_block'       =>  $shipping['html'],
                'show_delivery_time_block'  =>  $delivery['msg'],
                'cart_all_summ'             =>  $model->currency_view($all_sum['all-sum'], false, 2),//$cs$all_sum['sum']
                'cart_payment_cost'         =>  $model->currency_view($all_sum['payment-cost'], false, 2),
                'cart_delivery_cost'        =>  $model->currency_view($all_sum['shipping-cost'], false, 2),
                'payment'                   =>  $payment['payment'],
                'shipping'                  =>  $shipping['shipping'],
                'np_select'                 =>  $this->np_delivery($data['region'], $data['city'], $data),
                'offices'                   =>  $this->offices($data['region'], $data['city'], $data),
            );
        }
    }

    function delivery($data, $model)
    {
        $day_of_week    =   /*array_key_exists('week', $data) ? $data['week'] : */date('w', time()); // день недели
        $hour           =   /*array_key_exists('hour', $data) ? $data['hour'] : */date('H', time()); // часы

        global $settings;

        $delivery = array(
            'person'        =>  array(//физ лицо
                'warehouse'     =>  array(//свой склад
                    'pay_on_delivery'   =>  array(//оплата при получении
                        'novaposhta'    =>  array(
                            'all'           =>  array(
                                'time'          =>  array(//временной диапазон
                                    0               =>  array(
                                        12              =>  array(
                                            'msg'           =>  'Забрать заказ можно завтра, ' . date("d.m", (time()+86400)) . ' в своем городе после 12.00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        23          =>  array(
                                            'msg'           =>  'Забрать заказ можно послезавтра, ' . date("d.m", (time()+(2*86400))) . ' в своем городе после 12.00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 48 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                    )
                                )
                            ),
                        ),
                        'courier_today'     =>  array(//курьер на сегодня
                            13                  =>  array(//Киев
                                'time'              =>  array(//временной диапазон
                                    0                   =>  array(
                                        16                  =>  array(//до 16 часов
                                            'msg'               =>  'Заказ будет доставлен сегодня '. date("d.m", time()) . '.',
                                            'msg-wait'          =>  'Мы сможем доставить заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'          =>  'Как только появится в наличии.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'courier'           =>  array(//курьер
                            13                  =>  array(//Киев
                                'msg'               =>  'Заказ будет доставлен завтра ' . date("d.m", (time()+86400)) . '.',
                                'msg-wait'          =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления товара на склад.',
                                'msg-none'          =>  'Как только появится в наличии.',
                            ),
                        ),
                        'express'           =>  array(//экспресс
                            13                  =>  array(//Киев
                                'time'              =>  array(//временной диапазон
                                    0                   =>  array(
                                        10                  =>  array(//до 10 часов
                                            //null
                                        ),
                                        16                  =>  array(//до 16 часов
                                            'msg'              =>  'Заказ будет доставлен в течении 2 часов.',
                                            'msg-wait'         =>  'Мы сможем доставить заказ в течении 2 часов с момента поступления товара на склад.',
                                            'msg-none'         =>  'Как только появится в наличии.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'cash'          =>  array(//наличка
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  array(//временной диапазон
                                    0               =>  array(
                                        11              =>  array(//до 11 часов
                                            'day-of-week'   =>  array(1=>'',2=>'',3=>'',4=>'',5=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет сегодня '. date("d.m", time()) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        14              =>  array(//до 14 часов
                                            'day-of-week'   =>  array(1=>'',2=>'',3=>'',4=>'',5=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет сегодня '. date("d.m", time()) . ' после 15:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        17              =>  array(//до 17 часов
                                            'day-of-week'   =>  array(1=>'',2=>'',3=>'',4=>'',5=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет сегодня '. date("d.m", time()) . ' после 18:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        23              =>  array(//до 23 часов
                                            'day-of-week'   =>  array(1=>'',2=>'',3=>'',4=>'',5=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                    ),
                                    1               =>  array(
                                        11              =>  array(//до 11 часов
                                            'day-of-week'   =>  array(6=>'',0=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет сегодня '. date("d.m", time()) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        14              =>  array(//до 14 часов
                                            'day-of-week'   =>  array(6=>'',0=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет сегодня '. date("d.m", time()) . ' после 15:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 12 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                        23              =>  array(//до 23 часов
                                            'day-of-week'   =>  array(6=>'',0=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 12.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления платежа и товара на склад.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            )
                        ),
                    ),
                    'transfer'      =>  array(//перевод
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер и предоставит реквизиты для оплаты.',
                                'msg-wait'     =>  'С Вами свяжется менеджер и предоставит реквизиты для оплаты с момента поступления товара на склад.',
                                'msg-none'     =>  'Как только появится в наличии.',
                            ),
                        ),
                        'novaposhta_cash'=>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 12.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления платежа и товара на склад.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер и предоставит реквизиты для оплаты.',
                                'msg-wait'     =>  'С Вами свяжется менеджер и предоставит реквизиты для оплаты с момента поступления товара на склад.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                        'courier_today'        => array(//курьер
                            13                  =>  array(//Киев
                                'time'              =>  array(//временной диапазон
                                    0                   =>  array(
                                        16                  =>  array(//до 16 часов
                                            'msg'               =>  'В ближайшее время с Вами свяжется менеджер и предоставит реквизиты для оплаты.',
                                            'msg-wait'          =>  'С Вами свяжется менеджер и предоставит реквизиты для оплаты с момента поступления товара на склад.',
                                            'msg-none'      =>  'Как только появится в наличии.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'installment'   =>  array(//рассрочка
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'msg'          =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента получения положительного решения от кредитного отдела(после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                                'msg-wait'     =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента поступления товара на склад и получения положительного решения от кредитного отдела(после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                    ),
                ),
                'supplier'  =>  array(//склад поставщика
                    'pay_on_delivery'   =>  array(//оплата при получении
                        'novaposhta'    =>  array(
                            'all'           =>  array(
                                'time'          =>  array(//временной диапазон
                                    0               =>  array(
                                        10              =>  array(
                                            'msg'           =>  'Забрать заказ можно завтра, ' . date("d.m", (time()+86400)) . ' в своем городе после 12.00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        23          =>  array(
                                            'msg'           =>  'Забрать заказ можно послезавтра, ' . date("d.m", (time()+(2*86400))) . ' в своем городе после 12.00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 48 часов с момента поступления товара на склад.',
                                        ),
                                    )
                                )
                            ),
                        ),
                        'courier'           =>  array(//курьер
                            13                  =>  array(//Киев
                                'time'              =>  array(//временной диапазон
                                    0                   =>  array(
                                        16                  =>  array (
                                            'msg'              =>  'Заказ будет доставлен завтра ' . date("d.m", (time()+86400)) . '.',
                                            'msg-wait'         =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        23                  =>  array (
                                            'msg'              =>  'Заказ будет доставлен послезавтра ' . date("d.m", (time()+2*86400)) . '.',
                                            'msg-wait'         =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'cash'          =>  array(//наличка
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  array(//временной диапазон
                                    0               =>  array(
                                        11              =>  array(//до 11 часов
                                            'day-of-week'   =>  array(5=>'',1=>'',2=>'',3=>'',4=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        14              =>  array(//до 14 часов
                                            'day-of-week'   =>  array(5=>'',1=>'',2=>'',3=>'',4=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 15:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        17              =>  array(//до 17 часов
                                            'day-of-week'   =>  array(5=>'',1=>'',2=>'',3=>'',4=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 18:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        23              =>  array(//до 23 часов
                                            'day-of-week'   =>  array(5=>'',1=>'',2=>'',3=>'',4=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет послезавтра '. date("d.m", (time()+2*86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 48 часов с момента поступления товара на склад.',
                                        ),
                                    ),
                                    1               =>  array(
                                        11              =>  array(//до 11 часов
                                            'day-of-week'   =>  array(0=>'',6=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        14              =>  array(//до 14 часов
                                            'day-of-week'   =>  array(0=>'',6=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет завтра '. date("d.m", (time()+86400)) . ' после 15:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления товара на склад.',
                                        ),
                                        23              =>  array(//до 23 часов
                                            'day-of-week'   =>  array(0=>'',6=>''),//день недели
                                            'msg'           =>  'Забрать заказ можно будет послезавтра '. date("d.m", (time()+2*86400)) . ' после 12:00.',
                                            'msg-wait'      =>  'Вы сможете забрать заказ в течении 48 часов с момента поступления товара на склад.',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 10.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Вы сможете забрать заказ в течении 24 часов с момента поступления платежа и товара на склад.',
                            ),
                        ),
                    ),
                    'transfer'      =>  array(//перевод
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер и предоставит реквизиты для оплаты.',
                                'msg-wait'     =>  'С Вами свяжется менеджер и предоставит реквизиты для оплаты с момента поступления товара на склад.',
                            ),
                        ),
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 10.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления платежа и товара на склад.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер и предоставит реквизиты для оплаты.',
                                'msg-wait'     =>  'С Вами свяжется менеджер и предоставит реквизиты для оплаты с момента поступления товара на склад.',
                            ),
                        ),
                    ),
                    'installment'   =>  array(//рассрочка
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'msg'          =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента получения положительного решения от кредитного отдела(после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                                'msg-wait'     =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента поступления товара на склад и получения положительного решения от кредитного отдела(после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                            ),
                        ),
                    ),
                ),
            ),
            'corporation'   =>array(//юр лицо
                'warehouse'     =>  array(//свой склад
                    'account'       =>  array(//оплата по счету
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 12.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления платежа и товара на склад.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                        'pickup'    =>  array(
                            13              =>  array(
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер.',
                                'msg-wait'     =>  'С момента поступления товара на склад с Вами свяжется менеджер.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                        'courier'    =>  array(
                            13              =>  array(
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер.',
                                'msg-wait'     =>  'С момента поступления товара на склад с Вами свяжется менеджер.',
                                'msg-none'      =>  'Как только появится в наличии.',
                            ),
                        ),
                    ),
                ),
                'supplier'  =>  array(//склад поставщика
                    'account'       =>  array(//оплата по счету
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'msg'           =>  'Если платеж поступит до 10.00, то забрать заказ можно на следующий день в своем городе после 12.00.',
                                'msg-wait'      =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления товара на склад.',
                            ),
                        ),
                        'pickup'    =>  array(
                            13              =>  array(
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер.',
                                'msg-wait'     =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления товара на склад.',
                            ),
                        ),
                        'courier'    =>  array(
                            13              =>  array(
                                'msg'          =>  'В ближайшее время с Вами свяжется менеджер.',
                                'msg-wait'     =>  'Мы сможем доставить заказ в течении 24 часов с момента поступления товара на склад.',
                            ),
                        ),
                    ),
                ),
            ),
        );

        $person     =   $data['person'];
        $warehouse  =   isset($data['warehouse']) ? $data['warehouse'] : 0;
        $payment    =   $data['payment'];
        $shipping   =   $data['shipping'];
        $city       =   ($data['city'] == 13) ? 13 : 'all';

        $p_cost     =   0; // цена доставки
        $p_percent  =   0; // процент от доставки
        $msg        =  ''; // сообщение о доставке
        //$msg_wait   =  ''; // сообщение о доставке (товара в ожидании)
        $s_cost     =   0; // цена оплаты
        $s_percent  =   0; // процент от оплаты
        $ok         =   0; // разрешение на вывод


        /*$shipping_cost = array(
            'novaposhta'    =>  array(
                'persent'       =>  2.5,
                'cost'          =>  160,
            ),
            'courier_today'     =>  array(//курьер на сегодня
                'cost'              =>  431,//центы
            ),
            'express'           =>  array(//экспресс
                'cost'              =>  1232,//центы
            ),
        );
        $payment_cost = array(
            'transfer'      =>  array(//перевод
                'persent'       =>  1,
            ),
        );
        if ( array_key_exists($shipping, $shipping_cost) && array_key_exists('persent', $shipping_cost[$shipping]) && $shipping_cost[$shipping]['persent'] > 0 )
            $s_percent = $shipping_cost[$shipping]['persent'];
        if ( array_key_exists($shipping, $shipping_cost) && array_key_exists('cost', $shipping_cost[$shipping]) && $shipping_cost[$shipping]['cost'] > 0 )
            $s_cost = $shipping_cost[$shipping]['cost'];
        if ( array_key_exists($payment, $payment_cost) && array_key_exists('persent', $payment_cost[$payment]) && $payment_cost[$payment]['persent'] > 0 )
            $p_percent = $payment_cost[$payment]['persent'];
        if ( array_key_exists($payment, $payment_cost) && array_key_exists('cost', $payment_cost[$payment]) && $payment_cost[$payment]['cost'] > 0 )
            $p_cost = $payment_cost[$payment]['cost'];*/

        if (array_key_exists('shipping_cost-' . $shipping, $settings))
            $s_cost = $settings['shipping_cost-' . $shipping];
        if (array_key_exists('shipping_persent-' . $shipping, $settings))
            $s_percent = $settings['shipping_persent-' . $shipping];
        if (array_key_exists('payment_cost-' . $payment, $settings))
            $p_cost = $settings['payment_cost-' . $payment];
        if (array_key_exists('payment_persent-' . $payment, $settings))
            $p_percent = $settings['payment_persent-' . $payment];

        //echo $person . ' ' . $warehouse . ' ' . $payment . ' ' . $shipping . ' ' . $city . '<br />';

        if ( array_key_exists($person,       $delivery) &&
             array_key_exists($warehouse,    $delivery[$person]) &&
             array_key_exists($payment,      $delivery[$person][$warehouse]) &&
             array_key_exists($shipping,     $delivery[$person][$warehouse][$payment]) &&
             array_key_exists($city,         $delivery[$person][$warehouse][$payment][$shipping]) ) {

            $start          =   $delivery[$person][$warehouse][$payment][$shipping][$city];

            if (isset($start['time'])) { // временной диапазон
                foreach ( $start['time'] as $value ) {
                    foreach ( $value as $h=>$v ) {
                        if ( $hour < $h || ($h == 23 && $hour == $h ) ) {
                            if ( isset($v['day-of-week']) ) { // проверка на день недели
                                if ( array_key_exists($day_of_week, $v['day-of-week']) ) {
                                    $ok = 1;
                                    if (array_key_exists('none', $data) && $data['none'] == true) {
                                        if ( isset($v['msg-none']) ) $msg = $v['msg-none']; else $ok = 0;
                                    } elseif (array_key_exists('wait', $data) && $data['wait'] == true) {
                                        if ( isset($v['msg-wait']) ) $msg = $v['msg-wait']; else $ok = 0;
                                    } else {
                                        if ( isset($v['msg']) ) $msg = $v['msg']; else $ok = 0;
                                    }
                                    break;
                                } else {
                                    continue;
                                }
                            } else {
                                $ok = 1;
                                if (array_key_exists('none', $data) && $data['none'] == true) {
                                    if ( isset($v['msg-none']) ) $msg = $v['msg-none']; else $ok = 0;
                                } elseif (array_key_exists('wait', $data) && $data['wait'] == true) {
                                    if ( isset($v['msg-wait']) ) $msg = $v['msg-wait']; else $ok = 0;
                                } else {
                                    if ( isset($v['msg']) ) $msg = $v['msg']; else $ok = 0;
                                }
                                break;
                            }
                        }
                    }
                }
            } else {
                $ok = 1;
                if (array_key_exists('none', $data) && $data['none'] == true) {
                    if ( isset($start['msg-none']) ) $msg = $start['msg-none']; else $ok = 0;
                } elseif (array_key_exists('wait', $data) && $data['wait'] == true) {
                    if ( isset($start['msg-wait']) ) $msg = $start['msg-wait']; else $ok = 0;
                } else {
                    if ( isset($start['msg']) ) $msg = $start['msg']; else $ok = 0;
                }
            }
        }
//exit;

        return array(
            'msg'       =>  $msg,
            //'msg-wait'  =>  $msg_wait,
            'p_cost'    =>  $model->get_prices($p_cost, true),
            'p_percent' =>  $p_percent,
            'ok'        =>  $ok,
            's_percent' =>  $s_percent,
            's_cost'    =>  $model->get_prices($s_cost, true),
        );
    }

    function get_all_price($data, $sum)
    {
        $payment_cost   = array();
        $shipping_cost  = array();
        $all_sum        = $sum;

        foreach ( $sum as $k=>$v ) {
            //if ( $k == 'for_sum' ) continue;

            $payment_cost[$k] = 0;
            $shipping_cost[$k] = 0;

            if ( isset($data['p_percent']) && $data['p_percent'] > 0 ) {
                $payment_cost[$k]   =   $v * $data['p_percent'] / 100;
                $all_sum[$k]        =   $v + $v*$data['p_percent'] / 100;
            }
            if ( isset($data['p_cost']) && $data['p_cost'][$k] > 0 ) {
                $cost = $data['p_cost'][$k];

                if (array_key_exists('rounding-goods', $this->configs) && $this->configs['rounding-goods'] > 0 && $k != 'price')
                    $cost = round(($data['p_cost'][$k]) / $this->configs['rounding-goods']) * $this->configs['rounding-goods'];

                $payment_cost[$k]  +=   $cost;
                $all_sum[$k]       +=   $cost;
            }

            if ( isset($data['s_percent']) && $data['s_percent'] > 0 ) {
                $shipping_cost[$k]  =   $v * $data['s_percent'] / 100;
                $all_sum[$k]        =   $v + $v*$data['s_percent'] / 100;
            }
            if ( isset($data['s_cost']) && $data['s_cost'][$k] > 0 ) {
                $cost = $data['s_cost'][$k];

                if (array_key_exists('rounding-goods', $this->configs) && $this->configs['rounding-goods'] > 0 && $k != 'price')
                    $cost = round(($data['s_cost'][$k]) / $this->configs['rounding-goods']) * $this->configs['rounding-goods'];

                $shipping_cost[$k] +=   $cost;
                $all_sum[$k]       +=   $cost;
            }
        }

        return array(
            'all-sum'       =>  $all_sum,
            'payment-cost'  =>  $payment_cost,
            'shipping-cost' =>  $shipping_cost,
            'ok'            =>  $data['ok'],
        );
    }

    function show_coupons($i_unique = 1)
    {
        // купоны
        $coupons = $this->db->query('SELECT url, image, name FROM {banners} WHERE block=4 AND active=1 ORDER BY prio')->assoc();
        $coupons_html = '<img id="coupon-gift" alt="Start today!" src="' . $this->prefix . 'images/oformiv_zakaz_poluzhaete_v_podarok.png">';
        if ( $coupons ) {
            $coupons_html .= '<ul data-unique="' . $i_unique . '" class="product-board filters_group all_filters mycarousel mycarousel-horizontal jcarousel-skin-tango-coupons">';
            foreach ( $coupons as $coupon ) {
                $coupons_html .= '<li class="coupon product medium" href="' . ($coupon['url']) . '">' .
                    '<a href="' . $this->prefix . 'coupons" title="купон-' . $coupon['name'] . '">' .
                        '<img src="' . $this->prefix . 'images/flayers/' . $coupon['image'] . '" alt="' . $coupon['name'] . '">' .
                        //'<p class="center">' . $coupon['name'] . '</p>'
                        '<div class="coupon-shadow"></div>' .
                    '</a></li>';
            }
            $coupons_html .= '</ul>';
        }
        return $coupons_html;
    }

    function in_wishlist($goods_id)
    {
        if ( isset($_SESSION['user_id']) ) {

            $wishlist = $this->db->query('SELECT id FROM {clients_wishlists} WHERE user_id=?i AND goods_id=?i', array($_SESSION['user_id'], $goods_id))->el();

            if ( $wishlist ) {
                return true;
            }
        }

        return false;
    }
}
