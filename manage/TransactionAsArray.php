<?php

require_once __DIR__ . '/TransactionAsInterface.php';

class TransactionAsArray implements TransactionAsInterface
{
    protected $currencies;
    protected $total;
    protected $total_inc;
    protected $total_exp;
    protected $total_tr_inc;
    protected $total_tr_exp;
    /**
     * @var
     */
    private $contractors;
    /**
     * @var
     */
    private $all_configs;
    /**
     * @var
     */
    private $currency_suppliers_orders;

    /**
     * TransactionAsArray constructor.
     * @param $all_configs
     * @param $currencies
     * @param $contractors
     * @param $currency_suppliers_orders
     */
    public function __construct(&$all_configs, $currencies, $contractors, $currency_suppliers_orders)
    {
        $this->currencies = $currencies;
        $this->total = $this->total_inc = $this->total_exp = $this->total_tr_inc = $this->total_tr_exp = array_fill_keys(array_keys($currencies),
            '');
        $this->contractors = $contractors;
        $this->all_configs = $all_configs;
        $this->currency_suppliers_orders = $currency_suppliers_orders;
    }

    /**
     * @param array $transactions
     * @return array
     */
    public function result(array $transactions)
    {
        $out = array();

        foreach ($transactions as $transaction_id => $transaction) {
            //$sum = 'Неизвестный перевод';
            $cashbox_info = 'Неизвестная операция';
            $exp = $inc = 0;
            $exp_sc = $inc_sc = 0;

            // без группировки
            // расход
            if ($transaction['transaction_type'] == TRANSACTION_OUTPUT && $transaction['count_t'] == 0) {
                list($cashbox_info, $exp) = $this->outgo($transaction);
            }
            // доход
            if ($transaction['transaction_type'] == TRANSACTION_INPUT && $transaction['count_t'] == 0) {
                list($cashbox_info, $inc) = $this->income($transaction);
            }
            // перевод
            if ($transaction['transaction_type'] == TRANSACTION_TRANSFER) {
                list($cashbox_info, $exp, $inc) = $this->transfer($transaction);
            }
            // группировано
            // расход
            if ($transaction['transaction_type'] == TRANSACTION_OUTPUT && $transaction['count_t'] > 0) {
                list($cashbox_info, $exp, $inc) = $this->outgoGrouped($transaction);
            }
            // доход
            if ($transaction['transaction_type'] == TRANSACTION_INPUT && $transaction['count_t'] > 0) {
                list($cashbox_info, $exp, $inc) = $this->incomeGrouped($transaction);
            }
            $out[$transaction_id] = $this->row($transaction, $transaction_id, $cashbox_info, $inc, $exp);
        }
        return $out;
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function outgo(Array $transaction)
    {
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
        if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
            $exp = show_price($transaction['value_from_sc']);
        } else {
            $exp = show_price($transaction['value_from']);
        }
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                $this->currencies)
        ) {
            $exp .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
        }
        return array($cashbox_info, $exp);
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function income(Array $transaction)
    {
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
                $this->currencies)
        ) {
            $inc .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $inc);
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function transfer(Array $transaction)
    {
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
        if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
            $exp = show_price($transaction['value_from_sc']);
        } else {
            $exp = show_price($transaction['value_from']);
        }
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                $this->currencies)
        ) {
            $exp .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total_tr_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
        }
        $inc = show_price($transaction['value_to']);
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                $this->currencies)
        ) {
            $inc .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function outgoGrouped(Array $transaction)
    {
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
        if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
            $exp = show_price($transaction['value_from_sc']);
            $inc = show_price($transaction['value_to_sc']);
        } else {
            $exp = show_price($transaction['value_from']);
            $inc = show_price($transaction['value_to']);
        }
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'],
                $this->currencies)
        ) {
            $exp .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $inc .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $exp .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $inc .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'] + $transaction['value_from_sc'] + $transaction['value_to_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function incomeGrouped(Array $transaction)
    {
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
        if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
            $exp = show_price($transaction['value_from_sc']);
            $inc = show_price($transaction['value_to_sc']);
        } else {
            $exp = show_price($transaction['value_from']);
            $inc = show_price($transaction['value_to']);
        }
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                $this->currencies)
        ) {
            $inc .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $exp .= ' ' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $exp .= ' ' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'] + $transaction['value_from_sc'] + $transaction['value_to_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @param     $transaction
     * @param     $transaction_id
     * @param     $cashbox_info
     * @param     $inc
     * @param     $exp
     * @param int $inc_sc
     * @param int $exp_sc
     * @return mixed
     */
    public function row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $inc_sc = 0, $exp_sc = 0)
    {
        $row = array();
        $group = $transaction['count_t'] . ' транз.';

        $row['id'] = $transaction_id;
        $row['Дата'] = $transaction['date_transaction'];
        $row['Касса'] = ($transaction['count_t'] > 1 ? $group : $cashbox_info);
        $row['Контрагент'] = $transaction['contractor_name'];
        if ($transaction['client_order_id'] > 0) {
            $row['Заказ клиента'] = $transaction['client_order_id'];
        }
        $row['Заказ поставщика'] = ($transaction['supplier_order_id'] > 0 ? supplier_order_number(array('id' => $transaction['supplier_order_id'])) : '');
        if ($this->contractors) {
            $row['Транзакция'] = $transaction['transaction_id'];
            $row['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $inc;
            $row['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $exp;

            if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                $row['Серийник'] = $group;
            } else {
                if ($transaction['item_id'] > 0) {
                    $row['Серийник'] = suppliers_order_generate_serial_by_id($transaction['item_id'], true, true);
                }
            }
        } else {
            if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                $row[''] = $group;
            } else {
                $row['Цепочка'] = $transaction['chain_id'];
            }
            $row['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $inc;
            $row['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $exp;
        }
        $row['Ответственный'] = get_user_name($transaction);
        $row['Примечание'] = $transaction['comment'];
        return $row;
    }
}