<?php

$modulename[] = 'accountings';
$modulemenu[] = 'Бухгалтерия';
$moduleactive[] = !$ifauth['is_2'];

class accountings
{
    protected $cashboxes = array();
    protected $contractors = array();

    protected $all_configs;

    public $count_on_page;

    protected $months = array(
        '01' => 'январь',
        '02' => 'февраль',
        '03' => 'март',
        '04' => 'апрель',
        '05' => 'май',
        '06' => 'июнь',
        '07' => 'июль',
        '08' => 'август',
        '09' => 'сентябрь',
        '10' => 'октябрь',
        '11' => 'ноябрь',
        '12' => 'декабрь',
    );

    protected $course_default = 100; // default course (uah) in cent

    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'export') {
            $this->export();
        }

        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас не достаточно прав</p></div>';
        }

        // если отправлена форма
        if (count($_POST) > 0)
            $this->check_post($_POST);

        //if ($this->all_configs['ifauth']['is_2']) return false;

        $input_html['mcontent'] = $this->gencontent();

    }

    function can_show_module()
    {
        if ($this->all_configs['oRole']->hasPrivilege('accounting')
            || $this->all_configs['oRole']->hasPrivilege('accounting-contractors')
            || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
            || $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
            || $this->all_configs['oRole']->hasPrivilege('partner')) {

            return true;
        } else {
            return false;
        }
    }

    function check_post($post)
    {
        $mod_id = $this->all_configs['configs']['accountings-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        // допустимые валюты
        $currencies = $this->all_configs['suppliers_orders']->currencies;

        if (isset($post['filter-orders'])) {

            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            if (isset($post['categories']) && $post['categories'] > 0) {
                // фильтр по категориям товаров
                if (!empty($url))
                    $url .= '&';
                $url .= 'g_cg=' . intval($post['categories']);
            }

            if (isset($post['goods']) && $post['goods'] > 0) {
                // фильтр по товару
                if (!empty($url))
                    $url .= '&';
                $url .= 'by_gid=' . intval($post['goods']);
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['suppliers']) && !empty($post['suppliers'])) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'sp=' . implode(',', $post['suppliers']);
            }

            if (isset($post['client-order']) && !empty($post['client-order'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'co=' . urlencode(trim($post['client-order']));
            }

            if (isset($post['supplier_order_id_part']) && $post['supplier_order_id_part'] > 0) {
                // фильтр по заказу частичный
                if (!empty($url))
                    $url .= '&';
                $url .= 'pso_id=' . $post['supplier_order_id_part'];
            }
            
            if (isset($post['so-status']) && $post['so-status'] > 0) {
                // фильтр по статусу
                if (!empty($url))
                    $url .= '&';
                $url .= 'sst=' . intval($post['so-status']);
            }
            
            if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
                // фильтр по заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'so_id=' . $post['supplier_order_id'];
            }

            if (isset($post['so_st']) && $post['so_st'] > 0) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'so_st=' . $post['so_st'];
            }

            if (isset($post['my']) && !empty($post['my'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'my=1';
            }
            
            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }
        // фильтруем заказы клиентов
        if (isset($post['filters'])) {

            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            if (isset($post['categories']) && $post['categories'] > 0) {
                // фильтр по категориям товаров
                if (!empty($url))
                    $url .= '&';
                $url .= 'g_cg=' . intval($post['categories']);
            }

            if (isset($post['goods']) && $post['goods'] > 0) {
                // фильтр по товару
                if (!empty($url))
                    $url .= '&';
                $url .= 'by_gid=' . intval($post['goods']);
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['accepters']) && !empty($post['accepters'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'acp=' . implode(',', $post['accepters']);
            }

            if (isset($post['engineers']) && !empty($post['engineers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'eng=' . implode(',', $post['engineers']);
            }

            if (isset($post['suppliers']) && !empty($post['suppliers'])) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'sp=' . implode(',', $post['suppliers']);
            }

            if (isset($post['client-order_id']) && $post['client-order_id'] > 0) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'co_id=' . $post['client-order_id'];
            }

            if (isset($post['status']) && !empty($post['status'])) {
                // фильтр по статусу
                if (!empty($url))
                    $url .= '&';
                $url .= 'st=' . implode(',', $post['status']);
            }

            if (isset($post['client-order']) && !empty($post['client-order'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'co=' . urlencode(trim($post['client-order']));
            }

            if (isset($post['categories-last']) && intval($post['categories-last']) > 0) {
                // фильтр категория
                if (!empty($url))
                    $url .= '&';
                $url .= 'dev=' . intval($post['categories-last']);
            }

            if (isset($post['g_categories']) && !empty($post['g_categories'])) {
                // фильтр по категориям товаров
                if (!empty($url))
                    $url .= '&';
                $url .= 'g_cg=' . implode(',', $post['g_categories']);
            }

            if (isset($post['operators']) && !empty($post['operators'])) {
                // фильтр по операторам
                if (!empty($url))
                    $url .= '&';
                $url .= 'op=' . implode(',', $post['operators']);
            }

            if (!isset($post['commission'])) {
                // фильтр по комиссии
                if (!empty($url))
                    $url .= '&';
                $url .= 'cms=1';
            }

            if (isset($post['novaposhta'])) {
                // фильтр по доставке
                if (!empty($url))
                    $url .= '&';
                $url .= 'np=1';
            }

            if (isset($post['warranties'])) {
                // фильтр по доставке
                if (!empty($url))
                    $url .= '&';
                $url .= 'wrn=1';
            }

            if (isset($post['nowarranties'])) {
                // фильтр по доставке
                if (!empty($url))
                    $url .= '&';
                $url .= 'nowrn=1';
            }

            if (isset($post['return'])) {
                // фильтр по доставке
                if (!empty($url))
                    $url .= '&';
                $url .= 'rtrn=1';
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }

        if (isset($post['filter-transactions'])) {
            // фильтрация транзакций
            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            // фильтр по кассам
            if (isset($post['cashboxes']) && !empty($post['cashboxes'])) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'cb=' . implode(',', $post['cashboxes']);
                // искючить
                if (isset($post['include_cashboxes']) && $post['include_cashboxes'] == -1) {
                    $url .= '&cbe=-1';
                }
            }

            // фильтр по категориям
            if (isset($post['categories']) && !empty($post['categories'])) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'cg=' . implode(',', $post['categories']);
                // искючить
                if (isset($post['include_categories']) && $post['include_categories'] == -1) {
                    $url .= '&cge=-1';
                }
            }

            // фильтр по контрагентам
            if (isset($post['contractors']) && !empty($post['contractors'])) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'ct=' . implode(',', $post['contractors']);
                // искючить
                if (isset($post['include_contractors']) && $post['include_contractors'] == -1) {
                    $url .= '&cte=-1';
                }
            }

            // фильтр по контрагентам
            if (isset($post['by']) && !empty($post['by']) && isset($post['by_id']) && $post['by_id'] > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= $post['by'] . '=' . $post['by_id'];
            }
            // фильтр по контрагентам
            if (!isset($post['group']) || $post['group'] != 1) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'grp=1';
            }

            $hash = $post['hash'];

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url) . $hash;

            header('Location: ' . $url);
            exit;
        } elseif (isset($post['cashbox-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создание кассы
            $cashboxes_type = 1;
            $avail = isset($post['avail']) ? 1 : null;
            $avail_in_balance = isset($post['avail_in_balance']) ? 1 : null;
            $avail_in_orders = isset($post['avail_in_orders']) ? 1 : null;

            $cashbox_id = $this->all_configs['db']->query('INSERT INTO {cashboxes} (cashboxes_type, avail, avail_in_balance, avail_in_orders, name)
                VALUES (?i, ?n, ?n, ?n, ?)',
                array($cashboxes_type, $avail, $avail_in_balance, $avail_in_orders, trim($post['title'])), 'id');

            if (isset($post['cashbox_currency'])) {
                foreach ($post['cashbox_currency'] as $cashbox_currency) {
                    if ($cashbox_currency > 0 && array_key_exists($cashbox_currency, $currencies)) {
                        $this->all_configs['db']->query('INSERT IGNORE INTO {cashboxes_currencies} (cashbox_id, currency, amount) VALUES (?i, ?i, ?i)',
                            array($cashbox_id, $cashbox_currency, 0));
                    }
                }
            }
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-cashbox', $mod_id, $cashbox_id));
        } elseif (isset($post['cashbox-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование кассы
            if (!isset($post['cashbox-id']) || $post['cashbox-id'] == 0) {
                header("Location:" . $_SERVER['REQUEST_URI']);
                exit;
            }
            $cashboxes_type = 1;
            $avail = isset($post['avail']) ? 1 : null;
            $avail_in_balance = isset($post['avail_in_balance']) ? 1 : null;
            $avail_in_orders = isset($post['avail_in_orders']) ? 1 : null;

            $ar = $this->all_configs['db']->query('UPDATE {cashboxes} SET cashboxes_type=?i, avail=?n, avail_in_balance=?n, avail_in_orders=?n, name=?
                  WHERE id=?i',
                array($cashboxes_type, $avail, $avail_in_balance, $avail_in_orders, trim($post['title']), $post['cashbox-id']))->ar();

            $this->all_configs['db']->query('DELETE FROM {cashboxes_currencies} WHERE cashbox_id=?i AND id NOT IN(
                    SELECT cashboxes_currency_id_from as cc_id FROM {cashboxes_transactions}
                            WHERE cashboxes_currency_id_from IS NOT NULL
                        UNION
                    SELECT cashboxes_currency_id_from as cc_id FROM {contractors_transactions}
                            WHERE cashboxes_currency_id_from IS NOT NULL
                        UNION
                    SELECT cashboxes_currency_id_to as cc_id FROM {cashboxes_transactions}
                            WHERE cashboxes_currency_id_to IS NOT NULL
                        UNION
                    SELECT cashboxes_currency_id_to as cc_id FROM {contractors_transactions}
                            WHERE cashboxes_currency_id_to IS NOT NULL
                )',
                array($post['cashbox-id']));

            if (isset($post['cashbox_currency'])) {
                foreach ($post['cashbox_currency'] as $cashbox_currency) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {cashboxes_currencies} (cashbox_id, currency) VALUES (?i, ?i)',
                        array($post['cashbox-id'], $cashbox_currency));
                }
            }
            if ($ar) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'edit-cashbox', $mod_id, $post['cashbox-id']));
            }

        } elseif (isset($post['contractor_category-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создание категории
            $avail = isset($post['avail']) ? 1 : null;
            $parent_id = (isset($post['parent_id']) && $post['parent_id'] > 0) ? $post['parent_id'] : 0;

            $contractor_category = $this->all_configs['db']->query('INSERT INTO {contractors_categories}
                (avail, parent_id, name, code_1c, transaction_type, comment) VALUES (?n, ?i, ?, ?, ?i, ?)',
                array($avail, $parent_id, trim($post['title']), trim($post['code_1c']),
                    $post['transaction_type'], trim($post['comment'])), 'id');

            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-contractor_category', $mod_id, $contractor_category));
        } elseif (isset($post['contractor_category-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование категории
            if (!isset($post['contractor_category-id']) || $post['contractor_category-id'] == 0) {
                header("Location:" . $_SERVER['REQUEST_URI']);
                exit;
            }

            $avail = isset($post['avail']) ? 1 : null;
            $parent_id = (isset($post['parent_id']) && $post['parent_id'] > 0) ? $post['parent_id'] : 0;

            $ar = $this->all_configs['db']->query('UPDATE {contractors_categories}
                    SET avail=?n, parent_id=?i, name=?, code_1c=?, transaction_type=?i, comment=? WHERE id=?i',
                array($avail, $parent_id, trim($post['title']), trim($post['code_1c']),
                    $post['transaction_type'], trim($post['comment']), $post['contractor_category-id']))->ar();

            if ($ar) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'edit-contractor_category', $mod_id, $post['contractor_category-id']));
            }
        } elseif (isset($post['cashboxes-currencies-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование валюты
            if (!isset($post['cashbox_cur_name']) || !isset($post['cashbox_short_name'])) {
                header("Location:" . $_SERVER['REQUEST_URI']);
                exit;
            }

            foreach ($post['cashbox_cur_name'] as $currency_id => $name) {
                $short_name = array_key_exists($currency_id, $post['cashbox_short_name']) ? $post['cashbox_short_name'][$currency_id] : '';
                $course = (array_key_exists('cashbox_course', $post) && array_key_exists($currency_id, $post['cashbox_course'])) ? (100 * $post['cashbox_course'][$currency_id]) : $this->course_default;

                $this->all_configs['db']->query('INSERT INTO {cashboxes_courses} (currency, name, short_name, course) VALUES (?i, ?, ?, ?i) ON DUPLICATE KEY
                      UPDATE currency=VALUES(currency), name=VALUES(name), short_name=VALUES(short_name), course=VALUES(course)',
                    array($currency_id, trim($name), trim($short_name), $course));
            }
        }


        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    function get_contractors_categories($type = 0, $arrow = true)
    {
        // тип
        $query = '';
        if ($type > 0) {
            $query = $this->all_configs['db']->makeQuery('WHERE transaction_type=?i', array($type));
        }
        // стрелочка
        $query_arrow = $arrow == true ? ', c.transaction_type as arrow' : '';

        // достаем все категории
        return $this->all_configs['db']->query('SELECT c.id, c.name, c.avail, c.parent_id, c.code_1c,
              c.transaction_type, c.comment ?query FROM {contractors_categories} as c ?query',
            array($query_arrow, $query))->assoc();
    }

    function get_cashboxes_amounts()
    {
        // суммы по валютам
        $amounts = array('all' => 0, 'cashboxes' => array());

        // достаем все кассы
        $cashboxes = $this->all_configs['db']->query('SELECT c.name, c.id, c.avail, c.avail_in_balance, c.avail_in_orders, cc.amount, cc.currency,
              cr.name as cur_name, cr.short_name, cr.course, cr.currency
            FROM {cashboxes} as c
            LEFT JOIN (SELECT id, cashbox_id, amount, currency FROM {cashboxes_currencies})cc ON cc.cashbox_id=c.id
            LEFT JOIN (SELECT currency, name, short_name, course FROM {cashboxes_courses})cr ON cr.currency=cc.currency
            ORDER BY c.id')->assoc();

        if ($cashboxes) {

            //usort($cashboxes, array('accountings', 'akcsort'));

            foreach ($cashboxes as $cashbox) {

                if (!array_key_exists($cashbox['currency'], $amounts['cashboxes'])) {
                    $amounts['cashboxes'][$cashbox['currency']] = array(
                        'short_name' => $cashbox['short_name'],
                        'amount' => $cashbox['amount'],
                        'course' => $cashbox['course'],
                        'currency' => $cashbox['currency'],
                    );
                } else {
                    $amounts['cashboxes'][$cashbox['currency']]['amount'] += $cashbox['amount'];
                }

                $amounts['all'] += ($cashbox['amount'] * ($cashbox['course'] / 100));

                if (!array_key_exists($cashbox['id'], $this->cashboxes)) {
                    $this->cashboxes[$cashbox['id']] = array(
                        'id' => $cashbox['id'],
                        'name' => $cashbox['name'],
                        'avail' => $cashbox['avail'],
                        'avail_in_balance' => $cashbox['avail_in_balance'],
                        'avail_in_orders' => $cashbox['avail_in_orders'],
                        'currencies' => array()
                    );
                }
                if ($cashbox['currency'] > 0) {
                    $this->cashboxes[$cashbox['id']]['currencies'][$cashbox['currency']] = array(
                        'amount' => $cashbox['amount'],
                        'cur_name' => $cashbox['cur_name'],
                        'short_name' => $cashbox['short_name'],
                        //'course' => $cashbox['course'],
                    );
                }
            }
        }

        return $amounts;
    }

    function get_contractors($avail = null, $contractor_category_id = null, $operation = false)
    {
        $query = $query_or = '';

        // активность
        if ($avail !== null) {
            $query = $this->all_configs['db']->makeQuery('?query AND c.avail=?i', array($query, 1));
        }
        // по статье
        if ($contractor_category_id > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND l.contractors_categories_id=?i AND c.id=l.contractors_categories_id',
                array($query, $contractor_category_id));
        }

        // для транзакций
        if ($operation == true) {
            if (array_key_exists('erp-use-id-for-accountings-operations', $this->all_configs['configs'])
                && count($this->all_configs['configs']['erp-use-id-for-accountings-operations']) > 0) {

                $query_or = $this->all_configs['db']->makeQuery('k.id IN (?li) OR',
                    array($this->all_configs['configs']['erp-use-id-for-accountings-operations']));
            }

            $query = $this->all_configs['db']->makeQuery('?query AND (?query k.type IN (?li))',
                array($query, $query_or, array_values($this->all_configs['configs']['erp-use-for-accountings-operations'])));
        }

        // достаем всех контрагентов
        return $this->all_configs['db']->query('SELECT k.id, k.title, l.contractors_categories_id, c.avail,
                  c.transaction_type, c.id as c_id, c.name as contractor_name, k.type, k.comment, k.amount
                FROM {contractors} as k
                LEFT JOIN {contractors_categories_links} as l ON l.contractors_id=k.id
                LEFT JOIN {contractors_categories} as c ON c.id=l.contractors_categories_id
                WHERE 1=1 ?query ORDER BY k.title',
            array($query))->assoc();
    }

    function contractors_options($contractor_category_id, $contractor_id = 0)
    {
        $contractors = null;
        $out = '';

        if (array_key_exists('erp-use-for-accountings-operations', $this->all_configs['configs'])
            && count($this->all_configs['configs']['erp-use-for-accountings-operations']) > 0
            && $contractor_category_id > 0) {

            $contractors = $this->get_contractors(1, $contractor_category_id, true);
        }

        if ($contractors) {

            $out .= '<option value=""></option>';

            foreach ($contractors as $contractor) {
                $out .= '<option ' . ($contractor['id'] == $contractor_id ? 'selected' : '') . ' ';
                $out .= 'value="' . $contractor['id'] . '">' . htmlspecialchars($contractor['title']) . '</option>';
            }
        }

        return $out;
    }

    function preload()
    {
        $this->get_cashboxes_amounts();

        $contractors = $this->get_contractors();

        //$contractors_html = '<p class="text-error">Нет касс.</p>';
        if ($contractors) {
            //$contractors_html = '<table class="table"><thead><tr><td></td><td>Название</td><td>Сумма</td></tr></thead><tbody>';
            foreach ($contractors as $contractor) {

                if (!array_key_exists($contractor['id'], $this->contractors)) {
                    //$contractors_html .= '<tr><td>' . $contractor['id'] . '</td>';
                    //$contractors_html .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?ct=' . $contractor['id'] . '#transactions-contractors">' . $contractor['title'] . '</a></td>';
                    //$contractors_html .= '<td>' . show_price($contractor['amount']) . '</td></tr>';

                    $this->contractors[$contractor['id']] = array(
                        'id' => $contractor['id'],
                        'name' => $contractor['title'],
                        'type' => $contractor['type'],
                        'comment' => $contractor['comment'],
                        'amount' => $contractor['amount'],
                        'contractors_categories_ids' => array(
                            $contractor['contractors_categories_id'] => array(
                                'transaction_type' => $contractor['transaction_type'],
                                'name' => $contractor['contractor_name'],
                                //'avail' => $contractor['avail'],
                            ),
                        ),
                        'transaction_types' => array(
                            $contractor['transaction_type'] => array($contractor['contractors_categories_id']),
                        ),
                        'arrow' => $contractor['transaction_type'],
                    );
                } else {
                    $this->contractors[$contractor['id']]['contractors_categories_ids'][$contractor['contractors_categories_id']] = array(
                        'transaction_type' => $contractor['transaction_type'],
                        'name' => $contractor['contractor_name'],
                        //'avail' => $contractor['avail'],
                    );
                    if (!array_key_exists($contractor['transaction_type'], $this->contractors[$contractor['id']]['transaction_types'])) {
                        $this->contractors[$contractor['id']]['transaction_types'][$contractor['transaction_type']] = array($contractor['contractors_categories_id']);
                    } else {
                        $this->contractors[$contractor['id']]['transaction_types'][$contractor['transaction_type']][] = $contractor['contractors_categories_id'];
                    }
                }
            }
            //$contractors_html .= '</tbody></table>';
        }
    }

    function gencontent()
    {
        $this->preload();

        $out = '<div class="tabbable"><ul class="nav nav-tabs">';
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out .= '<li><a class="click_tab default" data-open_tab="accountings_cashboxes" onclick="click_tab(this, event)" data-toggle="tab" href="#cashboxes">Кассы</a></li>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')) {
            $out .= '<li><a class="click_tab default" data-open_tab="accountings_transactions" onclick="click_tab(this, event)" data-toggle="tab" href="#transactions">Транзакции</a></li>';
        }
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
                || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
                || $this->all_configs['oRole']->hasPrivilege('partner')) {
            $out .= '<li><a class="click_tab default" data-open_tab="accountings_reports" onclick="click_tab(this, event)" data-toggle="tab" href="#reports">Отчеты</a></li>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            //$out .= '<li><a class="click_tab" data-open_tab="accountings_orders_pre" onclick="click_tab(this, event)" data-toggle="tab" href="#orders_pre">Предоплата (заказы)<span class="tab_count hide tc_sum_accountings_orders_pre"></span></a></li>';
            $out .= '<li><a class="click_tab" data-open_tab="accountings_orders" onclick="click_tab(this, event)" data-toggle="tab" href="#a_orders">Заказы<span class="tab_count hide tc_sum_accountings_orders"></span></a>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-contractors')) {
            $out .= '<li><a class="click_tab default" data-open_tab="accountings_contractors" onclick="click_tab(this, event)" data-toggle="tab" href="#contractors">Контрагенты</a>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out .= '<li><a class="click_tab" data-open_tab="accountings_settings" onclick="click_tab(this, event)" data-toggle="tab" href="#settings">Настройки</a></li>';
        }
        $out .= '</ul><div class="tab-content">';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out .= '<div id="cashboxes" class="content_tab tab-pane">';
            $out .= '</div><!--#cashboxes-->';
        }

        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')) {
            $out .= '<div id="transactions" class="content_tab tab-pane">';
            $out .= '</div><!--#transactions-->';
        }

        // отчеты
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
                || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
                || $this->all_configs['oRole']->hasPrivilege('partner')) {
            $out .= '<div id="reports" class="content_tab tab-pane">';
            $out .= '</div><!--#reports-->';
        }

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            // постоплата
            $out .= '<div id="orders_pre" class="content_tab tab-pane">';
            $out .= '</div>';

            $out .= '<div id="a_orders" class="content_tab tab-pane">';
            $out .= '</div><!--#a_orders-->';
        }

        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-contractors')) {
            $out .= '<div id="contractors" class="content_tab tab-pane">';
            $out .= '</div><!--#contractors-->';
        }

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out .= '<div id="settings" class="content_tab tab-pane">';
            $out .= '</div>';
        }

        return $out;
    }

    function form_cashbox($cashboxes_currencies, $cashbox = null, $i = 1)
    {
        $currencies_html = '';
        if ($cashbox) {
            $btn = "<input type='hidden' name='cashbox-id' value='{$cashbox['id']}' /><input type='submit' class='btn' name='cashbox-edit' value='Редактировать' />";
            $title = htmlspecialchars($cashbox['name']);

            foreach ($cashboxes_currencies as $currency) {
                $checked = '';

                if (array_key_exists('currencies', $cashbox) && array_key_exists($currency['currency'], $cashbox['currencies'])) {
                    $c_t = $this->all_configs['db']->query('
                        SELECT COUNT(*) FROM {cashboxes_currencies} as cc
                        LEFT JOIN (SELECT cashboxes_currency_id_from, cashboxes_currency_id_to FROM {contractors_transactions})ct_t ON
                          (cc.id=ct_t.cashboxes_currency_id_from || cc.id=ct_t.cashboxes_currency_id_to)
                        LEFT JOIN (SELECT cashboxes_currency_id_from, cashboxes_currency_id_to FROM {cashboxes_transactions})cb_t ON
                          (cc.id=cb_t.cashboxes_currency_id_from || cc.id=cb_t.cashboxes_currency_id_to)
                        WHERE cc.currency=?i AND cc.cashbox_id=?i AND (ct_t.cashboxes_currency_id_from IS NOT NULL || ct_t.cashboxes_currency_id_to IS NOT NULL
                          || cb_t.cashboxes_currency_id_from IS NOT NULL || cb_t.cashboxes_currency_id_to IS NOT NULL)
                        GROUP BY ct_t.cashboxes_currency_id_from, ct_t.cashboxes_currency_id_to, cb_t.cashboxes_currency_id_from,
                          cb_t.cashboxes_currency_id_to
                        ', array($currency['currency'], $cashbox['id']))->el();

                    if ($c_t && $c_t > 0)
                        $checked = "checked readonly";
                    else
                        $checked = "checked";

                }

                $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' value='{$currency['currency']}' name='cashbox_currency[]' {$checked} type='checkbox' /> {$currency['name']}</label></div>";

            }
            $avail = '';
            $avail_in_balance = '';
            $avail_in_orders = '';
            if ($cashbox['avail'] == 1)
                $avail = 'checked';

            if ($cashbox['avail_in_balance'] == 1)
                $avail_in_balance = 'checked';

            if ($cashbox['avail_in_orders'] == 1)
                $avail_in_orders = 'checked';

        } else {

            foreach ($cashboxes_currencies as $currency) {
                if (array_key_exists($currency['id'], $this->all_configs['suppliers_orders']->currencies) && $this->all_configs['suppliers_orders']->currencies[$currency['id']]['currency-name'] == $this->all_configs['configs']['default-currency'])
                    $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' checked value='{$currency['currency']}' name='cashbox_currency[]' type='checkbox' /> {$currency['name']}</label></div>";
                else
                    $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' value='{$currency['currency']}' name='cashbox_currency[]' type='checkbox' /> {$currency['name']}</label></div>";
            }
            $btn = "<input type='submit' class='btn btn-primary' name='cashbox-add' value='Создать' />";
            $title = '';
            $avail = 'checked';
            $avail_in_balance = '';
            $avail_in_orders = '';
        }

        if ($i == 1) {
            $in = 'in';
            $accordion_title = 'Создать кассу';
        } else {
            $in = '';
            $accordion_title = "Редактировать кассу '{$title}'";
        }

        return "
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_cashboxes' href='#collapse_cashbox_{$i}'>{$accordion_title}</a>
                </div>
                <div id='collapse_cashbox_{$i}' class='panel-collapse collapse {$in}'>
                    <div class='panel-body'>
                        <form method='POST' style='max-width:300px'>
                            <div class='form-group'><label>Название: </label>
                                <input placeholder='введите название кассы' class='form-control' name='title' value='{$title}' />
                            </div>
                            <div class='form-group'>
                                <label>Используемые валюты: </label>
                                {$currencies_html}
                            </div>
                            <div class='form-group'>
                                <div class='controls'>
                                    <div class='checkbox'><label><input type='checkbox' {$avail} class='btn' name='avail' value='1' />Отображать</label></div>
                                    <div class='checkbox'><label><input type='checkbox' {$avail_in_balance} class='btn' name='avail_in_balance' value='1' /> Учитывать в балансе</label></div>
                                    <div class='checkbox'><label><input type='checkbox' {$avail_in_orders} class='btn' name='avail_in_orders' value='1' /> Участвуют в заказах</label></div>
                                </div>
                            </div>
                            <div class='form-group'>{$btn}</div>
                        </form>
                    </div>
                </div>
            </div>
        ";
    }

    function form_contractor($contractor = null, $opened = null)
    {
        $categories = $this->get_contractors_categories();
        if (count($categories) == 0) {
            return '<p class="text-error">Сперва нужно добавить статью.</p>';
        }

        $out = $name = $comment = '';
        if ($contractor) {
            $name = htmlspecialchars($contractor['name']);
            $comment = htmlspecialchars($contractor['comment']);
            $out .= '<div class="panel panel-default"><div class="panel-heading">';// data-toggle="collapse"
            $out .= '<a class="accordion-toggle" data-parent="#accordion_contractors" href="?ct=';
            $out .= $opened == $contractor['id'] ? '' : $contractor['id'];
            $out .= '#settings-contractors">' . $name . '</a></div>';
            $out .= '<div id="collapse_contractor_' . $contractor['id'] . '" class="panel-collapse collapse ';
            $out .= $contractor && $opened == $contractor['id'] ? 'in' : '';
            $out .= '"><div class="panel-body">';
        }
        if (($contractor && $opened == $contractor['id']) || !$contractor) {
            $out .= '<form method="POST" class="form_contractor "><div class="form-group">';
            $out .= '<label>Укажите статьи расходов для контрагента <small>(за что мы платим контрагенту)</small>: </label>';
            $out .= '<div id="add_category_to_' . ($contractor ? $contractor['id'] : 0) . '">';
            $out .= '<select class="multiselect input-small" multiple="multiple" name="contractor_categories_id[]">';
            $categories = $this->get_contractors_categories(1);
            if ($contractor) {
                $out .= build_array_tree($categories, array_keys($contractor['contractors_categories_ids']));
            } else {
                $out .= build_array_tree($categories);
            }
            $out .= '</select></div></div><div class="form-group">';
            $out .= '<label>Укажите статьи приходов для контрагента <small>(за что контрагент нам платит)</small>: </label>';
            $out .= '<div id="add_category_to_' . ($contractor ? $contractor['id'] : 0) . '">';
            $out .= '<select class="multiselect input-small" multiple="multiple" name="contractor_categories_id[]">';
            $categories = $this->get_contractors_categories(2);
            if ($contractor) {
                $out .= build_array_tree($categories, array_keys($contractor['contractors_categories_ids']));
            } else {
                $out .= build_array_tree($categories);
            }
            $out .= '</select></div></div>';
            $out .= '<div class="form-group"><label>ФИО: </label>';
            $out .= '<input placeholder="введите ФИО контрагента" class="input-contractor form-control" name="title" value="' . $name . '" />';
            $out .= '</div><div class="form-group"><label>Комментарий: </label>';
            $out .= '<textarea class="form-control" name="comment" placeholder="введите комментарий к контрагенту">' . $comment . '</textarea>';
            $out .= '</div><div class="form-group"><label class="control-label">Тип контрагента: </label>';
            $out .= '<select class="form-control" name="type"><option value=""></option>';
            foreach ($this->all_configs['configs']['erp-contractors-types'] as $c_id => $c_name) {
                if ($contractor && $c_id == $contractor['type']) {
                    $out .= '<option selected="selected" value="' . $c_id . '">' . $c_name . '</option>';
                } else {
                    $out .= '<option value="' . $c_id . '">' . $c_name . '</option>';
                }
            }
            $out .= '</select></div>';
            $out .= '';
            if ($contractor) {
                if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                    $out .= "
                        <div class='form-group'>
                            <input type='hidden' name='contractor-id' value='{$contractor['id']}' />
                            <input type='button' class='btn btn-primary' onclick='contractor_edit(this, \"{$contractor['id']}\")' value='Редактировать' />
                            <input type='button' onclick='contractor_remove(this, \"{$contractor['id']}\")' class='btn btn-danger contractor-remove' value='Удалить' />
                        </div>
                    ";
                }
                $client_contr = $this->all_configs['db']->query("SELECT id FROM {clients} "
                                                    . "WHERE contractor_id = ?i", array($contractor['id']), 'el');
                $out .= "
                    <div class='form-group'>
                        <label>Клиент:</label> ".
                            ($client_contr ? 
                                '<a href="'.$this->all_configs['prefix'].'clients/create/'.$client_contr.'">'.
                                    $client_contr.
                                '</a>' : 
                                    '<span class="text-danger">Не привязан</span>')
                            ." 
                    </div>
                ";
            }else{
                $out .= '
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Эл. адрес</label>
                        <input type="text" name="email" class="form-control">
                    </div>
                ';
            }
            $out .= '</form>';
        }
        if ($contractor) {
            $out .= '</div></div></div>';
        }

        return $out;
    }

    function form_contractor_category_btn($contractor_category = null)
    {
        $btn = '';

        if ($contractor_category) {
            if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $btn .= "<input type='hidden' name='contractor_category-id' value='{$contractor_category['id']}' />";
                $btn .= "<input type='button' class='btn' onclick='$(\"form.form_contractor_category\").submit();' value='Редактировать' />";
                $btn .= "<input type='button' onclick='contractor_category_remove(this, \"{$contractor_category['id']}\")' class='btn btn-danger contractor_category-remove' value='Удалить' />";
            }
        } else {
            $btn .= "<input type='button' class='btn' onclick='$(\"form.form_contractor_category\").submit();' value='Создать' />";
        }

        return $btn;
    }

    function form_contractor_category($type, $contractor_category = null)
    {
        //$btn = '';

        if ($contractor_category) {
            $code_1c = htmlspecialchars($contractor_category['code_1c']);
            $name = htmlspecialchars($contractor_category['name']);
            $category_html = "<select class='multiselect' name='parent_id'><option value=''>Высшая</option>";
            $categories = $this->get_contractors_categories($type);
            $category_html .= build_array_tree($categories, $contractor_category['parent_id']) . "</select>";
            /*if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $btn .= "<input type='button' class='btn' name='contractor_category-edit' value='Редактировать' />";
                $btn .= "<input type='button' onclick='contractor_category_remove(this, \"{$contractor_category['id']}\")' class='btn btn-danger contractor_category-remove' value='Удалить' />";
            }*/
            $avail = '';
            if ($contractor_category['avail'] == 1)
                $avail = 'checked';
            $id_html = '<div class="form-group"><label>ID: ' . $contractor_category['id'] . '</label></div>';
            $comment = htmlspecialchars($contractor_category['comment']);
            $id_html .= "<input type='hidden' name='contractor_category-edit' value='1' />";
            $id_html .= "<input type='hidden' name='contractor_category-id' value='{$contractor_category['id']}' />";

        } else {
            $category_html = "<select class='multiselect' name='parent_id'><option value=''>Высшая</option>";
            $categories = $this->get_contractors_categories($type);
            $category_html .= build_array_tree($categories) . "</select>";
            $name = '';
            $avail = 'checked';
            $code_1c = '';
            //$btn = "<input type='button' class='btn' name='contractor_category-add' value='Создать' />";
            $id_html = '';
            $comment = '';
            $id_html .= "<input type='hidden' name='contractor_category-add' value='1' />";
        }

        $out = "
            <form method='POST' class='form_contractor_category form-horizontal'>
                <input type='hidden' name='transaction_type' value='{$type}' />

                {$id_html}

                <div class='form-group'><label>Статья: </label>
                    <input class='form-control' placeholder='введите название статьи' name='title' value='{$name}' /></div></div>
                <div class='form-group'><label>Родительская статья: </label>
                    {$category_html}</div></div>
                <!--<div class='form-group'><label>Код 1с: </label>
                    <input class='form-control' placeholder='введите код 1с статьи' name='code_1c' value='{$code_1c}' /></div></div>
                -->
                <div class='form-group'><label>Комментарий: </label><div class='controls'>
                    <textarea class='form-control' name='comment' placeholder='введите комментарий к статье'>{$comment}</textarea></div></div>
                <div class='form-group'>
                    <div class='checkbox'><label><input type='checkbox' {$avail} class='btn' name='avail' value='1' />Отображать</label></div></div>

            </form>";

        return $out;
    }

    function cashboxes_courses()
    {
        // валюты
        return $this->all_configs['db']->query('SELECT id, currency, name, short_name, course
            FROM {cashboxes_courses}')->assoc();
    }

    function get_cashbox_currencies($cashbox_id)
    {
        $currencies_html = '';
        $_currencies = $this->all_configs['db']->query('SELECT cu.currency, co.short_name
                FROM {cashboxes_currencies} cu, {cashboxes_courses} co WHERE cu.cashbox_id=?i AND cu.currency=co.currency',
            array($cashbox_id))->vars();

        if ($_currencies) {
            foreach ($_currencies as $id=>$name) {
                $currencies_html .= '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
            }
        }

        return $currencies_html;
    }

    function export()
    {
        $array = array();
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // допустимые валюты
        $currencies = $this->all_configs['suppliers_orders']->currencies;

        if ($act == 'contractors_transactions')
            $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, true, array(), true, true);
        if ($act == 'cashboxes_transactions')
            $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, false, array(), true, true);
        if ($act == 'reports-turnover')
            $array = $this->accountings_reports_turnover_array();
        //if ($act == 'cashboxes_transactions')
        //    $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, true, 30);

        include_once $this->all_configs['sitepath'] . 'shop/exports.class.php';
        $exports = new Exports();
        $exports->build($array);
    }

    function ajax()
    {
        $data = array(
            'state' => false
        );

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['accountings-manage-page'];



        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав', 'state' => false));
            exit;
        }

        $this->preload();

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    echo json_encode(array('html' => $function['html'], 'state' => true, 'functions' => $function['functions']));
                } else {
                    echo json_encode(array('message' => 'Не найдено', 'state' => false));
                }
                exit;
            }
        }

        // заявки
        if ($act == 'orders-link') {
            $so_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
            $co_id = isset($_POST['so_co']) ? $_POST['so_co'] : 0;
            $data = $this->all_configs['suppliers_orders']->orders_link($so_id, $co_id);
        }

        // управление заказами поставщика
        if ($act == 'so-operations') {
            $this->all_configs['suppliers_orders']->operations(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // экспорт оборот
        if ($act == 'reports_turnover') {
            //table_to_excel($this->all_configs['path'], 'reports_turnover');
        }
        // экспорт
        if ($act == 'cashboxes_transactions') {
            $currencies = $this->all_configs['suppliers_orders']->currencies;
            //$array = $this->all_configs['suppliers_orders']->get_transactions($currencies);
            //table_to_excel($this->all_configs['path'], 'cashboxes_transactions');
        }
        /*
        // транзакций касс
        if (isset($_POST['table']) && $_POST['table'] == 'cashboxes_transactions') {
            //
        }
        // транзакций контрагентов
        if (isset($_POST['table']) && $_POST['table'] == 'contractors_transactions') {
            //$this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, true);
        }*/

        // сумма по транзакциям у контрагента
        if ($act == 'contractor-amount') {
            if (isset($_POST['contractor_id']) && $_POST['contractor_id'] > 0) {
                $amount = $this->all_configs['db']->query('SELECT
                        SUM(IF(t.transaction_type=2, t.value_to, 0)) - SUM(IF(t.transaction_type=1, t.value_from, 0))
                      FROM {contractors_transactions} as t, {contractors_categories_links} as l
                      WHERE l.contractors_id=?i AND t.contractor_category_link=l.id',
                    array($_POST['contractor_id']))->el();
                $data['message'] = show_price(1*$amount);
                $data['state'] = true;
            }
        }

        // добавление нового контрагента
        if ($act == 'contractor-create') {
            
            $data['state'] = true;
            // права
            if ($data['state'] == true && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $data['state'] = false;
                $data['message'] = 'Нет прав';
            }
            // статьи
            if ($data['state'] == true && !isset($_POST['contractor_categories_id']) || count($_POST['contractor_categories_id']) == 0) {
                $data['state'] = false;
                $data['message'] = 'Укажите статью';
            }
            // фио
            if ($data['state'] == true && !isset($_POST['title']) || mb_strlen(trim($_POST['title']), 'UTF-8') == 0) {
                $data['state'] = false;
                $data['message'] = 'Введите ФИО';
            }
            if ($data['state'] == true) {
                // создаем
                $contractor_id = $this->all_configs['db']->query('INSERT IGNORE INTO {contractors}
                        (title, type, comment) VALUES (?, ?i, ?)',
                    array(trim($_POST['title']), $_POST['type'], trim($_POST['comment'])), 'id');

                if ($contractor_id > 0) {
                    foreach ($_POST['contractor_categories_id'] as $contractor_category_id) {
                        if ($contractor_category_id > 0) {
                            $ar = $this->all_configs['db']->query('INSERT IGNORE INTO {contractors_categories_links}
                                (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                                array($contractor_category_id, $contractor_id))->ar();
                        }
                    }
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'add-contractor', $mod_id, $contractor_id));
                    // создаем клиента для контрагента
                    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
                    $email = isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
                    require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
                    $access = new access($this->all_configs, false);
                    $phone = $access->is_phone($phone);
                    if($phone || $email){
                        $exists_client = $access->get_client($email, $phone, true);
                        if($exists_client && !$this->all_configs['db']->query("SELECT contractor_id FROM {clients} WHERE id = ?i", array($exists_client['id']), 'el')){
                            // привязываем к существующему если к нему не привязан контрагент
                            $this->all_configs['db']->query("UPDATE {clients} SET contractor_id = ?i "
                                                           ."WHERE id = ?i", array($contractor_id,$exists_client['id']));
                        }else{
                            // создаем клиента и привязываем
                            $result = $access->registration(array(
                                'email' => $email,
                                'phone' => $phone[0],
                                'fio' => $_POST['title']
                            ));
                            if($result['new']){
                                $this->all_configs['db']->query("UPDATE {clients} SET contractor_id = ?i "
                                                               ."WHERE id = ?i", array($contractor_id,$result['id']));
                            }
                        }
                    }
                } else {
                    $data['state'] = false;
                    $data['message'] = 'Такой контрагент уже существует';
                }
            }
        }
        // редактирование контрагента
        if ($act == 'contractor-edit') {
            $data['state'] = true;
            // права
            if ($data['state'] == true && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $data['state'] = false;
                $data['message'] = 'Нет прав';
            }
            // ид
            if ($data['state'] == true && !isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] == 0) {
                $data['state'] = false;
                $data['message'] = 'Контрагент не найден';
            }
            // статьи
            if ($data['state'] == true && !isset($_POST['contractor_categories_id']) || count($_POST['contractor_categories_id']) == 0) {
                $data['state'] = false;
                $data['message'] = 'Укажите статью';
            }
            // фио
            if ($data['state'] == true && !isset($_POST['title']) || mb_strlen(trim($_POST['title']), 'UTF-8') == 0) {
                $data['state'] = false;
                $data['message'] = 'Введите ФИО';
            }
            if ($data['state'] == true) {
                $ar = $this->all_configs['db']->query('UPDATE {contractors} SET title=?, type=?i, comment=? WHERE id=?i',
                    array(trim($_POST['title']), $_POST['type'], trim($_POST['comment']), $this->all_configs['arrequest'][2]))->ar();

                if ($ar) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'edit-contractor', $mod_id, $this->all_configs['arrequest'][2]));
                }

                $contractor_categories_id = $this->all_configs['db']->query('SELECT contractors_categories_id
                        FROM {contractors_categories_links} WHERE contractors_id=?i',
                    array($this->all_configs['arrequest'][2]))->vars();

                foreach ($contractor_categories_id as $contractor_category_id) {
                    if ($contractor_category_id > 0) {
                        try {
                            $this->all_configs['db']->query('DELETE FROM {contractors_categories_links} WHERE contractors_id=?i
                                    AND contractors_categories_id=?i',
                                array($this->all_configs['arrequest'][2], $contractor_category_id))->ar();
                        } catch (Exception $e) {}
                    }
                }
                // категории
                if (isset($_POST['contractor_categories_id']) && count($_POST['contractor_categories_id']) > 0) {
                    foreach ($_POST['contractor_categories_id'] as $contractor_category_id) {
                        if ($contractor_category_id > 0) {
                            $this->all_configs['db']->query('INSERT IGNORE INTO {contractors_categories_links}
                                    (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                                array($contractor_category_id, $this->all_configs['arrequest'][2]))->ar();
                        }
                    }
                }
            }
        }

        // форма создания транзакции
        if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3'
            || $act == 'begin-transaction-1-co' || $act == 'begin-transaction-2-co'
            || $act == 'begin-transaction-1-so' || $act == 'begin-transaction-2-so') {
            $btn = 'Сохранить';
            // тип транзакции
            $tt = intval(preg_replace("/[^0-9]/", "", $act));
            // сегодня
            $today = date("d.m.Y", time());
            $select_cashbox = '';
            $selected_cashbox = isset($_POST['object_id']) && $_POST['object_id'] > 0 ? $_POST['object_id'] : 0;
            // список форм для редактирования касс
            if (count($this->cashboxes) > 0) {
                $erpct = $this->all_configs['configs']['erp-cashbox-transaction'];

                foreach ($this->cashboxes as $cashbox) {
                    // выбор кассы при транзакции
                    if ($cashbox['avail'] == 1) {
                        // кроме транзитной
                        $select_cashbox .= '<option' . ($cashbox['id'] == $erpct ? ' disabled' : '');
                        $select_cashbox .= ($cashbox['id'] == $selected_cashbox ? ' selected' : '');
                        $select_cashbox .= ' value="' . $cashbox['id'] . '">' . htmlspecialchars($cashbox['name']) . '</option>';
                        $selected_cashbox = $selected_cashbox == 0 ? $cashbox['id'] : $selected_cashbox;
                    }
                }
            }

            $daf = $dc = $dccf = $dcct = ''; // disabled
            $fc = ''; // form class
            $so_id = $co_id = 0; // orders
            $b_id = 0; // chanin body
            $t_extra = 0; // delivery payment
            $amount_from = $amount_to = 0; // amounts
            $client_contractor = 0; // client order contractor

            // контрагенты
            $select_contractors = '';
            $ccg_id = 0;

            // заказ поставщику
            if (isset($_POST['supplier_order_id']) && $_POST['supplier_order_id'] > 0) {
                // выдача
                if ($tt == 1) {
                    $amount_from = $this->all_configs['db']->query('SELECT (count_come * price)
                          FROM {contractors_suppliers_orders} WHERE id=?i',
                            array($_POST['supplier_order_id']))->el() / 100;

                    $ccg_id = $this->all_configs['configs']['erp-so-contractor_category_id_from'];
                    $c_id = $this->all_configs['db']->query('SELECT supplier FROM {contractors_suppliers_orders} WHERE id=?i',
                        array($_POST['supplier_order_id']))->el();
                    $select_contractors = $this->contractors_options($ccg_id, $c_id);

                    $daf = $dc = $dcct = 'disabled';
                }
                $fc .= ' transaction_type-so-' . $tt;
                $so_id = $_POST['supplier_order_id'];
            }

            // заказ клиента
            if (isset($_POST['client_order_id']) && $_POST['client_order_id'] > 0) {
                $co_id = $_POST['client_order_id'];
                $select_query_1 = $this->all_configs['db']->makeQuery('o.sum_paid-o.sum FROM {orders} as o', array());
                $select_query_2 = $this->all_configs['db']->makeQuery('o.sum-o.sum_paid FROM {orders} as o', array());
                $b_id = isset($_POST['b_id']) && $_POST['b_id'] > 0 ? $_POST['b_id'] : $b_id;

                // за доставку
                if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'delivery') {
                    $select_query_1 = $this->all_configs['db']->makeQuery('o.delivery_paid FROM {orders} as o', array());
                    $select_query_2 = $this->all_configs['db']->makeQuery('o.delivery_cost-o.delivery_paid FROM {orders} as o', array());
                    $t_extra = 'delivery';
                }
                // за комиссию
                if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'payment') {
                    $select_query_1 = $this->all_configs['db']->makeQuery('o.payment_paid FROM {orders} as o', array());
                    $select_query_2 = $this->all_configs['db']->makeQuery('o.payment_cost-o.payment_paid FROM {orders} as o', array());
                    $t_extra = 'payment';
                }
                // за предоплату
                if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'prepay') {
                    $select_query_1 = $this->all_configs['db']->makeQuery('o.sum_paid FROM {orders} as o', array());
                    $select_query_2 = $this->all_configs['db']->makeQuery('o.prepay-o.sum_paid FROM {orders} as o', array());
                    $t_extra = 'prepay';
                }
                // конкретная цепочка
                if ($b_id > 0 && (!isset($_POST['transaction_extra']) || ($_POST['transaction_extra'] != 'payment'
                            && $_POST['transaction_extra'] != 'delivery'))) {
                    // выдача
                    if ($tt == 1) {
                        $select_query_1 = $this->all_configs['db']->makeQuery('h.paid FROM {orders} as o
                                LEFT JOIN {chains_headers} as h ON h.order_id=o.id
                                    AND h.id=(SELECT chain_id FROM {chains_bodies} WHERE id=?i)
                                LEFT JOIN {orders_goods} as og ON h.order_goods_id=og.id',
                            array($b_id));
                    }
                    // внесение
                    if ($tt == 2) {
                        $select_query_2 = $this->all_configs['db']->makeQuery('og.price+og.warranties_cost-h.paid
                                FROM {orders} as o
                                LEFT JOIN {chains_headers} as h ON h.order_id=o.id
                                    AND h.id=(SELECT chain_id FROM {chains_bodies} WHERE id=?i)
                                LEFT JOIN {orders_goods} as og ON h.order_goods_id=og.id',
                            array($b_id));
                    }
                }
                // выдача
                if ($tt == 1) {
                    $btn = 'Выдать';
                    $amount_from = $this->all_configs['db']->query('SELECT ?query WHERE o.id=?i GROUP BY o.id',
                            array($select_query_1, $_POST['client_order_id']))->el() / 100;
                }
                // внесение
                if ($tt == 2) {
                    $amount_to = $this->all_configs['db']->query('SELECT ?query WHERE o.id=?i GROUP BY o.id',
                            array($select_query_2, $_POST['client_order_id']))->el() / 100;
                }

                $client_contractor = $this->all_configs['db']->query('SELECT c.contractor_id
                        FROM {orders} as o, {clients} as c WHERE o.id=?i AND o.user_id=c.id',
                    array($_POST['client_order_id']))->el();
            }

            $data['content'] = '<form method="post" id="transaction_form"><fieldset>';

            // категории для транзакции
            $categories = $this->get_contractors_categories(1);
            $select_contractors_categories_to = "<option value=''>Выберите</option>" . build_array_tree($categories, $ccg_id) . "</select>";
            $categories = $this->get_contractors_categories(2);
            $select_contractors_categories_from = "<option value=''>Выберите</option>" . build_array_tree($categories, $ccg_id) . "</select>";

            $cashbox_id = array_key_exists('object_id', $_POST) && $_POST['object_id'] > 0 ? $_POST['object_id'] : $selected_cashbox;
            // валюта
            $cashbox_currencies = $this->get_cashbox_currencies($cashbox_id);

            $data['content'] .= '<input type="hidden" name="transaction_type" id="transaction_type" value="' . $tt . '" />';
            $data['content'] .= '<input type="hidden" name="supplier_order_id" value="' . $so_id . '" />';
            $data['content'] .= '<input type="hidden" name="client_order_id" value="' . $co_id . '" />';
            $data['content'] .= '<input type="hidden" name="b_id" value="' . $b_id . '" />';
            $data['content'] .= '<input type="hidden" name="transaction_extra" value="' . $t_extra . '" />';

            //#transaction_type=>value #transaction_form_body=>.transaction_type-...
            //$data['content'] .= '<div class="btn-group">';
            //$data['content'] .= '<button class="btn ' . ($tt == 1 ? 'active' : '') . '">Выдача</button>';
            //$data['content'] .= '<button class="btn ' . ($tt == 2 ? 'active' : '') . '">Внесение</button>';
            //$data['content'] .= '<button class="btn ' . ($tt == 3 ? 'active' : '') . '">Перемещение</button></div>';

            $data['content'] .= '<div id="transaction_form_body" class="hide-conversion-3 transaction_type-' . $tt . ' ' . $fc . '">';
            $data['content'] .= '<table><thead><tr><td></td><td></td><td>Сумма</td><td>Валюта</td>';
            $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span>Курс</span></td><td class="hide-not-tt-1 hide-not-tt-2"></td></tr></thead><tbody>';
            //* С кассы 1 3
            $data['content'] .= '<tr class="hide-not-tt-2"><td>*&nbsp;С&nbsp;кассы</td>';
            $data['content'] .= '<td><select onchange="select_cashbox(this, 1)" name="cashbox_from" class="form-control cashbox-1">' . $select_cashbox . '</select></td>';
            $data['content'] .= '<td><input ' . $daf . ' class="form-control" onchange="get_course(1)" id="amount-1" type="text" name="amount_from" value="' . $amount_from . '" onkeydown="return isNumberKey(event, this)" /></td>';
            $data['content'] .= '<td><select class="form-control cashbox_currencies-1" onchange="get_course(0)" name="cashbox_currencies_from">' . $cashbox_currencies . '</select></td>';
            $onchange = '
                $(\'#amount-2\').val(($(\'#amount-1\').val()*$(\'#conversion-course-1\').val()).toFixed(2));
                if ($(\'#amount-2\').val() > 0)
                    $(\'#conversion-course-2\').val(($(\'#amount-1\').val()/$(\'#amount-2\').val()).toFixed(4));
                else
                    $(\'#conversion-course-2\').val(0.0000);';
            $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span><input id="conversion-course-1" onchange="' . $onchange . '" class="input-mini" onkeydown="return isNumberKey(event, this)" type="text" value="1.0000" name="cashbox_course_from"/></span></td>';
            $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion" onclick="get_course(0)"><span><small>Прямой</small><br /><small id="conversion-course-db-1">1.0000</small></span></td></tr>';
            //* В кассу 2 3
            $data['content'] .= '<tr class="hide-not-tt-1"><td>* В кассу</td>';
            $data['content'] .= '<td><select onchange="select_cashbox(this, 2)" name="cashbox_to" class="form-control cashbox-2">' . $select_cashbox . '</select></td>';
            $onchange = '
                if ($(\'#amount-1\').val() > 0 && $(\'#amount-2\').val() > 0) {
                    $(\'#conversion-course-1\').val(($(\'#amount-2\').val()/$(\'#amount-1\').val()).toFixed(4));
                    $(\'#conversion-course-2\').val(($(\'#amount-1\').val()/$(\'#amount-2\').val()).toFixed(4));
                } else {
                    $(\'#conversion-course-1\').val(0.0000);
                    $(\'#conversion-course-2\').val(0.0000);
                }';
            $data['content'] .= '<td class="hide-conversion"><span><input class="form-control" onchange="' . $onchange . '" id="amount-2" type="text" name="amount_to" value="' . $amount_to . '" onkeydown="return isNumberKey(event, this)" /></span></td>';
            $data['content'] .= '<td><select class="form-control cashbox_currencies-2" onchange="get_course(0)" name="cashbox_currencies_to">' . $cashbox_currencies . '</select></td>';
            $onchange = '
                if ($(\'#conversion-course-2\').val() > 0)
                    $(\'#amount-2\').val(($(\'#amount-1\').val()/$(\'#conversion-course-2\').val()).toFixed(2));
                else
                    $(\'#amount-2\').val(0.0000);
                if ($(\'#amount-2\').val() > 0)
                    $(\'#conversion-course-1\').val(($(\'#amount-2\').val()/$(\'#amount-1\').val()).toFixed(4));
                else
                    $(\'#conversion-course-1\').val(0.0000);';
            $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span><input id="conversion-course-2" onchange="' . $onchange . '" class="form-control" onkeydown="return isNumberKey(event, this)" type="text" value="1.0000" name="cashbox_course_to"/></span></td>';
            $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion" onclick="get_course(0)"><span><small>Обратный</small><br /><small id="conversion-course-db-2">1.0000</small></span></td></tr>';
            if ($co_id == 0) {
                //* Статья 1
                $data['content'] .= '<tr class="hide-not-tt-2 hide-not-tt-3"><td>* Статья</td>';
                $data['content'] .= '<td><select ' . $dcct . ' id="contractor_category-1" class="multiselect form-control" onchange="select_contractor_category(this, 1)" name="contractor_category_id_to">';
                $data['content'] .= $select_contractors_categories_to . '</select>';
                $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_expense';
                $data['content'] .= '</select><a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td></tr>';
                //* Статья 2
                $data['content'] .= '<tr class="hide-not-tt-1 hide-not-tt-3"><td>* Статья</td>';
                $data['content'] .= '<td><select ' . $dccf . ' id="contractor_category-2" class="multiselect input-medium" onchange="select_contractor_category(this, 2)" name="contractor_category_id_from">';
                $data['content'] .= $select_contractors_categories_from . '</select>';
                $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_income';
                $data['content'] .= '<a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td></tr>';
                //* Контрагент 1 2
                $data['content'] .= '<tr class="hide-not-tt-3"><td>*&nbsp;Контрагент</td>';
                $data['content'] .= '<td><select ' . $dc . ' class="form-control select_contractors" name="contractors_id">' . $select_contractors . '</select>';
                $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-contractors';
                $data['content'] .= '<a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td></tr>';
            }
            // только обычные транзакции
            if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
                // Без внесения на баланс 1
                $content = '(Ставим птичку в случае, если данная выплата производится за услуги или расходные материалы. ';
                $content .= 'Не ставим птичку - если оплата производится за приобретаемые оборотные активы)';
                $data['content'] .= '<tr class="hide-not-tt-2 hide-not-tt-3 hide-not-tt-so-1"><td colspan="2">';
                $data['content'] .= '<div class="checkbox"><label class="popover-info" data-original-title="" data-content="' . $content . '">';
                $js = '
                    if (this.checked) {
                        if (!confirm(\'Не зачислять контрагенту на баланс?\')) {
                            this.checked=false;
                        }
                    }';
                $data['content'] .= '<input type="checkbox" onchange="javascript:' . $js . '" name="without_contractor" value="1"/>Без внесения на баланс</label></div></td></tr>';
                // Без списания c баланса 2
                $content = '(Птичку ставим - когда поступление денежных средств не связано с приобретением или возвратом оборотных активов)';
                $js = 'if (this.checked) { if (!confirm(\'Не списывать у контрагента с баланса?\')) { this.checked=false; } }';
                $data['content'] .= '<tr class="hide-not-tt-1 hide-not-tt-3"><td colspan="2">';
                $data['content'] .= '<div class="checkbox"><label class="popover-info" data-original-title="" data-content="' . $content . '">';
                $data['content'] .= '<input type="checkbox" onchange="javascript:' . $js . '" name="without_contractor" value="1"/>Без списания с баланса</label></div></td></tr>';
            }
            // Списать с баланса контрагента (заказ клиента)
            if ($co_id > 0 && ($tt == 1 || $tt == 2) && $client_contractor > 0) {//TODO have contractor
                //onchange='transaction_with_supplier(this, {$this->all_configs['configs']['erp-cashbox-transaction']})'
                $ct = $this->all_configs['configs']['erp-cashbox-transaction'];
                $js = '
                    if (this.checked) {
                        $(\'.cashbox-1, .cashbox-2\').val(' . $ct . ').prop(\'disabled\', true);
                    } else {
                        $(\'.cashbox-1, .cashbox-2\').val(' . $selected_cashbox . ').prop(\'disabled\',false);
                    }';
                $data['content'] .= '<tr><td colspan="6"><label class="checkbox">';
                $data['content'] .= '<input name="client_contractor" value="1" type="checkbox" onchange="javascript:' . $js . '"/> ';
                $data['content'] .= $tt == 2 ? 'Списать с баланса контрагента' : 'Зачислить на баланс контрагента';
                $data['content'] .= '</label></td></tr>';
            }
            // только обычные транзакции или выдача за заказ клиента
            if ($act == 'begin-transaction-1-co' || $act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
                // Примечание 1 2 3
                $data['content'] .= '<tr><td colspan="2"><textarea class="form-control" name="comment" placeholder="примечание"></textarea></td>';
                // только обычные транзакции
                if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
                    //$data['content'] .= '<td colspan="4" class="center"><input type="text" name="date_transaction" class="input-small date-pickmeup" data-pmu-format="d.m.Y" value="' . $today .'" /></td>';
                    $data['content'] .= '<td colspan="4" class="center"><div class="datetimepicker">
                        <input class="form-control" data-format="yyyy-dd-MM" type="text" name="date_transaction" value="' . $today . '" />
                        <span class="add-on"><i class="glyphicon glyphicon-calendar" data-time-icon="glyphicon glyphicon-time" data-date-icon="glyphicon glyphicon-calendar"></i></span>
                        </div></div>';
                }
                $data['content'] .= '</tr>';
            }
            //$data['content'] .= '<tr><td colspan="2"></td><td colspan="4" class="center"></td></tr>';
            $data['content'] .= '</tbody></table></div></fieldset></form>';
            $data['btns'] = '<button type="button" onclick="create_transaction(this)" class="btn btn-success">' . $btn . '</button>';

            $data['functions'] = array('reset_multiselect()');
            $data['state'] = true;
        }

        // форма создания категории расход контрагента
        if ($act == 'create-cat-expense') {
            $data['state'] = true;
            if (array_key_exists('object_id', $_POST)) {
                $contractor_category = $this->all_configs['db']->query('SELECT * FROM {contractors_categories} WHERE id=?i',
                    array($_POST['object_id']))->row();
            } else {
                $contractor_category = null;
            }
            $data['btns'] = $this->form_contractor_category_btn($contractor_category);
            $data['content'] = $this->form_contractor_category(1, $contractor_category);
            $data['functions'] = array('reset_multiselect()');
        }

        // форма создания категории расход контрагента
        if ($act == 'create-cat-income') {
            $data['state'] = true;
            if (array_key_exists('object_id', $_POST)) {
                $contractor_category = $this->all_configs['db']->query('SELECT * FROM {contractors_categories} WHERE id=?i',
                    array($_POST['object_id']))->row();
            } else {
                $contractor_category = null;
            }
            $data['btns'] = $this->form_contractor_category_btn($contractor_category);
            $data['content'] = $this->form_contractor_category(2, $contractor_category);
            $data['functions'] = array('reset_multiselect()');
        }

        // форма создания контрагента
        if ($act == 'create-contractor-form' ) {
            $data['state'] = true;
            $data['content'] = $this->form_contractor();
            $data['functions'] = array('reset_multiselect()');
            $data['btns'] = "<input type='button' class='btn btn-success' onclick='contractor_create(this)' value='Создать' />";
        }

        // Кредит Отказ
        if ($act == 'accounting-credit-denied') {
            if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Кредит уже отказан', 'error' => true));
                exit;
            }
            $order = $this->all_configs['db']->query('SELECT id, status FROM {orders} WHERE id=?i',
                array($_POST['order_id']))->row();

            if (!$order || $order['status'] != $this->all_configs['configs']['order-status-loan-wait']) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Кредит уже отказан', 'error' => true));
                exit;
            }

            $this->all_configs['db']->query('UPDATE {orders} SET status=?i WHERE id=?i', array($this->all_configs['configs']['order-status-loan-denied'], $_POST['order_id']));

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Успешно'));
            exit;
        }

        // Кредит одобрен, документы готовы
        if ($act == 'accounting-credit-approved') {
            if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Кредит уже одобрен', 'error' => true));
                exit;
            }
            $order = $this->all_configs['db']->query('SELECT id, status FROM {orders} WHERE id=?i', array($_POST['order_id']))->row();

            if (!$order || $order['status'] != $this->all_configs['configs']['order-status-loan-wait']) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Кредит уже одобрен', 'error' => true));
                exit;
            }

            $this->all_configs['db']->query('UPDATE {orders} SET status=?i WHERE id=?i', array($this->all_configs['configs']['order-status-loan-approved'], $_POST['order_id']));

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Успешно'));
            exit;
        }

        // достаем всех котрагентов по категории
        if ($act == 'get-contractors-by-category_id') {
            if (isset($_POST['contractor_category_id']) && $_POST['contractor_category_id'] > 0) {

                $data['contractors'] = $this->contractors_options($_POST['contractor_category_id']);
                $data['state'] = true;
            }
        }

        // достаем все валюты кассы
        if ($act == 'get-cashbox-currencies') {
            $data['state'] = true;
            $data['currencies'] = $this->get_cashbox_currencies(array_key_exists('cashbox_id', $_POST) ? $_POST['cashbox_id'] : 0);
        }

        // добавление валюты
        if ($act == 'add-currency') {

            if (!isset($_POST['currency_id']) || $_POST['currency_id'] == 0 || !array_key_exists($_POST['currency_id'], $this->all_configs['suppliers_orders']->currencies)) {
                $data['msg'] = 'Нет такой валюты';
            } else {

                $name = $this->all_configs['suppliers_orders']->currencies[$_POST['currency_id']]['name'];
                $short_name = $this->all_configs['suppliers_orders']->currencies[$_POST['currency_id']]['shortName'];
                $course = $course = $this->course_default;

                $ar = $this->all_configs['db']->query('INSERT IGNORE INTO {cashboxes_courses} (currency, name, short_name, course) VALUES (?i, ?, ?, ?i)',
                    array($_POST['currency_id'], $name, $short_name, $course))->ar();

                // история
                if ($ar) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'add-to-cashbox-currency', $mod_id, $_POST['currency_id']));
                }

                $courses_html = '';
                $new_courses = $this->all_configs['suppliers_orders']->currencies;
                // валюты
                $cashboxes_currencies = $this->cashboxes_courses();

                foreach ($cashboxes_currencies as $cashbox_currency) {
                    if (array_key_exists($cashbox_currency['currency'], $new_courses))
                        unset($new_courses[$cashbox_currency['currency']]);

                    $courses_html .= "<tr>
                        <td><input type='text' class='form-control' name='cashbox_cur_name[{$cashbox_currency['currency']}]' placeholder='Наименование' value='{$cashbox_currency['name']}' /></td>
                        <td><input type='text' class='form-control' name='cashbox_short_name[{$cashbox_currency['currency']}]' placeholder='Сокращение' value='{$cashbox_currency['short_name']}' /></td>";
                    if (array_key_exists($cashbox_currency['id'], $this->all_configs['suppliers_orders']->currencies) && $this->all_configs['suppliers_orders']->currencies[$cashbox_currency['id']]['currency-name'] == $this->all_configs['configs']['default-currency']) {
                        $courses_html .= "<td></td><td></td>";
                    } else {
                        $price = show_price($cashbox_currency['course']);
                        $courses_html .= "
                            <td>1 {$cashbox_currency['short_name']} = <input class='form-control' type='text' name='cashbox_course[{$cashbox_currency['currency']}]' placeholder='Курс' value='{$price}' onkeydown='return isNumberKey(event, this)' /></td>
                            <td><i class='glyphicon glyphicon-remove remove_currency' onclick='remove_currency(this)' data-currency_id='{$cashbox_currency['currency']}'></i></td>";
                    }
                    $courses_html .= '</tr>';
                }
                $courses_html .= "<tr><td colspan='4'><input type='submit' class='btn btn-primary' name='cashboxes-currencies-edit' value='Сохранить' /></td></tr>";

                // добавить валюту
                $add_course_html = '<option value="">Не выбрана валюта...</option>';
                if (count($new_courses) > 0) {
                    foreach ($new_courses as $new_course_id => $new_course) {
                        $add_course_html .= "<option value='{$new_course_id}'>{$new_course['name']} [{$new_course['shortName']}]</option>";
                    }
                }

                $data['add'] = $add_course_html;
                $data['show'] = $courses_html;
                $data['state'] = true;
            }
        }

        // удаление валюты
        if ($act == 'remove-currency') {
            $check = true;
            if (!isset($_POST['currency_id']) || $_POST['currency_id'] == 0) {
                $data['msg'] = 'Нет такой валюты';
                $check = false;
            }

            if ($check == true) {
                $transactions = $this->all_configs['db']->query('SELECT count(t.id) FROM {cashboxes_transactions} as t, {cashboxes_currencies} as c
                    WHERE (t.cashboxes_currency_id_from=c.id OR t.cashboxes_currency_id_to=c.id) AND c.currency=?i',
                    array($_POST['currency_id']))->el();

                $c_transactions = $this->all_configs['db']->query('SELECT count(t.id) FROM {contractors_transactions} as t, {cashboxes_currencies} as c
                    WHERE (t.cashboxes_currency_id_from=c.id OR t.cashboxes_currency_id_to=c.id) AND c.currency=?i',
                    array($_POST['currency_id']))->el();

                if (($transactions && $transactions > 0) || ($c_transactions && $c_transactions > 0)) {
                    $check = false;
                    $data['msg'] = 'С данной валютой у вас уже имеются операции';
                }
            }

            if ($check == true) {
                $this->all_configs['db']->query('DELETE FROM {cashboxes_currencies} WHERE currency=?i', array($_POST['currency_id']));
                $ar = $this->all_configs['db']->query('DELETE FROM {cashboxes_courses} WHERE currency=?i', array($_POST['currency_id']))->ar();
                $data['state'] = true;

                // история
                if ($ar) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'remove-global-cashbox-course', $mod_id, $_POST['currency_id']));
                }

                $new_courses = $this->all_configs['suppliers_orders']->currencies;
                // валюты
                $cashboxes_currencies = $this->cashboxes_courses();

                foreach ($cashboxes_currencies as $cashbox_currency) {
                    if (array_key_exists($cashbox_currency['currency'], $new_courses))
                        unset($new_courses[$cashbox_currency['currency']]);
                }

                // добавить валюту
                $add_course_html = '<option value="">Не выбрана валюта...</option>';
                if (count($new_courses) > 0) {
                    foreach ($new_courses as $new_course_id => $new_course) {
                        $add_course_html .= "<option value='{$new_course_id}'>{$new_course['name']} [{$new_course['shortName']}]</option>";
                    }
                }
                $data['add'] = $add_course_html;
            }
        }

        // курс
        if ($act == 'get-course') {
            if (isset($_POST['transaction_type']) && $_POST['transaction_type'] == 3) {
                $course_db_1 = $this->course_default; // default course
                $course_db_2 = $this->course_default; // default course

                $cur_1 = isset($_POST['cashbox_currencies_from']) ? $_POST['cashbox_currencies_from'] : null;
                $cur_2 = isset($_POST['cashbox_currencies_to']) ? $_POST['cashbox_currencies_to'] : null;

                $data['noconversion'] = false;

                // разные курсы
                if ($cur_1 && $cur_2 && $cur_1 != $cur_2) {
                    $data['noconversion'] = true;

                    if (isset($_POST['cashbox_currencies_to']) && isset($_POST['cashbox_currencies_from'])) {
                        $to = $this->all_configs['db']->query('SELECT course FROM {cashboxes_courses} WHERE currency=?i',
                            array($_POST['cashbox_currencies_to']))->el();
                        $from = $this->all_configs['db']->query('SELECT course FROM {cashboxes_courses} WHERE currency=?i',
                            array($_POST['cashbox_currencies_from']))->el();

                        if ($to && $from && $from > 0 && $to > 0) {
                            $course_db_1 = $from / $to * 100;
                            $course_db_2 = $to / $from * 100;
                        }
                    }
                }

                if (isset($_GET['course-from-post']) && $_GET['course-from-post'] == 1
                    && isset($_POST['cashbox_course_from']) && isset($_POST['cashbox_course_to'])) {
                    $course_1 = $_POST['cashbox_course_from'] * 100;
                    $course_2 = $_POST['cashbox_course_to'] * 100;
                } else {
                    $course_1 = $course_db_1;
                    $course_2 = $course_db_2;
                }

                $data['course-1'] = show_price($course_1, 4);
                $data['course-2'] = show_price($course_2, 4);

                $data['course-db-1'] = show_price($course_db_1, 4);
                $data['course-db-2'] = show_price($course_db_2, 4);

                if (isset($_POST['amount_from'])) {
                    $data['amount-2'] = show_price($_POST['amount_from'] * $course_1, 2);
                }

                $data['state'] = true;
            }
        }

        // удаляем контрагента
        if ($act == 'remove-contractor') {
            if (!isset($_POST['contractor_id']) || $_POST['contractor_id'] == 0) {
                $data['msg'] = 'Такого контрагента не существует';
            } else {
                $count_t = 0;
                // количество транзакций
                if (array_key_exists('erp-use-for-accountings-operations', $this->all_configs['configs'])
                    && count($this->all_configs['configs']['erp-use-for-accountings-operations']) > 0) {
                    $query = '';
                    if (array_key_exists('erp-use-id-for-accountings-operations', $this->all_configs['configs'])
                        && count($this->all_configs['configs']['erp-use-id-for-accountings-operations']) > 0) {
                        $query = $this->all_configs['db']->makeQuery('c.id IN (?li) OR',
                            array($this->all_configs['configs']['erp-use-id-for-accountings-operations']));
                    }
                    $count_t = $this->all_configs['db']->query('SELECT count(t.id)
                            FROM {cashboxes_transactions} as t, {contractors_categories_links} as l, {contractors} as c
                            WHERE t.contractor_category_link=l.id AND l.contractors_id=c.id AND c.id=?i
                              AND (?query c.type IN (?li))',
                        array($_POST['contractor_id'], $query,
                            array_values($this->all_configs['configs']['erp-use-for-accountings-operations'])))->el();
                }

                if ($count_t > 0) {
                    $data['msg'] = 'Контрагент содержит операции, его нельзя удалить';
                } else {
                    $ar = $this->all_configs['db']->query('DELETE FROM {contractors} WHERE id=?i', array($_POST['contractor_id']))->ar();

                    // история
                    if ($ar) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'remove-contractor', $mod_id, $_POST['contractor_id']));
                    }

                    $data['state'] = true;
                }
            }
        }

        // удаляем категорию
        if ($act == 'remove-category') {
            if (!isset($_POST['category_id']) || $_POST['category_id'] == 0) {
                $data['msg'] = 'Такой статьи не существует';
            } else {
                // количество подкатегорий
                $count_c = $this->all_configs['db']->query('SELECT count(id) FROM {contractors_categories} WHERE parent_id=?i', array($_POST['category_id']))->el();
                // количество транзакций
                $count_t = $this->all_configs['db']->query('SELECT count(t.id) FROM {cashboxes_transactions} as t, {contractors_categories_links} as l, {contractors_categories} as c
                    WHERE t.contractor_category_link=l.id AND l.contractors_categories_id=c.id AND c.id=?i', array($_POST['category_id']))->el();

                if ($count_c > 0 || $count_t > 0) {
                    if ($count_c > 0)
                        $data['msg'] = 'Статья содержит подстатьи, чтобы удалить перенесите их в другие статьи';
                    if ($count_t > 0)
                        $data['msg'] = 'Статья содержит операции, ее нельзя удалить';
                } else {
                    $ar = $this->all_configs['db']->query('DELETE FROM {contractors_categories} WHERE id=?i', array($_POST['category_id']))->ar();

                    // история
                    if ($ar) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'remove-contractors-category', $mod_id, $_POST['category_id']));
                    }

                    $data['state'] = true;
                }
            }
        }

        // создаем транзакцию
        if ($act == 'create-transaction') {
            $data = $this->all_configs['chains']->create_transaction($_POST, $mod_id);
        }


        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    function month_select($y = null)
    {
        $out = '';

        $cur_year = date('Y', time());
        $cur_month = (isset($_GET['df']) && !empty($_GET['df'])) ? date('m', strtotime($_GET['df'])) : date('m', time());

        if ($y == null)
            $year = $cur_year;
        else
            $year = $y;

        if ($y == null) {
            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0];
            $out .= '<select class="form-control" onchange="window.location.href=\'' . $url . '?\' + this.value + \'#transactions\'">';
        }

        //$out .= '<option class="get_months" value="">' . ($year-1) . '</option>';

        foreach ($this->months as $number_month => $month) {
            $out .= '<option ' . (($cur_month == $number_month) ? 'selected' : '') . ' value="df=01.' . $number_month . '.' . $year . '&dt=' . date('t.' . $number_month . '.' . $year, strtotime('01.' . $number_month . '.' . $year)) . '">' . $month . (($year == $cur_year) ? '' : ', ' . $y) . '</option>';
        }

        //$out .= '<option class="get_months"  value="">' . ($year+1) . '</option>';

        if ($y == null)
            $out .= '</select>';

        return $out;
    }

    function transaction_filters($contractors = false)
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : date('01.m.Y', time())) . ' - ' .
            (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : date('t.m.Y', time()));

        $out = '<form method="post">';
        $out .= '<div class="form-group">
                    <label>Транзакции за:</label>
                    <div class="row container-fluid">
                        <div class="col-sm-3">
                            '.$this->month_select().'
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="date" value="' . $date . '" class="form-control daterangepicker" />
                        </div>
                        <div class="col-sm-3">
                            <a class="hash_link" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?df=' . date('01.01.Y', time()) . '&dt=' . date('31.12.Y', time()) . (($contractors == true) ? '#transactions-contractors' : '#transactions-cashboxes') . '">Весь ' . date('Y', time()) . ' год</a>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Кассы:</label>
                    <div class="row container-fluid">
                        <div class="col-sm-3">
                            <select class="form-control" name="include_cashboxes"><option value="1">Показать</option><option ' . ((isset($_GET['cbe']) && $_GET['cbe'] == -1) ? 'selected' : '') . ' value="-1">Исключить</option></select>
                        </div>
                        <div class="col-sm-3">
                            <select class="multiselect input-small" name="cashboxes[]" multiple="multiple">
                            '.build_array_tree($this->cashboxes, ((isset($_GET['cb'])) ? explode(',', $_GET['cb']) : array())).'
                            </select>
                        </div>
                    </div>
                </div>
        
        <div class="form-group"><label>Статьи:</label>';
        $out .= '<div class="row container-fluid"><div class="col-sm-3"><select class="form-control" name="include_categories"><option value="1">Показать</option><option ' . ((isset($_GET['cge']) && $_GET['cge'] == -1) ? 'selected' : '') . ' value="-1">Исключить</option></select>';
        $out .= '</div><div class="col-sm-3"><select class="multiselect form-control" name="categories[]" multiple="multiple">';
        $categories = $this->get_contractors_categories();
        $out .= build_array_tree($categories, ((isset($_GET['cg'])) ? explode(',', $_GET['cg']) : array()));
        $out .= '</select></div></div></div>';
        
        $out .= '<div class="form-group"><label>Контрагенты:</label>';
        $out .= '<div class="row container-fluid"><div class="col-sm-3"><select class="form-control" name="include_contractors"><option value="1">Показать</option><option ' . ((isset($_GET['cte']) && $_GET['cte'] == -1) ? 'selected' : '') . ' value="-1">Исключить</option></select>';
        $out .= '</div><div class="col-sm-3"><select class="multiselect form-control" name="contractors[]" multiple="multiple">';
        $out .= build_array_tree($this->contractors, ((isset($_GET['ct'])) ? explode(',', $_GET['ct']) : array()));
        $out .= '</select></div></div></div>';
        
        $out .= '<div class="form-group"><label class="control-label">По:</label>';
        $value = (isset($_GET['o_id']) && $_GET['o_id'] > 0) ? $_GET['o_id'] : ((isset($_GET['s_id']) && $_GET['s_id'] > 0) ? $_GET['s_id'] : ((isset($_GET['t_id']) && $_GET['t_id'] > 0) ? $_GET['t_id'] : ''));
        $out .= '<div class="row container-fluid"><div class="col-sm-3"><input class="form-control" value="' . $value . '" onkeydown="return isNumberKey(event, this)" type="text" name="by_id" placeholder="Введите ид" />';
        $out .= '</div><div class="col-sm-3"><select class="form-control" name="by"><option value="0"></option>';
        $out .= '<option ' . ((isset($_GET['o_id']) && $_GET['o_id'] > 0) ? 'selected' : '') . ' value="o_id">Заказу клиента</option>';
        $out .= '<option ' . ((isset($_GET['s_id']) && $_GET['s_id'] > 0) ? 'selected' : '') . ' value="s_id">Заказу поставщика</option>';
        $out .= '<option ' . ((isset($_GET['t_id']) && $_GET['t_id'] > 0) ? 'selected' : '') . ' value="t_id">Транзакции касс</option>';
        $out .= '</select></div></div></div>';
        $out .= '<div class="form-group"><div class="checkbox"><label class="">';
        if (isset($_GET['grp']) && $_GET['grp'] == 1)
            $out .= '<input type="checkbox" name="group" value="1" />';
        else
            $out .= '<input type="checkbox" checked name="group" value="1" />';
        $out .= 'Группировать</label></div></div>';
        $out .= '<div class="form-group"><div class="controls"><input class="btn btn-primary" type="submit" name="filter-transactions" value="Применить" /></div></div>';

        if ($contractors == true)
            $out .= '<input type="hidden" name="hash" value="#transactions-contractors" />';
        else
            $out .= '<input type="hidden" name="hash" value="#transactions-cashboxes" />';

        $out .= '</form>'; //.form-horizontal

        return $out;
    }

    function accountings_cashboxes()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $amounts = $this->get_cashboxes_amounts();
            // день
            $day = date("d.m.Y", time());
            $day_html = $day;
            //$query_day = date("Y-m-d", time());
            if (isset($_GET['d']) && !empty($_GET['d'])) {
                $days = explode('-', $_GET['d']);
                $day_html = urldecode(trim($_GET['d']));
                $day = trim($days[0]);
            }

            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            //if ($this->all_configs['oRole']->hasPrivilege('site-administration'))
            //    $out .= '<div class="go-to-settings"><a href="#settings-cashboxes" onclick="init_hash(\'#settings-cashboxes\')">Создать/редактировать кассу</a></div>';

            $out = "<form class='date-filter form-inline' method='get'>"
                  ."<div class='input-group'><input type='text' name='d' class='form-control daterangepicker_single' value='{$day_html}' />"
                  ."<span class='input-group-btn'><input class='btn' type='submit' value='Применить' /></span></div>";
            // сумма по кассам если дата не сегодня
            //if ($today != $day) {
            $amounts_by_day = $this->all_configs['db']->query('SELECT a.amount, a.cashboxes_currency_id, c.course
                    FROM {cashboxes_amount_by_day} as a, {cashboxes_courses} as c
                    WHERE DATE_FORMAT(a.date_add, "%d.%m.%Y")=? AND c.currency=a.cashboxes_currency_id',
                array($day))->assoc();

            if ($amounts_by_day) {
                $out .= '<p>На ' . $day . '. Всего: ';
                $all_amount = 0;
                //$default_currency = '';
                $out_amounts = '';
                foreach ($amounts_by_day as $amount_by_day) {
                    if (array_key_exists($amount_by_day['cashboxes_currency_id'], $currencies)) {
                        //if ($currencies[$amount_by_day['cashboxes_currency_id']]['default'] == 1)
                        //    $default_currency = $currencies[$amount_by_day['cashboxes_currency_id']]['shortName'];
                        $all_amount += $amount_by_day['amount'] * ($amount_by_day['course'] / 100);
                        $out_amounts .= show_price($amount_by_day['amount']) . ' ' . $currencies[$amount_by_day['cashboxes_currency_id']]['shortName'] . '  ';
                    }
                }
                if ($this->all_configs['configs']['manage-actngs-in-1-amount'] == true)
                    $out .= show_price($all_amount) . (empty($out_amounts) ? '' : ' (' . $out_amounts . ')') . '</p>';
                else
                    $out .= (empty($out_amounts) ? '' : $out_amounts) . '</p>';
            }
            //}
            $out .= '</form>';
            // достаем прибыль текущего бухгалтера
            //$this->all_configs['db']->query('SELECT FROM {warehouses} WHERE ');
            $out .= '<p>Всего: ' . ($this->all_configs['configs']['manage-actngs-in-1-amount'] == true ? show_price($amounts['all']) : '');
            $out .= $this->all_configs['configs']['manage-actngs-in-1-amount'] == true ? ' (' : '';
            $total_cashboxes = $this->total_cashboxes($amounts);
            $out .= $total_cashboxes['html'];
            $out .= $this->all_configs['configs']['manage-actngs-in-1-amount'] == true ? ')' : '';
            $out .= '<p>';
            // выводим кассы с возможностю транзакций
            if (count($this->cashboxes) > 0) {
                $out_cashbox_name = $out_cashbox_btns = $out_cashbox_cur ='';
                $cashboxes_cur = array();
                $out .= '<table class="cashboxes-table"><tbody>';
                foreach ($this->cashboxes as $cashbox) {
                    // кроме не активной и транзитной
                    if ($cashbox['avail'] != 1 || $cashbox['id'] == $this->all_configs['configs']['erp-cashbox-transaction'])
                        continue;

                    $out_cashbox_name .= '<td><h4 class="center">' . $cashbox['name'] . '</h4></td>';

                    if (array_key_exists('currencies', $cashbox)) {
                        ksort($cashbox['currencies']);
                        foreach ($cashbox['currencies'] as $cur_id => $currency) {
                            $name = show_price($currency['amount']) . ' ' . htmlspecialchars($currency['short_name']);
                            $cashboxes_cur[$cashbox['id']][$cur_id] = $name;
                        }
                    }
                    //$out_cashbox_btns .= '<td><div class="btns-cashbox"><div><button onclick="begin_transaction(1, \'' . $cashbox['id'] . '\')" class="btn btn-cashboxes">Выдача</button></div>';
                    //$out_cashbox_btns .= '<div><button onclick="begin_transaction(2, \'' . $cashbox['id'] . '\')" class="btn btn-cashboxes">Внесение</button></div>';
                    //$out_cashbox_btns .= '<div><button onclick="begin_transaction(3, \'' . $cashbox['id'] . '\')" class="btn btn-cashboxes">Перемещение</button></div>';
                    $out_cashbox_btns .= '<td><div class="btns-cashbox">';
                    $out_cashbox_btns .= '<div><button data-o_id="' . $cashbox['id'] . '" onclick="alert_box(this, false, \'begin-transaction-1\')" class="btn btn-cashboxes">Выдача</button></div>';
                    $out_cashbox_btns .= '<div><button data-o_id="' . $cashbox['id'] . '" onclick="alert_box(this, false, \'begin-transaction-2\')" class="btn btn-cashboxes">Внесение</button></div>';
                    $out_cashbox_btns .= '<div><button data-o_id="' . $cashbox['id'] . '" onclick="alert_box(this, false, \'begin-transaction-3\')" class="btn btn-cashboxes">Перемещение</button></div>';
                    $out_cashbox_btns .= '<div><button onclick="javascript:window.location.href=\'' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0];
                    $out_cashbox_btns .= '?cb=' . $cashbox['id'] . '#transactions\'" class="btn btn-cashboxes">Отчеты</button></div></div></td>';
                }
                $out .= '<tr>' . $out_cashbox_name . '</tr>';
                foreach ($currencies as $cur_id=>$currency) {
                    $out .= '<tr>';
                    foreach ($cashboxes_cur as $cashbox_cur) {
                        $out .= '<td class="text-success center">' . (array_key_exists($cur_id, $cashbox_cur) ? $cashbox_cur[$cur_id] : '') . '</td>';
                    }
                    $out .= '</tr>';
                }
                $out .= '<tr>' . $out_cashbox_btns . '</tr>';
                $out .= '</tbody></table>';
            } else {
                $out .= '<p class="text-error">Нет касс.</p>';
            }

            // списсок 30 последних транзакций по дню
            $out .= $this->all_configs['suppliers_orders']->get_transactions($currencies, true, 30);
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function total_cashboxes($amounts)
    {
        $out = '';
        $sum = array();

        if (array_key_exists('cashboxes', $amounts) && is_array($amounts['cashboxes']) && count($amounts['cashboxes']) > 0) {

            usort($amounts['cashboxes'], array('accountings','akcsort'));

            foreach ($amounts['cashboxes'] as $amount) {
                if ($amount['amount'] != 0) {
                    $out .= empty($out) ? '' : ', ';
                    $out .= show_price($amount['amount'], 2, ' ') . ' ' . htmlspecialchars($amount['short_name']);
                    $sum[$amount['currency']] = array_key_exists('currency', $sum) ?
                        ($sum[$amount['currency']] + $amount['amount']) : $amount['amount'];
                }
            }
        }

        return array(
            'html' => empty($out) ? 0 : $out,
            'amount' => $sum,
        );
    }

    function akcsort($a, $b)
    {
        return $b['currency'] - $a['currency'];
    }

    function accountings_transactions($hash)
    {
        if (trim($hash) == '#transactions' || (trim($hash) != '#transactions-cashboxes' && trim($hash) != '#transactions-contractors'))
            $hash = '#transactions-cashboxes';

        $out = '';

        if (!$this->all_configs['oRole']->hasPrivilege('accounting')) {
            $hash = '#transactions-contractors';
        }

        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')) {
            $out = '<ul class="nav nav-pills">';
            if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
                $out .= '<li><a class="click_tab" data-open_tab="accountings_transactions_cashboxes" onclick="click_tab(this, event)" href="#transactions-cashboxes" title="Транзакции касс">Касс</a></li>';
            }
            $out .= '<li><a class="click_tab" data-open_tab="accountings_transactions_contractors" onclick="click_tab(this, event)" href="#transactions-contractors" title="Транзакции контрагентов">Контрагентов</a></li>';
            $out .= '</ul>';
            $out .= '<div class="pill-content">';

            if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
                // фильтры транзакций
                $out .= '<div id="transactions-cashboxes" class="pill-pane">';
                $out .= '</div><!--#transactions-cashboxes-->';
            }
            $out .= '<div id="transactions-contractors" class="pill-pane">';
            $out .= '</div><!--#transactions-contractors-->';

            $out .= '</div><!--.pill-content-->';
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    function accountings_transactions_cashboxes()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            // фильтры
            $out = $this->transaction_filters();
            // списсок транзакций
            $out .= $this->all_configs['suppliers_orders']->get_transactions($currencies);
        }

        return array(
            'html' => $out,
            'functions' => array("reset_multiselect()"),
        );
    }

    function accountings_transactions_contractors()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')) {

            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            // фильтры
            $in = 'in';
            $contractor_html = '';
            if (isset($_GET['ct'])) {
                $cn = explode(',', $_GET['ct']);
                if (count($cn) == 1 && array_key_exists(0, $cn)) {
                    $contractor = $this->all_configs['db']->query('SELECT c.title, c.amount, c.type, cc.name FROM {contractors} as c
                        LEFT JOIN (SELECT contractors_id, contractors_categories_id FROM {contractors_categories_links})l ON l.contractors_id=c.id
                        LEFT JOIN (SELECT name, id FROM {contractors_categories})cc ON cc.id=l.contractors_categories_id
                        WHERE c.id=?i', array($cn[0]))->assoc();
                    if ($contractor) {
                        // выводим инфу контрагента
                        $contractor_html = '<h4 class="well">' . $contractor[0]['title'] . ', ' . show_price($contractor[0]['amount']) . '$';
                        $contractor_html .= (array_key_exists($contractor[0]['type'], $this->all_configs['configs']['erp-contractors-types']) ? '<br />' . $this->all_configs['configs']['erp-contractors-types'][$contractor[0]['type']] : '') . '<br />';
                        foreach ($contractor as $k => $contractor_category) {
                            $contractor_html .= $contractor_category['name'];
                            if (($k + 1) < count($contractor))
                                $contractor_html .= ', ';
                        }
                        $contractor_html .= '</h4>';
                        // сворачиваем фильтры
                        if (!isset($_GET['cb']) && !isset($_GET['cg']) && !isset($_GET['o_id']) && !isset($_GET['s_id']) && !isset($_GET['t_id']))
                            $in = '';
                    }
                }
            }
            $out = ' 
                <div class="panel-group" id="transaction_filters">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                          <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#transaction_filters" href="#transaction_filters_collapse">Фильтры</a>
                          </h4>
                        </div>
                        <div id="transaction_filters_collapse" class="panel-collapse collapse ' . $in . '">
                            <div class="panel-body">';
            $out .= $this->transaction_filters(true);
            $out .= '</div></div></div></div>';
            $out .= $contractor_html;
            // списсок транзакций
            $out .= $this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, true);
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function accountings_reports($hash = '#reports-turnover')
    {
        if (trim($hash) == '#reports' || (trim($hash) != '#reports-cash_flow'
                && trim($hash) != '#reports-annual_balance' && trim($hash) != '#reports-cost_of'
                && trim($hash) != '#reports-turnover' && trim($hash) != '#reports-net_profit')
        )
            $hash = '#reports-turnover';
        $out = '';

        if (!$this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $hash = '#reports-turnover';
        }

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
                || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
                || $this->all_configs['oRole']->hasPrivilege('partner')) {
            $out .= '<ul class="nav nav-pills">';

            $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_reports_turnover" class="click_tab"  href="#reports-turnover">Оборот</a></li>';
            if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
                $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_reports_net_profit" class="click_tab" href="#reports-net_profit">Чистая прибыль</a></li>';
                $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_reports_cost_of" class="click_tab"  href="#reports-cost_of">Стоимость компании</a></li>';
                $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_reports_cash_flow" class="click_tab"  href="#reports-cash_flow">Денежный поток</a></li>';
                $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_reports_annual_balance" class="click_tab"  href="#reports-annual_balance">Годовые балансы</a></li>';
            }
            $out .= '</ul><div class="pill-content">';

            // оборот
            $out .= '<div id="reports-turnover" class="pill-pane">';
            $out .= '</div>';

            if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
                // Денежный поток
                $out .= '<div id="reports-cash_flow" class="pill-pane">';
                $out .= '</div>';

                // Годовые балансы
                $out .= '<div class="pill-content"><div id="reports-annual_balance" class="pill-pane">';
                $out .= '</div>';

                // стоимость компании
                $out .= '<div id="reports-cost_of" class="pill-pane">';
                $out .= '</div>';

                // чистая прибыль
                $out .= '<div id="reports-net_profit" class="pill-pane">';
                $out .= '</div></div>';
            }
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    function accountings_reports_cash_flow()
    {
        $out = '';

        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            // категории (статьи) с которыми происходили транзакции
            // доходы
            $contractors_categories_inc = $this->all_configs['db']->query('SELECT cc.id, cc.name
                FROM {contractors_categories} as cc, {contractors_categories_links} as l, {cashboxes_transactions} as t
                WHERE t.contractor_category_link=l.id AND l.contractors_categories_id=cc.id AND t.transaction_type=?i
                  AND YEAR(t.date_transaction)<=?i GROUP BY cc.id', array(2, $year))->vars();
            // расходы
            $contractors_categories_exp = $this->all_configs['db']->query('SELECT cc.id, cc.name
                FROM {contractors_categories} as cc, {contractors_categories_links} as l, {cashboxes_transactions} as t
                WHERE t.contractor_category_link=l.id AND l.contractors_categories_id=cc.id AND t.transaction_type=?i
                  AND YEAR(t.date_transaction)<=?i GROUP BY cc.id', array(1, $year))->vars();

            // все транзакции касс кроме переводов(по месяцам, по типам транзакций, и по категориям)
            $transactions = $this->all_configs['db']->query('SELECT t.date_transaction as dt,
                      t.transaction_type as tt, l.contractors_categories_id as cid, cu.currency as cr,
                      SUM(CASE t.transaction_type WHEN 1 THEN t.value_from END) as exp,
                      SUM(CASE t.transaction_type WHEN 2 THEN t.value_to END) as inc
                    FROM {cashboxes_transactions} AS t
                    LEFT JOIN {contractors_categories_links} as l ON t.contractor_category_link=l.id
                    LEFT JOIN {cashboxes_currencies} as cu ON (cu.id=t.cashboxes_currency_id_from OR cu.id=t.cashboxes_currency_id_to)
                    WHERE YEAR(t.date_transaction)<=?i
                    GROUP BY t.transaction_type, l.contractors_categories_id, MONTH(t.date_transaction), YEAR(t.date_transaction), cu.currency
                    ORDER BY t.date_transaction',
                array($year))->assoc();

            $m_inc = array(); // доходы по месяцам
            $m_exp = array(); // расходы по месяцам
            $total = array(); // итого по месяцам
            $cumulative_total = array(); // нарастающий итог
            $exp_ct = array(); // доходы по категориям (статьям)
            $inc_ct = array(); // расходы по категориям (статьям)

            $total_inc = array(); // всего доходов
            $total_exp = array(); // всего расходов
            $total_total = array(); // всего всего

            $years = array($year => $year);
            // пробегаемся по тразакциям
            if ($transactions) {
                foreach ($transactions as $t) {
                    $m = date('m', strtotime($t['dt']));
                    $y = date('Y', strtotime($t['dt']));
                    $years[$y] = $y;

                    // до текущего года
                    if ($y < $year) {
                        $cumulative_total[$t['cr']] = (array_key_exists($t['cr'], $cumulative_total) ? $cumulative_total[$t['cr']] : 0) + ($t['inc'] - $t['exp']);
                    }

                    // текущий год
                    if ($y == $year) {
                        // доходы
                        if ($t['tt'] == 2) {
                            $m_inc[$m][$t['cr']] = (array_key_exists($m, $m_inc) && array_key_exists($t['cr'], $m_inc[$m])
                                    ? $m_inc[$m][$t['cr']] : 0) + $t['inc'];
                            $total[$m][$t['cr']] = (array_key_exists($m, $total) && array_key_exists($t['cr'], $total[$m])
                                    ? $total[$m][$t['cr']] : 0) + $t['inc'];
                            $inc_ct[$t['cid']][$m][$t['cr']] = (array_key_exists($t['cid'], $inc_ct)
                                && array_key_exists($m, $inc_ct[$t['cid']]) && array_key_exists($t['cr'], $inc_ct[$t['cid']][$m])
                                    ? $inc_ct[$t['cid']][$m][$t['cr']] : 0) + $t['inc'];
                            $total_inc[$t['cr']] = (array_key_exists($t['cr'], $total_inc) ? $total_inc[$t['cr']] : 0) + $t['inc'];
                            $total_total[$t['cr']] = (array_key_exists($t['cr'], $total_total) ? $total_total[$t['cr']] : 0) + $t['inc'];
                        }
                        // расходы
                        if ($t['tt'] == 1) {
                            $m_exp[$m][$t['cr']] = (array_key_exists($m, $m_exp) && array_key_exists($t['cr'], $m_exp[$m])
                                    ? $m_exp[$m][$t['cr']] : 0) + $t['exp'];
                            $total[$m][$t['cr']] = (array_key_exists($m, $total) && array_key_exists($t['cr'], $total[$m])
                                    ? $total[$m][$t['cr']] : 0) - $t['exp'];
                            $exp_ct[$t['cid']][$m][$t['cr']] = (array_key_exists($t['cid'], $exp_ct)
                                && array_key_exists($m, $exp_ct[$t['cid']]) && array_key_exists($t['cr'], $exp_ct[$t['cid']][$m])
                                    ? $exp_ct[$t['cid']][$m][$t['cr']] : 0) + $t['exp'];
                            $total_exp[$t['cr']] = (array_key_exists($t['cr'], $total_exp) ? $total_exp[$t['cr']] : 0) + $t['exp'];
                            $total_total[$t['cr']] = (array_key_exists($t['cr'], $total_total) ? $total_total[$t['cr']] : 0) - $t['exp'];
                        }
                    }
                }
            }

            $out .= $this->accountings_year_filter($year, '#reports-cash_flow', $years);
            $out .= '<div class="table-responsive"><table class="table table-bordered table-reports table-condensed"><thead><tr><td></td>';
            $out_inc = '<tr class="well"><td><a href="" onclick="toggle_report_cashflow(this, event, \'inc\')" class="none-decoration">';
            $out_inc .= '<i class="glyphicon glyphicon-chevron-down"></i></a> Доходы</td>';
            $out_exp = '<tr class="well"><td><a href="" onclick="toggle_report_cashflow(this, event, \'exp\')" class="none-decoration">';
            $out_exp .= '<i class="glyphicon glyphicon-chevron-down"></i></a> Расходы</td>';
            $out_total = '<tr class="well"><td>Итого</td>';
            $out_cumulative_total = '<tr><td>Нарастающий итог</td>';

            $crs = $this->all_configs['suppliers_orders']->currencies;
            $out_inc_ct = ''; // доходы по категориям (статьям)
            if ($contractors_categories_inc) {
                foreach ($contractors_categories_inc as $cid=>$ct) {
                    $total_inc_ct = array();
                    $out_inc_ct .= '<tr class="report-cashflow-inc"><td>' . htmlspecialchars($ct) . '</td>';
                    foreach ($this->months as $number => $name) {
                        $out_inc_ct .= '<td>';
                        if (array_key_exists($cid, $inc_ct) && array_key_exists($number, $inc_ct[$cid])) {
                            $out_inc_ct .= show_price($inc_ct[$cid][$number], 2, ' ', ',', 100, $crs);
                            //$total_inc_ct += $inc_ct[$cid][$number];
                            $total_inc_ct = $this->array_sum_values($inc_ct[$cid][$number], $total_inc_ct);
                        }
                        $out_inc_ct .= '</td>';
                    }
                    $out_inc_ct .= '<td>' . show_price($total_inc_ct, 2, ' ', ',', 100, $crs) . '</td></tr>';
                }
            }

            $out_exp_ct = ''; // расходы по категориям (статьям)
            if ($contractors_categories_exp) {
                foreach ($contractors_categories_exp as $cid=>$ct) {
                    $total_exp_ct = array();
                    $out_exp_ct .= '<tr class="report-cashflow-exp"><td>' . htmlspecialchars($ct) . '</td>';
                    foreach ($this->months as $number => $name) {
                        $out_exp_ct .= '<td>';
                        if (array_key_exists($cid, $exp_ct) && array_key_exists($number, $exp_ct[$cid])) {
                            $out_exp_ct .= show_price($exp_ct[$cid][$number], 2, ' ', ',', 100, $crs);
                            //$total_exp_ct += $exp_ct[$cid][$number];
                            $total_exp_ct = $this->array_sum_values($exp_ct[$cid][$number], $total_exp_ct);
                        }
                        $out_exp_ct .= '</td>';
                    }
                    $out_exp_ct .= '<td>' . show_price($total_exp_ct, 2, ' ', ',', 100, $crs) . '</td></tr>';
                }
            }

            // по месяцам
            foreach ($this->months as $number => $name) {
                $cumulative_total = array_key_exists($number, $total) ?
                    $this->array_sum_values($cumulative_total, $total[$number]) : $cumulative_total;

                $out .= '<td>' . htmlspecialchars($name) . '</td>';
                $out_inc .= '<td>' . (array_key_exists($number, $m_inc) ? show_price($m_inc[$number], 2, ' ', ',', 100, $crs) : '') . '</td>';
                $out_exp .= '<td>' . (array_key_exists($number, $m_exp) ? show_price($m_exp[$number], 2, ' ', ',', 100, $crs) : '') . '</td>';
                $out_total .= '<td>' . (array_key_exists($number, $total) ? show_price($total[$number], 2, ' ', ',', 100, $crs) : '') .  '</td>';
                $out_cumulative_total .= '<td>' . show_price($cumulative_total, 2, ' ', ',', 100, $crs) . '</td>';
            }
            $out .= '<td>Всего</td></tr></thead><tbody>';
            $out .= $out_inc . '<td>' . show_price($total_inc, 2, ' ', ',', 100, $crs) . '</td></tr>' . $out_inc_ct;
            $out .= $out_exp . '<td>' . show_price($total_exp, 2, ' ', ',', 100, $crs) . '</td></tr>' . $out_exp_ct;

            $out .= $out_total . '<td>' . show_price($total_total, 2, ' ', ',', 100, $crs) . '</td></tr>';
            $out .= $out_cumulative_total . '<td></td></tr>';
            $out .= '</tbody></table></div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function array_sum_values() {
        $return = array();
        //$intArgs = func_num_args();
        $arrArgs = func_get_args();

        if (count($arrArgs) > 0) {
            if (count($arrArgs) == 1) {
                reset($arrArgs);
                $return = current($arrArgs);
            } else {
                foreach($arrArgs as $arrItem) {
                    if (is_array($arrItem) && count($arrItem) > 0) {
                        foreach($arrItem as $k => $v) {
                            $return[$k] = (array_key_exists($k, $return) ? $return[$k] : 0) + $v;
                        }
                    }
                }
            }
        }

        return $return;
    }

    function accountings_reports_annual_balance()
    {
        $out = '';

        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $crs = $this->all_configs['suppliers_orders']->currencies;

            // все кассы с которыми происходили транзакции
            $cashboxes = $this->all_configs['db']->query('SELECT cb.id, cb.name
                FROM {cashboxes} as cb, {cashboxes_currencies} as cc, {cashboxes_transactions} as t
                WHERE (t.cashboxes_currency_id_from=cc.id OR t.cashboxes_currency_id_to=cc.id)
                  AND cc.cashbox_id=cb.id AND YEAR(t.date_transaction)<=?i
                GROUP BY cb.id', array($year))->vars();

            // все транзакции касс (по месяцам, по типам транзакций, и по кассам)
            $transactions = $this->all_configs['db']->query('SELECT cu.cashbox_id as cid, t.date_transaction as dt,
                      cu.currency as cr,
                      SUM(CASE t.transaction_type WHEN 1 THEN t.value_from WHEN 3 THEN t.value_from END) as exp,
                      SUM(CASE t.transaction_type WHEN 2 THEN t.value_to WHEN 3 THEN t.value_to END) as inc
                    FROM {cashboxes_transactions} AS t
                    LEFT JOIN (SELECT id, currency, cashbox_id FROM {cashboxes_currencies})cu ON
                      (t.cashboxes_currency_id_from=cu.id OR t.cashboxes_currency_id_to=cu.id)
                    WHERE YEAR(t.date_transaction)<=?i
                    GROUP BY cu.cashbox_id, MONTH(t.date_transaction), YEAR(t.date_transaction), cu.currency
                    ORDER BY t.date_transaction',
                array($year))->assoc();

            $m_tr_cb = array(); // по кассам по месяцам
            $b_tr_cb = array(); // по кассам до текущего года
            $total = array(); // итого

            $years = array($year => $year);
            if ($transactions) {
                foreach ($transactions as $t) {
                    $m = date('m', strtotime($t['dt']));
                    $y = date('Y', strtotime($t['dt']));
                    $years[$y] = $y;

                    // до текущего года
                    if ($y < $year) {
                        $b_tr_cb[$t['cid']][$t['cr']] = (array_key_exists($t['cid'], $b_tr_cb)
                            && array_key_exists($t['cr'], $b_tr_cb[$t['cid']])
                                ? $b_tr_cb[$t['cid']][$t['cr']] : 0) + ($t['inc'] - $t['exp']);
                    }

                    // текущий год
                    if ($y == $year) {
                        $m_tr_cb[$t['cid']][$m][$t['cr']] = (array_key_exists($t['cid'], $m_tr_cb)
                            && array_key_exists($m, $m_tr_cb[$t['cid']]) && array_key_exists($t['cr'], $m_tr_cb[$t['cid']][$m])
                                ? $m_tr_cb[$t['cid']][$m][$t['cr']] : 0) + ($t['inc'] - $t['exp']);
                    }
                }
            }

            $out .= $this->accountings_year_filter($year, '#reports-annual_balance', $years);
            $out .= '<div class="table-responsive"><table class="table table-bordered table-reports table-condensed"><thead><tr><td>Счет</td>';

            $out_cb = '';
            if ($cashboxes) {
                foreach ($cashboxes as $cid=>$c) {
                    $out_cb .= '<tr><td class="text-right">' . htmlspecialchars($c) . '</td>';

                    foreach ($this->months as $number => $name) {
                        /*$b_tr_cb[$cid] = (array_key_exists($cid, $b_tr_cb) ? $b_tr_cb[$cid] : 0) +
                            (array_key_exists($cid, $m_tr_cb) && array_key_exists($number, $m_tr_cb[$cid])
                                ? $m_tr_cb[$cid][$number] : 0);*/
                        $b_tr_cb[$cid] = $this->array_sum_values(
                            array_key_exists($cid, $b_tr_cb) ? $b_tr_cb[$cid] : array(),
                            array_key_exists($cid, $m_tr_cb) && array_key_exists($number, $m_tr_cb[$cid])
                                ? $m_tr_cb[$cid][$number] : array()
                        );
                        $out_cb .= '<td>' . show_price($b_tr_cb[$cid], 2, ' ', ',', 100, $crs) . '</td>';

                        $total[$number] = $this->array_sum_values($b_tr_cb[$cid],
                            (array_key_exists($number, $total) ? $total[$number] : array()));
                    }
                    $out_cb .= '</tr>';
                }
            }

            $out_total = ''; // итого
            foreach ($this->months as $number => $name) {
                $out .= '<td>' . htmlspecialchars($name) . '</td>';
                $out_total .= '<td>' . (array_key_exists($number, $total) ? show_price($total[$number], 2, ' ', ',', 100, $crs) : '') . '</td>';
            }
            $out .= '</tr></thead><tbody>' . $out_cb;
            //$out .= '<tr><td>Наличные</td>' . $out_total_tr . '</tr>';
            $out .= '<tr class="well"><td>Итого</td>' . $out_total . '</tr>';
            $out .= '</tbody></table></div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_year_filter($year, $hash, $years)
    {
        $y = date('Y');
        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?year=';

        $out = '<div class="well">';
        if (array_key_exists(($year - 1), $years) || $y < $year) {
            $out .= '<a href="' . $url . ($year - 1) . $hash . '" class="none-decoration"> ';
        }
        $out .= ' <i class="glyphicon glyphicon-chevron-left"></i> </a> ' . $year;
        if (array_key_exists(($year + 1), $years) || $y > $year) {
            $out .= ' <a href="' . $url . ($year + 1) . $hash . '" class="none-decoration"> ';
        }
        $out .= ' <i class="glyphicon glyphicon-chevron-right"></i> </a></div>';

        return $out;
    }

    function accountings_reports_cost_of()
    {
        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

            $query = '';
            /*// фильтры
            $out = '<form method="post" class="form-horizontal">';
            $out .= '<div class="control-group"><label class="control-label">Период:</label><div class="controls">';
            $out .= '<input type="text" name="date" value="' . $date . '" class="input-big daterangepicker" /></div></div>';
            $out .= '<div class="control-group"><div class="controls"><input class="btn" type="submit" name="filters" value="Применить" /></div></div>';
            $out .= '</form>';

            // фильтр по дате
            $day_from = null;//1 . date(".m.Y") . ' 00:00:00';
            $day_to = null;//31 . date(".m.Y") . ' 23:59:59';
            if (array_key_exists('df', $_GET) && strtotime($_GET['df']) > 0)
                $day_from = $_GET['df'] . ' 00:00:00';
            if (array_key_exists('dt', $_GET) && strtotime($_GET['dt']) > 0)
                $day_to = $_GET['dt'] . ' 23:59:59';

            if ($day_from && $day_to) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(t.date_add) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
                  AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));
            } elseif ($day_from) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(t.date_add)>=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                    array($day_from));
            } elseif ($day_to) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(t.date_add)<=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                    array($day_to));
            }*/

            $currencies = $this->all_configs['suppliers_orders']->currencies;
            $cso = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
            $s_balance = array('inv' => 0, 'exp' => 0, 'html' => 0, 'amount' => array($cso => 0));
            $assets = array('html' => 0, 'amount' => null);
            $total = array('html' => 0, 'amount' => null);

            // запросы для касс для разных привилегий
            $q = $this->all_configs['chains']->query_warehouses();
            $query_for_noadmin_w = $q['query_for_noadmin_w'];
            // списсок складов с общим количеством товаров
            $warehouses = $this->all_configs['chains']->warehouses($query_for_noadmin_w);
            // всего денег по кассам которые consider_all == 1
            $cost_of = cost_of($warehouses, $this->all_configs['settings'], $this->all_configs['suppliers_orders']);

            $ccl = '';
            if (array_key_exists('cat-non-current-assets', $this->all_configs['settings'])) {
                $ccl = preg_replace('/ {1,}/', '', $this->all_configs['settings']['cat-non-current-assets']);
                // необоротные активы
                $assets['amount'] = $this->all_configs['db']->query('SELECT cc.currency,
                          SUM(IF(t.transaction_type=1, t.value_from, 0)) AS amount
                        FROM {cashboxes_transactions} AS t, {contractors_categories_links} AS l, {cashboxes_currencies} AS cc
                        WHERE t.contractor_category_link=l.id AND l.contractors_categories_id IN (?li) ?query
                          AND IF(t.transaction_type=1, cc.id=t.cashboxes_currency_id_from, NULL) GROUP BY cc.currency',
                    array(explode(',', $this->all_configs['settings']['cat-non-current-assets']), $query))->vars();
            }
            if ($assets['amount']) {
                ksort($assets['amount']);
                $assets['html'] = '';
                $i = 1;
                foreach ($assets['amount'] as $c => $amount) {
                    $assets['html'] .= show_price($amount, 2, ' ') . ' ';
                    $assets['html'] .= array_key_exists($c, $currencies) ? $currencies[$c]['shortName'] : '';
                    $assets['html'] .= (count($assets['amount']) > $i ? ', ' : '');
                    $i++;
                }
            }

            //usort($this->contractors, array('accountings','akcsort'));

            // баланс поставщиков
            foreach ($this->contractors as $contractor) {
                if ($contractor['amount'] > 0)
                    $s_balance['exp'] -= $contractor['amount'];
                if ($contractor['amount'] < 0)
                    $s_balance['inv'] -= $contractor['amount'];
                $s_balance['html'] -= $contractor['amount'];
                $s_balance['amount'][$cso] -= $contractor['amount'];
            }

            $s_balance['html'] = show_price($s_balance['html'], 2, ' ');
            $s_balance['html'] .= array_key_exists($cso, $currencies) ? ' ' . $currencies[$cso]['shortName'] : '';
            if ($s_balance['inv'] > 0 || $s_balance['exp'] < 0) {
                $s_balance['html'] .= ' (';
                if ($s_balance['inv'] > 0) {
                    $s_balance['html'] .= 'Д: ' . show_price($s_balance['inv'], 2, ' ');
                }
                if ($s_balance['exp'] < 0) {
                    $s_balance['html'] .= $s_balance['inv'] > 0 ? ', Р: ' : 'Р: ';
                    $s_balance['html'] .= show_price($s_balance['exp'], 2, ' ');
                }
                $s_balance['html'] .= ')';
            }

            // в кассе
            $amounts = $this->get_cashboxes_amounts();
            $total_cashboxes = $this->total_cashboxes($amounts);

            // итого
            $total['amount'] = $this->sum_by_currency($cost_of['amount'], $assets['amount'], $s_balance['amount'],
                $total_cashboxes['amount']);
            if (is_array($total['amount']) && count($total['amount']) > 0) {
                $total['html'] = ''; $i = 1;
                foreach ($total['amount'] as $k => $a) {
                    $total['html'] .= show_price($a, 2, ' ');
                    $total['html'] .= array_key_exists($k, $currencies) ? ' ' . $currencies[$k]['shortName'] : '';
                    $total['html'] .= (count($total['amount']) > $i ? ', ' : '');
                    $i++;
                    $total['amount/2'][$k] = ($a / 2);
                }
            }

            $prefix = $this->all_configs['prefix'];
            $cost_of['html'] = '<a href="' . $prefix . 'warehouses#warehouses">' . $cost_of['html'] . '</a>';
            $assets['html'] = '<a class="hash_link" href="' . $prefix . 'accountings?cg=' . $ccl . '#transactions-cashboxes">' .
                $assets['html'] . '</a>';
            $s_balance['html'] = '<a class="hash_link" href="' . $prefix . 'accountings#contractors">' . $s_balance['html'] . '</a>';
            $total_cashboxes['html'] = '<a class="hash_link" href="' . $prefix . 'accountings#cashboxes">' . $total_cashboxes['html'] . '</a>';

            $out .= '<table class="table"><tbody>';
            $out .= '<tr><td><strong>Оборотные активы:</strong></td><td>' . $cost_of['html'] . '</td></tr>';
            $out .= '<tr><td><strong>Необоротные активы:</strong></td><td>' . $assets['html'] . '</td></tr>';
            $out .= '<tr><td><strong>Баланс поставщиков:</strong></td><td>' . $s_balance['html'] . '</td></tr>';
            $out .= '<tr><td><strong>В кассе:</strong></td><td>' . $total_cashboxes['html'] . '</td></tr>';
            $out .= '<tr><td><h5>Итого: </h5></td><td><h5>' . $total['html'] . '</h5></td></tr>';

            // расчет долевого участия контрагентов
            if (array_key_exists('erp-contractors-founders', $this->all_configs['configs'])
                && count($this->all_configs['configs']['erp-contractors-founders']) > 0) {

                $cfs = (array)$this->all_configs['configs']['erp-contractors-founders'];

                // сумма транзакций по контрагентам и валютам
                $total_by_ctr = $this->all_configs['db']->query('SELECT cc.currency, ct.id as ctr_id, ct.title,
                          (SUM(IF(t.transaction_type=2 AND cc.id=t.cashboxes_currency_id_to, t.value_to, 0)) -
                          SUM(IF(t.transaction_type=1 AND cc.id=t.cashboxes_currency_id_from, t.value_from, 0))) AS amount
                        FROM {contractors} as ct
                        CROSS JOIN {cashboxes_currencies} as cc
                        LEFT JOIN {contractors_categories_links} as l ON l.contractors_id=ct.id
                        LEFT JOIN {cashboxes_transactions} as t ON t.contractor_category_link=l.id
                        WHERE ct.id IN (?li) ?query
                        GROUP BY cc.currency, ct.id',
                    array(array_values($cfs), $query))->assoc();

                $ctr_total = array();
                //(итого Контрагент1/ 2) – (итого Контрагент2 / 2) - ...
                if ($total_by_ctr) {

                    usort($total_by_ctr, array('accountings','akcsort'));

                    foreach ($total_by_ctr as $ct) {
                        if ($ct['currency'] > 0) {
                            if (!array_key_exists($ct['ctr_id'], $ctr_total)
                                || !array_key_exists('amounts', $ctr_total[$ct['ctr_id']])
                                || !array_key_exists($ct['currency'], $ctr_total[$ct['ctr_id']]['amounts']))
                                $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']] = 0;

                            $ctr_total[$ct['ctr_id']]['title'] = $ct['title'];
                            $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']] += $ct['amount'] / 2;
                            $ctr_total[$ct['ctr_id']]['ba'][$ct['currency']] = $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']];
                        }
                    }

                    foreach ($ctr_total as $ctr_id=>$v) {
                        foreach ($ctr_total as $sctr_id=>$sv) {
                            if ($ctr_id != $sctr_id) {
                                foreach ($v['amounts'] as $cc=>$a) {
                                    if (array_key_exists($cc, $sv['amounts'])) {
                                        $ctr_total[$ctr_id]['amounts'][$cc] -= $sv['ba'][$cc];
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($ctr_total as $ctr_id=>$ctr) {
                    $out .= '<tr><td><strong>' . htmlspecialchars($ctr['title']) . ':</strong></td>';
                    $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?ct=' . $ctr_id;
                    $out .= '<td><a href="' . $url . '#transactions-cashboxes">';
                    //Контрагент1 =  (стоимость компании / 2 ) + (итого Контрагент1/ 2) – (итого Контрагент2 / 2) - ...
                    $sum = $this->sum_by_currency($total['amount/2'], $ctr['amounts']);
                    $out .= show_price($sum, 2, ' ', '.', 100, $currencies) . '</a></td></tr>';
                }
            }

            $out .= '</tbody></table>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function sum_by_currency()
    {
        $sum = array();
        $numargs = func_num_args();
        $arg_list = func_get_args();

        for ($i = 0; $i < $numargs; $i++) {
            if (is_array($arg_list[$i])) {
                foreach ($arg_list[$i] as $k => $a) {
                    $sum[$k] = array_key_exists($k, $sum) ? $sum[$k] + $a : $a;
                }
            }
        }

        return $sum;
    }

    /*function get_operators()
    {
        return $this->all_configs['db']->query("SELECT u.id, CONCAT(u.fio, ' ', u.login) as name
                FROM {users} AS u WHERE u.role=?i", array(5))->assoc();//TODO плохое решение role в настройку
    }*/

    function accountings_reports_turnover_array()
    {
        $array = array();

        $amounts = $this->all_configs['manageModel']->profit_margin($_GET);

        if ($amounts && is_array($amounts['orders']) && count($amounts['orders']) > 0) {
            foreach ($amounts['orders'] as $k=>$p) {
                $array[$k]['№ Заказа'] = $p['order_id'];
                $array[$k]['Устройство'] = $p['title'];

                $array[$k]['Запчасти'] = '';
                if (isset($p['goods'])) {
                    foreach ($p['goods'] as $g) {
                        $array[$k]['Запчасти'] .= $g['title'] . ' ';
                    }
                }
                $services_price = 0;
                $array[$k]['Работа'] = '';
                if (isset($p['services'])) {
                    foreach ($p['services'] as $s) {
                        $array[$k]['Работа'] .= $s['title'] . ' ';
                        $services_price += $s['price'];
                    }
                }
                $array[$k]['Стоимость работ'] = show_price($services_price, 2, '');

                $array[$k]['Цена продажи'] = show_price($p['turnover'], 2, '');
                $array[$k]['Цена запчасти'] = show_price($p['purchase'], 2, '');
                $array[$k]['Операционная прибыль'] = show_price($p['profit'], 2, '');
                $array[$k]['Наценка %'] = (is_numeric($p['avg']) ? round($p['avg'], 2) . ' %' : $p['avg']);
            }
        }

        return $array;
    }

    function accountings_reports_turnover()
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : date('01.m.Y', time())) . ' - ' .
            (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : date('t.m.Y', time()));
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
                || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
                || $this->all_configs['oRole']->hasPrivilege('partner')) {

            // категории товаров
            //$categories = $this->all_configs['db']->query("SELECT id, title as name, parent_id
            //    FROM {categories} ORDER BY prio")->assoc();

            // Операторы
            //$operators = $this->get_operators();

            // фильтры
            $out = '<form method="post" style="max-width: 300px">';
            $out .= '<div class="form-group"><label class="">Период:</label>';
            $out .= '<input type="text" name="date" value="' . $date . '" class="form-control daterangepicker" /></div>';
            if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                // менеджеры
                $managers = $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders');
                $out .= '<div class="form-group"><label>Менеджер:</label>';
                //if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
                    $out .= ' <select class="multiselect form-control report-filter" name="managers[]" multiple="multiple">';
                //} else {
                //    $out .= '<select disabled class="multiselect input-small report-filter" name="managers[]" multiple="multiple">';
                //    $_GET['mg'] = $_SESSION['id'];
                //}
                $out .= build_array_tree($managers, ((isset($_GET['mg'])) ? explode(',', $_GET['mg']) : array()));
                $out .= '</select></div>';
            }
            // приемщикы
            $accepters = $this->all_configs['oRole']->get_users_by_permissions('create-clients-orders');
            $out .= '<div class="form-group"><label>Приемщик:</label>';
            //if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
                $disabled = $this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration') ? 'disabled' : '';
                $out .= '<select ' . $disabled . ' class="multiselect form-control report-filter" name="accepters[]" multiple="multiple">';
            //} else {
            //    $out .= '<select disabled class="multiselect input-small report-filter" name="managers[]" multiple="multiple">';
            //    $_GET['mg'] = $_SESSION['id'];
            //}
            $selected = $this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration') ? $user_id : ((isset($_GET['acp'])) ? explode(',', $_GET['acp']) : array());
            $out .= build_array_tree($accepters, $selected);
            $out .= '</select></div>';
            // инженеры
            if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $engineers = $this->all_configs['oRole']->get_users_by_permissions('engineer');
                $out .= '<div class="form-group"><label>Инженер:</label>';
                //if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
                    $out .= '<select class="multiselect form-control report-filter" name="engineers[]" multiple="multiple">';
                //} else {
                //    $out .= '<select disabled class="multiselect input-small report-filter" name="managers[]" multiple="multiple">';
                //    $_GET['mg'] = $_SESSION['id'];
                //}
                $out .= build_array_tree($engineers, ((isset($_GET['eng'])) ? explode(',', $_GET['eng']) : array()));
                $out .= '</select></div>';
            }
            //$out .= '<div class="control-group"><label class="control-label">Оператор:</label><div class="controls">';
            //$out .= '<select class="multiselect input-small report-filter" name="operators[]" multiple="multiple">';
            //$out .= build_array_tree($operators, ((isset($_GET['op'])) ? explode(',', $_GET['op']) : array()));
            //$out .= '</select></div></div>';
            //$out .= '<div class="control-group"><label class="control-label">Категории товаров:</label><div class="controls">';
            //$out .= '<select class="multiselect input-small report-filter" name="g_categories[]" multiple="multiple">';// onchange="change_report_filter(this)"
            //$out .= build_array_tree($categories, ((isset($_GET['g_cg'])) ? explode(',', $_GET['g_cg']) : array()));
            //$out .= '</select></div></div>';
            $out .= '<div class="form-group"><label>Товар:</label>';
            $out .= typeahead($this->all_configs['db'], 'goods', true, isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 4);
            //$out .= '<input class="input-big report-filter" type="text" placeholder="Введите ид" name="by_gid" value="';// onchange="change_report_filter(this)"
            //$out .= isset($_GET['by_gid']) && $_GET['by_gid'] > 0 ? intval($_GET['by_gid']) : '';
            //$out .= '" onkeydown="return isNumberKey(event, this)">';
            $out .= '</div><div class="form-group"><label >Категория:</label>';
            $out .= typeahead($this->all_configs['db'], 'categories-last', true, isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : '', 5);
            $out .= '</div><div class="form-group">';
            $out .= '<div class="checkbox"><label><input type="checkbox" value="1" name="novaposhta" ';
            $out .= (isset($_GET['np']) && $_GET['np'] == 1) ? 'checked' : '';
            $out .= ' >принято через Новую Почту</label></div>';
            $out .= '<div class="checkbox"><label><input type="checkbox" value="1" name="warranties" ';
            $out .= (isset($_GET['wrn']) && $_GET['wrn'] == 1) ? 'checked' : '';
            $out .= '>гарантийные</label></div>';
            $out .= '<div class="checkbox"><label><input type="checkbox" value="1" name="nowarranties" ';
            $out .= (isset($_GET['nowrn']) && $_GET['nowrn'] == 1) ? 'checked' : '';
            $out .= '>не гарантийные</label></div>';
            //$out .= '</div></div><div class="control-group"><div class="controls">';
            //$out .= '<label class="checkbox"><input type="checkbox" value="1" name="commission" ';
            //$out .= (isset($_GET['cms']) && $_GET['cms'] == 1) ? '' : 'checked';
            //$out .= ' >Учитывать комиссию</label>';
            //$out .= '<label class="checkbox"><input type="checkbox" value="1" name="delivery" ';
            //$out .= (isset($_GET['dlv']) && $_GET['dlv'] == 1) ? '' : 'checked';
            //$out .= '>Учитывать доставку</label>';
            $out .= '<div class="checkbox"><label><input type="checkbox" value="1" name="return" ';
            $out .= (isset($_GET['rtrn']) && $_GET['rtrn'] == 1) ? 'checked' : '';
            $out .= '>Не учитывать возвраты поставщику и списание товаров</label></div></div></div>';
            $out .= '<div class="form-group"><input class="btn btn-primary" type="submit" name="filters" value="Применить" /></div>';
            $out .= '</form>';

            // прибыль и оборот
            $currencies = $this->all_configs['suppliers_orders']->currencies;
            $cco = $this->all_configs['suppliers_orders']->currency_clients_orders;
            $orders_out = '';
            $profit = $turnover = $avg = $delivery = $commission = $warranties = 0;
            $filters = array();
            if ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $filters['acp'] = $user_id;
            }
            $amounts = $this->all_configs['manageModel']->profit_margin($filters + $_GET);
            if ($amounts && is_array($amounts['orders']) && count($amounts['orders']) > 0) {
                $count_goods = $sell_price = $purchase_price = $profit = $margin = 0;
                //$orders_out .= '<div class="well well-small">* приведенные ниже цены указаны без учета комиссии и доставки</div>';
                //$onclick = 'window.open(\'' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/ajax/?act=reports_turnover\')';
                //$orders_out .= '<input class="btn pull-right" onclick="' . $onclick . '" value="Выгрузить в Excel" type="button">';
                $orders_out .= '<table class="table table-compact"><thead><tr><td></td><td>№ Заказа</td>';
                $orders_out .= '<td>Устройство</td><td>Запчасти</td><td>Работа</td>';
                if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                    $orders_out .= '<td>Стоимость работ</td>';
                }
                $orders_out .= '<td>Цена продажи</td>';
                if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                    $orders_out .= '<td>Цена запчасти</td><td class="reports_turnover_profit invisible" >Операц. приб.</td><td class="reports_turnover_margin invisible">Наценка %</td>';
                }
                $orders_out .= '</tr></thead><tbody>';
                $services_prices = 0;
                foreach ($amounts['orders'] as $p) {
                    $url = $this->all_configs['prefix'] . 'orders/create/' . $p['order_id'];
                    $orders_out .= '<tr><td></td><td><a href="' . $url . '">' . $p['order_id'] . '</a></td>';
                    $url = $this->all_configs['prefix'] . 'categories/create/' . $p['category_id'];
                    $orders_out .= '<td><a href="' . $url . '">' . $p['title'] . '</a></td>';

                    $orders_out .= '<td>';
                    if (isset($p['goods'])) {
                        foreach ($p['goods'] as $g) {
                            $url = $this->all_configs['prefix'] . 'products/create/' . $g['goods_id'];
                            $orders_out .= '<a href="' . $url . '">' . $g['title'] . '</a><br />';
                        }
                    }
                    $orders_out .= '</td>';
                    $orders_out .= '<td>';
                    $services_price = 0;
                    if (isset($p['services'])) {
                        foreach ($p['services'] as $s) {
                            $url = $this->all_configs['prefix'] . 'products/create/' . $s['goods_id'];
                            $orders_out .= '<a href="' . $url . '">' . $s['title'] . '</a><br />';
                            $services_price += roundUpToAny($s['price'], 5000);
                            $services_prices += roundUpToAny($s['price'], 5000);
                        }
                    }
                    $orders_out .= '</td>';
                    if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                        $orders_out .= '<td>' . show_price($services_price, 2, ' ') . '</td>';
                    }
                    $orders_out .= '<td>' . show_price($p['turnover'], 2, ' ') . '</td>';
                    if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                        $orders_out .= '<td>' . show_price($p['purchase'], 2, ' ') . '</td>';
                        $orders_out .= '<td class="reports_turnover_profit invisible" >' . show_price($p['profit'], 2, ' ') . '</td>';
                        $orders_out .= '<td class="reports_turnover_margin invisible" >' . (is_numeric($p['avg']) ? round($p['avg'], 2) . ' %' : $p['avg']) . '</td></tr>';
                    }
                    $count_goods++;
                }
                $profit = $amounts['profit'];
                $turnover = $amounts['turnover'];
                $avg = $amounts['avg'];
                $purchase = $amounts['purchase'];
                $purchase2 = $amounts['purchase2'];

                $orders_out .= '<tr><td colspan="8"></td></tr><tr><td>Итого</td><td>' . $count_goods . ' шт.</td>';
                $orders_out .= '<td></td><td></td><td></td>';
                $orders_out .= '<td>' . show_price($services_prices, 2, ' ') . '</td>';
                $orders_out .= '<td>' . show_price($turnover, 2, ' ') . '</td>';
                $orders_out .= '<td>&sum;' . show_price($purchase, 2, ' ') . '<br />&equiv;' . show_price($purchase2, 2, ' ') . '</td>';
                $orders_out .= '<td class="reports_turnover_profit invisible">' . show_price(($profit), 2, ' ') . '</td>';
                $orders_out .= '<td class="reports_turnover_margin invisible">' . (is_numeric($avg) ? round($avg, 2) . ' %' : $avg) . '</td></tr>';
                $orders_out .= '</tbody></table>';
            }

            $href = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/export?act=reports-turnover&' . get_to_string();
            $out .= '<div class="well"><a class="btn btn-default pull-right" href="' . $href . '" target="_blank">Выгрузить</a>';
            $out .= '<p>Оборот: <strong>' . show_price($turnover, 2, ' ');
            $out .= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') . '</strong></p>';
            if (!$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $out .= '<p>Операционная прибыль: <a id="show_reports_turnover_profit_button" class="btn" > Рассчитать </a><strong><span class="reports_turnover_profit invisible" >'. show_price($profit, 2, ' ');
                $out .= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') . '</strong></span></p>';
                $out .= '<p>Средняя наценка: <a id="show_reports_turnover_margin_button" class="btn" > Рассчитать </a><strong><span class="reports_turnover_margin invisible" >' . (is_numeric($avg) ? round($avg, 2) : 0) . ' %</span></strong></p>';
            }
            $out .= '</div>' . $orders_out;
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function accountings_reports_net_profit()
    {
        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : date('01.m.Y', time())) . ' - ' .
                (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : date('t.m.Y', time()));

            $currencies = $this->all_configs['suppliers_orders']->currencies;
            $cco = $this->all_configs['suppliers_orders']->currency_clients_orders;

            // фильтры
            $out = '<form method="post" style="max-width: 300px">';
            $out .= '<label>Период:</label>
                     <div class="input-group">
                        <input type="text" name="date" value="' . $date . '" class="form-control daterangepicker" />
                        <span class="input-group-btn">
                            <input class="btn" type="submit" name="filters" value="Применить" />
                        </span>
                     </div>';
            $out .= '</form><br>';

            // чистая прибыль

            // фильтр по дате
            $day_from = 1 . date(".m.Y") . ' 00:00:00';
            $day_to = 31 . date(".m.Y") . ' 23:59:59';
            if (array_key_exists('df', $_GET) && strtotime($_GET['df']) > 0)
                $day_from = $_GET['df'] . ' 00:00:00';
            if (array_key_exists('dt', $_GET) && strtotime($_GET['dt']) > 0)
                $day_to = $_GET['dt'] . ' 23:59:59';

            $query = $this->all_configs['db']->makeQuery('AND t.date_transaction BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
                AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));

            // прибыль
            $amounts = $this->all_configs['manageModel']->profit_margin(array('df' => $day_from, 'dt' => $day_to));
            $profit = $amounts ? $amounts['profit'] : 0;

            $ext_query = '';
            if (array_key_exists('cat-non-all-ext', $this->all_configs['settings'])) {
                $cnae = array_filter(explode(',', $this->all_configs['settings']['cat-non-all-ext']));
                if (count($cnae) > 0) {
                    $ext_query = $this->all_configs['db']->makeQuery('AND l.contractors_categories_id NOT IN (?li)',
                        array($cnae));
                }
            }
            // затраты
            $ext = $this->all_configs['db']->query('SELECT cc.currency,
                      SUM(IF(t.transaction_type=1, -t.value_from, 0)) AS amount
                    FROM {cashboxes_transactions} AS t, {contractors_categories_links} AS l, {cashboxes_currencies} AS cc
                    WHERE t.contractor_category_link=l.id ?query
                      AND IF(t.transaction_type=1, cc.id=t.cashboxes_currency_id_from, NULL) ?query GROUP BY cc.currency',
                array($ext_query, $query))->vars();

            $out .= '<p>Чистая прибыль: <strong>';
            if (!$ext || !array_key_exists($cco, $ext)) {
                $out .= show_price($profit, 2, ' ');
                $out .= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '');
                $out .= ($ext && count($ext) > 0 ? ', ' : '');
            }
            if ($ext) {
                $i = 1;
                foreach ($ext as $c => $amount) {
                    if ($c == $cco) {
                        $out .= show_price($profit + $amount, 2, ' ');
                        $out .= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '');
                    } else {
                        $out .= show_price($amount, 2, ' ');
                        $out .= (array_key_exists($c, $currencies) ? ' ' . $currencies[$c]['shortName'] : '');
                    }
                    $out .= (count($ext) > $i ? ', ' : '');
                    $i++;
                }
            }
            $out .= '</strong></p>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_orders_pre($hash = '#orders_pre-noncash')
    {
        if (trim($hash) == '#orders_pre' || (trim($hash) != '#orders_pre-credit' && trim($hash) != '#orders_pre-noncash'))
            $hash = '#orders_pre-noncash';
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out = '<ul class="nav nav-pills">';
            $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_pre_noncash" class="click_tab"  href="#orders_pre-noncash" title="Безнал">Безнал<span class="tab_count hide tc_accountings_noncash_orders_pre"></span></a></li>';
            $out .= '<li><a onclick="click_tab(this, event)" data-open_tab="accountings_orders_pre_credit" class="click_tab" href="#orders_pre-credit" title="Кредит">Кредит<span class="tab_count hide tc_accountings_credit_orders_pre"></span></a></li></ul>';
            // бухгалтерия безнал
            $out .= '<div class="pill-content"><div id="orders_pre-noncash" class="pill-pane">';
            $out .= '</div>';

            // бухгалтерия кредит
            $out .= '<div id="orders_pre-credit" class="pill-pane">';
            $out .= '</div></div>';
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    function accountings_pre_noncash()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {

            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

            $out = '<div class="span2">';
            $out .= '<form method="post">';
            $out .= '<legend>Фильтры:</legend><label>Менеджер:</label>';
            $out .= '<select class="multiselect input-small" name="managers[]" multiple="multiple">';
            // менеджеры
            $managers = $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders');
            foreach ($managers as $manager) {
                $out .= '<option ' . ((isset($_GET['mg']) && in_array($manager['id'], explode(',', $_GET['mg']))) ? 'selected' : '');
                $out .= ' value="' . $manager['id'] . '">' . htmlspecialchars($manager['name']) . '</option>';
            }
            $out .= '</select>';
            $out .= '<label>Дата:</label>';
            $out .= '<input type="text" placeholder="Дата" name="date" class="daterangepicker input-medium" value="' . $date . '" />';
            $out .= '<label>№ заказа:</label><input name="client-order" value="';
            $out .= isset($_GET['co']) && !empty($_GET['co']) ? trim(htmlspecialchars($_GET['co'])) : '';
            $out .= '" type="text" class="input-medium" placeholder="№ заказа">';
            $out .= '<label>Клиент:</label>';
            $out .= '<div>' . typeahead($this->all_configs['db'], 'clients', false, (isset($_GET['c_id']) && $_GET['c_id'] > 0 ? $_GET['c_id'] : 0), 3) . '</div>';
            $out .= '<input type="submit" name="filters" class="btn" value="Фильтровать">';
            $out .= '</form>';

            $out .= '</div><div class="span10">';

            $_GET['prepay'] = true;
            $queries = $this->all_configs['manageModel']->clients_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            // достаем заказы
            $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page);


            if (count($orders) > 0) {
                $out .= '<table class="table table-striped"><thead><tr><td>№</td><td>Дата</td><td>Кто обработал</td>';
                $out .= '<td>ФИО клиента</td><td>Сумма</td><td>Оплачено</td><td>Способ оплаты</td><td>Оплата</td></tr></thead><tbody>';
                foreach ($orders as $order) { //<td>Товар</td>
                    $btn = '<div class="text-success">Оплачено</div>';
                    //if ($order['sum_paid'] != $order['sum'])
                    //    $btn = '<div class="text-error">Не оплачено</div>';
                    if ($order['sum'] > $order['sum_paid'] && ($order['status'] == $this->all_configs['configs']['order-status-wait-pay']
                            || $order['status'] == $this->all_configs['configs']['order-status-part-pay'])) {
                        $onclick = 'pay_client_order(this, 2, ' . $order['order_id'] . ', 0)';
                        $btn = '<input type="button" class="btn btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
                    }
                    $payment = (array_key_exists($order['payment'], $this->all_configs['configs']['payment-msg'])) ? $this->all_configs['configs']['payment-msg'][$order['payment']]['name'] : '';
                    //$fio = (mb_strlen(trim($order['fio']), 'UTF-8') > 0) ? trim($order['fio']) : ((mb_strlen(trim($order['phone']), 'UTF-8') > 0) ? trim($order['phone']) : trim($order['email']));
                    $out .= '<tr><td>' . $order['order_id'] . '</td>'
                        . '<td><span title="' . do_nice_date($order['date_add'], false) . '">' . do_nice_date($order['date_add']) . '</span></td>'
                        . '<td>' . get_user_name($order, 'h_') . '</td>'
                        . '<td>' . get_user_name($order, 'o_') . '</td>'
                        //. '<td><a href="' . $this->all_configs['prefix'] . 'products/create/' . $chain['goods_id'] . '">' . htmlspecialchars($chain['g_title']) . '</a></td>'
                        . '<td>' . show_price($order['sum']) . '</td>'
                        . '<td>' . show_price($order['sum_paid']) . '</td>'
                        . '<td>' . $payment . '</td>'
                        . '<td>' . $btn . '</td></tr>';
                }
                $out .= '</tbody></table>';

                // количество заказов клиентов
                $count = $this->all_configs['manageModel']->get_count_clients_orders($query);
                $count_page = ceil($count/$count_on_page);
                // строим блок страниц
                $out .= page_block($count_page, '#orders_pre-noncash');
            } else {
                $out .= '<p  class="text-error">Нет заказов</p>';
            }
            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function accountings_orders_pre_credit()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {

            $orders = $this->all_configs['db']->query('SELECT o.id, c.fio, c.email, c.phone, o.status, c.contractor_id FROM {orders} as o
                    LEFT JOIN (SELECT id, fio, email, phone, contractor_id FROM {clients})c ON c.id=o.user_id
                    WHERE o.status=?i OR o.status=?i OR o.status=?i ORDER BY o.status, o.date_add DESC',
                array($this->all_configs['configs']['order-status-loan-approved'], $this->all_configs['configs']['order-status-loan-wait'],
                    $this->all_configs['configs']['order-status-loan-denied']))->assoc();

            if ($orders && count($orders) > 0) {
                $out .= '<table class="table table-striped"><thead><tr><td>ФИО клиента</td><td>Товар</td><td>Кредит одобрен,<br />документы готовы</td><td>Отказ</td></tr></thead><tbody>';
                foreach ($orders as $order) {
                    $goods_html = '';
                    $goods = $this->all_configs['db']->query('SELECT title FROM {orders_goods} WHERE order_id=?i', array($order['id']))->assoc();
                    if ($goods && count($goods) > 0) {
                        foreach ($goods as $product) {
                            $goods_html .= '<p>' . htmlspecialchars($product['title']) . '</p>';
                        }
                    }
                    $disabled = '';
                    $approved_status = '';
                    $denied_status = '';
                    if ($order['status'] == $this->all_configs['configs']['order-status-loan-approved']) {
                        $approved_status = 'checked';
                        $disabled = 'disabled';
                    }
                    if ($order['status'] == $this->all_configs['configs']['order-status-loan-denied']) {
                        $denied_status = 'checked';
                        $disabled = 'disabled';
                    }
                    $fio = (mb_strlen(trim($order['fio']), 'UTF-8') > 0) ? trim($order['fio']) : ((mb_strlen(trim($order['phone']), 'UTF-8') > 0) ? trim($order['phone']) : trim($order['email']));
                    $out .= '
                        <tr>
                            <td>' . htmlspecialchars($fio) . '</td>
                            <td>' . $goods_html . '</td>
                            <td><input ' . $approved_status . ' ' . $disabled . ' type="checkbox" value="1" class="btn" onclick="accounting_credit_approved(this)" data-id="' . $order['id'] . '" /></td>
                            <td><input ' . $denied_status . ' ' . $disabled . ' type="checkbox" value="1" class="btn" onclick="accounting_credit_denied(this)" data-id="' . $order['id'] . '" /></td>
                        </tr>';
                }
                $out .= '</tbody></table>';
            } else {
                $out .= '<p  class="text-error">Нет заказов</p>';
            }
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_orders($hash = '#a_orders-clients')
    {
        if (trim($hash) == '#a_orders' || (trim($hash) != '#a_orders-clients' && trim($hash) != '#a_orders-suppliers'))
            $hash = '#a_orders-clients';
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out = '<ul class="nav nav-pills">';
            $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_orders_clients"';
            $out .= ' href="#a_orders-clients" title="Заказы клиентов">Клиентов<span class="tab_count hide tc_accountings_clients_orders"></span></a></li>';
            $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_orders_suppliers"';
            $out .= ' href="#a_orders-suppliers" title="Заказы поставщику">Поставщику<span class="tab_count hide tc_accountings_suppliers_orders"></span></a></li></ul>';
            $out .= '<div class="pill-content">';

            $out .= '<div id="a_orders-suppliers" class="pill-pane">';
            $out .= '</div><!--#a_orders-suppliers-->';

            $out .= '<div id="a_orders-clients" class="pill-pane">';

            $out .= '</div><!--#a_orders-clients--></div><!--.pill-content-->';
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    function accountings_orders_suppliers()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out = '<div class="span2">';
            $out .= $this->all_configs['suppliers_orders']->show_filters_suppliers_orders();
            $out .= '</div><div class="span10">';
            $out .= '<h4>Заказы поставщику которые ждут оплаты</h4><br />';
            $_GET['type'] = 'pay';
            $queries = $this->all_configs['manageModel']->suppliers_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            $out .= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders, false, true);
            // количество заказов
            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);
            //$count = $this->all_configs['manageModel']->get_count_accounting_suppliers_orders();
            $count_page = ceil($count / $count_on_page);
            $out .= page_block($count_page, '#a_orders-suppliers');
            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function show_tr_accountings_orders_clients($chain, $type = 0)
    {
        $out = '';
        if ($type == 1) {
            $class = $chain['return'] == 0 && $chain['delivery_cost'] > $chain['delivery_paid'] ? '' : 'class="success"';
            $out .= '<td ' . $class . '>Оплата за доставку';
        } elseif($type == 2) {
            $class = $chain['return'] == 0 && $chain['payment_cost'] > $chain['payment_paid'] ? '' : 'class="success"';
            $out .= '<td ' . $class . '>Оплата за комиссию';
        } else {
            $class = $chain['return'] == 0 && $chain['price'] > $chain['paid'] ? '' : 'class="success"';
            $out .= '<td ' . $class . '><a href="' . $this->all_configs['prefix'] . 'products/create/' . $chain['goods_id'] . '">';
            $out .= htmlspecialchars($chain['g_title']) . '</a>';
        }
        $out .= '</td><td ' . $class . '>' . get_user_name($chain) . '</td>';
        $out .= '<td ' . $class . '>' . get_user_name($chain, 'u_') . '</td>';
        $out .= '<td ' . $class . '><span title="' . do_nice_date($chain['date_add'], false) . '">' . do_nice_date($chain['date_add']) . '</span></td>';
        $out .= '<td ' . $class . '><span title="' . do_nice_date($chain['date_accept'], false) . '">' . do_nice_date($chain['date_accept']) . '</span></td>';
        $out .= '<td ' . $class . '><a href="' . $this->all_configs['prefix'] . 'orders/create/' . $chain['order_id'] . '">';
        $out .= htmlspecialchars($chain['order_id']) . '</a></td>';
        if ($type == 1) {
            $out .= '<td ' . $class . '>' . show_price($chain['delivery_cost']) . '</td>';
            $out .= '<td ' . $class . '>' . show_price($chain['delivery_paid']) . '</td>';
        } elseif($type == 2) {
            $out .= '<td ' . $class . '>' . show_price($chain['payment_cost']) . '</td>';
            $out .= '<td ' . $class . '>' . show_price($chain['payment_paid']) . '</td>';
        } else {
            $out .= '<td ' . $class . '>' . show_price($chain['price']) . '</td>';
            $out .= '<td ' . $class . '>' . show_price($chain['paid']) . '</td>';
        }
        $out .= '<td ' . $class . '>';
        if ($chain['return'] == 1 && $chain['paid'] > 0 && $type == 0) {
            $onclick = 'pay_client_order(this, 1, ' . $chain['order_id'] . ', ' . $chain['b_id'] . ')';
            $out .= '<input type="button" class="btn btn-mini" value="Выдать оплату" onclick="' . $onclick . '" />';
        }
        if ($chain['return'] == 0 && $chain['price'] > $chain['paid'] && $type == 0) {
            $onclick = 'pay_client_order(this, 2, ' . $chain['order_id'] . ', ' . $chain['b_id'] . ')';
            $out .= '<input type="button" class="btn btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
        } elseif ($chain['return'] == 0 && $chain['delivery_cost'] > $chain['delivery_paid'] && $type == 1) {
            $onclick = 'pay_client_order(this, 2, ' . $chain['order_id'] . ', ' . $chain['b_id'] . ', \'delivery\')';
            $out .= '<input type="button" class="btn btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
        } elseif ($chain['return'] == 0 && $chain['payment_cost'] > $chain['payment_paid'] && $type == 2) {
            $onclick = 'pay_client_order(this, 2, ' . $chain['order_id'] . ', ' . $chain['b_id'] . ', \'payment\')';
            $out .= '<input type="button" class="btn btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
        } else {

        }
        $out .= '</td>';

        return $out;
    }

    function accountings_orders_clients()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

            $out = '<div class="span2"><form method="post"><legend>Фильтры:</legend>';
            //$out .= '<label>Оператор:</label>';
            //$out .= '<select class="multiselect input-small report-filter" name="operators[]" multiple="multiple">';
            //$operators = $this->get_operators();
            //$out .= build_array_tree($operators, ((isset($_GET['op'])) ? explode(',', $_GET['op']) : array()));
            //$out .= '</select>';
            $out .= '<div class="form-group"><label>Дата:</label>';
            $out .= '<input type="text" placeholder="Дата" name="date" class="daterangepicker form-control" value="' . $date . '" /></div>';
            $out .= '<div class="form-group"><label>№ заказа:</label><input name="client-order_id" value="';
            $out .= isset($_GET['co_id']) && $_GET['co_id'] > 0 ? $_GET['co_id'] : '';
            $out .= '" type="text" class="form-control" placeholder="№ заказа"></div>';
            $out .= '<div class="form-group"><label>Категория:</label>';
            $out .= typeahead($this->all_configs['db'], 'categories', false, isset($_GET['g_cg']) && $_GET['g_cg'] > 0 ? $_GET['g_cg'] : 0);
            $out .= '</div><div class="form-group"><label>ФИО:</label><input name="client-order" value="';
            $out .= isset($_GET['co']) && !empty($_GET['co']) ? trim(htmlspecialchars($_GET['co'])) : '';
            $out .= '" type="text" class="form-control" placeholder="ФИО">';
            $out .= '</div><div class="form-group"><label>Товар:</label>';
            $out .= typeahead($this->all_configs['db'], 'goods', true, isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 2, 'input-small', 'input-mini');
            $out .= '</div><div class="form-group"><input type="submit" name="filters" class="btn btn-primary" value="Фильтровать"></div></div>';
            $out .= '</form></div><div class="span10">';

            $chains = array();
            $_chains = null;
            $query = $this->all_configs['manageModel']->global_filters($_GET,
                array('date', 'category', 'product', 'operators', 'client', 'client_orders_id'));

            $count_on_page = $this->count_on_page;
            $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;

            /*$chains_ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                    LEFT JOIN {goods} as g ON og.goods_id=g.id
                    LEFT JOIN {category_goods} as cg ON cg.goods_id=g.id
                    LEFT JOIN {chains_headers} as h ON h.goods_id=g.id AND h.order_id=o.id AND o.id=h.order_id AND og.order_id=h.order_id AND og.id=h.order_goods_id
                    LEFT JOIN {chains_bodies} as b ON h.id=b.chain_id
                    LEFT JOIN {clients} as c ON c.id=o.user_id
                    LEFT JOIN {users} as u ON u.id=h.user_id
                    WHERE o.id > 0 AND h.id > 0 AND b.id > 0 AND IF(h.return=0, b.type=?i, b.type=?i) ?query
                    ORDER BY b.date_add DESC LIMIT ?i, ?i',
                array($this->all_configs['chains']->chain_accounting_from,
                    $this->all_configs['chains']->chain_accounting_to, $query, $skip, $count_on_page))->vars();

            if ($chains_ids) {
                $_chains = $this->all_configs['db']->query('SELECT og.title as g_title, h.id as h_id, h.goods_id, h.order_id,
                      h.paid, og.price, og.warranties_cost, o.course_value, b.user_id_issued, b.id as b_id, o.delivery_cost,
                      b.date_accept, o.delivery_paid, o.payment_cost, o.payment_paid, c.contractor_id, h.return,
                      b.date_add, u.login as u_login, u.email as u_email, u.fio as u_fio, o.fio, o.phone, o.email
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                    LEFT JOIN {goods} as g ON og.goods_id=g.id
                    #LEFT JOIN {category_goods} as cg ON cg.goods_id=g.id
                    LEFT JOIN {chains_headers} as h ON h.goods_id=g.id AND h.order_id=o.id AND o.id=h.order_id AND og.order_id=h.order_id AND og.id=h.order_goods_id
                    LEFT JOIN {chains_bodies} as b ON h.id=b.chain_id
                    LEFT JOIN {clients} as c ON c.id=o.user_id
                    LEFT JOIN {users} as u ON u.id=h.user_id
                    WHERE o.id > 0 AND h.id > 0 AND b.id > 0 AND IF(h.return=0, b.type=?i, b.type=?i) AND o.id IN (?li)
                    ORDER BY Field(o.id, ?li)',
                    array($this->all_configs['chains']->chain_accounting_from,
                        $this->all_configs['chains']->chain_accounting_to,
                        array_keys($chains_ids), array_keys($chains_ids)))->assoc();

                $count = $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                    LEFT JOIN {goods} as g ON og.goods_id=g.id
                    LEFT JOIN {category_goods} as cg ON cg.goods_id=g.id
                    LEFT JOIN {chains_headers} as h ON h.goods_id=g.id AND h.order_id=o.id AND o.id=h.order_id AND og.order_id=h.order_id AND og.id=h.order_goods_id
                    LEFT JOIN {chains_bodies} as b ON h.id=b.chain_id
                    LEFT JOIN {clients} as c ON c.id=o.user_id
                    LEFT JOIN {users} as u ON u.id=h.user_id
                    WHERE o.id > 0 AND h.id > 0 AND b.id > 0 AND IF(h.return=0, b.type=?i, b.type=?i) ?query',
                    array($this->all_configs['chains']->chain_accounting_from,
                        $this->all_configs['chains']->chain_accounting_to, $query))->el();
            }

            if ($_chains) {
                foreach ($_chains as $_chain) {
                    $chains[$_chain['order_id']][$_chain['h_id']] = $_chain;
                }
            }*/

            $count = $this->all_configs['manageModel']->get_count_accounting_clients_orders($query);
            $orders = $this->all_configs['db']->query('SELECT o.id, o.course_value, o.sum, o.sum_paid, o.fio,
                        o.phone, o.email, o.date_add, o.date_pay, o.prepay,
                        a.email as a_email, a.fio as a_fio, a.phone as a_phone, a.login as a_login
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                    LEFT JOIN {users} as a ON a.id=o.accepter
                    WHERE 1=1 ?query GROUP BY o.id ORDER BY o.date_add DESC LIMIT ?i, ?i',
                array($query, $skip, $count_on_page))->assoc('id');

            if ($orders) {
                $goods = $this->all_configs['db']->query('SELECT og.title, og.goods_id, og.order_id, og.date_add
                        FROM {orders_goods} as og
                        WHERE og.order_id IN (?li) ORDER BY og.date_add DESC',
                    array(array_keys($orders)))->assoc();

                foreach ($goods as $product) {
                    $orders[$product['order_id']]['goods'][$product['goods_id']] = $product;
                }

                $out .= '<table class="table table-bordered table-medium"><thead><tr><td></td><td>Наименование</td><td>ФИО клиента</td><td>Кто запросил</td><td>Дата запроса</td>';
                $out .= '<td>Заказ</td><td>Оплата</td><td>Оплачено</td><td>Управление</td></tr></thead><tbody>';//<td>Дата оплаты</td>
                $i = 1;
                foreach($orders as $order) {
                    $out .= '<tr class=""><td>' . $i++ . '</td><td>';
                    if (isset($order['goods']) && count($order['goods']) > 0) {
                        foreach ($order['goods'] as $product) {
                            $href = $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'];
                            $out .= '<a href="' . $href . '">' . htmlspecialchars($product['title']) . '</a><br />';
                        }
                    }
                    $out .= '</td><td>' . get_user_name($order) . '</td>';
                    $out .= '<td>' . get_user_name($order) . '</td>';
                    $out .= '<td><span title="' . do_nice_date($order['date_add'], false) . '">' . do_nice_date($order['date_add']) . '</span></td>';
                    //$out .= '<td><span title="' . do_nice_date($order['date_pay'], false) . '">' . do_nice_date($order['date_pay']) . '</span></td>';
                    $href = $this->all_configs['prefix'] . 'orders/create/' . $order['id'];
                    $out .= '<td><a href="' . $href . '">№' . $order['id'] . '</a></td>';
                    if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])) {
                        $out .= '<td>' . show_price($order['prepay']) . '</td>';
                        $out .= '<td>' . show_price($order['sum_paid']) . '</td><td>';
                    } else {
                        $out .= '<td>' . show_price($order['sum']) . '</td>';
                        $out .= '<td>' . show_price($order['sum_paid']) . '</td><td>';
                    }

                    if (intval($order['sum']) < intval($order['sum_paid'])) {
                        $onclick = 'pay_client_order(this, 1, ' . $order['id'] . ')';
                        $out .= '<input type="button" class="btn btn-mini" value="Выдать оплату" onclick="' . $onclick . '" />';
                    }
                    if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])) {
                        $onclick = 'pay_client_order(this, 2, ' . $order['id'] . ', 0, \'prepay\')';
                        $out .= '<input type="button" class="btn btn-xs" value="Принять предоплату" onclick="' . $onclick . '" />';
                    } elseif (intval($order['sum']) > intval($order['sum_paid'])) {
                        $onclick = 'pay_client_order(this, 2, ' . $order['id'] . ')';
                        $out .= '<input type="button" class="btn btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
                    }
                    $out .= '</td></tr>';
                }
                $out .= '</tbody></table>';

                $count_page = ceil($count / $count_on_page);
                $out .= page_block($count_page, '#a_orders-clients');
            } else {
                $out .= '<p class="text-error">Нет заказов</p>';
            }

            /*if (count($chains) > 0) {
                //$out .= '<h4>Заказы клиентов которые ждут оплаты</h4><br />';
                $out .= '<table class="table table-bordered table-medium"><thead><tr><td></td><td>Наименование</td><td>ФИО клиента</td><td>Кто запросил</td><td>Дата запроса</td>';
                $out .= '<td>Дата оплаты</td><td>Заказ</td><td>Оплата</td><td>Оплачено</td><td>Управление</td></tr></thead><tbody>';
                $i = 1;
                foreach ($chains as $h_chain) {
                    //$chain = current($h_chain);
                    $rowspan = count($h_chain);// + ($chain['delivery_cost'] > 0 ? 1 : 0) + ($chain['payment_cost'] > 0 ? 1 : 0);
                    $out .= '<tr class="border-top"><td rowspan="' . $rowspan . '">' . $i . '</td>';
                    foreach ($h_chain as $chain) {
                        $chain['price'] = $this->all_configs['chains']->chain_price($chain);
                        $out .= $this->show_tr_accountings_orders_clients($chain);
                        $out .= '</tr><tr>';
                    }
                    $out .= '<td rowspan="' . $rowspan . '"><input type="button"></td>';
                    // оплата способа доставки
                    if ($chain['delivery_cost'] > 0) {
                        $out .= $this->show_tr_accountings_orders_clients($chain, 1);
                        $out .= '</tr><tr>';
                    }
                    // оплата за комиссию (способ оплаты)
                    if ($chain['payment_cost'] > 0) {
                        $out .= $this->show_tr_accountings_orders_clients($chain, 2);
                    }
                    $out .= '</tr>';
                    $i++;
                }
                $out .= '</tbody></table>';
                $count_page = ceil($count / $count_on_page);
                $out .= page_block($count_page, '#a_orders-clients');
            } else {
                $out .= '<p class="text-error">Нет заказов</p>';
            }*/
            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function accountings_contractors()
    {
        $contractors_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
                $this->all_configs['oRole']->hasPrivilege('accounting-contractors')) {
            // списсок контрагентов
            if ($this->contractors) {

                $contractors_html = '';
                //$contractors_html = '<pre>'.print_r($this->contractors, true).'</pre>';
                //$contractors_html = '<pre>'.print_r($this->all_configs['suppliers_orders']->currencies[$this->all_configs['configs']['erp-contractor-balance-currency']]['shortName'], true).'</pre>';


                $contractors_html .= '<table class="table"><thead><tr><td></td><td>Название</td><td>Сумма</td><td></td></tr></thead><tbody>';
                foreach ($this->contractors as $contractor) {

                    //if ($contractor['type'] == ) { если надо конкретный тип контрагентов
                    $contractors_html .= '<tr class="'
                        . ($contractor['amount'] > 0 ? 'success' : '')
                        . ($contractor['amount'] < 0 ? 'danger' : '')
                        . '">'
                        . '<td>' . $contractor['id'] . '</td>';
                    $contractors_html .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0];
                    $contractors_html .= '?ct=' . $contractor['id'] .'#transactions-contractors">' . $contractor['name'] . '</a></td>';
                    $contractors_html .= '<td>' . show_price($contractor['amount'])
                        . ' '
                        //. $this->all_configs['suppliers_orders']->currencies[$this->all_configs['configs']['erp-contractor-balance-currency']]['shortName']
                        .'</td><td><input class="btn btn-default btn-xs" type="button" value="Проверить" onclick="check_contractor_amount(this, ' . $contractor['id'] . ')" />'
                        .'<div class="pull-right">'.($contractor['amount'] > 0 ? 'Вам должны' : 
                                                     ($contractor['amount'] > 0 ? 'Вы должны' : '')).'</div></td></tr>';
                    //}
                }
                $contractors_html .= '</tbody></table>';
            } else {

                $contractors_html = '<p class="text-error">Список контрагентов пуст.</p>';
            }
        }

        return array(
            'html' => $contractors_html,
            'functions' => array(),
        );
    }

    function accountings_settings($hash = '#settings-cashboxes')
    {
        if (trim($hash) == '#settings' || (trim($hash) != '#settings-cashboxes' && trim($hash) != '#settings-currencies'
                && trim($hash) != '#settings-categories_expense' && trim($hash) != '#settings-categories_income'
                && trim($hash) != '#settings-contractors')
        )
            $hash = '#settings-cashboxes';

        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $out = '<ul class="nav nav-pills">';
            if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_cashboxes" href="#settings-cashboxes" title="Создать/редактировать кассу">Кассы</a></li>';
                $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_currencies" href="#settings-currencies" title="Валюты">Валюты</a></li>';
            }
            $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_categories_expense" href="#settings-categories_expense" title="Создать/редактировать статью расход">Статьи расход</a></li>';
            $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_categories_income" href="#settings-categories_income" title="Создать/редактировать статью приход">Статьи приход</a></li>';
            $out .= '<li><a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_contractors" href="#settings-contractors" title="Создание/редактирование контрагентов">Контрагенты</a></li>';
            $out .= '</ul>';
            $out .= '<div class="pill-content">';

            if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $out .= '<div id="settings-cashboxes" class="pill-pane">';
                $out .= '</div>';

                $out .= '<div id="settings-currencies" class="pill-pane">';
                $out .= '</div>';
            }

            if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
                $out .= '<div id="settings-categories_expense" class="pill-pane">';
                $out .= '</div><!--#settings-categories_expense-->';

                $out .= '<div id="settings-categories_income" class="pill-pane">';
                $out .= '</div><!--#settings-categories_income-->';

                $out .= '<div id="settings-contractors" class="pill-pane">';
                $out .= '</div><!--#settings-categories-->';
            }
            $out .= '</div>';

            $out .= '</div><div>';
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    function accountings_settings_cashboxes()
    {
        $out = '';

        // форма для создания кассы
        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // валюты
            $cashboxes_currencies = $this->cashboxes_courses();

            $out .= "<div class='panel-group' id='accordion_cashboxes'>" . $this->form_cashbox($cashboxes_currencies);

            // список форм для редактирования касс
            if (count($this->cashboxes) > 0) {
                $i = 1;
                foreach ($this->cashboxes as $cashbox) {
                    $i++;
                    if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                        $out .= $this->form_cashbox($cashboxes_currencies, $cashbox, $i);
                    }
                }
            }

            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_settings_currencies()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // валюты
            $cashboxes_currencies = $this->cashboxes_courses();
            $out .= '<form method="post">';
            $new_courses = $this->all_configs['suppliers_orders']->currencies;
            // редактируем валюты касс
            $out .= '<table class="table table-striped"><thead><tr><td>Наименование</td><td>Сокращение</td>';
            $out .= '<td>Курс</td><td>Удалить</td></tr></thead><tbody id="edit-courses-from">';
            foreach ($cashboxes_currencies as $cashbox_currency) {
                if (array_key_exists($cashbox_currency['currency'], $new_courses))
                    unset($new_courses[$cashbox_currency['currency']]);

                $out .= "<tr>
                        <td><input class='form-control' type='text' name='cashbox_cur_name[{$cashbox_currency['currency']}]' placeholder='Наименование' value='{$cashbox_currency['name']}' /></td>
                        <td><input class='form-control' type='text' name='cashbox_short_name[{$cashbox_currency['currency']}]' placeholder='Сокращение' value='{$cashbox_currency['short_name']}' /></td>";
                if (array_key_exists($cashbox_currency['currency'], $this->all_configs['suppliers_orders']->currencies) && $this->all_configs['suppliers_orders']->currencies[$cashbox_currency['currency']]['currency-name'] == $this->all_configs['configs']['default-currency']) {
                    $out .=
                        "<td></td><td></td>";
                } else {

                    $price = show_price($cashbox_currency['course']);
                    $out .= "
                        <td>1 {$cashbox_currency['short_name']} = <input class='form-control input-auto-width inline-block' type='text' name='cashbox_course[{$cashbox_currency['currency']}]' placeholder='Курс' value='{$price}' onkeydown='return isNumberKey(event, this)' /></td>
                        <td><i class='glyphicon glyphicon-remove remove_currency' onclick='remove_currency(this)' data-currency_id='{$cashbox_currency['currency']}'></i></td>";
                }
                $out .= '</tr>';
            }
            $out .= "<tr><td colspan='4'><input type='submit' class='btn btn-primary' name='cashboxes-currencies-edit' value='Сохранить' /></td></tr></tbody></table>";
            $out .= '</form>';
            // добавить валюту
            $out .= '<form class="form-inline"><label>Добавить валюту </label> <select class="form-control" onchange="add_currency(this)" id="add_new_course"><option value="">Не выбрана валюта...</option>';
            foreach ($new_courses as $new_course_id => $new_course) {
                $out .= "<option value='{$new_course_id}'>{$new_course['name']} [{$new_course['shortName']}]</option>";
            }
            $out .= '</select></form>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_settings_categories_expense()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            // создать статью расход
            $out = '<button class="btn btn-primary" onclick="alert_box(this, false, \'create-cat-expense\')" type="button">Создать статью расход</button>';

            // списсок статей
            $categories = $this->get_contractors_categories(1);
            $out .= '<br /><br /><div class="three-column" id="create-cat-expense">' . build_array_tree($categories, array(), 3) . '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_settings_categories_income()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            // создать статью приход
            $out = '<button class="btn btn-primary" onclick="alert_box(this, false, \'create-cat-income\')" type="button">Создать статью приход</button>';

            // списсок статей
            $categories = $this->get_contractors_categories(2);
            $out .= '<br /><br /><div class="three-column" id="create-cat-income">' . build_array_tree($categories, array(), 3) . '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    function accountings_settings_contractors()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            // форма для создания контрагента расход
            $out = '<div class="panel-group" id="accordion_contractors">';
            $out .= '<button type="button" onclick="alert_box(this, false, \'create-contractor-form\')" class="btn btn-primary">Создать контрагента</button>';
            $out .= '<br><br><legend>Редактирование статей контрагента</legend>';
            // список форм для редактирования категории расход
            if (count($this->contractors) > 0) {
                $i = 1;
                foreach ($this->contractors as $contractor) {
                    //if (array_key_exists(1, $contractor['transaction_types'])) {
                    $out .= $this->form_contractor($contractor, isset($_GET['ct']) && $_GET['ct'] > 0 ? $_GET['ct'] : 0);
                    $i++;
                    //}
                }
            }
            $out .= '</div><!--#accordion_contractors-->';
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

}