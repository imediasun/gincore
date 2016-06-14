<?php

require_once __DIR__ . '/Core/Object.php';
require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/Core/Exceptions.php';

/**
 * @property  MContractors             Contractors
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
        $this->currency_suppliers_orders = $this->all_configs['settings']['currency_suppliers_orders'];
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
    public function get_transactions(
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
        if ($contractors) {
            $fieldsQuery = $this->all_configs['db']->makeQuery('t.transaction_id, t.item_id, IFNULL(t.supplier_order_id, UUID()) as unq_supplier_order_id',
                array());
            $transactionTable = 'contractors_transactions';
        } else {
            $fieldsQuery = $this->all_configs['db']->makeQuery('t.chain_id, IFNULL(t.client_order_id, UUID()) as unq_client_order_id',
                array());
            $transactionTable = 'cashboxes_transactions';
        }

        if (empty($this->all_configs['suppliers_orders'])) {

            $all_configs['suppliers_orders'] = new Suppliers($this->all_configs);
            $all_configs['suppliers_orders']->suppliers_orders = $all_configs['settings']['currency_suppliers_orders'];
            $all_configs['suppliers_orders']->currency_clients_orders = $all_configs['settings']['currency_orders'];
        }
        $supplierCurrency = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
        if ($supplierCurrency == $this->all_configs['suppliers_orders']->currency_clients_orders) {

            if ((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) {
                $amountQuery = $this->all_configs['db']->makeQuery('
                    IF((t.transaction_type=1 OR t.transaction_type=3), -t.value_from, 0) as value_from,
                    IF((t.transaction_type=2 OR t.transaction_type=3), t.value_to, 0) as value_to,
                    IF(cc_from.currency =?i, 0, 0) as value_from_sc,
                    IF(cc_to.currency =?i, 0, 0) as value_to_sc
            ', array($supplierCurrency, $supplierCurrency));
            } else {
                if ($contractors) {
                    $amountQuery = $this->all_configs['db']->makeQuery('
                SUM(IF((t.transaction_type=1 OR t.transaction_type=3), -t.value_from, 0)) as value_from,
                SUM(IF((t.transaction_type=2 OR t.transaction_type=3), t.value_to, 0)) as value_to,
                SUM(IF(cc_from.currency =?i, 0, 0)) as value_from_sc,
                SUM(IF(cc_to.currency =?i, 0, 0)) as value_to_sc,
                COUNT(t.id) as count_t ', array($supplierCurrency, $supplierCurrency));
                } else {
                    $amountQuery = $this->all_configs['db']->makeQuery('
                SUM(IF((t.transaction_type=1 OR t.transaction_type=3), -t.value_from, 0)) as value_from,
                SUM(IF((t.transaction_type=2 OR t.transaction_type=3), t.value_to, 0)) as value_to,
                SUM(IF(cc_from.currency =?i, 0, 0)) as value_from_sc,
                SUM(IF(cc_to.currency =?i, 0, 0)) as value_to_sc,
                COUNT(t.id) as count_t ',
                        array($supplierCurrency, $supplierCurrency));

                }
            }
        } else {
            if ((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) {
                $amountQuery = $this->all_configs['db']->makeQuery('
                    IF(NOT cc_from.currency =?i, -t.value_from, 0) as value_from,
                    IF(NOT cc_to.currency =?i, t.value_to, 0) as value_to,
                    IF(cc_from.currency =?i, -t.value_from, 0) as value_from_sc,
                    IF(cc_to.currency =?i, t.value_to, 0) as value_to_sc
            ', array($supplierCurrency, $supplierCurrency, $supplierCurrency, $supplierCurrency));
            } else {
                if ($contractors) {
                    $amountQuery = $this->all_configs['db']->makeQuery('
                SUM(IF((t.transaction_type=1 OR t.transaction_type=3), -t.value_from, 0)) as value_from,
                SUM(IF((t.transaction_type=2 OR t.transaction_type=3), t.value_to, 0)) as value_to,
                SUM(IF(cc_from.currency =?i, 0, 0)) as value_from_sc,
                SUM(IF(cc_to.currency =?i, 0, 0)) as value_to_sc,
                COUNT(t.id) as count_t ', array($supplierCurrency, $supplierCurrency));
                } else {
                    $amountQuery = $this->all_configs['db']->makeQuery('
                SUM(IF(NOT cc_from.currency =?i, -t.value_from, 0)) as value_from,
                SUM(IF(NOT cc_to.currency =?i, t.value_to, 0)) as value_to,
                SUM(IF(cc_from.currency =?i, -t.value_from, 0)) as value_from_sc,
                SUM(IF(cc_to.currency =?i, t.value_to, 0)) as value_to_sc,
                COUNT(t.id) as count_t ',
                        array($supplierCurrency, $supplierCurrency, $supplierCurrency, $supplierCurrency));

                }
            }
        }
        $transactions = $this->all_configs['db']->query('SELECT t.id, t.date_transaction, t.comment, t.transaction_type,
                ?query,

                t.cashboxes_currency_id_from,
                t.cashboxes_currency_id_to,

                cc_from.id as cc_from_id,
                cc_to.id as cc_to_id,
                cc_from.currency as cc_from_currency,
                cc_to.currency as cc_to_currency,
                cb_from.name as cc_from_name,
                cb_to.name as cc_to_name,
                cc_from.cashbox_id as cc_from_cashbox_id,
                cc_to.cashbox_id as cc_to_cashbox_id,

                ct.name as category_name, c.title as contractor_name, c.id as contractor_id,
                t.user_id, u.email, u.fio, t.supplier_order_id, t.client_order_id, 
                ?query
                FROM ?t as t
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_from ON cc_from.id=t.cashboxes_currency_id_from
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_to ON cc_to.id=t.cashboxes_currency_id_to
                LEFT JOIN (SELECT name, id FROM {cashboxes})cb_from ON cb_from.id = cc_from.cashbox_id
                LEFT JOIN (SELECT name, id FROM {cashboxes})cb_to ON cb_to.id=cc_to.cashbox_id

                LEFT JOIN (SELECT id, contractors_categories_id, contractors_id FROM {contractors_categories_links})l ON l.id=t.contractor_category_link
                LEFT JOIN (SELECT id, name FROM {contractors_categories})ct ON ct.id=l.contractors_categories_id
                LEFT JOIN (SELECT id, title FROM {contractors})c ON c.id=l.contractors_id
                LEFT JOIN (SELECT id, email, fio FROM {users})u ON u.id=t.user_id
                WHERE ?query 1=1 ' .
            (((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) ? '' : (($contractors == false) ? 'GROUP BY unq_client_order_id' : 'GROUP BY unq_supplier_order_id')) .
            ' ORDER BY DATE(t.date_transaction) DESC, t.id DESC ?query',
            array($amountQuery, $fieldsQuery, $transactionTable, $query_where, $query_end))->assoc();
        if ($transactions) {
            foreach ($transactions as $transaction) {

                if (array_key_exists($transaction['id'], $result)) {
                    if ($transaction['cc_from_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['cc_from_name'],
                            'currency' => $transaction['cc_to_currency'],
                            'cashbox_id' => $transaction['cc_from_cashbox_id'],
                        );
                    }
                    if ($transaction['cc_to_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['cc_to_name'],
                            'currency' => $transaction['cc_to_currency'],
                            'cashbox_id' => $transaction['cc_to_cashbox_id'],
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
                        'value_from_sc' => isset($transaction['value_from_sc']) ? $transaction['value_from_sc'] : 0,
                        'value_to_sc' => isset($transaction['value_to_sc']) ? $transaction['value_to_sc'] : 0,
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

                    if ($transaction['cc_from_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['cc_from_name'],
                            'currency' => $transaction['cc_from_currency'],
                            'cashbox_id' => $transaction['cc_from_cashbox_id'],
                        );
                    }
                    if ($transaction['cc_to_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['cc_to_name'],
                            'currency' => $transaction['cc_to_currency'],
                            'cashbox_id' => $transaction['cc_to_cashbox_id'],
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
            require_once __DIR__ . '/TransactionShow.php';
            $TransactionShow = new TransactionShow($this->all_configs, $currencies, $contractors,
                $this->currency_suppliers_orders);
            if ($return_array == true) {
                require_once __DIR__ . '/TransactionAsArray.php';

                $out = $TransactionShow->result(new TransactionAsArray($this->all_configs, $currencies, $contractors,
                    $this->currency_suppliers_orders), $transactions);
            } else {
                require_once __DIR__ . '/TransactionAsTable.php';

                $table = $TransactionShow->result(new TransactionAsTable($this->all_configs, $currencies, $contractors,
                    $this->currency_suppliers_orders), $transactions);
                $out = '<div class="out-transaction">';
                if ($show_balace == true) {
                    $out .= $this->balance($query_balance, $contractors, $currencies, $TransactionShow->totals(),
                        $by_day);
                }
                $out .= $table . '</div>';
            }
        } else {
            if ($show_balace == true) {
                $out .= $this->balance($query_balance, $contractors, $currencies, null, $by_day);
            }
            $out .= '<p class="text-danger">' . l('Нет транзакций по Вашему запросу') . '.</p>';
        }

        return $out;
    }

    /**
     * @param      $order
     * @param null $title
     * @param bool $link
     * @return null|string
     */
    public function supplier_order_number($order, $title = null, $link = true)
    {
        return supplier_order_number($order, $title, $link);
    }

    /**
     * @param $transaction
     * @param $contractors
     * @return bool
     */
    public function use_s_value($transaction, $contractors)
    {
        return ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders && !$contractors && $this->currency_suppliers_orders != $this->all_configs['suppliers_orders']->currency_clients_orders);
    }
}
