<?php

require_once __DIR__.'/../../Core/Controller.php';
$modulename[60] = 'products';
$modulemenu[60] = l('Товары');
$moduleactive[60] = !$ifauth['is_2'];

class products extends Controller
{
    private $goods = array();

    /*
     * for left imt block
     * */
    public $show_imt = null;

    public $count_goods;
    public $count_on_page = 20;

    private $errors = array();

    /**
     * @inheritdoc
     */
    public function routing(Array $arrequest)
    {
        global $input_html;
        parent::routing($arrequest);
        if (!isset($arrequest[1]) || $arrequest[1] != 'create') {
            $input_html['mmenu'] = $this->genmenu(); // список категорий
            $input_html['mcontent'] = $this->gencontent(); // список товаров
        } elseif (isset($arrequest[1]) && $arrequest[1] == 'create') { // форма изменения товара
            $input_html['mcontent'] = $this->gencreate();
        }
    }

    /**
     * products constructor.
     * @param $all_configs
     */
    public function __construct($all_configs)
    {
        global $input_html;
        parent::__construct($all_configs);
        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');

        if(!empty($input_html['mmenu'])){
            $input_html['menu_span'] = 'col-sm-3';
            $input_html['content_span'] = 'col-sm-9';
        }else{
            $input_html['menu_span'] = '';
            $input_html['content_span'] = 'col-sm-10 col-sm-offset-1';
        }
    }

    /**
     * @param $array
     * @param $array2
     * @param $array3
     * @return array
     */
    public function build_releted_array($array, $array2, $array3)
    {
        asort($array2);
        $ordered = array();
        foreach ($array2 as $key => $v) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);

            }
        }
        $array = $ordered + $array;

        $return = array();
        foreach ($array as $k => $v) {
            if ($v == 0) {
                continue;
            }
            if (array_key_exists($k, $array3)) {
                $return[$v] = $array3[$k];
            } else {
                $return[$v] = 0;
            }
        }

        return $return;
    }

    /**
     * @param array $post
     * @return array
     */
    public function check_post(Array $post)
    {
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $user_id = $this->getUserId();

        $product_id = (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) ? $this->all_configs['arrequest'][2] : null;

        // создание продукта
        if (isset($post['create-product']) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {

            $url = transliturl(trim($post['title']));

            // ошибки
            if (/*$product_url || */mb_strlen(trim($post['title']), 'UTF-8') == 0) {
                if (mb_strlen(trim($post['title']), 'UTF-8') == 0) {
                    return array('error' => l('Заполните название'), 'post' => $post);
                }
            } else {
                $id = $this->all_configs['db']->query('INSERT INTO {goods}
                    (title, secret_title, url, avail, price, article, author, type) VALUES (?, ?, ?n, ?i, ?i, ?, ?i, ?i)',
                    array(trim($post['title']), '', $url, isset($post['avail']) ? 1 : 0,
                        trim($post['price']) * 100, $user_id, '', isset($_POST['type']) ? 1 : 0), 'id'
                );

                if ($id > 0) {
                    $_POST['product_id'] = $id;

                    if (isset($post['categories']) && count($post['categories']) > 0) {
                        foreach ($post['categories'] as $new_cat) {
                            if ($new_cat == 0) continue;
                            $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id)
                                VALUES (?i, ?i)', array($new_cat, $id));
                        }
                    }
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'create-goods', $mod_id, $id));

                    include $this->all_configs['sitepath'] . 'mail.php';
                    $messages = new Mailer($this->all_configs);

                    if (isset($post['users']) && count($post['users']) > 0) {

                        foreach ($post['users'] as $user) {
                            if (intval($user) > 0) {
                                $ar = $this->all_configs['db']->query('INSERT IGNORE INTO {users_goods_manager} SET user_id=?i, goods_id=?i',
                                    array(intval($user), $id))->ar();

                                if ($ar) {
                                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                        array($user_id, 'add-manager', $mod_id, intval($user)));
                                }
                            }
                        }
                    }

                    // уведомление
                    if (isset($post['mail'])) {
                        $content = l('Создан новый товар') . ' <a href="' . $this->all_configs['prefix'] . 'products/create/' . $id . '">';
                        $content .= htmlspecialchars(trim($post['title'])) . '</a>.';
                        $messages->send_message($content, l('Требуется обработка товарной позиции'), 'mess-create-product', 1);
                    }
                    if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
                        return array('id' => $id, 'state' => true);
                    }else{
                        Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $id);
                    }
                }
            }
        }

        // редактирование товара
        if ($product_id > 0 && $this->all_configs['oRole']->hasPrivilege('edit-goods')) {

            $ar = 0;
            // редактируем title картинки
            if (isset($post['images_title'])) {
                foreach ($post['images_title'] as $im_id => $image_title) {
                    $ar = $this->all_configs['db']->query('UPDATE {goods_images} SET title=? WHERE id=?i',
                        array($image_title, intval($im_id)))->ar();
                }
            }
            if (intval($ar) > 0) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-goods-title-image', $mod_id, $product_id));
            }
            $ar = 0;
            // редактируем приоритет картинок
            if (isset($post['image_prio'])) {
                foreach ($post['image_prio'] as $im_id => $image_prio) {
                    $ar = $this->all_configs['db']->query('UPDATE {goods_images} SET prio=?i WHERE id=?i',
                        array($image_prio, intval($im_id)))->ar();
                }
            }
            if (intval($ar) > 0) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-goods-image-prio', $mod_id, $product_id));
            }

            //если нужно удаляeм картинку с базы и с папки
            if (isset($post['images_del'])) {
                $secret_title = $this->all_configs['db']->query('SELECT secret_title FROM {goods} WHERE id=?i', array($product_id))->el();

                foreach ($post['images_del'] AS $del_id => $image_title) {
                    $this->all_configs['db']->query('DELETE FROM {goods_images} WHERE id=?i', array(intval($del_id)));
                    unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $product_id . '/' . $image_title);

                    $path_parts = full_pathinfo($image_title);

                    if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $product_id . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'])) {
                        unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $product_id . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension']);
                    }
                    if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $product_id . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension'])) {
                        unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $product_id . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension']);
                    }

                    if (isset($post['one-image-secret_title']) && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen($secret_title, 'UTF-8') > 0) {
                        $del_related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i', array($secret_title, $product_id))->assoc();

                        if ($del_related && count($del_related) > 0) {
                            foreach ($del_related as $del_r) {
                                $this->all_configs['db']->query('DELETE FROM {goods_images} WHERE goods_id=?i AND image=?', array($del_r['id'], $image_title));

                                unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $image_title);

                                $path_parts = full_pathinfo($image_title);

                                if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'])) {
                                    unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension']);
                                }
                                if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension'])) {
                                    unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension']);
                                }

                                $count_images = $this->all_configs['db']->query('SELECT count(id) FROM {goods_images} WHERE goods_id=?i', array($del_r['id']))->el();

                                if ($count_images == 0)
                                    $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i', array(0, $del_r['id']));
                            }
                        }
                    }

                    $count_images = $this->all_configs['db']->query('SELECT count(id) FROM {goods_images} WHERE goods_id=?i', array($product_id))->el();

                    if ($count_images == 0)
                        $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i', array(0, $product_id));
                }
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'delete-goods-image', $mod_id, $product_id));
            }

            if (isset($post['youtube'])) {
                foreach ($post['youtube'] as $ytid => $youtube) {
                    $youtube = trim($youtube);

                    if (isset($post['remove-video']) && isset($post['remove-video'][$ytid])) {
                        $this->all_configs['db']->query('DELETE FROM {goods_images} WHERE id=?i AND type=?i AND goods_id=?i',
                            array($ytid, 2, $product_id));
                        continue;
                    }
                    $yt = $this->all_configs['db']->query('SELECT * FROM {goods_images} WHERE type=?i AND goods_id=?i AND id=?i',
                        array(2, $product_id, $ytid))->row();

                    if ($yt) {
                        $this->all_configs['db']->query('UPDATE {goods_images} SET image=? WHERE type=?i AND goods_id=?i AND id=?i',
                            array($youtube, 2, $product_id, $ytid));
                    } else {
                        if (empty($youtube))
                            continue;

                        $this->all_configs['db']->query('INSERT INTO {goods_images} (image, type, goods_id) VALUES (?, ?i, ?i)',
                            array($youtube, 2, $product_id));
                    }
                }
            }

            // основные
            if (isset($post['edit-product-main'])) {
                $url = (isset($post['url']) && !empty($post['url'])) ? trim($post['url']) : trim($post['title']);

                if (mb_strlen(trim($post['title']), 'UTF-8') == 0) {
                    return array('error' => l('Заполните название'), 'post' => $post);
                }

                $ar = $this->all_configs['db']->query('UPDATE {goods}
                    SET title=?, secret_title=?, url=?n, prio=?i, article=?n, barcode=? WHERE id=?i',
                    array(trim($post['title']), trim($post['secret_title']), transliturl($url),
                        intval($post['prio']), empty($post['article']) ? null : trim($post['article']),
                        trim($post['barcode']), $product_id))->ar();

                if (intval($ar) > 0) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'edit-goods', $mod_id, $product_id));
                }
            }

            // дополнительно
            if (isset($post['edit-product-additionally'])) {

                $ar = $this->all_configs['db']->query('UPDATE {goods}
                    SET avail=?i, type=?i WHERE id=?i',
                    array(isset($post['avail']) ? 1 : 0, isset($post['type']) ? 1 : 0, $product_id))->ar();

                if (intval($ar) > 0) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'edit-goods', $mod_id, $product_id));
                }

                $query = '';
                if (isset($post['categories']) && count($post['categories']) > 0) {
                    $query = $this->all_configs['db']->makeQuery(' AND category_id NOT IN (?li)', array($post['categories']));
                }
                $this->all_configs['db']->query('DELETE FROM {category_goods} WHERE goods_id=?i ?query',
                    array($product_id, $query));

                // добавляем товар в старые/новые категории
                if (isset($post['categories']) && count($post['categories']) > 0) {
                    foreach ($post['categories'] as $new_cat) {
                        if ($new_cat == 0) continue;

                        $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id)
                                VALUES (?i, ?i)', array($new_cat, $product_id));

                    }
                }

            }

            // менеджеры
            if (isset($post['edit-product-managers_managers'])) {
                $this->all_configs['db']->query('DELETE FROM {users_goods_manager} WHERE goods_id=?i',
                    array($product_id));
                // добавляем доступ к товару пользователям
                if (isset($post['users'])) {
                    foreach ($post['users'] as $user) {
                        if ($user > 0) {
                            $this->all_configs['db']->query('INSERT IGNORE INTO {users_goods_manager}
                                    SET user_id=?i, goods_id=?i',
                                array($user, $product_id));
                        }
                    }
                }
            }

            // finance/stock заказы поставщикам
            if (isset($post['edit-product-financestock_finance'])) {
                $this->all_configs['db']->query('DELETE FROM {goods_suppliers} WHERE goods_id=?i', array($product_id));
                if (isset($post['links'])) {
                    foreach ($post['links'] as $link) {
                        if (mb_strlen(trim($link), 'UTF-8') > 0) {
                            $this->all_configs['db']->query(
                                'INSERT INTO {goods_suppliers} (goods_id, link) VALUES (?i, ?)',
                                array($product_id, trim($link)));
                        }
                    }
                }
            }

            // омт уведомления
            if (isset($post['edit-product-omt_notices'])) {
                $each_sale = 0;
                if (isset($post['each_sale'])) $each_sale = 1;
                $by_balance = 0;
                if (isset($post['by_balance'])) $by_balance = 1;
                $balance = 0;
                if (isset($post['balance']) && $post['balance'] > 0) $balance = intval($post['balance']);
                $by_critical_balance = 0;
                if (isset($post['by_critical_balance'])) $by_critical_balance = 1;
                $critical_balance = 0;
                if (isset($post['critical_balance']) && $post['critical_balance'] > 0) $critical_balance = intval($post['critical_balance']);
                $seldom_sold = 0;
                if (isset($post['seldom_sold'])) $seldom_sold = 1;
                $supply_goods = 0;
                if (isset($post['supply_goods'])) $supply_goods = 1;
                $this->all_configs['db']->query('INSERT INTO {users_notices} (user_id, goods_id, each_sale, by_balance,
                        balance, by_critical_balance, critical_balance, seldom_sold, supply_goods)
                      VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i) ON duplicate KEY
                    UPDATE user_id=VALUES(user_id), goods_id=VALUES(goods_id), each_sale=VALUES(each_sale),
                      by_balance=VALUES(by_balance), balance=VALUES(balance), by_critical_balance=VALUES(by_critical_balance),
                      critical_balance=VALUES(critical_balance), seldom_sold=VALUES(seldom_sold), supply_goods=VALUES(supply_goods)',
                    array($_SESSION['id'], $product_id, $each_sale, $by_balance, $balance, $by_critical_balance,
                        $critical_balance, $seldom_sold, $supply_goods));
            }

            // омт управление закупками
            if (isset($post['edit-product-omt_procurement']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
                // если есть роль внутреннего маркета
                $query_update = $this->all_configs['db']->makeQuery('price=?',
                    array(trim($post['price']) * 100));

                // старая цена
                if (array_key_exists('use-goods-old-price', $this->all_configs['configs'])
                    && $this->all_configs['configs']['use-goods-old-price'] == true && isset($post['old_price'])) {
                    $query_update = $this->all_configs['db']->makeQuery('?query, old_price=?',
                        array($query_update, trim($post['old_price']) * 100));
                }

                // редактируем количество только если отключен 1с и управление складами
                if ($this->all_configs['configs']['onec-use'] == false && $this->all_configs['configs']['erp-use'] == false) {
                    $query_update = $this->all_configs['db']->makeQuery('?query, qty_store=?i, qty_wh=?i,
                            price_purchase=?i, price_wholesale=?',
                        array($query_update, intval($post['exist']), intval($post['qty_wh']),
                            trim($post['price_purchase']) * 100, trim($post['price_wholesale']) * 100));
                }
                $this->all_configs['db']->query('UPDATE {goods} SET ?query WHERE id=?i',
                    array($query_update, $product_id));
                // сохранение по товарам в группе размеров
                if ($this->all_configs['configs']['group-goods'] && isset($sgg_ids_query)) {
                    $this->all_configs['db']->query('UPDATE {goods} SET ?query WHERE ?q',
                        array($query_update, $sgg_ids_query));
                }
            }

            // експорт в 1с
            if (isset($post['1c-export']) && $this->all_configs['configs']['save_goods-export_to_1c'] == true && $this->all_configs['configs']['onec-use'] == true) {
                $this->export_product_1c($product_id);
            }

            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2]);
        }

    }

    /**
     * @param bool $ajax_quick_create
     * @param bool $service
     * @return string
     */
    function create_product_form($ajax_quick_create = false, $service = false)
    {
        $managers = $this->get_managers();
        $groups_size = array();
        if ($this->all_configs['configs']['group-goods']) {
            $groups_size = $this->all_configs['db']->query('SELECT *
                FROM {goods_group_size}
                ORDER BY name')->assoc();
        }
        return $this->view->renderFile('products/create_product_form', array(
            'managers' => $managers,
            'groups_size' => $groups_size,
            'isAjax' => $ajax_quick_create,
            'service' => $service,
            'errors' => $this->errors,
            'categories' => $this->get_categories()
        ));
    }

    /**
     * @return string
     */
    public function show_product_body()
    {
        return $this->view->renderFile('products/show_product_body', array(
            'errors' => $this->errors
        ));
    }

    /**
     * @return string
     */
    private function gencreate()
    {
        // строим форму изменения товара
        $goods_html = '';

        if (isset($this->all_configs['arrequest'][2]) && intval($this->all_configs['arrequest'][2]) > 0) {
            $product = $this->all_configs['db']->query('SELECT id, url, title FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            if ($product) {
                $goods_html .= '<fieldset><legend>' . l('Редактирование товара') .' ID: ' . $product['id'] . '. ' .
                        htmlspecialchars($product['title']).
                    '</legend>' .
                    $this->show_product_body();
            } else {
                $goods_html .= '<p  class="text-error">' . l('Товар не найден') .'</p>';
            }
        } else {
            if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
                $goods_html = $this->create_product_form();
            } else {
                $goods_html .= '<p  class="text-error">' . l('У Вас нет прав для добавления нового товара') .'</p>';
            }
        }

        return $goods_html;
    }

    /**
     * @param int $gid
     * @return mixed
     */
    function get_managers($gid = 0)
    {
        $query = '';
        if ($gid > 0) {
            $query = $this->all_configs['db']->makeQuery('AND m.goods_id=?i', array($gid));
        }
        // убераем менеджеров которые уже прикреплены к товару
        return $this->all_configs['db']->query('
                SELECT u.id, u.login, m.user_id as manager FROM {users} as u
                LEFT JOIN {users_roles} as r ON u.role=r.id
                LEFT JOIN {users_role_permission} as rp ON rp.role_id=r.id
                RIGHT JOIN (SELECT id FROM {users_permissions} WHERE link="external-marketing")p ON p.id=rp.permission_id
                LEFT JOIN {users_goods_manager} as m ON m.user_id=u.id
                ?query WHERE u.avail=1 GROUP BY u.id',

            array($query))->assoc();
    }

    /**
     * @param       $array
     * @param int   $index
     * @param array $tree
     * @return array
     */
    function array_tree($array, $index = 0, $tree = array())
    {
        $space = "";
        for ($i = 0; $i < $index; $i++) {
            $space .= " ○ ";
        }

        if (gettype($array) == "array") {
            $index++;
            while (list ($x, $tmp) = each($array)) {
                $main = '';
                if ($index == 1)
                    $main = 'text-info';

                $tree[] = array('id' => $tmp['id'], 'title' => $space . htmlspecialchars($tmp['title']), 'class' => $main);
                if (array_key_exists('child', $tmp))
                    $tree = $this->array_tree($tmp['child'], $index, $tree);
            }
        }
        return $tree;
    }

    /**
     * @return mixed
     */
    private function get_goods_ids()
    {
        // все категории
        $goods_query = $this->all_configs['db']->makeQuery('WHERE 1=1', array());

        // выбранные категории
        $categories = isset($_GET['cats']) ? array_filter(explode('-', $_GET['cats'])) : array();
        if (count($categories) > 0) {
            // конкретные категории
            $goods_query = $this->all_configs['db']->makeQuery(', {category_goods} AS cg
                    ?query AND cg.category_id IN (?li) AND g.id=cg.goods_id',
                array($goods_query, array_values($categories)));
        }

        // Отобразить
        if (isset($_GET['show'])) {
            $show = array_filter(explode('-', $_GET['show']));
            // мои
            if (array_search('my', $show) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery(', {users_goods_manager} as m
                    ?query AND m.goods_id=g.id AND m.user_id=?i', array($goods_query, $_SESSION['id']));
            }
            // Не заполненные
            if (array_search('empty', $show) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery('?query
                    AND (g.image_set IS NULL OR g.image_set=0 OR g.price=0)', array($goods_query));
            }
            // Без картинок
            if (array_search('noimage', $show) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery('?query
                    AND (g.image_set IS NULL OR g.image_set=0)', array($goods_query));
            }
            // Услуги
            if (array_search('services', $show) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery('?query
                    AND (g.type=1)', array($goods_query));
            }
            // Товары
            if (array_search('items', $show) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery('?query
                    AND (g.type IS NULL OR g.type=0)', array($goods_query));
            }
        }
        // По складам
        if (isset($_GET['wh']) && count(array_values(array_filter(explode('-', $_GET['wh'])))) > 0) {
            $goods_query = $this->all_configs['db']->makeQuery(', {warehouses_goods_items} as i
                ?query AND i.goods_id=g.id AND i.wh_id IN (?li)',
                array($goods_query, array_values(array_filter(explode('-', $_GET['wh'])))));
        }

        // поиск
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $s = str_replace(array("\xA0", '&nbsp;', ' '), '%', trim(urldecode($_GET['s'])));
            $goods_query = $this->all_configs['db']->makeQuery('?query AND (g.title LIKE "%?e%" OR g.barcode LIKE "%?e%")',
                array($goods_query, $s, $s));
        }

        // imt
        $imt = isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : null;
        if (isset($_GET['imt'])) $imt = $_GET['imt'];
        // ид товаров для 1 странички
        switch ($imt) {
            case ('top'):
                // Топ дня
                $this->show_imt = 'top';
                $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id=?i',
                    array($goods_query, $this->all_configs['settings']['top-day']));

                break;

            case ('index'):
                // Товары на главной
                if (count($this->top) > 0) {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id IN (?li)',
                        array($goods_query, array_keys($this->top)));
                } else {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id=0',
                        array($goods_query, array_keys($this->top)));
                }
                $this->show_imt = 'index';

                break;

            case ('discount'):
                // Товары со скидкой
                if (count($this->discounts) > 0) {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id IN (?li)',
                        array($goods_query, array_keys($this->discounts)));
                } else {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id=0',
                        array($goods_query, array_keys($this->discounts)));
                }
                $this->show_imt = 'discount';

                break;

            case ('best'):
                // Хиты продаж
                if (count($this->bestsellers) > 0) {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id IN (?li)',
                        array($goods_query, array_keys($this->bestsellers)));
                } else {
                    $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id=0',
                        array($goods_query, array_keys($this->bestsellers)));
                }
                $this->show_imt = 'best';

                break;

            case ('uncategorised'):
                // Без категорий
                $goods_query = $this->all_configs['db']->makeQuery('?query AND g.id NOT IN (
                        SELECT DISTINCT goods_id FROM {category_goods})', array($goods_query));
                $this->show_imt = 'uncategorised';

                break;
        }

        // выбранные фильтры
        $sfilters = isset($_GET['filters']) ? array_filter(explode('-', $_GET['filters'])) : array();
        $filters_query = $goods_query;
        $filters_query = $this->all_configs['db']->makeQuery('?query AND n.id=nv.fname_id AND v.id=nv.fvalue_id
            AND nv.id=f.filter_id AND g.id=f.goods_id AND g.id=f.goods_id', array($filters_query));
        // фильтрация по фильтрам
        if (count($sfilters) > 0) {
            $goods_query = $this->all_configs['db']->makeQuery(', {goods_filter} as f
                    ?query AND g.id=f.goods_id AND f.filter_id IN(?li)
                    GROUP BY f.goods_id HAVING COUNT(f.filter_id)=?i',
                array($goods_query, array_values($sfilters), count($sfilters)));
        }

        // проверяем наличие сортировки
        $sorting = 'ORDER BY id';
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'rid':
                    $sorting = 'ORDER BY id DESC';
                    break;
                case 'title':
                    $sorting = 'ORDER BY title';
                    break;
                case 'rtitle':
                    $sorting = 'ORDER BY title DESC';
                    break;
                case 'price':
                    $sorting = 'ORDER BY price';
                    break;
                case 'rprice':
                    $sorting = 'ORDER BY price DESC';
                    break;
                case 'date':
                    $sorting = 'ORDER BY date_add';
                    break;
                case 'rdate':
                    $sorting = 'ORDER BY date_add DESC ';
                    break;
                case 'avail':
                    $sorting = 'ORDER BY avail';
                    break;
                case 'ravail':
                    $sorting = 'ORDER BY avail DESC';
                    break;
                default:
                    $sorting = 'ORDER BY id';
                    break;
            }
        }

        return $this->all_configs['db']->query('SELECT DISTINCT g.id, g.price FROM {goods} AS g ?query ?query',
            array($goods_query, $sorting))->vars();
    }

    /**
     *
     */
    private function getGoods()
    {
        // текущая страничка
        $current_page = isset($_GET['p']) ? $_GET['p'] - 1 : 0;

        // все
        $goods_ids = $this->get_goods_ids();

        // количество
        $this->count_goods = count($goods_ids);

        // режем нужное количество
        $goods_ids = array_slice($goods_ids, $current_page * $this->count_on_page, $this->count_on_page, true);

        // достаем описания товаров
        if (count($goods_ids) > 0) {
            $add_fields = array();
            $this->goods = $this->all_configs['db']->query('SELECT g.title, g.id, g.avail, g.price, g.date_add, g.url,
                    g.image_set, SUM(g.qty_wh) as qty_wh, SUM(g.qty_store) as qty_store ?q
                  FROM {goods} AS g WHERE g.id IN (?list) GROUP BY g.id ORDER BY FIELD(g.id, ?li)',
                array(implode(',', $add_fields), array_keys($goods_ids), array_keys($goods_ids)))->assoc('id');

            // картинки
            if ($this->all_configs['configs']['manage-show-plist-img'] && count($this->goods) > 0) {
                $images = $this->all_configs['db']->query('SELECT DISTINCT goods_id, image FROM {goods_images}
                        WHERE goods_id IN (?li) AND type=1 ORDER BY prio',
                    array(array_keys($this->goods)))->assoc();
                if ($images) {
                    foreach ($images as $image) {
                        $this->goods[$image['goods_id']]['image'] = $image['image'];
                    }
                }
            }
        }

    }

    /**
     * @return string
     */
    private function genfilter()
    {
        $this->getGoods();
        $filters_html = '<p class="label label-info">' . l('Отобразить') . '</p>';
        $filters_html .= '<div class="well"><ul style="padding-left:25px"><li><label class="checkbox"><input type="checkbox" ';
        $filters_html .= $this->click_filters('show', 'my') . '>' . l('Мои товары') . '</label></li>';
        $filters_html .= '<li><label class="checkbox"><input type="checkbox" ' . $this->click_filters('show', 'empty');
        $filters_html .= '>' . l('Не заполненные') . '</label></li>';
        $filters_html .= '<li><label class="checkbox"><input type="checkbox"' . $this->click_filters('show', 'noimage');
        $filters_html .= '>' . l('Без картинок') . '</label></li>';
        $filters_html .= '<li><label class="checkbox"><input type="checkbox"' . $this->click_filters('show', 'services');
        $filters_html .= '>' . l('Услуги') . '</label></li>';
        $filters_html .= '<li><label class="checkbox"><input type="checkbox"' . $this->click_filters('show', 'items');
        $filters_html .= '>' . l('Товары') . '</label></li></ul></div>';

        $filters_html .= '<p class="label label-info">' . l('По складам') . '</p>';
        $filters_html .= '<div class="well"><ul style="padding-left:25px">';//<label class="checkbox"><input type="checkbox" id="my_checkbox" value="my" name="my" ' . $this->my_checked . ' onclick="checkbox_select(this, \'' . $a . '\')">' . l('Мои товары') . '</label></li>';
        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses}')->vars();
        if ($warehouses) {
            foreach ($warehouses as $wh_id=>$wh_title) {
                $filters_html .= '<li><label class="checkbox"><input type="checkbox" name="warehouse" value="' . $wh_id . '" ';
                $filters_html .= $this->click_filters('wh', $wh_id) . '>' . htmlspecialchars($wh_title) . '</label></li>';
            }
        }
        $filters_html .= '</ul></div>';

        return $filters_html;
    }

    /**
     * @return mixed
     */
    private function get_categories()
    {
        return $this->all_configs['db']->query("SELECT * FROM {categories}")->assoc();
    }

    /**
     * @param $categories_tree
     * @return string
     */
    function categories_tree_menu($categories_tree)
    {
        $categories_html = '';
        foreach ($categories_tree as $k => $v) {
            $all = array($v['id'] => $v['id']) + (isset($v['child']) ? $this->get_all_childrens($v['child']) : array());

            $categories_html .= '<li><label class="checkbox"><input type="checkbox" ';
            $categories_html .= $this->click_filters('cats', $all) . '>' . htmlspecialchars($v['title']) . '</label>';

            if (isset($v['child'])) {
                $categories_html .= '<ul class="nav nav-list">' . $this->categories_tree_menu($v['child']);
            }
        }
        $categories_html .= '</ul></li>';

        return $categories_html;
    }

    /**
     * @param       $array
     * @param array $return
     * @return array
     */
    function get_all_childrens($array, $return = array())
    {
        foreach ($array as $el) {
            $return[$el['id']] = $el['id'];

            if (isset($el['child'])) {
                $return = $this->get_all_childrens($el['child'], $return);
            }
        }

        return $return;
    }

    /**
     * @return string
     */
    private function genmenu()
    {
       ================== 
        $categories = $this->get_categories();

        $categories_html = '
            <div class="control-group">
            </div>';

        $filters_html = $this->genfilter(); // список фильтров
        $data = array();

        foreach ($categories as $category) {
            $data[$category['parent_id']][] = array(
                'id' => $category['id'],
                'parent_id' => $category['parent_id'],
                'title' => $category['title'],
                'url' => $category['url']
            );
        }

        $categories_tree = count($data) > 0 ? $this->createTree($data, $data[0]) : array();
        $categories_html .= '<p class="label label-info">' . l('Категории') . '</p>';
        $categories_html .= '<ul class="nav nav-list well well-white" id="tree">' . $this->categories_tree_menu($categories_tree) . '</ul>';

        return $categories_html . '<p></p>' . $filters_html;

    }

    /**
     * @param $list
     * @param $parent
     * @return array
     */
    function createTree(&$list, $parent)
    {
        $tree = array();

        if (is_array($parent) && count($parent) > 0) {
            foreach ($parent as $k => $l) {
                if (isset($list[$l['id']])) {
                    $l['child'] = $this->createTree($list, $list[$l['id']]);
                }
                $tree[] = $l;
            }
        }

        return $tree;
    }

    /**
     * @return string
     */
    public function gencontent()
    {
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $warranties = $this->all_configs['configs']['warranties'];

        // импорт товаров с яндекс маркета
        if (isset($_POST['ym-import_goods']) && $this->all_configs['oRole']->hasPrivilege('parsing')) {

            require_once($this->all_configs['path'] . 'parser/pp.php');
            require_once($this->all_configs['sitepath'] . 'mail.php');

            if (isset($_POST['categories']) && $_POST['categories'] > 0) {

                $a = new YM_Products_Parser($this->all_configs, false);

                $a->go($_POST['categories']);

                echo '<br /><br ><a href="">' . l('Обновить') .'</a>';
                exit;
            }
        }

        // быстрое обновление
        if (isset($_POST['quick-edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods')) {
            // обновление активности товара
            if (isset($_POST['avail']) && is_array($_POST['avail'])/* && $this->all_configs['oRole']->hasPrivilege('external-marketing')*/) {
                foreach ($_POST['avail'] as $p_id=>$p_avail) {
                    if ($p_id > 0) {
                        $ar = $this->all_configs['db']->query('UPDATE {goods} SET avail=?i WHERE id=?i', array($p_avail, $p_id))->ar();

                        if ($ar) {
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'edit-product-avail', $mod_id, $p_id));
                        }
                    }
                }
            }

            // обновление цен
            if (isset($_POST['price']) && is_array($_POST['price']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
                foreach ($_POST['price'] as $p_id=>$p_price) {
                    if ($p_id > 0) {
                        $this->all_configs['db']->query('UPDATE {goods} g
                                LEFT JOIN {goods_extended} e ON e.goods_id=g.id
                                SET g.price=?i
                                WHERE g.id=?i AND (e.hotline_flag IS NULL OR e.hotline_flag=0)',
                            array($p_price*100, $p_id))->ar();
                    }
                }
            }

            // обновление остатков
            if (isset($_POST['qty_store']) && is_array($_POST['qty_store']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')
                && $this->all_configs['configs']['erp-use'] == false && $this->all_configs['configs']['onec-use'] == false) {

                foreach ($_POST['qty_store'] as $gid=>$qty_store) {
                    if ($gid > 0) {
                        $this->all_configs['db']->query('UPDATE {goods} g SET qty_store=?i, qty_wh=?i WHERE id=?i',
                            array($qty_store, $qty_store, $gid))->ar();
                    }
                }
            }

            // обновление остатков
            if (isset($_POST['qty_store']) && is_array($_POST['qty_store']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')
                && $this->all_configs['configs']['erp-use'] == false && $this->all_configs['configs']['onec-use'] == false) {

                foreach ($_POST['qty_store'] as $gid=>$qty_store) {
                    if ($gid > 0) {
                        $this->all_configs['db']->query('UPDATE {goods} g SET qty_store=?i, qty_wh=?i WHERE id=?i',
                            array($qty_store, $qty_store, $gid))->ar();
                    }
                }
            }

            // обновление яндекс маркет ид
            if (isset($_POST['ym_id']) && is_array($_POST['ym_id']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

                foreach ($_POST['ym_id'] as $gid=>$value) {
                    if ($gid > 0) {
                        if ($value == 0)
                            $value = null;

                        $ar = $this->all_configs['db']->query('INSERT INTO {goods_extended} (market_yandex_id, goods_id) VALUES (?n, ?i) ON DUPLICATE KEY
                            UPDATE market_yandex_id=VALUES(market_yandex_id)', array($value, $gid))->ar();

                        if ($ar) {
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'edit-ym_id', $mod_id, $gid));
                        }
                    }
                }
            }
            header("Location:" . $_SERVER['REQUEST_URI']);
            exit;
        }

        // поиск товаров
        if (isset($_POST['search'])) {
            $_GET['s'] = isset($_POST['text']) ? trim($_POST['text']) : '';

            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?' . get_to_string('p', $_GET));
            exit;
        }

        // если изменяем нсатройки гарантии
        if (isset($_POST['default-add-product']) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {

            $this->all_configs['db']->query('INSERT INTO {settings} (`name`, `value`) VALUES (?, ?) ON DUPLICATE KEY
                    UPDATE `value`=VALUES(`value`)',
                array("warranty", intval($_POST['warranty'])));
            $this->all_configs['db']->query('INSERT INTO {settings} (`name`, `value`) VALUES (?, ?) ON DUPLICATE KEY
                    UPDATE `value`=VALUES(`value`)',
                array("manager", intval($_POST['users'])));

            if (intval($_POST['warranty']) > 0) {
                $w = array();
                foreach ($_POST['warranties'] as $m) {
                    if (array_key_exists($m, $warranties)) {
                        $w[$m] = $m;
                    }
                }
                $ar = $this->all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?',
                    array(serialize($w), 'warranties'))->ar();
                if (intval($ar) > 0) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'edit-warranties-add', $mod_id, 0));
                }
            }

            header("Location:" . $_SERVER['REQUEST_URI']);
        }

        $goods = $this->goods;

        // строим таблицу доступных товаров
        $goods_html = '<div class="tabbable">
            <div class="clearfix nav-tabs">
                <ul class="nav nav-tabs pull-left" style="border-bottom:0">
                    <li class="active"><a data-toggle="tab"  href="#goods">' . l('Товары') . '</a></li>'
                . (($this->all_configs['configs']['no-warranties'] == false) ?
                    '<li><a data-toggle="tab"  href="#settings">' . l('Настройки') .'</a></li>'
                    : '') . ''
                . ($this->all_configs['oRole']->hasPrivilege('export-goods') ?
                    '<li><a data-toggle="tab" href="#exports">' . l('Экспорт') . '</a></li>'
                    : '' ) .
                '
                </ul>
                <div class="pull-right">
                    <form class="pull-left m-r-xs" method="post">
                        <div class="input-group" style="width:250px">
                            <input class="form-control" name="text" type="text" value="' . (isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '') . '" />
                            <span class="input-group-btn">
                                <input type="submit" name="search" value="' . l('Поиск') . '" class="btn" />
                            </span>
                        </div>
                    </form>
        ';
        if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
            $goods_html .= '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create" class="btn btn-success pull-right">' . l('Добавить товар') . '</a>'; //<a href="" class="btn btn-danger">Удалить</a>
        }
        $goods_html .= '
                </div>
            </div>
            <div class="tab-content"><div id="goods" class="tab-pane active">';

        if (count($goods) > 0) {
            // узнаем количество страниц доступных товаров
            $count_page = ceil($this->count_goods / $this->count_on_page);

            // проверяем сортировку
            //$sort = '';
            $sort_id = '<a href="?sort=rid">ID';
            $sort_title = '<a href="?sort=title">' . l('Название продукта') . '';
            $sort_price = '<a href="?sort=price">' . l('Цена');
            $sort_date = '<a href="?sort=date">'.l('Дата').'';
            $sort_avail = '<a href="?sort=avail">' . l('Вкл.');
            if (isset($_GET['sort'])) {
                switch ($_GET['sort']) {
                    case 'id':
                        $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rid':
                        $sort_id = '<a href="?sort=id">ID<i class="glyphicon glyphicon-chevron-up"></i>';
                        break;
                    case 'title':
                        $sort_title = '<a href="?sort=rtitle">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rtitle':
                        $sort_title = '<a href="?sort=title">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        break;
                    case 'price':
                        $sort_price = '<a href="?sort=rprice">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rprice':
                        $sort_price = '<a href="?sort=price">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        break;
                    case 'date':
                        $sort_date = '<a href="?sort=rdate">'.l('Дата').'<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rdate':
                        $sort_date = '<a href="?sort=date">'.l('Дата').'<i class="glyphicon glyphicon-chevron-up"></i>';
                        break;
                    case 'avail':
                        $sort_avail = '<a href="?sort=ravail">' . l('Вкл.') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'ravail':
                        $sort_avail = '<a href="?sort=avail">' . l('Вкл.') .'<i class="glyphicon glyphicon-chevron-up"></i>';
                        break;
                }
            } else {
                $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
            }

            $quick_edit_title = '';
            // быстрое редактирование
            if (isset($_GET['edit']) && !empty($_GET['edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                    $goods_html .= '<form method="POST">';
                }
                if ($_GET['edit'] == 'ym_id')
                    $quick_edit_title = 'yandex market ID';
                if (($_GET['edit'] == 'price' || $_GET['edit'] == 'active_price') && $this->all_configs['oRole']->hasPrivilege('external-marketing'))
                    $quick_edit_title = l('Цена');
            }
            $goods_html .= '<table class="table table-striped"><thead><tr>';
            $goods_html .= '<td>' . $sort_id . '</a></td>';
            $goods_html .= '<td>' . $sort_title . '</a></td><td colspan="2">';
            if ($this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                $goods_html .= '<div class="btn-group">';
                $goods_html .= '<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="glyphicon glyphicon-wrench"></i></a>';
                $goods_html .= '<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">';
                if ($this->all_configs['oRole']->hasPrivilege('external-marketing')) {
                    $goods_html .= '<li ' . (isset($_GET['edit']) && $_GET['edit'] == 'active_price' ? 'class="active"' : '') . '>';
                    $goods_html .= '<a tabindex="-1" href="?edit=active_price&' . get_to_string('edit') . '">' . l('Редактирование цены и активности') . '</a></li>';
                }
                $goods_html .= '<li class="divider"></li><li ' . (!isset($_GET['edit']) ? 'class="active"' : '') . '>';
                $goods_html .= '<a tabindex="-1" href="' . $this->all_configs['prefix'] . 'products">' . l('Стандартный вид') . '</a></li>';
                $goods_html .= '</ul></div>';
            }
            $goods_html .= '</td><td>' . $quick_edit_title . '</td>';
            $goods_html .= '<td>' . $sort_avail . '</a></td>';
            $goods_html .= '<td>' . $sort_price . '</a></td>';
            $goods_html .= '<td>' . $sort_date . '</a></td>';
            $goods_html .= '<td title="' . l('Общий остаток') . '">' . l('Общ') . '</td><td title="' . l('Свободный остаток') . '">' . l('Своб') . '</td>';
            $goods_html .= '</tr></thead><tbody>';

            $serials = array();
            $data = $this->all_configs['db']->query(
                'SELECT i.goods_id, w.title as wh_title, t.location, COUNT(i.goods_id) as `count`
                FROM {warehouses_goods_items} as i, {warehouses} as w, {warehouses_locations} as t
                WHERE w.id=i.wh_id AND w.consider_all=?i AND t.id=i.location_id AND i.goods_id IN (?li)
                GROUP BY w.id, t.id, i.goods_id', array(1, array_keys($goods)))->assoc();

            if ($data) {
                foreach ($data as $i) {
                    $serials[$i['goods_id']] = (isset($serials[$i['goods_id']]) ? $serials[$i['goods_id']] : '') . htmlspecialchars($i['wh_title']) . ' - ' . htmlspecialchars($i['location']) . ' - ' . $i['count'] . '<br />';
                }
            }

            foreach ($goods as $id=>$good) {
                $edit = ''; // быстрое редактирование
                $price_icon = ''; // нет цены
                $image_icon = ''; // нет картинки
                $avail = $good['avail'];
                $qty_store = intval($good['qty_store']);

                // быстрое редактирование
                if (isset($_GET['edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                    // редактирование цены
                    if (($_GET['edit'] == 'price' || $_GET['edit'] == 'active_price') && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
                        //$disabled_row = $good['hotline_flag'] == 0 ? '' : 'disabled';
                        $edit = '<input class="input-small" onkeydown="return isNumberKey(event, this)" type="input" name="price[';
                        $edit .= $good['id'] . ']" value="' . number_format($good['price'] / 100, 2, '.', '') . '" />';
                    }
                    // редактирование активности
                    if (($_GET['edit'] == 'set_active' || $_GET['edit'] == 'active_price')) {
                        $avail = '<div class="edit_active"><label class="checkbox"><input value="1" ' . ($good['avail'] == 1 ? 'checked' : '') . ' name="avail[' . $good['id'] . ']" type="radio" />' . l('Вкл') . '</label>';
                        $avail .= '<label class="checkbox"><input value="0" ' . ($good['avail'] == 1 ? '' : 'checked') . ' name="avail[' . $good['id'] . ']" type="radio" />' . l('Выкл') . '</label></div>';
                    }
                    // редактирование свободного остатка
                    if (($_GET['edit'] == 'set_active' || $_GET['edit'] == 'active_price')
                        && $this->all_configs['configs']['erp-use'] == false && $this->all_configs['configs']['onec-use'] == false) {
                        $qty_store = '<input class="input-mini" onkeydown="return isNumberKey(event)" type="input" name="qty_store[';
                        $qty_store .= $good['id'] . ']" value="' . intval($good['qty_store']) . '" />';
                    }
                }
                if ($good['image_set'] == 1)
                    $image_icon = '<i class="glyphicon glyphicon-picture"></i>';
                if (intval($good['price']) > 0)
                    $price_icon = '<i class="glyphicon glyphicon-shopping-cart"></i>';

                $add_name = '';

                $img = '';
                if (array_key_exists('image', $good)) {
                    $path_parts = full_pathinfo($good['image']);
                    $image = $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'];
                    $url = $this->all_configs['siteprefix'] . $this->all_configs['configs']['goods-images-path'] .  $good['id'] . '/' . $image;
                    $img = ' <img src="' . $url . '">';
                }

                $content = '<i class="glyphicon glyphicon-move popover-info" data-content="' . (isset($serials[$id]) ? $serials[$id] : l('Нет на складе')) . '" data-original-title=""></i>';
                $goods_html .= '<tr>
                    <td class="small_ids">' . $good['id'] . $img . '</td>
                    <!--<td><a href="' . $this->all_configs['siteprefix'] . htmlspecialchars($good['url']) . '/' . $this->all_configs['configs']['product-page'] . '/' . $good['id'] . '/"><i class="glyphicon glyphicon-eye-open"></i></a></td>
                        -->
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $good['id'] . '/">' . htmlspecialchars($good['title']) . $add_name . '</a> ' . $content . '</td>
                    <td>' . $image_icon . '</td>
                    <td><!--' . $price_icon . '--></td>
                    <td>' . $edit . '</td>
                    <td>' . $avail . '</td>
                    <td>' . number_format($good['price'] / 100, 2, ',', ' ') . '</td>
                    <td><span title="' . do_nice_date($good['date_add'], false) . '">' . do_nice_date($good['date_add']) . '</span></td>
                    <td>' . intval($good['qty_wh']) . '</td><td>' . $qty_store . '</td>
                </tr>';
            }

            $goods_html .= '</tbody></table>';

            // быстрое редактирование
            if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                $goods_html .= '<input type="submit" name="quick-edit" value="'.l('Сохранить').'" class="btn quick-edit-save_btn" />';
                $goods_html .= '</form>';
            }

            // строим блок страниц
            $goods_html .= page_block($count_page, $this->count_goods);
            // строим блок настроек гарантии
            if ($this->all_configs['configs']['no-warranties'] == false) {
                $goods_html .= '</div><div id="settings" class="tab-pane">';
                if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
                    $goods_html .= '<form method="post">';
                    $goods_html .= '<h4>' . l('При добавлении нового товара будут автоматически добавленны такие настройки') .':</h4>';

                    $is_warranty = array_key_exists('warranty', $this->all_configs['settings'])
                    && $this->all_configs['settings']['warranty'] > 0 ? true : false;
                    $goods_html .= '<div class="control-group"><label class="control-label">' . l('Гарантии') .': </label><div class="controls">';
                    $goods_html .= '<label class="radio"><input onclick="$(\'.default-warranty\').prop(\'disabled\', true);" ';
                    $goods_html .= ($is_warranty ? '' : 'checked') . ' type="radio" name="warranty" value="0">' . l('Без гарантий') .'</label>';
                    $goods_html .= '<label class="radio"><input onclick="$(\'.default-warranty\').prop(\'disabled\', false);" ';
                    $goods_html .= ($is_warranty ? 'checked' : '') . ' type="radio" name="warranty" value="1">' . l('С гарантиями') .'</label>';
                    $goods_html .= '<div class="well">';
                    $config_warranties = array_key_exists('warranties', $this->all_configs['settings']) ?
                        (array)unserialize($this->all_configs['settings']['warranties']) : array();

                    foreach ($warranties as $m => $warranty) {
                        $goods_html .= '<label class="checkbox">' . $m . ' '. l('мес') . '';
                        $goods_html .= '<input class="default-warranty" type="checkbox" value="' . $m . '" ';
                        $goods_html .= (array_key_exists($m, $config_warranties) ? ' checked ' : '');
                        $goods_html .= ($is_warranty ? '' : ' disabled ') . ' name="warranties[]"></label>';
                    }
                    $goods_html .= '</div></div></div>';
                    $goods_html .= '<div class="control-group"><label class="control-label">' . l('manager') . ': </label>';
                    $goods_html .= '<div class="controls"><select class="multiselect input-small" name="users">';
                    // проверка на количество менеджеров у товара
                    $managers = $this->get_managers();

                    if ($managers && count($managers) > 0) {
                        $m = array_key_exists('manager', $this->all_configs['settings'])
                            ? $this->all_configs['settings']['manager'] : $_SESSION['id'];

                        foreach ($managers as $manager) {
                            $goods_html .= '<option value="' . $manager['id'] . '"';
                            $goods_html .= $manager['id'] == $m ? ' selected ' : '';
                            $goods_html .= '>' . $manager['login'] . '</option>';
                        }
                    }
                    $goods_html .= '</select></div></div>';

                    $goods_html .= '<div class="control-group"><div class="controls">';
                    $goods_html .= '<input type="submit" value="'.l('Сохранить').'" name="default-add-product" class="btn btn-primary" />';
                    $goods_html .= '</div></div></form>';
                } else {
                    $goods_html .= '<p  class="text-error">' . l('У Вас нет прав для добавления новых товаров') .'</p>';
                }
            }
        } else {
            $goods_html .= '<p class="text-error">' . l('Нет ни одного продутка') .'</p>';
        }
        $goods_html .= '</div>';

        // экспорт товаров
        if ($this->all_configs['oRole']->hasPrivilege('export-goods')) {
            $goods_html .= '<div id="exports" class="tab-pane">';
            include_once __DIR__ . '/exports.php';
            $goods_html .= product_exports_form($this->all_configs);
            $goods_html .= '</div>';
        }
        $goods_html .= '</div></div>';

        return $goods_html;
    }

    /**
     * @return mixed
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
        $user_id = $this->getUserId();
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        if($act == 'create_form'){
            $form = $this->create_product_form(true, isset($_GET['service']) ? true : false);
            echo json_encode(array('state' => true, 'html' => $form));
            exit;
        }

        if($act == 'create_new'){
            $_POST['create-product'] = true;
            $create = $this->check_post($_POST);
            if(!empty($create['error'])){
                echo json_encode(array('state' => false, 'msg' => $create['error']));
            }else{
                echo json_encode(array('state' => true, 'id' => $create['id'], 'name' => $_POST['title']));
            }
            exit;
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    echo json_encode(array('html' =>  $function['html'], 'state' => true, 'functions' => $function['functions']));
                } else {
                    echo json_encode(array('message' => l('Не найдено'), 'state' => false));
                }
                exit;
            }
        }

        // управление заказами поставщика
        if ($act == 'so-operations') {
            $this->all_configs['suppliers_orders']->operations(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // создаем заказ поставщику
        if ($act == 'create-supplier-order') {
            $_POST['goods-goods'] = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : 0;
            $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $_POST);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($data);
            exit;
        }

        // экспорт товаров
        if ($act == 'exports-goods' && $this->all_configs['oRole']->hasPrivilege('export-goods')) {
            include_once __DIR__ . '/exports.php';
            $ids = $this->get_goods_ids();
            exports_goods($this->all_configs, $ids);
        }

        // новый раздел сопутствующих товаров
        if ($act == 'goods-section') {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('У Вас недостаточно прав'), 'error' => true));
                exit;
            }
            if (!isset($this->all_configs['arrequest'][2])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Произошла ошибка'), 'error' => true));
                exit;
            }
            $cats = $this->all_configs['db']->query('SELECT category_id FROM {category_goods} WHERE goods_id=?i',
                array($this->all_configs['arrequest'][2]))->vars();

            if (!$cats) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Товар должен находится в категории'), 'error' => true));
                exit;
            }
            foreach ($cats as $k=>$cat_id) {
                if ($cat_id > 0) {
                    if (isset($_POST['del']) && $_POST['del'] == 1) {
                        $this->all_configs['db']->query('DELETE FROM {related_sections} WHERE category_id=?i AND name=?',
                            array($cat_id, trim($_POST['name'])));
                    } else {
                        $this->all_configs['db']->query('INSERT IGNORE INTO {related_sections} (category_id, name) VALUES (?i, ?)',
                            array($cat_id, trim($_POST['name'])));
                    }
                }
            }

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Успешно создана')));
            exit;
        }

        // форма раздела
        if ($act == 'section-form') {
            $data['state'] = true;
            $data['content'] = '<form method="post">';
            if (isset($_POST['object_id']) && $_POST['object_id'] == 'del') {
                $sections = null;
                // достаем все категории в которых лежит товар
                $product_categories = $this->all_configs['db']->query('SELECT cg.category_id, c.title
                        FROM {categories} as c, {category_goods} as cg WHERE cg.goods_id=?i AND c.id=cg.category_id',
                    array($this->all_configs['arrequest'][2]))->vars();
                if (count($product_categories) > 0) {
                    $sections = $this->all_configs['db']->query('SELECT name, id FROM {related_sections}
                        WHERE category_id IN (?li) GROUP BY name', array(array_keys($product_categories)))->assoc();
                }

                $data['content'] .= '<select id="goods_section_name"><option value="">' . l('Выберите') . '</option>';
                if (is_array($sections)) {
                    foreach ($sections as $section) {
                        $data['content'] .= '<option value="' . htmlspecialchars($section['name']) . '">' . htmlspecialchars($section['name']) . '</option>';
                    }
                }
                $data['content'] .= '</select>';
                $data['btns'] = '<input type="button" value="' . l('Удалить') . '" class="btn btn-danger" onclick="goods_section(this, 1)" />';
            } else {
                $data['content'] .= '<input type="text" id="goods_section_name" value="" placeholder="' . l('новый раздел') .'" />';
                $data['btns'] = '<input type="button" value="' . l('Создать') . '" class="btn btn-success" onclick="goods_section(this, 0)" />';
            }
            $data['content'] .= '</form>';
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($data);
            exit;
        }

        // перемещаем изделие
        if ($act == 'move-item') {
            $data = $this->all_configs['chains']->move_item_request($_POST, $mod_id);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($data);
            exit;
        }

        // добавляем аналогичный
        if ($act == 'context') {
            if (!isset($_POST['provider']) || !isset($this->all_configs['configs']['api-context'][$_POST['provider']])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Неизвестный провайдер')));
                exit;
            }
            if (!isset($_POST['goods_id']) || $_POST['goods_id'] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Неизвестный товар')));
                exit;
            }
            if (!isset($this->all_configs['settings'][$this->all_configs['configs']['api-context'][$_POST['provider']]['avail']])
                || $this->all_configs['settings'][$this->all_configs['configs']['api-context'][$_POST['provider']]['avail']] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Модуль отключен')));
                exit;
            }
            require_once $this->all_configs['sitepath'] . 'shop/context.class.php';
            $context = new context_class($this->all_configs);
            // set provider
            $context->set_provider($_POST['provider']);

            // get campaign
            $campaign = $context->get_campaign($_POST['goods_id'], true);
            if ($campaign && array_key_exists($_POST['provider'], $campaign)) {
                $status = key($campaign[$_POST['provider']]['items']);
                $campaign_id = key($campaign[$_POST['provider']]['items'][$status]);
                // update campaign
                $data = $context->update_ads($campaign[$_POST['provider']]['items'][$status][$campaign_id]);
            } else {
                $data['message'] = l('Не хватает данных');
            }
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($data);
            exit;
        }

        // добавляем аналогичный
        if ($act == 'add-similar') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
                && array_key_exists('product_id', $_POST) && $_POST['product_id'] > 0) {

                $sim = $this->all_configs['db']->query('SELECT id FROM {goods_similar} WHERE (first=?i AND second=?i)
                        OR (first=?i AND second=?i)',
                    array($this->all_configs['arrequest'][2], $_POST['product_id'],
                        $_POST['product_id'], $this->all_configs['arrequest'][2]))->el();

                if (!$sim) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {goods_similar}
                            (first, second, second_prio) VALUES (?i, ?i, ?i)',
                        array($_POST['product_id'], $this->all_configs['arrequest'][2], 0));
                }
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('state' => true));
                exit;
            }
        }

        // добавляем сопутствующий
        if ($act == 'add-related') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
                && array_key_exists('product_id', $_POST) && $_POST['product_id'] > 0) {

                $related = $this->all_configs['db']->query('SELECT related FROM {goods} WHERE id=?i',
                    array($this->all_configs['arrequest'][2]))->el();
                $related = $related ? unserialize($related) : array();
                $related[$_POST['product_id']] = 0;

                $this->all_configs['db']->query('INSERT IGNORE INTO {goods_related} (goods_id, related_id) VALUES (?i, ?i)',
                    array($this->all_configs['arrequest'][2], $_POST['product_id']));

                $this->all_configs['db']->query('UPDATE {goods} SET related=? WHERE id=?i',
                    array(serialize($related), $this->all_configs['arrequest'][2]));

                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('state' => false));
                exit;
            }
        }

        if ($act == 'upload_picture_for_goods') {

            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                return false;
            }

            require_once 'class_qqupload.php';

            if (!isset($_GET['product']) || $_GET['product'] == 0) {
                return false;
            }
            $product = $this->all_configs['db']->query('SELECT secret_title, id FROM {goods} WHERE id=?i', array($_GET['product']))->row();
            if (!$product) {
                return false;
            }

            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
            // max file size in bytes
            $sizeLimit = 100 * 1024 * 1024;
            $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

            $dir = $this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $_GET['product'] . '/';
            if (!is_dir($dir)) {
                if (mkdir($dir)) {
                    chmod($dir, 0777);
                } else {
                    return false;
                }
            }
            $result = $uploader->handleUpload($dir);
            require_once $this->all_configs['sitepath'] . 'shop/watermark.class.php';

            if ($result['success'] == true) {

                $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i', array(1, $product['id']));
                // делаем уменьшеные копии картинок
                if (isset($this->all_configs['configs']['images-sizes']) && count($this->all_configs['configs']['images-sizes']) > 0) {
                    require_once($this->all_configs['sitepath'] . 'shop/resize_img.class.php');
                    $path_parts = full_pathinfo($result['filename']);
                    $image = new SimpleImage();
                    $first = 1;
                    foreach ($this->all_configs['configs']['images-sizes'] as $size_prefix => $size) {
                        $image->load($dir . $result['filename']);

                        if ($image->getHeight() <= $image->getWidth())
                            $image->resizeToWidth($size);
                        else
                            $image->resizeToHeight($size);
                        $image->save($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension'], exif_imagetype($dir . $result['filename']));

                        // водяной знак только большей картинке
                        if ($first == 1 && isset($_GET['watermark']) && $_GET['watermark'] == 'true' && $this->all_configs['configs']['set_watermark'] == true) {
                            $watermark = new Watermark($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension']);
                            $watermark->setWatermarkImage($this->all_configs['sitepath'] . 'images/watermark_small.png');
                            $watermark->setType(Watermark::BOTTOM_CENTER);
                            $watermark->saveAs($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension']);
                        }
                        // копируем картинку всем аналогичным товарам по secret_title
                        if (isset($_GET['oist']) && $_GET['oist'] == 'true' && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen(trim($product['secret_title']), 'UTF-8') > 0) {
                            $related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i', array(trim($product['secret_title']), $_GET['product']))->assoc();
                            if ($related && count($related) > 0) {
                                foreach ($related as $r) {
                                    $dir1 = $this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $r['id'] . '/';
                                    if (!is_dir($dir1)) {
                                        if (mkdir($dir1)) {
                                            chmod($dir1, 0777);
                                        } else {
                                            return false;
                                        }
                                    }
                                    copy($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension'],
                                        $dir1 . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension']);
                                }
                            }
                        }
                        $first++;
                    }
                }

                // водяной знак оригиналу картинки
                if (isset($_GET['watermark']) && $_GET['watermark'] == 'true' && $this->all_configs['configs']['set_watermark'] == true) {
                    $watermark = new Watermark($dir . $result['filename']);
                    $watermark->setWatermarkImage($this->all_configs['sitepath'] . 'images/watermark.png');
                    $watermark->setType(Watermark::BOTTOM_CENTER);
                    $watermark->saveAs($dir . $result['filename']);
                }

                $img_id = $this->all_configs['db']->query('INSERT IGNORE INTO {goods_images} (image, goods_id, type) VALUE (?, ?i, ?i)',
                    array($result['filename'], intval($_GET['product']), 1), 'id');
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'add-image-goods', $mod_id, intval($_GET['product'])));

                // копируем картинку всем аналогичным товарам по secret_title
                if (isset($_GET['oist']) && $_GET['oist'] == 'true' && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen(trim($product['secret_title']), 'UTF-8') > 0) {
                    $related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i', array(trim($product['secret_title']), $_GET['product']))->assoc();
                    $this->copy_image_from_product_to_products($related, $dir, $result['filename'], $user_id, $mod_id);
                }
                $result['img_id'] = $img_id;

                // заливаем фотки по товарам в группе размеров
                if ($this->all_configs['configs']['group-goods']) {
                    $size_group_goods = $this->all_configs['db']->query(
                        "SELECT goods_id as id FROM {goods_groups_size_links}"
                        ."WHERE group_id = (SELECT group_id FROM {goods_groups_size_links} "
                        ."WHERE goods_id = ?i LIMIT 1) "
                        . "AND goods_id != ?i", array($_GET['product'],$_GET['product']), 'assoc');
                    $this->copy_image_from_product_to_products($size_group_goods, $dir, $result['filename'], $user_id, $mod_id);
                }
            }

            $data = htmlspecialchars(json_encode($result), ENT_NOQUOTES);

        }

        // форма принятия заказа поставщику
        if ($act == 'form-accept-so') {
            $this->all_configs['suppliers_orders']->accept_form();header("Content-Type: application/json; charset=UTF-8");
            exit;
        }

        // удаление заказа поставщика
        if ($act == 'remove-supplier-order') {
            $this->all_configs['suppliers_orders']->remove_order($mod_id);
            exit;
        }

        // заявки
        if ($act == 'orders-link') {
            $so_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
            $co_id = isset($_POST['so_co']) ? $_POST['so_co'] : 0;
            $data = $this->all_configs['suppliers_orders']->orders_link($so_id, $co_id);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($data);
            exit;
        }

        // принятие заказа
        if ($act == 'accept-supplier-order') {
            $this->all_configs['suppliers_orders']->accept_order($mod_id, $this->all_configs['chains']);
            exit;
        }

        if ($act == 'new_market_category') {

            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                return false;
            }

            if (!isset($_GET['name'])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Введите имя'), 'error' => true));
                exit;
            }
            if (!isset($_GET['market_id'])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Произошла ошибка'), 'error' => true));
                exit;
            }
            try {
                $id = $this->all_configs['db']->query('INSERT INTO {exports_markets_categories} (title,market_id) VALUES (?,?i)', array($_GET['name'], $_GET['market_id']), 'id');
            } catch (Exception $e) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Произошла ошибка'), 'error' => true));
                exit;
            }

            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-market-category', $mod_id, $id));

            $result = $id;
            $data = htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        }

        if (isset($_POST['act']) && $_POST['act'] == 'hotline' && $this->all_configs['oRole']->hasPrivilege('parsing')) {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('У Вас недостаточно прав'), 'error' => true));
                exit;
            }
            include($this->all_configs['sitepath'] . 'hotlineparse.php');

            if (!isset($_POST['hotline_url']) || empty($_POST['hotline_url'])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Заполните ссылку на hotline'), 'error' => true));
                exit;
            }
            if (!isset($_POST['goods_id']) || empty($_POST['goods_id'])) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Попробуйте еще раз'), 'error' => true));
                exit;
            }

            $prices = build_hotline_url(array('hotline_url' => $_POST['hotline_url'], 'goods_id' => $_POST['goods_id']),
                                        getCourse($this->all_configs['settings']['currency_suppliers_orders']), $this->all_configs['configs']);

            if (!$prices) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Неправильная ссылка'), 'error' => true));
                exit;
            }

            // записываем в бд
            $this->all_configs['db']->query('DELETE FROM {goods_hotline_prices} WHERE goods_id=?i',
                array($_POST['goods_id']));
            $this->all_configs['db']->query('INSERT INTO {goods_hotline_prices}
                (`price`, `shop`, `goods_id`, `number_list`, `date_add`) VALUES ?v', array($prices));

            $val = $this->all_configs['db']->query('SELECT e.*, g.price
                    FROM {goods_extended} as e, {goods} as g WHERE g.id=?i AND g.id=e.goods_id',
                array($_POST['goods_id']))->row();

            $msg = '';
            // обновление цены
            if ($val && $val['hotline_flag'] == 1) {
                $price = $val['price'];

                if ( $val['hotline_number_list_flag'] == 1 ) {
                    if ( $val['hotline_number_list'] > 0 ) {
                        foreach ( $prices as $hv ) {
                            if ( $hv['number_list'] == $val['hotline_number_list'] ) {
                                $price = $hv['price'];
                                break;
                            }
                        }
                    } else {
                        $price = $prices[0]['number_list'];
                    }
                } elseif ( $val['hotline_shop_flag'] == 1 ) {
                    foreach ( $prices as $hv ) {
                        if ( $hv['shop'] == $val['hotline_shop'] ) {
                            $price = $hv['price'];
                            break;
                        }
                    }
                }

                if ( $val['purchase_flag'] == 1 && $price < ($val['price_purchase']+$val['purchase'])) {
                    if ( $val['price_purchase'] == 0 ) {
                        $price = $val['price'];

                    } else {
                        $price = ($val['price_purchase'] + $val['purchase']);
                    }
                }
                $price -= (intval($val['hotline_less']) * 100);

                $ar = $this->all_configs['db']->query('UPDATE {goods} SET `price`=? WHERE id=?i',
                    array($price, $_POST['goods_id']))->ar();
                if ($ar) {
                    $msg = l('Цена товара изменена.');
                }
            }

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array(/*'table' => $out, */'message' => l('Цены успешно загружены.') . $msg));
            exit;
        }

        if (isset($_POST['act']) && $_POST['act'] == 'export_product' && $this->all_configs['configs']['onec-use'] == true) {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('У Вас недостаточно прав'), 'error' => true));
                exit;
            }
            if (!isset($_POST['goods_id']) || $_POST['goods_id'] < 1) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого товара не существует'), 'error' => true));
                exit;
            }

            $this->export_product_1c($_POST['goods_id']);

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Товар успешно выгружен')));
            exit;

        }
        if (isset($_POST['act']) && $_POST['act'] == 'goods_add_size_group') {
            $data = array();
            $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : 0;
            if ($group_id) {
                $product_id = $this->all_configs['db']->query("SELECT goods_id FROM {goods_groups_size_links} "
                    ."WHERE group_id = ?i LIMIT 1", array($group_id), 'el');
                $product = $this->all_configs['db']->query("SELECT id, price / 100 as price, title, article, content "
                    ."FROM {goods} WHERE id = ?i", array($product_id), 'row');
                $data['state'] = true;
                $model = new Model($this->all_configs['db'], $this->all_configs['configs']);
                $filters = $this->all_configs['db']->query('SELECT nv.*, fv.*
                    FROM {filter_name_value} as nv, {filter_value} as fv
                    WHERE nv.fname_id=?i AND nv.fvalue_id=fv.id AND fv.value != ""
                    ORDER BY fv.value
                ', array($model->fname_id_sizes))->assoc();
                $filters_list = '';
                foreach ($filters as $filter) {
                    $filters_list .= '<option value="'.$filter['id'].':'.$filter['value'].'">'.$filter['value'].'</option>';
                }
                $data['size_select'] =
                    '<div class="control-group" id="group_size_select">'.
                    '<input name="size_group_goods_id" type="hidden" value="'.$product['id'].'">'.
                    '<label class="control-label">' . l('Размер') .': </label>'.
                    '<div class="controls">'.
                    '<select name="g_size" class="size">'.
                    $filters_list.
                    '</option>'.
                    '</div>'.
                    '</div>'
                ;
                $data['product'] = $product;
                $data['product']['categories'] = $this->all_configs['db']->query("SELECT category_id FROM {category_goods} "
                    ."WHERE goods_id = ?i", array($product_id), 'vars');
            } else {
                $data['state'] = false;
                $data['msg'] = l('Неверный id группы');
            }
            header("Content-Type: application/json; charset=UTF-8");
            echo $data;//json_encode($data);
            exit;
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo $data; //json_encode($data);
        exit;
    }

    /**
     * @param $products
     * @param $dir
     * @param $filename
     * @param $user_id
     * @param $mod_id
     * @return bool
     */
    function copy_image_from_product_to_products($products, $dir, $filename, $user_id, $mod_id)
    {
        if ($products && count($products) > 0) {
            foreach ($products as $r) {
                $dir1 = $this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $r['id'] . '/';
                if (!is_dir($dir1)) {
                    if (mkdir($dir1)) {
                        chmod($dir1, 0777);
                    } else {
                        return false;
                    }
                }
                if (copy($dir . $filename, $dir1 . $filename)) {
                    $path_parts = full_pathinfo($filename);
                    if (file_exists($dir . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'])) {
                        copy($dir . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'],
                            $dir1 . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension']);
                    }
                    if (file_exists($dir . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension'])) {
                        copy($dir . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension'],
                            $dir1 . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension']);
                    }
                    // сама картинки
                    $this->all_configs['db']->query('INSERT IGNORE INTO {goods_images} (image, goods_id, type) VALUE (?, ?i, ?i)',
                        array($filename, $r['id'], 1), 'id');
                    // флаг наличия картинки
                    $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i', array(1, $r['id']));
                    // история
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'add-image-goods', $mod_id, $r['id']));
                }
            }
        }
    }

    /**
     * @param $product_id
     */
    function export_product_1c($product_id)
    {
        $uploaddir = $this->all_configs['sitepath'] . '1c/goods/';

        if (!is_dir($uploaddir)) {
            if (mkdir($uploaddir)) {
                chmod($uploaddir, 0777);
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Нет доступа к директории ') . $uploaddir, 'error' => true));
                exit;
            }
        }

        $product = $this->all_configs['db']->query('SELECT g.id, g.price, g.code_1c, g.barcode, g.title, g.qty_store as exist, g.price_purchase, g.price_wholesale, g.article, g.avail, g.content, h.hotline_url
                FROM {goods} as g
                LEFT JOIN (SELECT goods_id, hotline_url FROM {goods_extended})h ON h.goods_id=g.id
                WHERE g.id=?i', array($product_id))->row();
        //hotline

        if (!$product) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Такого товара не существует'), 'error' => true));
            exit;
        }

        $this->all_configs['suppliers_orders']->exportProduct($product);

        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($user_id, 'export-order', $mod_id, $product['id']));
    }

    /**
     * @return array
     */
    function products_main()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT title, secret_title, article, code_1c, material, weight,
                    size, id, url, barcode, price_wholesale, price, content, price_purchase, qty_wh, qty_store, prio
                FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            if ($product) {
                $goods_html .= '<form method="post">';
                $goods_html .= '<div class="form-group"><label>' . l('Название') . ': </label>';
                $goods_html .= '<input class="form-control" placeholder="' . l('введите название') . '" name="title" value="';
                if (is_array($this->errors) && array_key_exists('post', $this->errors) && array_key_exists('title', $this->errors['post']))
                    $goods_html .= htmlspecialchars($this->errors['post']['title']);
                else
                    $goods_html .= htmlspecialchars($product['title']);
                $goods_html .= '" /></div>';
                $goods_html .= '<div class="form-group"><label>' . l('Штрих код') .': </label>
                            <input placeholder="' . l('штрих код') .'" class="form-control" name="barcode" value="' . ((is_array($this->errors) && array_key_exists('post', $this->errors) && array_key_exists('title', $this->errors['post'])) ? htmlspecialchars($this->errors['post']['barcode']) : $product['barcode']) . '" /></div>';
                $goods_html .= '<div class="form-group"><label>' . l('Приоритет') . ': </label>
                            <input onkeydown="return isNumberKey(event)" class="form-control" name="prio" value="' . ((is_array($this->errors) && array_key_exists('post', $this->errors) && array_key_exists('prio', $this->errors['post'])) ? htmlspecialchars($this->errors['post']['prio']) : $product['prio']) . '" /></div>';
                $goods_html .= '<div class="form-group"><label>' . l('Розничная цена') .' ('.viewCurrency('shortName').'): </label>
                            ' . number_format($product['price'] / 100, 2, '.', ' ') . '</div>';
                $goods_html .= '<div class="form-group"><label>' . l('Закупочная цена последней партии') .' ('.viewCurrencySuppliers('shortName').'): </label>
                            ' . number_format($product['price_purchase'] / 100, 2, '.', ' ') . '</div>';
                $goods_html .= '<div class="form-group"><label>' . l('Оптовая цена') .' ('.viewCurrency('shortName').'): </label>
                            ' . number_format($product['price_wholesale'] / 100, 2, '.', ' ') . '</div>';
                $goods_html .= '<div class="form-group"><label>' . l('Свободный остаток') .':</label>
                            ' . intval($product['qty_store']) . '</div>';
                $goods_html .= '<div class="form-group"><label>' . l('Общий остаток') .':</label>
                            ' . intval($product['qty_wh']) . '</div>';
                $goods_html .= $this->btn_save_product('main');
                $goods_html .= '</form>';
            }
        }

        return array(
            'html' => $goods_html,
            'functions' => array('tiny_mce()'),
        );
    }

    /**
     * @return array
     */
    function products_additionally()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT type, avail FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            if ($product) {
                // достаем все категории в которых лежит товар
                $product_categories = $this->all_configs['db']->query('SELECT cg.category_id, c.title FROM {categories} as c, {category_goods} as cg
                  WHERE cg.goods_id=?i AND c.id=cg.category_id', array($this->all_configs['arrequest'][2]))->assoc();

                $cat_for_goods = array();
                $categories_html = '';
                foreach ($product_categories as $product_c) { //echo $product['title'];
                    $cat_for_goods[$product_c['category_id']] = $product_c['title'];
                    $categories_html .= '<tr><td>' . $product_c['category_id'] . '</td>';
                    $categories_html .= '<td>' . htmlspecialchars($product_c['title']) . '</td>';
                    $categories_html .= '<td><label class="checkbox">';
                    $categories_html .= '<input type="checkbox" name="del-cat[' . $product_c['category_id'] . ']" /></label></td></tr>';
                }

                $checked = '';
                if ($product['avail'] == 1)
                    $checked = 'checked';
                $goods_html .= '<form method="post" style="max-width:300px">';
                $goods_html .= '<div class="form-group"><div class="checkbox">';
                $goods_html .= '<label><input name="avail" ' . $checked . ' type="checkbox">' . l('Активность') . '</label></div></div>';
                $checked = '';
                if ($product['type'] == 1)
                    $checked = 'checked';
                $goods_html .= '<div class="form-group"><div class="checkbox">';
                $goods_html .= '<label><input name="type" ' . $checked . ' type="checkbox">' . l('Услуга') . '</label></div></div>';
                $goods_html .= '<div class="form-group"><label>' . l('Категории') . ': </label>';
                $goods_html .= '';
                $goods_html .= '<select class="multiselect form-control" multiple="multiple" name="categories[]">';
                $categories = $this->get_categories();
                $selected_categories = $this->all_configs['db']->query('SELECT cg.category_id, cg.category_id
                        FROM {category_goods} as cg WHERE cg.goods_id=?i',
                    array($this->all_configs['arrequest'][2]))->vars();
                $goods_html .= build_array_tree($categories, array_keys($selected_categories));
                $goods_html .= '</select>';
                $goods_html .= '</div>';
                $goods_html .= $this->btn_save_product('additionally');
                $goods_html .= '</form>';
            }
        }

        return array(
            'html' => $goods_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function products_managers($hash = '#managers-managers')
    {
        if (trim($hash) == '#managers' || (trim($hash) != '#managers-managers' && trim($hash) != '#managers-history'))
            $hash = '#managers-managers';

        $goods_html = '';
        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT id, author FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            if ($product) {
                $goods_html .= '<ul class="nav nav-pills">';
                $goods_html .= '<li><a class="click_tab" data-open_tab="products_managers_managers" onclick="click_tab(this, event)" title="' . l('Уведомления') .'" href="#managers-managers">' . l('Менеджеры') .'</a></li>';
                $goods_html .= '<li><a class="click_tab" data-open_tab="products_managers_history" onclick="click_tab(this, event)" title="' . l('Уведомления') .'" href="#managers-history">' . l('История изменений') . '</a></li>';
                $goods_html .= '</ul><div class="pill-content">';

                $goods_html .= '<div id="managers-managers" class="pill-pane">';
                $goods_html .= '</div>';

                $goods_html .= '<div id="managers-history" class="pill-pane">';
                $goods_html .= '</div>';//</div>
            }
        }
        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function products_managers_managers()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $author = $this->all_configs['db']->query('SELECT login FROM {users} as u, {goods} as g
                WHERE u.id=g.author AND g.id=?i ',
                array($this->all_configs['arrequest'][2]))->el();
            $goods_html .= '<form style="max-width: 300px" method="post">';
            $goods_html .= '<div class="form-group"><label>' . l('Автор') . ': </label>';
            $goods_html .= ' <a href="'
                . $this->all_configs['prefix'] . 'users">' . $author . '</a></div>';
            $goods_html .= '<div class="form-group"><label>' . l('manager') . ': </label>';
            $goods_html .= '<select class="multiselect form-control" ';
            // проверка на количество менеджеров у товара
            $goods_html .= $this->all_configs['configs']['manage-product-managers'] == true ? 'multiple="multiple"' : '';
            $goods_html .= ' name="users[]"><option value="0">' . l('Не выбран') . '</option>';
            $managers = $this->get_managers($this->all_configs['arrequest'][2]);

            if ($managers && count($managers) > 0) {
                foreach ($managers as $manager) {//del-user
                    $goods_html .= '<option value="' . $manager['id'] . '"';
                    $goods_html .= $manager['id'] == $manager['manager'] ? ' selected ' : '';
                    $goods_html .= '>' . $manager['login'] . '</option>';
                }
            }
            $goods_html .= '</select></div>';
            $goods_html .= $this->btn_save_product('managers_managers');
            $goods_html .= '</form>';
        }

        return array(
            'html' => $goods_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function products_managers_history()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $mod_id = $this->all_configs['configs']['products-manage-page'];

            $histories = $this->all_configs['db']->query('SELECT c.date_add, c.work, u.login FROM {changes} as c
                                    LEFT JOIN (SELECT id, login FROM {users})u ON u.id=c.user_id
                                    WHERE c.map_id=?i AND c.object_id=?i ORDER BY c.date_add DESC',
                array($mod_id, $this->all_configs['arrequest'][2]))->assoc();
            if ($histories && count($histories) > 0) {
                $goods_html .= '<table class="table table-striped"><thead><tr><td>' . l('Автор') . '</td><td>' . l('Редактирование') .'</td><td>'.l('Дата').'</td></tr></thead><tbody>';
                foreach ($histories as $history) {
                    $goods_html .= '<tr><td><a href="' . $this->all_configs['prefix'] . 'users">' . $history['login'] . '</a></td>';
                    $goods_html .= '<td>' . $this->all_configs['configs']['changes'][$history['work']] . '</td>';
                    $goods_html .= '<td><span title="' . do_nice_date($history['date_add'], false) . '">' . do_nice_date($history['date_add']) . '</span></td></tr>';
                }
                $goods_html .= '</tbody></table>';
            } else {
                $goods_html .= '<p  class="text-error">' . l('Нет ни одного изменения') .'</p>';
            }
            $goods_html .= '</div>';
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function products_financestock($hash = '#financestock-stock')
    {
        if (trim($hash) == '#financestock' || (trim($hash) != '#financestock-stock' && trim($hash) != '#financestock-finance'))
            $hash = '#financestock-stock';

        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $goods_html .= '<ul class="nav nav-pills">';
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_financestock_stock" onclick="click_tab(this, event)" title="' . l('Склады') .'" href="#financestock-stock">' . l('Склады') . '</a></li>';
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_financestock_finance" onclick="click_tab(this, event)" title="' . l('Заказы поставщикам') .'" href="#financestock-finance">' . l('Заказы поставщикам') .'</a></li>';
            $goods_html .= '</ul><div class="pill-content">';

            $goods_html .= '<div id="financestock-main" class="pill-pane">';
            $goods_html .= '</div><!--#financestock-main-->';

            // склады
            $goods_html .= '<div id="financestock-stock" class="pill-pane">';
            $goods_html .= '</div><!--#financestock-stock-->';

            // заказы поставщикам
            $goods_html .= '<div id="financestock-finance" class="pill-pane">';
            $goods_html .= '</div><!--#financestock-finance-->';

            $goods_html .= '</div><!--.pill-content-->';
            //}
        }

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function products_financestock_stock()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $counts = $this->all_configs['db']->query('SELECT w.title, i.wh_id, COUNT(DISTINCT i.id) as qty_wh,
                      SUM(IF (w.consider_store=1 AND i.order_id IS NULL, 1, 0)) - COUNT(DISTINCT l.id) as qty_store
                    FROM {warehouses} as w, {warehouses_goods_items} as i
                    LEFT JOIN {orders_suppliers_clients} AS l ON i.supplier_order_id = l.supplier_order_id
                      AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)
                    WHERE i.goods_id=?i AND w.id=i.wh_id AND w.consider_all=1 GROUP BY i.wh_id',
                array($this->all_configs['arrequest'][2]))->assoc();

            if ($counts) {
                $goods_html .= '<table class="table table-striped"><thead><tr><td>' . l('Склад') . '</td><td>' . l('Общий остаток') .'</td>';
                $goods_html .= '<td>' . l('Свободный остаток') . '</td></tr></thead><tbody>';
                $all_qty_wh = 0;
                $all_qty_store = 0;
                foreach ($counts as $vgw) {
                    $vgw['qty_store'] = $vgw['qty_store'] > 0 ? $vgw['qty_store'] : 0;
                    $all_qty_wh += intval($vgw['qty_wh']);
                    $all_qty_store += intval($vgw['qty_store']);

                    $url = $this->all_configs['prefix'] . 'warehouses?pid=' . $this->all_configs['arrequest'][2] . '&whs=' . $vgw['wh_id'] . '#show_items';
                    $goods_html .= '<tr><td><a href="' . $url . '">' . htmlspecialchars($vgw['title']) . '</a></td>';
                    $goods_html .= '<td>' . intval($vgw['qty_wh']) . '</td>';
                    $goods_html .= '<td>' . intval($vgw['qty_store']) . '</td></tr>';
                }
                $goods_html .= '<tr><td><b>' . l('Всего') .'</b></td><td>' . $all_qty_wh . '</td>';
                $goods_html .= '<td>' . $all_qty_store . '</td></tr></tbody></table>';
            } else {
                $goods_html .= '<p  class="text-error">' . l('Нет информации') .'</p>';
            }

        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function products_financestock_finance()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $goods_html .= '<form class="form-horizontal" method="post">';
            $goods_html .= '<div class="well"><h4>' . l('Склады поставщиков Локально') .'</h4>';
            $goods_suppliers = $this->all_configs['db']->query('SELECT link FROM {goods_suppliers} WHERE goods_id=?i',
                array($this->all_configs['arrequest'][2]))->assoc();
            if ($goods_suppliers) {
                foreach ($goods_suppliers as $product_supplier) {
                    $goods_html .= '<input type="text" name="links[]" placeholder="' . l('гиперссылка') .'" class="form-control" value="' . $product_supplier['link'] . '" />';
                }
            }
            $goods_html .= '<input type="text" name="links[]" placeholder="' . l('гиперссылка') .'" class="form-control" />';
            $goods_html .= '<i class="glyphicon glyphicon-plus cursor-pointer" onclick="$(\'<input>\').attr({type: \'text\', name: \'links[]\', class: \'form-control\'}).insertBefore(this);"></i></div>';
            $goods_html .= $this->btn_save_product('financestock_finance');
            $goods_html .= '</form>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
                $goods_html .= '<div id="accordion_product_suppliers_orders"><div class="panel-group">';
                $goods_html .= '<div class="panel panel-default"><div class="panel-heading">';
                $goods_html .= '<a class="panel-toggle" href="#collapse_create_product_supplier_order" data-parent="#accordion_product_suppliers_orders" data-toggle="collapse">' . l('Создать заказ поставщику') .'</a>';
                $goods_html .= '</div><div id="collapse_create_product_supplier_order" class="panel-body collapse"><div class="accordion-inner">';
                $goods_html .= $this->all_configs['suppliers_orders']->create_order_block();
                $goods_html .= '</div><!--.accordion-inner--></div></div><!--#collapse_create_product_supplier_order--></div><!--.accordion-group--></div><!--#accordion_product_suppliers_orders-->';
            }
            $queries = $this->all_configs['manageModel']->suppliers_orders_query(array('by_gid' => $this->all_configs['arrequest'][2]));
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $queries['count_on_page'];

            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            $goods_html .= '<div class="table-responsive">'.
                                $this->all_configs['suppliers_orders']->show_suppliers_orders($orders).
                           '</div>';

            $count = $this->all_configs['db']->query('SELECT count(id) FROM {contractors_suppliers_orders} WHERE goods_id=?i',
                array($this->all_configs['arrequest'][2]))->el();
            if ($count > 10)
                $goods_html .= '<a href="' . $this->all_configs['prefix'] . 'orders?goods=' . $this->all_configs['arrequest'][2] . '#show_suppliers_orders">' . l('Еще') .'</a>';

        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function products_omt($hash = '#omt-notices')
    {
        $goods_html = '';

        if (trim($hash) == '#omt' || (trim($hash) != '#omt-notices' && trim($hash) != '#omt-procurement' && trim($hash) != '#omt-suppliers')) {
            $hash = '#omt-notices';
        }

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
        ) {

            $goods_html .= '<ul class="nav nav-pills">';
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_omt_notices" onclick="click_tab(this, event)" href="#omt-notices" title="' . l('Уведомления') . '">' . l('Уведомления') . '</a></li>';
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_omt_procurement" onclick="click_tab(this, event)" href="#omt-procurement" title="' . l('Управление закупками') . '">' . l('Упр. закупками') . '</a></li>';
            $goods_html .= '</ul><div class="pill-content">';

            $goods_html .= '<div id="omt-notices" class="pill-pane">';
            $goods_html .= '</div>';

            $goods_html .= '<div id="omt-procurement" class="pill-pane">';
            $goods_html .= '</div>';

            $goods_html .= '</div>';
        }

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function products_omt_notices()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {

            $user = $this->all_configs['db']->query('SELECT * FROM {users_notices} WHERE user_id=?i AND goods_id=?i',
                array($_SESSION['id'], $this->all_configs['arrequest'][2]))->row();
            $checked = '';
            if ($user && $user['each_sale'] == 1) $checked = 'checked';
            $goods_html .= '<form method="post" style="max-width:400px">';
            $goods_html .= '<div class="form-group"><div class="checkbox"><label><input ' . $checked . ' type="checkbox" name="each_sale" /> ' . l('уведомлять меня о каждой продаже этого товара') .'</div></div>';
            $checked = '';
            if ($user && $user['by_balance'] == 1) $checked = 'checked';
            $balance = '';
            if ($user && $user['balance'] > 0) $balance = $user['balance'];
            $goods_html .= '<div class="form-group"><label class="checkbox-inline"><input ' . $checked . ' type="checkbox" name="by_balance" /> уведомлять меня об остатке</label>
                        <div class="input-group"><input placeholder="' . l('количество товаров') . '" value="' . $balance . '" type="text" class="form-control" onkeydown="return isNumberKey(event)" name="balance" /><div class="input-group-addon">' . l('или менее единиц.') . '</div></div>';
            $goods_html .= $this->btn_save_product('omt_notices');
            $goods_html .= '</form>';
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function products_omt_aggregators()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {

            $markets = $this->all_configs['db']->query('SELECT m.market_id, k.title as ctitle, k.id as cid, m.image,
                  g.avail, m.title, g.title1, g.title2, g.content, g.category_id
                FROM {exports_markets} as m
                LEFT JOIN (SELECT title1, title2, goods_id, market_id, avail, category_id, content FROM {exports_markets_goods})g ON g.goods_id=?i AND g.market_id=m.market_id
                LEFT JOIN (SELECT id, title, market_id FROM {exports_markets_categories})k ON k.market_id=m.market_id
                ORDER BY k.title',
                array($this->all_configs['arrequest'][2]))->assoc();

            $aMarkets = array();
            if ($markets && count($markets) > 0) {
                foreach ($markets as $market) {
                    if (array_key_exists($market['market_id'], $aMarkets)) {
                        $aMarkets[$market['market_id']]['categories'][$market['cid']] = $market['ctitle'];
                    } else {
                        $aMarkets[$market['market_id']] = array(
                            'image' => $market['image'],
                            'avail' => $market['avail'],
                            'title' => $market['title'],
                            'title1' => $market['title1'],
                            'title2' => $market['title2'],
                            'content' => $market['content'],
                            'category_id' => $market['category_id'],
                            'categories' => array($market['cid'] => $market['ctitle']));
                    }
                }
                foreach ($aMarkets as $m_id => $aMarket) {
                    $checked = '';
                    $title1 = '';
                    $title2 = '';
                    if ($aMarket['avail'] == 1)
                        $checked = 'checked';
                    if (isset($aMarket['title1']))
                        $title1 = $aMarket['title1'];
                    if (isset($aMarket['title2']))
                        $title2 = $aMarket['title2'];
                    $goods_html .= '<div class="control-group"><label class="control-label">' . htmlspecialchars($aMarket['title']) . '</label><div class="controls">';
                    $goods_html .= '<input ' . $checked . ' class="span5" type="checkbox" name="market-avail[' . $m_id . ']" /></div></div>';
                    $goods_html .= '<div class="control-group"><label class="control-label">' . l('Название') .' ' . htmlspecialchars($aMarket['title']) . '&nbsp;1:</label>';
                    $goods_html .= '<div class="controls"><input class="span5" type="text" name="market-title1[' . $m_id . ']" value="' . htmlspecialchars($title1) . '" /></div></div>';
                    $goods_html .= '<div class="control-group"><label class="control-label">' . l('Название') .' ' . htmlspecialchars($aMarket['title']) . '&nbsp;2:</label>';
                    $goods_html .= '<div class="controls"><input class="span5" type="text" name="market-title2[' . $m_id . ']" value="' . htmlspecialchars($title2) . '" /></div></div>';
                    $goods_html .= '<div class="controls"><textarea rows="5" name="market-content[' . $m_id . ']" class="span5">' . htmlspecialchars($aMarket['content']) . '</textarea></div></div>';
                    $goods_html .= '<div class="control-group"><label class="control-label">' . l('Категория') .' ' . htmlspecialchars($aMarket['title']) . ':</label>';
                    $goods_html .= '<div class="controls">';
                    $goods_html .= '<select class="span5" id="market-category-' . $m_id . '" name="market-category[' . $m_id . ']"><option value=""></option>';
                    if (isset($aMarket['categories']) && count($aMarket['categories']) > 0) {
                        foreach ($aMarket['categories'] as $cat_id => $val) {

                            if ($aMarket['category_id'] == $cat_id)
                                $goods_html .= '<option selected value="' . $cat_id . '">' . htmlspecialchars($val) . '</option>';
                            else
                                $goods_html .= '<option value="' . $cat_id . '">' . htmlspecialchars($val) . '</option>';
                        }
                    }
                    $goods_html .= '</select>';
                    $goods_html .= '<input type="button" onclick="add_cat(this, \'' . $m_id . '\')" class="btn add-cat" value="' . l('Добавить категорию') . ' +" /></div></div><br><br><br>';
                }

            } else {
                $goods_html .= '<p class="text-error">' . l('Нет ни одного магазина в базе данных') . '</p>';
            }
            $goods_html .= $this->btn_save_product('omt_aggregators');
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function products_omt_procurement()
    {
        $out = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
        ) {

            $product = $this->all_configs['db']->query('SELECT price, price_purchase, price_wholesale, qty_store, qty_wh, old_price
                FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            $out = $this->view->renderFile('products/products_omt_procurement', array(
                'product' => $product,
                'all_configs' => $this->all_configs,
                'btn_save_product' => $this->btn_save_product('omt_procurement')
            ));
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function products_omt_suppliers()
    {
        $goods_html = '';

        if ($this->all_configs['configs']['manage-show-imports'] == true && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
            && array_key_exists(2, $this->all_configs['arrequest'])) {

            $goods_suppliers = $this->all_configs['db']->query('SELECT s.supplier_id, s.price, s.price_sell, s.qty, s.date_add, n.title
                FROM {contractors_suppliers_goods_price} AS s
                LEFT JOIN (SELECT title, id, type FROM {contractors})n ON n.id=s.supplier_id# AND type=1
                WHERE s.goods_id=?i ORDER BY s.price',
                array($this->all_configs['arrequest'][2]))->assoc();

            if ($goods_suppliers) {
                $goods_html .= '<table class="table table-striped"><thead><tr><td>' . l('Поставщик') . '</td>';
                $goods_html .= '<td>' . l('Цена закупки') .'</td><td>' . l('Цена продажи') .'</td><td>' . l('Количество') .'</td><td>'.l('Дата').'</td></tr></thead><tbody>';
                foreach ($goods_suppliers as $vgs) {
                    $goods_html .= '<tr><td>' . htmlspecialchars($vgs['title']) . '</td>';
                    $goods_html .= '<td>' . number_format($vgs['price'] / 100, 2, ',', ' ') . '</td>';
                    $goods_html .= '<td>' . number_format($vgs['price_sell'] / 100, 2, ',', ' ') . '</td>';
                    $goods_html .= '<td>' . htmlspecialchars($vgs['qty']) . '</td>';
                    $goods_html .= '<td>' . do_nice_date($vgs['date_add']) . '</td></tr>';
                }
                $goods_html .= '</tbody></table>';
            } else {
                $goods_html .= '<p  class="text-error">' . l('Нет информации') .'</p>';
            }
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }


    /**
     * @param string $hash
     * @return array
     */
    function products_imt($hash = '#imt-main')
    {
        if (trim($hash) == '#imt' || (trim($hash) != '#imt-main' && trim($hash) != '#imt-comments' && trim($hash) != '#imt-warranties'
                && trim($hash) != '#imt-related' && trim($hash) != '#imt-relatedgoods' && trim($hash) != '#imt-relatedservice'
                && trim($hash) != '#imt-similar' && trim($hash) != '#imt-group' && trim($hash) != '#imt-comments_links'))
            $hash = '#imt-main';

        $goods_html = '';

        $goods_html .= '<div class="tabbable"><ul class="nav nav-pills">';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_main" onclick="click_tab(this, event)" href="#imt-main" title="">' . l('Основные') .'</a></li>';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_comments" onclick="click_tab(this, event)" href="#imt-comments" title="' . l('Отзывы') .'">' . l('Отзывы') .'</a></li>';
        if ($this->all_configs['configs']['no-warranties'] == false)
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_warranties" onclick="click_tab(this, event)" href="#imt-warranties" title="' . l('Гарантийные пакеты') .'">' . l('') .'</a></li>';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_related" onclick="click_tab(this, event)" href="#imt-related" title="' . l('Сопутствующий') .'">' . l('Сопутствующий') .'</a></li>';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_relatedgoods" onclick="click_tab(this, event)" href="#imt-relatedgoods" title="' . l('Сопутствующие товары') .'">' . l('Сопут. товары') .'</a></li>';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_relatedservice" onclick="click_tab(this, event)" href="#imt-relatedservice" title="' . l('Сопутствующие услуги') .'">' . l('Сопут. услуги') .'</a></li>';
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_similar" onclick="click_tab(this, event)" href="#imt-similar" title="' . l('Аналогичные') .'">' . l('Аналогичные') .'</a></li>';
        if ($this->all_configs['configs']['group-goods'] == true) {
            $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_group" onclick="click_tab(this, event)" href="#imt-group" title="' . l('Группа') .'">' . l('Группа') .'</a></li>';
        }
        $goods_html .= '<li><a class="click_tab" data-open_tab="products_imt_comments_links" onclick="click_tab(this, event)" href="#imt-comments_links" title="' . l('Ссылки для парсинга') .'">' . l('Парсер') .'</a></li></ul>';

        $goods_html .= '<div class="pill-content"><div id="imt-main" class="pill-pane">';
        $goods_html .= '</div>';

        // comment parse
        $goods_html .= '<div id="imt-comments_links" class="pill-pane">';
        $goods_html .= '</div>';

        if ($this->all_configs['configs']['no-warranties'] == false) {
            $goods_html .= '<div id="imt-warranties" class="pill-pane">';
            $goods_html .= '</div>';
        }

        $goods_html .= '<div id="imt-comments" class="pill-pane">';
        $goods_html .= '</div>';

        $goods_html .= '<div id="imt-related" class="pill-pane">';
        $goods_html .= '</div>';

        $goods_html .= '<div id="imt-relatedgoods" class="pill-pane">';
        // временное решение
        $products_imt_relatedgoods = $this->products_imt_relatedgoods();
        $goods_html .= $products_imt_relatedgoods['html'];
        $goods_html .= '</div>';

        $goods_html .= '<div id="imt-relatedservice" class="pill-pane">';
        // временное решение
        $products_imt_relatedservice = $this->products_imt_relatedservice();
        $goods_html .= $products_imt_relatedservice['html'];
        $goods_html .= '</div>';

        $goods_html .= '<div id="imt-similar" class="pill-pane">';
        $goods_html .= '</div>';

        if ($this->all_configs['configs']['group-goods'] == true) {
            $goods_html .= '<div id="imt-group" class="pill-pane">';
            $goods_html .= '</div>';
        }

        $goods_html .= '</div></div>';

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()'),
        );
    }

    /**
     * @param $tab
     * @return string
     */
    function btn_save_product($tab)
    {
        $goods_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-goods')) {
            $goods_html .= '<div class="control-group"><div class="controls">';
            $goods_html .= '<input class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-product-' . $tab . '">';
            if ($this->all_configs['configs']['save_goods-export_to_1c'] == true && $this->all_configs['configs']['onec-use'] == true)
                $goods_html .= '<label class="checkbox"><input type="checkbox" checked name="1c-export" />' . l('Отправить в 1с') .'</label>';
            $goods_html .= '</div></div>';
        } else {
            $goods_html .= '<script>jQuery(document).ready(function($) {$(":input:not(:disabled)").prop("disabled",true)})</script>';
        }

        return $goods_html;
    }

    /**
     * @param      $key
     * @param      $values
     * @param bool $option
     * @return string
     */
    public function click_filters($key, $values, $option = false)
    {
        $active = '';
        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0];
        $url .= isset($this->all_configs['arrequest'][1]) && !empty($this->all_configs['arrequest'][1]) ? ('/' . $this->all_configs['arrequest'][1]) : '';

        $values = (array)$values;
        $get = $_GET;

        if (array_key_exists($key, $get)) {

            $svalues = explode('-', $get[$key]);

            foreach ($values as $value) {
                $p = array_search($value, $svalues);
                if ($p !== false) {
                    unset($svalues[$p]);
                    $active = $option == true ? 'selected' : 'checked';
                } else {
                    $svalues[] = trim($value);
                }
            }

            $get[$key] = implode('-', array_filter($svalues));
        } else {
            $get[$key] = implode('-', array_filter($values));
        }

        $url .= '?' . get_to_string('p', $get);

        return $option == true
            ? ' value="' . $url . '" ' . $active . ' '
            : ' onclick="javascript:window.location.href=\'' . $url . '\'; return false;" ' . $active . ' ';
    }
}
