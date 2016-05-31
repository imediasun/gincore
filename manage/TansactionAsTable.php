<?php

require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/TransactionAsInterface.php';

class TransactionAsTable implements TransactionAsInterface
{
    protected $currencies;
    protected $total;
    protected $total_inc;
    protected $total_exp;
    protected $total_tr_inc;
    protected $total_tr_exp;
    protected $view;
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
     * TransactionAsTable constructor.
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
        $this->view = new View($all_configs);
    }

    /**
     * @param array $transactions
     * @return string
     */
    public function result(Array $transactions)
    {
        $header = $this->view->renderFile('transactions/as_table/header', array(
            'contractors' => $this->contractors
        ));
        $body = '';
        foreach ($transactions as $transaction_id => $transaction) {
            $cashbox_info = l('Неизвестная операция');
            $exp = $inc = 0;
            $inc_sc = show_price(array($transaction['value_to_sc']));
            $exp_sc = show_price(array($transaction['value_from_sc']));
            $inc_sc .= ' ' . viewCurrencySuppliers();
            $exp_sc .= ' ' . viewCurrencySuppliers();

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

            $body .= $this->row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $inc_sc, $exp_sc);
        }
        $footer = $this->view->renderFile('transactions/as_table/footer', array(
            'total' => $this->total()
        ));
        return $header . $body . $footer;
    }

    /**
     * @param array $transaction
     * @return array
     */
    public function outgo(Array $transaction)
    {
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
            $exp .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
                $this->currencies)
        ) {
            $inc .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
            $exp .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
            $inc .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
            $exp .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $inc .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $exp .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $inc .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
        $cashbox_info .= ' &larr; ' . $transaction['category_name'];
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
            $inc .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $exp .= '&nbsp;' . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $exp .= '&nbsp;' . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'] + $transaction['value_from_sc'] + $transaction['value_to_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @return string
     */
    private function total()
    {
        $out = '<tfoot><tr><td colspan="5"></td><td colspan="2">' . l('Итого') . ': ';
        $out_inc = $out_exp = $out_trans = '</td><td>';
        $set = false;
        foreach ($this->total as $k => $t) {
            $show = false;
            if ($this->total_inc[$k] > 0 || $this->total_tr_inc[$k] > 0 || $this->total_exp[$k] < 0 || $this->total_tr_exp[$k] < 0) {
                $class = 'data-toggle="tooltip" title="Итого" class="' . ($t > 0 ? 'text-success' : 'text-warning') . '"';
                $out .= '<br /><span ' . $class . '>' . show_price($t) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
                $set = $show = true;
            }
            if ($this->total_inc[$k] > 0 || $show == true) {
                $out_inc .= '<br /><span title="Доход" class="text-success">';
                $out_inc .= show_price($this->total_inc[$k]) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
            }
            if ($this->total_tr_inc[$k] > 0 || $this->total_tr_exp[$k] < 0) {
                $out_trans .= '<br /><span data-original-title="Перевод" class="popover-info" data-content="';
                $out_trans .= show_price($this->total_tr_inc[$k]) . '; ' . show_price($this->total_tr_exp[$k]);
                $out_trans .= '">' . show_price($this->total_tr_inc[$k] + $this->total_tr_exp[$k]) . '&nbsp;';
                $out_trans .= $this->currencies[$k]['shortName'] . '</span>';
            }
            if ($this->total_exp[$k] < 0 || $show == true) {
                $out_exp .= '<br /><span title="Расход" class="text-warning">';
                $out_exp .= show_price($this->total_exp[$k]) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
            }
        }
        if ($set == false) {
            $out .= 0;
        }
        $out .= $out_inc . $out_exp . $out_trans . '</td><td colspan="0"></td></tr><tfoot>';
        return $out;
    }

    /**
     * @param $transaction
     * @param $transaction_id
     * @param $cashbox_info
     * @param $inc
     * @param $exp
     * @param $inc_sc
     * @param $exp_sc
     * @return string
     */
    public function row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $inc_sc = 0, $exp_sc = 0)
    {
        return $this->view->renderFile('transactions/as_table/row', array(
            'transactions' => $transaction,
            'transaction_id' => $transaction_id,
            'cashbox_info' => $cashbox_info,
            'inc' => $inc,
            'exp' => $exp,
            'inc_sc' => $inc_sc,
            'exp_sc' => $exp_sc,
            'contractors' => $this->contractors
        ));
    }

}