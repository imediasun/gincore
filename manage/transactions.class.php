<?php

require_once __DIR__ . '/Core/Object.php';
require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/Core/Exceptions.php';

/**
 * @property  MContractors Contractors
 * @property  MContractorsTransactions ContractorsTransactions
 */
class Transactions extends Object
{
    protected $all_configs;

    public $currencies = null;

    public $currency_suppliers_orders; // валюта заказов поставщикам
    /** @var View */
    protected $view;
    public $uses = array(
        'Contractors',
        'ContractorsTransactions',
    );

    /**
     * Suppliers constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);
        $this->currency_suppliers_orders = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
        $this->applyUses();
    }

    /**
     * транзакция контрагенту
     * @param $data
     */
    public function add_contractors_transaction($data)
    {
        $array = array(
            'transaction_type' => array_key_exists('transaction_type', $data) ? $data['transaction_type'] : 0,
            'cashboxes_currency_id_from' => array_key_exists('cashboxes_currency_id_from',
                $data) ? $data['cashboxes_currency_id_from'] : null,
            'cashboxes_currency_id_to' => array_key_exists('cashboxes_currency_id_to',
                $data) ? $data['cashboxes_currency_id_to'] : null,
            'value_from' => array_key_exists('value_from', $data) ? $data['value_from'] : 0,
            'value_to' => array_key_exists('value_to', $data) ? $data['value_to'] : 0,
            'comment' => array_key_exists('comment', $data) ? $data['comment'] : '',
            'contractor_category_link' => array_key_exists('contractor_category_link',
                $data) ? $data['contractor_category_link'] : null,
            'date_transaction' => array_key_exists('date_transaction',
                $data) ? $data['date_transaction'] : date("Y-m-d H:i:s", time()),
            'user_id' => array_key_exists('user_id', $data) ? $data['user_id'] : $_SESSION['id'],
            'supplier_order_id' => array_key_exists('supplier_order_id', $data) ? $data['supplier_order_id'] : null,
            'client_order_id' => array_key_exists('client_order_id', $data) ? $data['client_order_id'] : null,
            'transaction_id' => array_key_exists('transaction_id', $data) ? $data['transaction_id'] : null,
            'item_id' => array_key_exists('item_id', $data) ? $data['item_id'] : null,
            'goods_id' => array_key_exists('goods_id', $data) ? $data['goods_id'] : null,
            'type' => array_key_exists('type', $data) ? $data['type'] : 0,

            'contractors_id' => array_key_exists('contractors_id', $data) ? $data['contractors_id'] : 0,
        );

        // добавляем транзакцию контрагенту
        $id = $this->ContractorsTransactions->insert(array(
            'transaction_type' => $array['transaction_type'],
            'cashboxes_currency_id_from' => $array['cashboxes_currency_id_from'],
            'cashboxes_currency_id_to' => $array['cashboxes_currency_id_to'],
            'value_from' => round((float)$array['value_from'] * 100),
            'value_to' => round((float)$array['value_to'] * 100),
            'comment' => trim($array['comment']),
            'contractor_category_link' => $array['contractor_category_link'],
            'date_transaction' => date("Y-m-d H:i:s", strtotime($array['date_transaction'])),
            'user_id' => $array['user_id'],
            'supplier_order_id' => $array['supplier_order_id'],
            'client_order_id' => $array['client_order_id'],
            'transaction_id' => $array['transaction_id'],
            'item_id' => $array['item_id'],
            'goods_id' => $array['goods_id'],
            'type' => $array['type']

        ));

        $this->set_amount_contractor($data);
    }

    /**
     * списывание/зачисление сумы контрагенту
     * @param $data
     */
    function set_amount_contractor($data)
    {
        $array = array(
            'transaction_type' => array_key_exists('transaction_type', $data) ? $data['transaction_type'] : 0,
            'value_from' => array_key_exists('value_from', $data) ? $data['value_from'] : 0,
            'value_to' => array_key_exists('value_to', $data) ? $data['value_to'] : 0,
            'contractors_id' => array_key_exists('contractors_id', $data) ? $data['contractors_id'] : 0,
        );

        // обновляем суму у контрагента
        // выдача
        if ($array['transaction_type'] == 1) {
            $this->Contractors->decrease('amount', round((float)$array['value_from'] * 100),
                array($this->Contractors->pk() => intval($array['contractors_id'])));
        }
        // внесение
        if ($array['transaction_type'] == 2) {
            $this->Contractors->increase('amount', round((float)$array['value_to'] * 100),
                array($this->Contractors->pk() => intval($array['contractors_id'])));
        }
    }

    /**
     * @param       $currencies
     * @param bool  $by_day
     * @param null  $limit
     * @param bool  $contractors
     * @param array $filters
     * @param bool  $show_balace
     * @param bool  $return_array
     * @return array|string
     */
    function get_transactions(
        $currencies,
        $by_day = false,
        $limit = null,
        $contractors = false,
        $filters = array(),
        $show_balace = true,
        $return_array = false
    ) {
        $result = array();
        $query_end = '';

        if ($by_day == true) {
            // сегодня
            $day = date("d.m.Y", time());
            if (isset($_GET['d']) && !empty($_GET['d'])) {
                $days = explode('-', $_GET['d']);
                $day = $days[0];
            }
            $query_where = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")=? AND',
                array($day));
            $query_balance = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")<? AND',
                array($day));
        } else {
            // текущий месяц
            $day_from = 1 . date(".m.Y", time()) . ' 00:00:00';
            $day_before = 1 . date(".m.Y", time());
            $day_to = 31 . date(".m.Y", time()) . ' 23:59:59';

            if (isset($_GET['df']) && !empty($_GET['df'])) {
                $day_from = $_GET['df'] . ' 00:00:00';
                $day_before = $_GET['df'];
            }

            if (isset($_GET['dt']) && !empty($_GET['dt'])) {
                $day_to = $_GET['dt'] . ' 23:59:59';
            }

            $query_where = $this->all_configs['db']->makeQuery('t.date_transaction BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
                        AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") AND',
                array($day_from, $day_to));
            $query_balance = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")<? AND',
                array($day_before));

            // фильтры вручную
            if (count($filters) > 0) {
                $query_where = '';

                if (array_key_exists('supplier_order_id', $filters) && $filters['supplier_order_id'] > 0) {
                    $query_where = $this->all_configs['db']->makeQuery('t.supplier_order_id=?i AND ?query',
                        array($filters['supplier_order_id'], $query_where));
                }
            }

            // фильтр по категориям
            if (isset($_GET['cg']) && !empty($_GET['cg'])) {
                // исключающее
                if (isset($_GET['cge']) && $_GET['cge'] == -1) {
                    $query_where = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li))) AND',
                        array($query_where, explode(',', $_GET['cg'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li))) AND',
                        array($query_balance, explode(',', $_GET['cg'])));
                } else {
                    $query_where = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li)) AND',
                        array($query_where, explode(',', $_GET['cg'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li)) AND t.transaction_type<>3 AND',
                        array($query_balance, explode(',', $_GET['cg'])));
                }
            }

            // фильтр по контрагентам
            if (isset($_GET['ct']) && !empty($_GET['ct'])) {
                // исключающее
                if (isset($_GET['cte']) && $_GET['cte'] == -1) {
                    $query_where = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li))) AND',
                        array($query_where, explode(',', $_GET['ct'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li))) AND',
                        array($query_balance, explode(',', $_GET['ct'])));
                } else {
                    $query_where = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li)) AND',
                        array($query_where, explode(',', $_GET['ct'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li)) AND t.transaction_type<>3 AND',
                        array($query_balance, explode(',', $_GET['ct'])));
                }
            }

            // фильтр по заказку поставщика
            if (isset($_GET['s_id']) && $_GET['s_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.supplier_order_id=?i AND',
                    array($query_where, $_GET['s_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.supplier_order_id=?i AND',
                    array($query_balance, $_GET['s_id']));
            }

            // фильтр по заказку клиента
            if (isset($_GET['o_id']) && $_GET['o_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.client_order_id=?i AND',
                    array($query_where, $_GET['o_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.client_order_id=?i AND',
                    array($query_balance, $_GET['o_id']));
            }

            // фильтр по кассам
            if (isset($_GET['cb']) && !empty($_GET['cb'])) {
                // исключающее
                if (isset($_GET['cbe']) && $_GET['cbe'] == -1) {
                    $query_balance = $this->all_configs['db']->makeQuery('?query
                            ((cc_to.cashbox_id NOT IN (?li) OR cc_to.cashbox_id IS NULL) AND
                            (cc_from.cashbox_id NOT IN (?li) OR cc_from.cashbox_id IS NULL)) AND',
                        array($query_balance, explode(',', $_GET['cb']), explode(',', $_GET['cb'])));
                } else {
                    $query_balance = $this->all_configs['db']->makeQuery('?query
                            (cc_to.cashbox_id IN (?li) OR cc_from.cashbox_id IN (?li)) AND',
                        array($query_balance, explode(',', $_GET['cb']), explode(',', $_GET['cb'])));
                }
            }
        }

        // лимит
        if ($limit != null && $limit > 0) {
            $query_end = $this->all_configs['db']->makeQuery('?query LIMIT ?i', array($query_end, $limit));
        }

        // какую табличку доставать
        if ($contractors == false) {
            // фильтр по транзакции касс
            if (isset($_GET['t_id']) && $_GET['t_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.id=?i AND',
                    array($query_where, $_GET['t_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.id=?i AND',
                    array($query_balance, $_GET['t_id']));
            }
        } else {
            // фильтр по транзакции касс
            if (isset($_GET['t_id']) && $_GET['t_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.transaction_id=?i AND',
                    array($query_where, $_GET['t_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.transaction_id=?i AND',
                    array($query_balance, $_GET['t_id']));
            }
        }

        // все транзакции
        $transactions = $this->all_configs['db']->query('SELECT t.id, t.date_transaction, t.comment, t.transaction_type, '
            . (((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) ?
                'IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0) as value_from,
                        IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0) as value_to, '
                : 'SUM(IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0)) as value_from,
                            SUM(IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0)) as value_to, COUNT(t.id) as count_t, ')
            . 't.cashboxes_currency_id_from, t.cashboxes_currency_id_to, cc.currency, cb.name, cc.id as c_id,
                    cc.cashbox_id, ct.name as category_name, c.title as contractor_name, c.id as contractor_id,
                    t.user_id, u.email, u.fio, t.supplier_order_id, t.client_order_id,
                    ' . ($contractors == true ?
                't.transaction_id, t.item_id, IFNULL(t.supplier_order_id, UUID()) as unq_supplier_order_id' :
                't.chain_id, IFNULL(t.client_order_id, UUID()) as unq_client_order_id') . '
                FROM {' . ($contractors == false ? 'cashboxes_transactions' : 'contractors_transactions') . '} as t
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc ON (cc.id=t.cashboxes_currency_id_from || cc.id=t.cashboxes_currency_id_to)
                LEFT JOIN (SELECT name, id FROM {cashboxes})cb ON cb.id=cc.cashbox_id
                LEFT JOIN (SELECT id, contractors_categories_id, contractors_id FROM {contractors_categories_links})l ON l.id=t.contractor_category_link
                LEFT JOIN (SELECT id, name FROM {contractors_categories})ct ON ct.id=l.contractors_categories_id
                LEFT JOIN (SELECT id, title FROM {contractors})c ON c.id=l.contractors_id
                LEFT JOIN (SELECT id, email, fio FROM {users})u ON u.id=t.user_id
                WHERE ?query 1=1 '
            . (((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) ? '' :
                (($contractors == false) ? 'GROUP BY unq_client_order_id' : 'GROUP BY unq_supplier_order_id'))
            . ' ORDER BY DATE(t.date_transaction) DESC, t.id DESC ?query',
            array($query_where, $query_end))->assoc();

        if ($transactions) {
            foreach ($transactions as $transaction) {

                if (array_key_exists($transaction['id'], $result)) {
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                } else {
                    $result[$transaction['id']] = array(
                        'date_transaction' => $transaction['date_transaction'],
                        'comment' => $transaction['comment'],
                        'email' => $transaction['email'],
                        'fio' => $transaction['fio'],
                        'user_id' => $transaction['user_id'],
                        'category_name' => $transaction['category_name'],
                        'contractor_name' => $transaction['contractor_name'],
                        'transaction_type' => $transaction['transaction_type'],
                        'value_from' => $transaction['value_from'],
                        'value_to' => $transaction['value_to'],
                        'cashboxes_currency_id_from' => $transaction['cashboxes_currency_id_from'],
                        'cashboxes_currency_id_to' => $transaction['cashboxes_currency_id_to'],
                        'client_order_id' => $transaction['client_order_id'],
                        'supplier_order_id' => $transaction['supplier_order_id'],
                        'transaction_id' => array_key_exists('transaction_id',
                            $transaction) ? $transaction['transaction_id'] : '',
                        'item_id' => array_key_exists('item_id', $transaction) ? $transaction['item_id'] : '',
                        'chain_id' => array_key_exists('chain_id', $transaction) ? $transaction['chain_id'] : '',
                        'contractor_id' => $transaction['contractor_id'],
                        'count_t' => array_key_exists('count_t', $transaction) ? $transaction['count_t'] : '',
                    );

                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                }

                // фильтра по кассам
                if (isset($_GET['cb']) && !empty($_GET['cb']) && $by_day == false) {
                    // исключающее
                    if (isset($_GET['cbe']) && $_GET['cbe'] == -1) {
                        if (($transaction['transaction_type'] == 1 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) !== false
                            ) ||
                            ($transaction['transaction_type'] == 2 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) !== false
                            ) ||
                            ($transaction['transaction_type'] == 3 &&
                                array_key_exists($transaction['cashboxes_currency_id_from'],
                                    $result[$transaction['id']]['cashboxes']) &&
                                array_key_exists($transaction['cashboxes_currency_id_to'],
                                    $result[$transaction['id']]['cashboxes']) &&
                                (array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'],
                                        explode(',', $_GET['cb'])) !== false
                                    ||
                                    array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'],
                                        explode(',', $_GET['cb'])) !== false)
                            )
                        ) {
                            unset($result[$transaction['id']]);
                        }
                    } else {
                        if (($transaction['transaction_type'] == 1 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) === false
                            ) ||
                            ($transaction['transaction_type'] == 2 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) === false
                            ) ||
                            ($transaction['transaction_type'] == 3 &&
                                array_key_exists($transaction['cashboxes_currency_id_from'],
                                    $result[$transaction['id']]['cashboxes']) &&
                                array_key_exists($transaction['cashboxes_currency_id_to'],
                                    $result[$transaction['id']]['cashboxes']) &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) === false
                                &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'],
                                    explode(',', $_GET['cb'])) === false
                            )
                        ) {
                            unset($result[$transaction['id']]);
                        }
                    }
                }
            }
        }

        return $this->show_transactions($result, $currencies, $contractors, $by_day, $query_balance, $show_balace,
            $return_array);
    }

    /**
     * @param      $query_balance
     * @param      $contractors
     * @param      $currencies
     * @param null $balance
     * @param bool $by_day
     * @return string
     */
    function balance($query_balance, $contractors, $currencies, $balance = null, $by_day = false)
    {
        // достаем суммы транзакций по валютам
        $balances_begin = $this->all_configs['db']->query('SELECT cc_to.currency as currency_to,
                    cc_from.currency as currency_from, t.transaction_type,
                    IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0) as value_from,
                    IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0) as value_to

                FROM {' . ($contractors == false ? 'cashboxes_transactions' : 'contractors_transactions') . '} as t
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_to ON cc_to.id=t.cashboxes_currency_id_to
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_from ON cc_from.id=t.cashboxes_currency_id_from
                WHERE ?query ((t.transaction_type=3 && cc_to.id IS NOT NULL && cc_from.id IS NOT NULL) || t.transaction_type<>3)',
            array($query_balance))->assoc();

        $balance_begin = array();
        if ($balances_begin && is_array($balances_begin)) {
            foreach ($balances_begin as $b) {
                if ($b['currency_to'] != 0) {
                    if (!array_key_exists($b['currency_to'], $balance_begin)) {
                        $balance_begin[$b['currency_to']] = 0;
                    }
                    $balance_begin[$b['currency_to']] += $b['value_to'];
                }
                if ($b['currency_from'] != 0) {
                    if (!array_key_exists($b['currency_from'], $balance_begin)) {
                        $balance_begin[$b['currency_from']] = 0;
                    }
                    $balance_begin[$b['currency_from']] += $b['value_from'];
                }
            }
        }
        $balance_html = '<div class="well">';
        if ($by_day == false) {
            $transaction_type = $contractors == false ? 'cashboxes_transactions' : 'contractors_transactions';
            $onclick = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/export/?act=' . $transaction_type . '&' . get_to_string();
            $balance_html .= '<a class="btn btn-default pull-right" target="_blank" href="' . $onclick . '">' . l('Выгрузить') . '</a>';
        }
        $balance_html .= '<p>' . l('Баланс на начало периода') . ': ';
        ksort($balance_begin, SORT_NUMERIC);
        $balance_begin_html = '';
        foreach ($balance_begin as $c => $b) {
            if ($b != 0) {
                $balance_begin_html .= show_price($b) . ' ' . $currencies[$c]['shortName'] . ', ';
            }
        }
        $balance_html .= empty($balance_begin_html) ? '0, ' : $balance_begin_html;
        $date_from = isset($_GET['df']) ? date('Y-m-01 00:00:00', strtotime($_GET['df'])) : date('Y-m-01 00:00:00');
        $balance_html .= l('Дата начала периода') . ': <span title="' . do_nice_date($date_from, false,
                false) . '">' . do_nice_date($date_from, true, false) . '</span>';
        $balance_html .= '</p><p>' . l('Баланс на конец периода') . ': ';
        $balance_end_html = '';
        if ($balance && is_array($balance)) {
            ksort($balance, SORT_NUMERIC);
            foreach ($balance as $k => $b) {
                if ($b != 0) {
                    if (array_key_exists($k, $balance_begin)) {
                        $balance_end_html .= show_price($b + $balance_begin[$k]) . ' ' . $currencies[$k]['shortName'] . ', ';
                    } else {
                        $balance_end_html .= show_price($b) . ' ' . $currencies[$k]['shortName'] . ', ';
                    }
                }
            }
        } else {
            $balance_end_html = $balance_begin_html;
        }
        $balance_html .= empty($balance_end_html) ? '0, ' : $balance_end_html;
        $date_to = isset($_GET['dt']) ? date('Y-m-t 23:59:59', strtotime($_GET['dt'])) : date('Y-m-t 23:59:59');
        $balance_html .= l('Дата конца периода') . ': <span title="' . do_nice_date($date_to, false,
                false) . '">' . do_nice_date($date_to, true, false) . '</span>';
        $balance_html .= '</p></div>';

        return $balance_html;
    }


    /**
     * @param $transactions
     * @param $currencies
     * @param $contractors
     * @param $by_day
     * @param $query_balance
     * @param $show_balace
     * @param $return_array
     * @return array|string
     */
    function show_transactions(
        $transactions,
        $currencies,
        $contractors,
        $by_day,
        $query_balance,
        $show_balace,
        $return_array
    ) {
        $out = '';

        if ($transactions) {
            if ($return_array == true) {
                $out = array();

                $total = $total_inc = $total_exp = $total_tr_inc = $total_tr_exp =/* $balance =*/
                    array_fill_keys(array_keys($currencies), '');
                foreach ($transactions as $transaction_id => $transaction) {
                    //$sum = 'Неизвестный перевод';
                    $cashbox_info = 'Неизвестная операция';
                    $exp = $inc = 0;

                    // без группировки
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] == 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' -> ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] == 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' <- ' . $transaction['category_name'];
                        // сумма
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // перевод
                    if ($transaction['transaction_type'] == 3 ) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        $cashbox_info .= ' -> ';
                        // в кассу
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // группировано
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] > 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' -> ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
                        } else {
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] > 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' <- ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    $group = $transaction['count_t'] . ' транз.';

                    $out[$transaction_id]['id'] = $transaction_id;
                    $out[$transaction_id]['Дата'] = $transaction['date_transaction'];
                    $out[$transaction_id]['Касса'] = ($transaction['count_t'] > 1 ? $group : $cashbox_info);
                    $out[$transaction_id]['Контрагент'] = $transaction['contractor_name'];
                    if ($transaction['client_order_id'] > 0) {
                        $out[$transaction_id]['Заказ клиента'] = $transaction['client_order_id'];
                    }
                    $out[$transaction_id]['Заказ поставщика'] = ($transaction['supplier_order_id'] > 0 ? $this->all_configs['suppliers_orders']->supplier_order_number(array('id' => $transaction['supplier_order_id'])) : '');
                    if ($contractors == true) {
                        $out[$transaction_id]['Транзакция'] = $transaction['transaction_id'];
                        $out[$transaction_id]['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $inc;
                        $out[$transaction_id]['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $exp;

                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out[$transaction_id]['Серийник'] = $group;
                        } else {
                            if ($transaction['item_id'] > 0) {
                                $item = $this->all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i',
                                    array($transaction['item_id']))->row();
                                $out[$transaction_id]['Серийник'] = suppliers_order_generate_serial($item, true, true);
                            }
                        }
                    } else {
                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out[$transaction_id][''] = $group;
                        } else {
                            $out[$transaction_id]['Цепочка'] = $transaction['chain_id'];
                        }
                        $out[$transaction_id]['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $inc;
                        $out[$transaction_id]['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $exp;
                    }
                    $out[$transaction_id]['Ответственный'] = get_user_name($transaction);
                    $out[$transaction_id]['Примечание'] = $transaction['comment'];
                }
                // итого
            } else {
                $out .= '<div class="out-transaction"><table class="table table-striped table-compact"><thead><tr><td></td><td>' . l('Дата') . '</td>';
                $out .= '<td>' . l('Касса') . '</td><td>' . l('Контрагент') . '</td><td>' . l('Заказ клиента') . '</td><td>' . l('Заказ поставщика') . '</td>';
                if ($contractors == true) {
                    $out .= '<td>' . l('Транзакция') . '</td><td>' . l('Доход') . '</td><td>' . l('Расход') . '</td><td>' . l('Серийник') . '</td>';
                } else {
                    $out .= '<td>' . l('Цепочка') . ' ' . InfoPopover::getInstance()->createQuestion('l_transaction_chain_info') . '</td><td>' . l('Доход') . ' ' . InfoPopover::getInstance()->createQuestion('l_transaction_income_info') . '</td><td>' . l('Расход') . ' ' . InfoPopover::getInstance()->createQuestion('l_transaction_expence_info') . '</td>';
                }
                $out .= '<td>' . l('Ответственный') . '</td><td>' . l('Примечание') . '</td></tr></thead><tbody>';
                $total = $total_inc = $total_exp = $total_tr_inc = $total_tr_exp =/* $balance =*/
                    array_fill_keys(array_keys($currencies), '');
                foreach ($transactions as $transaction_id => $transaction) {
                    //$sum = 'Неизвестный перевод';
                    $cashbox_info = l('Неизвестная операция');
                    $exp = $inc = 0;

                    // без группировки
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] == 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' &rarr; ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] == 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' &larr; ' . $transaction['category_name'];
                        // сумма
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // перевод
                    if ($transaction['transaction_type'] == 3 /*&& $transaction['count_t'] == 0*/) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        $cashbox_info .= ' &rarr; ';
                        // в кассу
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // группировано
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] > 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' &rarr; ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                                $currencies)
                        ) {
                            $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
                        } else {
                            $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] > 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes'])
                        ) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' &larr; ' . $transaction['category_name'];
                        // сумма
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes',
                                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                                $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                                $currencies)
                        ) {
                            $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    $group = '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?';
                    if ($transaction['supplier_order_id'] > 0) {
                        $group .= 's_id=' . $transaction['supplier_order_id'] . '&grp=1#transactions-contractors">(';
                    } else {
                        if ($transaction['client_order_id'] > 0) {
                            $group .= 'o_id=' . $transaction['client_order_id'] . '&grp=1#transactions-cashboxes">(';
                        }
                    }
                    $group .= $transaction['count_t'] . ' транз.)</a>';

                    $out .= '<tr>';
                    $out .= '<td>' . $transaction_id . '</td>';
                    $out .= '<td><span title="' . do_nice_date($transaction['date_transaction'], false,
                            false) . '">' . do_nice_date($transaction['date_transaction'], true,
                            false) . '</span></td>';
                    $out .= '<td>' . ($transaction['count_t'] > 1 ? $group : $cashbox_info) . '</td>';
                    $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?ct=' . $transaction['contractor_id'] . '#transactions-contractors">' . $transaction['contractor_name'] . '</a></td>';
                    $out .= '<td>';
                    if ($transaction['client_order_id'] > 0) {
                        $out .= '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/create/' . $transaction['client_order_id'] . '">№' . $transaction['client_order_id'] . '</a>';
                    }
                    $out .= '</td>';
                    $out .= '<td>' . ($transaction['supplier_order_id'] > 0 ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/edit/' . $transaction['supplier_order_id'] . '#create_supplier_order">' . $this->all_configs['suppliers_orders']->supplier_order_number(array('id' => $transaction['supplier_order_id'])) . '</a>' : '') . '</td>';
                    if ($contractors == true) {
                        $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?t_id=' . $transaction['transaction_id'] . '#transactions-cashboxes">' . $transaction['transaction_id'] . '</td>';
                        $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $inc . '</td>';
                        $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $exp . '</td>';

                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out .= '<td>' . $group . '</td>';
                        } else {
                            if ($transaction['item_id'] > 0) {
                                $item = $this->all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i',
                                    array($transaction['item_id']))->row();
                                $out .= '<td>' . suppliers_order_generate_serial($item, true, true) . '</td>';
                            } else {
                                $out .= '<td></td>';
                            }
                        }
                    } else {
                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out .= '<td>' . $group . '</td>';
                        } else {
                            $out .= '<td>' . $transaction['chain_id'] . '</td>';
                        }
                        $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $inc . '</td>';
                        $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $exp . '</td>';
                    }
                    $out .= '<td>' . get_user_name($transaction) . '</td>';
                    $out .= '<td>' . cut_string($transaction['comment']) . '</td>';
                    $out .= '</tr>';
                }
                // итого
                $out .= '<tr><td colspan="5"></td><td colspan="2">' . l('Итого') . ': ';
                $out_inc = $out_exp = $out_trans = '</td><td>';
                $set = false;
                foreach ($total as $k => $t) {
                    $show = false;
                    if ($total_inc[$k] > 0 || $total_tr_inc[$k] > 0 || $total_exp[$k] < 0 || $total_tr_exp[$k] < 0) {
                        $class = 'data-toggle="tooltip" title="Итого" class="' . ($t > 0 ? 'text-success' : 'text-warning') . '"';
                        $out .= '<br /><span ' . $class . '>' . show_price($t) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                        $set = $show = true;
                    }
                    if ($total_inc[$k] > 0 || $show == true) {
                        $out_inc .= '<br /><span title="Доход" class="text-success">';
                        $out_inc .= show_price($total_inc[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                    }
                    if ($total_tr_inc[$k] > 0 || $total_tr_exp[$k] < 0) {
                        $out_trans .= '<br /><span data-original-title="Перевод" class="popover-info" data-content="';
                        $out_trans .= show_price($total_tr_inc[$k]) . '; ' . show_price($total_tr_exp[$k]);
                        $out_trans .= '">' . show_price($total_tr_inc[$k] + $total_tr_exp[$k]) . '&nbsp;';
                        $out_trans .= $currencies[$k]['shortName'] . '</span>';
                    }
                    if ($total_exp[$k] < 0 || $show == true) {
                        $out_exp .= '<br /><span title="Расход" class="text-warning">';
                        $out_exp .= show_price($total_exp[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                    }
                }
                if ($set == false) {
                    $out .= 0;
                }
                $out .= $out_inc . $out_exp . $out_trans . '</td><td colspan="0"></td></tr>';
                $out .= '</tbody></table>';
                if ($show_balace == true) {
                    $out = $this->balance($query_balance, $contractors, $currencies, $total, $by_day) . $out;
                }
                $out .= '</div>';
            }
        } else {
            if ($show_balace == true) {
                $out .= $this->balance($query_balance, $contractors, $currencies, null, $by_day);
            }
            $out .= '<p class="text-danger">' . l('Нет транзакций по Вашему запросу') . '.</p>';
        }

        return $out;
    }
}
