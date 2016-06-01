<?php

require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/TransactionAsInterface.php';

class TransactionAsTable implements TransactionAsInterface
{
    protected $currencies;
    protected $view;
    const DELIMITER = '&nbsp;';
    const RIGHT_ARROW = ' &rarr; ';
    const LEFT_ARROW = ' &larr; ';
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
        $this->contractors = $contractors;
        $this->all_configs = $all_configs;
        $this->currency_suppliers_orders = $currency_suppliers_orders;
        $this->view = new View($all_configs);
    }

    /**
     * @param array $rows
     * @param array $totals
     * @return string
     * @internal param array $transactions
     */
    public function result(Array $rows, array $totals)
    {
        $header = $this->view->renderFile('transactions/as_table/header', array(
            'contractors' => $this->contractors
        ));
        $footer = $this->view->renderFile('transactions/as_table/footer', array(
            'total' => $this->total($totals)
        ));
        return $header . implode(' ', $rows) . $footer;
    }

    /**
     * @param array $totals
     * @return string
     */
    private function total(array $totals)
    {
        $out = '<tfoot><tr><td colspan="5"></td><td colspan="2">' . l('Итого') . ': ';
        $out_inc = $out_exp = $out_trans = '</td><td>';
        $set = false;
        foreach ($totals['total'] as $k => $t) {
            $show = false;
            if ($totals['total_inc'][$k] > 0 || $totals['total_tr_inc'][$k] > 0 || $totals['total_exp'][$k] < 0 || $totals['total_tr_exp'][$k] < 0) {
                $class = 'data-toggle="tooltip" title="Итого" class="' . ($t > 0 ? 'text-success' : 'text-warning') . '"';
                $out .= '<br /><span ' . $class . '>' . show_price($t) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
                $set = $show = true;
            }
            if ($totals['total_inc'][$k] > 0 || $show == true) {
                $out_inc .= '<br /><span title="Доход" class="text-success">';
                $out_inc .= show_price($totals['total_inc'][$k]) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
            }
            if ($totals['total_tr_inc'][$k] > 0 || $totals['total_tr_exp'][$k] < 0) {
                $out_trans .= '<br /><span data-original-title="Перевод" class="popover-info" data-content="';
                $out_trans .= show_price($totals['total_tr_inc'][$k]) . '; ' . show_price($totals['total_tr_exp'][$k]);
                $out_trans .= '">' . show_price($totals['total_tr_inc'][$k] + $totals['total_tr_exp'][$k]) . '&nbsp;';
                $out_trans .= $this->currencies[$k]['shortName'] . '</span>';
            }
            if ($totals['total_exp'][$k] < 0 || $show == true) {
                $out_exp .= '<br /><span title="Расход" class="text-warning">';
                $out_exp .= show_price($totals['total_exp'][$k]) . '&nbsp;' . $this->currencies[$k]['shortName'] . '</span>';
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

    /**
     * @return bool
     */
    public function withCurrency()
    {
        return true;
    }
}