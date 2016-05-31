<?php

interface TransactionAsInterface
{
    public function result(Array $transactions);

    public function outgo(Array $transaction);
    public function income(Array $transaction);
    public function transfer(Array $transaction);
    public function outgoGrouped(Array $transaction);
    public function incomeGrouped(Array $transaction);
    public function row($transaction, $transaction_id, $cashbox_info, $inc, $exp, $inc_sc = 0, $exp_sc = 0);
}