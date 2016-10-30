<?php

function product_exports_form($all_configs)
{
    $url = $all_configs['prefix'] . (isset($all_configs['arrequest'][0]) ? $all_configs['arrequest'][0] . '/' : '') . 'ajax';
    $html = '<form target="_blank" method="get" action="' . $url . '" class="form-horizontal">';
    $html .= '<input name="act" value="exports-goods" type="hidden" />';
    if (isset($_GET['imt'])) {
        $html .= '<input name="imt" value="' . $_GET['imt'] . '" type="hidden" />';
    }
    if (isset($_GET['cats'])) {
        $html .= '<input name="cats" value="' . $_GET['cats'] . '" type="hidden" />';
    }
    if (isset($_GET['filters'])) {
        $html .= '<input name="filters" value="' . $_GET['filters'] . '" type="hidden" />';
    }
    if (isset($_GET['wh'])) {
        $html .= '<input name="wh" value="' . $_GET['wh'] . '" type="hidden" />';
    }
    if (isset($_GET['s'])) {
        $html .= '<input name="s" value="' . $_GET['s'] . '" type="hidden" />';
    }
    if (isset($_GET['show'])) {
        $html .= '<input name="show" value="' . $_GET['show'] . '" type="hidden" />';
    }

    $arr = array(
        array('label' => 'ID', 'name' => 'id'),
        array('label' => lq('Категория'), 'name' => 'categories'),
        array('label' => lq('Наименование'), 'name' => 'title'),
        array('label' => lq('Артикул'), 'name' => 'vendor_code'),
        array('label' => lq('Цена закупки'), 'name' => 'price_purchase'),
        array('label' => lq('Цена оптовая'), 'name' => 'price_wholesale'),
        array('label' => lq('Цена розничная'), 'name' => 'price'),
        array('label' => lq('Свободный остаток'), 'name' => 'qty_store'),
        array('label' => lq('manager'), 'name' => 'managers'),
    );

    $arr_additional = array(
        array('label' => lq('% от прибыли'), 'name' => 'percent_from_profit'),
        array('label' => lq('Фиксированная оплата'), 'name' => 'fixed_payment'),
        array('label' => lq('Уведомлять меня об остатке'), 'name' => 'notify_by_balance'),
        array('label' => lq('Неснижаемый остаток'), 'name' => 'minimum_balance'),
        array('label' => lq('Автонаценка розница'), 'name' => 'automargin'),
        array('label' => lq('В валюте (р)'), 'name' => 'automargin_type'),
        array('label' => lq('Автонаценка опт'), 'name' => 'wholesale_automargin'),
        array('label' => lq('В валюте (o)'), 'name' => 'wholesale_automargin_type'),
    );

    foreach ($arr as $item) {
        $html .= '<div class="form-group"><label>' . $item['label'] . '</label><br>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(0);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-success hide">' . $item['label'] . '</button>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(1);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-danger">' . $item['label'] . '</button>';
        $html .= '<input type="hidden" value="0" name="' . $item['name'] . '" id="export_goods_' . $item['name'] . '"/></div>';
    }

    $html .= '<div class="m-b-md"><a  role="button" data-toggle="collapse" href="#collapseAdditinal" aria-expanded="false" aria-controls="collapseAdditinal"><strong>' . lq('Дополнительные поля') . '</strong> <i class="glyphicon glyphicon-chevron-down"></i></a></div>';

    $html .= '<div class="collapse" id="collapseAdditinal">';
    foreach ($arr_additional as $item) {
        $html .= '<div class="form-group"><label>' . $item['label'] . '</label><br>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(0);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-success hide">' . $item['label'] . '</button>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(1);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-danger">' . $item['label'] . '</button>';
        $html .= '<input type="hidden" value="0" name="' . $item['name'] . '" id="export_goods_' . $item['name'] . '"/></div>';
    }
    $html .= '</div>';

    $html .= '<div class="form-group">';
    $html .= '<input type="submit" value="' . l('Выгрузить данные') . '" class="btn btn-small btn-primary"></div></form>';

    return $html;
}

function exports_goods($all_configs, $ids)
{
    // наличие ключа
    $isset = function ($arr, $key, $n = '') use (&$isset) {
        return isset($arr[trim($key . $n)]) ? $isset($arr, $key, (intval($n) + 1)) : trim($key . $n);
    };

    $goods = $select = array();

    // какие данные нужно
    $select[] = 'DISTINCT g.id as ID';//обязательно
    if (isset($_GET['title']) && $_GET['title'] == 1) {
        $select[] = 'g.title as `' . lq('Наименование') . '`';
    }
    if (isset($_GET['vendor_code']) && $_GET['vendor_code'] == 1) {
        $select[] = 'g.vendor_code as `' . lq('Артикул') . '`';
    }
    if (isset($_GET['url']) && $_GET['url'] == 1) {
        $select[] = 'CONCAT("http://' . $_SERVER['HTTP_HOST'] . $all_configs['siteprefix'] . '", g.url, "/' . $all_configs['configs']['product-page'] . '/", g.id) as `Ссылка на товар`';
    }
    if (isset($_GET['price_purchase']) && $_GET['price_purchase'] == 1) {
        $select[] = 'g.price_purchase/100 as `' . lq('Цена закупки') . '`';
    }
    if (isset($_GET['price_wholesale']) && $_GET['price_wholesale'] == 1) {
        $select[] = 'g.price_wholesale/100 as `' . lq('Цена оптовая') . '`';
    }
    if (isset($_GET['price']) && $_GET['price'] == 1) {
        $select[] = 'g.price/100 as `' . lq('Цена розничная') . '`';
    }
    if (isset($_GET['qty_store']) && $_GET['qty_store'] == 1) {
        $select[] = 'g.qty_store as `' . lq('Свободный остаток') . '`';
    }
    if (isset($_GET['foreign_warehouse']) && $_GET['foreign_warehouse'] == 1) {
        $select[] = 'g.foreign_warehouse as `' . lq('Наличие у поставщика') . '`';
    }
    if (isset($_GET['warranties']) && $_GET['warranties'] == 1) {
        $select[] = 'g.warranties';
    }


    if (isset($_GET['percent_from_profit']) && $_GET['percent_from_profit'] == 1) {
        $select[] = 'g.percent_from_profit as `' . lq('% от прибыли') . '`';
    }

    if (isset($_GET['fixed_payment']) && $_GET['fixed_payment'] == 1) {
        $select[] = 'g.fixed_payment as `' . lq('Фиксированная оплата') . '`';
    }

    if (isset($_GET['use_minimum_balance']) && $_GET['minimum_balance'] == 1) {
        $select[] = 'g.minimum_balance as `' . lq('Неснижаемый остаток') . '`';
    }

    if (isset($_GET['automargin']) && $_GET['automargin'] == 1) {
        $select[] = 'g.automargin as `' . lq('Автонаценка розница') . '`';
    }
    if (isset($_GET['automargin_type']) && $_GET['automargin_type'] == 1) {
        $select[] = 'IF(g.automargin_type=1,"' . lq('Нет') . '","' . lq('Да') . '") as `' . lq('В валюте (р)') . '`';
    }

    if (isset($_GET['wholesale_automargin']) && $_GET['wholesale_automargin'] == 1) {
        $select[] = 'g.wholesale_automargin as `' . lq('Автонаценка опт') . '`';
    }

    if (isset($_GET['wholesale_automargin_type']) && $_GET['wholesale_automargin_type'] == 1) {
        $select[] = 'IF(g.wholesale_automargin_type=1,"' . lq('Нет') . '","' . lq('Да') . '") as `' . lq('В валюте (o)') . '`';
    }

    if (isset($_GET['notify_by_balance']) && $_GET['notify_by_balance'] == 1) {
        $select[] = 'un.balance as `' . lq('Уведомлять меня об остатке') . '`';
    }


    if (is_array($ids) && count($ids) > 0) {

        // достаем товары
        if (count($select) > 0) {
            $goods = $all_configs['db']->query('SELECT ?query FROM {goods} as g LEFT JOIN {users_notices} as un ON un.goods_id=g.id AND user_id=' . $_SESSION['id'] . ' WHERE g.id IN (?li) AND g.avail=?i',
                array(implode(', ', $select), array_keys($ids), 1))->assoc('ID');
            // удаляем ид
            if (!isset($_GET['id']) || $_GET['id'] != 1) {
                array_walk($goods, function (&$v) {
                    unset($v['id']);
                });
            }
        }

        // гарантия
        if (count($goods) > 0 && isset($_GET['warranties']) && $_GET['warranties'] == 1) {
            foreach ($goods as $k => $product) {
                $product['warranties'] = (array)@unserialize($product['warranties']);
                unset($goods[$k]['warranties']);
                foreach ($all_configs['configs']['warranties'] as $m => $w) {
                    $goods[$k]['Гарантия' . $m . 'м'] = array_key_exists($m, $product['warranties']) ? $m : 0;
                }
            }
        }

        // картинка
        if (count($goods) > 0 && isset($_GET['image']) && $_GET['image'] == 1) {
            $images = $all_configs['db']->query('SELECT DISTINCT i.id, i.goods_id, i.image FROM {goods_images} as i
                  WHERE i.goods_id IN (?li) AND i.type=?i ORDER by i.prio DESC',
                array(array_keys($ids), 1))->assoc('goods_id');// одна на товар
            if ($images) {
                foreach ($images as $image) {
                    if (isset($goods[$image['goods_id']])) {
                        $url = 'http://' . $_SERVER['HTTP_HOST'] . $all_configs['siteprefix'] . $all_configs['configs']['goods-images-path'] . $image['goods_id'] . '/';
                        $goods[$image['goods_id']][$isset($goods[$image['goods_id']],
                            'Фото ')] = $url . rawurlencode($image['image']);
                    }
                }
            }
        }

        // менеджеры
        if (count($goods) > 0 && isset($_GET['managers']) && $_GET['managers'] == 1) {
            $managers = $all_configs['db']->query('SELECT DISTINCT m.id, u.login, u.email, u.fio, u.phone, m.goods_id
                  FROM {users_goods_manager} as m, {users} as u WHERE m.goods_id IN (?li) AND m.user_id=u.id',
                array(array_keys($ids)))->assoc('goods_id');// один на товар
            if ($managers) {
                foreach ($managers as $manager) {
                    if (isset($goods[$manager['goods_id']])) {
                        $goods[$manager['goods_id']][$isset($goods[$manager['goods_id']],
                            lq('manager') . ' ')] = empty($manager['fio']) ? $manager['login'] : $manager['fio'];
                    }
                }
            }
        }

        // категории
        if (count($goods) > 0 && isset($_GET['categories']) && $_GET['categories'] == 1) {
            $categories = $all_configs['db']->query('SELECT DISTINCT g.id, g.goods_id, cg.title, g.category_id
                  FROM {category_goods} as g, {categories} as cg WHERE g.goods_id IN (?li) AND g.category_id=cg.id AND cg.avail=?i',
                array(array_keys($ids), 1))->assoc();
            if ($categories) {
                foreach ($categories as $category) {
                    if (isset($goods[$category['goods_id']])) {
                        $goods[$category['goods_id']][$isset($goods[$category['goods_id']],
                            lq('Категория '))] = $category['title'];
                    }
                }
            }
        }
    }

    include_once $all_configs['sitepath'] . 'shop/exports.class.php';
    $exports = new Exports();
    $exports->build($goods);
}