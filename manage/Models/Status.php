<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MStatus
 *
 */
class MStatus extends AModel
{
    public $table = 'status';

    /**
     *
     */
    public function setDefaultIfNeed()
    {
        if ($this->isEmpty()) {
            $this->setDefault();
        }
    }
    /**
     * @param int $type
     * @return array
     */
    public function getAll($type = ORDER_REPAIR)
    {
        $this->setDefaultIfNeed();
        return $this->query('SELECT * FROM ?t WHERE order_type=?i ORDER by status_id ASC',
            array($this->table, $type))->assoc('id');
    }

    /**
     *
     */
    protected function setDefault()
    {
        foreach ($this->all_configs['configs']['order-status'] as $id => $status) {
            $this->insert(array(
                'name' => $status['name'],
                '`from`' => json_encode($status['from']),
                'color' => $status['color'],
                'status_id' => $id,
                'order_type' => ORDER_REPAIR,
                'system' => 1,
                'use_in_manager' => 1,
                'active' => 1,
            ));
        }
        foreach ($this->all_configs['configs']['sale-order-status'] as $id => $status) {
            $this->insert(array(
                'name' => $status['name'],
                '`from`' => json_encode($status['from']),
                'color' => $status['color'],
                'status_id' => $id,
                'order_type' => ORDER_SELL,
                'system' => 1,
                'use_in_manager' => 1,
                'active' => 1,
            ));
        }
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'name',
            'from',
            'color',
            'status_id',
            'order_type',
            'system',
            'use_in_manager',
            'active',
        );
    }

    /**
     * @param int $type
     * @return string
     */
    public function getNextStatusId($type = ORDER_REPAIR)
    {
        $last = $this->query('SELECT status_id FROM ?t WHERE order_type=?i ORDER by status_id DESC LIMIT 1', array(
            $this->table,
            $type
        ))->el();
        return $last + 1;
    }

    /**
     * @param int $type
     * @return array
     */
    public function getStatus($type  = ORDER_REPAIR)
    {
        $status = $this->query('
            SELECT status_id, name, color, `from`, system, use_in_manager
            FROM ?t 
            WHERE order_type=?i AND active=1
            ORDER by status_id ASC
        ', array(
            $this->table,
            $type
        ))->assoc('status_id');
        if(!empty($status)) {
            foreach ($status as $id => $state) {
                $status[$id]['from'] = json_decode($state['from'], true);
            }
        }
        return $status;
    }
}
