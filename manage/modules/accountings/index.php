<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Models/Cashboxes.php';

$modulename[30] = 'accountings';
$modulemenu[30] = l('Бухгалтерия');
$moduleactive[30] = !$ifauth['is_2'];

/**
 * @property  MUsers                      Users
 * @property  MOrders                     Orders
 * @property  MClients                    Clients
 * @property  MContractorsCategoriesLinks ContractorsCategoriesLinks
 * @property  MCashboxesTransactions      CashboxesTransactions
 * @property  MCashboxes                  Cashboxes
 * @property Transactions                 Transactions
 */
class accountings extends Controller
{
    protected $cashboxes = array();
    protected $contractors = array();

    protected $months = array(
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'Augest',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    );

    protected $course_default = 100; // default course (uah) in cent
    public $uses = array(
        'Users',
        'Cashboxes',
        'CashboxesTransactions',
        'Clients',
        'Orders',
        'ContractorsCategoriesLinks'
    );

    public function __construct(&$all_configs)
    {
        parent::__construct($all_configs);

        $this->Transactions = new Transactions($this->all_configs);
    }

    /**
     * @inheritdoc
     */
    public function routing(Array $arrequest)
    {
        $result = parent::routing($arrequest);
        if (isset($arrequest[1]) && $arrequest[1] == 'export') {
            $result = $this->export();
        }
        return $result;
    }

    /**
     * @return mixed|string
     */
    public function getNotCanShowModuleError()
    {
        return l('У Вас нет доступа к кассам. Доступ к кассам можно настроить в карточке пользователя или с помощью системы прав доступа');
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return ($this->all_configs['oRole']->hasCashierPermission($this->getUserId())
            || $this->all_configs['oRole']->hasPrivilege('accounting-contractors')
            || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
            || $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
            || $this->all_configs['oRole']->hasPrivilege('partner'));
    }

    /**
     * @param $post
     */
    function check_post(Array $post)
    {
        $mod_id = $this->all_configs['configs']['accountings-manage-page'];
        $user_id = $this->getUserId();

        // допустимые валюты
        $currencies = $this->all_configs['suppliers_orders']->currencies;

        if (isset($post['filter-orders'])) {

            $url = array();

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url[] = 'df=' . urlencode(trim($df));
                $url[] = 'dt=' . urlencode(trim($dt));
            }

            if (isset($post['categories']) && $post['categories'] > 0) {
                // фильтр по категориям товаров
                $url[] = 'g_cg=' . intval($post['categories']);
            }

            if (isset($post['goods']) && $post['goods'] > 0) {
                // фильтр по товару
                $url[] = 'by_gid=' . intval($post['goods']);
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                $url[] = 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['suppliers']) && !empty($post['suppliers'])) {
                // фильтр по поставщикам
                $url[] = 'sp=' . implode(',', $post['suppliers']);
            }

            if (isset($post['client-order']) && !empty($post['client-order'])) {
                // фильтр клиенту/заказу
                $url[] = 'co=' . urlencode(trim($post['client-order']));
            }

            if (isset($post['supplier_order_id_part']) && $post['supplier_order_id_part'] > 0) {
                // фильтр по заказу частичный
                $url[] = 'pso_id=' . $post['supplier_order_id_part'];
            }

            if (isset($post['so-status']) && $post['so-status'] > 0) {
                // фильтр по статусу
                $url[] = 'sst=' . intval($post['so-status']);
            }

            if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
                // фильтр по заказу
                $url[] = 'so_id=' . $post['supplier_order_id'];
            }

            if (isset($post['so_st']) && $post['so_st'] > 0) {
                // фильтр клиенту/заказу
                $url[] = 'so_st=' . $post['so_st'];
            }

            if (isset($post['my']) && !empty($post['my'])) {
                // фильтр клиенту/заказу
                $url[] = 'my=1';
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . implode('&',
                        $url));
            Response::redirect($url);
        }
        // фильтруем заказы клиентов
        if (isset($post['filters'])) {
            $url = $this->orderFilters($post);
            Response::redirect($url);
        }

        if (isset($post['filter-transactions'])) {
            // фильтрация транзакций
            $url = array();

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url[] = 'df=' . urlencode(trim($df));
                $url[] = 'dt=' . urlencode(trim($dt));
            }

            // фильтр по кассам
            if (isset($post['cashboxes']) && !empty($post['cashboxes'])) {
                $url[] = 'cb=' . implode(',', $post['cashboxes']);
                // искючить
                if (isset($post['include_cashboxes']) && $post['include_cashboxes'] == -1) {
                    $url[] = 'cbe=-1';
                }
            }

            // фильтр по категориям
            if (isset($post['categories']) && !empty($post['categories'])) {
                $url[] = 'cg=' . implode(',', $post['categories']);
                // искючить
                if (isset($post['include_categories']) && $post['include_categories'] == -1) {
                    $url[] = 'cge=-1';
                }
            }

            // фильтр по контрагентам
            if (isset($post['contractors']) && !empty($post['contractors'])) {
                $url[] = 'ct=' . implode(',', $post['contractors']);
                // искючить
                if (isset($post['include_contractors']) && $post['include_contractors'] == -1) {
                    $url[] = 'cte=-1';
                }
            }

            // фильтр по контрагентам
            if (isset($post['by']) && !empty($post['by']) && isset($post['by_id']) && $post['by_id'] > 0) {
                $url[] = $post['by'] . '=' . $post['by_id'];
            }
            // фильтр по контрагентам
            if (!isset($post['group']) || $post['group'] != 1) {
                $url[] = 'grp=1';
            }

            $hash = $post['hash'];

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . implode('&',
                        $url)) . $hash;

            Response::redirect($url);
        } elseif (isset($post['cashbox-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создание кассы
            $cashboxes_type = CASHBOX_NOT_SYSTEM;
            $avail = 1;
            $avail_in_balance = 1;
            $avail_in_orders = 1;
            $title = trim($post['title']);
            if (empty($title)) {
                FlashMessage::set(l('Название кассы не может быть пустым'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $checkByTitle = $this->all_configs['db']->query('SELECT count(*) FROM {cashboxes} WHERE name=?',
                array($title))->el();
            if (!empty($checkByTitle)) {
                FlashMessage::set(l('Касса с таким названием уже существует'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $cashbox_id = $this->Cashboxes->insert(array(
                'cashboxes_type' => $cashboxes_type,
                'avail' => $avail,
                'avail_in_balance' => $avail_in_balance,
                'avail_in_orders' => $avail_in_orders,
                'name' => $title
            ));

            if (isset($post['cashbox_currency'])) {
                foreach ($post['cashbox_currency'] as $cashbox_currency) {
                    if ($cashbox_currency > 0 && array_key_exists($cashbox_currency, $currencies)) {
                        $this->all_configs['db']->query('INSERT IGNORE INTO {cashboxes_currencies} (cashbox_id, currency, amount) VALUES (?i, ?i, ?i)',
                            array($cashbox_id, $cashbox_currency, 0));
                    }
                }
            }
            $this->History->save('add-cashbox', $mod_id, $cashbox_id);
        } elseif (isset($post['cashbox-delete']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // удаление кассы
            if (!isset($post['cashbox-id']) || $post['cashbox-id'] == 0) {
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            if ($this->cashboxHaveTransaction(array('id' => $post['cashbox-id']))) {
                FlashMessage::set(l('По кассе имеются транзакции'), FlashMessage::WARNING);
                Response::redirect($_SERVER['REQUEST_URI']);
            }

            $this->Cashboxes->delete($post['cashbox-id']);
            $this->all_configs['db']->query('DELETE FROM {cashboxes_currencies} WHERE cashbox_id=?i',
                array($post['cashbox-id']));

        } elseif (isset($post['cashbox-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование кассы
            if (!isset($post['cashbox-id']) || $post['cashbox-id'] == 0) {
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $title = trim($post['title']);
            if (empty($title)) {
                FlashMessage::set(l('Название кассы не может быть пустым'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $checkByTitle = $this->all_configs['db']->query('SELECT count(*) FROM {cashboxes} WHERE name=? AND NOT id=?i',
                array($title, $post['cashbox-id']))->el();
            if (!empty($checkByTitle)) {
                FlashMessage::set(l('Касса с таким названием уже существует'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $avail = 1;
            $avail_in_balance = 1;
            $avail_in_orders = 1;

            $ar = $this->Cashboxes->update(array(
                'avail' => $avail,
                'avail_in_balance' => $avail_in_balance,
                'avail_in_orders' => $avail_in_orders,
                'name' => $title
            ), array($this->Cashboxes->pk() => $post['cashbox-id']));

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
                $this->History->save('edit-cashbox', $mod_id, $post['cashbox-id']);
            }

        } elseif (isset($post['contractor_category-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создание категории
            $avail = isset($post['avail']) ? 1 : null;
            $parent_id = (isset($post['parent_id']) && $post['parent_id'] > 0) ? $post['parent_id'] : 0;

            $title = trim($post['title']);
            $exist = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_categories} WHERE `name`=?',
                array(
                    $title
                ))->el();
            if (empty($title)) {
                FlashMessage::set(l('Название статьи не может быть пустым'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $exist = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_categories} WHERE `name`=?',
                array(
                    $title
                ))->el();
            if ($exist) {
                FlashMessage::set(l('Статья с таким названием уже существует'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }

            $contractor_category = $this->all_configs['db']->query('INSERT INTO {contractors_categories}
                (avail, parent_id, name, code_1c, transaction_type, comment, is_system) VALUES (?n, ?i, ?, ?, ?i, ?, 0)',
                array(
                    $avail,
                    $parent_id,
                    $title,
                    trim($post['code_1c']),
                    $post['transaction_type'],
                    trim($post['comment'])
                ), 'id');

            $this->History->save('add-contractor_category', $mod_id, $contractor_category);
            if ($parent_id > 0) {
                $this->addContractorCategoryForUsers($parent_id, $contractor_category);
            }
            if (!empty($post['contractors'])) {
                $this->ContractorsCategoriesLinks->addCategoryToContractors($contractor_category, $post['contractors']);
            }

        } elseif (isset($post['contractor_category-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование категории
            if (!isset($post['contractor_category-id']) || $post['contractor_category-id'] == 0) {
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $title = trim($post['title']);
            if (empty($title)) {
                FlashMessage::set(l('Название статьи не может быть пустым'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            $exist = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_categories} WHERE `name`=? AND NOT id=?i',
                array(
                    $title,
                    $post['contractor_category-id']
                ))->el();
            if ($exist) {
                FlashMessage::set(l('Статья с таким названием уже существует'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }

            $avail = isset($post['avail']) ? 1 : null;
            $parent_id = (isset($post['parent_id']) && $post['parent_id'] > 0) ? $post['parent_id'] : 0;

            $ar = $this->all_configs['db']->query('UPDATE {contractors_categories}
                    SET avail=?n, parent_id=?i, name=?, code_1c=?, transaction_type=?i, comment=? WHERE id=?i',
                array(
                    $avail,
                    $parent_id,
                    $title,
                    trim($post['code_1c']),
                    $post['transaction_type'],
                    trim($post['comment']),
                    $post['contractor_category-id']
                ))->ar();

            if ($ar) {
                $this->History->save('edit-contractor_category', $mod_id, $post['contractor_category-id']);
            }
            if (!empty($post['contractors'])) {
                $this->ContractorsCategoriesLinks->updateCategoryToContractors($post['contractor_category-id'],
                    $post['contractors']);
            }

        } elseif (isset($post['cashboxes-currencies-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактирование валюты
            $courses = isset($post['cashbox_course']) ? $post['cashbox_course'] : '';
            if (!$courses) {
                header("Location:" . $_SERVER['REQUEST_URI']);
                exit;
            }
            foreach ($courses as $curr_id => $course) {
                if (array_key_exists($curr_id, $this->all_configs['configs']['currencies'])) {
                    $course *= 100;
                    db()->query("UPDATE {cashboxes_courses} "
                        . "SET course = ?f WHERE currency = ?i",
                        array($course, $curr_id));
                }
            }
        }

        Response::redirect($_SERVER['REQUEST_URI']);
    }

    /**
     * @param int  $type
     * @param bool $arrow
     * @return mixed
     */
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
        return $this->all_configs['db']->query('SELECT c.id, c.is_system, c.name, c.avail, c.parent_id, c.code_1c,
              c.transaction_type, c.comment ?query FROM {contractors_categories} as c ?query',
            array($query_arrow, $query))->assoc();
    }

    /**
     * @return array
     */
    function get_cashboxes_amounts()
    {
        return $this->calculateCashboxesAmount($this->Cashboxes->getCashboxes());
    }

    /**
     * @param null $avail
     * @param null $contractor_category_id
     * @param bool $operation
     * @return mixed
     */
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
                && count($this->all_configs['configs']['erp-use-id-for-accountings-operations']) > 0
            ) {

                $query_or = $this->all_configs['db']->makeQuery('k.id IN (?li) OR',
                    array($this->all_configs['configs']['erp-use-id-for-accountings-operations']));
            }

            $query = $this->all_configs['db']->makeQuery('?query AND (?query k.type IN (?li))',
                array(
                    $query,
                    $query_or,
                    array_values($this->all_configs['configs']['erp-use-for-accountings-operations'])
                ));
        }

        // достаем всех контрагентов
        return $this->all_configs['db']->query('SELECT k.id, k.title, l.contractors_categories_id, c.avail,
                  c.transaction_type, c.id as c_id, c.name as contractor_name, k.type, k.comment, k.amount
                FROM {contractors} as k
                LEFT JOIN {contractors_categories_links} as l ON l.contractors_id=k.id and l.deleted=0
                LEFT JOIN {contractors_categories} as c ON c.id=l.contractors_categories_id
                WHERE 1=1 ?query ORDER BY k.title',
            array($query))->assoc();
    }

    /**
     * @param     $contractor_category_id
     * @param int $contractor_id
     * @return string
     */
    function contractors_options($contractor_category_id, $contractor_id = 0)
    {
        $contractors = null;
        $out = '';

        if (array_key_exists('erp-use-for-accountings-operations', $this->all_configs['configs'])
            && count($this->all_configs['configs']['erp-use-for-accountings-operations']) > 0
            && $contractor_category_id > 0
        ) {

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

    /**
     *
     */
    function preload()
    {
        $this->get_cashboxes_amounts();

        $contractors = $this->get_contractors();

        if ($contractors) {
            foreach ($contractors as $contractor) {

                if (!array_key_exists($contractor['id'], $this->contractors)) {

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
                    );
                    if (!array_key_exists($contractor['transaction_type'],
                        $this->contractors[$contractor['id']]['transaction_types'])
                    ) {
                        $this->contractors[$contractor['id']]['transaction_types'][$contractor['transaction_type']] = array($contractor['contractors_categories_id']);
                    } else {
                        $this->contractors[$contractor['id']]['transaction_types'][$contractor['transaction_type']][] = $contractor['contractors_categories_id'];
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function gencontent()
    {
        $this->preload();
        return $this->view->renderFile('accountings/gencontent', array(
            'mod_submenu' => $this->mod_submenu,
            'isCashier' => $this->all_configs['oRole']->hasCashierPermission($this->getUserId())
        ));
    }

    /**
     * @param      $cashboxes_currencies
     * @param null $cashbox
     * @param int  $i
     * @return string
     */
    function form_cashbox($cashboxes_currencies, $cashbox = null, $i = 1, $wrap_accordion = true)
    {
        $currencies_html = '';
        if ($cashbox) {
            $btn = "<input type='hidden' name='cashbox-id' value='{$cashbox['id']}' />";
            $btn .= "<input type='submit' class='btn' name='cashbox-edit' value='" . l('Редактировать') . "'";
            $readonly = '';
            if (in_array($cashbox['name'], array(
                lq('Транзитная'),
                lq('Терминал'),
            ))) {
                $btn .= ' onclick="alert(\'' . l('Системная касса не подлежит редактированию') . '\'); return false"';
                $readonly = 'disabled="disabled"';
            }
            $btn .= "/>";
            $btn .= "&nbsp;<input type='submit' class='btn' name='cashbox-delete' value='" . l('Удалить') . "'";
            if ($this->cashboxHaveTransaction($cashbox)
                || in_array($cashbox['name'], array(
                    lq('Основная'),
                    lq('Транзитная'),
                    lq('Терминал'),
                ))
            ) {
                $btn .= ' onclick="alert(\'' . l('Касса задействована в транзакциях') . '\'); return false"';
            }
            $btn .= " />";
            $title = htmlspecialchars($cashbox['name']);

            foreach ($cashboxes_currencies as $currency) {
                $checked = '';

                if (array_key_exists('currencies', $cashbox) && array_key_exists($currency['currency'],
                        $cashbox['currencies'])
                ) {
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

                    if ($c_t && $c_t > 0) {
                        $checked = "checked disabled='disabled'";
                    } else {
                        $checked = "checked";
                    }

                }

                $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' {$readonly} value='{$currency['currency']}' name='cashbox_currency[]' {$checked} type='checkbox' /> {$currency['name']}</label></div>";

            }
        } else {
            foreach ($cashboxes_currencies as $currency) {
                if (array_key_exists($currency['id'],
                        $this->all_configs['suppliers_orders']->currencies) && $this->all_configs['suppliers_orders']->currencies[$currency['id']]['currency-name'] == $this->all_configs['configs']['default-currency']
                ) {
                    $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' checked value='{$currency['currency']}' name='cashbox_currency[]' type='checkbox' /> {$currency['name']}</label></div>";
                } else {
                    $currencies_html .= "<div class='checkbox'><label><input class='checkbox-cashbox-currency' value='{$currency['currency']}' name='cashbox_currency[]' type='checkbox' /> {$currency['name']}</label></div>";
                }
            }
            $btn = "<input type='submit' class='btn btn-success' name='cashbox-add' value='" . l('Создать') . "' />";
            $title = '';
        }

        if ($i == 1) {
            $in = 'in';
            $accordion_title = l('Создать кассу');
        } else {
            $in = '';
            $accordion_title = l('Редактировать кассу') . " '{$title}'";
        }

        if (in_array($cashbox['name'], array(
            lq('Основная'),
            lq('Транзитная'),
            lq('Терминал'),
        ))
        ) {
            $readonly = 'readonly';
        }
        $cashbox_form = "
            <form method='POST' style='max-width:300px'>
                <div class='form-group'><label>" . l('Название') . ": </label>
                    <input placeholder='" . l('введите название кассы') . "' class='form-control' name='title' value='{$title}' {$readonly} />
                </div>
                <div class='form-group'>
                    <label>" . l('Используемые валюты') . ": " . InfoPopover::getInstance()->createQuestion('l_cashbox_currencies_info') . "</label>
                    {$currencies_html}
                </div>
                <div class='form-group'>{$btn}</div>
            </form>
        ";
        if ($wrap_accordion) {
            return "
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_cashboxes' href='#collapse_cashbox_{$i}'>{$accordion_title}</a>
                    </div>
                    <div id='collapse_cashbox_{$i}' class='panel-collapse collapse {$in}'>
                        <div class='panel-body'>
                            
                                " . $cashbox_form . "
                            </form>
                        </div>
                    </div>
                </div>
            ";
        } else {
            return $cashbox_form;
        }
    }

    /**
     * @param null $contractor
     * @param null $opened
     * @param bool $wrap_form
     * @return string
     */
    function form_contractor($contractor = null, $opened = null, $wrap_form = false)
    {
        $categories = $this->get_contractors_categories();
        if (count($categories) == 0) {
            return '<p class="text-error">' . l('Сперва нужно добавить статью.') . '</p>';
        }

        $out = $name = $comment = '';
        if ($contractor) {
            $name = htmlspecialchars($contractor['name']);
            $comment = htmlspecialchars($contractor['comment']);
            $out .= '<div class="panel panel-default"><div class="panel-heading">';
            $out .= '<a class="accordion-toggle" data-parent="#accordion_contractors" href="?ct=';
            $out .= $opened == $contractor['id'] ? '' : $contractor['id'];
            $out .= '#settings-contractors">' . $name . '</a></div>';
            $out .= '<div id="collapse_contractor_' . $contractor['id'] . '" class="panel-collapse collapse ';
            $out .= $contractor && $opened == $contractor['id'] ? 'in' : '';
            $out .= '"><div class="panel-body">';
        }
        if (($contractor && $opened == $contractor['id']) || !$contractor) {
            $out .= (!$wrap_form ? '<form method="POST" class="form_contractor ">' : '') . '<div class="form-group">';
            $out .= '</div><div class="form-group"><label class="control-label">' . l('Тип контрагента') . ': ' . InfoPopover::getInstance()->createQuestion('l_contragent_type_info') . '</label>';
            $out .= '<select id="contractor_type_select" class="form-control" name="type"><option value=""></option>';
            foreach ($this->all_configs['configs']['erp-contractors-types'] as $c_id => $c_name) {
                $sel = '';
                if ($contractor && $c_id == $contractor['type']) {
                    $sel = ' selected="selected"';
                }
                $cats_1 = $this->all_configs['configs']['erp-contractors-type-categories'][$c_id][1];
                $cats_2 = $this->all_configs['configs']['erp-contractors-type-categories'][$c_id][2];
                $out .= '<option' . $sel . ' data-categories_1="[' . implode(',', $cats_1) . ']" '
                    . 'data-categories_2="[' . implode(',',
                        $cats_2) . ']" value="' . $c_id . '">' . $c_name . '</option>';
            }
            $out .= '</select></div>';
            $out .= '<label>' . l('Укажите статьи расходов для контрагента') . ' <small>(' . l('за что мы платим контрагенту') . ')</small>: </label>';
            $out .= '<div id="add_category_to_' . ($contractor ? $contractor['id'] : 0) . '">';
            $out .= '<select class="multiselect input-small" data-type="categories_1" multiple="multiple" name="contractor_categories_id[]">';
            $categories = $this->get_contractors_categories(1);
            if ($contractor) {
                $out .= build_array_tree($categories, array_keys($contractor['contractors_categories_ids']));
            } else {
                $out .= build_array_tree($categories);
            }
            $out .= '</select></div><div class="form-group">';
            $out .= '<label>' . l('Укажите статьи приходов для контрагента') . ' <small>(' . l('за что контрагент нам платит') . ')</small>: </label>';
            $out .= '<div id="add_category_to_' . ($contractor ? $contractor['id'] : 0) . '">';
            $out .= '<select class="multiselect input-small" data-type="categories_2" multiple="multiple" name="contractor_categories_id[]">';
            $categories = $this->get_contractors_categories(2);
            if ($contractor) {
                $out .= build_array_tree($categories, array_keys($contractor['contractors_categories_ids']));
            } else {
                $out .= build_array_tree($categories);
            }
            $out .= '</select></div></div>';
            $out .= '<div class="form-group"><label>' . l('ФИО') . ': </label>';
            $out .= '<input placeholder="' . l('введите ФИО контрагента') . '" class="input-contractor form-control" name="title" value="' . $name . '" />';
            $out .= '</div><div class="form-group"><label>' . l('Комментарий') . ': </label>';
            $out .= '<textarea class="form-control" name="comment" placeholder="' . l('введите комментарий к контрагенту') . '">' . $comment . '</textarea>';
            $out .= '';
            if ($contractor) {
                if ($contractor['comment'] == 'system') {
                    // системного низя менять
                    $out .= "
                        <div class='form-group'>
                            <p class='text-info'>" . l('Технический контрагент - не подлежит редактированию') . "</p>
                        </div>
                    ";
                } else {
                    if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                        $out .= "
                            <div class='form-group'>
                                <input type='hidden' name='contractor-id' value='{$contractor['id']}' />
                                <input type='button' class='btn btn-primary' onclick='contractor_edit(this, \"{$contractor['id']}\")' value='" . l('Редактировать') . "' />
                                <input type='button' onclick='contractor_remove(this, \"{$contractor['id']}\")' class='btn btn-danger contractor-remove' value='" . l('Удалить') . "' />
                            </div>
                        ";
                    }
                }
                $client_contr = $this->all_configs['db']->query("SELECT id FROM {clients} "
                    . "WHERE contractor_id = ?i", array($contractor['id']), 'el');
                $out .= "
                    <div class='form-group'>
                        <label>" . l('Клиент') . ":</label> " .
                    ($client_contr ?
                        '<a href="' . $this->all_configs['prefix'] . 'clients/create/' . $client_contr . '">' .
                        $client_contr .
                        '</a>' :
                        '<span class="text-danger">' . l('Не привязан') . '</span>')
                    . "
                    </div></div>
                ";
            } else {
                $out .= '
                    <div class="form-group">
                        <label>' . l('Телефон') . '</label>
                        <input ' . input_phone_mask_attr() . 'type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>' . l('Эл. адрес') . '</label>
                        <input type="text" name="email" class="form-control">
                    </div>
                    <script>
                    jQuery(document).ready(function(){init_input_masks();})
                    </script>
                ';
            }
            if (!$wrap_form) {
                $out .= '</form>';
            }
        }
        if ($contractor) {
            $out .= '</div></div></div>';
        }

        return $out;
    }

    /**
     * @param null $contractor_category
     * @return string
     */
    function form_contractor_category_btn($contractor_category = null)
    {
        $btn = '';

        if ($contractor_category) {
            if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $btn .= "<input type='hidden' name='contractor_category-id' value='{$contractor_category['id']}' />";
                $btn .= "<input type='button' class='btn' onclick='$(\"form.form_contractor_category\").submit();' value='" . l('Редактировать') . "' />";
                if (!$contractor_category['is_system']) {
                    $btn .= "<input type='button' onclick='contractor_category_remove(this, \"{$contractor_category['id']}\")' class='btn btn-danger contractor_category-remove' value='" . l('Удалить') . "' />";
                }
            }
        } else {
            $btn .= "<input type='button' class='btn' onclick='$(\"form.form_contractor_category\").submit();' value='" . l('Создать') . "' />";
        }

        return $btn;
    }

    /**
     * @param      $type
     * @param null $contractor_category
     * @return string
     */
    function form_contractor_category($type, $contractor_category = null)
    {
        $categories = $this->get_contractors_categories($type);
        $contractors = db()->query('SELECT id, title FROM {contractors}')->assoc('id');
        if ($contractor_category) {
            $contractors_category_links = db()->query("SELECT contractors_id FROM {contractors_categories_links} WHERE contractors_categories_id=?i and deleted=0",
                array($contractor_category['id']))->col();
        } else {
            $contractors_category_links = array();
        }
        return $this->view->renderFile('accountings/form_contractor_category', array(
            'categories' => $categories,
            'contractor_category' => $contractor_category,
            'type' => $type,
            'contractors' => $contractors,
            'contractors_category_links' => $contractors_category_links
        ));
    }

    /**
     * @return mixed
     */
    function cashboxes_courses()
    {
        // валюты
        return $this->all_configs['db']->query('SELECT id, currency, name, short_name, course
            FROM {cashboxes_courses}')->assoc('currency');
    }

    /**
     * @param $cashbox_id
     * @return string
     */
    function get_cashbox_currencies($cashbox_id)
    {
        $currencies_html = '';
        $_currencies = $this->all_configs['db']->query('SELECT cu.currency, co.short_name
                FROM {cashboxes_currencies} cu, {cashboxes_courses} co WHERE cu.cashbox_id=?i AND cu.currency=co.currency',
            array($cashbox_id))->vars();

        if ($_currencies) {
            foreach ($_currencies as $id => $name) {
                $currencies_html .= '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
            }
        }

        return $currencies_html;
    }

    /**
     *
     */
    public function export()
    {
        $array = array();
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // допустимые валюты
        $currencies = $this->all_configs['suppliers_orders']->currencies;

        if ($act == 'contractors_transactions') {
            $array = $this->Transactions->get_transactions($currencies, false, null, true, array(),
                true, true);
        }
        if ($act == 'cashboxes_transactions') {
            $array = $this->Transactions->get_transactions($currencies, false, null, false, array(),
                true, true);
        }
        if ($act == 'reports-turnover') {
            $array = $this->accountings_reports_turnover_array();
        }

        include_once $this->all_configs['sitepath'] . 'shop/exports.class.php';
        $exports = new Exports();
        $exports->build($array);
        return '';
    }

    /**
     *
     */
    public function ajax()
    {
        $data = array(
            'state' => false,
            'message' => l('Не известная ошибка')
        );
        $user_id = $this->getUserId();
        $mod_id = $this->all_configs['configs']['accountings-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        $this->preload();

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
                    $return = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
                    );
                    if (isset($function['menu'])) {
                        $return['menu'] = $function['menu'];
                    }
                } else {
                    $return = array('message' => l('Не найдено'), 'state' => false);
                }
                Response::json($return);
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
        }

        // сумма по транзакциям у контрагента
        if ($act == 'contractor-amount') {
            if (isset($_POST['contractor_id']) && $_POST['contractor_id'] > 0) {
                $amount = $this->all_configs['db']->query('SELECT
                        SUM(IF(t.transaction_type=?i, t.value_to, 0)) - SUM(IF(t.transaction_type=?i, t.value_from, 0))
                      FROM {contractors_transactions} as t, {contractors_categories_links} as l 
                      WHERE l.contractors_id=?i AND t.contractor_category_link=l.id',
                    array(TRANSACTION_INPUT, TRANSACTION_OUTPUT, $_POST['contractor_id']))->el();
                $data['message'] = show_price(1 * $amount);
                $data['state'] = true;
            }
        }

        // добавление нового контрагента
        if ($act == 'contractor-create') {
            $data = $this->contractorCreate($data, $user_id, $mod_id);
        }
        // редактирование контрагента
        if ($act == 'contractor-edit') {
            $data = $this->contractorEdit($data, $user_id, $mod_id);
        }

        // форма создания транзакции
        if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3'
            || $act == 'begin-transaction-1-co' || $act == 'begin-transaction-2-co'
            || $act == 'begin-transaction-1-so' || $act == 'begin-transaction-2-so'
        ) {
            $data = $this->createTransactionForm($data, $user_id, $act);
        }

        // форма оплаты за ремонт
        if ($act == 'begin-transaction-repair' || $act == 'begin-transaction-repair-co') {
            $data = $this->createPayForm('repair', $data, $user_id);
        }
        // форма оплаты за продажу
        if ($act == 'begin-transaction-sale' || $act == 'begin-transaction-sale-co') {
            $data = $this->createPayForm('sale', $data, $user_id);
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
        if ($act == 'create-contractor-form') {
            $data['state'] = true;
            $data['content'] = $this->form_contractor();
            $data['functions'] = array('reset_multiselect()');
            $data['btns'] = "<input type='button' class='btn btn-success' onclick='contractor_create(this" . (isset($_POST['callback']) ? ', ' . htmlspecialchars($_POST['callback']) : '') . ")' value='" . l('Создать') . "' />";
        }

        // форма создания кассы
        if ($act == 'create-cashbox') {
            $data['state'] = true;
            $data['content'] = $this->form_cashbox($this->cashboxes_courses(), null, 1, false);
            $data['functions'] = array('reset_multiselect()');
            $data['btns'] = false;
        }

        if ($act == 'create-contractor-form-no-modal') {
            $data['state'] = true;
            $data['html'] =
                $this->form_contractor(null, null, true)
                . "<input type='button' class='btn btn-success' onclick='contractor_create(this,new_quick_create_supplier_callback)' value='" . l('Создать') . "' />"
                . '&nbsp;<button type="button" class="btn btn-default hide_typeahead_add_form">' . l('Отмена') . '</button>';
        }

        // Кредит Отказ
        if ($act == 'accounting-credit-denied') {
            if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
                Response::json(array('message' => l('Кредит уже отказан'), 'error' => true));
            }
            $order = $this->all_configs['db']->query('SELECT id, status FROM {orders} WHERE id=?i',
                array($_POST['order_id']))->row();

            if (!$order || $order['status'] != $this->all_configs['configs']['order-status-loan-wait']) {
                Response::json(array('message' => l('Кредит уже отказан'), 'error' => true));
            }

            $this->all_configs['db']->query('UPDATE {orders} SET status=?i WHERE id=?i',
                array($this->all_configs['configs']['order-status-loan-denied'], $_POST['order_id']));

            Response::json(array('message' => l('Успешно')));
        }

        // Кредит одобрен, документы готовы
        if ($act == 'accounting-credit-approved') {
            if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
                Response::json(array('message' => l('Кредит уже одобрен'), 'error' => true));
            }
            $order = $this->all_configs['db']->query('SELECT id, status FROM {orders} WHERE id=?i',
                array($_POST['order_id']))->row();

            if (!$order || $order['status'] != $this->all_configs['configs']['order-status-loan-wait']) {
                Response::json(array('message' => l('Кредит уже одобрен'), 'error' => true));
            }

            $this->all_configs['db']->query('UPDATE {orders} SET status=?i WHERE id=?i',
                array($this->all_configs['configs']['order-status-loan-approved'], $_POST['order_id']));

            Response::json(array('message' => l('Успешно')));
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
            $data['currencies'] = $this->get_cashbox_currencies(array_key_exists('cashbox_id',
                $_POST) ? $_POST['cashbox_id'] : 0);
        }

        // добавление валюты
        if ($act == 'add-currency') {

            if (!isset($_POST['currency_id']) || $_POST['currency_id'] == 0 || !array_key_exists($_POST['currency_id'],
                    $this->all_configs['suppliers_orders']->currencies)
            ) {
                $data['msg'] = l('Нет такой валюты');
            } else {

                $name = $this->all_configs['suppliers_orders']->currencies[$_POST['currency_id']]['name'];
                $short_name = $this->all_configs['suppliers_orders']->currencies[$_POST['currency_id']]['shortName'];
                $course = $course = $this->course_default;

                $ar = $this->all_configs['db']->query('INSERT IGNORE INTO {cashboxes_courses} (currency, name, short_name, course) VALUES (?i, ?, ?, ?i)',
                    array($_POST['currency_id'], $name, $short_name, $course))->ar();

                // история
                if ($ar) {
                    $this->History->save('add-to-cashbox-currency', $mod_id, $_POST['currency_id']);
                }

                $courses_html = $this->gen_currency_table();
                $add_course_html = $this->gen_new_currency_options(false);

                $data['add'] = $add_course_html;
                $data['show'] = $courses_html;
                $data['state'] = true;
            }
        }

        // удаление валюты
        if ($act == 'remove-currency') {
            $check = true;
            if (!isset($_POST['currency_id']) || $_POST['currency_id'] == 0) {
                $data['msg'] = l('Нет такой валюты');
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
                    $data['msg'] = l('С данной валютой у вас уже имеются операции');
                }
            }

            if ($check == true) {
                $this->all_configs['db']->query('DELETE FROM {cashboxes_currencies} WHERE currency=?i',
                    array($_POST['currency_id']));
                $ar = $this->all_configs['db']->query('DELETE FROM {cashboxes_courses} WHERE currency=?i',
                    array($_POST['currency_id']))->ar();
                $data['state'] = true;

                // история
                if ($ar) {
                    $this->History->save('remove-global-cashbox-course', $mod_id, $_POST['currency_id']);
                }

                $new_courses = $this->all_configs['suppliers_orders']->currencies;
                // валюты
                $cashboxes_currencies = $this->cashboxes_courses();

                foreach ($cashboxes_currencies as $cashbox_currency) {
                    if (array_key_exists($cashbox_currency['currency'], $new_courses)) {
                        unset($new_courses[$cashbox_currency['currency']]);
                    }
                }

                // добавить валюту
                $add_course_html = '<option value="">' . l('Не выбрана валюта') . '...</option>';
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
            if (isset($_POST['transaction_type']) && $_POST['transaction_type'] == TRANSACTION_TRANSFER) {
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
                    && isset($_POST['cashbox_course_from']) && isset($_POST['cashbox_course_to'])
                ) {
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
                $data['msg'] = l('Такого контрагента не существует');
            } else {
                $data['state'] = true;
                $is_system = $this->all_configs['db']->query("SELECT id FROM {contractors} "
                    . "WHERE id = ?i AND comment = 'system'", array($_POST['contractor_id']), 'el');
                if ($is_system) {
                    $data['state'] = false;
                    $data['msg'] = l('Системный контрагент - не подлежит редактированию');
                } else {
                    $count_t = $count_i = $count_o = 0;
                    // количество транзакций
                    if (array_key_exists('erp-use-for-accountings-operations', $this->all_configs['configs'])
                        && count($this->all_configs['configs']['erp-use-for-accountings-operations']) > 0
                    ) {
                        $query = '';
                        if (array_key_exists('erp-use-id-for-accountings-operations', $this->all_configs['configs'])
                            && count($this->all_configs['configs']['erp-use-id-for-accountings-operations']) > 0
                        ) {
                            $query = $this->all_configs['db']->makeQuery('c.id IN (?li) OR',
                                array($this->all_configs['configs']['erp-use-id-for-accountings-operations']));
                        }
                        $count_t = $this->all_configs['db']->query('SELECT count(t.id)
                                FROM {cashboxes_transactions} as t, {contractors_categories_links} as l, {contractors} as c
                                WHERE t.contractor_category_link=l.id AND l.contractors_id=c.id AND c.id=?i
                                  AND (?query c.type IN (?li))',
                            array(
                                $_POST['contractor_id'],
                                $query,
                                array_values($this->all_configs['configs']['erp-use-for-accountings-operations'])
                            ))->el();
                        $count_i = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses_goods_items} WHERE supplier_id=?i',
                            array($_POST['contractor_id']))->el();
                        $count_o = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_suppliers_orders} WHERE supplier=?i',
                            array($_POST['contractor_id']))->el();
                    }

                    if ($count_t > 0 || $count_i > 0 || $count_o > 0) {
                        $data['state'] = false;
                        $data['msg'] = l('Контрагент содержит операции, его нельзя удалить');
                    } else {
                        $ar = $this->all_configs['db']->query('DELETE FROM {contractors} WHERE id=?i',
                            array($_POST['contractor_id']))->ar();

                        // история
                        if ($ar) {
                            $this->History->save('remove-contractor', $mod_id, $_POST['contractor_id']);
                        }

                        $data['state'] = true;
                    }
                }
            }
        }

        // удаляем категорию
        if ($act == 'remove-category') {
            if (!isset($_POST['category_id']) || $_POST['category_id'] == 0) {
                $data['msg'] = l('Такой статьи не существует');
            } else {
                // количество подкатегорий
                $count_c = $this->all_configs['db']->query('SELECT count(id) FROM {contractors_categories} WHERE parent_id=?i',
                    array($_POST['category_id']))->el();
                // количество транзакций
                $count_t = $this->all_configs['db']->query('SELECT count(t.id) FROM {cashboxes_transactions} as t, {contractors_categories_links} as l, {contractors_categories} as c
                    WHERE t.contractor_category_link=l.id AND l.contractors_categories_id=c.id AND c.id=?i',
                    array($_POST['category_id']))->el();

                if ($count_c > 0 || $count_t > 0) {
                    if ($count_c > 0) {
                        $data['msg'] = l('Статья содержит подстатьи, чтобы удалить перенесите их в другие статьи');
                    }
                    if ($count_t > 0) {
                        $data['msg'] = l('Статья содержит операции, ее нельзя удалить');
                    }
                } else {
                    $ar = $this->all_configs['db']->query('DELETE FROM {contractors_categories} WHERE id=?i',
                        array($_POST['category_id']))->ar();

                    // история
                    if ($ar) {
                        $this->History->save('remove-contractors-category', $mod_id, $_POST['category_id']);
                    }

                    $data['state'] = true;
                }
            }
        }

        // создаем транзакцию
        if ($act == 'create-transaction') {
            $data = $this->all_configs['chains']->create_transaction($_POST, $mod_id);
        }

        // создаем транзакцию оплаты за ремонт
        if ($act == 'create-transaction-repair') {
            $_POST['cashbox_from'] = $_POST['cashbox_to'];
            $_POST['amount_from'] = 0;
            $_POST['cashbox_currencies_from'] = $_POST['cashbox_currencies_to'];
            list($co_id, $b_id, $t_extra, $amount_to, $order) = $this->getInfoForPayForm();

            if ($amount_to < $_POST['amount_to']) {
                $data = array(
                    'state' => false,
                    'msg' => l('Сумма платежа больше суммы задолженности')
                );
                Response::json($data);
            }
            if (!empty($_POST['discount'])) {
                $discount = $_POST['amount_without_discount'] - $_POST['amount_to'];
                $this->Orders->increase('discount', $discount * 100, array(
                    'id' => $_POST['client_order_id']
                ));
                $this->History->save('change-orders-discount', $mod_id, $_POST['client_order_id'],
                    l('Сделана скидка на сумму') . ':' . $_POST['discount'] . viewCurrency());
            }
            $data = $this->all_configs['chains']->create_transaction($_POST, $mod_id);
            if ($data['state'] && !empty($_POST['issued'])) {
                $order = $this->Orders->getByPk($_POST['client_order_id']);
                $_POST['status'] = $this->all_configs['configs']['order-status-issued'];
                if (!empty($order) && $order['status'] != $_POST['status']) {
                    $this->changeOrderStatus($order, array('state' => true), l('Статус не изменился'));
                }
            }
        }

        // создаем транзакцию оплаты за продажу
        if ($act == 'create-transaction-sale') {
            list($co_id, $b_id, $t_extra, $amount_to, $order) = $this->getInfoForPayForm();
            $_POST['cashbox_from'] = $_POST['cashbox_to'];
            $_POST['amount_from'] = 0;
            $_POST['cashbox_currencies_from'] = $_POST['cashbox_currencies_to'];
            $data = array(
                'state' => true
            );
            if ($amount_to > 0) {
                if ($amount_to < $_POST['amount_to']) {
                    $data = array(
                        'state' => false,
                        'msg' => l('Сумма платежа больше суммы задолженности')
                    );
                    Response::json($data);
                }
                $data = $this->all_configs['chains']->create_transaction($_POST, $mod_id);
            }
            if ($data['state'] && !empty($_POST['issued'])) {
                $order = $this->Orders->getByPk($_POST['client_order_id']);
                $_POST['status'] = $this->all_configs['configs']['order-status-issued'];
                if (!empty($order) && $order['status'] != $_POST['status']) {
                    $data = $this->changeOrderStatus($order, $data, l('Статус не изменился'));
                }
            }
        }
        Response::json($data);
    }

    /**
     * @param null $year
     * @return string
     */
    function month_select($year = null)
    {
        $cur_year = date('Y', time());
        $cur_month = (isset($_GET['df']) && !empty($_GET['df'])) ? date('m', strtotime($_GET['df'])) : date('m',
            time());


        return $this->view->renderFile('accountings/month_select', array(
            'months' => $this->months,
            'currentYear' => ($year == null) ? $cur_year : $year,
            'year' => $year,
            'cur_month' => $cur_month,
            'cur_year' => $cur_year
        ));
    }

    /**
     * @param bool $contractors
     * @return string
     */
    function transaction_filters($contractors = false)
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : date('01.m.Y', time())) . ' - ' .
            (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : date('t.m.Y', time()));
        $value = (isset($_GET['o_id']) && $_GET['o_id'] > 0) ? $_GET['o_id'] : ((isset($_GET['s_id']) && $_GET['s_id'] >
            0) ? $_GET['s_id'] : ((isset($_GET['t_id']) && $_GET['t_id'] > 0) ? $_GET['t_id'] : ''));
        $in = 'in';
        if (!isset($_GET['cb']) && !isset($_GET['cg']) && !isset($_GET['o_id']) && !isset($_GET['s_id']) && !isset($_GET['t_id'])) {
            $in = '';
        }

        return $this->view->renderFile('accountings/transaction_filters', array(
            'date' => $date,
            'month_select' => $this->month_select(),
            'categories' => $this->get_contractors_categories(),
            'contractors' => $this->contractors,
            'cashboxes' => $this->getCashboxes($this->getUserId()),
            'isContractors' => $contractors,
            'value' => $value,
            'in' => $in
        ));
    }

    /**
     * @param $cashbox
     * @return bool
     */
    public function cashboxAvailable($cashbox)
    {
        return !($cashbox['avail'] != 1
            || $cashbox['id'] == $this->all_configs['configs']['erp-cashbox-transaction']
            || ($cashbox['id'] == $this->all_configs['configs']['erp-so-cashbox-terminal']
                && !$this->all_configs['configs']['manage-show-terminal-cashbox']));
    }

    /**
     * @return array
     */
    function accountings_cashboxes()
    {
        $out = '';
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $isCashier = $this->all_configs['oRole']->hasCashierPermission($user_id);
        if ($isCashier) {
            $amounts = $this->get_cashboxes_amounts();
            // день
            $day = date("d.m.Y", time());
            $day_html = $day;
            if (isset($_GET['d']) && !empty($_GET['d'])) {
                $days = explode('-', $_GET['d']);
                $day_html = urldecode(trim($_GET['d']));
                $day = trim($days[0]);
            }

            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            // сумма по кассам если дата не сегодня
            $amounts_by_day = $this->all_configs['db']->query('SELECT a.amount, a.cashboxes_currency_id, c.course
                    FROM {cashboxes_amount_by_day} as a, {cashboxes_courses} as c
                    WHERE DATE_FORMAT(a.date_add, "%d.%m.%Y")=? AND c.currency=a.cashboxes_currency_id',
                array($day))->assoc();
            $total_cashboxes = $this->total_cashboxes($amounts);
            $cashboxes = $this->getCashboxes($user_id);

            $all_amount = 0;
            $out_amounts = '';
            if ($amounts_by_day) {
                foreach ($amounts_by_day as $amount_by_day) {
                    if (array_key_exists($amount_by_day['cashboxes_currency_id'], $currencies)) {
                        $all_amount += $amount_by_day['amount'] * ($amount_by_day['course'] / 100);
                        $out_amounts .= show_price($amount_by_day['amount']) . ' ' . ($currencies[$amount_by_day['cashboxes_currency_id']]['shortName']) . '  ';
                    }
                }
            }

            $cashboxes_cur = array();
            $used_currencies = array();
            if (count($cashboxes) > 0) {
                foreach ($cashboxes as $cashbox) {
                    if ($this->cashboxAvailable($cashbox)) {
                        $cashboxes_cur[$cashbox['id']] = array();
                        if (array_key_exists('currencies', $cashbox)) {
                            ksort($cashbox['currencies']);
                            foreach ($cashbox['currencies'] as $cur_id => $currency) {
                                $used_currencies[] = $cur_id;
                                $name = show_price($currency['amount']) . ' <span>' . htmlspecialchars(l($currency['short_name'])) . '</span>';
                                $cashboxes_cur[$cashbox['id']][$cur_id] = $name;
                            }
                        }
                    }
                }
            }
            $out = $this->view->renderFile('accountings/accountings_cashboxes', array(
                'day_html' => $day_html,
                'cashboxes' => $cashboxes,
                'amounts_by_day' => $amounts_by_day,
                'total_cashboxes' => $total_cashboxes,
                'currencies' => $currencies,
                'out_amounts' => $out_amounts,
                'all_amount' => $all_amount,
                'controller' => $this,
                'cashboxes_cur' => $cashboxes_cur,
                'prefix' => $this->all_configs['prefix'],
                'used_currencies' => array_unique($used_currencies)
            ));
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @param $amounts
     * @return array
     */
    function total_cashboxes($amounts)
    {
        $out = '';
        $sum = array();

        if (array_key_exists('cashboxes',
                $amounts) && is_array($amounts['cashboxes']) && count($amounts['cashboxes']) > 0
        ) {

            usort($amounts['cashboxes'], array('accountings', 'akcsort'));

            foreach ($amounts['cashboxes'] as $amount) {
                if ($amount['amount'] != 0) {
                    $out .= empty($out) ? '' : ', ';
                    $out .= show_price($amount['amount'], 2, ' ') . ' ' . htmlspecialchars(l($amount['short_name']));
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

    /**
     * @param $a
     * @param $b
     * @return mixed
     */
    function akcsort($a, $b)
    {
        return $b['currency'] - $a['currency'];
    }

    /**
     * @param $hash
     * @return array
     */
    public function accountings_transactions($hash)
    {
        if (trim($hash) == '#transactions' || (trim($hash) != '#transactions-cashboxes' && trim($hash) != '#transactions-contractors')) {
            $hash = '#transactions-cashboxes';
        }
        if (!$this->all_configs['oRole']->hasPrivilege('accounting')) {
            $hash = '#transactions-contractors';
        }

        return array(
            'html' => $this->view->renderFile('accountings/accountings_transactions', array(
                'isCashier' => $this->all_configs['oRole']->hasCashierPermission($this->getUserId())
            )),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
            'hash' => $hash
        );
    }

    /**
     * @return array
     */
    function accountings_transactions_cashboxes()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasCashierPermission($this->getUserId())) {
            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            // фильтры
            $out = $this->transaction_filters();
            // списсок транзакций
            $out .= $this->Transactions->get_transactions($currencies);
        }

        return array(
            'html' => $out,
            'functions' => array("reset_multiselect()"),
        );
    }

    /**
     * @return array
     */
    function accountings_transactions_contractors()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasCashierPermission($this->getUserId()) ||
            $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
        ) {

            // допустимые валюты
            $currencies = $this->all_configs['suppliers_orders']->currencies;

            // фильтры
            $contractor_html = '';
            if (isset($_GET['ct'])) {
                $cn = explode(',', $_GET['ct']);
                if (count($cn) == 1 && array_key_exists(0, $cn)) {
                    $contractor = $this->all_configs['db']->query('
                        SELECT ct.title, ct.amount, ct.type, cc.name 
                        FROM {contractors} as ct
                        LEFT JOIN (SELECT contractors_id, contractors_categories_id FROM {contractors_categories_links})l ON l.contractors_id=ct.id
                        LEFT JOIN (SELECT name, id FROM {contractors_categories})cc ON cc.id=l.contractors_categories_id
                        WHERE ct.id=?i', array($cn[0]))->assoc();
                    if ($contractor) {
                        // выводим инфу контрагента
                        $contractor_html = '<h4 class="well">' . $contractor[0]['title'] . ', ' . show_price($contractor[0]['amount']) . '$';
                        $contractor_html .= (array_key_exists($contractor[0]['type'],
                                $this->all_configs['configs']['erp-contractors-types']) ? '<br />' . $this->all_configs['configs']['erp-contractors-types'][$contractor[0]['type']] : '') . '<br />';
                        foreach ($contractor as $k => $contractor_category) {
                            $contractor_html .= $contractor_category['name'];
                            if (($k + 1) < count($contractor)) {
                                $contractor_html .= ', ';
                            }
                        }
                        $contractor_html .= '</h4>';
                    }
                }
            }
            $out .= $this->transaction_filters(true);
            $out .= $contractor_html;
            // списсок транзакций
            $out .= $this->Transactions->get_transactions($currencies, false, null, true);
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function accountings_reports($hash = '#reports-turnover')
    {
        if (trim($hash) == '#reports' || (trim($hash) != '#reports-cash_flow'
                && trim($hash) != '#reports-annual_balance' && trim($hash) != '#reports-cost_of'
                && trim($hash) != '#reports-turnover' && trim($hash) != '#reports-net_profit')
        ) {
            $hash = '#reports-turnover';
        }

        if (!$this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $hash = '#reports-turnover';
        }

        return array(
            'html' => $this->view->renderFile('accountings/accountings_reports', array()),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
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
                  AND YEAR(t.date_transaction)<=?i GROUP BY cc.id', array(TRANSACTION_INPUT, $year))->vars();
            // расходы
            $contractors_categories_exp = $this->all_configs['db']->query('SELECT cc.id, cc.name
                FROM {contractors_categories} as cc, {contractors_categories_links} as l, {cashboxes_transactions} as t
                WHERE t.contractor_category_link=l.id AND l.contractors_categories_id=cc.id AND t.transaction_type=?i
                  AND YEAR(t.date_transaction)<=?i GROUP BY cc.id', array(TRANSACTION_OUTPUT, $year))->vars();

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
                        $cumulative_total[$t['cr']] = (array_key_exists($t['cr'],
                                $cumulative_total) ? $cumulative_total[$t['cr']] : 0) + ($t['inc'] - $t['exp']);
                    }

                    // текущий год
                    if ($y == $year) {
                        // доходы
                        if ($t['tt'] == 2) {
                            $m_inc[$m][$t['cr']] = (array_key_exists($m, $m_inc) && array_key_exists($t['cr'],
                                    $m_inc[$m])
                                    ? $m_inc[$m][$t['cr']] : 0) + $t['inc'];
                            $total[$m][$t['cr']] = (array_key_exists($m, $total) && array_key_exists($t['cr'],
                                    $total[$m])
                                    ? $total[$m][$t['cr']] : 0) + $t['inc'];
                            $inc_ct[$t['cid']][$m][$t['cr']] = (array_key_exists($t['cid'], $inc_ct)
                                && array_key_exists($m, $inc_ct[$t['cid']]) && array_key_exists($t['cr'],
                                    $inc_ct[$t['cid']][$m])
                                    ? $inc_ct[$t['cid']][$m][$t['cr']] : 0) + $t['inc'];
                            $total_inc[$t['cr']] = (array_key_exists($t['cr'],
                                    $total_inc) ? $total_inc[$t['cr']] : 0) + $t['inc'];
                            $total_total[$t['cr']] = (array_key_exists($t['cr'],
                                    $total_total) ? $total_total[$t['cr']] : 0) + $t['inc'];
                        }
                        // расходы
                        if ($t['tt'] == 1) {
                            $m_exp[$m][$t['cr']] = (array_key_exists($m, $m_exp) && array_key_exists($t['cr'],
                                    $m_exp[$m])
                                    ? $m_exp[$m][$t['cr']] : 0) + $t['exp'];
                            $total[$m][$t['cr']] = (array_key_exists($m, $total) && array_key_exists($t['cr'],
                                    $total[$m])
                                    ? $total[$m][$t['cr']] : 0) - $t['exp'];
                            $exp_ct[$t['cid']][$m][$t['cr']] = (array_key_exists($t['cid'], $exp_ct)
                                && array_key_exists($m, $exp_ct[$t['cid']]) && array_key_exists($t['cr'],
                                    $exp_ct[$t['cid']][$m])
                                    ? $exp_ct[$t['cid']][$m][$t['cr']] : 0) + $t['exp'];
                            $total_exp[$t['cr']] = (array_key_exists($t['cr'],
                                    $total_exp) ? $total_exp[$t['cr']] : 0) + $t['exp'];
                            $total_total[$t['cr']] = (array_key_exists($t['cr'],
                                    $total_total) ? $total_total[$t['cr']] : 0) - $t['exp'];
                        }
                    }
                }
            }

            $out .= $this->accountings_year_filter($year, '#reports-cash_flow', $years);
            $out .= '<div class="table-responsive"><table class="table table-bordered table-reports table-condensed"><thead><tr><td></td>';
            $out_inc = '<tr class="well"><td><a href="" onclick="toggle_report_cashflow(this, event, \'inc\')" class="none-decoration">';
            $out_inc .= '<i class="glyphicon glyphicon-chevron-down"></i></a>' . l('Доходы') . '</td>';
            $out_exp = '<tr class="well"><td><a href="" onclick="toggle_report_cashflow(this, event, \'exp\')" class="none-decoration">';
            $out_exp .= '<i class="glyphicon glyphicon-chevron-down"></i></a>' . l('Расходы') . '</td>';
            $out_total = '<tr class="well"><td>' . l('Итого') . '</td>';
            $out_cumulative_total = '<tr><td>' . l('Нарастающий итог') . '</td>';

            $crs = $this->all_configs['suppliers_orders']->currencies;
            $out_inc_ct = ''; // доходы по категориям (статьям)
            if ($contractors_categories_inc) {
                foreach ($contractors_categories_inc as $cid => $ct) {
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
                foreach ($contractors_categories_exp as $cid => $ct) {
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
                $out_inc .= '<td>' . (array_key_exists($number, $m_inc) ? show_price($m_inc[$number], 2, ' ', ',', 100,
                        $crs) : '') . '</td>';
                $out_exp .= '<td>' . (array_key_exists($number, $m_exp) ? show_price($m_exp[$number], 2, ' ', ',', 100,
                        $crs) : '') . '</td>';
                $out_total .= '<td>' . (array_key_exists($number, $total) ? show_price($total[$number], 2, ' ', ',',
                        100, $crs) : '') . '</td>';
                $out_cumulative_total .= '<td>' . show_price($cumulative_total, 2, ' ', ',', 100, $crs) . '</td>';
            }
            $out .= '<td>' . l('Всего') . '</td></tr></thead><tbody>';
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

    /**
     * @return array|mixed
     */
    function array_sum_values()
    {
        $return = array();
        //$intArgs = func_num_args();
        $arrArgs = func_get_args();

        if (count($arrArgs) > 0) {
            if (count($arrArgs) == 1) {
                reset($arrArgs);
                $return = current($arrArgs);
            } else {
                foreach ($arrArgs as $arrItem) {
                    if (is_array($arrItem) && count($arrItem) > 0) {
                        foreach ($arrItem as $k => $v) {
                            $return[$k] = (array_key_exists($k, $return) ? $return[$k] : 0) + $v;
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @return array
     */
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
                            && array_key_exists($m, $m_tr_cb[$t['cid']]) && array_key_exists($t['cr'],
                                $m_tr_cb[$t['cid']][$m])
                                ? $m_tr_cb[$t['cid']][$m][$t['cr']] : 0) + ($t['inc'] - $t['exp']);
                    }
                }
            }

            $out .= $this->accountings_year_filter($year, '#reports-annual_balance', $years);
            $out .= '<div class="table-responsive"><table class="table table-bordered table-reports table-condensed"><thead><tr><td>' . l('Счет') . '</td>';

            $out_cb = '';
            if ($cashboxes) {
                foreach ($cashboxes as $cid => $c) {
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
                $out_total .= '<td>' . (array_key_exists($number, $total) ? show_price($total[$number], 2, ' ', ',',
                        100, $crs) : '') . '</td>';
            }
            $out .= '</tr></thead><tbody>' . $out_cb;
            //$out .= '<tr><td>Наличные</td>' . $out_total_tr . '</tr>';
            $out .= '<tr class="well"><td>' . l('Итого') . '</td>' . $out_total . '</tr>';
            $out .= '</tbody></table></div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @param $year
     * @param $hash
     * @param $years
     * @return string
     */
    function accountings_year_filter($year, $hash, $years)
    {
        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?year=';

        return $this->view->renderFile('accountings/accountings_year_filter', array(
            'year' => $year,
            'hash' => $hash,
            'years' => $years,
            'url' => $url,
            'currentYear' => date('Y')
        ));
    }

    /**
     * @return array
     */
    function accountings_reports_cost_of()
    {
        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

            $query = '';

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
                if ($contractor['amount'] > 0) {
                    $s_balance['exp'] -= $contractor['amount'];
                }
                if ($contractor['amount'] < 0) {
                    $s_balance['inv'] -= $contractor['amount'];
                }
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
                $total['html'] = '';
                $i = 1;
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
            $out .= '<tr><td><strong>' . l('Оборотные активы') . ': ' . InfoPopover::getInstance()->createQuestion('l_accountings_working_capital_info') . '</strong></td><td>' . $cost_of['html'] . '</td></tr>';
            $out .= '<tr><td><strong>' . l('Необоротные активы') . ': ' . InfoPopover::getInstance()->createQuestion('l_accountings_noncurrent_assets_info') . '</strong></td><td>' . $assets['html'] . '</td></tr>';
            $out .= '<tr><td><strong>' . l('Баланс поставщиков') . ': ' . InfoPopover::getInstance()->createQuestion('l_accountings_cash_balance_suppl_info') . '</strong></td><td>' . $s_balance['html'] . '</td></tr>';
            $out .= '<tr><td><strong>' . l('В кассе') . ': ' . InfoPopover::getInstance()->createQuestion('l_accountings_cash_info') . '</strong></td><td>' . $total_cashboxes['html'] . '</td></tr>';
            $out .= '<tr><td><h5>' . l('Итого') . ': </h5></td><td><h5>' . $total['html'] . '</h5></td></tr>';

            // расчет долевого участия контрагентов
            if (array_key_exists('erp-contractors-founders', $this->all_configs['configs'])
                && count($this->all_configs['configs']['erp-contractors-founders']) > 0
            ) {

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

                    usort($total_by_ctr, array('accountings', 'akcsort'));

                    foreach ($total_by_ctr as $ct) {
                        if ($ct['currency'] > 0) {
                            if (!array_key_exists($ct['ctr_id'], $ctr_total)
                                || !array_key_exists('amounts', $ctr_total[$ct['ctr_id']])
                                || !array_key_exists($ct['currency'], $ctr_total[$ct['ctr_id']]['amounts'])
                            ) {
                                $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']] = 0;
                            }

                            $ctr_total[$ct['ctr_id']]['title'] = $ct['title'];
                            $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']] += $ct['amount'] / 2;
                            $ctr_total[$ct['ctr_id']]['ba'][$ct['currency']] = $ctr_total[$ct['ctr_id']]['amounts'][$ct['currency']];
                        }
                    }

                    foreach ($ctr_total as $ctr_id => $v) {
                        foreach ($ctr_total as $sctr_id => $sv) {
                            if ($ctr_id != $sctr_id) {
                                foreach ($v['amounts'] as $cc => $a) {
                                    if (array_key_exists($cc, $sv['amounts'])) {
                                        $ctr_total[$ctr_id]['amounts'][$cc] -= $sv['ba'][$cc];
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($ctr_total as $ctr_id => $ctr) {
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    function accountings_reports_turnover_array()
    {
        $array = array();

        $amounts = $this->profit_margin($_GET);

        if ($amounts && is_array($amounts['orders']) && count($amounts['orders']) > 0) {
            foreach ($amounts['orders'] as $k => $p) {
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

    /**
     * @return array
     */
    function accountings_reports_turnover()
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : date('01.m.Y', time())) . ' - ' .
            (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : date('t.m.Y', time()));
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
            || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
            || $this->all_configs['oRole']->hasPrivilege('partner')
        ) {

            $isAdmin = !$this->all_configs['oRole']->hasPrivilege('partner') || $this->all_configs['oRole']->hasPrivilege('site-administration');
            if ($isAdmin) {
                $managers = $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders');
                $engineers = $this->all_configs['oRole']->get_users_by_permissions('engineer');
            }
            // фильтры
            $states = $this->all_configs['configs']['order-status'];
            $filters = $this->view->renderFile('accountings/reports_turnover/filters', array(
                'isAdmin' => $isAdmin,
                'date' => $date,
                'managers' => isset($managers) ? $managers : null,
                'engineers' => isset($engineers) ? $engineers : null,
                'accepters' => $this->all_configs['oRole']->get_users_by_permissions('create-clients-orders'),
                'states' => array_map(function ($id, $value) {
                    return array(
                        'id' => $id,
                        'title' => $value['name']
                    );
                }, array_keys($states), $states),
                'userId' => $user_id,
                'brands' => $this->all_configs['db']->query('SELECT id, title FROM {brands}')->vars()
            ));

            // прибыль и оборот
            $currencies = $this->all_configs['suppliers_orders']->currencies;
            $cco = $this->all_configs['suppliers_orders']->currency_clients_orders;
            $profit = $turnover = $avg = 0;

            $by = array();
            if (!$isAdmin) {
                $by['acp'] = $user_id;
            }
            $table_of_orders = '';
            $amounts = $this->profit_margin($by + $_GET);
            if ($amounts && is_array($amounts['orders']) && count($amounts['orders']) > 0) {
                $turnover = $amounts['turnover'];
                $profit = $amounts['profit'];
                $avg = $amounts['avg'];
                $table_of_orders = $this->view->renderFile('accountings/reports_turnover/table_of_orders', array(
                    'isAdmin' => $isAdmin,
                    'amounts' => $amounts,
                ));

            }

            $unloading = $this->view->renderFile('accountings/reports_turnover/unloading', array(
                'cco' => $cco,
                'isAdmin' => $isAdmin,
                'profit' => $profit,
                'turnover' => $turnover,
                'avg' => $avg,
                'currencies' => $currencies
            ));


            $out = $filters . $unloading . $table_of_orders . $this->showSalary($amounts['orders'], $by + $_GET);
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
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
            $out .= '<label>' . l('Период') . ':</label>
                     <div class="input-group">
                        <input type="text" name="date" value="' . $date . '" class="form-control daterangepicker" />
                        <span class="input-group-btn">
                            <input class="btn" type="submit" name="filters" value="' . l('Применить') . '" />
                        </span>
                     </div>';
            $out .= '</form><br>';

            // чистая прибыль

            // фильтр по дате
            $day_from = 1 . date(".m.Y") . ' 00:00:00';
            $day_to = 31 . date(".m.Y") . ' 23:59:59';
            if (array_key_exists('df', $_GET) && strtotime($_GET['df']) > 0) {
                $day_from = $_GET['df'] . ' 00:00:00';
            }
            if (array_key_exists('dt', $_GET) && strtotime($_GET['dt']) > 0) {
                $day_to = $_GET['dt'] . ' 23:59:59';
            }

            $query = $this->all_configs['db']->makeQuery('AND t.date_transaction BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
                AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));

            // прибыль
            $amounts = $this->profit_margin(array('df' => $day_from, 'dt' => $day_to));
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
                      AND IF(t.transaction_type=?, cc.id=t.cashboxes_currency_id_from, NULL) ?query GROUP BY cc.currency',
                array($ext_query, TRANSACTION_OUTPUT, $query))->vars();

            $out .= '<p>' . l('Чистая прибыль') . ': <strong>';
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
            $out .= '</strong> ' . InfoPopover::getInstance()->createQuestion('l_accountings_net_profit_info') . ' </p>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function accountings_orders_pre($hash = '#orders_pre-noncash')
    {
        if (trim($hash) == '#orders_pre' || (trim($hash) != '#orders_pre-credit' && trim($hash) != '#orders_pre-noncash')) {
            $hash = '#orders_pre-noncash';
        }

        return array(
            'html' => $this->view->renderFile('accountings/accountings_orders_pre'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    public function accountings_pre_noncash()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {

            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);
            $managers = $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders');
            $_GET['prepay'] = true;
            $queries = $this->all_configs['manageModel']->clients_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            // достаем заказы
            $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page);

            $out = $this->view->renderFile('accountings/accountings_pre_noncash', array(
                'date' => $date,
                'managers' => $managers,
                'query' => $query,
                'count_on_page' => $count_on_page,
                'orders' => $orders,
            ));
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function accountings_orders_pre_credit()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {

            $orders = $this->all_configs['db']->query('SELECT o.id, c.fio, c.email, c.phone, o.status, c.contractor_id FROM {orders} as o
                    LEFT JOIN (SELECT id, fio, email, phone, contractor_id FROM {clients})c ON c.id=o.user_id
                    WHERE o.status=?i OR o.status=?i OR o.status=?i ORDER BY o.status, o.date_add DESC',
                array(
                    $this->all_configs['configs']['order-status-loan-approved'],
                    $this->all_configs['configs']['order-status-loan-wait'],
                    $this->all_configs['configs']['order-status-loan-denied']
                ))->assoc();

            if ($orders && count($orders) > 0) {
                $out .= '<table class="table table-striped"><thead><tr><td>' . l('ФИО клиента') . '</td><td>' . l('Товар') . '</td><td>' . l('Кредит одобрен') . ',<br />' . l('документы готовы') . '</td><td>Отказ</td></tr></thead><tbody>';
                foreach ($orders as $order) {
                    $goods_html = '';
                    $goods = $this->all_configs['db']->query('SELECT title FROM {orders_goods} WHERE order_id=?i',
                        array($order['id']))->assoc();
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
                    $fio = (mb_strlen(trim($order['fio']),
                            'UTF-8') > 0) ? trim($order['fio']) : ((mb_strlen(trim($order['phone']),
                            'UTF-8') > 0) ? trim($order['phone']) : trim($order['email']));
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
                $out .= '<p  class="text-error">' . l('Нет заказов') . '</p>';
            }
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    public function accountings_orders($hash = '#a_orders-clients')
    {
        if (trim($hash) == '#a_orders' || (trim($hash) != '#a_orders-clients' && trim($hash) != '#a_orders-suppliers')) {
            $hash = '#a_orders-clients';
        }

        return array(
            'html' => $this->view->renderFile('accountings/accountings_orders'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function accountings_orders_suppliers()
    {
        $out = '';
        $fitlers = '';
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $fitlers = $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(false, true, false,
                'a_orders-suppliers');
            $out .= '<h4>' . l('Заказы поставщику которые ждут оплаты') . '</h4><br />';
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
            $out .= page_block($count_page, $count, '#a_orders-suppliers');
        }
        return array(
            'html' => $out,
            'menu' => $fitlers,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @param     $chain
     * @param int $type
     * @return string
     */
    function show_tr_accountings_orders_clients($chain, $type = 0)
    {
        return $this->view->renderFile('accountings/show_tr_accountings_orders_clients', array(
            'chain' => $chain,
            'type' => $type
        ));
    }

    /**
     * @return array
     */
    public function accountings_orders_clients()
    {
        $out = '';
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

            $filters = $this->view->renderFile('accountings/_accountings_orders_clients_filters', array(
                'date' => $date
            ));

            $chains = array();
            $_chains = null;
            $query = $this->all_configs['manageModel']->global_filters($_GET,
                array('date', 'category', 'product', 'operators', 'client', 'client_orders_id', 'cashless'));

            $count_on_page = $this->count_on_page;
            $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;


            $count = $this->all_configs['manageModel']->get_count_accounting_clients_orders($query);
            $orders = $this->all_configs['db']->query('SELECT o.id, o.course_value, o.sum, o.sum_paid, o.fio,
                        o.phone, o.email, o.date_add, o.date_pay, o.prepay, o.discount, o.type, o.return_id,  
                        a.email as a_email, a.fio as a_fio, a.phone as a_phone, a.login as a_login
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                    LEFT JOIN {users} as a ON a.id=o.accepter
                    WHERE 1=1 ?query GROUP BY o.id ORDER BY o.date_add DESC LIMIT ?i, ?i',
                array($query, $skip, $count_on_page))->assoc('id');
            $count_page = ceil($count / $count_on_page);

            if ($orders) {
                $goods = $this->all_configs['db']->query('SELECT og.title, og.goods_id, og.order_id, og.date_add
                        FROM {orders_goods} as og
                        WHERE og.order_id IN (?li) ORDER BY og.date_add DESC',
                    array(array_keys($orders)))->assoc();

                foreach ($goods as $product) {
                    $orders[$product['order_id']]['goods'][$product['goods_id']] = $product;
                }
            }
            $out = $this->view->renderFile('accountings/accountings_orders_clients', array(
                'count' => $count,
                'count_page' => $count_page,
                'orders' => $orders,

            ));
        }
        return array(
            'html' => $out,
            'menu' => $filters,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function accountings_contractors()
    {
        return array(
            'html' => $this->view->renderFile('accountings/accountings_contractors', array(
                'contractors' => $this->contractors
            )),
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function accountings_settings($hash = '#settings-cashboxes')
    {
        if (trim($hash) == '#settings' || (trim($hash) != '#settings-cashboxes' && trim($hash) != '#settings-currencies'
                && trim($hash) != '#settings-categories_expense' && trim($hash) != '#settings-categories_income'
                && trim($hash) != '#settings-contractors')
        ) {
            $hash = '#settings-cashboxes';
        }

        return array(
            'html' => $this->view->renderFile('accountings/accountings_settings'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function accountings_settings_cashboxes()
    {
        $out = '';

        // форма для создания кассы
        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // валюты
            $cashboxes_currencies = $this->cashboxes_courses();

            $out .= "<div class='panel-group row-fluid' id='accordion_cashboxes'><div class='col-sm-6'>" . $this->form_cashbox($cashboxes_currencies);

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

            $out .= '</div></div>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @return string
     */
    function gen_currency_table()
    {
        return $this->view->renderFile('accountings/gen_currency_table', array(
            'cashboxes_currencies' => $this->cashboxes_courses()
        ));
    }

    /**
     * @param bool $show_all
     * @param null $current
     * @param bool $show_default
     * @return string
     */
    function gen_new_currency_options($show_all = true, $current = null, $show_default = true)
    {
        if (!$show_all) {
            $cashboxes_currencies = $this->cashboxes_courses();
        }
        $new_courses = $this->all_configs['suppliers_orders']->currencies;
        $out = '';
        if ($show_default) {
            $out .= '<option value="">' . l('Выберите валюту') . '</option>';
        }
        foreach ($new_courses as $new_course_id => $new_course) {
            if ($show_all || !array_key_exists($new_course_id, $cashboxes_currencies)) {
                $sel = $current == $new_course_id ? ' selected' : '';
                $data = ' data-shortname="' . $new_course['shortName'] . '"';
                $out .= "<option" . $sel . $data . " value='{$new_course_id}'>{$new_course['name']} [{$new_course['shortName']}]</option>";
            }
        }
        return $out;
    }

    /**
     * @return array
     */
    function accountings_settings_currencies()
    {
        return array(
            'html' => $this->view->renderFile('accountings/accountings_settings_currencies', array(
                'controller' => $this
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function accountings_settings_categories_expense()
    {
        return array(
            'html' => $this->view->renderFile('accountings/accountings_settings_categories', array(
                'categories' => $this->get_contractors_categories(1),
                'title' => l('Создать статью расход'),
                'categories_type' => 'expense'
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function accountings_settings_categories_income()
    {
        return array(
            'html' => $this->view->renderFile('accountings/accountings_settings_categories', array(
                'categories' => $this->get_contractors_categories(2),
                'title' => l('Создать статью приход'),
                'categories_type' => 'income'
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function accountings_settings_contractors()
    {
        return array(
            'html' => $this->view->renderFile('accountings/accountings_settings_contractors', array(
                'contractors' => $this->contractors,
                'controller' => $this
            )),
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    public static function get_submenu($oRole = null)
    {
        $submenu = array(
            array(
                'click_tab' => true,
                'url' => '#cashboxes',
                'name' => l('Кассы')
            ),
            array(
                'click_tab' => true,
                'url' => '#transactions',
                'name' => l('Транзакции')
            ),
        );
        if (empty($oRole)) {
            global $all_configs;
            $oRole = $all_configs['oRole'];
        }

        if ($oRole->hasPrivilege("site-administration")
            || $oRole->hasPrivilege('accounting-reports-turnover')
            || $oRole->hasPrivilege('partner')
        ) {
            $submenu[2] = array(
                'click_tab' => true,
                'url' => '#reports',
                'name' => l('Отчеты')
            );
        }
        if ($oRole->hasPrivilege('accounting')) {
            $submenu[3] = array(
                'click_tab' => true,
                'url' => '#a_orders',
                'name' => l('Заказы')
            );
        }
        if ($oRole->hasPrivilege('accounting') ||
            $oRole->hasPrivilege('accounting-contractors')
        ) {
            $submenu[4] = array(
                'click_tab' => true,
                'url' => '#contractors',
                'name' => l('Контрагенты')
            );
        }
        if ($oRole->hasPrivilege('accounting')) {
            $submenu[5] = array(
                'click_tab' => true,
                'url' => '#settings',
                'name' => l('Настройки')
            );
        }
        return $submenu;
    }

    /**
     * @param $parent_id
     * @param $contractor_category
     */
    private function addContractorCategoryForUsers($parent_id, $contractor_category)
    {
        $this->all_configs['db']->query('
            INSERT INTO  restore4_contractors_categories_links (contractors_categories_id, contractors_id)
            SELECT ?, contractors_id FROM {contractors_categories_links} WHERE contractors_categories_id = ? and deleted=0
            ', array($contractor_category, $parent_id))->ar();
    }

    /**
     * @param $data
     * @param $user_id
     * @param $mod_id
     * @return array
     */
    private function contractorEdit($data, $user_id, $mod_id)
    {
        $data = array(
            'state' => true,
            'message' => ''
        );
        $is_system = $this->all_configs['db']->query("SELECT id FROM {contractors} "
            . "WHERE id = ?i AND comment = 'system'", array($this->all_configs['arrequest'][2]), 'el');
        try {
            if ($is_system) {
                throw  new ExceptionWithMsg(l('Системный контрагент - не подлежит редактированию'));
            }
            // права
            if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                throw  new ExceptionWithMsg(l('Нет прав'));
            }
            // ид
            if (!isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] == 0) {
                throw  new ExceptionWithMsg(l('Контрагент не найден'));
            }
//            // статьи
//            if (!isset($_POST['contractor_categories_id']) || count($_POST['contractor_categories_id']) == 0) {
//                throw  new ExceptionWithMsg(l('Укажите статью'));
//            }
            // фио
            if (!isset($_POST['title']) || mb_strlen(trim($_POST['title']), 'UTF-8') == 0) {
                throw  new ExceptionWithMsg(l('Введите ФИО'));
            }
            $ar = $this->all_configs['db']->query('UPDATE {contractors} SET title=?, type=?i, comment=? WHERE id=?i',
                array(
                    trim($_POST['title']),
                    $_POST['type'],
                    trim($_POST['comment']),
                    $this->all_configs['arrequest'][2]
                ))->ar();

            if ($ar) {
                $this->History->save('edit-contractor', $mod_id, $this->all_configs['arrequest'][2]);
            }

            $this->ContractorsCategoriesLinks->update(array(
                'deleted' => 1
            ), array(
                'contractors_id' => $this->all_configs['arrequest'][2]
            ));

            // категории
            if (isset($_POST['contractor_categories_id']) && count($_POST['contractor_categories_id']) > 0) {
                foreach ($_POST['contractor_categories_id'] as $contractor_category_id) {
                    if ($contractor_category_id > 0) {
                        $this->ContractorsCategoriesLinks->addCategoryToContractors($contractor_category_id,
                            $this->all_configs['arrequest'][2]);
                    }
                }
            }
            FlashMessage::set(l('Контрагент изменен'), FlashMessage::SUCCESS);

        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }
        return $data;
    }

    /**
     * @param $data
     * @param $user_id
     * @param $mod_id
     * @return array
     */
    private function contractorCreate($data, $user_id, $mod_id)
    {
        $data['state'] = true;
        try {
            // права
            if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                throw  new ExceptionWithMsg(l('Нет прав'));
            }
//            // статьи
//            if (!isset($_POST['contractor_categories_id']) || count($_POST['contractor_categories_id']) == 0) {
//                throw  new ExceptionWithMsg(l('Укажите статью'));
//            }
            // фио
            if (!isset($_POST['title']) || mb_strlen(trim($_POST['title']), 'UTF-8') == 0) {
                throw  new ExceptionWithMsg(l('Введите ФИО'));
            }

            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
            $access = new access($this->all_configs, false);
            $phone = $access->is_phone($phone);
            // телефон
            if (empty($phone)) {
                throw  new ExceptionWithMsg(l('Введите номер телефона в формате вашей страны'));
            }
            // создаем
            $contractor_id = $this->all_configs['db']->query('INSERT IGNORE INTO {contractors}
                        (title, type, comment) VALUES (?, ?i, ?)',
                array(trim($_POST['title']), $_POST['type'], trim($_POST['comment'])), 'id');

            if ($contractor_id <= 0) {
                throw  new ExceptionWithMsg(l('Такой контрагент уже существует'));
            }
            $data['id'] = $contractor_id;
            $data['name'] = htmlspecialchars($_POST['title']);
            if (isset($_POST['contractor_categories_id']) && count($_POST['contractor_categories_id']) > 0) {
                foreach ($_POST['contractor_categories_id'] as $contractor_category_id) {
                    if ($contractor_category_id > 0) {
                        $this->ContractorsCategoriesLinks->addCategoryToContractors($contractor_category_id,
                            $contractor_id);
                    }
                }
            }
            $this->History->save('add-contractor', $mod_id, $contractor_id);

            // создаем клиента для контрагента
            //email проверяется чуть выше
            $email = isset($_POST['email']) && filter_var($_POST['email'],
                FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
            if ($phone || $email) {
                $exists_client = $access->get_client($email, $phone, true);
                if ($exists_client && !$this->all_configs['db']->query("SELECT contractor_id FROM {clients} WHERE id = ?i",
                        array($exists_client['id']), 'el')
                ) {
                    // привязываем к существующему если к нему не привязан контрагент
                    $this->Clients->update(array('contractor_id' => $contractor_id),
                        array($this->Clients->pk() => $exists_client['id']));
                } else {
                    // создаем клиента и привязываем
                    $result = $access->registration(array(
                        'email' => $email,
                        'phone' => $phone[0],
                        'fio' => $_POST['title']
                    ));
                    if ($result['new']) {
                        $this->Clients->update(array('contractor_id' => $contractor_id),
                            array($this->Clients->pk() => $result['id']));
                    }
                }
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }


        return $data;
    }

    /**
     * @param $data
     * @param $user_id
     * @return mixed
     */
    private function createPayForm($formType, $data, $user_id)
    {
        // сегодня
        $today = date("d.m.Y");
        $select_cashbox = '';
        $selected_cashbox = isset($_POST['object_id']) && $_POST['object_id'] > 0 ? $_POST['object_id'] : 0;
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $cashboxes = $this->cashboxes;
        } else {
            $cashboxes = $this->all_configs['db']->query('SELECT * FROM {cashboxes}  WHERE id in (SELECT cashbox_id FROM {cashboxes_users} WHERE user_id = ?i)',
                array(
                    $user_id
                ))->assoc('id');
        }
        // список форм для редактирования касс
        if (count($cashboxes) > 0) {
            $erpct = $this->all_configs['configs']['erp-cashbox-transaction'];
            $erpt = $this->all_configs['configs']['erp-so-cashbox-terminal'];

            foreach ($cashboxes as $cashbox) {
                // выбор кассы при транзакции
                if ($cashbox['avail'] == 1) {
                    // кроме транзитной
                    $dis = $cashbox['id'] == $erpct
                        || ($cashbox['id'] == $erpt && !$this->all_configs['configs']['manage-show-terminal-cashbox']);

                    $select_cashbox .= '<option' . ($dis ? ' disabled' : '');
                    $select_cashbox .= ($cashbox['id'] == $selected_cashbox ? ' selected' : '');
                    $select_cashbox .= ' value="' . $cashbox['id'] . '">' . htmlspecialchars($cashbox['name']) . '</option>';
                    $selected_cashbox = $selected_cashbox == 0 ? $cashbox['id'] : $selected_cashbox;
                }
            }
        }

        $daf = $dc = $dccf = $dcct = ''; // disabled
        $supplier_order_id = 0; // orders
        $amount_from = 0; // amounts
        // контрагенты
        $select_contractors = '';
        $ccg_id = 0;

        list($co_id, $b_id, $t_extra, $amount_to, $order) = $this->getInfoForPayForm();

        if (!empty($_POST['transaction_extra']) && $amount_to == 0) {
            $_POST['transaction_extra'] = '';
            list($co_id, $b_id, $t_extra, $amount_to, $order) = $this->getInfoForPayForm();
        }

        $cashbox_id = array_key_exists('object_id',
            $_POST) && $_POST['object_id'] > 0 ? $_POST['object_id'] : $selected_cashbox;
        // валюта

        $data['content'] = $this->view->renderFile("accountings/pay_for_{$formType}_form", array(
            'supplier_order_id' => $supplier_order_id,
            'co_id' => $co_id,
            'b_id' => $b_id,
            't_extra' => $t_extra,
            'select_cashbox' => $select_cashbox,
            'selected_cashbox' => $selected_cashbox,
            'daf' => $daf,
            'amount_from' => $amount_from,
            'amount_to' => $amount_to,
            'cashbox_currencies' => $this->get_cashbox_currencies($cashbox_id),
            'dcct' => $dcct,
            'dccf' => $dccf,
            'dc' => $dc,
            'select_contractors' => $select_contractors,
            'order' => $order,
            'today' => $today,
            'ccg_id' => $ccg_id,
            'categories_from' => $this->get_contractors_categories(2),
            'categories_to' => $this->get_contractors_categories(1),
        ));

        if ($formType == 'repair') {
            $data['btns'] = $this->view->renderFile('accountings/repair_print_buttons', array(
                'order' => $order
            ));
        } else {
            $data['btns'] = '';
        }
        $data['btns'] .= '<button type="button" onclick="create_transaction_for(\'' . $formType . '\', this, {issued:' . (empty($_POST['issued']) ? 'false' : 'true') . '})" class="btn btn-success">' . l('Внести в кассу') . '</button>';
        $data['no-cancel-button'] = true;
        if ($formType == 'repair') {
            $data['btns'] .= '<button type="button" onclick="give_without_pay(\'' . $formType . '\', this)" class="btn btn-primary">' . l('Выдать без оплаты') . '</button>';
        }

        $data['functions'] = array('reset_multiselect()');
        $data['state'] = true;
        return $data;
    }

    /**
     * @param $data
     * @param $user_id
     * @param $act
     * @return mixed
     */
    private function createTransactionForm($data, $user_id, $act)
    {
        $btn = l('Сохранить');
        // тип транзакции
        $transactionType = intval(preg_replace("/[^0-9]/", "", $act));
        // сегодня
        $today = date("d.m.Y");
        $select_cashbox = '';
        $selected_cashbox = isset($_POST['object_id']) && $_POST['object_id'] > 0 ? $_POST['object_id'] : 0;
        if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
            $cashboxes = $this->cashboxes;
        } else {
            $cashboxes = $this->all_configs['db']->query('SELECT * FROM {cashboxes}  WHERE id in (SELECT cashbox_id FROM {cashboxes_users} WHERE user_id = ?i)',
                array(
                    $user_id
                ))->assoc('id');
        }
        // список форм для редактирования касс
        if (count($cashboxes) > 0) {
            $erpct = $this->all_configs['configs']['erp-cashbox-transaction'];
            $erpt = $this->all_configs['configs']['erp-so-cashbox-terminal'];

            foreach ($cashboxes as $cashbox) {
                // выбор кассы при транзакции
                if ($cashbox['avail'] == 1) {
                    // кроме транзитной
                    $dis = $cashbox['id'] == $erpct
                        || ($cashbox['id'] == $erpt && !$this->all_configs['configs']['manage-show-terminal-cashbox']);

                    $select_cashbox .= '<option' . ($dis ? ' disabled' : '');
                    $select_cashbox .= ($cashbox['id'] == $selected_cashbox ? ' selected' : '');
                    $select_cashbox .= ' value="' . $cashbox['id'] . '">' . htmlspecialchars($cashbox['name']) . '</option>';
                    $selected_cashbox = $selected_cashbox == 0 ? $cashbox['id'] : $selected_cashbox;
                }
            }
        }

        $daf = $dc = $dccf = $dcct = ''; // disabled
        $formClass = ''; // form class
        $supplier_order_id = $co_id = 0; // orders
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
            if ($transactionType == 1) {
                $amount_from = $this->all_configs['db']->query('SELECT (count_come * price)
                          FROM {contractors_suppliers_orders} WHERE id=?i',
                        array($_POST['supplier_order_id']))->el() / 100;
                $ccg_id = $this->all_configs['configs']['erp-so-contractor_category_id_from'];
                $contractor_id = $this->all_configs['db']->query('SELECT supplier FROM {contractors_suppliers_orders} WHERE id=?i',
                    array($_POST['supplier_order_id']))->el();
                $select_contractors = $this->contractors_options($ccg_id, $contractor_id);

                $daf = $dc = $dcct = 'disabled';
            }
            $formClass .= ' transaction_type-so-' . $transactionType;
            $supplier_order_id = $_POST['supplier_order_id'];
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
                $select_query_2 = $this->all_configs['db']->makeQuery('o.delivery_cost-o.delivery_paid FROM {orders} as o',
                    array());
                $t_extra = 'delivery';
            }
            // за комиссию
            if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'payment') {
                $select_query_1 = $this->all_configs['db']->makeQuery('o.payment_paid FROM {orders} as o', array());
                $select_query_2 = $this->all_configs['db']->makeQuery('o.payment_cost-o.payment_paid FROM {orders} as o',
                    array());
                $t_extra = 'payment';
            }
            // за предоплату
            if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'prepay') {
                $select_query_1 = $this->all_configs['db']->makeQuery('o.sum_paid FROM {orders} as o', array());
                $select_query_2 = $this->all_configs['db']->makeQuery('o.prepay-o.sum_paid FROM {orders} as o',
                    array());
                $t_extra = 'prepay';
            }
            // конкретная цепочка
            if ($b_id > 0 && (!isset($_POST['transaction_extra']) || ($_POST['transaction_extra'] != 'payment'
                        && $_POST['transaction_extra'] != 'delivery'))
            ) {
                // выдача
                if ($transactionType == 1) {
                    $select_query_1 = $this->all_configs['db']->makeQuery('h.paid FROM {orders} as o
                                LEFT JOIN {chains_headers} as h ON h.order_id=o.id
                                    AND h.id=(SELECT chain_id FROM {chains_bodies} WHERE id=?i)
                                LEFT JOIN {orders_goods} as og ON h.order_goods_id=og.id',
                        array($b_id));
                }
                // внесение
                if ($transactionType == 2) {
                    $select_query_2 = $this->all_configs['db']->makeQuery('og.price+og.warranties_cost-h.paid
                                FROM {orders} as o
                                LEFT JOIN {chains_headers} as h ON h.order_id=o.id
                                    AND h.id=(SELECT chain_id FROM {chains_bodies} WHERE id=?i)
                                LEFT JOIN {orders_goods} as og ON h.order_goods_id=og.id',
                        array($b_id));
                }
            }
            // выдача
            if ($transactionType == 1) {
                $btn = l('Выдать');
                $amount_from = $this->all_configs['db']->query('SELECT ?query WHERE o.id=?i GROUP BY o.id',
                        array($select_query_1, $_POST['client_order_id']))->el() / 100;
            }
            // внесение
            if ($transactionType == 2) {
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
        $select_contractors_categories_to = "<option value=''>" . l('Выберите') . "</option>" . build_array_tree($categories,
                $ccg_id) . "</select>";
        $categories = $this->get_contractors_categories(2);
        $select_contractors_categories_from = "<option value=''>" . l('Выберите') . "</option>" . build_array_tree($categories,
                $ccg_id) . "</select>";

        $cashbox_id = array_key_exists('object_id',
            $_POST) && $_POST['object_id'] > 0 ? $_POST['object_id'] : $selected_cashbox;
        // валюта
        $cashbox_currencies = $this->get_cashbox_currencies($cashbox_id);

        $data['content'] .= '<input type="hidden" name="transaction_type" id="transaction_type" value="' . $transactionType . '" />';
        $data['content'] .= '<input type="hidden" name="supplier_order_id" value="' . $supplier_order_id . '" />';
        $data['content'] .= '<input type="hidden" name="client_order_id" value="' . $co_id . '" />';
        $data['content'] .= '<input type="hidden" name="b_id" value="' . $b_id . '" />';
        $data['content'] .= '<input type="hidden" name="transaction_extra" value="' . $t_extra . '" />';

        $data['content'] .= '<div id="transaction_form_body" class="hide-conversion-3 transaction_type-' . $transactionType . ' ' . $formClass . '">';
        $data['content'] .= '<table><thead><tr><td></td><td></td><td>' . l('Сумма') . '</td><td>' . l('Валюта') . '</td>';
        $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span>Курс</span></td><td class="hide-not-tt-1 hide-not-tt-2"></td></tr></thead><tbody>';
        //* С кассы 1 3
        $data['content'] .= '<tr class="hide-not-tt-2"><td>* ' . l('С кассы') . '</td>';
        $data['content'] .= '<td><select onchange="select_cashbox(this, 1)" name="cashbox_from" class="form-control input-sm cashbox-1">' . $select_cashbox . '</select></td>';
        $data['content'] .= '<td><input ' . (empty($daf) ? '' : 'readonly') . ' class="form-control input-sm ' . $daf . '" style="width:80px" onchange="get_course(1)" id="amount-1" type="text" name="amount_from" value="' . $amount_from . '" onkeydown="return isNumberKey(event, this)" /></td>';
        $data['content'] .= '<td><select class="form-control input-sm cashbox_currencies-1" onchange="get_course(0)" name="cashbox_currencies_from">' . $cashbox_currencies . '</select></td>';
        $onchange = '
                $(\'#amount-2\').val(($(\'#amount-1\').val()*$(\'#conversion-course-1\').val()).toFixed(2));
                if ($(\'#amount-2\').val() > 0)
                    $(\'#conversion-course-2\').val(($(\'#amount-1\').val()/$(\'#amount-2\').val()).toFixed(4));
                else
                    $(\'#conversion-course-2\').val(0.0000);';
        $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span><input id="conversion-course-1" style="width:80px" onchange="' . $onchange . '" class="form-control input-mini" onkeydown="return isNumberKey(event, this)" type="text" value="1.0000" name="cashbox_course_from"/></span></td>';
        $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion" onclick="get_course(0)"><span><small>' . l('Прямой') . '</small><br /><small id="conversion-course-db-1">1.0000</small></span></td></tr>';
        //* В кассу 2 3
        $data['content'] .= '<tr class="hide-not-tt-1"><td>* ' . l('В кассу') . '</td>';
        $data['content'] .= '<td><select onchange="select_cashbox(this, 2)" name="cashbox_to" class="form-control input-sm cashbox-2">' . $select_cashbox . '</select></td>';
        $onchange = '
                if ($(\'#amount-1\').val() > 0 && $(\'#amount-2\').val() > 0) {
                    $(\'#conversion-course-1\').val(($(\'#amount-2\').val()/$(\'#amount-1\').val()).toFixed(4));
                    $(\'#conversion-course-2\').val(($(\'#amount-1\').val()/$(\'#amount-2\').val()).toFixed(4));
                } else {
                    $(\'#conversion-course-1\').val(0.0000);
                    $(\'#conversion-course-2\').val(0.0000);
                }';
        $data['content'] .= '<td class="hide-conversion"><span><input class="form-control input-sm" onchange="' . $onchange . '" id="amount-2" type="text" style="width:80px" name="amount_to" value="' . $amount_to . '" onkeydown="return isNumberKey(event, this)" /></span></td>';
        $data['content'] .= '<td><select class="form-control input-sm cashbox_currencies-2" onchange="get_course(0)" name="cashbox_currencies_to">' . $cashbox_currencies . '</select></td>';
        $onchange = '
                if ($(\'#conversion-course-2\').val() > 0)
                    $(\'#amount-2\').val(($(\'#amount-1\').val()/$(\'#conversion-course-2\').val()).toFixed(2));
                else
                    $(\'#amount-2\').val(0.0000);
                if ($(\'#amount-2\').val() > 0)
                    $(\'#conversion-course-1\').val(($(\'#amount-2\').val()/$(\'#amount-1\').val()).toFixed(4));
                else
                    $(\'#conversion-course-1\').val(0.0000);';
        $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span><input id="conversion-course-2" style="width:80px" onchange="' . $onchange . '" class="form-control input-sm" onkeydown="return isNumberKey(event, this)" type="text" value="1.0000" name="cashbox_course_to"/></span></td>';
        $data['content'] .= '<td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion" onclick="get_course(0)"><span><small>' . l('Обратный') . '</small><br /><small id="conversion-course-db-2">1.0000</small></span></td></tr>';
        if ($co_id == 0) {
            //* Статья 1
            $data['content'] .= '<tr class="hide-not-tt-2 hide-not-tt-3"><td>* ' . l('Статья') . '</td>';
            $data['content'] .= '<td style="width:150px"><select ' . $dcct . ' id="contractor_category-1" class="multiselect input-sm form-control multiselect-sm" onchange="select_contractor_category(this, 1)" name="contractor_category_id_to">';
            $data['content'] .= $select_contractors_categories_to . '</select>';
            $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_expense';
            $data['content'] .= '</select></td><td><a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td></tr>';
            //* Статья 2
            $data['content'] .= '<tr class="hide-not-tt-1 hide-not-tt-3"><td>* ' . l('Статья') . '</td>';
            $data['content'] .= '<td style="width:150px"><select ' . $dccf . ' id="contractor_category-2" class="multiselect  multiselect-sm" onchange="select_contractor_category(this, 2)" name="contractor_category_id_from">';
            $data['content'] .= $select_contractors_categories_from . '</select></td>';
            $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_income';
            $data['content'] .= '<td><a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td></tr>';
            //* Контрагент 1 2
            $data['content'] .= '<tr class="hide-not-tt-3"><td>*&nbsp;' . l('Контрагент') . '</td>';
            $data['content'] .= '<td style="width:150px"><select ' . $dc . ' class="form-control input-sm select_contractors" name="contractors_id" style="width: 100%;">' . $select_contractors . '</select></td>';
            $url = $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-contractors';
            $data['content'] .= '<td><a target="_blank" href="' . $url . '"> <i class="glyphicon glyphicon-plus"></i></a></td>';
            $data['content'] .= '</tr>';
        }
        // только обычные транзакции
        if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
            // Без внесения на баланс 1
            $content = '(' . l('Ставим птичку в случае, если данная выплата производится за услуги или расходные материалы.') . ' ';
            $content .= l('Не ставим птичку - если оплата производится за приобретаемые оборотные активы)');
            $data['content'] .= '<tr class="hide-not-tt-2 hide-not-tt-3 hide-not-tt-so-1"><td colspan="2">';
            $data['content'] .= '<div class="checkbox"><label class="popover-info" data-original-title="" data-content="' . $content . '">';
            $js = '
                    if (this.checked) {
                        if (!confirm(\'' . l('Не зачислять контрагенту на баланс?') . '\')) {
                            this.checked=false;
                        }
                    }';
            $data['content'] .= '<input type="checkbox" onchange="javascript:' . $js . '" name="without_contractor" value="1"/>' . l('Без внесения на баланс') . '</label></div></td></tr>';
            // Без списания c баланса 2
            $content = '(' . l('Птичку ставим - когда поступление денежных средств не связано с приобретением или возвратом оборотных активов') . ')';
            $js = 'if (this.checked) { if (!confirm(\'' . l('Не списывать у контрагента с баланса?') . '\')) { this.checked=false; } }';
            $data['content'] .= '<tr class="hide-not-tt-1 hide-not-tt-3"><td colspan="2">';
            $data['content'] .= '<div class="checkbox"><label class="popover-info" data-original-title="" data-content="' . $content . '">';
            $data['content'] .= '<input type="checkbox" onchange="javascript:' . $js . '" name="without_contractor" value="1"/>' . l('Без списания с баланса') . '</label></div></td></tr>';
        }
        // Списать с баланса контрагента (заказ клиента)
        if ($co_id > 0 && ($transactionType == 1 || $transactionType == 2) && $client_contractor > 0) {//TODO have contractor
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
            $data['content'] .= $transactionType == 2 ? l('Списать с баланса контрагента') : l('Зачислить на баланс контрагента');
            $data['content'] .= '</label></td></tr>';
        }
        // только обычные транзакции или выдача за заказ клиента
        if ($act == 'begin-transaction-1-co' || $act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
            // Примечание 1 2 3
            $data['content'] .= '<tr><td colspan="2"><textarea class="form-control input-sm" name="comment" placeholder="' . l('примечание') . '"></textarea></td>';
            // только обычные транзакции
            if ($act == 'begin-transaction-1' || $act == 'begin-transaction-2' || $act == 'begin-transaction-3') {
                $data['content'] .=
                    '<td colspan="4" class="center"><div class="form-group">
                            <input class="form-control daterangepicker_single input-sm" type="text" name="date_transaction" value="' . $today . '" />
                        </div>';
            }
            $data['content'] .= '</tr>';
        }
        $data['content'] .= '</tbody></table></div></fieldset></form>';
        $data['content'] .= '
            <style>
                .multiselect-btn-group {
                    width: 150px !important;
                }

                button.multiselect {
                    width: 150px !important;
                }
            </style>
        ';

        $data['btns'] = '<button type="button" onclick="return create_transaction(this, 0, event)" class="btn btn-success">' . $btn . '</button>';

        $data['functions'] = array('reset_multiselect()');
        $data['state'] = true;
        return $data;
    }

    /**
     * @param $userId
     * @return array
     */
    protected function getCashboxes($userId)
    {
        return $this->Cashboxes->getPreparedCashboxes($userId);
    }

    /**
     * @param $cashboxes
     * @return array
     */
    private function calculateCashboxesAmount($cashboxes)
    {
        $this->cashboxes = $this->Cashboxes->prepareCashboxes($cashboxes);
        return $this->Cashboxes->calculateAmount($cashboxes);
    }

    /**
     * @param $orders
     * @param $filters
     * @return string
     */
    protected function showSalary($orders, $filters)
    {
        $managersIds = array();
        if (array_key_exists('mg', $filters) && count(array_filter(explode(',', $filters['mg']))) > 0) {
            $managersIds = array_filter(explode(',', $filters['mg']));
        }
        // фильтр по приемщику
        $acceptorsIds = array();
        if (array_key_exists('acp', $filters) && count(array_filter(explode(',', $filters['acp']))) > 0) {
            $acceptorsIds = array_filter(explode(',', $filters['acp']));
        }
        // фильтр по Инженер
        $engineersIds = array();
        if (array_key_exists('eng', $filters) && count(array_filter(explode(',', $filters['eng']))) > 0) {
            $engineersIds = array_filter(explode(',', $filters['eng']));
        }
        $all = array_merge($acceptorsIds, $managersIds, $engineersIds);
        if (empty($all)) {
            return '';
        }
        $users = $this->Users->query('
            SELECT id, fio, salary_from_repair, salary_from_sale, use_fixed_payment, use_percent_from_profit 
            FROM {users} 
            WHERE id in (?li) AND (salary_from_repair > 0 OR salary_from_sale > 0 OR use_fixed_payment > 0 OR use_percent_from_profit > 0)
        ', array($all))->assoc();
        $saleProfit = array();
        $repairProfit = array();
        $detailed = array();
        if (!empty($users)) {
            foreach ($users as $user) {
                foreach ($orders as $order) {
                    if (!in_array($user['id'], array($order['manager'], $order['acceptor'], $order['engineer']))) {
                        continue;
                    }
                    if ($order['order_type'] == ORDER_SELL) {
                        if (!isset($saleProfit[$user['id']])) {
                            $saleProfit[$user['id']] = 0;
                        }
                        $profit = $this->calculateSaleProfit($order, $user);
                        $saleProfit[$user['id']] += $profit['value'];
                        if (!empty($profit['detailed'])) {
                            $detailed[$user['id']] = $profit['detailed'];
                        }
                    }
                    if ($order['order_type'] == ORDER_REPAIR) {
                        if (!isset($repairProfit[$user['id']])) {
                            $repairProfit[$user['id']] = 0;
                        }
                        $profit = $this->calculateRepairProfit($order, $user);
                        $repairProfit[$user['id']] += $profit['value'];
                        if (!empty($profit['detailed'])) {
                            $detailed[$user['id']] = $profit['detailed'];
                        }
                    }
                }
            }
        }
        return $this->view->renderFile('accountings/reports_turnover/salary', array(
            'users' => $users,
            'saleProfit' => $saleProfit,
            'repairProfit' => $repairProfit,
            'detailed' => $detailed
        ));
    }

    /**
     * @return array
     */
    private function getInfoForPayForm()
    {
// заказ клиента
        $co_id = 0;
        $t_extra = '';
        $amount_to = 0;
        $order = array();
        $b_id = 0;
        if (isset($_POST['client_order_id']) && $_POST['client_order_id'] > 0) {
            $co_id = $_POST['client_order_id'];
            $select_query_2 = $this->all_configs['db']->makeQuery('o.sum-o.sum_paid-o.discount FROM {orders} as o',
                array());
            $b_id = isset($_POST['b_id']) && $_POST['b_id'] > 0 ? $_POST['b_id'] : $b_id;

            // за доставку
            if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'delivery') {
                $select_query_2 = $this->all_configs['db']->makeQuery('o.delivery_cost-o.delivery_paid-o.discount FROM {orders} as o',
                    array());
                $t_extra = 'delivery';
            }
            // за комиссию
            if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'payment') {
                $select_query_2 = $this->all_configs['db']->makeQuery('o.payment_cost-o.payment_paid-o.discount FROM {orders} as o',
                    array());
                $t_extra = 'payment';
            }
            // за предоплату
            if (isset($_POST['transaction_extra']) && $_POST['transaction_extra'] == 'prepay') {
                $select_query_2 = $this->all_configs['db']->makeQuery('o.prepay-o.sum_paid-o.discount FROM {orders} as o',
                    array());
                $t_extra = 'prepay';
            }
            // конкретная цепочка
            if ($b_id > 0 && (!isset($_POST['transaction_extra']) || ($_POST['transaction_extra'] != 'payment'
                        && $_POST['transaction_extra'] != 'delivery'))
            ) {
                // выдача
                $select_query_2 = $this->all_configs['db']->makeQuery('og.price+og.warranties_cost-h.paid
                                FROM {orders} as o
                                LEFT JOIN {chains_headers} as h ON h.order_id=o.id
                                    AND h.id=(SELECT chain_id FROM {chains_bodies} WHERE id=?i)
                                LEFT JOIN {orders_goods} as og ON h.order_goods_id=og.id',
                    array($b_id));
            }
            $amount_to = $this->all_configs['db']->query('SELECT ?query WHERE o.id=?i GROUP BY o.id',
                    array($select_query_2, $_POST['client_order_id']))->el() / 100;

            $order = $this->all_configs['db']->query('SELECT o.*, c.contractor_id
                        FROM {orders} as o, {clients} as c WHERE o.id=?i AND o.user_id=c.id',
                array($_POST['client_order_id']))->row();
        }
        return array($co_id, $b_id, $t_extra, $amount_to, $order);
    }

    /**
     * @param        $order
     * @param        $data
     * @param string $defaultMessage
     * @return mixed
     */
    protected function changeOrderStatus($order, $data, $defaultMessage = '')
    {
// меняем статус
        $response = update_order_status($order, $_POST['status']);
        if (!isset($response['state']) || $response['state'] == false) {
            $data['state'] = false;
            $_POST['status'] = $order['status'];
            $data['msg'] = isset($response['msg']) && !empty($response['msg']) ? $response['msg'] : $defaultMessage;
        }
        return $data;
    }

    /**
     * @param $cashbox
     * @return mixed
     */
    private function cashboxHaveTransaction($cashbox)
    {
        return $this->all_configs['db']->query('
        SELECT count(*) 
        FROM {cashboxes_transactions} as ct
        WHERE ct.cashboxes_currency_id_from in (select id FROM {cashboxes_currencies} WHERE cashbox_id=?i) 
        OR ct.cashboxes_currency_id_to in (select id FROM {cashboxes_currencies} WHERE cashbox_id=?i)
        ', array($cashbox['id'], $cashbox['id']))->el();
    }

    /**
     * @param $filters
     * @return array
     */
    private function getProfitMarginCondition($filters)
    {
        $query = '';

        // фильтр по менеджерам
        $mg = array_filter(explode(',', $filters['mg']));
        if (array_key_exists('mg', $filters) && count($mg) > 0) {
            if (count($mg) > 1 || !in_array(-1, $mg)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                    array($query, $mg));
            }
            if (in_array(-1, $mg)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.manager IS NULL',
                    array($query));
            }
        }
        // фильтр по приемщику
        $acp = array_filter(explode(',', $filters['acp']));
        if (array_key_exists('acp', $filters) && count($acp) > 0) {
            if (count($acp) > 1 || !in_array(-1, $acp)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IN (?li)',
                    array($query, $acp));
            }
            if (in_array(-1, $acp)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IS NULL',
                    array($query));
            }
        }
        // фильтр по Инженер
        $eng = array_filter(explode(',', $filters['eng']));
        if (array_key_exists('eng', $filters) && count($eng) > 0) {
            if (count($eng) > 1 || !in_array(-1, $eng)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IN (?li)',
                    array($query, $eng));
            }
            if (in_array(-1, $eng)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IS NULL',
                    array($query));
            }
        }
        // фильтр по статусу
        if (array_key_exists('sts', $filters)) {
            $states = explode(',', $filters['sts']);
            if (count($states) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.status IN (?li)',
                    array($query, $states));
            }
        }
        // фильтр по оператору
        if (array_key_exists('op', $filters) && count(array_filter(explode(',', $filters['op']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['op']))));
        }
        // фильтр по товару
        if (array_key_exists('by_gid', $filters) && $filters['by_gid'] > 0) {
            $cos = $this->all_configs['db']->query('SELECT DISTINCT order_id FROM {orders_goods} WHERE goods_id=?i',
                array(intval($filters['by_gid'])))->vars();
            if (count($cos) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id IN (?li)',
                    array($query, array_keys($cos)));
            } else {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i', array($query, 0));
            }
        }
        // принято через новую почту
        if (array_key_exists('np', $filters) && $filters['np'] == 1) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.np_accept=?i',
                array($query, 1));
        }
        // гарантийный
        if (array_key_exists('wrn', $filters) && $filters['wrn'] == 1 && (!array_key_exists('nowrn',
                    $filters) || $filters['nowrn'] <> 1)
        ) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.repair=?i',
                array($query, 1));
        }
        // негарантийный
        if (array_key_exists('nowrn', $filters) && $filters['nowrn'] == 1 && (!array_key_exists('wrn',
                    $filters) || $filters['wrn'] <> 1)
        ) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.repair<>?i',
                array($query, 1));
        }
        // не учитывать возвраты поставщикам и списания
//        if (array_key_exists('rtrn', $filters) && $filters['rtrn'] == 1) {
        $query = $this->all_configs['db']->makeQuery('?query AND o.type NOT IN (?li)',
            array($query, array(ORDER_RETURN, ORDER_WRITE_OFF)));
//        }
        // не учитывать доставку
        if (array_key_exists('dlv', $filters)) {
            $query = $this->all_configs['db']->makeQuery('?query AND t.type<>?i', array($query, 7));
        }
        // не учитывать комиссию
        if (array_key_exists('cms', $filters)) {
            $query = $this->all_configs['db']->makeQuery('?query AND t.type<>?i', array($query, 6));
        }
        // категория
        if (array_key_exists('dev', $filters) && intval($filters['dev']) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND cg.id=?i',
                array($query, intval($filters['dev'])));
        }
        // только продажи
        if (array_key_exists('sale', $filters) && intval($filters['sale']) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.type=?i',
                array($query, ORDER_SELL));
        }
        // только ремонты
        if (array_key_exists('repair', $filters) && intval($filters['repair']) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.type=?i',
                array($query, ORDER_REPAIR));
        }
        if (array_key_exists('by_cid', $filters) && intval($filters['by_cid']) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.user_id=?i',
                array($query, $filters['by_cid']));
        }
        if (array_key_exists('brands', $filters) && count(array_filter(explode(',', $filters['brands']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.brand_id in (?li)',
                array($query, explode(',', $filters['brands'])));
        }
        return $query;
    }


    /**
     * @param array $filters
     * @return array
     */
    public function profit_margin($filters = array())
    {
        // фильтр по дате
        $day_from = date("1.m.Y 00:00:00");
        $day_to = date("31.m.Y 23:59:59");
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0) {
            $day_from = $filters['df'] . ' 00:00:00';
        }
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0) {
            $day_to = $filters['dt'] . ' 23:59:59';
        }

        $query = $this->getProfitMarginCondition($filters);

        $orders = $this->all_configs['db']->query('
          SELECT o.id as order_id, o.type as order_type, t.type, o.course_value, t.transaction_type,
              SUM(IF(t.transaction_type=2, t.value_to, 0)) as value_to, t.order_goods_id as og_id, o.category_id,
              SUM(IF(t.transaction_type=1, t.value_from, 0)) as value_from, cg.title,
              SUM(IF(t.transaction_type=1, 1, 0)) as has_from, 
              SUM(IF(t.transaction_type=2, 1, 0)) as has_to,
              o.manager, o.accepter as acceptor, o.engineer, o.brand_id,
              SUM(IF(l.contractors_categories_id=2, 1, 0)) as has_return
            FROM {orders} as o
            JOIN {categories} as cg ON cg.id=o.category_id
            JOIN {cashboxes_transactions} as t ON o.id=t.client_order_id
            JOIN (SELECT id, contractors_categories_id, contractors_id FROM {contractors_categories_links}) as l ON l.id=t.contractor_category_link
            WHERE  t.type<>?i AND t.date_transaction BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") 
              ?query GROUP BY order_id ORDER BY o.id',
            array(8, $day_from, $day_to, $query))->assoc('order_id');

        $profit = $turnover = $avg = $purchase = $purchase2 = $sell = $buy = 0;
        if ($orders) {
            $prices = $this->all_configs['db']->query('SELECT i.order_id, SUM(i.price) as price
                FROM {warehouses_goods_items} as i WHERE i.order_id IN (?li) GROUP BY i.order_id',
                array(array_keys($orders)))->vars();

            $goods = array();
            $data = $this->all_configs['db']->query('
                SELECT og.title, og.price, og.order_id, og.`type`, og.goods_id, og.id, og.count, g.percent_from_profit, g.fixed_payment, g.price_purchase
                FROM {orders_goods} og 
                JOIN {goods} g ON og.goods_id=g.id
                WHERE order_id IN (?li)
            ', array(array_keys($orders)))->assoc();

            if ($data) {
                foreach ($data as $p) {
                    $goods[$p['order_id']][$p['type'] == 1 ? 'services' : 'goods'][$p['id']] = $p;
                }
            }
            foreach ($orders as $order_id => &$order) {
                $order['goods'] = isset($goods[$order_id]) && isset($goods[$order_id]['goods']) ? $goods[$order_id]['goods'] : array();
                $order['services'] = isset($goods[$order_id]) && isset($goods[$order_id]['services']) ? $goods[$order_id]['services'] : array();

                $price = ($prices && isset($prices[$order_id])) ? intval($prices[$order_id]) : 0;
                if ($order['order_type'] == ORDER_RETURN) {
                    $orders[$order_id]['turnover'] = $order['value_from'] * ($order['course_value'] / 100);
                } else {
                    $orders[$order_id]['turnover'] = $order['value_to'] - $order['value_from'] * ($order['course_value'] / 100);
                }

                $course = $order['course_value'] / 100;
                $orders[$order_id]['purchase'] = $price * ($course > 0 ? $course : 1);
                $orders[$order_id]['profit'] = 0;
                if ($order['has_to'] > 0 && $orders[$order_id]['turnover'] > 0) {
                    $orders[$order_id]['profit'] = $order['value_to'];
                }
                if ($order['has_from'] > 0 && $orders[$order_id]['turnover'] > 0) {
                    $orders[$order_id]['profit'] -= ($order['value_from'] * $order['course_value'] / 100);
                }
                if ($order['order_type'] != ORDER_RETURN) {
                    $orders[$order_id]['profit'] -= $orders[$order_id]['purchase'];
                }

                $orders[$order_id]['avg'] = 0;
                if ($orders[$order_id]['purchase'] == 0) {
                    $orders[$order_id]['avg'] = '&infin;';
                }
                if ($orders[$order_id]['purchase'] > 0 && $orders[$order_id]['turnover'] > 0) {
                    $orders[$order_id]['avg'] = $orders[$order_id]['profit'] / $orders[$order_id]['purchase'] * 100;
                }

                $sell += $order['value_to'];
                $buy += $order['value_from'];
                $purchase += $orders[$order_id]['purchase'];
                $turnover += max(0, $orders[$order_id]['turnover']);
                $profit += $orders[$order_id]['profit'];
                $purchase2 += ($orders[$order_id]['turnover'] > 0 ? $orders[$order_id]['purchase'] : 0);
            }
        }

        if ($purchase2 == 0) {
            $avg = '&infin;';
        }
        if ($purchase2 > 0 && $turnover > 0) {
            $avg = ($profit) / $purchase2 * 100;
        }

        return array(
            'profit' => $profit,
            'turnover' => $turnover,
            'avg' => $avg,
            'purchase' => $purchase,
            'purchase2' => $purchase2,
            'sell' => $sell,
            'orders' => $orders,
        );

    }

    /**
     * @param array $post
     * @return array
     */
    private function orderFilters(array $post)
    {
        $url = array();

        // фильтр по дате
        if (isset($post['date']) && !empty($post['date'])) {
            list($df, $dt) = explode('-', $post['date']);
            $url['df'] = urlencode(trim($df));
            $url['dt'] = urlencode(trim($dt));
        }

        if (isset($post['cashless']) && is_numeric($post['cashless'])) {
            $url['cashless'] = intval($post['cashless']);
        }


        if (isset($post['categories']) && $post['categories'] > 0) {
            // фильтр по категориям товаров
            $url['g_cg'] = intval($post['categories']);
        }

        if (isset($post['goods']) && $post['goods'] > 0) {
            // фильтр по товару
            $url['by_gid'] = intval($post['goods']);
        }

        if (isset($post['managers']) && !empty($post['managers'])) {
            // фильтр по менеджерам
            $url['mg'] = implode(',', $post['managers']);
        }

        if (isset($post['accepters']) && !empty($post['accepters'])) {
            // фильтр по менеджерам
            $url['acp'] = implode(',', $post['accepters']);
        }
        if (isset($post['states']) && !empty($post['states'])) {
            // фильтр по статусам
            $url['sts'] = implode(',', $post['states']);
        }

        if (isset($post['engineers']) && !empty($post['engineers'])) {
            // фильтр по менеджерам
            $url['eng'] = implode(',', $post['engineers']);
        }

        if (isset($post['suppliers']) && !empty($post['suppliers'])) {
            // фильтр по поставщикам
            $url['sp'] = implode(',', $post['suppliers']);
        }

        if (isset($post['client-order_id']) && !empty($post['client-order_id'])) {
            // фильтр по поставщикам
            if (preg_match('/^[zZ]-/', trim($post['client-order_id'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($post['client-order_id']));
            } else {
                $orderId = trim($post['client-order_id']);
            }
            $url['co_id'] = intval($orderId);
        }

        if (isset($post['status']) && !empty($post['status'])) {
            // фильтр по статусу
            $url['st'] = implode(',', $post['status']);
        }

        if (isset($post['client-order']) && !empty($post['client-order'])) {
            // фильтр клиенту/заказу
            $url['co'] = trim($post['client-order']);
        }

        if (isset($post['categories-last']) && intval($post['categories-last']) > 0) {
            // фильтр категория
            $url['dev'] = intval($post['categories-last']);
        }

        if (isset($post['g_categories']) && !empty($post['g_categories'])) {
            // фильтр по категориям товаров
            $url['g_cg'] = implode(',', $post['g_categories']);
        }

        if (isset($post['operators']) && !empty($post['operators'])) {
            // фильтр по операторам
            $url['op'] = implode(',', $post['operators']);
        }

        if (!isset($post['commission'])) {
            // фильтр по комиссии
            $url['cms'] = 1;
        }

        if (isset($post['novaposhta'])) {
            // фильтр по доставке
            $url['np'] = 1;
        }

        if (isset($post['warranties'])) {
            // фильтр по доставке
            $url['wrn'] = 1;
        }

        if (isset($post['nowarranties'])) {
            // фильтр по доставке
            $url['nowrn'] = 1;
        }

        if (isset($post['return'])) {
            // фильтр по доставке
            $url['rtrn'] = 1;
        }
        if (isset($post['sale'])) {
            // фильтр по доставке
            $url['sale'] = 1;
        }
        if (isset($post['repair'])) {
            // фильтр по доставке
            $url['repair'] = 1;
        }
        if (isset($post['clients'])) {
            // фильтр по доставке
            $url['by_cid'] = intval($post['clients']);
        }
        if (isset($post['brands']) && count($post['brands']) > 0) {
            $url['brands'] = implode(',', $post['brands']);
        }

        return $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url));
    }

    /**
     * @param $order
     * @param $user
     * @return int
     */
    private function calculateSaleProfit($order, $user)
    {
        switch (true) {
            case $user['use_fixed_payment']:
                $profit = $this->calculateSaleProfitWith(MGoods::FIXED_PAYMENT, $order);
                break;
            case $user['use_percent_from_profit']:
                $profit = $this->calculateSaleProfitWith(MGoods::PERCENT_FROM_PROFIT, $order);
                break;
            default:
                $profit = array(
                    'value' => $order['profit'],
                    'detailed' => array()
                );
        }
        return $profit;
    }

    /**
     * @param $order
     * @param $user
     * @return array
     */
    private function calculateRepairProfit($order, $user)
    {
        switch (true) {
            case $user['use_fixed_payment']:
                $profit = $this->calculateRepairProfitWith(MGoods::FIXED_PAYMENT, $order);
                break;
            case $user['use_percent_from_profit']:
                $profit = $this->calculateRepairProfitWith(MGoods::PERCENT_FROM_PROFIT, $order);
                break;
            default:
                $profit = array(
                    'value' => $order['profit'],
                    'detailed' => array()
                );
        }
        return $profit;
    }

    /**
     * @param $with
     * @param $order
     * @return array
     */
    private function calculateSaleProfitWith($with, $order)
    {
        $profit = array(
            'value' => 0,
            'detailed' => array()
        );
        foreach ($order['goods'] as $good) {
            if ($with == MGoods::FIXED_PAYMENT) {
                $value = $good['count'] * $good['fixed_payment'];
                $profit['value'] += $value;
                for ($i = $good['count']; $i > 0; $i--) {
                    $profit['detailed'][] = array(
                        'order_id' => $order['order_id'],
                        'product' => $good['title'],
                        'cost_price' => $good['price_purchase'] * $order['course_value'],
                        'selling_price' => $good['price'],
                        'salary' => $value,
                        'percent' => 0
                    );
                }
            }
            if ($with == MGoods::PERCENT_FROM_PROFIT) {
                $value = ($good['price'] - $good['price_purchase'] * $order['course_value']) * $good['percent_from_profit'] / 100;
                $profit['value'] += $good['count'] * $value;

                for ($i = $good['count']; $i > 0; $i--) {
                    $profit['detailed'][] = array(
                        'order_id' => $order['order_id'],
                        'product' => $good['title'],
                        'cost_price' => $good['price_purchase'] * $order['course_value'],
                        'selling_price' => $good['price'],
                        'salary' => $value,
                        'percent' => $good['percent_from_profit']
                    );
                }
            }
        }
        return $profit;
    }

    /**
     * @param $with
     * @param $order
     * @return array
     */
    private function calculateRepairProfitWith($with, $order)
    {
        $profit = array(
            'value' => 0,
            'detailed' => array()
        );
        foreach ($order['services'] as $service) {
            if ($with == MGoods::FIXED_PAYMENT) {
                $value = $service['count'] * $service['fixed_payment'];
                $profit['value'] += $value;
                for ($i = $service['count']; $i > 0; $i--) {
                    $profit['detailed'][] = array(
                        'order_id' => $order['order_id'],
                        'product' => $service['title'],
                        'cost_price' => $service['fixed_payment'],
                        'selling_price' => $service['fixed_payment'],
                        'salary' => $service['fixed_payment'],
                        'percent' => 0
                    );
                }
            }
            if ($with == MGoods::PERCENT_FROM_PROFIT) {
                $value = $order['profit'] * $service['percent_from_profit'] / 100;
                $profit['value'] += $service['count'] * $value;
                for ($i = $service['count']; $i > 0; $i--) {
                    $profit['detailed'][] = array(
                        'order_id' => $order['order_id'],
                        'product' => $service['title'],
                        'cost_price' => $value,
                        'selling_price' => $value,
                        'salary' => $value,
                        'percent' => $service['percent_from_profit']
                    );
                }
            }
        }
        return $profit;
    }
}