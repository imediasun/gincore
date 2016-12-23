<?php

interface TransactionAsInterface
{
    public function withCurrency();
    public function result(Array $rows, Array $totals);
    public function row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $inc_sc = 0, $exp_sc = 0);
}