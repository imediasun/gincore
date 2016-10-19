<?php

require_once __DIR__ . '/../../Core/Controller.php';
$modulename[60] = 'products';
$modulemenu[60] = l('Товары');
$moduleactive[60] = !$ifauth['is_2'];

/**
 * Class products
 *
 * @property MGoods         Goods
 * @property MCategoryGoods CategoryGoods
 * @property MCategories    Categories
 * @property  MLockFilters  LockFilters
 */
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
    public $uses = array(
        'Goods',
        'Categories',
        'LockFilters',
        'CategoryGoods'
    );

    /**
     * @inheritdoc
     */
    public function routing(Array $arrequest)
    {
        global $input_html;
        parent::routing($arrequest);
        if (!isset($arrequest[1]) || $arrequest[1] != 'create') {
            return $this->gencontent(); // список товаров
        } elseif (isset($arrequest[1]) && $arrequest[1] == 'create') { // форма изменения товара
            return $this->gencreate();
        }
        return '';
    }

    /**
     * products constructor.
     * @param $all_configs
     */
    public function __construct($all_configs)
    {
        parent::__construct($all_configs);
        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');

    }

    /**
     * @return string
     */
    public function render()
    {
        global $input_html;
        $result = parent::render();
        if (!empty($input_html['mmenu'])) {
            $input_html['menu_span'] = 'col-sm-3';
            $input_html['content_span'] = 'col-sm-9';
        } else {
            $input_html['menu_span'] = '';
            $input_html['content_span'] = 'col-sm-10 col-sm-offset-1';
        }
        return $result;
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
    public function check_post(array $post)
    {
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $user_id = $this->getUserId();

        $product_id = (array_key_exists(2,
                $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) ? $this->all_configs['arrequest'][2] : null;

        if (isset($post['filters'])) {
            $url = $this->setFilters($_POST);
            Response::redirect($url);
        }

        // создание продукта
        if (isset($post['create-product']) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            return $this->createProduct($post, $user_id, $mod_id);
        }

        if (isset($post['delete-product'])) {
            if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
                $data = $this->deleteProduct($post, $mod_id);
                if (!$data['state']) {
                    FlashMessage::set($data['message'], FlashMessage::DANGER);
                }
            } else {
                FlashMessage::set(l('У вас не хватает прав для этой операции'), FlashMessage::DANGER);
            }
        }
        if (isset($post['restore-product']) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            $this->Goods->restoreProduct($post, $mod_id);
        }

        // импорт товаров с яндекс маркета
        if (isset($post['ym-import_goods']) && $this->all_configs['oRole']->hasPrivilege('parsing')) {
            $this->importFromYM($post);
        }

        // быстрое обновление
        if (isset($post['quick-edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods')) {
            $this->quickEdit($post, $mod_id);
            Response::redirect(Response::referrer());
        }
        if (isset($post['products-table-columns'])) {
            $this->LockFilters->toggle('products-table-columns', $_POST);
            Response::redirect(Response::referrer());
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
                $this->History->save('update-goods-title-image', $mod_id, $product_id);
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
                $this->History->save('update-goods-image-prio', $mod_id, $product_id);
            }

            //если нужно удаляeм картинку с базы и с папки
            if (isset($post['images_del'])) {
                $post = $this->deleteImage($post, $product_id, $mod_id);
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
                        if (empty($youtube)) {
                            continue;
                        }

                        $this->all_configs['db']->query('INSERT INTO {goods_images} (image, type, goods_id) VALUES (?, ?i, ?i)',
                            array($youtube, 2, $product_id));
                    }
                }
            }

            // основные
            if (isset($post['edit-product-main'])) {
                return $this->editProductMain($post, $product_id, $mod_id);
            }

            // дополнительно
            if (isset($post['edit-product-additionally'])) {
                $post = $this->editProductAdditionally($post, $product_id, $mod_id);
            }

            // менеджеры
            if (isset($post['edit-product-managers_managers'])) {
                $this->editProductManagers($post, $product_id);
            }

            // finance/stock заказы поставщикам
            if (isset($post['edit-product-financestock_finance'])) {
                $this->editProductFinacestock($post, $product_id);
            }

            // омт уведомления
            if (isset($post['edit-product-omt_notices'])) {
                $this->editProductOmtNotices($post, $product_id, $mod_id);
            }

            // омт управление закупками
            if (isset($post['edit-product-omt_procurement']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
                $this->editProductOmtProcurement($post, $product_id, $mod_id);

            }

            // експорт в 1с
            if (isset($post['1c-export']) && $this->all_configs['configs']['save_goods-export_to_1c'] == true && $this->all_configs['configs']['onec-use'] == true) {
                $this->export_product_1c($product_id);
            }

            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2]);
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
                $goods_html .= '<fieldset><legend>' . l('Редактирование товара') . ' ID: ' . $product['id'] . '. ' .
                    h($product['title']) .
                    '</legend>' .
                    $this->show_product_body();
            } else {
                $goods_html .= '<p  class="text-error">' . l('Товар не найден') . '</p>';
            }
        } else {
            if ($this->all_configs['oRole']->hasPrivilege('create-goods')) {
                $goods_html = $this->create_product_form();
            } else {
                $goods_html .= '<p  class="text-error">' . l('У Вас нет прав для добавления нового товара') . '</p>';
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
                SELECT u.id, if(u.fio is NULL OR u.fio="",  u.login, u.fio) as login, m.user_id as manager FROM {users} as u
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
                if ($index == 1) {
                    $main = 'text-info';
                }

                $tree[] = array(
                    'id' => $tmp['id'],
                    'title' => $space . h($tmp['title']),
                    'class' => $main
                );
                if (array_key_exists('child', $tmp)) {
                    $tree = $this->array_tree($tmp['child'], $index, $tree);
                }
            }
        }
        return $tree;
    }

    /**
     * @param $categories
     * @return bool
     */
    protected function showDeleted($categories)
    {
        $deletedCategories = 0;
        $recycleBin = $this->Categories->getRecycleBin();
        if (!empty($categories)) {
            $deletedCategories = $this->Categories->query('SELECT count(*) FROM {categories} WHERE deleted=1 AND id in (?li)',
                array($categories));
        }

        return !empty($categories) && (in_array($recycleBin['id'], $categories) || $deletedCategories > 0);
    }

    /**
     * @param $get
     * @return mixed
     */
    private function get_goods_ids($get)
    {
        // все категории
        $goods_query = $this->all_configs['db']->makeQuery('WHERE 1=1', array());

        // выбранные категории
        $categories = isset($get['cats']) ? array_filter(explode('-', $get['cats'])) : array();
        if (count($categories) > 0) {
            // конкретные категории
            $goods_query = $this->all_configs['db']->makeQuery(', {category_goods} AS cg
                    ?query AND cg.category_id IN (?li) AND g.id=cg.goods_id',
                array($goods_query, array_values($categories)));

        }

        // выводим удаленные только если выбрана категория Корзина
        if (!$this->showDeleted($categories)) {
            $goods_query = $this->Goods->makeQuery('?query AND g.deleted=?i', array($goods_query, 0));
        }
        if (!empty($get['ids']) && is_array($get['ids'])) {
            $goods_query = $this->Goods->makeQuery('?query AND g.id in (?li)', array($goods_query, $get['ids']));
        }

        // в наличии
        if (isset($get['avail'])) {
            $avail = array_filter(explode('-', $get['avail']));
            if (array_search('free', $avail) !== false) {
                $ids = $this->all_configs['db']->query('SELECT goods_id FROM {warehouses_goods_items} WHERE order_id IS NULL OR order_id=0 GROUP by goods_id',
                    array())->col();
                $goods_query = $this->all_configs['db']->makeQuery(' ?query AND g.id in (?li)',
                    array($goods_query, $ids));
            }
            if (array_search('not', $avail) !== false) {
                $ids = $this->all_configs['db']->query('SELECT goods_id FROM {warehouses_goods_items}  GROUP by goods_id',
                    array())->col();
                $goods_query = $this->all_configs['db']->makeQuery(' ?query AND NOT g.id in (?li)',
                    array($goods_query, $ids));
            }
            if (array_search('all', $avail) !== false) {
                $ids = $this->all_configs['db']->query('SELECT goods_id FROM {warehouses_goods_items}  GROUP by goods_id',
                    array())->col();
                $goods_query = $this->all_configs['db']->makeQuery(' ?query AND g.id in (?li)',
                    array($goods_query, $ids));
            }
            if (array_search('mb', $avail) !== false) {
                $goods_query = $this->all_configs['db']->makeQuery(' ?query AND g.use_minimum_balance=1 AND g.minimum_balance <= ?i AND g.qt_wh < g.minimum_balance',
                    array($goods_query, $avail['mb']));
            }
        }
        // Отобразить
        if (isset($get['show'])) {
            $show = array_filter(explode('-', $get['show']));
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
        if (isset($get['wh']) && count(array_values(array_filter(explode('-', $get['wh'])))) > 0) {
            $goods_query = $this->all_configs['db']->makeQuery(', {warehouses_goods_items} as i
                ?query AND i.goods_id=g.id AND i.wh_id IN (?li)',
                array($goods_query, array_values(array_filter(explode('-', $get['wh'])))));
        }

        // поиск
        if (isset($get['s']) && !empty($get['s'])) {
            $s = trim(urldecode($get['s']));
            $goods_query = $this->all_configs['db']->makeQuery('?query AND (g.title LIKE "%?e%" OR g.barcode LIKE "%?e%" OR g.vendor_code LIKE "%?e%") AND g.deleted=0 ',
                array($goods_query, $s, $s, $s));
        }

        // imt
        $imt = isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : null;
        if (isset($get['imt'])) {
            $imt = $get['imt'];
        }
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
        $sfilters = isset($get['filters']) ? array_filter(explode('-', $get['filters'])) : array();
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
        if (isset($get['sort'])) {
            switch ($get['sort']) {
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
     * @param null $get
     */
    private function getGoods($get = null)
    {
        if (!is_array($get)) {
            $get = $_GET;
        }
        // текущая страничка
        $current_page = isset($get['p']) ? $get['p'] - 1 : 0;

        // все
        $goods_ids = $this->get_goods_ids($get);

        // количество
        $this->count_goods = count($goods_ids);

        // режем нужное количество
        $goods_ids = array_slice($goods_ids, $current_page * $this->count_on_page, $this->count_on_page, true);

        // достаем описания товаров
        if (count($goods_ids) > 0) {
            $add_fields = array();
            $this->goods = $this->all_configs['db']->query('SELECT 
                    g.*, SUM(g.qty_wh) as qty_wh, SUM(g.qty_store) as qty_store ?q, u.fio as manager, csoc.expect, csoc.min_date_come, csod.have
                  FROM {goods} AS g 
                  JOIN {users_goods_manager} as ugm ON ugm.goods_id=g.id
                  JOIN {users} as u ON ugm.user_id=u.id
                  LEFT JOIN (SELECT sum(count_come) as expect, MIN(date_come) as min_date_come, c.goods_id FROM {contractors_suppliers_orders} c WHERE count_come > 0 GROUP by c.goods_id) csoc ON csoc.goods_id=g.id
                  LEFT JOIN (SELECT sum(count_debit) as have, c.goods_id FROM {contractors_suppliers_orders} c WHERE count_debit > 0 GROUP by c.goods_id) csod ON csod.goods_id=g.id
                  WHERE g.id IN (?list) GROUP BY g.id ORDER BY FIELD(g.id, ?li)',
                array(implode(',', $add_fields), array_keys($goods_ids), array_keys($goods_ids)))->assoc('id');

            // картинки
            if (count($this->goods) > 0) {
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
     * @param $get
     * @return string
     */
    private function filters($get)
    {
        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses}')->vars();
        return $this->view->renderFile('products/navigation', array(
            'warehouses' => $warehouses,
            'controller' => $this,
            'categories' => $this->get_categories(),
            'managers' => $this->get_managers(),
            'current_categories' => isset($get['cats']) ? explode('-', $get['cats']) : array(),
            'current_warehouses' => isset($get['wh']) ? explode('-', $get['wh']) : array(),
            'current_avail' => isset($get['avail']) ? explode('-', $get['avail']) : array(),
            'current_show' => isset($get['show']) ? explode('-', $get['show']) : array(),
        ));
    }

    /**
     * @return mixed
     */
    private function get_categories()
    {
        return $this->all_configs['db']->query("
            SELECT * FROM {categories} 
            WHERE deleted=0 AND NOT url in (?l)
        ", array(
            array(
                'recycle-bin',
                'prodazha',
                'spisanie',
                'vozvrat-postavschiku',
            )
        ))->assoc();
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
        $warranties = $this->all_configs['configs']['warranties'];


        if (isset($_GET['delete-all'])) {
            if ($this->all_configs['oRole']->hasPrivilege('edit-users')) {
                $this->deleteAll($_GET, $mod_id);
            } else {
                FlashMessage::set(l('У вас не хватает прав для этой операции'), FlashMessage::DANGER);
            }
            unset($_GET['delete-all']);
            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?' . get_to_string('p',
                    $_GET));
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
                    $this->History->save('edit-warranties-add', $mod_id, 0);
                }
            }

            Response::redirect($_SERVER['REQUEST_URI']);
        }

        // поиск товаров
        if (isset($_POST['search'])) {
            $_GET['s'] = isset($_POST['text']) ? trim($_POST['text']) : '';
            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?' . get_to_string('p',
                    $_GET));
        }

        $this->getGoods($_GET);
        $goods = $this->goods;
        $serials = array();
        if (count($goods) > 0) {
            $data = $this->all_configs['db']->query(
                'SELECT i.goods_id, w.title as wh_title, t.location, COUNT(i.goods_id) as `count`
                FROM {warehouses_goods_items} as i, {warehouses} as w, {warehouses_locations} as t
                WHERE w.id=i.wh_id AND w.consider_all=?i AND t.id=i.location_id AND i.goods_id IN (?li)
                GROUP BY w.id, t.id, i.goods_id', array(1, array_keys($goods)))->assoc();

            if ($data) {
                foreach ($data as $i) {
                    $serials[$i['goods_id']] = (isset($serials[$i['goods_id']]) ? $serials[$i['goods_id']] : '') . h($i['wh_title']) . ' - ' . h($i['location']) . ' - ' . $i['count'] . '<br />';
                }
            }
        }

        $columns = $this->LockFilters->load('products-table-columns');
        if (empty($columns) || count($columns) == 1) {
            $columns = array(
                'id' => 'on',
                'marker' => 'on',
                'photo' => 'on',
                'title' => 'on',
                'vc' => 'on',
                'price' => 'on',
                'rprice' => 'on',
                'wprice' => 'on',
                'balance' => 'on',
                'fbalance' => 'on',
                'sbalance' => 'on',
                'delivery' => 'on',
                'cart' => 'on',
                'del' => 'on'
            );
            $this->LockFilters->toggle('products-table-columns', $columns);
        }
        include_once __DIR__ . '/exports.php';
        $goods_html = $this->view->renderFile('products/products', array(
            'goods' => $goods,
            'product_exports_form' => product_exports_form($this->all_configs),
            'product_imports_form' => $this->productImportsForm(),
            'count_goods' => $this->count_goods,
            'count_on_page' => $this->count_on_page,
            'managers' => $this->get_managers(),
            'serials' => $serials,
            'isEditable' => isset($_GET['edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods'),
            'filters' => $this->filters($_GET),
            'columns' => $columns,
            'item_in_cart' => $this->getItemInCart()
        ));

        return $goods_html;
    }

    /**
     * @return string
     */
    public function productImportsForm()
    {
        return $this->view->renderFile('products/product_imports_form');
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


        if ($act == 'on-warehouse') {
            $form = $this->onWarehouse($_GET);
            Response::json(array(
                'state' => true,
                'html' => $form
            ));
        }

        if ($act == 'create_form') {
            $form = $this->create_product_form(true, isset($_GET['service']) ? true : false);
            Response::json(array(
                'state' => true,
                'html' => $form
            ));
        }

        if ($act == 'create_new') {
            $_POST['create-product'] = true;
            $create = $this->check_post($_POST);
            if (!empty($create['error'])) {
                $result = array('state' => false, 'msg' => $create['error']);
            } else {
                $result = array('state' => true, 'id' => $create['id'], 'name' => $_POST['title']);
            }
            Response::json($result);
        }
        if ($act == 'action-form') {
            Response::json($this->actionForm($_GET));
        }
        if ($act == 'apply-action') {
            Response::json($this->applyAction($_GET, $_POST));
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

        // загружаем сайдбар
        if ($act == 'sidebar-load') {
            Response::json($this->loadSideBar());
        }

        // сохраняем новые данные о продукте
        if ($act == 'sidebar-product-update') {
            Response::json($this->updateProductSideBar());
        }

        // управление заказами поставщика
        if ($act == 'so-operations') {
            $this->all_configs['suppliers_orders']->operations(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // создаем заказ поставщику
        if ($act == 'create-supplier-order') {
            $_POST['goods-goods'] = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : 0;
            $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $_POST);
            Response::json($data);
        }

        // экспорт товаров
        if ($act == 'exports-goods' && $this->all_configs['oRole']->hasPrivilege('export-goods')) {
            include_once __DIR__ . '/exports.php';
            $ids = $this->get_goods_ids($_GET);
            exports_goods($this->all_configs, $ids);
        }

        // новый раздел сопутствующих товаров
        if ($act == 'goods-section') {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                Response::json(array('message' => l('У Вас недостаточно прав'), 'error' => true));
            }
            if (!isset($this->all_configs['arrequest'][2])) {
                Response::json(array('message' => l('Произошла ошибка'), 'error' => true));
            }
            $cats = $this->all_configs['db']->query('SELECT category_id FROM {category_goods} WHERE goods_id=?i',
                array($this->all_configs['arrequest'][2]))->vars();

            if (!$cats) {
                Response::json(array('message' => l('Товар должен находится в категории'), 'error' => true));
            }
            foreach ($cats as $k => $cat_id) {
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

            Response::json(array('message' => l('Успешно создана')));
        }

        // форма раздела
        if ($act == 'section-form') {
            $data = $this->sectionForm($data);
            Response::json($data);
        }

        // перемещаем изделие
        if ($act == 'move-item') {
            $data = $this->all_configs['chains']->move_item_request($_POST, $mod_id);
            Response::json($data);
        }

        // добавляем аналогичный
        if ($act == 'context') {
            $data = $this->addContext($data);
            Response::json($data);
        }

        // добавляем аналогичный
        if ($act == 'add-similar') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
                && array_key_exists('product_id', $_POST) && $_POST['product_id'] > 0
            ) {

                $sim = $this->all_configs['db']->query('SELECT id FROM {goods_similar} WHERE (first=?i AND second=?i)
                        OR (first=?i AND second=?i)',
                    array(
                        $this->all_configs['arrequest'][2],
                        $_POST['product_id'],
                        $_POST['product_id'],
                        $this->all_configs['arrequest'][2]
                    ))->el();

                if (!$sim) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {goods_similar}
                            (first, second, second_prio) VALUES (?i, ?i, ?i)',
                        array($_POST['product_id'], $this->all_configs['arrequest'][2], 0));
                }
                Response::json(array('state' => true));
            }
        }

        // добавляем сопутствующий
        if ($act == 'add-related') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
                && array_key_exists('product_id', $_POST) && $_POST['product_id'] > 0
            ) {

                $related = $this->all_configs['db']->query('SELECT related FROM {goods} WHERE id=?i',
                    array($this->all_configs['arrequest'][2]))->el();
                $related = $related ? unserialize($related) : array();
                $related[$_POST['product_id']] = 0;

                $this->all_configs['db']->query('INSERT IGNORE INTO {goods_related} (goods_id, related_id) VALUES (?i, ?i)',
                    array($this->all_configs['arrequest'][2], $_POST['product_id']));

                $this->all_configs['db']->query('UPDATE {goods} SET related=? WHERE id=?i',
                    array(serialize($related), $this->all_configs['arrequest'][2]));

                Response::json(array('state' => false));
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
            $product = $this->all_configs['db']->query('SELECT secret_title, id FROM {goods} WHERE id=?i',
                array($_GET['product']))->row();
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

                $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i',
                    array(1, $product['id']));
                // делаем уменьшеные копии картинок
                if (isset($this->all_configs['configs']['images-sizes']) && count($this->all_configs['configs']['images-sizes']) > 0) {
                    require_once($this->all_configs['sitepath'] . 'shop/resize_img.class.php');
                    $path_parts = full_pathinfo($result['filename']);
                    $image = new SimpleImage();
                    $first = 1;
                    foreach ($this->all_configs['configs']['images-sizes'] as $size_prefix => $size) {
                        $image->load($dir . $result['filename']);

                        if ($image->getHeight() <= $image->getWidth()) {
                            $image->resizeToWidth($size);
                        } else {
                            $image->resizeToHeight($size);
                        }
                        $image->save($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension'],
                            exif_imagetype($dir . $result['filename']));

                        // водяной знак только большей картинке
                        if ($first == 1 && isset($_GET['watermark']) && $_GET['watermark'] == 'true' && $this->all_configs['configs']['set_watermark'] == true) {
                            $watermark = new Watermark($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension']);
                            $watermark->setWatermarkImage($this->all_configs['sitepath'] . 'images/watermark_small.png');
                            $watermark->setType(Watermark::BOTTOM_CENTER);
                            $watermark->saveAs($dir . $path_parts['filename'] . $this->all_configs['configs'][$size_prefix] . $path_parts['extension']);
                        }
                        // копируем картинку всем аналогичным товарам по secret_title
                        if (isset($_GET['oist']) && $_GET['oist'] == 'true' && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen(trim($product['secret_title']),
                                'UTF-8') > 0
                        ) {
                            $related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i',
                                array(trim($product['secret_title']), $_GET['product']))->assoc();
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
                $this->History->save('add-image-goods', $mod_id, intval($_GET['product']));

                // копируем картинку всем аналогичным товарам по secret_title
                if (isset($_GET['oist']) && $_GET['oist'] == 'true' && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen(trim($product['secret_title']),
                        'UTF-8') > 0
                ) {
                    $related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i',
                        array(trim($product['secret_title']), $_GET['product']))->assoc();
                    $this->copy_image_from_product_to_products($related, $dir, $result['filename'], $user_id, $mod_id);
                }
                $result['img_id'] = $img_id;

                // заливаем фотки по товарам в группе размеров
                if ($this->all_configs['configs']['group-goods']) {
                    $size_group_goods = $this->all_configs['db']->query(
                        "SELECT goods_id as id FROM {goods_groups_size_links}"
                        . "WHERE group_id = (SELECT group_id FROM {goods_groups_size_links} "
                        . "WHERE goods_id = ?i LIMIT 1) "
                        . "AND goods_id != ?i", array($_GET['product'], $_GET['product']), 'assoc');
                    $this->copy_image_from_product_to_products($size_group_goods, $dir, $result['filename'], $user_id,
                        $mod_id);
                }
            }

            $data = htmlspecialchars(json_encode($result), ENT_NOQUOTES);

        }

        // форма принятия заказа поставщику
        if ($act == 'form-accept-so') {
            $this->all_configs['suppliers_orders']->accept_form();
            header("Content-Type: application/json; charset=UTF-8");
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
            Response::json($data);
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
                Response::json(array('message' => l('Введите имя'), 'error' => true));
            }
            if (!isset($_GET['market_id'])) {
                Response::json(array('message' => l('Произошла ошибка'), 'error' => true));
            }
            try {
                $id = $this->all_configs['db']->query('INSERT INTO {exports_markets_categories} (title,market_id) VALUES (?,?i)',
                    array($_GET['name'], $_GET['market_id']), 'id');
            } catch (Exception $e) {
                Response::json(array('message' => l('Произошла ошибка'), 'error' => true));
            }

            $this->History->save('add-market-category', $mod_id, $id);

            $result = $id;
            $data = h(json_encode($result), ENT_NOQUOTES);
        }

        if (isset($_POST['act']) && $_POST['act'] == 'hotline' && $this->all_configs['oRole']->hasPrivilege('parsing')) {
            $data = $this->saveHotlinePrices();
            Response::json($data);
        }

        if ($act == 'delete-product') {
            if ($this->all_configs['oRole']->hasPrivilege('parsing')) {
                $data = $this->deleteProduct($_POST, $mod_id);
            } else {
                $data = array(
                    'state' => false,
                    'msg' => l('У вас не хватает прав для этой операции')
                );
            }
            Response::json($data);
        }

        if (isset($_POST['act']) && $_POST['act'] == 'export_product' && $this->all_configs['configs']['onec-use'] == true) {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
                Response::json(array('message' => l('У Вас недостаточно прав'), 'error' => true));
            }
            if (!isset($_POST['goods_id']) || $_POST['goods_id'] < 1) {
                Response::json(array('message' => l('Такого товара не существует'), 'error' => true));
            }

            $this->export_product_1c($_POST['goods_id']);

            Response::json(array('message' => l('Товар успешно выгружен')));

        }
        if (isset($_POST['act']) && $_POST['act'] == 'goods_add_size_group') {
            $data = array();
            $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : 0;
            if ($group_id) {
                $product_id = $this->all_configs['db']->query("SELECT goods_id FROM {goods_groups_size_links} "
                    . "WHERE group_id = ?i LIMIT 1", array($group_id), 'el');
                $product = $this->all_configs['db']->query("SELECT id, price / 100 as price, title, article, content "
                    . "FROM {goods} WHERE id = ?i", array($product_id), 'row');
                $data['state'] = true;
                $model = new Model($this->all_configs['db'], $this->all_configs['configs']);
                $filters = $this->all_configs['db']->query('SELECT nv.*, fv.*
                    FROM {filter_name_value} as nv, {filter_value} as fv
                    WHERE nv.fname_id=?i AND nv.fvalue_id=fv.id AND fv.value != ""
                    ORDER BY fv.value
                ', array($model->fname_id_sizes))->assoc();
                $filters_list = '';
                foreach ($filters as $filter) {
                    $filters_list .= '<option value="' . $filter['id'] . ':' . $filter['value'] . '">' . $filter['value'] . '</option>';
                }
                $data['size_select'] =
                    '<div class="control-group" id="group_size_select">' .
                    '<input name="size_group_goods_id" type="hidden" value="' . $product['id'] . '">' .
                    '<label class="control-label">' . l('Размер') . ': </label>' .
                    '<div class="controls">' .
                    '<select name="g_size" class="size">' .
                    $filters_list .
                    '</option>' .
                    '</div>' .
                    '</div>';
                $data['product'] = $product;
                $data['product']['categories'] = $this->all_configs['db']->query("SELECT category_id FROM {category_goods} "
                    . "WHERE goods_id = ?i", array($product_id), 'vars');
            } else {
                $data['state'] = false;
                $data['msg'] = l('Неверный id группы');
            }
            Response::json($data);
        }
        preg_match('/changes:(.+)/', $act, $arr);
        if (count($arr) == 2 && isset($arr[1])) {
            $data = $this->getAllChanges($act, $mod_id);
            Response::json($data);
        }
        echo $data;
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
                    $this->History->save('add-image-goods', $mod_id, $r['id']);
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

        $this->History->save('export-order', $mod_id, $product['id']);
    }

    /**
     *
     */
    protected function updateProductSideBar()
    {
        $id_product = (int)$this->all_configs['arrequest'][2];
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $errors = array();
        $post = $_POST;

        $url = (isset($post['url']) && !empty($post['url'])) ? trim($post['url']) : trim($post['title']);

        if (mb_strlen(trim($post['title']), 'UTF-8') == 0) {
            $errors[] = l('Заполните название');
        }
        
        if (empty($errors)) {
            try {
            $product = $this->Goods->getByPk($id_product);

            $update = array(
                'title' => trim($post['title']),
                'secret_title' => trim($post['secret_title']),
                'url' => transliturl($url),
                'prio' => intval($post['prio']),
                'article' => empty($post['article']) ? null : trim($post['article']),
                'barcode' => trim($post['barcode']),
                'vendor_code' => trim($post['vendor_code']),
                'avail' => isset($post['avail']) ? 1 : 0,
                '`type`' => isset($post['type']) ? 1 : 0,
                'percent_from_profit' => $post['percent_from_profit'],
                'fixed_payment' => $post['fixed_payment'] * 100,
                'category_for_margin' => empty($post['category_for_margin']) ? 0 : intval($post['category_for_margin']),

                'use_minimum_balance' => (int)(strcmp($post['use_minimum_balance'], 'on') === 0),
                'minimum_balance' => $post['minimum_balance'],
                'use_automargin' => (int)(strcmp($post['use_automargin'], 'on') === 0),
                'automargin_type' => $post['automargin_type'],
                'automargin' => $post['automargin'],
                'wholesale_automargin_type' => $post['wholesale_automargin_type'],
                'wholesale_automargin' => $post['wholesale_automargin'],
                'price' => trim($post['price']) * 100,
                'price_wholesale' => trim($post['price_wholesale']) * 100
            );

            // старая цена
            if (array_key_exists('use-goods-old-price', $this->all_configs['configs'])
                && $this->all_configs['configs']['use-goods-old-price'] == true && isset($post['old_price'])
            ) {
                $update['old_price'] = trim($post['old_price']) * 100;
            }

            // редактируем количество только если отключен 1с и управление складами
            if ($this->all_configs['configs']['onec-use'] == false && $this->all_configs['configs']['erp-use'] == false) {
                $update['qty_store'] = intval($post['exist']);
                $update['qty_wh'] = intval($post['qty_wh']);
                $update['price_purchase'] = trim($post['price_purchase']) * 100;
                $update['price_wholesale'] = trim($post['price_wholesale']) * 100;
            }

            $ar = $this->Goods->update($update, array(
                'id' => $id_product
            ));

            if (intval($ar) > 0) {
                $this->saveMoreHistory($update, $product, $mod_id);
            }

            $query = '';
            if (isset($post['categories']) && count($post['categories']) > 0) {
                $query = $this->all_configs['db']->makeQuery(' AND category_id NOT IN (?li)',
                    array($post['categories']));
            }
            $this->all_configs['db']->query('DELETE FROM {category_goods} WHERE goods_id=?i ?query',
                array($id_product, $query));

            // добавляем товар в старые/новые категории
            if (isset($post['categories']) && count($post['categories']) > 0) {
                foreach ($post['categories'] as $new_cat) {
                    if ($new_cat != 0) {
                        $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id)
                                VALUES (?i, ?i)', array($new_cat, $id_product));
                    }
                }
            }


            $this->editProductManagersSideBar($post, $id_product);
            $this->editProductFinacestockSideBar($post, $id_product);
            $this->editProductNoticesSideBar($post, $id_product, $mod_id);  
            } catch( Exception $e){
                $errors[] = $e->getMessage();
            }
        }
       

        Response::json([
            'hasError' => !empty($errors),
            'errors' => $errors,
            'msg' => l('Товар изменен успешно')
        ]);
    }

    /**
     * @param array $post
     * @param       $product_id
     */
    private function editProductManagersSideBar(array $post, $product_id)
    {
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

    /**
     * @param array $post
     * @param       $product_id
     */
    private function editProductFinacestockSideBar(array $post, $product_id)
    {
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

    /**
     * @param array $post
     * @param       $product_id
     * @param       $mod_id
     */
    private function editProductNoticesSideBar(array $post, $product_id, $mod_id)
    {
        $each_sale = 0;
        if (isset($post['each_sale'])) {
            $each_sale = 1;
        }
        $by_balance = 0;
        if (isset($post['by_balance'])) {
            $by_balance = 1;
        }
        $balance = 0;
        if (isset($post['balance']) && $post['balance'] > 0) {
            $balance = intval($post['balance']);
        }

        $by_critical_balance = 0;
        if (isset($post['by_critical_balance'])) {
            $by_critical_balance = 1;
        }
        $critical_balance = 0;
        if (isset($post['critical_balance']) && $post['critical_balance'] > 0) {
            $critical_balance = intval($post['critical_balance']);
        }
        $seldom_sold = 0;
        if (isset($post['seldom_sold'])) {
            $seldom_sold = 1;
        }
        $supply_goods = 0;
        if (isset($post['supply_goods'])) {
            $supply_goods = 1;
        }
        $this->all_configs['db']->query('INSERT INTO {users_notices} (user_id, goods_id, each_sale, by_balance,
                        balance, by_critical_balance, critical_balance, seldom_sold, supply_goods)
                      VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i) ON duplicate KEY
                    UPDATE each_sale=VALUES(each_sale), by_balance=VALUES(by_balance), balance=VALUES(balance)',
            array(
                $_SESSION['id'],
                $product_id,
                $each_sale,
                $by_balance,
                $balance,
                $by_critical_balance,
                $critical_balance,
                $seldom_sold,
                $supply_goods
            ));

    }

    /**
     * @return array
     */
    protected function loadSideBar()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $id_product = (int)$this->all_configs['arrequest'][2];

            $product = $this->all_configs['db']->query('SELECT g.*, g.fixed_payment/100 as fixed_payment 
                FROM {goods} as g WHERE g.id=?i',
                array($id_product))->row();

            // картинки
            $images = $this->all_configs['db']->query('SELECT id, image FROM {goods_images}
                    WHERE goods_id=?i AND type=1 ORDER BY prio',
                array($id_product))->assoc();

            $selected_categories = $this->all_configs['db']->query('SELECT cg.category_id, cg.category_id
                        FROM {category_goods} as cg WHERE cg.goods_id=?i',
                array($id_product))->vars();

            $author = $this->all_configs['db']->query('SELECT login FROM {users} as u, {goods} as g
                WHERE u.id=g.author AND g.id=?i ',
                array($id_product))->el();

            $managers = $this->get_managers($id_product);
            $histories = $this->History->getProductsManagersChanges($id_product);
            $warehouses_counts = $this->all_configs['db']->query('SELECT w.title, i.wh_id, COUNT(DISTINCT i.id) as qty_wh,
                      SUM(IF (w.consider_store=1 AND i.order_id IS NULL, 1, 0)) - COUNT(DISTINCT l.id) as qty_store
                    FROM {warehouses_goods_items} as i
                    LEFT JOIN {warehouses} as w ON w.id=i.wh_id
                    LEFT JOIN {orders_suppliers_clients} AS l ON i.supplier_order_id = l.supplier_order_id
                      AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)
                    WHERE i.goods_id=?i AND w.consider_all=1 GROUP BY i.wh_id',
                array($id_product))->assoc();

            $notifications = $this->all_configs['db']->query('SELECT * FROM {users_notices} WHERE user_id=?i AND goods_id=?i',
                array($this->getUserId(), $id_product))->row();




            $goods_html = $this->view->renderFile('products/sidebar/goods', array_merge (
                    array (
                        'product' => $product,
                        'images' => $images,
                        'author' => $author,
                        'managers' => $managers,
                        'histories' => $histories,
                        'warehouses_counts' => $warehouses_counts,
                        'notifications' => $notifications,
                        'categories' => $this->get_categories(),
                        'selected_categories' => $selected_categories,
                    ),
                    $this->getSupplierOrdersTplVars($id_product)
                )
            );

        }

        return array(
            'hasError' => !empty($this->errors),
            'errors' => $this->errors,
            'html' => $goods_html,
        );
    }

    /**
     * @param $id_product
     * @return array
     */
    protected function getSupplierOrdersTplVars($id_product){

        $goods_suppliers = $this->all_configs['db']->query('SELECT link FROM {goods_suppliers} WHERE goods_id=?i',
            array($id_product))->assoc();
        $queries = $this->all_configs['manageModel']->suppliers_orders_query(array('by_gid' => $id_product));
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $queries['count_on_page'];
        $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);

        return array(
            'goods_suppliers' => $goods_suppliers,
            'count' => $this->all_configs['db']->query('SELECT count(id) FROM {contractors_suppliers_orders} WHERE goods_id=?i',
                array($id_product))->el(),
            'orders' => $orders
        );
    }

    /**
     * @return array
     */
    function products_main()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT g.* 
                FROM {goods} as g WHERE g.id=?i',
                array($this->all_configs['arrequest'][2]))->row();
            

            $goods_html = $this->view->renderFile('products/products_main', array(
                'product' => $product,
                'errors' => $this->errors,
                'btn_save' => $this->btn_save_product('main')
            ));
        }

        return array(
            'html' => $goods_html,
            'functions' => array('tiny_mce()'),
        );
    }

    /**
     * @return array
     */
    public function products_additionally()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT type, avail, deleted, fixed_payment/100 as fixed_payment, percent_from_profit, category_for_margin FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            if ($product) {
                $selected_categories = $this->all_configs['db']->query('SELECT cg.category_id, cg.category_id
                        FROM {category_goods} as cg WHERE cg.goods_id=?i',
                    array($this->all_configs['arrequest'][2]))->vars();

                $goods_html = $this->view->renderFile('products/products_additionally', array(
                    'product' => $product,
                    'selected_categories' => $selected_categories,
                    'btn_save' => $this->btn_save_product('additionally'),
                    'categories' => $this->get_categories()
                ));
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
    public function products_managers($hash = '#managers-managers')
    {
        if (trim($hash) == '#managers' || (trim($hash) != '#managers-managers' && trim($hash) != '#managers-history')) {
            $hash = '#managers-managers';
        }

        $goods_html = '';
        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            $product = $this->all_configs['db']->query('SELECT id, author FROM {goods} WHERE id=?i',
                array($this->all_configs['arrequest'][2]))->row();

            $goods_html = $this->view->renderFile('products/products_managers', array(
                'product' => $product,
            ));
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
            $managers = $this->get_managers($this->all_configs['arrequest'][2]);
            $goods_html = $this->view->renderFile('products/products_managers_managers', array(
                'managers' => $managers,
                'author' => $author,
                'controller' => $this
            ));
        }

        return array(
            'html' => $goods_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    public function products_managers_history()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $histories = $this->History->getProductsManagersChanges($this->all_configs['arrequest'][2]);
            $goods_html = $this->view->renderFile('products/products_managers_history', array(
                'histories' => $histories,
            ));
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
    public function products_financestock($hash = '#financestock-stock')
    {
        if (trim($hash) == '#financestock' || (trim($hash) != '#financestock-stock' && trim($hash) != '#financestock-finance')) {
            $hash = '#financestock-stock';
        }

        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $goods_html = $this->view->renderFile('products/products_financestock');
        }

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    public function products_financestock_stock()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $counts = $this->all_configs['db']->query('SELECT w.title, i.wh_id, COUNT(DISTINCT i.id) as qty_wh,
                      SUM(IF (w.consider_store=1 AND i.order_id IS NULL, 1, 0)) - COUNT(DISTINCT l.id) as qty_store
                    FROM {warehouses_goods_items} as i
                    LEFT JOIN {warehouses} as w ON w.id=i.wh_id
                    LEFT JOIN {orders_suppliers_clients} AS l ON i.supplier_order_id = l.supplier_order_id
                      AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)
                    WHERE i.goods_id=?i AND w.consider_all=1 GROUP BY i.wh_id',
                array($this->all_configs['arrequest'][2]))->assoc();

            $goods_html = $this->view->renderFile('products/products_financestock_stock', array(
                'counts' => $counts
            ));
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function products_financestock_finance()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            $goods_suppliers = $this->all_configs['db']->query('SELECT link FROM {goods_suppliers} WHERE goods_id=?i',
                array($this->all_configs['arrequest'][2]))->assoc();
            $queries = $this->all_configs['manageModel']->suppliers_orders_query(array('by_gid' => $this->all_configs['arrequest'][2]));
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $queries['count_on_page'];
            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            $goods_html = $this->view->renderFile('products/products_financestock_finance', array(
                'goods_suppliers' => $goods_suppliers,
                'btn_save' => $this->btn_save_product('financestock_finance'),
                'count' => $this->all_configs['db']->query('SELECT count(id) FROM {contractors_suppliers_orders} WHERE goods_id=?i',
                    array($this->all_configs['arrequest'][2]))->el(),
                'orders' => $orders
            ));
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
    public function products_omt($hash = '#omt-notices')
    {
        $goods_html = '';

        if (trim($hash) == '#omt' || (trim($hash) != '#omt-notices' && trim($hash) != '#omt-procurement' && trim($hash) != '#omt-suppliers')) {
            $hash = '#omt-notices';
        }

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
        ) {
            $goods_html = $this->view->renderFile('products/products_omt');
        }

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    public function products_omt_notices()
    {
        $goods_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
        ) {
            $user = $this->all_configs['db']->query('SELECT * FROM {users_notices} WHERE user_id=?i AND goods_id=?i',
                array($_SESSION['id'], $this->all_configs['arrequest'][2]))->row();
            $product = $this->Goods->getByPk($this->all_configs['arrequest'][2]);
            $goods_html = $this->view->renderFile('products/products_omt_notices', array(
                'user' => $user,
                'btn_save' => $this->btn_save_product('omt_notices'),
                'product' => $product
            ));
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
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
        ) {

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
                            'categories' => array($market['cid'] => $market['ctitle'])
                        );
                    }
                }
            }
            $goods_html = $this->view->renderFile('products/products_omt_procurement', array(
                'aMarkets' => $aMarkets,
                'btn_save' => $this->btn_save_product('omt_aggregators')
            ));
        }

        return array(
            'html' => $goods_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function products_omt_procurement()
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
    public function products_omt_suppliers()
    {
        $goods_html = '';

        if ($this->all_configs['configs']['manage-show-imports'] == true && $this->all_configs['arrequest'][2] > 0
            && $this->all_configs['oRole']->hasPrivilege('external-marketing')
            && array_key_exists(2, $this->all_configs['arrequest'])
        ) {

            $goods_suppliers = $this->all_configs['db']->query('SELECT s.supplier_id, s.price, s.price_sell, s.qty, s.date_add, n.title
                FROM {contractors_suppliers_goods_price} AS s
                LEFT JOIN (SELECT title, id, type FROM {contractors})n ON n.id=s.supplier_id# AND type=1
                WHERE s.goods_id=?i ORDER BY s.price',
                array($this->all_configs['arrequest'][2]))->assoc();

            $goods_html = $this->view->renderFile('products/products_omt_suppliers', array(
                'goods_suppliers' => $goods_suppliers
            ));
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
    public function products_imt($hash = '#imt-main')
    {
        if (trim($hash) == '#imt' || (trim($hash) != '#imt-main' && trim($hash) != '#imt-comments' && trim($hash) != '#imt-warranties'
                && trim($hash) != '#imt-related' && trim($hash) != '#imt-relatedgoods' && trim($hash) != '#imt-relatedservice'
                && trim($hash) != '#imt-similar' && trim($hash) != '#imt-group' && trim($hash) != '#imt-comments_links')
        ) {
            $hash = '#imt-main';
        }

        $goods_html = $this->view->renderFile('products/products_imt', array(
            'products_imt_relatedgoods' => $this->products_imt_relatedgoods(),
            'products_imt_relatedservice' => $this->products_imt_relatedservice(),
        ));

        return array(
            'html' => $goods_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()'),
        );
    }

    /**
     * @param $tab
     * @return string
     */
    public function btn_save_product($tab)
    {
        return $this->view->renderFile('products/btn_save_product', array(
            'tab' => $tab
        ));
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

    /**
     * @param array $post
     * @param       $user_id
     * @param       $mod_id
     * @return array|string
     */
    private function createProduct(array $post, $user_id, $mod_id)
    {
        $url = transliturl(trim($post['title']));

        // ошибки
        if (mb_strlen(trim($post['title']), 'UTF-8') == 0) {
            return array('error' => l('Заполните название'), 'post' => $post);
        }
        $id = $this->all_configs['db']->query('INSERT INTO {goods}
                    (title, secret_title, url, avail, price, price_wholesale, article, author, type, vendor_code) VALUES (?, ?, ?n, ?i, ?i, ?i, ?, ?i, ?i, ?)',
            array(
                trim($post['title']),
                '',
                $url,
                isset($post['avail']) ? 1 : 0,
                floatval(trim($post['price'])) * 100,
                floatval(trim($post['price_wholesale'])) * 100,
                $user_id,
                '',
                isset($_POST['type']) ? 1 : 0,
                trim($_POST['vendor_code'])
            ), 'id'
        );

        if ($id > 0) {
            $_POST['product_id'] = $id;

            if (isset($post['categories']) && count($post['categories']) > 0) {
                foreach ($post['categories'] as $new_cat) {
                    if ($new_cat == 0) {
                        continue;
                    }
                    $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id)
                                VALUES (?i, ?i)', array($new_cat, $id));
                }
                $this->Goods->update(array(
                    'category_for_margin' => current($post['categories'])
                ), array(
                    'id' => $id
                ));
            }
            $this->History->save('create-goods', $mod_id, $id);

            require_once $this->all_configs['sitepath'] . 'mail.php';
            $messages = new Mailer($this->all_configs);

            if (isset($post['users']) && count($post['users']) > 0) {

                foreach ($post['users'] as $user) {
                    if (intval($user) > 0) {
                        $ar = $this->all_configs['db']->query('INSERT IGNORE INTO {users_goods_manager} SET user_id=?i, goods_id=?i',
                            array(intval($user), $id))->ar();

                        if ($ar) {
                            $this->History->save('add-manager', $mod_id, intval($user));
                        }
                    }
                }
            }

            // уведомление
            if (isset($post['mail'])) {
                $content = l('Создан новый товар') . ' <a href="' . $this->all_configs['prefix'] . 'products/create/' . $id . '">';
                $content .= h(trim($post['title'])) . '</a>.';
                $messages->send_message($content, l('Требуется обработка товарной позиции'),
                    'mess-create-product', 1);
            }
            if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
                return array('id' => $id, 'state' => true);
            } else {
                Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $id);
            }
        }
        return '';
    }

    /**
     * @param $goodId
     * @return bool
     */
    public function isUsedGood($goodId)
    {
        return $this->Goods->isUsed($goodId);
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function deleteProduct($post, $mod_id)
    {
        if (!$this->isUsedGood(intval($post['id']))) {
            $this->Goods->deleteProduct($post, $mod_id);
            return array(
                'state' => true
            );
        }
        return array(
            'state' => false,
            'message' => l('Товар используется в логистических операциях или заказах')
        );
    }

    /**
     * @param $data
     * @return mixed
     */
    private function sectionForm($data)
    {
        $data['state'] = true;
        $sections = array();
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
            $data['btns'] = '<input type="button" value="' . l('Удалить') . '" class="btn btn-danger" onclick="goods_section(this, 1)" />';
        } else {
            $data['btns'] = '<input type="button" value="' . l('Создать') . '" class="btn btn-success" onclick="goods_section(this, 0)" />';
        }
        $data['content'] = $this->view->renderFile('products/section_form', array(
            'sections' => $sections
        ));
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function addContext($data)
    {
        if (!isset($_POST['provider']) || !isset($this->all_configs['configs']['api-context'][$_POST['provider']])) {
            return array('message' => l('Неизвестный провайдер'));
        }
        if (!isset($_POST['goods_id']) || $_POST['goods_id'] == 0) {
            return array('message' => l('Неизвестный товар'));
        }
        if (!isset($this->all_configs['settings'][$this->all_configs['configs']['api-context'][$_POST['provider']]['avail']])
            || $this->all_configs['settings'][$this->all_configs['configs']['api-context'][$_POST['provider']]['avail']] == 0
        ) {
            return array('message' => l('Модуль отключен'));
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
        return $data;
    }

    /**
     * @return array
     */
    private function saveHotlinePrices()
    {
        if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
            return array('message' => l('У Вас недостаточно прав'), 'error' => true);
        }

        include($this->all_configs['sitepath'] . 'hotlineparse.php');

        if (!isset($_POST['hotline_url']) || empty($_POST['hotline_url'])) {
            return array('message' => l('Заполните ссылку на hotline'), 'error' => true);
        }
        if (!isset($_POST['goods_id']) || empty($_POST['goods_id'])) {
            return array('message' => l('Попробуйте еще раз'), 'error' => true);
        }

        $prices = build_hotline_url(array('hotline_url' => $_POST['hotline_url'], 'goods_id' => $_POST['goods_id']),
            getCourse($this->all_configs['settings']['currency_suppliers_orders']), $this->all_configs['configs']);

        if (!$prices) {
            return array('message' => l('Неправильная ссылка'), 'error' => true);
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

            if ($val['hotline_number_list_flag'] == 1) {
                if ($val['hotline_number_list'] > 0) {
                    foreach ($prices as $hv) {
                        if ($hv['number_list'] == $val['hotline_number_list']) {
                            $price = $hv['price'];
                            break;
                        }
                    }
                } else {
                    $price = $prices[0]['number_list'];
                }
            } elseif ($val['hotline_shop_flag'] == 1) {
                foreach ($prices as $hv) {
                    if ($hv['shop'] == $val['hotline_shop']) {
                        $price = $hv['price'];
                        break;
                    }
                }
            }

            if ($val['purchase_flag'] == 1 && $price < ($val['price_purchase'] + $val['purchase'])) {
                if ($val['price_purchase'] == 0) {
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

        return array(
            'message' => l('Цены успешно загружены.') . $msg
        );
    }

    /**
     * @param array $post
     * @param       $product_id
     * @param       $mod_id
     * @return array
     */
    private function deleteImage(array $post, $product_id, $mod_id)
    {
        $secret_title = $this->all_configs['db']->query('SELECT secret_title FROM {goods} WHERE id=?i',
            array($product_id))->el();

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

            if (isset($post['one-image-secret_title']) && $this->all_configs['configs']['one-image-secret_title'] == true && mb_strlen($secret_title,
                    'UTF-8') > 0
            ) {
                $del_related = $this->all_configs['db']->query('SELECT id FROM {goods} WHERE secret_title=? AND id<>?i',
                    array($secret_title, $product_id))->assoc();

                if ($del_related && count($del_related) > 0) {
                    foreach ($del_related as $del_r) {
                        $this->all_configs['db']->query('DELETE FROM {goods_images} WHERE goods_id=?i AND image=?',
                            array($del_r['id'], $image_title));

                        unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $image_title);

                        $path_parts = full_pathinfo($image_title);

                        if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension'])) {
                            unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension']);
                        }
                        if (file_exists($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension'])) {
                            unlink($this->all_configs['sitepath'] . $this->all_configs['configs']['goods-images-path'] . $del_r['id'] . '/' . $path_parts['filename'] . $this->all_configs['configs']['medium-image'] . $path_parts['extension']);
                        }

                        $count_images = $this->all_configs['db']->query('SELECT count(id) FROM {goods_images} WHERE goods_id=?i',
                            array($del_r['id']))->el();

                        if ($count_images == 0) {
                            $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i',
                                array(0, $del_r['id']));
                        }
                    }
                }
            }

            $count_images = $this->all_configs['db']->query('SELECT count(id) FROM {goods_images} WHERE goods_id=?i',
                array($product_id))->el();

            if ($count_images == 0) {
                $this->all_configs['db']->query('UPDATE {goods} SET image_set=?i WHERE id=?i',
                    array(0, $product_id));
            }
        }
        $this->History->save('delete-goods-image', $mod_id, $product_id);
        return $post;
    }

    /**
     * @param array $post
     * @param       $product_id
     * @param       $mod_id
     * @return array
     */
    private function editProductAdditionally(array $post, $product_id, $mod_id)
    {
        $good = $this->Goods->getByPk($product_id);

        $update = array(
            'avail' => isset($post['avail']) ? 1 : 0,
            '`type`' => isset($post['type']) ? 1 : 0,
            'percent_from_profit' => $post['percent_from_profit'],
            'fixed_payment' => $post['fixed_payment'] * 100,
            'category_for_margin' => empty($post['category_for_margin']) ? 0 : intval($post['category_for_margin'])
        );
        $ar = $this->Goods->update($update, array(
            'id' => $product_id
        ));

        if (intval($ar) > 0) {
            $this->saveMoreHistory($update, $good, $mod_id);
        }


        // добавляем товар в старые/новые категории
        if (isset($post['categories']) && count($post['categories']) > 0) {
            $query = $this->all_configs['db']->makeQuery(' AND category_id NOT IN (?li)',
                array($post['categories']));
            $this->all_configs['db']->query('DELETE FROM {category_goods} WHERE goods_id=?i ?query',
                array($product_id, $query));
            foreach ($post['categories'] as $new_cat) {
                if ($new_cat != 0) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id)
                                VALUES (?i, ?i)', array($new_cat, $product_id));
                }
            }
            if (!in_array($good['category_for_margin'], $post['categories'])) {
                $this->Goods->update(array(
                    'category_for_margin' => current($post['categories'])
                ), array(
                    'id' => $product_id
                ));
            }
        }
        if (!isset($post['deleted']) && $good['deleted']) {
            $this->Goods->restoreProduct(array('id' => $product_id), $mod_id);
        }

        if (isset($post['deleted']) && !$good['deleted']) {
            $data = $this->deleteProduct(array('id' => $product_id), $mod_id);
            if (!$data['state']) {
                FlashMessage::set($data['message'], FlashMessage::DANGER);
            }
        }
        return $post;
    }

    /**
     * @param array $post
     * @param       $product_id
     * @param       $mod_id
     * @return array
     */
    private function editProductOmtProcurement(array $post, $product_id, $mod_id)
    {
        $update = array(
            'price' => trim($post['price']) * 100,
            'price_wholesale' => trim($post['price_wholesale']) * 100
        );

        // старая цена
        if (array_key_exists('use-goods-old-price', $this->all_configs['configs'])
            && $this->all_configs['configs']['use-goods-old-price'] == true && isset($post['old_price'])
        ) {
            $update['old_price'] = trim($post['old_price']) * 100;
        }
        $product = $this->Goods->getByPk($product_id);

        // редактируем количество только если отключен 1с и управление складами
        if ($this->all_configs['configs']['onec-use'] == false && $this->all_configs['configs']['erp-use'] == false) {
            $update['qty_store'] = intval($post['exist']);
            $update['qty_wh'] = intval($post['qty_wh']);
            $update['price_purchase'] = trim($post['price_purchase']) * 100;
            $update['price_wholesale'] = trim($post['price_wholesale']) * 100;
        }
        $ar = $this->Goods->update($update, array(
            'id' => $product_id
        ));
        if ($ar > 0) {
            $this->saveMoreHistory($update, $product, $mod_id);
        }
    }

    /**
     * @param array $post
     * @param       $product_id
     */
    private function editProductOmtNotices(array $post, $product_id, $mod_id)
    {
        $each_sale = 0;
        if (isset($post['each_sale'])) {
            $each_sale = 1;
        }
        $by_balance = 0;
        if (isset($post['by_balance'])) {
            $by_balance = 1;
        }
        $balance = 0;
        if (isset($post['balance']) && $post['balance'] > 0) {
            $balance = intval($post['balance']);
        }
        $by_critical_balance = 0;
        if (isset($post['by_critical_balance'])) {
            $by_critical_balance = 1;
        }
        $critical_balance = 0;
        if (isset($post['critical_balance']) && $post['critical_balance'] > 0) {
            $critical_balance = intval($post['critical_balance']);
        }
        $seldom_sold = 0;
        if (isset($post['seldom_sold'])) {
            $seldom_sold = 1;
        }
        $supply_goods = 0;
        if (isset($post['supply_goods'])) {
            $supply_goods = 1;
        }
        $this->all_configs['db']->query('INSERT INTO {users_notices} (user_id, goods_id, each_sale, by_balance,
                        balance, by_critical_balance, critical_balance, seldom_sold, supply_goods)
                      VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i) ON duplicate KEY
                    UPDATE user_id=VALUES(user_id), goods_id=VALUES(goods_id), each_sale=VALUES(each_sale),
                      by_balance=VALUES(by_balance), balance=VALUES(balance), by_critical_balance=VALUES(by_critical_balance),
                      critical_balance=VALUES(critical_balance), seldom_sold=VALUES(seldom_sold), supply_goods=VALUES(supply_goods)',
            array(
                $_SESSION['id'],
                $product_id,
                $each_sale,
                $by_balance,
                $balance,
                $by_critical_balance,
                $critical_balance,
                $seldom_sold,
                $supply_goods
            ));
        $product = $this->Goods->getByPk($product_id);
        $update = array(
            'use_minimum_balance' => (int)(strcmp($post['use_minimum_balance'], 'on') === 0),
            'minimum_balance' => $post['minimum_balance'],
            'use_automargin' => (int)(strcmp($post['use_automargin'], 'on') === 0),
            'automargin_type' => $post['automargin_type'],
            'automargin' => $post['automargin'],
            'wholesale_automargin_type' => $post['wholesale_automargin_type'],
            'wholesale_automargin' => $post['wholesale_automargin'],
        );
        $ar = $this->Goods->update($update, array(
            'id' => $product_id
        ));

        if (intval($ar) > 0) {
            $this->saveMoreHistory($update, $product, $mod_id);
        }
    }

    /**
     * @param array $post
     * @param       $product_id
     * @param       $mod_id
     * @return array
     */
    private function editProductMain(array $post, $product_id, $mod_id)
    {
        $url = (isset($post['url']) && !empty($post['url'])) ? trim($post['url']) : trim($post['title']);

        if (mb_strlen(trim($post['title']), 'UTF-8') == 0) {
            return array('error' => l('Заполните название'), 'post' => $post);
        }
        $product = $this->Goods->getByPk($product_id);

        $update = array(
            'title' => trim($post['title']),
            'secret_title' => trim($post['secret_title']),
            'url' => transliturl($url),
            'prio' => intval($post['prio']),
            'article' => empty($post['article']) ? null : trim($post['article']),
            'barcode' => trim($post['barcode']),
            'vendor_code' => trim($post['vendor_code']),
        );
        $ar = $this->Goods->update($update, array(
            'id' => $product_id
        ));

        if (intval($ar) > 0) {
            $this->saveMoreHistory($update, $product, $mod_id);
        }
        return array('state' => true);
    }

    /**
     * @param $get
     * @param $mod_id
     * @return bool
     */
    protected function deleteAll($get, $mod_id)
    {
        $ids = $this->get_goods_ids($get);
        $used = array();
        if (!empty($ids)) {
            foreach ($ids as $id => $value) {
                $result = $this->deleteProduct(array('id' => $id), $mod_id);
                if ($result['state'] === false) {
                    $used[] = $id;
                }
            }
        }
        if (!empty($used)) {
            FlashMessage::set(l('Список ID товаров, которые не могут быть удалены, так как используются в логистических операциях или заказах:') . implode(',',
                    $used), FlashMessage::WARNING);
        } else {
            FlashMessage::set(l('Выбранные товары успешно удалены') . implode(',',
                    $used), FlashMessage::SUCCESS);
        }
        return true;
    }

    /**
     * @param array $post
     * @param       $product_id
     */
    private function editProductFinacestock(array $post, $product_id)
    {
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

    /**
     * @param array $post
     * @param       $product_id
     */
    private function editProductManagers(array $post, $product_id)
    {
        // добавляем доступ к товару пользователям
        if (isset($post['users'])) {
            $this->all_configs['db']->query('DELETE FROM {users_goods_manager} WHERE goods_id=?i',
                array($product_id));
            foreach ($post['users'] as $user) {
                if ($user > 0) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {users_goods_manager}
                                    SET user_id=?i, goods_id=?i',
                        array($user, $product_id));
                }
            }
        }
    }

    /**
     * @param array $update
     * @param       $product
     * @param       $mod_id
     */
    private function saveMoreHistory(array $update, $product, $mod_id)
    {
        if (isset($update['title']) && strcmp(trim($update['title']), $product['title']) !== 0) {
            $this->History->save('edit-goods', $mod_id, $product['id'], l('Название') . ': ' . $product['title']);
        }
        if (isset($update['prio']) && intval($update['prio']) != $product['prio']) {
            $this->History->save('edit-goods', $mod_id, $product['id'], l('Приоритет') . ': ' . $product['prio']);
        }
        if (isset($update['barcode']) && strcmp(trim($update['barcode']), $product['barcode']) !== 0) {
            $this->History->save('edit-goods', $mod_id, $product['id'], l('Штрихкод') . ': ' . $product['barcode']);
        }
        if (isset($update['vendor_code']) && strcmp(trim($update['vendor_code']), $product['vendor_code']) !== 0) {
            $this->History->save('edit-goods', $mod_id, $product['id'], l('Артикул') . ': ' . $product['vendor_code']);
        }
        if (isset($update['price']) && $product['price'] != $update['price']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Цена') . ': ' . ($product['price'] / 100) . viewCurrency());
        }
        if (isset($update['price_wholesale']) && $product['price_wholesale'] != $update['price_wholesale']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Оптовая цена') . ': ' . ($product['price_wholesale'] / 100) . viewCurrency());
        }
        if (isset($update['price_purchase']) && $product['price_purchase'] != $update['price_purchase']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Розничная цена') . ': ' . $product['price_purchase']);
        }
        if (isset($update['avail']) && $product['avail'] != $update['avail']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Доступность') . ': ' . ($product['avail'] ? l('Да') : l('Нет')));
        }
        if (isset($update['`type`']) && $product['type'] != $update['`type`']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Тип') . ': ' . ($product['type'] == GOODS_TYPE_ITEM ? l('Товар') : l('Услуга')));
        }
        if (isset($update['percent_from_profit']) && $product['percent_from_profit'] != $update['percent_from_profit']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Процент от прибыли') . ': ' . $product['percent_from_profit'] . '%');
        }
        if (isset($update['fixed_payment']) && $product['fixed_payment'] != $update['fixed_payment']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Фиксированная оплата') . ': ' . $product['fixed_payment'] / 100 . viewCurrency());
        }
        if (isset($update['use_minimum_balance']) && $product['use_minimum_balance'] != $update['use_minimum_balance']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Использовать неснижаемый остаток') . ': ' . ($product['use_minimum_balance'] ? l('Да') : l('Нет')));
        }
        if (isset($update['use_automargin']) && $product['use_automargin'] != $update['use_automargin']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Использовать автонаценку') . ': ' . ($product['use_automargin'] ? l('Да') : l('Нет')));
        }
        if (isset($update['automargin']) && ($product['automargin'] != $update['automargin'] || $product['automargin_type'] != $update['automargin_type'])) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Автонаценка') . ': ' . $product['automargin'] . ($product['automargin_type'] ? viewCurrency() : '%'));
        }
        if (isset($update['wholesale_automargin']) && ($product['wholesale_automargin'] != $update['wholesale_automargin'] || $product['wholesale_automargin_type'] != $update['wholesale_automargin_type'])) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Оптовая автонаценка') . ': ' . $product['wholesale_automargin'] . ($product['wholesale_automargin_type'] ? viewCurrency() : '%'));
        }
        if (isset($update['minimum_balance']) && $product['minimum_balance'] != $update['minimum_balance']) {
            $this->History->save('edit-goods', $mod_id, $product['id'],
                l('Неснижаемый остаток') . ': ' . $product['minimum_balance']);
        }
    }

    /**
     * @param $get
     * @return array
     */
    public function actionForm($get)
    {
        $ids = isset($get['ids']) ? explode('-', $get['ids']) : array();
        return array(
            'state' => true,
            'content' => $this->view->renderFile('products/action_form', array(
                'categories' => $this->get_categories(),
                'managers' => $this->get_managers(),
                'selected' => count($ids),
                'ids' => $ids
            )),
            'title' => l('Действия')
        );
    }

    /**
     * @param $get
     * @param $post
     * @return array
     */
    public function applyAction($get, $post)
    {
        if(empty($post['ids'])) {
            return array(
                'state' => true,
                'reload' => true
            );
        }
        $goods_ids = isset($post['ids']) ? explode('-', $post['ids']) : array();
        if (!empty($goods_ids)) {
            $update = array();
            if (isset($post['delete'])) {
                $this->deleteAll($get, $this->all_configs['configs']['products-manage-page']);
            }
            // добавляем товар в старые/новые категории
            if (isset($post['categories']) && is_array($post['categories'])) {
                $query = $this->all_configs['db']->makeQuery(' AND category_id NOT IN (?li)',
                    array($post['categories']));
                foreach ($goods_ids as $product_id) {
                    $this->all_configs['db']->query('DELETE FROM {category_goods} WHERE goods_id=?i ?query',
                        array($product_id, $query));
                    foreach ($post['categories'] as $new_cat) {
                        if ($new_cat != 0) {
                            $this->CategoryGoods->insert(array(
                                'category_id' => $new_cat,
                                'goods_id' => $product_id
                            ));
                        }
                    }
                }
            }
            if (isset($post['active'])) {
                $update['active'] = 1;
            }
            if (isset($post['is_service'])) {
                $update['type'] = GOODS_TYPE_SERVICE;
            }
            if (isset($post['price']) && !empty($post['price'])) {
                $update['price'] = $post['price'];
            }
            if (isset($post['price_wholesale']) && !empty($post['price_wholesale'])) {
                $update['price_wholesale'] = $post['price_wholesale'];
            }
            if (isset($post['manager']) && !empty($post['manager'])) {
                $update['manager'] = $post['manager'];
            }
            if (isset($post['use_minimum_balance']) && strcmp($post['use_minimum_balance'], 'on') === 0) {
                $update['use_minimum_balance'] = 1;
                $update['minimum_balance'] = intval($post['minimum_balance']);
            }
            if (isset($post['use_automargin']) && strcmp($post['use_automargin'], 'on') === 0) {
                $update['use_automargin'] = 1;
                $update['automargin_type'] = intval($post['automargin_type']);
                $update['automargin'] = intval($post['automargin']) * 100;
                $update['wholesale_automargin_type'] = intval($post['wholesale_automargin_type']);
                $update['wholesale_automargin'] = intval($post['wholesale_automargin']);
            }

            $this->Goods->update($update, array(
                'id' => $goods_ids
            ));
        }
        return array(
            'state' => true,
            'reload' => true
        );
    }

    /**
     * @param $post
     */
    private function importFromYM($post)
    {
        require_once($this->all_configs['path'] . 'parser/pp.php');
        require_once($this->all_configs['sitepath'] . 'mail.php');

        if (isset($post['categories']) && $post['categories'] > 0) {

            $a = new YM_Products_Parser($this->all_configs, false);

            $a->go($post['categories']);

            echo '<br /><br ><a href="">' . l('Обновить') . '</a>';
            exit;
        }
    }

    /**
     * @param $post
     * @param $mod_id
     */
    private function quickEdit($post, $mod_id)
    {
// обновление активности товара
        if (isset($post['avail']) && is_array($post['avail'])) {
            foreach ($post['avail'] as $p_id => $p_avail) {
                if ($p_id > 0) {
                    $ar = $this->all_configs['db']->query('UPDATE {goods} SET avail=?i WHERE id=?i',
                        array($p_avail, $p_id))->ar();

                    if ($ar) {
                        $this->History->save('edit-product-avail', $mod_id, $p_id);
                    }
                }
            }
        }

        // обновление цен
        if (isset($post['price']) && is_array($post['price']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
            foreach ($post['price'] as $p_id => $p_price) {
                if ($p_id > 0) {
                    $this->all_configs['db']->query('UPDATE {goods} g
                                LEFT JOIN {goods_extended} e ON e.goods_id=g.id
                                SET g.price=?i
                                WHERE g.id=?i AND (e.hotline_flag IS NULL OR e.hotline_flag=0)',
                        array($p_price * 100, $p_id))->ar();
                }
            }
        }
        // обновление оптовых цен
        if (isset($post['price_wholesale']) && is_array($post['price_wholesale']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')) {
            foreach ($post['price_wholesale'] as $p_id => $p_price) {
                if ($p_id > 0) {
                    $this->all_configs['db']->query('UPDATE {goods} g
                                LEFT JOIN {goods_extended} e ON e.goods_id=g.id
                                SET g.price_wholesale=?i
                                WHERE g.id=?i AND (e.hotline_flag IS NULL OR e.hotline_flag=0)',
                        array($p_price * 100, $p_id))->ar();
                }
            }
        }

        // обновление остатков
        if (isset($post['qty_store']) && is_array($post['qty_store']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')
            && $this->all_configs['configs']['erp-use'] == false && $this->all_configs['configs']['onec-use'] == false
        ) {

            foreach ($post['qty_store'] as $gid => $qty_store) {
                if ($gid > 0) {
                    $this->all_configs['db']->query('UPDATE {goods} g SET qty_store=?i, qty_wh=?i WHERE id=?i',
                        array($qty_store, $qty_store, $gid))->ar();
                }
            }
        }

        // обновление остатков
        if (isset($post['qty_store']) && is_array($post['qty_store']) && $this->all_configs['oRole']->hasPrivilege('external-marketing')
            && $this->all_configs['configs']['erp-use'] == false && $this->all_configs['configs']['onec-use'] == false
        ) {

            foreach ($post['qty_store'] as $gid => $qty_store) {
                if ($gid > 0) {
                    $this->all_configs['db']->query('UPDATE {goods} g SET qty_store=?i, qty_wh=?i WHERE id=?i',
                        array($qty_store, $qty_store, $gid))->ar();
                }
            }
        }

        // обновление яндекс маркет ид
        if (isset($post['ym_id']) && is_array($post['ym_id']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

            foreach ($post['ym_id'] as $gid => $value) {
                if ($gid > 0) {
                    if ($value == 0) {
                        $value = null;
                    }

                    $ar = $this->all_configs['db']->query('INSERT INTO {goods_extended} (market_yandex_id, goods_id) VALUES (?n, ?i) ON DUPLICATE KEY
                            UPDATE market_yandex_id=VALUES(market_yandex_id)', array($value, $gid))->ar();

                    if ($ar) {
                        $this->History->save('edit-ym_id', $mod_id, $gid);
                    }
                }
            }
        }
    }

    /**
     * @param array $post
     * @return string
     */
    public function setFilters(array $post)
    {
        $url = array();

        // в наличии
        if (isset($post['avail']) && is_array($post['avail'])) {
            $url['avail'] = implode('-', $post['avail']);
        }
        // Отобразить
        if (isset($post['show']) && is_array($post['show'])) {
            $url['show'] = implode('-', $post['show']);
        }
        // По складам
        if (isset($post['warehouses']) && is_array($post['warehouses'])) {
            $url['wh'] = implode('-', $post['warehouses']);
        }
        // По категориям
        if (isset($post['categories']) && is_array($post['categories'])) {
            $url['cats'] = implode('-', $post['categories']);
        }

        return $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url));
    }

    /**
     * @return mixed
     */
    public function getItemInCart()
    {
        $cart = Session::getInstance()->get('cart');
        return empty($cart) ? 0 : array_reduce($cart, function ($carry, $value) {
            return $carry + $value;
        });
    }

    /**
     * @param $get
     * @return string
     */
    public function onWarehouse($get)
    {
        $goods = $this->Goods->query('
        SELECT SUM(1) as all_on_wh, SUM(IF(wgi.order_id IS NULL, 1, 0)) as free, g.title as title, w.title as wh, wl.location as location
        FROM {warehouses_goods_items} wgi
        JOIN {goods} g ON g.id=wgi.goods_id
        JOIN {warehouses} w ON w.id=wgi.wh_id
        JOIN {warehouses_locations} wl ON wl.id=wgi.location_id
        WHERE wgi.goods_id=?i GROUP by wgi.location_id
        ', array($get['id']))->assoc();
        return $this->view->renderFile('products/on_warehouse', array(
           'goods' => $goods
        ));
    }
}
