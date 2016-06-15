<?php

require_once __DIR__ . '/TransactionAsInterface.php';

class TransactionShow
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
     * @param TransactionAsInterface $drawer
     * @param array                  $transactions
     * @return array
     */
    public function result(TransactionAsInterface $drawer, array $transactions)
    {
        $out = array();

        foreach ($transactions as $transaction_id => $transaction) {
            //$sum = 'Неизвестный перевод';
            $cashbox_info = l('Неизвестная операция');
            $exp = $inc = 0;
            if ($drawer->withCurrency()) {
                $inc_sc = show_price(array($transaction['value_to_sc']));
                $exp_sc = show_price(array($transaction['value_from_sc']));
                $inc_sc .= ' ' . viewCurrencySuppliers();
                $exp_sc .= ' ' . viewCurrencySuppliers();
            } else {
                $exp_sc = $inc_sc = 0;
            }

            if ($transaction['transaction_type'] == TRANSACTION_OUTPUT && $transaction['count_t'] == 0) {
                list($cashbox_info, $exp) = $this->outgo($drawer, $transaction);
            }
            if ($transaction['transaction_type'] == TRANSACTION_INPUT && $transaction['count_t'] == 0) {
                list($cashbox_info, $inc) = $this->income($drawer, $transaction);
            }
            if ($transaction['transaction_type'] == TRANSACTION_TRANSFER) {
                list($cashbox_info, $exp, $inc) = $this->transfer($drawer, $transaction);
            }
            if ($transaction['transaction_type'] == TRANSACTION_OUTPUT && $transaction['count_t'] > 0) {
                list($cashbox_info, $exp, $inc) = $this->outgoGrouped($drawer, $transaction);
            }
            if ($transaction['transaction_type'] == TRANSACTION_INPUT && $transaction['count_t'] > 0) {
                list($cashbox_info, $exp, $inc) = $this->incomeGrouped($drawer, $transaction);
            }
            $out[$transaction_id] = $drawer->row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $exp_sc,
                $inc_sc);
        }
        return $drawer->result($out, $this->totals());
    }

    /**
     * @param       $drawer
     * @param array $transaction
     * @return array
     */
    protected function outgo($drawer, Array $transaction)
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
        $cashbox_info .= $drawer::RIGHT_ARROW . $transaction['category_name'];
        // сумма
        if ($this->useSuppliersValue($transaction, $this->contractors)) {
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
            $exp .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
        }
        return array($cashbox_info, $exp);
    }

    /**
     * @param       $drawer
     * @param array $transaction
     * @return array
     */
    protected function income($drawer, Array $transaction)
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
        $cashbox_info .= $drawer::LEFT_ARROW . $transaction['category_name'];
        // сумма
        if ($this->useSuppliersValue($transaction, $this->contractors)) {
            $inc = show_price($transaction['value_to_sc']);
        } else {
            $inc = show_price($transaction['value_to']);
        }
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                $transaction['cashboxes']) &&
            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'],
                $this->currencies)
        ) {
            $inc .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $inc);
    }

    /**
     * @param       $drawer
     * @param array $transaction
     * @return array
     */
    protected function transfer($drawer, Array $transaction)
    {
        // с кассы
        $cashbox_info = '';
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'],
                $transaction['cashboxes'])
        ) {
            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
        }
        $cashbox_info .= $drawer::RIGHT_ARROW;
        // в кассу
        if (array_key_exists('cashboxes',
                $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'],
                $transaction['cashboxes'])
        ) {
            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
        }
        // сумма
        if ($this->useSuppliersValue($transaction, $this->contractors)) {
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
            $exp .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
            }
        } else {
            $exp .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
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
            $inc .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @param       $drawer
     * @param array $transaction
     * @return array
     */
    protected function outgoGrouped($drawer, Array $transaction)
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
        $cashbox_info .= $drawer::RIGHT_ARROW . $transaction['category_name'];
        // сумма
        if ($this->useSuppliersValue($transaction, $this->contractors)) {
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
            $exp .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $inc .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $exp .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $inc .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'] + $transaction['value_from_sc'] + $transaction['value_to_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @param       $drawer
     * @param array $transaction
     * @return array
     */
    protected function incomeGrouped($drawer, Array $transaction)
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
        $cashbox_info .= $drawer::LEFT_ARROW . $transaction['category_name'];
        // сумма
        if ($this->useSuppliersValue($transaction, $this->contractors)) {
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
            $inc .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $exp .= $drawer::DELIMITER . $this->currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
            $this->total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
            $this->total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
            $this->total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
            if ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders) {
                $this->total[$this->currency_suppliers_orders] += $transaction['value_from_sc'] + $transaction['value_to_sc'];
                $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from_sc'];
                $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to_sc'];
            }
        } else {
            $inc .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $exp .= $drawer::DELIMITER . $this->currencies[$this->currency_suppliers_orders]['shortName'];
            $this->total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'] + $transaction['value_from_sc'] + $transaction['value_to_sc'];
            $this->total_exp[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_from_sc'];
            $this->total_inc[$this->currency_suppliers_orders] += $transaction['value_to'] + $transaction['value_to_sc'];
        }
        return array($cashbox_info, $exp, $inc);
    }

    /**
     * @return array
     */
    public function totals()
    {
        return array(
            'total' => $this->total,
            'total_inc' => $this->total_inc,
            'total_exp' => $this->total_exp,
            'total_tr_inc' => $this->total_tr_inc,
            'total_tr_exp' => $this->total_tr_exp
        );
    }

    public function useSuppliersValue($transaction, $contractors)
    {
        return ($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'] == $this->currency_suppliers_orders && !$contractors && $this->currency_suppliers_orders != $this->all_configs['suppliers_orders']->currency_clients_orders);
    }
}
