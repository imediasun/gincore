<?php

class Model
{

    protected $db;
    protected $configs;

    public function __construct()
    {
        global $db, $sitepath;
        $this->db = $db;

        $this->configs = Configs::getInstance()->get();

    }

    public function get_goods_filters($url, $sort = null, $like = null)
    {
        if ($url == 'all') {
            $this_sql = '';
        } elseif (is_null($like)) {
            $this_sql = $this->db->makeQuery('{category_goods}.category_id=(SELECT id FROM {categories} WHERE url=? AND avail=1) AND',
                array($url));
        } else {
            $this_sql = $this->db->makeQuery('{category_goods}.category_id IN (SELECT id FROM {categories} WHERE parent_id=(SELECT id FROM {categories} WHERE url=? AND avail=1)) AND',
                array($url));
        }

        $sort = $this->sorting($sort);

        $array = array($this_sql);
        if (!is_null($like)) {
            $array = array($this_sql, $like);
            $like = ' AND {goods}.title LIKE "%?e%" ';
        }

        $sql = $this->db->makeQuery(
            'SELECT {goods}.`id`, {goods}.`rating`, {goods}.`url`, {goods}.`title`, {goods}.`content`, {goods}.`price`, {goods}.`date_add`, {goods}.`prio`, {goods}.`action`,
                {goods}.`wait`, {goods}.`exist`, v.id AS fname_id, v.value, v.id AS fvalue_id, n.title AS ftitle, n.id AS fname_id,
                {goods_images}.image, {goods_images}.title as image_title, n.prio as fname_prio, {goods}.foreign_warehouse
            FROM {goods_images}, {category_goods}

            INNER JOIN (
            SELECT `id`, `rating`, `url`, `title`, `content`, `price`, `wait`, `exist`, `date_add`, `prio`, `avail`, foreign_warehouse, `action` FROM {goods} #LIMIT 0, 2
            ){goods} ON {goods}.id = {category_goods}.goods_id AND {goods}.avail=1

            LEFT JOIN (
            SELECT goods_id, filter_id FROM {goods_filter}
            )lf ON {goods}.id = lf.goods_id

            LEFT JOIN (
            SELECT value, id FROM {filter_value}
            )v ON v.id = lf.filter_id

            LEFT JOIN (
            SELECT fname_id, fvalue_id FROM {filter_name_value}
            )ln ON ln.fvalue_id = v.id
            LEFT JOIN (

            SELECT id, title, prio FROM {filter_name}
            )n ON n.id = ln.fname_id
            WHERE ?query
            {goods_images}.goods_id={goods}.id AND {goods_images}.type=1' . $like . $sort[0] . ' , {goods_images}.prio DESC',
            $array
        );

        $sql = $this->db->plainQuery($sql);
        $goods = $sql->assoc();
        return array($goods, $sort[1]);
    }

    public function sorting($sort)
    {

        $select_sort = '';

        switch ($sort) {
            case 'exist':
                $sorting = ' ORDER BY if({goods}.exist>0,1,0) DESC';
                $select_sort = 'exist';
                break;
            case 'title':
                $sorting = ' ORDER BY {goods}.title';
                $select_sort = 'title';
                break;
            case 'rtitle':
                $sorting = ' ORDER BY {goods}.title DESC';
                $select_sort = 'rtitle';
                break;
            case 'price':
                $sorting = ' ORDER BY {goods}.price';
                $select_sort = 'price';
                break;
            case 'rprice':
                $sorting = ' ORDER BY {goods}.price DESC';
                $select_sort = 'rprice';
                break;
            case 'date':
                $sorting = ' ORDER BY {goods}.date_add';
                $select_sort = 'date';
                break;
            case 'rdate':
                $sorting = ' ORDER BY {goods}.date_add DESC';
                $select_sort = 'rdate';
                break;
            default:
                $sorting = ' ORDER BY if({goods}.exist>0,1,0) DESC, {goods}.prio DESC';
        }
        return array(0 => $sorting, 1 => $select_sort);
    }

    public function get_single_goods($id, $avail = null, $all = null)
    {

        if (!$avail) {
            $avail = ' AND {goods}.avail=1';
            $join = 'INNER';
        } else {
            $avail = '';
            $join = 'LEFT';
        }
        $goods_info = '{goods}.id, {goods}.url, {goods}.title, {goods}.content, {goods}.price, {goods}.rating, {goods}.wait, {goods}.no_warranties,
            {goods}.exist, {goods}.`trade-in`, {goods}.related, {goods}.warranties, {goods}.article, {goods}.action, {goods}.foreign_warehouse,';
        if ($all) {
            $goods_info = '{goods}.id, {goods}.rating, {goods}.url, {goods}.title, {goods}.content, {goods}.price, {goods}.date_add,
                {goods}.article, {goods}.warranties, {goods}.`trade-in`, {goods}.related, {goods}.action, {goods}.votes,
                {goods}.`type`, {goods}.`secret_title`, {goods}.`code_1c`, {goods}.`material`, {goods}.`weight`, {goods}.`barcode`,
                {goods}.`prio`, {goods}.`section`, {goods}.`avail`, {goods}.wait, {goods}.exist, {goods}.price_purchase, {goods}.no_warranties,
                {goods}.`price_wholesale`, {goods}.`foreign_warehouse`,';
        }

        $goods = $this->db->query('
            SELECT ' . $goods_info . ' v.value, n.title as ftitle, c.category_id, ln.*, lf.id as goods_filter_id,
                i.title as ititle, i.image, i.id as iid
            FROM {goods}
            LEFT JOIN (
            SELECT goods_id, filter_id,id id FROM {goods_filter}
            )lf ON {goods}.id = lf.goods_id
            LEFT JOIN (
            SELECT value, id FROM {filter_value}
            )v ON v.id = lf.filter_id
            LEFT JOIN (
            SELECT fname_id, fvalue_id FROM {filter_name_value}
            )ln ON ln.fvalue_id = v.id
            LEFT JOIN (
            SELECT id, title FROM {filter_name}
            )n ON n.id = ln.fname_id
            LEFT JOIN (
            SELECT goods_id, category_id FROM {category_goods}
            )c ON c.goods_id={goods}.id
            ' . $join . ' JOIN (
            SELECT prio, type, title, goods_id, image, id FROM {goods_images}
            )i ON {goods}.id = i.goods_id AND i.type = 1

            WHERE {goods}.id=?i ' . $avail . ' ORDER BY i.prio
            ', array(intval($id)))->assoc();

        return $goods;
    }

    public function get_all_filters_by_goods_ids($ids)
    {
        if (count($ids) < 1) {
            return array();
        }

        $filters = $this->db->query('
            SELECT {filter_name}.id AS fname_id, {filter_name}.title, {filter_value}.id AS fvalue_id,
              {filter_value}.value, {filter_name}.type, {filter_category}.category_id
            FROM {filter_category}, {filter_name}, {filter_value}, {filter_name_value}
            WHERE {filter_category}.category_id IN (?li)
            AND {filter_category}.fname_id = {filter_name}.id
            AND {filter_name}.id = {filter_name_value}.fname_id
            AND {filter_name_value}.fvalue_id = {filter_value}.id',
            array($ids)
        )->assoc();

        return $filters;
    }

    public function get_goods($ids, $avail = '', $limit = '', $order = '', $rand = '', $type = '')
    {
        $where = '';
        //$order = '';
        if ($rand) {
            $rand = ' i.prio, RAND(), ';
        }
        if ($type) {
            $type = ' AND {goods}.type<>1 ';
        }
        if (!$avail && !empty($ids)) {
            $avail = ' AND {goods}.avail=1';
            $join = 'RIGHT';
        } else {
            if (!$avail && empty($ids)) {
                $avail = ' WHERE {goods}.avail=1' . $type;
                $join = 'RIGHT';
            } else {
                if ($avail) {
                    $avail = '';
                    $join = 'LEFT';
                }
            }
        }

        if (!empty($ids)) {
            $where = 'WHERE {goods}.id IN (?li)';
            $order = ' ORDER BY if({goods}.exist>0,1,0) DESC, ' . $rand . ' field({goods}.id, ?li), i.prio';
            $ids = array(array_keys($ids), array_keys($ids));
        } else {
            $ids = array();
            if (empty($order)) {
                $order = ' ORDER BY if({goods}.exist>0,1,0) DESC, {goods}.title, {goods}.prio';
            } else {
                $order = ' ORDER BY if({goods}.exist>0,1,0) DESC, {goods}.' . $order . ' DESC';
            }
        }

        if (intval($limit) > 0) {
            $limit = ' LIMIT 0, ' . $limit;
        }


        $sql = $this->db->query(
            "SELECT {goods}.id, {goods}.rating, {goods}.url, {goods}.title, {goods}.content, {goods}.price, {goods}.date_add, {goods}.`type`,{goods}.`section`, {goods}.`action`,
                {goods}.wait, {goods}.foreign_warehouse, {goods}.exist, i.title as image_title, i.image, i.prio as iprio, gc.category_id
            FROM {goods}
            " . $join . " JOIN (
              SELECT title, image, goods_id, type, prio FROM {goods_images} ORDER BY prio
            )i ON i.goods_id={goods}.id AND i.type=1
            LEFT JOIN (SELECT goods_id, category_id FROM {category_goods})gc ON gc.goods_id={goods}.id
            " . $where . $avail . $order . $limit, $ids

        );

        $goods = $sql->assoc();
        return $goods;
    }

    public function show_sort_block($sort, $field = '')
    {
        global $prefix, $arrequest;

        $sort_values = array(
            'price' => 'цене',
            'rprice' => 'убыванию цены',
            'date' => 'дате',
            'exist' => 'наличию'
        );

        $sort_list = '';
        $selected_value = '';
        foreach ($sort_values as $key => $value) {
            $active = ($sort == $key);
            if ($active) {
                $selected_value = $value;
            } else {
                $sort_list .= '<li' . ($active ? ' class="active"' : '') . '>' .
                    '<a href="' . $prefix . implode('/',
                        $arrequest) . '?sort=' . $key . $field . '">' . $value . '</a>' .
                    '</li>';
            }
        }

        $sort_block = '
            <div class="ground-gray">
                <span class="text-bold">Фильтры для выбора</span>
                <div class="text-right">
                    
                    <div class="info-block">
                        <div class="text-left">Сортировать по:</div>
                        <div class="my_select_mini">
                            <span data-id="1"><span>' . ($selected_value ?: 'выбрать') . '</span> ▼</span>
                            <div class="my_select_mini_body" data-id="1">
                                <div class="msm_selected"><span>' . ($selected_value ?: 'выбрать') . '</span> ▼</div>
                                <div class="msm_list">
                                    <ul>' . $sort_list . '</ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <span class="info-block">' .
            $this->show_currency() . '
                    </span>
                    
                    <span class="info-block">' .
            $this->show_rate() . '
                    </span>
                </div>
            </div>
        ';

        return $sort_block;
    }

    public function show_rate()
    {
        global $settings, $cfg;

        $user_currency = isset($_COOKIE[$cfg['tbl'] . $this->configs['course']]) ? $_COOKIE[$cfg['tbl'] . $this->configs['course']] : '';

        $course = array(
            'grn-cash' => $settings['grn-cash'] . ' нал',
            'grn-vat' => $settings['grn-vat'] . ' ндс',
            'grn-noncash' => $settings['grn-noncash'] . ' б/н'
        );

        $currency_list = '';
        $selected_value = '';
        foreach ($course as $key => $value) {
            $active = false;
            if ($user_currency == $key || (!$user_currency && $key == $this->configs['default-course'])) {
                $active = true;
                $selected_value = $value;
            }
            $currency_list .= '<li' . ($active ? ' class="msm_hidden"' : '') . '><span onClick="course_change(\'' . $key . '\');">' . $value . '</span></li>';
        }

        return '
            <div class="text-left">Курс:</div>
            <div class="my_select_mini" data-autoselect_value="true">
                <span data-id="3"><span>' . $selected_value . '</span> ▼</span>
                <div class="my_select_mini_body" data-id="3">
                    <div class="msm_selected"><span>' . $selected_value . '</span> ▼</div>
                    <div class="msm_list">
                        <ul>' . $currency_list . '</ul>
                    </div>
                </div>
            </div>';
    }

    public function show_currency()
    {
        global $cfg;

        $user_currency = isset($_COOKIE[$cfg['tbl'] . $this->configs['currency']]) ? $_COOKIE[$cfg['tbl'] . $this->configs['currency']] : '';

        $currency_values = array(
            'grn-cash' => 'гривна',
            'dollar' => 'доллар'
        );

        $currency_list = '';
        $selected_value = '';
        foreach ($currency_values as $key => $value) {
            $active = false;
            if ($user_currency == $key || (!$user_currency && $key == $this->configs['default-currency'])) {
                $active = true;
                $selected_value = $value;
            }
            $currency_list .= '<li' . ($active ? ' class="msm_hidden"' : '') . '><span onClick="currency_change(\'' . $key . '\');">' . $value . '</span></li>';
        }
        $currency_select = '
            <div class="text-left">Ваша валюта:</div>
            <div class="my_select_mini" data-autoselect_value="true">
                <span data-id="2"><span>' . $selected_value . '</span> ▼</span>
                <div class="my_select_mini_body" data-id="2">
                    <div class="msm_selected"><span>' . $selected_value . '</span> ▼</div>
                    <div class="msm_list">
                        <ul>' . $currency_list . '</ul>
                    </div>
                </div>
            </div>
        ';

        return $currency_select;
    }

    function show_warranties($goods, $selected = null, $in_cart = false, $check_in_cart = false)
    {
        $warranties_html = '';
        $warranty_cost = 0;
        if ($check_in_cart) {
            $cart = new Cart($this->db);
            $exists_in_cart = array();
        }
        if ($goods['no_warranties'] == 0) {
            $warranties = $this->configs['warranties'];
            if (is_array(unserialize($goods['warranties'])) && count(unserialize($goods['warranties'])) > 0) {
                $warranties = unserialize($goods['warranties']);
            }

            foreach ($warranties as $warranty => $on) {
                if (array_key_exists($warranty, $this->configs['warranties'])) {
                    foreach ($this->configs['warranties'][$warranty] as $to => $p) {
                        if (intval($goods['price']) <= intval($to) || $to == 'inf') {

                            $class = '';
                            if (empty($warranties_html)) {
                                $p = 0;
                                if (!$selected) {
                                    $class = 'warranties-border';
                                    if ($in_cart) {
                                        $warranty_cost = $p;
                                    } else {
                                        $warranty_cost = $warranty;
                                    }
                                }
                            }

                            if ($selected && $selected == $warranty) {
                                if ($in_cart) {
                                    $warranty_cost = $p;
                                } else {
                                    $warranty_cost = $warranty;
                                }
                                $class = ' warranties-border';
                            }

                            if ($in_cart) {
                                $data = 'data-cart_id="' . $goods['cart_id'] . '" data-warranty="' . $warranty . '"';
                            } else {
                                $data = 'data="' . $warranty . '"';
                            }

                            $already_in_shopping_cart = false;
                            if ($check_in_cart) {
                                $already_in_shopping_cart = $cart->already_in_shopping_cart($goods['id'], $warranty);
                                $exists_in_cart[$warranty] = $already_in_shopping_cart;
                            }

                            $warranties_html .= '
                                <li ' . $data . ' data-in_sc="' . ($already_in_shopping_cart ? 1 : 0) . '" id="warranty-' . $warranty . '" class="text-left warranties center ' . $class . '">
                                    <div class="warranty_ico"><span>' . $warranty . '</span></div>
                                    ' . (!$in_cart ? '<div class="warranty_value">Гарантия ' . $warranty . ' ' . l('мес') . '</div>' : '') . '
                                    <div class="warranty_price red-title">' . $this->cur_currency($p) . '</div>
                                </li>';

                            break;
                        }
                    }
                }
            }
        }
        $return_data = array('html' => $warranties_html, 'w' => $warranty_cost);
        if ($check_in_cart) {
            $return_data['in_cart'] = $exists_in_cart;
        }
        return $return_data;
    }

    public function get_all_categories($avail = '')
    {
        if (!$avail) {
            $avail = ' WHERE avail=1 ';
        } else {
            $avail = '';
        }

        return $this->db->query("SELECT id, title, parent_id, url, image, thumbs, content FROM {categories} " .
            $avail . " ORDER BY prio ")->assoc();
    }

    public function get_category($cat_id, $prefix, $cat_page, $crumbs_arrow, $begin = null, $product = null)
    {
        $str = '';
        if (!$begin) {
            $begin = $cat_id;
        }

        $category = $this->db->query('SELECT id,url,parent_id,title FROM {categories} WHERE id=?i',
            array($cat_id))->row();

        if ($category['parent_id'] > 0) {
            $str = $this->get_category($category['parent_id'], $prefix, $cat_page, $crumbs_arrow, $begin,
                    $product) . $crumbs_arrow;
        }
        if (($category['id'] == $begin || $category['parent_id'] < 1) && !$product) {
            $str .= '<td>' . $category['title'] . '</td>';
        } else {
            $str .= '<td><a href="' . $prefix . $category['url'] . '/' . $cat_page . '">' . $category['title'] . '</a></td>';
        }

        return $str;
    }

    public function bread_crumbs($cat_id, $prefix, $cat_page, $product = null)
    {
        $crumbs_arrow = '<td class="crumbs_arrow"><img src="' . $prefix . 'images/bg_crumbs_arr.gif" alt="&raquo;"></td>';
        $bread_crumbs = '<td><a href="' . $prefix . '"><img src="' . $prefix . 'images/home.gif" alt="home"></a></td>' . $crumbs_arrow;
        $bread_crumbs .= $this->get_category($cat_id, $prefix, $cat_page, $crumbs_arrow, $product);

        if ($product) {
            $bread_crumbs .= $crumbs_arrow . '<td>' . $product . '</td>';
        }

        return $bread_crumbs;
    }

    function createTree(&$list, $parent)
    {

        $tree = array();
        foreach ($parent as $k => $l) {
            if (isset($list[$l['id']])) {
                $l['child'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

    function specifications($goods)
    {
        $a = array();
        foreach ($goods as $good) {
            if (array_key_exists($good['fname_id'], $a)) {
                $a[$good['fname_id']]['child'] = array($good['fvalue_id'] => $good['value']);
            } else {
                $a[$good['fname_id']] = array(
                    'title' => $good['ftitle'],
                    'child' => array($good['fvalue_id'] => $good['value'])
                );
            }
        }
        return $a;
    }

    function similar_goods($id, $ids = '', $avail = '')
    {
        if (is_array($ids) > 0) {
            $sql2 = '(g.id IN (?li) OR g.id=s.first OR g.id=s.second) AND g.id <> ' . $id;
            $sql1 = 's.second IN (?li) OR s.first IN (?li)';
            $array = array($ids, $ids, $ids);
        } else {
            $sql2 = '(g.id=s.first or g.id=s.second) AND g.id<>?i';
            $sql1 = 's.second=?i OR s.first=?i';
            $array = array($id, $id, $id);
        }

        if (!$avail) {
            $avail = ' AND g.avail=1 ';
            $join = 'RIGHT';
        } else {
            $join = 'LEFT';
            $avail = '';
        }

        $similar = $this->db->query('
            SELECT g.id, g.rating, g.url, g.title, g.content, g.price, g.wait, g.exist, i.title as image_title, i.image,
                i.prio as iprio, s.first_prio, s.second_prio, s.first, s.second, g.section, g.warranties, g.action, g.foreign_warehouse
            FROM {goods} as g

            LEFT OUTER JOIN (
            SELECT first_prio,second_prio,first,second FROM {goods_similar}
            )s ON ' . $sql1 . '

            ' . $join . ' JOIN (
            SELECT title, image, goods_id, type, prio FROM {goods_images} ORDER BY prio
            )i ON i.goods_id=g.id AND i.type=1

            WHERE ' . $sql2 . $avail . ' GROUP BY g.id ORDER BY g.section, s.second, s.first',
            $array)->assoc();

        return $similar;
    }

    function get_prices($price, $course_key = false, $course_value = 0)
    {
        global $settings, $db;

        if (!$settings || !isset($settings['grn-cash'])) {
            $settings['grn-cash'] = $db->query('SELECT value FROM {settings} WHERE name="grn-cash"')->el();
        }
        if (!$settings || !isset($settings['grn-vat'])) {
            $settings['grn-vat'] = $db->query('SELECT value FROM {settings} WHERE name="grn-vat"')->el();
        }
        if (!$settings || !isset($settings['grn-noncash'])) {
            $settings['grn-noncash'] = $db->query('SELECT value FROM {settings} WHERE name="grn-noncash"')->el();
        }

        if ($course_key && $course_value > 0) {
            $settings[$course_key] = $course_value;
        }

        $prices = array();

        if ($this->configs['rounding-goods'] == true) {
            $prices['grn-cash'] = round(($price * $settings['grn-cash']) / 5) * 5;
            $prices['grn-vat'] = round(($price * $settings['grn-vat']) / 5) * 5;
            $prices['grn-noncash'] = round(($price * $settings['grn-noncash']) / 5) * 5;
            $prices['price'] = round($price / 5) * 5;
        } else {
            $prices['grn-cash'] = round($price * $settings['grn-cash'], 2);
            $prices['grn-vat'] = round(($price * $settings['grn-vat']), 2);
            $prices['grn-noncash'] = round(($price * $settings['grn-noncash']), 2);
            $prices['price'] = $price;
        }

        if ($course_key && array_key_exists($course_key, $prices)) {
            return $prices[$course_key];
        } else {
            return $prices;
        }
    }

    function currency_view($data)
    {
        return
            '<span class="for-currency-hide dollar"><span data="dollar" ' . $data['for_sum'] . '>' . number_format($data['price'],
                0, ',', ' ') . '</span> $ </span>' .
            '<span class="for-currency-hide grn-cash"><span data="grn-cash" ' . $data['for_sum'] . '>' . number_format($data['grn-cash'],
                0, ',', ' ') . '</span> ' . l('грн') . '. </span>' .
            '<span class="for-currency-hide grn-vat"><span data="grn-vat" ' . $data['for_sum'] . '>' . number_format($data['grn-vat'],
                0, ',', ' ') . '</span> ' . l('грн') . '. </span>' .
            '<span class="for-currency-hide grn-noncash"><span data="grn-noncash" ' . $data['for_sum'] . '>' . number_format($data['grn-noncash'],
                0, ',', ' ') . '</span> ' . l('грн') . '. </span>';
    }

    function show_price($price, $course_value, $course_key)
    {
        $price = $this->get_prices($price, $course_key, $course_value);

        return number_format($price, 0, ',', ' ') . ' ' . l('грн.');
    }

    function cur_currency($price, $sum = '', $count = 1, $prices = null)
    {

        $for_sum = '';

        if (is_null($prices)) {
            $prices = $this->get_prices($price);
        }

        $price = $prices['price'];
        $grn_cash = $prices['grn-cash'];
        $grn_vat = $prices['grn-vat'];
        $grn_noncash = $prices['grn-noncash'];

        if ($sum === 1) {
            $for_sum = 'class="all-amount"';
        }

        return $this->currency_view(array(
            'for_sum' => $for_sum,
            'price' => ($price * $count),
            'grn-cash' => ($grn_cash * $count),
            'grn-vat' => ($grn_vat * $count),
            'grn-noncash' => ($grn_noncash * $count)
        ));
    }

    function get_product_exists_qty($product = null)
    {
        if (is_null($product)) {
            // todo if necessary
            return 0;
        }
        $exists_qty = $product['exist'];

        if ($product['foreign_warehouse'] == 1 || (!$exists_qty && strtotime($product['wait']) > time())) {
            $exists_qty = $this->configs['waiting-goods-count'];
        }

        return $exists_qty;
    }

    function show_select_count($exist, $count_goods = 1, $gid = '', $view = false, $my_select_id = 4, $onclick = false)
    {

        if (intval($exist) > 0) {
            $count = '';
            $selected_count = '1 ' . l('шт.') . '';
            if ($count_goods > $exist) {
                $count_goods = 1;
            }
            for ($i = 1; $i <= $exist; $i++) {
                if ($i > 10) {
                    break;
                }
                if ($count_goods == $i) {
                    if (!$view) {
                        $count .= '<option selected value="' . $i . '">' . $i . '</option>';
                    } else {
                        $selected_count = $i . ' ' . l('шт.') . '';
                        if (!$onclick) {
                            $count .= '<li class="msm_hidden" data-value="' . $i . '"><span>' . $i . ' ' . l('шт.') . '</span></li>';
                        } else {
                            $count .= '<li onclick="sc_change_goods_count(' . $i . ', ' . $gid . ')" class="msm_hidden" data-value="' . $i . '"><span>' . $i . ' ' . l('шт.') . '</span></li>';
                        }
                    }
                } else {
                    if (!$view) {
                        $count .= '<option value="' . $i . '">' . $i . '</option>';
                    } else {
                        if ($onclick == false) {
                            $count .= '<li data-value="' . $i . '"><span>' . $i . ' ' . l('шт.') . '</span></li>';
                        } else {
                            $count .= '<li onclick="sc_change_goods_count(' . $i . ', ' . $gid . ')" data-value="' . $i . '">
                                <span>' . $i . ' ' . l('шт.') . '</span></li>';
                        }
                    }
                }
            }
        }

        $count_html = '<input type="hidden" value="' . $count_goods . '" class="for-count-goods" />';
        if (!$view) {
            $count_html .=
                '<select name="select-count[' . $gid . ']" class="count-goods">' .
                //'<option value="">0</option>'.
                $count .
                '</select>';
        } else {
            $count_html .=
                '<div class="my_select_mini count-goods" data-autoselect_value="true">
                    <span data-id="' . $my_select_id . '"><span>' . $selected_count . '</span> ▼</span>
                    <div class="my_select_mini_body" data-id="' . $my_select_id . '">
                        <div class="msm_selected"><span>' . $selected_count . '</span> ▼</div>
                        <div class="msm_list">
                            <ul>' . $count . '</ul>
                        </div>
                    </div>
                </div>';
        }

        return $count_html;
    }


    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', '.'), $data);
        return $data;
    }

    function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-', '_', '.'), array('+', '/', '='), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    function show_rating($float, $new = null)
    {
        $star = '';
        $rand = $this->generateRandomString(10);
        if ($float == 0 || fmod(5, $float) == 0) {
            for ($i = 1; $i <= 5; $i++) {
                $disabled = 'disabled="disabled"';
                $name = 'star_' . $rand;

                if ($new == 1) {
                    $disabled = '';
                    $name = 'star';
                }

                if ($float == $i) {
                    $star .= '<input value="' . $i . '" name="' . $name . '" type="radio" class="star" checked="checked" ' . $disabled . '/>';
                } else {
                    $star .= '<input value="' . $i . '" name="' . $name . '" type="radio" class="star" ' . $disabled . '/>';
                }
            }
        } else {
            $max_count = 20;
            $checked = round($max_count / (5 / $float));
            for ($i = 1; $i <= $max_count; $i++) {
                if ($checked == $i) {
                    $star .= '<input value="' . $i . '" name="star_' . $rand . '" type="radio" class="star {split:4}" checked="checked" disabled="disabled" />';
                } else {
                    $star .= '<input value="' . $i . '" name="star_' . $rand . '" type="radio" class="star {split:4}" disabled="disabled" />';
                }
            }
        }

        return '<div class="srtgs">' . $star . '</div>';
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz_-';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    // селектим жалобы и предложения
    function get_shop_comments($start = 0, $limit = 5, $positive = false)
    {
        global $db;

        $reviews = $db->query('SELECT r.text, c.fio, r.become_status, r.status, r.date FROM {reviews} as r
                               LEFT JOIN (SELECT `fio`, `id` FROM {clients})c ON c.id=r.user_id
                               WHERE r.avail=1 AND r.shop=1 ' . ($positive ? 'AND r.status IN(1,2)' : '') . ' 
                               ORDER BY date DESC LIMIT ?i,?i', array($start, $limit))->assoc();

        $reviews_html = '';

        if ($reviews) {
            foreach ($reviews as $val) {
                if (!empty($val['fio'])) {
                    $become_status = '';
                    if (array_key_exists($val['become_status'], $this->configs['reviews-shop-become_status'])) {
                        $become_status = $this->configs['reviews-shop-become_status'][$val['become_status']];
                    }
                    $mark = '';
                    if (array_key_exists($val['status'], $this->configs['reviews-shop-status'])) {
                        $mark = '<div class="cb_comment_mark comment_mark_' . $val['status'] . '" title="' . $become_status . '">' . $this->configs['reviews-shop-status'][$val['status']] . '</div>';
                    }
                    $reviews_html .=
                        '<div class="cb_comment">' .
                        '<div class="cb_comment_header">' . htmlspecialchars($val['fio']) . '</div>' .
                        '<div class="cb_comment_text">' .
                        htmlspecialchars($val['text']) .
                        '</div>' .
                        '<div class="cb_comment_footer">' .
                        '<div class="cb_comment_date">' . date('d.m.y H:i', strtotime($val['date'])) . '</div>' .
                        $mark .
                        '<div class="clear_both"></div>' .
                        '</div>' .
                        '<div class="clear_both"></div>' .
                        '</div>';
                }
            }
        }
        return $reviews_html;
    }
}