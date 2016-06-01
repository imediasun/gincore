<?php

require_once __DIR__ . '/TransactionAsInterface.php';

class TransactionAsArray implements TransactionAsInterface
{
    protected $currencies;
    private $contractors;
    private $all_configs;
    private $currency_suppliers_orders;
    
    const DELIMITER = ' ';
    const RIGHT_ARROW = ' -> ';
    const LEFT_ARROW = ' <- ';
    

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
        $this->contractors = $contractors;
        $this->all_configs = $all_configs;
        $this->currency_suppliers_orders = $currency_suppliers_orders;
    }

    /**
     * @param array $rows
     * @param array $totals
     * @return array
     * @internal param array $transactions
     */
    public function result(array $rows, array $totals)
    {
        return $rows;
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

    /**
     * @return bool
     */
    public function withCurrency()
    {
        return false;
    }
}