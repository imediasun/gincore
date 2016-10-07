<?php

require_once __DIR__ . '/../../Core/Controller.php';

$modulename[70] = 'categories';
$modulemenu[70] = l('Категории');
$moduleactive[70] = !$ifauth['is_2'];

/**
 * @property  MCategories Categories
 * @property  MGoods      Goods
 */
class categories extends Controller
{
    public $cat_id;
    public $cat_img;

    public $uses = array(
        'Categories',
        'Goods'
    );

    /**
     * categories constructor.
     * @param $all_configs
     */
    public function __construct(&$all_configs)
    {
        parent::__construct($all_configs);

        $this->cat_img = $this->all_configs['configs']['cat-img'];
        $this->cat_id = isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0 ?
            $this->all_configs['arrequest'][2] : null;
    }

    /**
     * @return string
     */
    public function renderCanShowModuleError()
    {
        return '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас нет прав для просмотра категорий') . '</p></div>';

    }

    /**
     * @param $get
     * @return string
     */
    public function check_get($get)
    {
        global $input_html;
        if (!array_key_exists(1, $this->all_configs['arrequest'])) {// выбрана ли категория
            return $this->show_categories();
        }
        $input_html['mmenu'] = '<div class="span3">' . $this->genmenu() . '</div>';
        return '<div class="span9">' . $this->gencontent() . '</div>';
    }

    /**
     * @param      $post
     * @param bool $redirect
     * @return array
     */
    public function check_post(Array $post, $redirect = true)
    {
        $mod_id = $this->all_configs['configs']['categories-manage-page'];

        $title = (isset($post['title']) && !is_array($post['title'])) ? trim($post['title']) : '';
        $content = isset($post['content']) ? $post['content'] : '';
        $avail = isset($post['avail']) ? 1 : null;
        $url = isset($post['url']) ? $this->transliturl($post['url']) : $this->transliturl($title);

        // создание категории
        if (isset($post['create-category']) && $this->all_configs['oRole']->hasPrivilege('create-filters-categories')) {

            $category_url = $this->all_configs['db']->query('SELECT id FROM {categories} WHERE url=?',
                array($url))->el();

            if ($category_url) {
                if ($redirect) {
                    Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                        . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/?error=url');
                }
                return array('error' => l('Категория с таким названием уже существует.'));
            }

            $id = $this->Categories->insert(array(
                'title' => $title,
                'url' => $url,
                'content' => $content,
                'parent_id' => intval($post['categories']),
                'avail' => $avail

            ));

            $this->History->save('create-category', $mod_id, $id);

            if ($redirect) {
                Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                    . $this->all_configs['arrequest'][1] . '/' . $id);
            }
            return $id;
        } elseif (isset($post['edit-seo']) && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            // редактирование seo
            $ar = $this->Categories->update(array(
                'page_title' => trim($post['page_title']),
                'page_description' => trim($post['page_description']),
                'page_keywords' => trim($post['page_keywords']),
                'page_content' => trim($post['page_content'])
            ), array(
                'id' => intval($post['category_id'])
            ));
            if (intval($ar) > 0) {
                $this->History->save('edit-seo-category', $mod_id, intval($post['category_id']));
            }

            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2]);
        } elseif (isset($post['edit-context'])) {
            // редактирование контекстной рекламы
            if (isset($post['campaign_id'])) {
                foreach ($post['campaign_id'] as $system_id => $campaign_id) {
                    $this->all_configs['db']->query('INSERT INTO {context_categories} (category_id, system_id, campaign_id)
                            VALUES (?i, ?i, ?) ON DUPLICATE KEY UPDATE campaign_id=VALUES(campaign_id)',
                        array($this->cat_id, $system_id, $campaign_id));
                }
            }
        } elseif (isset($post['edit-category']) && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            // редактирование категории
            if (intval($post['id']) < 1) {
                return false;
            }
            $category = $this->Categories->getByPk(intval($post['id']));
            $recycleBin = $this->Categories->getRecycleBin();
            if (intval($post['id']) == $recycleBin['id']) {
                FlashMessage::set(l('Редактирование системной категории "Корзина" запрещено'), FlashMessage::DANGER);
                return false;
            }
            if (isset($_FILES['thumbs']) && $_FILES['thumbs']['error'] < 1 && $_FILES["thumbs"]["size"] > 0 && $_FILES["thumbs"]["size"] < 1024 * 1024 * 1 &&
                ($_FILES["thumbs"]["type"] == "image/gif" || $_FILES["thumbs"]["type"] == "image/jpeg"
                    || $_FILES["thumbs"]["type"] == "image/jpg" || $_FILES["thumbs"]["type"] == "image/png")
            ) {
                list($width, $height, $type, $attr) = getimagesize($_FILES["thumbs"]["tmp_name"]);
                $path_parts = full_pathinfo($_FILES["thumbs"]["name"]);
                if ($width == 30 && $height == 30) {
                    if (move_uploaded_file($_FILES["thumbs"]["tmp_name"],
                        $this->all_configs['sitepath'] . $this->cat_img . $url . '.' . $path_parts['extension'])) {

                        $ar = $this->Categories->update(array(
                            'thumbs' => $url . '.' . $path_parts['extension']
                        ), array(
                            'id' => intval($post['id'])
                        ));
                        if (intval($ar) > 0) {
                            $this->History->save('edit-category-thumbs', $mod_id, intval($post['id']));
                        }
                    }
                }
            }
            if (isset($_FILES['image']) && $_FILES['image']['error'] < 1 && $_FILES["image"]["size"] > 0 && $_FILES["image"]["size"] < 1024 * 1024 * 10 &&
                $_FILES["image"]["type"] == "image/png"
            ) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"],
                    $this->all_configs['sitepath'] . $this->cat_img . $url . '_image.png')) {

                    $ar = $this->Categories->update(array(
                        'image' => $url . '_image.png'
                    ), array(
                        'id' => intval($post['id'])
                    ));
                    if (intval($ar) > 0) {
                        $this->History->save('edit-category-image', $mod_id, intval($post['id']));
                    }
                }
            }
            if (isset($_FILES['cat-image']) && $_FILES['cat-image']['error'] < 1 && $_FILES["cat-image"]["size"] > 0 && $_FILES["cat-image"]["size"] < 1024 * 1024 * 1 &&
                ($_FILES["cat-image"]["type"] == "image/gif" || $_FILES["cat-image"]["type"] == "image/jpeg"
                    || $_FILES["cat-image"]["type"] == "image/jpg" || $_FILES["cat-image"]["type"] == "image/png")
            ) {

                $path_parts = full_pathinfo($_FILES["cat-image"]["name"]);
                if (move_uploaded_file($_FILES["cat-image"]["tmp_name"],
                    $this->all_configs['sitepath'] . $this->cat_img . $url . '_cat.' . $path_parts['extension'])) {

                    $ar = $this->Categories->update(array(
                        'cat-image' => $url . '_cat.' . $path_parts['extension']
                    ), array(
                        'id' => intval($post['id'])
                    ));
                    if (intval($ar) > 0) {
                        $this->History->save('edit-category-cat-image', $mod_id, intval($post['id']));
                    }
                }
            }

            $category_url = $this->all_configs['db']->query('SELECT id FROM {categories} WHERE url=? AND id<>?',
                array($url, intval($post['id'])))->el();

            if ($category_url) {
                Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                    . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/?error=url');
            }

            $ar = $this->Categories->update(array(
                'title' => $title,
                'url' => $url,
                'content' => $content,
                'parent_id' => intval($post['categories']),
                'prio' => $post['prio'],
                'warehouses_suppliers' => isset($post['warehouses_suppliers']) ? trim($post['warehouses_suppliers']) : '',
                'information' => isset($post['information']) ? trim($post['information']) : '',
                'avail' => $avail,
                'rating' => isset($post['rating']) ? trim($post['rating']) : 0,
                'votes' => isset($post['votes']) ? trim($post['votes']) : 0,
                'percent_from_profit' => isset($post['percent_from_profit']) ? intval($post['percent_from_profit']) : 0,
                'fixed_payment' => isset($post['fixed_payment']) ? floatval($post['fixed_payment']) * 100 : 0
            ), array(
                'id' => intval($post['id'])
            ));
            if (!empty($post['information']) && trim($post['information']) != $category['information']) {
                $this->History->save('change-category-info', $mod_id, $category['id'], $category['information']);
            }
            if (intval($ar) > 0) {
                $this->all_configs['db']->query('UPDATE {orders} SET title=? WHERE category_id=?i',
                    array($title, intval($post['id'])));
                $this->History->save('edit-category', $mod_id, intval($post['id']));
            }

            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                . $this->all_configs['arrequest'][1] . '/' . intval($post['id']));

        } elseif (isset($post['recovery-category']) && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            $this->recoveryCategories($mod_id);
        }
        return '';
    }

    /**
     * @param string $name
     * @param bool   $ajax
     * @return string
     */
    private function gencreate($name = '', $ajax = false)
    {
        return $this->view->renderFile('categories/gencreate', array(
            'ajax' => $ajax,
            'name' => $name
        ));
    }

    /**
     * @return string
     */
    private function show_categories()
    {
        return $this->view->renderFile('categories/show_categories', array(
            'categories' => $this->get_categories()
        ));
    }

    /**
     * @return mixed
     */
    private function get_categories()
    {
        return $this->all_configs['db']->query("SELECT * FROM {categories} ORDER BY prio")->assoc();
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('categories/genmenu', array(
            'categories' => $this->get_categories(),
            'cat_id' => $this->cat_id
        ));
    }

    /**
     * @param      $cats
     * @param int  $count
     * @param null $pid
     * @param int  $c
     * @return string
     */
    public function categories_tree_menu($cats, $count = 0, $pid = null, $c = 0)
    {
        static $i = 1, $table = 1;
        $tree = '';

        foreach ($cats as $cat) {
            $disabledpage = '';
            if ($cat['avail'] != 1) {
                $disabledpage = 'class="disabledpage"';
            }
            if ($count > 0) { // если нужно в несколько колонок
                if ($i == 1) {
                    $tree .= '<td>';
                }
            }
            if ($cat['parent_id'] == 0) {
                $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' connectedSortable nav nav-list first-in-menu">';
            }

            $h = 1;
            for ($iter = 0; $iter < $c; $iter++) {
                if ($h == 1 && $c > 1) {
                    $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' hide-ul connectedSortable nav nav-list">';
                } else {
                    $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' connectedSortable nav nav-list">';
                }
                $h++;
            }
            $i++;

            if (intval($cat['id']) == intval($this->cat_id)) {
                if ($c == 1 && isset($cat['child'])) {
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><span class="show-child-cat">+</span><a '
                        . $disabledpage . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] . '/">' . $cat['title'] . '</a>';
                } else {
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><a ' . $disabledpage . ' href="'
                        . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] . '/">' . $cat['title'] . '</a>';
                }
            } else {
                if ($c == 1 && isset($cat['child'])) {
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><span class="show-child-cat">+</span><a '
                        . $disabledpage . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] . '/">' . $cat['title'] . '</a>';
                } else {
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><a ' . $disabledpage . ' href="'
                        . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] . '/">' . $cat['title'] . '</a>';
                }
            }
            $c++;
            if (isset($cat['child'])) {
                $tree .= $this->categories_tree_menu($cat['child'], $count, $cat['parent_id'], $c);
            } else {
                $tree .= '<ul class="sortable' . $table . ' connectedSortable nav nav-list"><li style="height:2px"></li></ul>';
            }

            $c--;
            $tree .= '</li>';
            for ($iter = 0; $iter < $c; $iter++) {
                $tree .= '</ul>';
            }
            if ($cat['parent_id'] == 0) {
                $tree .= '</ul>';
            }
            if ($count > 0) { // если нужно в несколько колонок
                if (intval($count / 4) <= $i && $cat['parent_id'] == 0) {
                    $i = 1;
                    $tree .= '</td>';
                    $table++;
                }
            }
        }

        return $tree;
    }

    /**
     * @param $list
     * @param $parent
     * @return array
     */
    public function createTree(&$list, $parent)
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

    /**
     * @return mixed
     */
    public function get_cur_category()
    {
        return $this->Categories->getByPk($this->cat_id);
    }

    /**
     * @param bool $filters_values
     * @return mixed
     */
    public function get_filters($filters_values = false)
    {
        // добываем все группы фильтров по текущей категории
        $filters = $this->all_configs['db']->query('SELECT fn.id, fn.title, fn.type, fn.prio
                FROM {filter_name} as fn, {category_filter_name} as cfn
                WHERE cfn.category_id=? AND cfn.fname_id=fn.id ',
            array($this->cat_id))->assoc();

        if ($filters && $filters_values == true) {
            $filters_value = $this->all_configs['db']->query('SELECT fv.value, fv.id, nv.fname_id, fc.filter_id
                    FROM {filter_value} as fv, {category_filter} as fc, {filter_name_value} as nv
                    WHERE fc.category_id=?i AND fc.filter_id=nv.id AND nv.fvalue_id=fv.id',
                array($this->cat_id))->assoc();

            if ($filters_value) {
                foreach ($filters as $k => $filter) {
                    //$filters[$k]['values'] = array();
                    foreach ($filters_value as $filter_value) {
                        if ($filter_value['fname_id'] == $filter['id']) {
                            $filters[$k]['values'][$filter_value['id']] = array(
                                'value' => $filter_value['value'],
                                'filter_id' => $filter_value['filter_id'],
                            );
                        }
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * @return array
     */
    public function categories_edit_tab_category()
    {
        return array(
            'html' => $this->view->renderFile('categories/categories_edit_tab_category', array(
                'cur_category' => $this->get_cur_category(),
                'cat_img' => $this->cat_img
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function categories_edit_tab_goods()
    {
        $category_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('show-goods')) {
            // проверяем сортировку
            $sorting = ' ORDER BY {goods}.prio';

            $sort = '';
            $sort_id = '<a href="?sort=rid">ID';
            $sort_title = '<a href="?sort=title">' . l('Название продукта');
            $sort_price = '<a href="?sort=price">' . l('Цена');
            $sort_date = '<a href="?sort=date">' . l('Дата') . '';
            $sort_avail = '<a href="?sort=avail">' . l('Вкл');
            if (isset($_GET['sort'])) {
                $sort = '&sort=' . $_GET['sort'];
                switch ($_GET['sort']) {
                    case 'id':
                        $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rid':
                        $sort_id = '<a href="?sort=id">ID<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.id DESC';
                        break;
                    case 'title':
                        $sort_title = '<a href="?sort=rtitle">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.title';
                        break;
                    case 'rtitle':
                        $sort_title = '<a href="?sort=title">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.title DESC';
                        break;
                    case 'price':
                        $sort_price = '<a href="?sort=rprice">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.price';
                        break;
                    case 'rprice':
                        $sort_price = '<a href="?sort=price">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.id DESC';
                        break;
                    case 'date':
                        $sort_date = '<a href="?sort=rdate">' . l('Дата') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.date_add';
                        break;
                    case 'rdate':
                        $sort_date = '<a href="?sort=date">' . l('Дата') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.date_add DESC';
                        break;
                    case 'avail':
                        $sort_avail = '<a href="?sort=ravail">' . l('Вкл') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.avail';
                        break;
                    case 'ravail':
                        $sort_avail = '<a href="?sort=avail">' . l('Вкл') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.avail DESC';
                        break;
                }
            } else {
                $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
            }


            // добываем все товары для текущей категории
            $goods = $this->all_configs['db']->query('SELECT {goods}.*
                FROM {goods}, {category_goods}
                WHERE {category_goods}.category_id=? AND {category_goods}.goods_id={goods}.id' . $sorting,
                array($this->cat_id))->assoc();

            // строим таблицу товаров


            if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
                $category_html .= '<a class="btn btn-primary" href="' . $this->all_configs['prefix'] . 'products/create?cat_id=';
                $category_html .= $this->cat_id . '">' . l('Добавить товар') . '</a><br /><br />';
            }

            if (count($goods) > 0) {
                $category_html .= '<table class="table table-striped"><thead><tr>
                        <td>' . $sort_id . '</a></td>
                        <td>' . $sort_title . '</a></td>
                        <td>' . $sort_avail . '</td>
                        <td>' . $sort_price . '</a></td>
                        <td>' . $sort_date . '</a></td>
                        <td title="' . l('Общий остаток') . '">' . l('Общий') . '</td>
                        <td title="' . l('Свободный остаток') . '">' . l('Свободный') . '</td>
                    </td></tr></thead><tbody>';
                $count_on_page = $this->count_on_page;//20; // количество товаров на страничке

                if (isset($_GET['p'])) {
                    $current_page = $_GET['p'] - 1;
                } else {
                    $current_page = 0;
                }

                $count_page = $count_on_page > 0 ? ceil(count($goods) / $count_on_page) : 0;
                $show_goods = array_slice($goods, $count_on_page * $current_page, $count_on_page);
                foreach ($show_goods as $good) {
                    $category_html .= '<tr>
                        <td>' . $good['id'] . '</td>
                        <td><a href="' . $this->all_configs['prefix'] . 'products/create/' . $good['id'] . '/">' . htmlspecialchars($good['title']) . '<i class="icon-pencil"></i></a>
                            <span style="float:right">
                                <a href="' . $this->all_configs['prefix'] . 'products/create/' . $good['id'] . '/"><i class="glyphicon glyphicon-eye-open"></i></a>
                            </span></td>
                        <td>' . $good['avail'] . '</td><td>' . show_price($good['price'], 2, ' ') . '</td>' .
                        '<td><span title="' . do_nice_date($good['date_add'],
                            false) . '">' . do_nice_date($good['date_add']) . '</span></td>
                        <td>' . intval($good['qty_wh']) . '</td><td>' . intval($good['qty_store']) . '</td>
                    </tr>';
                }
                $category_html .= '</tbody></table>';


                // строим блок страничек товаров
                $category_html .= page_block($count_page, count($goods), '#edit_tab_goods');

            } else {
                $category_html .= '<p  class="text-error">' . l('В выбранной Вами категории нет ни одного товара') . '</p>';
            }
        } else {
            $category_html .= '<p  class="text-error">' . l('У Вас нет прав для просмотра товаров') . '</p>';
        }

        return array(
            'html' => $category_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function categories_edit_tab_seo()
    {
        return array(
            'html' => $this->view->renderFile('categories/categories_edit_tab_seo', array(
                'cur_category' => $this->get_cur_category(),
                'cat_id' => $this->cat_id
            )),
            'functions' => array('tiny_mce()'),
        );
    }

    /**
     * @return string
     */
    public function gencontent()
    {
        // добываем текущюю категорию
        if ($this->cat_id > 0) {
            $cur_category = $this->get_cur_category();

            // проверяем на наличие категории
            if (empty($cur_category)) {
                return '<p  class="text-error">' . l('Не существует такой категории') . '</p>';
            }
        } else {
            return $this->gencreate();
        }

        $category_html = '';
        $category_html .= '<div class="tabbable"><ul class="nav nav-tabs">';
        $category_html .= '<li><a class="click_tab default" data-open_tab="categories_edit_tab_category" onclick="click_tab(this, event)" data-toggle="tab" href="#edit_tab_category">' . l('Категория') . '</a></li>';
        $category_html .= '<li><a class="click_tab" data-open_tab="categories_edit_tab_goods" onclick="click_tab(this, event)" data-toggle="tab" href="#edit_tab_goods">' . l('Товары') . '</a></li>';
        $category_html .= '</ul>';

        $category_html .= '<div class="tab-content"><div id="edit_tab_category" class="tab-pane">';
        $category_html .= '</div>';

        if ($this->all_configs['oRole']->hasPrivilege('show-goods')) {
            $category_html .= '<div id="edit_tab_goods" class="tab-pane">';
            $category_html .= '</div>';
        }

        if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            $category_html .= '<div id="edit_tab_seo" class="tab-pane">';
            $category_html .= '</div>';
        }

        $category_html .= '</div>';

        return $category_html;
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return $this->all_configs['oRole']->hasPrivilege('show-goods');
    }

    /**
     *
     */
    public function ajax()
    {
        $data = array(
            'state' => false
        );
        $mod_id = $this->all_configs['configs']['categories-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            Response::json(array('message' => 'Нет прав', 'state' => false));
        }

        if ($act == 'create_form') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            Response::json(array('html' => $this->gencreate($name, true), 'state' => true));
        }

        if ($act == 'create_new') {
            $_POST['create-category'] = true;
            $create = $this->check_post($_POST, false);
            if (isset($create['error'])) {
                $result = array('state' => false, 'msg' => $create['error']);
            } else {
                $result = array('state' => true, 'id' => $create, 'name' => $_POST['title']);
            }
            Response::json($result);
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array(
                            (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                    'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                        )
                    );
                    $result = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
                    );
                } else {
                    $result = array('message' => l('Не найдено'), 'state' => false);
                }
                Response::json($result);
            }
        }

        // редактирование названия группы фильтров
        if ($act == 'rename-filter-name') {
            if ($this->cat_id > 0 && isset($_POST['pk']) && $_POST['pk'] > 0 && isset($_POST['value'])) {
                $this->all_configs['db']->query('UPDATE {filter_name} SET title=? WHERE id=?i',
                    array(trim($_POST['value']), $_POST['pk']));
                $data['state'] = true;
            }
        }

        // редактирование названия фильтра
        if ($act == 'rename-filter-value') {
            if ($this->cat_id > 0 && isset($_POST['pk']) && $_POST['pk'] > 0 && isset($_POST['value'])) {
                $this->all_configs['db']->query('UPDATE {filter_value} SET value=? WHERE id=?i',
                    array(trim($_POST['value']), $_POST['pk']));
                $data['state'] = true;
            }
        }

        // drag-and-drop категорий товаров
        if ($act == 'update-categories') {
            $data = $this->updateCategories($data);
        }

        if ($act == 'change-info-form') {
            $data = $this->changeInfoForm();
        }
        if ($act == 'change-info') {
            $data = $this->changeInformation($_POST);
        }

        if ($act == 'delete-categories' && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            $data = $this->deleteCategories($mod_id);
        }

        preg_match('/changes:(.+)/', $act, $arr);
        // история изменений инженера
        if (count($arr) == 2 && isset($arr[1])) {
            $data = $this->getChanges($act, $_POST, $mod_id);
        }
        Response::json($data);
    }

    /**
     * @param $str
     * @return mixed|string
     */
    public function transliturl($str)
    {
        return transliturl($str);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function updateCategories($data)
    {
        if (isset($_POST['cur_id']) && $_POST['cur_id'] > 0) {
            $position = isset($_POST['position']) ? intval($_POST['position']) + 1 : 1;
            $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

            // обновляем парент ид
            $this->Categories->update(array(
                'parent_id' => $parent_id,
                'prio' => $position
            ), array('id' => $_POST['cur_id']));

            // достаем всех соседей категории
            $categories = $this->Categories->query('SELECT id, prio FROM {categories}
                      WHERE parent_id IN (SELECT parent_id FROM {categories} WHERE id=?i)
                        AND id<>?i ORDER BY prio',
                array($_POST['cur_id'], $_POST['cur_id']))->vars();

            if ($categories) {
                $i = 1;
                foreach ($categories as $category => $prio) {
                    $i = $i == $position ? $i + 1 : $i;
                    $this->Categories->update(array(
                        'prio' => $i
                    ), array('id' => $category));
                    $i++;
                }
            }

            $data['state'] = true;
        }
        return $data;
    }

    /**
     * @param $state
     * @param $id
     */
    private function setDeleted($state, $id)
    {
        $avail = !$state;
        $this->Categories->update(array(
            'deleted' => $state,
            'avail' => $avail
        ), array('id' => $id));
        $ids = array($id);
        do {
            $this->Categories->update(array(
                'deleted' => $state,
                'avail' => $avail
            ), array('id' => $ids));
            $this->all_configs['db']->query('UPDATE {goods} SET deleted=?i, avail=?i WHERE id in (SELECT goods_id FROM {category_goods} WHERE category_id in (?li))',
                array($state, $avail, $ids));
            $ids = $this->Categories->query('SELECT id FROM {categories} WHERE parent_id in (?li)',
                array($ids))->col();
        } while (!empty($ids));
    }

    /**
     * @param $mod_id
     * @return array
     */
    private function deleteCategories($mod_id)
    {
        try {
            $recycleBin = $this->Categories->getRecycleBin();
            if (empty($recycleBin)) {
                throw new ExceptionWithMsg(l('Корзина не найдена. Обновите систему.'));
            }
            if (intval($_POST['id']) == $recycleBin['id']) {
                throw new ExceptionWithMsg(l('Нельзя удалить корзину'));
            }
            $this->Categories->update(array(
                'parent_id' => $recycleBin['id'],
            ), array('id' => intval($_POST['id'])));
            $this->History->save('delete-category', $mod_id, intval($_POST['id']));
            $this->setDeleted(1, $_POST['id']);
            $data = array(
                'state' => true
            );

        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }
        return $data;
    }

    /**
     * @param $mod_id
     * @return array
     */
    private function recoveryCategories($mod_id)
    {
        try {
            $category = $this->Categories->getByPk(intval($_POST['id']));
            $recycleBin = $this->Categories->getRecycleBin();
            if (empty($recycleBin)) {
                throw new ExceptionWithMsg(l('Корзина не найдена. Обновите систему.'));
            }
            if (empty($category) || $category['parent_id'] !== $recycleBin['id']) {
                throw new ExceptionWithMsg(l('Категория не в корзине.'));
            }
            if (intval($_POST['id']) == $recycleBin['id']) {
                throw new ExceptionWithMsg(l('Нельзя произвести эту операцию с  корзиной'));
            }
            $this->Categories->update(array(
                'parent_id' => 0,
            ), array('id' => intval($_POST['id'])));
            $this->History->save('restore-category', $mod_id, intval($_POST['id']));
            $this->setDeleted(0, $_POST['id']);

            $data = array(
                'state' => true
            );
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }
        return $data;
    }

    /**
     * @return array
     */
    private function changeInfoForm()
    {
        if (empty($_GET['category_id'])) {
            return array(
                'state' => false,
                'msg' => l('Категория не найдена')
            );
        }
        $category = $this->Categories->getByPk($_GET['category_id']);
        if (empty($category)) {
            return array(
                'state' => false,
                'msg' => l('Категория не найдена')
            );
        }
        return array(
            'state' => true,
            'title' => l('Редактирование категории') . '&nbsp;' . $category['title'] . InfoPopover::getInstance()->createQuestion('l_category_information'),
            'content' => $this->view->renderFile('categories/change_info_form', array(
                'category' => $category
            ))
        );
    }

    /**
     * @param $post
     * @return array
     */
    private function changeInformation($post)
    {
        if (empty($post['category_id'])) {
            return array(
                'state' => false,
                'message' => l('Категория не найдена')
            );
        }
        $category = $this->Categories->getByPk($post['category_id']);
        if (empty($category)) {
            return array(
                'state' => false,
                'message' => l('Категория не найдена')
            );
        }
        if (strcmp($post['information'], $category['information']) !== 0) {
            $this->Categories->update(array(
                'information' => $post['information']
            ), array('id' => $category['id']));
            $mod_id = $this->all_configs['configs']['categories-manage-page'];
            $this->History->save('change-category-info', $mod_id, $category['id'], $category['information']);
        }

        return array(
            'state' => true,
            'message' => l('Информация успешно изменена')
        );
    }
}

