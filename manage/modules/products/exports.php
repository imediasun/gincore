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
        array('label' => 'Категория', 'name' => 'categories'),
        array('label' => 'Наименование', 'name' => 'title'),
//        array('label' => 'Ссылка на товар', 'name' => 'url'),
//        array('label' => 'Фото', 'name' => 'image'),
        array('label' => 'Цена закупки', 'name' => 'price_purchase'),
        array('label' => 'Цена оптовая', 'name' => 'price_wholesale'),
        array('label' => 'Цена розничная', 'name' => 'price'),
        array('label' => 'Свободный остаток', 'name' => 'qty_store'),
//        array('label' => 'Наличие у поставщика', 'name' => 'foreign_warehouse'),
        array('label' => l('manager'), 'name' => 'managers'),
    );

    foreach ($arr as $item) {
        $html .= '<div class="form-group"><label>' . $item['label'] . '</label><br>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(0);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-success hide">' . $item['label'] . '</button>';
        $onclick = 'onclick="$(\'#export_goods_' . $item['name'] . '\').val(1);$(this).parent().find(\'button\').removeClass(\'hide\');$(this).addClass(\'hide\');"';
        $html .= '<button type="button" ' . $onclick . ' class="btn btn-small btn-danger">' . $item['label'] . '</button>';
        $html .= '<input type="hidden" value="0" name="' . $item['name'] . '" id="export_goods_' . $item['name'] . '"/></div>';
    }

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
    //if (isset($_GET['id']) && $_GET['id'] == 1)
    $select[] = 'DISTINCT g.id';//обязательно
    if (isset($_GET['title']) && $_GET['title'] == 1) {
        $select[] = 'g.title as `Наименование`';
    }
    if (isset($_GET['url']) && $_GET['url'] == 1) {
        $select[] = 'CONCAT("http://' . $_SERVER['HTTP_HOST'] . $all_configs['siteprefix'] . '", g.url, "/' . $all_configs['configs']['product-page'] . '/", g.id) as `Ссылка на товар`';
    }
    if (isset($_GET['price_purchase']) && $_GET['price_purchase'] == 1) {
        $select[] = 'g.price_purchase/100 as `Цена закупки`';
    }
    if (isset($_GET['price_wholesale']) && $_GET['price_wholesale'] == 1) {
        $select[] = 'g.price_wholesale/100 as `Цена оптовая`';
    }
    if (isset($_GET['price']) && $_GET['price'] == 1) {
        $select[] = 'g.price/100 as `Цена розничная`';
    }
    if (isset($_GET['qty_store']) && $_GET['qty_store'] == 1) {
        $select[] = 'g.qty_store as `Свободный остаток`';
    }
    if (isset($_GET['foreign_warehouse']) && $_GET['foreign_warehouse'] == 1) {
        $select[] = 'g.foreign_warehouse as `Наличие у поставщика`';
    }
    if (isset($_GET['warranties']) && $_GET['warranties'] == 1) {
        $select[] = 'g.warranties';
    }

    if (is_array($ids) && count($ids) > 0) {

        // достаем товары
        if (count($select) > 0) {
            $goods = $all_configs['db']->query('SELECT ?query FROM {goods} as g WHERE g.id IN (?li) AND g.avail=?i',
                array(implode(', ', $select), array_keys($ids), 1))->assoc('id');
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
                        $arr = array($manager['login'], $manager['email'], $manager['fio'], $manager['phone']);
                        $goods[$manager['goods_id']][$isset($goods[$manager['goods_id']],
                            l('manager') . ' ')] = implode(', ', $arr);
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
                            'Категория ')] = $category['title'];
                    }
                }
            }
        }
    }

    include_once $all_configs['sitepath'] . 'shop/exports.class.php';
    $exports = new Exports();
    $exports->build($goods);
}