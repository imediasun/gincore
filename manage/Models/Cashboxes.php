<?php
require_once __DIR__ . '/../Core/AModel.php';

class Cashboxes extends AModel
{
    public $table = '{cashboxes}';

    /**
     * @param string $query
     * @return array
     */
    public function getCashboxes($query = '')
    {
        return $this->db->query('SELECT c.name, c.id, c.avail, c.avail_in_balance, c.avail_in_orders, cc.amount, cc.currency,
              cr.name as cur_name, cr.short_name, cr.course, cr.currency
            FROM ?q as c
            LEFT JOIN (SELECT id, cashbox_id, amount, currency FROM {cashboxes_currencies})cc ON cc.cashbox_id=c.id
            LEFT JOIN (SELECT currency, name, short_name, course FROM {cashboxes_courses})cr ON cr.currency=cc.currency
            ?q ORDER BY c.id', array($this->table, $query))->assoc();
    }

    /**
     * @param $userId
     * @return array
     */
    public function getPreparedCashboxes($userId)
    {
        $query = '';
        if (!$this->all_configs['oRole']->hasPrivilege('accounting')) {
            $query = $this->db->makeQuery(" WHERE c.id in (SELECT cashbox_id FROM {cashboxes_users} WHERE user_id=?i) ",
                array($userId));
        }
        return $this->prepareCashboxes($this->getCashboxes($query));
    }

    /**
     * @param $cashboxes
     * @return array
     */
    public function calculateAmount($cashboxes)
    {
        $amounts = array('all' => 0, 'cashboxes' => array());
        if ($cashboxes) {
            foreach ($cashboxes as $cashbox) {

                if (!array_key_exists($cashbox['currency'], $amounts['cashboxes'])) {
                    $amounts['cashboxes'][$cashbox['currency']] = array(
                        'short_name' => $cashbox['short_name'],
                        'amount' => $cashbox['amount'],
                        'course' => $cashbox['course'],
                        'currency' => $cashbox['currency'],
                    );
                } else {
                    $amounts['cashboxes'][$cashbox['currency']]['amount'] += $cashbox['amount'];
                }

                $amounts['all'] += ($cashbox['amount'] * ($cashbox['course'] / 100));
            }
        }

        return $amounts;
    }

    /**
     * @param $cashboxes
     * @return array
     */
    public function prepareCashboxes($cashboxes)
    {
        $result = array();
        if ($cashboxes) {
            foreach ($cashboxes as $cashbox) {
                if (!array_key_exists($cashbox['id'], $result)) {
                    $result[$cashbox['id']] = array(
                        'id' => $cashbox['id'],
                        'name' => $cashbox['name'],
                        'avail' => $cashbox['avail'],
                        'avail_in_balance' => $cashbox['avail_in_balance'],
                        'avail_in_orders' => $cashbox['avail_in_orders'],
                        'currencies' => array()
                    );
                }
                if ($cashbox['currency'] > 0) {
                    $result[$cashbox['id']]['currencies'][$cashbox['currency']] = array(
                        'amount' => $cashbox['amount'],
                        'cur_name' => $cashbox['cur_name'],
                        'short_name' => $cashbox['short_name'],
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'cashboxes_type',
            'avail',
            'avail_in_balance',
            'avail_in_orders',
            'name',
        );
    }
}
